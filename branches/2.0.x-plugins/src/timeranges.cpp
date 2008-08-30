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

#ifdef USE_UNIXODBC
#include "odbcconn.h"
#include "odbcquery.h"
#endif

#ifdef USE_MYSQL
#include "mysqlconn.h"
#include "mysqlquery.h"
#endif

#ifdef USE_PQ
#include "pgconn.h"
#include "pgquery.h"
#endif

#include "timeranges.h"
#include "timerange.h"
#include "debug.h"
#include "samsconfig.h"

bool TimeRanges::_loaded = false;
DBConn *TimeRanges::_conn;                ///< Соединение с БД
bool TimeRanges::_connection_owner;
map<string, TimeRange*> TimeRanges::_list;

bool TimeRanges::load()
{
  if (_loaded)
    return true;

  return reload();
}

bool TimeRanges::reload()
{
  DEBUG (DEBUG_TPL, "[" << __FUNCTION__ << "] ");

  if (!_conn)
    {
      DBConn::DBEngine engine = SamsConfig::getEngine();

      if (engine == DBConn::DB_UODBC)
        {
          #ifdef USE_UNIXODBC
          _conn = new ODBCConn();
          #else
          return false;
          #endif
        }
      else if (engine == DBConn::DB_MYSQL)
        {
          #ifdef USE_MYSQL
          _conn = new MYSQLConn();
          #else
          return false;
          #endif
        }
      else
        return false;

      if (!_conn->connect ())
        {
          delete _conn;
          return false;
        }
      _connection_owner = true;
      DEBUG (DEBUG_TPL, "[" << __FUNCTION__ << "] Using new connection " << _conn);
    }
    else
    {
      DEBUG (DEBUG_TPL, "[" << __FUNCTION__ << "] Using old connection " << _conn);
    }


  basic_stringstream < char >sqlcmd;
  DBQuery *query = NULL;

  DBConn::DBEngine engine = SamsConfig::getEngine();

  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      query = new ODBCQuery((ODBCConn*)_conn);
      #else
      return false;
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      query = new MYSQLQuery((MYSQLConn*)_conn);
      #else
      return false;
      #endif
    }
  else
    return false;

  long s_trange_id;
  char s_name[25];
  char s_days[10];
  char s_timestart[20];
  char s_timeend[20];

  if (!query->bindCol (1, DBQuery::T_LONG,  &s_trange_id, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (2, DBQuery::T_CHAR,  s_name, sizeof(s_name)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (3, DBQuery::T_CHAR,  s_days, sizeof(s_days)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (4, DBQuery::T_CHAR,  s_timestart, sizeof(s_timestart)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (5, DBQuery::T_CHAR,  s_timeend, sizeof(s_timeend)))
    {
      delete query;
      return false;
    }

  if (!query->sendQueryDirect ("select s_trange_id, s_name, s_days, s_timestart, s_timeend from timerange"))
    {
      delete query;
      return false;
    }

  basic_stringstream < char >sql_cmd;

  TimeRange *trange = NULL;
  _list.clear();
  while (query->fetch())
    {
      DEBUG (DEBUG_TPL, "[" << __FUNCTION__ << "] " << s_trange_id << ", " << s_name << ", " << s_days << ", " << s_timestart << ", " << s_timeend);

      trange = new TimeRange (s_trange_id, s_name);
      trange->setTimeRange (s_days, s_timestart, s_timeend);
      _list[s_name] = trange;
    }

  delete query;
  _loaded = true;

  return true;
}

void TimeRanges::useConnection (DBConn * conn)
{
  if (_conn)
    {
      DEBUG (DEBUG_TPL, "[" << __FUNCTION__ << "] Already using " << _conn);
      return;
    }
  if (conn)
    {
      DEBUG (DEBUG_TPL, "[" << __FUNCTION__ << "] Using external connection " << conn);
      _conn = conn;
      _connection_owner = false;
    }
}

void TimeRanges::destroy()
{
  if (_connection_owner && _conn)
    {
      DEBUG (DEBUG_TPL, "[" << __FUNCTION__ << "] Destroy connection " << _conn);
      delete _conn;
      _conn = NULL;
    }
  else
    {
      DEBUG (DEBUG_TPL, "[" << __FUNCTION__ << "] Not owner for connection " << _conn);
    }
}

vector<long> TimeRanges::getIds()
{
  load();

  vector<long> lst;
  map <string, TimeRange*>::iterator it;
  for (it = _list.begin (); it != _list.end (); it++)
    {
      lst.push_back((*it).second->getId ());
    }
  return lst;
}

TimeRange * TimeRanges::getTimeRange (long id)
{
  load();

  map < string, TimeRange* >::iterator it;
  for (it = _list.begin (); it != _list.end (); it++)
    {
      if (id == (*it).second->getId ())
        return (*it).second;
    }
  DEBUG (DEBUG_TPL, "[" << __FUNCTION__ << "] " << id << " not found");
  return NULL;
}
