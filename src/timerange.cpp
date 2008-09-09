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

TimeRange::TimeRange(long id, const string &name)
{
  _id = id;
}


TimeRange::~TimeRange()
{
}

long TimeRange::getId () const
{
  return _id;
}

void TimeRange::setTimeRange(const string &days, const string &tstart, const string &tend)
{
  struct tm date_time;
  char *rest;
  char strbuf[10];
  string spec;

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

bool TimeRange::hasMidnight () const
{
  return _hasMidnight;
}

bool TimeRange::isFullDay () const
{
  return (_tstart == _tend);
}

string TimeRange::getDays () const
{
  return _days;
}

string TimeRange::getStartTimeStr () const
{
  return _tstart;
}

string TimeRange::getEndTimeStr () const
{
  return _tend;
}

