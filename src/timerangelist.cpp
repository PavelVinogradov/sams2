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
#include "config.h"

#include "dbconn.h"
#include "dbquery.h"
#include "timerangelist.h"
#include "timerange.h"
#include "debug.h"
#include "samsconfig.h"

bool TimeRangeList::_loaded = false;
DBConn *TimeRangeList::_conn;                ///< Соединение с БД
bool TimeRangeList::_connection_owner;
map<long, TimeRange*> TimeRangeList::_list;

bool TimeRangeList::load()
{
  if (_loaded)
    return true;

  return reload();
}

bool TimeRangeList::reload()
{
  DEBUG (DEBUG2, "[" << __FUNCTION__ << "] ");

  destroy ();
  if (!_conn)
    {
      _conn = SamsConfig::newConnection ();
      if (!_conn)
        {
          ERROR ("Unable to create connection.");
          return false;
        }

      if (!_conn->connect ())
        {
          delete _conn;
          return false;
        }
      _connection_owner = true;
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Using new connection " << _conn);
    }
    else
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Using old connection " << _conn);
    }


  basic_stringstream < char >sqlcmd;
  DBQuery *query = NULL;
  _conn->newQuery (query);

  if (!query)
    {
      ERROR("Unable to create query.");
      return false;
    }

  long s_trange_id;
  char s_days[10];
  char s_timestart[20];
  char s_timeend[20];

  if (!query->bindCol (1, DBQuery::T_LONG,  &s_trange_id, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (2, DBQuery::T_CHAR,  s_days, sizeof(s_days)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (3, DBQuery::T_CHAR,  s_timestart, sizeof(s_timestart)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (4, DBQuery::T_CHAR,  s_timeend, sizeof(s_timeend)))
    {
      delete query;
      return false;
    }

  if (!query->sendQueryDirect ("select s_trange_id, s_days, s_timestart, s_timeend from timerange"))
    {
      delete query;
      return false;
    }

  basic_stringstream < char >sql_cmd;

  TimeRange *trange = NULL;
  //_list.clear();
  while (query->fetch())
    {
      trange = new TimeRange (s_trange_id);
      trange->setTimeRange (s_days, s_timestart, s_timeend);
      _list[s_trange_id] = trange;

      DEBUG (DEBUG9, "[" << __FUNCTION__ << "] Found Time Interval: " <<
        "id=" << s_trange_id << " " <<
        "days=" << s_days << " " <<
        "start=" << s_timestart << " " <<
        "end=" << s_timeend
        );
    }

  delete query;
  _loaded = true;

  return true;
}

void TimeRangeList::useConnection (DBConn * conn)
{
  if (_conn)
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Already using " << _conn);
      return;
    }
  if (conn)
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Using external connection " << conn);
      _conn = conn;
      _connection_owner = false;
    }
}

void TimeRangeList::destroy()
{
  if (_connection_owner && _conn)
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Destroy connection " << _conn);
      delete _conn;
      _conn = NULL;
    }
  else if (_conn)
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Not owner for connection " << _conn);
    }
  else
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Not connected");
    }

  map <long, TimeRange*>::iterator it;
  for (it = _list.begin (); it != _list.end (); it++)
    {
      delete it->second;
    }

  _list.clear ();
}

vector <TimeRange*> TimeRangeList::getList ()
{
  load ();

  vector <TimeRange*> lst;
  map <long, TimeRange*>::const_iterator it;
  for (it = _list.begin (); it != _list.end (); it++)
    {
      lst.push_back (it->second);
    }
  return lst;
}

TimeRange * TimeRangeList::getTimeRange (long id)
{
  load();

  map < long, TimeRange* >::iterator it = _list.find(id);
  if (it != _list.end ())
    return it->second;

  WARNING ( "Time interval " << id << " not found.");
  return NULL;
}
