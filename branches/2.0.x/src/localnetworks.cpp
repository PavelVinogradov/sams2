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

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");

  if (conn->getEngine() == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      ODBCQuery queryODBC( (ODBCConn*)conn );
      if (!queryODBC.bindCol (1, SQL_C_CHAR, s_url, sizeof (s_url)))
          return false;
      if (!queryODBC.sendQueryDirect ("select s_url from url u, redirect r where u.s_redirect_id=r.s_redirect_id and r.s_type='local';"))
          return false;
      while (queryODBC.fetch ())
        {
          net = Net::fromString (s_url);
          _nets.push_back (net);
        }
      #else
      return false;
      #endif
    }
  else if (conn->getEngine() == DBConn::DB_MYSQL)
    {
      return false;
    }

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
