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
#include "grouplist.h"
#include "samsconfig.h"
#include "debug.h"

bool GroupList::_loaded = false;
DBConn * GroupList::_conn;
bool GroupList::_connection_owner = false;
map<string, int> GroupList::_list;


void GroupList::useConnection (DBConn * conn)
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

bool GroupList::reload()
{
  DEBUG (DEBUG_GROUP, "[" << __FUNCTION__ << "] ");

  basic_stringstream < char >sqlcmd;
  DBQuery *query = NULL;

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

  _conn->newQuery (query);

  if (!query)
    {
      ERROR("Unable to create query.");
      return false;
    }

  long s_grp_id;
  char s_name[50];

  if (!query->bindCol (1, DBQuery::T_LONG,  &s_grp_id, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (2, DBQuery::T_CHAR,  s_name, sizeof(s_name)))
    {
      delete query;
      return false;
    }

  if (!query->sendQueryDirect ("select s_group_id, s_name from sgroup"))
    {
      delete query;
      return false;
    }

  _list.clear();
  while (query->fetch())
    {
      _list[s_name] = s_grp_id;
    }
  delete query;
  _loaded = true;

  return true;
}

void GroupList::destroy()
{
  if (_connection_owner && _conn)
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Destroy connection " << _conn);
      delete _conn;
      _conn = NULL;
    }
  else
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Not owner for connection " << _conn);
    }
}

int GroupList::getGroupId(const string & name)
{
  load();

  map < string, int >::iterator it = _list.find (name);
  if (it == _list.end ())
    {
      DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << name << " not found");
      return -1;
    }
  DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << name << "=" << (*it).second);
  return (*it).second;
}

bool GroupList::load ()
{
  if (_loaded)
    return true;

  return reload();
}

