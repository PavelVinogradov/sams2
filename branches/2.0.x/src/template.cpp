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
#include "timeranges.h"
#include "timerange.h"
#include "urlgrouplist.h"
#include "urlgroup.h"

#include "debug.h"

Template::Template (long id)
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  _id = id;
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

void Template::setAuth (const string & auth)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << auth << ")]");

  if (auth == "ip")
    _auth = Proxy::AUTH_IP;
  else if (auth == "ncsa")
    _auth = Proxy::AUTH_NCSA;
  else if (auth == "ntlm")
    _auth = Proxy::AUTH_NTLM;
  else if (auth == "adld")
    _auth = Proxy::AUTH_ADLD;
  else if (auth == "ldap")
    _auth = Proxy::AUTH_LDAP;
  else
    {
      ERROR ("Unknown authentication scheme: " << auth);
    }
}

void Template::setAuth (Proxy::usrAuthType auth)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(...)]");
  _auth = auth;
}

Proxy::usrAuthType Template::getAuth () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = ...");
  return _auth;
}

void Template::setQuote (long quote)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << quote << ")]");
  _quote = quote;
}

long Template::getQuote () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _quote);
  return _quote;
}

void Template::setPeriod (Template::PeriodType ptype, long days)
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

void Template::setAllDeny (bool alldeny)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << alldeny << ")]");
  _alldeny = alldeny;
}

bool Template::getAllDeny () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _alldeny);
  return _alldeny;
}

void Template::addTimeRange (long id)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << id << ")]");
  _times.push_back (id);
}

vector <long> Template::getTimeRangeIds () const
{
  return _times;
}

void Template::addUrlGroup (long id)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << id << ")]");
  _urlgroups.push_back (id);
}

vector <long> Template::getUrlGroupIds () const
{
  return _urlgroups;
}

bool Template::isTimeDenied (const string & url) const
{
  TimeRange * tr = NULL;
  vector <long>::const_iterator it;

  if (_times.empty () )
    return false;

  for (it = _times.begin (); it != _times.end(); it++)
    {
      tr = TimeRanges::getTimeRange (*it);
      if (!tr)
        continue;
      if (tr->hasNow ())
        return false;
    }

  return true;
}

bool Template::isUrlWhitelisted (const string &url) const
{
  vector <long>::const_iterator it;
  UrlGroup *grp = NULL;

  for (it = _urlgroups.begin (); it != _urlgroups.end (); it++)
    {
      grp = UrlGroupList::getUrlGroup (*it);
      if (!grp)
        continue;
      if (grp->getAccessType () == UrlGroup::ACC_ALLOW && grp->hasUrl (url))
        return true;
    }
  return false;
}

bool Template::isUrlBlacklisted (const string &url) const
{
  vector <long>::const_iterator it;
  UrlGroup *grp = NULL;

  for (it = _urlgroups.begin (); it != _urlgroups.end (); it++)
    {
      grp = UrlGroupList::getUrlGroup (*it);
      if (!grp)
        continue;
      if (grp->getAccessType () == UrlGroup::ACC_DENY && grp->hasUrl (url))
        return true;
    }
  return false;
}
