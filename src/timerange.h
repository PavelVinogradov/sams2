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
#ifndef TIMERANGE_H
#define TIMERANGE_H

using namespace std;

#include <string>

#include <time.h>

class TimeRange
{
public:
  TimeRange(long id, const string &name);

  ~TimeRange();

  long getId () const;

  void setTimeRange(const string &days, const string &tstart, const string &tend);

  bool hasMidnight () const;

  string getDays () const;

  string getStartTimeStr () const;

  string getEndTimeStr () const;

private:
  long  _id;
  string _days;
  string _tstart;
  string _tend;
  time_t _time_start;           ///< Начало интервала
  time_t _time_end;             ///< Окончание интервала
  bool _hasMidnight;
};

#endif
