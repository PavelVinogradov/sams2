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

  long s_url_id;
  char s_url[1024];
  Net *net;
  DBQuery *query = NULL;

  string sqlcmd = "select s_url_id, s_url from url u, redirect r where u.s_redirect_id=r.s_redirect_id and r.s_type='local'";

  _conn->newQuery (query);
  if (!query)
    {
      ERROR("Unable to create query.");
      return false;
    }

  if (!query->bindCol (1, DBQuery::T_LONG, &s_url_id, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (2, DBQuery::T_CHAR, s_url, sizeof (s_url)))
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
  string str_tmp;
  while (query->fetch ())
    {
      str_tmp = s_url;
      net = Net::fromString (str_tmp);
      net->setId (s_url_id);
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

  vector < Net * >::iterator it;
  for (it = _nets.begin (); it != _nets.end (); it++)
    {
      delete (*it);
    }
  _nets.clear ();
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

vector < Net * > LocalNetworks::getAllNetworks ()
{
  return _nets;
}
