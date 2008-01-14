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

#include "config.h"

#ifdef USE_MYSQL
#include "mysqlconn.h"
#include "mysqlquery.h"
#endif

#ifdef USE_UNIXODBC
#include "odbcconn.h"
#include "odbcquery.h"
#endif

#include "dbcleaner.h"
#include "samsconfig.h"
#include "datefilter.h"
#include "userfilter.h"
#include "debug.h"

#include "global.h"

DBCleaner::DBCleaner (int proxyid)
{
  _proxyid = proxyid;
  _date_filter = NULL;
  _user_filter = NULL;
}

DBCleaner::~DBCleaner ()
{
}

void DBCleaner::setUserFilter (UserFilter * filt)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << filt);
  _user_filter = filt;
}

void DBCleaner::setDateFilter (DateFilter * filt)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << filt);
  _date_filter = filt;
}

void DBCleaner::clearCounters ()
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");

  if (config->getEngine() == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      ODBCConn connODBC;
      if (!connODBC.connect ())
        return;
      ODBCQuery queryODBC(&connODBC);
      if (!queryODBC.sendQueryDirect ("update squiduser set s_size=0, s_hit=0"))
        {
          return;
        }
      if (!queryODBC.sendQueryDirect ("update squiduser set s_enabled=1 where s_enabled=0"))
        {
          return;
        }
      #endif
    }
  else if (config->getEngine() == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      MYSQLConn connMYSQL;
      if (!connMYSQL.connect ())
        return;
      MYSQLQuery queryMYSQL(&connMYSQL);
      if (!queryMYSQL.sendQueryDirect ("update squiduser set s_size=0, s_hit=0"))
        {
          return;
        }
      if (!queryMYSQL.sendQueryDirect ("update squiduser set s_enabled=1 where s_enabled=0"))
        {
          return;
        }
      #endif
    }
  else
    return;
}

void DBCleaner::clearCache ()
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");

  basic_stringstream < char >sqlcmd;
  DBConn *conn = NULL;
  DBQuery *query = NULL;

  DBConn::DBEngine engine = config->getEngine();

  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      conn = new ODBCConn();
      if (!conn->connect ())
        {
          delete conn;
          return;
        }
      query = new ODBCQuery((ODBCConn*)conn);
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      conn = new MYSQLConn();
      if (!conn->connect ())
        {
          delete conn;
          return;
        }
      query = new MYSQLQuery((MYSQLConn*)conn);
      #endif
    }
  else
    return;


  if (!query->sendQueryDirect ("update squiduser set s_size=0, s_hit=0"))
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->sendQueryDirect ("update squiduser set s_enabled=1 where s_enabled=0"))
    {
      delete query;
      delete conn;
      return;
    }

  sqlcmd << "delete from squidcache where s_proxy_id=" << _proxyid;
  if (_date_filter != NULL)
    {
      sqlcmd << " and s_date>='" << _date_filter->getStartDateAsString () << "'";
      sqlcmd << " and s_date<='" << _date_filter->getEndDateAsString () << "' ";
    }

  if (!query->sendQueryDirect (sqlcmd.str ()))
    {
      delete query;
      delete conn;
      return;
    }

  sqlcmd.str("");
  sqlcmd << "delete from cachesum where s_proxy_id=" << _proxyid;

  if (!query->sendQueryDirect (sqlcmd.str ()))
    {
      delete query;
      delete conn;
      return;
    }

  sqlcmd.str("");
  sqlcmd << "insert into cachesum select " << _proxyid;
  sqlcmd << ", s_date, s_user, s_domain, sum(s_size), sum(s_hit)";
  sqlcmd << " from squidcache where s_proxy_id=" << _proxyid;
  sqlcmd << " group by s_date, s_domain, s_user";

  query->sendQueryDirect (sqlcmd.str ());
  delete query;
  delete conn;

  return;
}
