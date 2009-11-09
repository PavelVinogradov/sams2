/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/
#include "template.h"
#include "timerangelist.h"
#include "timerange.h"
#include "urlgrouplist.h"
#include "urlgroup.h"
#include "proxy.h"
#include "tools.h"

#include "debug.h"

Template::Template (const long & id, const long & id2)
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "(" << id << ", " << id2 << ")]");

  _id = id;
  _id2 = id2;
  _period_type = Template::PERIOD_MONTH;
}


Template::~Template ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  _times.clear ();
  _urlgroups.clear ();
}

long Template::getId () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _id);
  return _id;
}

long Template::getLimitedId () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _id2);
  return _id2;
}

void Template::setAuth (const string & auth)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << auth << ")]");

  _auth = Proxy::toAuthType(auth);
}

void Template::setAuth (const Proxy::usrAuthType & auth)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(...)]");
  _auth = auth;
}

Proxy::usrAuthType Template::getAuth () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << Proxy::toString (_auth));
  return _auth;
}

void Template::setQuote (const long & quote)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << quote << ")]");
  _quote = quote;
}

long Template::getQuote () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _quote);
  return _quote;
}

void Template::setPeriod (const Template::PeriodType & ptype, const long & days)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(..., " << days << ")]");

  _period_type = ptype;
  _period_days = days;
}

Template::PeriodType Template::getPeriodType () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = ...");
  return _period_type;
}

void Template::setClearDate (const string & dateSpec)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << dateSpec << ")]");
  _clear_date = dateSpec + " 00:00:00";
}

bool Template::getClearDate (struct tm & clear_date) const
{
  // Если период очистки счетчиков стандартный, то нужно проверять по смене периода
  // например, начало новой недели или начало нового месяца
  if (_period_type != Template::PERIOD_CUSTOM)
    return false;
  if (_clear_date.empty ())
    return false;

  char *rest;
  rest = strptime (_clear_date.c_str (), "%Y-%m-%d %H:%M:%S", &clear_date);
  if (rest == NULL)
    {
      ERROR ("Invalid date specification: " << _clear_date);
      return false;
    }
  return true;
}

bool Template::getClearDateStr (string & date_str) const
{
  // Если период очистки счетчиков стандартный, то нужно проверять по смене периода
  // например, начало новой недели или начало нового месяца
  if (_period_type != Template::PERIOD_CUSTOM)
    return false;
  if (_clear_date.empty ())
    return false;


  struct tm clear_date;
  char *rest;
  rest = strptime (_clear_date.c_str (), "%Y-%m-%d %H:%M:%S", &clear_date);
  if (rest == NULL)
    {
      ERROR ("Invalid date specification: " << _clear_date);
      return false;
    }

  date_str = _clear_date.substr (0, 10);

  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << date_str);

  return true;
}

void Template::adjustClearDate()
{
  // Если период очистки счетчиков стандартный, то дата обнуления счетчиков не важна.
  if (_period_type != Template::PERIOD_CUSTOM)
    return;

  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "]");

  struct tm clear_date;
  time_t new_date;
  time_t now;
  char str_new_date[15];
  char *rest;
  rest = strptime (_clear_date.c_str (), "%Y-%m-%d %H:%M:%S", &clear_date);
  if (rest == NULL)
    {
      ERROR ("Invalid date specification: " << _clear_date);
      return;
    }
  new_date = mktime(&clear_date);
  // Если с последнего обновления прошло несколько периодов, то учтем это,
  // чтобы следующая дата очистки не оказалась в прошлом.
  now = time (NULL);
  while (difftime (new_date, now) < 86399)
    {
      new_date += _period_days * 86400;
    }

  strftime (str_new_date, sizeof (str_new_date), "%Y-%m-%d", localtime (&new_date));
  _clear_date = str_new_date;
  _clear_date += " 00:00:00";

  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] new date: " << _clear_date);
}

bool Template::insidePeriod(struct tm &date_time) const
{
  string str_start;
  string str_end;
  string str_tmp2;
  char str_tmp[15];
  time_t start;
  time_t end;
  time_t to_test;
  time_t now = time (NULL);
  struct tm *now_tm = localtime (&now);
  struct tm tmp_tm;
  char *rest;

  int days_before;

  switch (_period_type)
    {
      case PERIOD_MONTH:
        strftime (str_tmp, sizeof (str_tmp), "%Y-%m-01", now_tm);
        str_start = str_tmp;
        switch (now_tm->tm_mon)
          {
            case 0:   // Январь
            case 2:   // Март
            case 4:   // Май
            case 6:   // Июль
            case 7:   // Август
            case 9:   // Октябрь
            case 11:  // Декабрь
              now_tm->tm_mday = 31;
              break;
            case 3:   // Апрель
            case 5:   // Июнь
            case 8:   // Сентябрь
            case 10:  // Ноябрь
              now_tm->tm_mday = 30;
              break;
            case 1:   // Февраль
              if (now_tm->tm_year % 4 == 0)
                now_tm->tm_mday = 29;
              else
                now_tm->tm_mday = 28;
              break;
          }
        strftime (str_tmp, sizeof (str_tmp), "%Y-%m-%d", now_tm);
        str_end = str_tmp;
        break;
      case PERIOD_WEEK:
        days_before = now_tm->tm_wday - 1;
        if (days_before<0)
          days_before = 6;

        timeSubstractDays(*now_tm, days_before);
        strftime (str_tmp, sizeof (str_tmp), "%Y-%m-%d", now_tm);
        str_start = str_tmp;

        timeSubstractDays(*now_tm, -6);
        strftime (str_tmp, sizeof (str_tmp), "%Y-%m-%d", now_tm);
        str_end = str_tmp;
        break;
      case PERIOD_DAY:
        strftime (str_tmp, sizeof (str_tmp), "%Y-%m-%d", now_tm);
        str_start = str_tmp;
        str_end = str_tmp;
        break;
      case PERIOD_CUSTOM:
        str_tmp2 = _clear_date + " 00:00:00";
        rest = strptime (str_tmp2.c_str (), "%Y-%m-%d %H:%M:%S", &tmp_tm);
        timeSubstractDays(tmp_tm, _period_days+1);
        strftime (str_tmp, sizeof (str_tmp), "%Y-%m-%d", &tmp_tm);
        str_start = str_tmp;

        str_tmp2 = _clear_date + " 23:59:59";
        rest = strptime (str_tmp2.c_str (), "%Y-%m-%d %H:%M:%S", &tmp_tm);
        timeSubstractDays(tmp_tm, 1);
        strftime (str_tmp, sizeof (str_tmp), "%Y-%m-%d", &tmp_tm);
        str_end = str_tmp;
        break;
    }

  str_start += " 00:00:00";
  str_end += " 23:59:59";

  rest = strptime (str_start.c_str (), "%Y-%m-%d %H:%M:%S", &tmp_tm);
  start = mktime (&tmp_tm);
  rest = strptime (str_end.c_str (), "%Y-%m-%d %H:%M:%S", &tmp_tm);
  end = mktime (&tmp_tm);
  to_test = mktime (&date_time);

  bool inside = ((unsigned long) start <= (unsigned long) to_test) &&
                ((unsigned long) to_test <= (unsigned long) end);

  return inside;
}

void Template::setAllDeny (const bool & alldeny)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << alldeny << ")]");
  _alldeny = alldeny;
}

bool Template::getAllDeny () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _alldeny);
  return _alldeny;
}

void Template::addTimeRange (const long & id)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << id << ")]");
  _times.push_back (id);
}

vector <long> Template::getTimeRangeIds () const
{
  return _times;
}

void Template::addUrlGroup (const long & id)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << id << ")]");

  UrlGroup *grp = UrlGroupList::getUrlGroup (id);

  if (grp)
    _urlgroups.push_back (grp);
}

vector <long> Template::getUrlGroupIds () const
{
  vector <long> res;
  vector <UrlGroup*>::const_iterator it;

  for (it = _urlgroups.begin (); it != _urlgroups.end (); it++)
    {
      res.push_back ((*it)->getId ());
    }

  return res;
}

bool Template::isTimeDenied (const string & url) const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << url << ")]");

  TimeRange * tr = NULL;
  vector <long>::const_iterator it;

  if (_times.empty () )
    return false;

  for (it = _times.begin (); it != _times.end(); it++)
    {
      tr = TimeRangeList::getTimeRange (*it);
      if (!tr)
        continue;
      if (tr->hasNow ())
        return false;
    }

  return true;
}

bool Template::isUrlWhitelisted (const string &url) const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << url << ")]");

  vector <UrlGroup*>::const_iterator it;

  for (it = _urlgroups.begin (); it != _urlgroups.end (); it++)
    {
      if ((*it)->getAccessType () == UrlGroup::ACC_ALLOW && (*it)->hasUrl (url))
        return true;
    }
  return false;
}

bool Template::isUrlBlacklisted (const string &url) const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << url << ")]");

  vector <UrlGroup*>::const_iterator it;

  for (it = _urlgroups.begin (); it != _urlgroups.end (); it++)
    {
      if ((*it)->getAccessType () == UrlGroup::ACC_DENY && (*it)->hasUrl (url))
        return true;
    }
  return false;
}

bool Template::isUrlHasFileExt (const string & url) const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << url << ")]");

  vector <UrlGroup*>::const_iterator it;

  for (it = _urlgroups.begin (); it != _urlgroups.end (); it++)
    {
      if ((*it)->getAccessType () == UrlGroup::ACC_FILEEXT && (*it)->hasUrl (url))
        return true;
    }
  return false;
}

bool Template::isUrlMatchRegex (const string & url) const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << url << ")]");

  vector <UrlGroup*>::const_iterator it;

  for (it = _urlgroups.begin (); it != _urlgroups.end (); it++)
    {
      if ((*it)->getAccessType () == UrlGroup::ACC_REGEXP && (*it)->hasUrl (url))
        return true;
    }
  return false;
}

string Template::modifyUrl (const string & url) const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << url << ")]");

  vector <UrlGroup*>::const_iterator it;
  string res = "";

  for (it = _urlgroups.begin (); it != _urlgroups.end (); it++)
    {
      res = (*it)->modifyUrl (url);
      if (!res.empty ())
        break;
    }

  return res;
}
