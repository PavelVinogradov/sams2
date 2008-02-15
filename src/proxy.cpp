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
#include <vector>
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

#include "proxy.h"
#include "debug.h"
#include "tools.h"
#include "samsusers.h"
#include "samsuser.h"
#include "samsconfig.h"
#include "templates.h"
#include "template.h"
#include "groups.h"

bool Proxy::_loaded = false;
Proxy::usrAuthType Proxy::_auth;
Proxy::TrafficType Proxy::_trafType;
long Proxy::_id = -1;
long Proxy::_kbsize = 0;
long Proxy::_endvalue = 0;
bool Proxy::_needResolve = false;
bool Proxy::_usedomain = false;
string Proxy::_defaultdomain;
bool Proxy::_autouser = false;
long Proxy::_defaulttpl;
long Proxy::_defaultgrp;
DBConn *Proxy::_conn = NULL;
bool Proxy::_connection_owner = false;

string Proxy::toString (TrafficType t)
{
  string res;
  switch (t)
    {
    case TRAF_REAL:
      res = "real";
      break;
    case TRAF_FULL:
      res = "full";
      break;
    default:
      res = "unknown";
      break;
    }
  return res;
}

string Proxy::toString (usrAuthType t)
{
  string res;
  switch (t)
    {
    case AUTH_NONE:
      res = "unknown";
      break;
    case AUTH_NTLM:
      res = "ntlm";
      break;
    case AUTH_ADLD:
      res = "adld";
      break;
    case AUTH_LDAP:
      res = "ldap";
      break;
    case AUTH_NCSA:
      res = "ncsa";
      break;
    case AUTH_IP:
      res = "ip";
      break;
    default:
      res = "unknown";
      break;
    }
  return res;
}

void Proxy::useConnection (DBConn * conn)
{
  if (_conn)
    {
      DEBUG (DEBUG_PROXY, "[" << __FUNCTION__ << "] Already using " << _conn);
      return;
    }
  if (conn)
    {
      DEBUG (DEBUG_PROXY, "[" << __FUNCTION__ << "] Using external connection " << conn);
      _conn = conn;
      _connection_owner = false;
    }
}

long Proxy::getId ()
{
  load();

  return _id;
}

void Proxy::setEndValue(long endvalue)
{
  load();

  DEBUG (DEBUG_PROXY, "[" << __FUNCTION__ << "] " << endvalue);

  _endvalue = endvalue;

  if (_endvalue < 0)
    _endvalue = 0;
}

long Proxy::getEndValue()
{
  load();

  return _endvalue;
}

long Proxy::getKbSize ()
{
  load();

  return _kbsize;
}

Proxy::TrafficType Proxy::getTrafficType ()
{
  load();

  return _trafType;
}

string Proxy::getRedirectAddr ()
{
  return "http://redirect.addr.here";
}

SAMSUser *Proxy::findUser (const IP & ip, const string & ident)
{
  load();

  SAMSUser *usr = NULL;
  string usrDomain;
  string usrNick;
  vector < string > identTbl;

  DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] " << ip << ":" << ident);

  Split (ident, DOMAIN_SEPARATORS, identTbl);
  if (identTbl.size () == 2)
    {
      usrDomain = identTbl[0];
      usrNick = identTbl[1];
    }
  else
    {
      usrDomain = "";
      usrNick = identTbl[0];
    }

  if (_auth == AUTH_IP || usrNick == "-")
    usr = SAMSUsers::findUserByIP (ip);
  else
    usr = SAMSUsers::findUserByNick (usrDomain, usrNick);

  if (usr == NULL && usrNick != "-")
    {
      if (_auth == AUTH_IP)
        usr = SAMSUsers::findUserByNick (usrDomain, usrNick);
      else
        usr = SAMSUsers::findUserByIP (ip);
    }

  if (usr == NULL)
    {
      DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] " << ip << ":" << ident << " not found");

      if (_autouser)
        {
          Template *tpl = NULL;
          tpl = Templates::getTemplate (_defaulttpl);

          if (!tpl)
            {
              DEBUG (DEBUG_PROXY, "[" << __FUNCTION__ << "] Template could not be found");
              return NULL;
            }

          SAMSUser *usr = new SAMSUser ();
          if (tpl->getAuth() == Proxy::AUTH_IP)
            {
              usr->setNick (ip.asString());
              usr->setIP (ip.asString());
            }
          else if (usrNick == "-")
            {
              DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] Unable to create user '-' with non IP auth");
              delete usr;
              return NULL;
            }
          else
            usr->setNick (usrNick);

          usr->setDomain (usrDomain);
          usr->setGroupId (_defaultgrp);
          usr->setShablonId (_defaulttpl);
          usr->setQuote (tpl->getQuote());
          usr->setEnabled (SAMSUser::STAT_ACTIVE);
          if (!SAMSUsers::addNewUser (usr))
            {
              delete usr;
              return NULL;
            }
        }
    }

  return usr;
}

SAMSUser *Proxy::findUser (const string & ip, const string & ident)
{
  IP _ip;
  _ip.parseString (ip);

  return findUser (_ip, ident);
}

bool Proxy::load ()
{
  if (_loaded)
    return true;

  return reload();
}

bool Proxy::reload ()
{
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
      DEBUG (DEBUG_PROXY, "[" << __FUNCTION__ << "] Using new connection " << _conn);
    }
    else
    {
      DEBUG (DEBUG_PROXY, "[" << __FUNCTION__ << "] Using old connection " << _conn);
    }

  int err;
  _id = SamsConfig::getInt (defPROXYID, err);
  if (err != ERR_OK)
    {
      ERROR ("No proxyid defined. Check " << defPROXYID << " in config file.");
      return false;
    }

  char s_auth[5];
  long s_checkdns;
  char s_realsize[5];
  long s_usedomain;
  char s_defaultdomain[25];
  long s_autouser;
  long s_autotpl;
  long s_autogrp;

  DBQuery *query = NULL;
  basic_stringstream < char >sqlcmd;

  if (_conn->getEngine() == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      query = new ODBCQuery((ODBCConn*)_conn);
      #else
      return false;
      #endif
    }
  else if (_conn->getEngine() == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      query = new MYSQLQuery((MYSQLConn*)_conn);
      #else
      return false;
      #endif
    }
  else
    return false;

  if (!query->bindCol (1, DBQuery::T_CHAR, s_auth, sizeof (s_auth)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (2, DBQuery::T_LONG, &s_checkdns, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (3, DBQuery::T_CHAR, s_realsize, sizeof (s_realsize)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (4, DBQuery::T_LONG, &_kbsize, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (5, DBQuery::T_LONG, &_endvalue, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (6, DBQuery::T_LONG, &s_usedomain, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (7, DBQuery::T_CHAR, s_defaultdomain, sizeof(s_defaultdomain)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (8, DBQuery::T_LONG, &s_autouser, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (9, DBQuery::T_LONG, &s_autotpl, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (10, DBQuery::T_LONG, &s_autogrp, 0))
    {
      delete query;
      return false;
    }

  sqlcmd << "select s_auth, s_checkdns, s_realsize, s_kbsize, s_endvalue, s_usedomain, s_defaultdomain";
  sqlcmd << ", s_autouser, s_autotpl, s_autogrp";
  sqlcmd << " from proxy where s_proxy_id=" << _id;

  if (!query->sendQueryDirect (sqlcmd.str ()))
    {
      delete query;
      return false;
    }
  if (!query->fetch ())
    {
      delete query;
      return false;
    }

  if (strcmp (s_auth, "ip") == 0)
    _auth = AUTH_IP;
  else if (strcmp (s_auth, "ncsa") == 0)
    _auth = AUTH_NCSA;
  else if (strcmp (s_auth, "ntlm") == 0)
    _auth = AUTH_NTLM;
  else if (strcmp (s_auth, "adld") == 0)
    _auth = AUTH_ADLD;
  else if (strcmp (s_auth, "ldap") == 0)
    _auth = AUTH_LDAP;
  else
    {
      ERROR ("Unknown authentication scheme: " << s_auth);
    }

  if (s_checkdns > 0)
    _needResolve = true;
  else
    _needResolve = false;

  if (s_usedomain > 0)
    _usedomain = true;
  else
    _usedomain = false;

  _defaultdomain = s_defaultdomain;

  if (s_autouser > 0)
    _autouser = true;
  else
    _autouser = false;

  _defaulttpl = s_autotpl;
  _defaultgrp = s_autogrp;

  if (strcmp (s_realsize, "real") == 0)
    _trafType = TRAF_REAL;
  else if (strcmp (s_realsize, "full") == 0)
    _trafType = TRAF_FULL;
  else
    {
      ERROR ("Unknown traffic type: " << s_realsize);
    }

  DEBUG (DEBUG_PROXY, "Authentication: " << toString (_auth));
  DEBUG (DEBUG_PROXY, "DNS Resolving: " << ((_needResolve) ? ("true") : ("false")));
  DEBUG (DEBUG_PROXY, "Traffic type: " << toString (_trafType));
  DEBUG (DEBUG_PROXY, "Kilobyte size: " << _kbsize);

  if (_usedomain)
    {
      DEBUG (DEBUG_PROXY, "Default domain: " << _defaultdomain);
    }

  if (_autouser)
    {
      DEBUG (DEBUG_PROXY, "AutoUserTemplate: " << _defaulttpl);
      DEBUG (DEBUG_PROXY, "AutoUserGroup: " << _defaultgrp);
    }
  delete query;

  _loaded = true;

  return true;
}

void Proxy::destroy()
{
  if (_connection_owner && _conn)
    {
      DEBUG (DEBUG_PROXY, "[" << __FUNCTION__ << "] Destroy connection " << _conn);
      delete _conn;
      _conn = NULL;
    }
  else
    {
      DEBUG (DEBUG_PROXY, "[" << __FUNCTION__ << "] Not owner for connection " << _conn);
    }
}

