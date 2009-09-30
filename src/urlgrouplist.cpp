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

#include "dbconn.h"
#include "dbquery.h"
#include "urlgrouplist.h"
#include "urlgroup.h"
#include "samsconfig.h"
#include "debug.h"

bool UrlGroupList::_loaded = false;
vector < UrlGroup * > UrlGroupList::_groups;
DBConn * UrlGroupList::_conn;
bool UrlGroupList::_connection_owner = false;

void UrlGroupList::useConnection (DBConn * conn)
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

bool UrlGroupList::reload()
{
  DEBUG(DEBUG8, "[" << __FUNCTION__ << "] ");

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

  _conn->newQuery(query);

  if (query == NULL)
    {
      ERROR("Unable to create query.");
      return false;
    }

  UrlGroup *grp = NULL;
  UrlGroup::accessType access_type;

  long s_redirect_id;
  char s_type[30];
  char s_url[1024];
  char s_dest[130];

  if (!query->bindCol (1, DBQuery::T_LONG, &s_redirect_id, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (2, DBQuery::T_CHAR, s_type, sizeof(s_type)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (3, DBQuery::T_CHAR, s_url, sizeof(s_url)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (4, DBQuery::T_CHAR, s_dest, sizeof(s_dest)))
    {
      delete query;
      return false;
    }

  // Локальные сети анализируются в специальном классе LocalNetworks
  string sqlcmd = "select a.s_redirect_id, a.s_type, b.s_url, a.s_dest from redirect a, url b where a.s_redirect_id=b.s_redirect_id and a.s_type!='local' order by a.s_type, a.s_redirect_id";
  if (!query->sendQueryDirect (sqlcmd.c_str()))
    {
      delete query;
      return false;
    }
  _groups.clear();

  string s_tmp;

  while (query->fetch ())
    {
      s_tmp = s_type;
      if (!grp || grp->getId () != s_redirect_id)
        {
          if (s_tmp == "allow")
            access_type = UrlGroup::ACC_ALLOW;
          else if (s_tmp == "denied" || s_tmp == "deny")
            access_type = UrlGroup::ACC_DENY;
          else if (s_tmp == "regex")
            access_type = UrlGroup::ACC_REGEXP;
          else if (s_tmp == "redir")
            access_type = UrlGroup::ACC_REDIR;
          else if (s_tmp == "replace")
            access_type = UrlGroup::ACC_REPLACE;
          else if (s_tmp == "files")
            access_type = UrlGroup::ACC_FILEEXT;
          else
            {
              WARNING("Unsupported url group type: " << s_tmp);
              continue;
            }
          grp = new UrlGroup (s_redirect_id, access_type);
          grp->setReplacement (s_dest);
          _groups.push_back (grp);
        }
      grp->addUrl (s_url);

      DEBUG (DEBUG9, "[" << __FUNCTION__ << "] Found url rule: " <<
        "id=" << s_redirect_id << " " <<
        "type=" << s_tmp << " " <<
        "url=" << s_url << " " <<
        "replacement=" << s_dest
        );
    }

  delete query;

  _loaded = true;

  return true;
}

void UrlGroupList::destroy()
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

  vector < UrlGroup * >::iterator it;
  for (it = _groups.begin (); it != _groups.end (); it++)
    {
      delete *it;
    }
  _groups.clear();
}

vector < UrlGroup * > UrlGroupList::getAllGroups ()
{
  return _groups;
}

UrlGroup* UrlGroupList::getUrlGroup (long id)
{
  DEBUG(DEBUG8, "[" << __FUNCTION__ << "(" << id << ")]");
  load();

  UrlGroup *grp = NULL;
  vector < UrlGroup * >::const_iterator it;
  for (it = _groups.begin (); it != _groups.end (); it++)
    {
      if ( (*it)->getId () == id)
        {
          grp = (*it);
          break;
        }
    }
  return grp;
}

/*
string UrlGroupList::modifyUrl (const string &url)
{
  DEBUG(DEBUG8, "[" << __FUNCTION__ << "(" << url << ")]");

  string res = "";
  vector < UrlGroup * >::const_iterator it = _groups.begin ();

  while ( (it != _groups.end ()) && res.empty () )
    res = (*it++)->modifyUrl (url);

  return res;
}
*/

bool UrlGroupList::load ()
{
  if (_loaded)
    return true;

  return reload();
}

