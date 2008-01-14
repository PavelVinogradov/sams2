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

LocalNetworks::LocalNetworks ()
{
}


LocalNetworks::~LocalNetworks ()
{
}

bool LocalNetworks::load (DBConn * conn)
{
  char s_url[1024];
  Net *net;
  DBQuery *query = NULL;

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");

  string sqlcmd = "select s_url from url u, redirect r where u.s_redirect_id=r.s_redirect_id and r.s_type='local'";

  if (conn->getEngine() == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      query = new ODBCQuery ((ODBCConn*)conn);
      #endif
    }
  else if (conn->getEngine() == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      query = new MYSQLQuery ((MYSQLConn*)conn);
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
  while (query->fetch ())
    {
      net = Net::fromString (s_url);
      _nets.push_back (net);
    }

  delete query;

  return true;
}

bool LocalNetworks::isLocalHost (const string & host)
{
  vector < Net * >::iterator it;
  string addr;

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << host);

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

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << url);

  u.setUrl (url);
  addr = u.getAddress ();

  return isLocalHost (addr);
}
