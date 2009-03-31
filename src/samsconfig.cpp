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

#include <fstream>

#include "configmake.h"
#include "config.h"

#ifdef USE_UNIXODBC
#include "odbcconn.h"
#include "odbcquery.h"
#endif

#ifdef USE_MYSQL
#include "mysqlconn.h"
#include "mysqlquery.h"
#endif

#ifdef USE_PQ
#include "pgconn.h"
#include "pgquery.h"
#endif

#include "samsconfig.h"
#include "debug.h"
#include "tools.h"

string SamsConfig::_config_file = "";
bool SamsConfig::_file_loaded = false;
bool SamsConfig::_db_loaded = false;
bool SamsConfig::_internal = false;
DBConn::DBEngine SamsConfig::_engine;
map < string, string > SamsConfig::_attributes;

void SamsConfig::useFile (const string &fname)
{
  _config_file = fname;
}

bool SamsConfig::load()
{
  if (_file_loaded && _db_loaded)
    return true;

  if (_internal)
    return true;

  return reload();
}

bool SamsConfig::reload()
{
  if (!readFile())
    return false;

  if (!readDB())
    return false;

  return true;
}

void SamsConfig::destroy ()
{
  _attributes.clear ();
}

bool SamsConfig::readFile ()
{
  fstream in;
  string line;
  string name;
  string value;
  int signpos;

  DEBUG (DEBUG4, "[" << __FUNCTION__ << "] ");

  INFO ("Reading config file " << _config_file);

  in.open (_config_file.c_str (), ios_base::in);
  if (!in.is_open ())
    {
      ERROR ("Failed to open file " << _config_file);
      return false;
    }

  while (in.good ())
    {
      getline (in, line);
      line = StripComments (line);

      if (line.empty ())
        continue;

      signpos = line.find_first_of ('=');
      if (signpos < 0)
        continue;

      name = line.substr (0, signpos);
      value = line.substr (signpos + 1, line.size () - signpos);

      name = TrimSpaces (name);
      value = TrimSpaces (value);
      setString (name, value);
    }

  in.close ();

  _file_loaded = true;

  map < string, string >::iterator it = _attributes.find (defDBENGINE);
  if (it == _attributes.end ())
    {
      ERROR ("Unspecified DB engine. Check " << defDBENGINE << " in config file.");
      return false;
    }

  string dbengine = (*it).second;

  _engine = DBConn::DB_UNKNOWN;

  if (dbengine == "MySQL")
    {
      #ifndef USE_MYSQL
      ERROR ("MySQL engine is not enabled. Reconfigure package to enable it or change engine.");
      return false;
      #else
      DEBUG (DEBUG3, "[" << __FUNCTION__ << "] " << "using MySQL engine.");
      _engine = DBConn::DB_MYSQL;
      #endif
    }
  else if (dbengine == "unixODBC")
    {
      #ifndef USE_UNIXODBC
      ERROR ("unixODBC engine is not enabled. Reconfigure package to enable it or change engine.");
      return false;
      #else
      DEBUG (DEBUG3, "[" << __FUNCTION__ << "] " << "using unixODBC engine.");
      _engine = DBConn::DB_UODBC;
      #endif
    }
  else if (dbengine == "PostgreSQL")
    {
      #ifndef USE_PQ
      ERROR ("PostgreSQL engine is not enabled. Reconfigure package to enable it or change engine.");
      return false;
      #else
      DEBUG (DEBUG3, "[" << __FUNCTION__ << "] " << "using PostgreSQL engine.");
      _engine = DBConn::DB_PGSQL;
      #endif
    }
  else
    {
      ERROR ("Unsupported DB engine " << dbengine);
      return false;
    }

  return true;
}

bool SamsConfig::readDB ()
{
  int err;

  DEBUG (DEBUG4, "[" << __FUNCTION__ << "] ");

  _internal = true;

  int proxyid = getInt (defPROXYID, err);

  if (err != ERR_OK)
    {
      ERROR ("No proxyid defined. Check " << defPROXYID << " in config file.");
      _internal = false;
      return false;
    }

  DBConn *conn = SamsConfig::newConnection ();;

  if (!conn)
    {
      _internal = false;
      ERROR ("Unable to create connection.");
      return false;
    }

  DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Using connection " << conn);

  if (!conn->connect ())
    {
      delete conn;
      _internal = false;
      return false;
    }

  DBQuery *query = NULL;
  conn->newQuery (query);
  if (!query)
    {
      ERROR("Unable to create query.");
      delete conn;
      _internal = false;
      return false;
    }


  basic_stringstream < char >s;

  long s_sleep;
  long s_parser_time;

  s << "select s_sleep, s_parser_time from proxy where s_proxy_id=" << proxyid;
  //s << "select s_parser_time, s_sleep from proxy where s_proxy_id=" << proxyid;

  if (!query->bindCol (1, DBQuery::T_LONG, &s_sleep, 0))
    {
      delete query;
      delete conn;
      _internal = false;
      return false;
    }
  if (!query->bindCol (2, DBQuery::T_LONG, &s_parser_time, 0))
    {
      delete query;
      delete conn;
      _internal = false;
      return false;
    }

  if (!query->sendQueryDirect (s.str ()))
    {
      delete query;
      delete conn;
      _internal = false;
      return false;
    }

  if (!query->fetch ())
    {
      WARNING ("No settings for proxy " << proxyid << ". Somethig wrong?");
      delete query;
      delete conn;
      _internal = false;
      return false;
    }

  setInt (defSLEEPTIME, s_sleep);
  setInt (defDAEMONSTEP, s_parser_time);

  delete query;

  DBQuery *query2 = NULL;
  conn->newQuery (query2);
  if (!query2)
    {
      ERROR("Unable to create query.");
      delete conn;
      _internal = false;
      return false;
    }

  basic_stringstream < char >s2;

  char s_version[10];
  s2 << "select s_version from websettings";

  if (!query2->bindCol (1, DBQuery::T_CHAR, s_version, sizeof (s_version)))
    {
      delete query2;
      delete conn;
      _internal = false;
      return false;
    }
  if (!query2->sendQueryDirect (s2.str()) )
    {
      delete query2;
      delete conn;
      _internal = false;
      return false;
    }
  if (!query2->fetch ())
    {
      delete query2;
      delete conn;
      _internal = false;
      return false;
    }

  setString (defDBVERSION, TrimSpaces (s_version) );

  delete query2;

  delete conn;
  _internal = false;
  _db_loaded = true;

  return true;
}

string SamsConfig::getString (const string & attrname, int &err)
{
  load();

  map < string, string >::iterator it = _attributes.find (attrname);
  if (it == _attributes.end ())
    {
      err = ATTR_NOT_FOUND;
      DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << attrname << ")] = not found");
      return "";
    }

  if (attrname == defDBPASSWORD)
    {
      DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << attrname << ")] = *hidden*");
    }
  else
    {
      DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << attrname << ")] = " << (*it).second);
    }

  err = ERR_OK;
  return (*it).second;
}

int SamsConfig::getInt (const string & attrname, int &err)
{
  int res;
  string val;

  val = getString (attrname, err);
  if (err == ATTR_NOT_FOUND)
    return 0;
  if (sscanf (val.c_str (), "%d", &res) != 1)
    {
      err = ATTR_NOT_PARSED;
      DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << attrname << " not parsed");
      return 0;
    }
  return res;
}

double SamsConfig::getDouble (const string & attrname, int &err)
{
  double res;
  string val;

  val = getString (attrname, err);
  if (err == ATTR_NOT_FOUND)
    return 0;
  if (sscanf (val.c_str (), "%lf", &res) != 1)
    {
      err = ATTR_NOT_PARSED;
      DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << attrname << " not parsed");
      return 0;
    }
  return res;
}

bool SamsConfig::getBool (const string & attrname, int &err)
{
  int res;
  string val;

  val = getString (attrname, err);
  if (err == ATTR_NOT_FOUND)
    return 0;
  if (sscanf (val.c_str (), "%d", &res) != 1)
    {
      err = ATTR_NOT_PARSED;
      DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << attrname << " not parsed");
      return false;
    }
  if (res == 0)
    return false;
  return true;
}

void SamsConfig::setString (const string & attrname, const string & value)
{
  if (attrname == defDBPASSWORD)
    {
      DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << attrname << ", *hidden*)]");
    }
  else
    {
      DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << attrname << ", " << value << ")]");
    }

  _attributes[attrname] = value;
}

void SamsConfig::setInt (const string & attrname, const int &value)
{
  char buf[64];
  sprintf (&buf[0], "%d", value);
  DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << attrname << "=" << buf);
  _attributes[attrname] = buf;
}

void SamsConfig::setDouble (const string & attrname, const double &value)
{
  char buf[64];
  sprintf (&buf[0], "%lf", value);
  DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << attrname << "=" << buf);
  _attributes[attrname] = buf;
}

void SamsConfig::setBool (const string attrname, const bool value)
{
  char buf[64];
  sprintf (&buf[0], "%d", (value == true) ? 1 : 0);
  DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << attrname << "=" << buf);
  _attributes[attrname] = buf;
}

DBConn * SamsConfig::newConnection ()
{
  DBConn * conn = NULL;

  if (_engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      conn = new ODBCConn();
      #endif
    }
  else if (_engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      conn = new MYSQLConn();
      #endif
    }
  else if (_engine == DBConn::DB_PGSQL)
    {
      #ifdef USE_PQ
      conn = new PgConn();
      #endif
    }

  return conn;
}

DBConn::DBEngine SamsConfig::getEngine()
{
  load();

  return _engine;
}
