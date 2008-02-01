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

#include "dbconn.h"
#include "localnetworks.h"
#include "debug.h"
#include "url.h"
#include "net.h"
#include "samsconfig.h"

bool LocalNetworks::_loaded = false;
vector < Net * > LocalNetworks::_nets;
DBConn *LocalNetworks::_conn = NULL;
bool LocalNetworks::_connection_owner = false;

void LocalNetworks::useConnection (DBConn * conn)
{
  if (_conn)
    {
      DEBUG (DEBUG_HOST, "[" << __FUNCTION__ << "] Already using " << _conn);
      return;
    }
  if (conn)
    {
      DEBUG (DEBUG_HOST, "[" << __FUNCTION__ << "] Using external connection " << conn);
      _conn = conn;
      _connection_owner = false;
    }
}

bool LocalNetworks::load ()
{
  if (_loaded)
    return true;

  return reload();
}

bool LocalNetworks::reload ()
{
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
      DEBUG (DEBUG_HOST, "[" << __FUNCTION__ << "] Using new connection " << _conn);
    }
    else
    {
      DEBUG (DEBUG_HOST, "[" << __FUNCTION__ << "] Using old connection " << _conn);
    }

  char s_url[1024];
  Net *net;
  DBQuery *query = NULL;

  string sqlcmd = "select s_url from url u, redirect r where u.s_redirect_id=r.s_redirect_id and r.s_type='local'";

  if (_conn->getEngine() == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      query = new ODBCQuery ((ODBCConn*)_conn);
      #endif
    }
  else if (_conn->getEngine() == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      query = new MYSQLQuery ((MYSQLConn*)_conn);
      #endif
    }
  else
    return false;

  if (!query->bindCol (1, DBQuery::T_CHAR, s_url, sizeof (s_url)))
    {
      delete query;
      return false;
    }
  if (!query->sendQueryDirect (sqlcmd.c_str()))
    {
      delete query;
      return false;
    }
  _nets.clear();
  while (query->fetch ())
    {
      net = Net::fromString (s_url);
      _nets.push_back (net);
    }

  delete query;

  _loaded = true;

  return true;
}

void LocalNetworks::destroy()
{
  if (_connection_owner && _conn)
    {
      DEBUG (DEBUG_HOST, "[" << __FUNCTION__ << "] Destroy connection " << _conn);
      delete _conn;
      _conn = NULL;
    }
  else
    {
      DEBUG (DEBUG_HOST, "[" << __FUNCTION__ << "] Not owner for connection " << _conn);
    }
}

bool LocalNetworks::isLocalHost (const string & host)
{
  vector < Net * >::iterator it;
  string addr;

  load ();

  DEBUG (DEBUG_HOST, "[" << __FUNCTION__ << "] " << host);

  for (it = _nets.begin (); it != _nets.end (); it++)
    {
      if ((*it)->hasHost (host))
        {
          return true;
        }
    }
  return false;
}

bool LocalNetworks::isLocalUrl (const string & url)
{
  string addr;
  Url u;

  load ();

  DEBUG (DEBUG_HOST, "[" << __FUNCTION__ << "] " << url);

  u.setUrl (url);
  addr = u.getAddress ();

  return isLocalHost (addr);
}
