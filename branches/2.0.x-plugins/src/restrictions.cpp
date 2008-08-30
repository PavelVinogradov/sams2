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

#include "restrictions.h"

#include "samsconfig.h"
#include "debug.h"

bool Restrictions::_loaded = false;
DBConn * Restrictions::_conn;
bool Restrictions::_connection_owner = false;

void Restrictions::useConnection (DBConn * conn)
{
  if (_conn)
    {
      DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] Already using " << _conn);
      return;
    }
  if (conn)
    {
      DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] Using external connection " << conn);
      _conn = conn;
      _connection_owner = false;
    }
}


bool Restrictions::reload()
{
  DBQuery *query = NULL;

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
      else if (engine == DBConn::DB_PGSQL)
        {
          #ifdef USE_PQ
          _conn = new PgConn();
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
      DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] Using new connection " << _conn);
    }
    else
    {
      DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] Using old connection " << _conn);
    }

  if (_conn->getEngine() == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      query = new ODBCQuery( (ODBCConn*)_conn );
      #else
      return false;
      #endif
    }
  else if (_conn->getEngine() == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      query = new MYSQLQuery( (MYSQLConn*)_conn );
      #else
      return false;
      #endif
    }
  else if (_conn->getEngine() == DBConn::DB_PGSQL)
    {
      #ifdef USE_PQ
      query = new PgQuery( (PgConn*)_conn );
      #else
      return false;
      #endif
    }
  else
    return false;

  // Загружаем данные тут

  delete query;

  _loaded = true;

  return true;
}

void Restrictions::destroy()
{
  if (_connection_owner && _conn)
    {
      DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] Destroy connection " << _conn);
      delete _conn;
      _conn = NULL;
    }
  else if (_conn)
    {
      DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] Not owner for connection " << _conn);
    }
  else
    {
      DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] Not connected");
    }

  // Уничтожаем данные тут
}

bool Restrictions::load ()
{
  if (_loaded)
    return true;

  return reload();
}

