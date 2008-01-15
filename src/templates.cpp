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

#include "templates.h"
#include "template.h"
#include "debug.h"
#include "samsconfig.h"

bool Templates::_loaded = false;
map<string, Template*> Templates::_list;

bool Templates::load()
{
  if (_loaded)
    return true;

  return reload();
}

bool Templates::reload()
{
  DEBUG (DEBUG_DB, "[" << __FUNCTION__ << "] ");

  basic_stringstream < char >sqlcmd;
  DBConn *conn = NULL;
  DBQuery *query = NULL;

  _list.clear();

  DBConn::DBEngine engine = SamsConfig::getEngine();

  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      conn = new ODBCConn();
      if (!conn->connect ())
        {
          delete conn;
          return false;
        }
      query = new ODBCQuery((ODBCConn*)conn);
      #else
      return false;
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      conn = new MYSQLConn();
      if (!conn->connect ())
        {
          delete conn;
          return false;
        }
      query = new MYSQLQuery((MYSQLConn*)conn);
      #else
      return false;
      #endif
    }
  else
    return false;

  long s_tpl_id;
  char s_name[25];
  char s_auth[5];
  long s_quote;

  if (!query->bindCol (1, DBQuery::T_LONG,  &s_tpl_id, 0))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->bindCol (2, DBQuery::T_CHAR,  s_name, sizeof(s_name)))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->bindCol (3, DBQuery::T_CHAR,  s_auth, sizeof(s_auth)))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->bindCol (4, DBQuery::T_LONG,  &s_quote, 0))
    {
      delete query;
      delete conn;
      return false;
    }

  if (!query->sendQueryDirect ("select s_shablon_id, s_name, s_auth, s_quote from shablon"))
    {
      delete query;
      delete conn;
      return false;
    }

  Template *tpl = NULL;
  while (query->fetch())
    {
      tpl = new Template(s_tpl_id, s_name);
      tpl->setAuth (s_auth);
      tpl->setQuote (s_quote);
      _list[s_name] = tpl;
    }
  delete query;
  delete conn;
  _loaded = true;

  return true;
}

Template * Templates::getTemplate(const string & name)
{
  load();

  map < string, Template* >::iterator it = _list.find (name);
  if (it == _list.end ())
    {
      DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << name << " not found");
      return NULL;
    }
  DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << name << "=" << (*it).second);
  return (*it).second;
}
