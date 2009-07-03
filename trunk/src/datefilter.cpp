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
#include <sstream>

#include "datefilter.h"
#include "debug.h"
#include "tools.h"

DateFilter::DateFilter ():Filter ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
  setDateInterval (",");
}

DateFilter::DateFilter (const string & dateSpec):Filter ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
  setDateInterval (dateSpec);
}

DateFilter::~DateFilter ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
}

bool DateFilter::setDateInterval (const string & dateSpec)
{
  vector < string > tblDates;
  string str_date_start;
  string str_date_end;
  struct tm date_time;
  char str_today[15];
  time_t today;
  char *rest;

  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << dateSpec << ")]");

  Split (dateSpec, ",", tblDates);

  if (tblDates.size () == 2)
    {
      str_date_start = tblDates[0];
      str_date_end = tblDates[1];
    }
  else if (tblDates.size () == 1)
    {
      str_date_start = tblDates[0];
      str_date_end = tblDates[0];
    }
  else
    {
      str_date_start = "";
      str_date_end = "";
    }

  if (str_date_start.empty ())
    {
      str_date_start = "2000-01-01";
    }

  if (str_date_end.empty ())
    {
      today = time (NULL);
      strftime (str_today, sizeof (str_today), "%Y-%m-%d", localtime (&today));
      str_date_start = str_today;
    }

  str_date_start += " 00:00:00";
  str_date_end += " 23:59:59";


  DEBUG (DEBUG8, "Converting date " << str_date_start);
  rest = strptime (str_date_start.c_str (), "%Y-%m-%d %H:%M:%S", &date_time);
  if (rest == NULL)
    {
      ERROR ("Invalid date specification: " << str_date_start);
      return false;
    }
  _date_start = mktime (&date_time);

  DEBUG (DEBUG8, "Converting date " << str_date_end);
  rest = strptime (str_date_end.c_str (), "%Y-%m-%d %H:%M:%S", &date_time);
  if (rest == NULL)
    {
      ERROR ("Invalid date specification: " << str_date_end);
      return false;
    }
  _date_end = mktime (&date_time);

  _validity = true;

  return _validity;
}

bool DateFilter::match (struct tm & date)
{
  if (!_validity)
    return true;

  time_t t = mktime (&date);

  bool inside = ((unsigned long) _date_start <= (unsigned long) t) &&
                ((unsigned long) t <= (unsigned long) _date_end);

  DEBUG (DEBUG9, (unsigned long) _date_start << " " << (unsigned long) t << " " << (unsigned long) _date_end);

  return inside;
}


string DateFilter::getStartDateAsString () const
{
  basic_stringstream < char >s;
  char strbuf[20];

  strftime (strbuf, sizeof (strbuf), "%Y-%m-%d", localtime (&_date_start));
  s << strbuf;

  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << s);

  return s.str ();
}

string DateFilter::getEndDateAsString () const
{
  basic_stringstream < char >s;
  char strbuf[20];

  strftime (strbuf, sizeof (strbuf), "%Y-%m-%d", localtime (&_date_end));
  s << strbuf;

  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << s);

  return s.str ();
}
