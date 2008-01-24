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

#ifdef USE_UNIXODBC
#include "odbcconn.h"
#include "odbcquery.h"
#endif

#ifdef USE_MYSQL
#include "mysqlconn.h"
#include "mysqlquery.h"
#endif

#include "samsusers.h"
#include "samsuser.h"
#include "samsconfig.h"
#include "debug.h"

bool SAMSUsers::_loaded = false;
vector < SAMSUser * > SAMSUsers::_users;
DBConn * SAMSUsers::_conn;
bool SAMSUsers::_connection_owner = false;

void SAMSUsers::useConnection (DBConn * conn)
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

bool SAMSUsers::reload()
{
  SAMSUser *usr;

  long s_user_id;
  int s_group_id;
  int s_shablon_id;
  char s_nick[50];
  char s_domain[50];
  int s_quote;
  long long s_size;
  long long s_hit;
  long s_enabled;
  char s_ip[15];
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
  else
    return false;

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

  string sqlcmd = "select s_user_id, s_group_id, s_shablon_id, s_nick, s_domain, s_quote, s_size, s_hit, s_enabled, s_ip from squiduser";
  if (!query->sendQueryDirect (sqlcmd.c_str()))
    {
      delete query;
      return false;
    }
  _users.clear();
  while (query->fetch ())
    {
      usr = new SAMSUser ();
      usr->setId (s_user_id);
      usr->setGroupId (s_group_id);
      usr->setShablonId (s_shablon_id);
      usr->setNick (s_nick);
      usr->setDomain (s_domain);
      usr->setQuote (s_quote);
      usr->setSize (s_size);
      usr->setHit (s_hit);
      usr->setEnabled (s_enabled);
      usr->setIP (s_ip);

      _users.push_back (usr);
    }

  delete query;

  _loaded = true;

  return true;
}

void SAMSUsers::destroy()
{
  if (_connection_owner && _conn)
    {
      DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] Destroy connection " << _conn);
      delete _conn;
      _conn = NULL;
    }
  else
    {
      DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] Not owner for connection " << _conn);
    }
}

bool SAMSUsers::load ()
{
  if (_loaded)
    return true;

  return reload();
}


SAMSUser *SAMSUsers::findUserByNick (const string & domain, const string & nick)
{
  if (nick == "-")
    return NULL;

  if (!load())
    return NULL;

  SAMSUser *usr = NULL;
  vector < SAMSUser * >::iterator it;
  for (it = _users.begin (); it != _users.end (); it++)
    {
      if (((*it)->getNick () == nick) && ((*it)->getDomain () == domain))
        {
          usr = (*it);
          break;
        }
    }
  return usr;
}

SAMSUser *SAMSUsers::findUserByIP (const IP & ip)
{
  if (!load())
    return NULL;

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
  return usr;
}

bool SAMSUsers::addNewUser(SAMSUser *user)
{
  if (!user)
    return false;

  if (!load())
    return false;

  if (user->getId() > 0)
    {
      DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] " << "User must have id < 0");
      return false;
    }

  DBQuery *query = NULL;

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
  else
    return false;

  basic_stringstream < char >sql_cmd;
  sql_cmd << "insert into squiduser (s_group_id, s_shablon_id, s_nick, s_domain, s_quote, s_size, s_hit, s_enabled, s_ip)";
  sql_cmd << " VALUES (";
  sql_cmd << user->getGroupId();
  sql_cmd << "," << user->getShablonId();
  sql_cmd << ",'" << user->getNick() << "'";
  sql_cmd << ",'" << user->getDomain()<< "'";
  sql_cmd << "," << user->getQuote();
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

  user->setId (s_user_id);

  _users.push_back (user);

  basic_stringstream < char >mess;

  mess << "User " << user->getNick() << " created.";

  INFO (mess.str ());
  Logger::addLog(Logger::LK_USER, mess.str());

  return true;
}
