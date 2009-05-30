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

#include "configmake.h"
#include "config.h"

#if HAVE_DLFCN_H
#include <dlfcn.h>
#endif

#include <stdlib.h>
#include <string.h>
#include "pluginlist.h"
#include "samsconfig.h"
#include "proxy.h"
#include "tools.h"
#include "dbconn.h"
#include "dbquery.h"
#include "debug.h"

bool PluginList::_loaded = false;
DBConn *PluginList::_conn = NULL;
bool PluginList::_connection_owner = false;
vector < Plugin * > PluginList::_plugins;

bool PluginList::reload ()
{
#if USE_DL
  vector<string> liblist;
  vector<string>::iterator it;

  destroy ();

  liblist = fileList (PKGLIBDIR, "*.so");

  for (it = liblist.begin (); it != liblist.end (); it++)
    {
      loadPlugin (*it);
    }

  _loaded = true;
#endif

  return true;
}

void PluginList::useConnection (DBConn * conn)
{
#if USE_DL
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
#endif
}

void PluginList::destroy ()
{
#if USE_DL
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

  vector<Plugin*>::iterator it;
  for (it = _plugins.begin (); it != _plugins.end (); it++)
    {
      dlclose ((*it)->handle);
      free (*it);
    }
  _plugins.clear();
#endif
}

bool PluginList::updateInfo ()
{
#if USE_DL
  DEBUG (DEBUG6, "[" << __FUNCTION__ << "] ");

  if (!load ())
    return false;

  int err;
  bool store_to_db;

  string str_db_ver = SamsConfig::getString (defDBVERSION, err);
  if (err != ERR_OK)
    {
      ERROR ("Unable to get database version.");
      return false;
    }

  string str_pkg_ver = VERSION;

  if (str_db_ver.compare (0, 5, "1.9.9") == 0)
    {
      ERROR ("DB doesn't support sysinfo plugins.");
      store_to_db = false;
    }
  else if (str_db_ver.compare (2, 3, "9.9") == 0)
    {
      DEBUG (DEBUG3, "[" << __FUNCTION__ << "] " << "Internal database version.");
      store_to_db = true;
    }
  else if (str_db_ver.compare (0, 3, str_pkg_ver, 0, 3) != 0)
    {
      DEBUG (DEBUG3, "[" << __FUNCTION__ << "] " << "Database version accepted.");
      store_to_db = true;
    }
  else if (str_db_ver.compare (0, 5, str_pkg_ver, 0, 5) != 0)
    {
      ERROR ("Incompatible database version. Expected " << str_pkg_ver.substr (0, 5) << ", but got " << str_db_ver.substr (0, 5));
      store_to_db = false;
    }
  else
    {
      DEBUG (DEBUG3, "[" << __FUNCTION__ << "] " << "Database version ok.");
      store_to_db = true;
    }


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

  DBQuery *querySelect = NULL;
  DBQuery *queryUpdate = NULL;
  DBQuery *queryInsert = NULL;

  char sys_name[50];
  char sys_version[10];
  char sys_author[30];
  char sys_info[1024];
  char sys_date[20];
  long sys_status;

  int proxyid = Proxy::getId ();

  if (store_to_db)
    {
      _conn->newQuery (querySelect);
      if (!querySelect)
        {
          ERROR("Unable to create query.");
          return false;
        }
      _conn->newQuery (queryUpdate);
      if (!queryUpdate)
        {
          ERROR("Unable to create query.");
          delete querySelect;
          return false;
        }
      _conn->newQuery (queryInsert);
      if (!queryInsert)
        {
          ERROR("Unable to create query.");
          delete querySelect;
          delete queryUpdate;
          return false;
        }

      if (!querySelect->bindCol (1, DBQuery::T_LONG, &sys_status, 0))
        {
          delete querySelect;
          delete queryUpdate;
          delete queryInsert;
          return false;
        }


      basic_stringstream < char > sysinfo_update_cmd;
      sysinfo_update_cmd << "update sysinfo set s_info=?, s_date=? where s_name=? and s_version=? and s_proxy_id=" << proxyid;
      if (!queryUpdate->prepareQuery (sysinfo_update_cmd.str ()))
        {
          delete queryUpdate;
          return false;
        }
      if (!queryUpdate->bindParam (1, DBQuery::T_CHAR, &sys_info, sizeof(sys_info)))
        {
          delete queryUpdate;
          return false;
        }
      if (!queryUpdate->bindParam (2, DBQuery::T_CHAR, &sys_date, sizeof(sys_date)))
        {
          delete queryUpdate;
          return false;
        }
      if (!queryUpdate->bindParam (3, DBQuery::T_CHAR, &sys_name, sizeof(sys_name)))
        {
          delete queryUpdate;
          return false;
        }
      if (!queryUpdate->bindParam (4, DBQuery::T_CHAR, &sys_version, sizeof(sys_version)))
        {
          delete queryUpdate;
          return false;
        }


      basic_stringstream < char > sysinfo_insert_cmd;
      sysinfo_insert_cmd << "insert into sysinfo (s_proxy_id, s_name, s_version, s_author, s_info, s_date, s_status)";
      sysinfo_insert_cmd << " values (" << proxyid << ", ?, ?, ?, ?, ?, 0)";
      if (!queryInsert->prepareQuery (sysinfo_insert_cmd.str ()))
        {
          return false;
        }
      if (!queryInsert->bindParam (1, DBQuery::T_CHAR, sys_name, sizeof (sys_name)))
        {
          return false;
        }
      if (!queryInsert->bindParam (2, DBQuery::T_CHAR, sys_version, sizeof (sys_version)))
        {
          return false;
        }
      if (!queryInsert->bindParam (3, DBQuery::T_CHAR, sys_author, sizeof (sys_author)))
        {
          return false;
        }
      if (!queryInsert->bindParam (4, DBQuery::T_CHAR, sys_info, sizeof (sys_info)))
        {
          return false;
        }
      if (!queryInsert->bindParam (5, DBQuery::T_CHAR, sys_date, sizeof (sys_date)))
        {
          return false;
        }
    }

  string plug_name;
  string plug_version;
  string plug_author;
  string plug_data;
  basic_stringstream < char > sysinfo_select_cmd;
  struct tm *date_time;
  time_t now;

  vector<Plugin*>::iterator it;
  for (it = _plugins.begin (); it != _plugins.end (); it++)
    {

      plug_name = (*((*it)->getName))();
      plug_version = (*((*it)->getVersion))();
      plug_author = (*((*it)->getAuthor))();
      now = time (NULL);
      date_time = localtime(&now);
      strftime (sys_date, sizeof (sys_date), "%Y-%m-%d %H:%M:%S", date_time);

      if (store_to_db)
        {
          sysinfo_select_cmd.str("");
          sysinfo_select_cmd << "select s_status from sysinfo where s_proxy_id=" << proxyid;
          sysinfo_select_cmd << " and s_name='" << plug_name << "'";
          sysinfo_select_cmd << " and s_version='" << plug_version << "'";
          if (!querySelect->sendQueryDirect (sysinfo_select_cmd.str ()))
            continue;

          strcpy (sys_name, plug_name.c_str());
          strcpy (sys_version, plug_version.c_str());
          strcpy (sys_author, plug_author.c_str());

          if (!querySelect->fetch ()) // такой записи еще нет, нужно добавлять
            {
              queryInsert->sendQuery ();
            }
          else //такая запись существует, нужно обновлять
            {
              if (sys_status == 1) // обновляем если плагин активен
                {
                  plug_data = (*((*it)->getInfo))();
                  strncpy (sys_info, plug_data.c_str(), 1024);
                  if (plug_data.size () >= 1024)
                    sys_info[1023] = 0;
                  queryUpdate->sendQuery ();
                }
            }
        }
    }
#endif // #if USE_DL

  return true;
}

bool PluginList::load ()
{
  if (_loaded)
    return true;

  return reload();
}

bool PluginList::loadPlugin (const string &path)
{
#if USE_DL
  Plugin *pl = NULL;

  const char *error;

  pl = (Plugin*)malloc (sizeof(Plugin));
  pl->handle = dlopen (path.c_str (), RTLD_LAZY);

  if (!pl->handle)
    {
      ERROR (dlerror());
      free(pl);
      return false;
    }

  dlerror();    /* Clear any existing error */
  *(void **) (&(pl->getInfo)) = dlsym(pl->handle, "_Z11informationv");
  if ((error = dlerror()) != NULL)
    {
      ERROR (error);
      dlclose(pl->handle);
      free (pl);
      return false;
    }
  *(void **) (&(pl->getName)) = dlsym(pl->handle, "_Z4namev");
  if ((error = dlerror()) != NULL)
    {
      ERROR (error);
      dlclose(pl->handle);
      free (pl);
      return false;
    }
  *(void **) (&(pl->getVersion)) = dlsym(pl->handle, "_Z7versionv");
  if ((error = dlerror()) != NULL)
    {
      ERROR (error);
      dlclose(pl->handle);
      free (pl);
      return false;
    }
  *(void **) (&(pl->getAuthor)) = dlsym(pl->handle, "_Z6authorv");
  if ((error = dlerror()) != NULL)
    {
    }

  _plugins.push_back (pl);
#endif // #if USE_DL

  return true;
}
