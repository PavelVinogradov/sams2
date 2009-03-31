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
#include <limits.h>

#include "config.h"

#include "dbconn.h"
#include "dbquery.h"
#include "samsuserlist.h"
#include "samsuser.h"
#include "samsconfig.h"
#include "proxy.h"
#include "debug.h"

bool SAMSUserList::_loaded = false;
vector < SAMSUser * > SAMSUserList::_users;
DBConn * SAMSUserList::_conn;
bool SAMSUserList::_connection_owner = false;

void SAMSUserList::useConnection (DBConn * conn)
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

bool SAMSUserList::reload()
{
  SAMSUser *usr;

  long s_user_id;
  long s_group_id;
  long s_shablon_id;
  long s_shablon_id2;
  char s_nick[55];
  char s_domain[55];
  long s_quote;
  long long s_size;
  long long s_hit;
  long s_enabled;
  char s_ip[20];
  char s_passwd[25];
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

  if (query == NULL)
    {
      ERROR("Unable to create query.");
      return false;
    }

  if (!query->bindCol (1, DBQuery::T_LONG, &s_user_id, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (2, DBQuery::T_LONG, &s_group_id, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (3, DBQuery::T_LONG, &s_shablon_id, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (4, DBQuery::T_CHAR, s_nick, sizeof(s_nick)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (5, DBQuery::T_CHAR, s_domain, sizeof(s_domain)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (6, DBQuery::T_LONG, &s_quote, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (7, DBQuery::T_LONGLONG, &s_size, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (8, DBQuery::T_LONGLONG, &s_hit, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (9, DBQuery::T_LONG, &s_enabled, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (10, DBQuery::T_CHAR, s_ip, sizeof(s_ip)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (11, DBQuery::T_CHAR, s_passwd, sizeof(s_passwd)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (12, DBQuery::T_LONG, &s_shablon_id2, 0))
    {
      delete query;
      return false;
    }

  //string sqlcmd = "select s_user_id, s_group_id, s_shablon_id, s_nick, s_domain, s_quote, s_size, s_hit, s_enabled, s_ip, s_passwd from squiduser";
  string sqlcmd = "select a.s_user_id, a.s_group_id, a.s_shablon_id, a.s_nick, a.s_domain, a.s_quote, a.s_size, a.s_hit, a.s_enabled, a.s_ip, a.s_passwd, b.s_shablon_id2 \
                   from squiduser a, shablon b where a.s_shablon_id=b.s_shablon_id";
  if (!query->sendQueryDirect (sqlcmd.c_str()))
    {
      delete query;
      return false;
    }
  _users.clear();

  // Используется только для предотвращения утечки памяти
  string s_tmp;
  bool usedomain = Proxy::useDomain ();
  while (query->fetch ())
    {
      usr = new SAMSUser ();
      usr->setId (s_user_id);
      usr->setGroupId (s_group_id);
      usr->setActiveTemplateId (s_shablon_id);
      if (s_shablon_id2 == LONG_MAX)
        usr->setLimitedTemplateId (-1);
      else
        usr->setLimitedTemplateId (s_shablon_id2);
      s_tmp = s_nick;
      usr->setNick (s_tmp);
      if (usedomain)
        {
          s_tmp = s_domain;
          usr->setDomain (s_tmp);
        }
      usr->setQuote (s_quote);
      usr->setSize (s_size);
      usr->setHit (s_hit);
      usr->setEnabled (s_enabled);
      s_tmp = s_ip;
      usr->setIP (s_tmp);
      s_tmp = s_passwd;
      usr->setPassword (s_tmp);

      _users.push_back (usr);
    }

  delete query;

  _loaded = true;

  return true;
}

void SAMSUserList::destroy()
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

  vector < SAMSUser * >::iterator it;
  for (it = _users.begin (); it != _users.end (); it++)
    {
      delete *it;
    }
  _users.clear();
}

bool SAMSUserList::load ()
{
  if (_loaded)
    return true;

  return reload();
}


SAMSUser *SAMSUserList::findUserByNick (const string & domain, const string & nick)
{
  if (nick == "-")
    return NULL;

  if (!load())
    return NULL;

  DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << domain << ", " << nick << ")]");

  bool usedomain = Proxy::useDomain ();
  SAMSUser *usr = NULL;
  vector < SAMSUser * >::iterator it;
  for (it = _users.begin (); it != _users.end (); it++)
    {
      if ((*it)->getNick () == nick)
        {
          if (usedomain && ((*it)->getDomain () != domain))
            continue;
          usr = (*it);
          break;
        }
    }
  if (usr)
    {
      DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << domain << ", " << nick << ")] = " << *usr);
    }
  else
    {
      DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << domain << ", " << nick << ")] = NULL");
    }
  return usr;
}

SAMSUser *SAMSUserList::findUserByIP (const IP & ip)
{
  if (!load ())
    return NULL;

  DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << ip << ")]");

  SAMSUser *usr = NULL;
  vector < SAMSUser * >::iterator it;
  for (it = _users.begin (); it != _users.end (); it++)
    {
      if ((*it)->getIP ().equal (ip))
        {
          usr = (*it);
          break;
        }
    }
  if (usr)
    {
      DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << ip << ")] = " << *usr);
    }
  else
    {
      DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << ip << ")] = NULL");
    }
  return usr;
}

void SAMSUserList::getUsersByTemplate (long id, vector<SAMSUser *> &lst)
{
  load ();

  DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << id << ")]");

  lst.clear();
  vector < SAMSUser * >::iterator it;
  for (it = _users.begin (); it != _users.end (); it++)
    {
      if ((*it)->getCurrentTemplateId () == id)
        {
          DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << *(*it));
          lst.push_back ( (*it) );
        }
    }
  DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << id << ")] Qty users in template: " << lst.size ());
//  sort(lst.begin(), lst.end());
}

bool SAMSUserList::addNewUser(SAMSUser *user)
{
  if (!user)
    return false;

  if (!load())
    return false;

  DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << user << ")]");

  if (user->getId() > 0)
    {
      WARNING ("User must have id < 0, but it is " << user->getId());
      return false;
    }

  DBQuery *query = NULL;
  _conn->newQuery(query);

  if (query == NULL)
    {
      ERROR("Unable to create query.");
      return false;
    }

  basic_stringstream < char >sql_cmd;
  sql_cmd << "insert into squiduser (s_group_id, s_shablon_id, s_nick, s_domain, s_quote, s_size, s_hit, s_enabled, s_ip)";
  sql_cmd << " VALUES (";
  sql_cmd << user->getGroupId();
  sql_cmd << "," << user->getCurrentTemplateId();
  sql_cmd << ",'" << user->getNick() << "'";
  sql_cmd << ",'" << user->getDomain()<< "'";
  sql_cmd << "," << user->getRealQuote();
  sql_cmd << "," << user->getSize();
  sql_cmd << "," << user->getHit();
  sql_cmd << "," << (int)user->getEnabled();
  sql_cmd << ",'" << user->getIP() << "'";
  sql_cmd << ")";

  if (!query->sendQueryDirect( sql_cmd.str()))
    {
      delete query;
      return false;
    }

  DEBUG (DEBUG8, "[" << __FUNCTION__ << "] " << "User " << *user << " inserted into db");

  long s_user_id;
  if (!query->bindCol(1, DBQuery::T_LONG, &s_user_id, 0))
    {
      delete query;
      return false;
    }

  sql_cmd.str("");
  sql_cmd << "select s_user_id from squiduser where";
  sql_cmd << " s_nick='" << user->getNick() << "'";
  sql_cmd << " and s_domain='" << user->getDomain() << "'";
  sql_cmd << " and s_ip='" << user->getIP() << "'";
  if (!query->sendQueryDirect( sql_cmd.str()))
    {
      delete query;
      return false;
    }

  if (!query->fetch())
    {
      delete query;
      return false;
    }

  DEBUG (DEBUG8, "[" << __FUNCTION__ << "] " << "User " << *user << " got id " << s_user_id);

  user->setId (s_user_id);

  _users.push_back (user);

  basic_stringstream < char >mess;

  mess << "User " << *user << " created.";

  INFO (mess.str ());
  Logger::addLog(Logger::LK_USER, mess.str());

  delete query;

  return true;
}

long SAMSUserList::activeUsersInTemplate (long template_id)
{
  load ();
  long cnt = 0;
  vector < SAMSUser * >::iterator it;
  for (it = _users.begin (); it != _users.end (); it++)
    {
      if ((*it)->getCurrentTemplateId () == template_id)
        {
          if ((*it)->getEnabled () == SAMSUser::STAT_ACTIVE || (*it)->getEnabled () == SAMSUser::STAT_LIMITED)
            cnt++;
        }
    }
  DEBUG (DEBUG8, "[" << __FUNCTION__ << "("<<template_id<<")] Active users in template: " << cnt);
  return cnt;
}

