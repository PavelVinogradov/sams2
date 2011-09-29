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
#include "timerange.h"

#include "debug.h"

TimeRange::TimeRange(long id)
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  _id = id;
}


TimeRange::~TimeRange()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
}

long TimeRange::getId () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _id);
  return _id;
}

void TimeRange::setTimeRange(const string &days, const string &tstart, const string &tend)
{
  struct tm date_time;
  char *rest;
  char strbuf[10];
  string spec;

  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << days << ", " << tstart << ", " << tend << ")]");

  spec = "2000-01-01 " + tstart;
  DEBUG (DEBUG9, "Converting time " << tstart);
  rest = strptime (spec.c_str (), "%Y-%m-%d %H:%M:%S", &date_time);
  if (rest == NULL)
    {
      ERROR ("Invalid time specification: " << tstart);
      return;
    }
  _time_start = mktime (&date_time);

  strftime (strbuf, sizeof (strbuf), "%H:%M", localtime (&_time_start));
  _tstart = strbuf;

  spec = "2000-01-01 " + tend;
  DEBUG (DEBUG9, "Converting time " << tend);
  rest = strptime (spec.c_str (), "%Y-%m-%d %H:%M:%S", &date_time);
  if (rest == NULL)
    {
      ERROR ("Invalid time specification: " << tend);
      return;
    }
  _time_end = mktime (&date_time);

  strftime (strbuf, sizeof (strbuf), "%H:%M", localtime (&_time_end));
  _tend = strbuf;

  _days = days;

  if (tstart > tend)
    _hasMidnight = true;
  else
    _hasMidnight = false;
}

bool TimeRange::hasNow () const
{
  char strbuf[10];
  string spec;
  struct tm date_time;

  time_t now = time (NULL);

  struct tm *tm_now = localtime(&now);
  string week_day = "";
  switch (tm_now->tm_wday)
    {
      case 0: week_day = "S"; break;
      case 1: week_day = "M"; break;
      case 2: week_day = "T"; break;
      case 3: week_day = "W"; break;
      case 4: week_day = "H"; break;
      case 5: week_day = "F"; break;
      case 6: week_day = "A"; break;
      default: return false; break; // По идее такого не должно быть
    }

  DEBUG (DEBUG4, "Checking if " << week_day << " is in " << _days);

  //текущий день недели не входит в указанный список
  if (_days.find(week_day) == string::npos)
    {
      DEBUG (DEBUG4, "Current week day is not in the list");
      return false;
    }

  //Если доступ ограничен только по дням недели, то время не проверяем
  if (_time_start == _time_end)
    {
      DEBUG (DEBUG4, "Full day access");
      return true;
    }

  strftime (strbuf, sizeof (strbuf), "%H:%M:%S", localtime (&now));
  spec = "2000-01-01 ";
  spec += strbuf;
  DEBUG (DEBUG9, "Converting time " << spec);
  strptime (spec.c_str (), "%Y-%m-%d %H:%M:%S", &date_time);
  now = mktime (&date_time);

  time_t end = _time_end;
  if (_hasMidnight)
    {
      end += 86400;
    }

  DEBUG (DEBUG4, "Checking if " << now << " between " << _time_start << " and " << end);

  if (difftime (now, _time_start) >= 0 && difftime (end, now) >= 0)
    {
      DEBUG (DEBUG4, "Time inside range");
      return true;
    }
  else
    {
      DEBUG (DEBUG4, "Time outside range");
      return false;
    }
}

bool TimeRange::hasMidnight () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _hasMidnight);
  return _hasMidnight;
}

bool TimeRange::isFullDay () const
{
  return (_time_start == _time_end);
}

string TimeRange::getDays () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _days);
  return _days;
}

string TimeRange::getStartTimeStr () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _tstart);
  return _tstart;
}

string TimeRange::getEndTimeStr () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _tend);
  return _tend;
}

