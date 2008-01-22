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
Proxy::usrUseAutoTemplate Proxy::_autotpl;
string Proxy::_defaulttpl;
Proxy::usrUseAutoGroup Proxy::_autogrp;
string Proxy::_defaultgrp;
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
      DEBUG (DEBUG_PROXY, "[" << __FUNCTION__ << "] Using external connection " << _conn);
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
          Template *tpl = Templates::getTemplate( _defaulttpl );
          if (!tpl)
            {
              return false;
            }

          SAMSUser *usr = new SAMSUser ();
          if (tpl->getAuth() == Proxy::AUTH_IP)
            {
              usr->setNick (ip.asString());
              usr->setIP(ip.asString());
            }
          else
            usr->setNick(usrNick);
          usr->setDomain (usrDomain);
          usr->setGroupId (2);
          usr->setShablonId (tpl->getId());
          usr->setQuote (tpl->getQuote());
          usr->setEnabled (SAMSUser::STAT_ACTIVE);
          if (!SAMSUsers::addNewUser(usr))
            {
              usr = NULL;
            }
        }

    }

  return usr;
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
  char s_autotpl_val[30];
  long s_autogrp;
  char s_autogrp_val[30];

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
  if (!query->bindCol (10, DBQuery::T_CHAR, s_autotpl_val, sizeof(s_autotpl_val)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (11, DBQuery::T_LONG, &s_autogrp, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (12, DBQuery::T_CHAR, s_autogrp_val, sizeof(s_autogrp_val)))
    {
      delete query;
      return false;
    }

  sqlcmd << "select s_auth, s_checkdns, s_realsize, s_kbsize, s_endvalue, s_usedomain, s_defaultdomain";
  sqlcmd << ", s_autouser, s_autotpl, s_autotpl_val, s_autogrp, s_autogrp_val";
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

  _autotpl = (usrUseAutoTemplate) s_autotpl;
  _defaulttpl = s_autotpl_val;
  _autogrp = (usrUseAutoGroup) s_autogrp;
  _defaultgrp = s_autogrp_val;

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
      switch (_autotpl)
        {
          case TPL_DEFAULT:
            DEBUG (DEBUG_PROXY, "AutoUserTemplate: " << "Default");
            break;
          case TPL_SPECIFIED:
            DEBUG (DEBUG_PROXY, "AutoUserTemplate: " << _defaulttpl);
            break;
          case TPL_TAKE_FROM_GROUP:
            DEBUG (DEBUG_PROXY, "AutoUserTemplate: " << "take name from user primary group");
            break;
        }
      switch (_autogrp)
        {
          case GRP_DEFAULT:
            DEBUG (DEBUG_PROXY, "AutoUserGroup: " << "Default");
            break;
          case GRP_SPECIFIED:
            DEBUG (DEBUG_PROXY, "AutoUserGroup: " << _defaultgrp);
            break;
          case GRP_TAKE_FROM_GROUP:
            DEBUG (DEBUG_PROXY, "AutoUserGroup: " << "take name from user primary group");
            break;
        }
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

void Proxy::commitChanges ()
{
  vector < SAMSUser * >::iterator it;
  SAMSUser *usr;
  long long allowed_limit;
  long long s_size;
  long long s_hit;
  long s_enabled;
  long s_user_id;
  basic_stringstream < char > update_cmd;

  load();

  DBQuery *query = NULL;

  DBConn::DBEngine engine = _conn->getEngine();
  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      query = new ODBCQuery((ODBCConn*)_conn);
      #else
      return;
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      query = new MYSQLQuery((MYSQLConn*)_conn);
      #else
      return;
      #endif
    }
  else
    return;

  update_cmd << "update proxy set s_endvalue=" << _endvalue << " where s_proxy_id=" << _id;
  if (!query->sendQueryDirect( update_cmd.str ()))
    {
      delete query;
      return;
    }

  update_cmd.str("");
  update_cmd << "update squiduser set";
  update_cmd << " s_size=?";
  update_cmd << ",s_hit=?";
  update_cmd << ",s_enabled=?";
  update_cmd << " where s_user_id=?";
  if (!query->prepareQuery (update_cmd.str ()))
    {
      delete query;
      return;
    }
  if (!query->bindParam (1, DBQuery::T_LONGLONG, &s_size, 0))
    {
      delete query;
      return;
    }
  if (!query->bindParam (2, DBQuery::T_LONGLONG, &s_hit, 0))
    {
      delete query;
      return;
    }
  if (!query->bindParam (3, DBQuery::T_LONG, &s_enabled, 0))
    {
      delete query;
      return;
    }
  if (!query->bindParam (4, DBQuery::T_LONG, &s_user_id, 0))
    {
      delete query;
      return;
    }

  long long used_size;
  for (it=SAMSUsers::_users.begin(); it != SAMSUsers::_users.end(); it++)
    {
      usr = *it;
      allowed_limit = usr->getQuote();
      allowed_limit *= _kbsize * _kbsize;
      s_size = usr->getSize();
      s_hit = usr->getHit();
      s_user_id = usr->getId();

      switch (_trafType)
        {
          case TRAF_REAL:
            used_size = s_size - s_hit;
            break;
          case TRAF_FULL:
            used_size = s_size;
            break;
          default:
            used_size = 0;
            break;
        }

      DEBUG(DEBUG_USER, *usr << " size="<<used_size<<" limit="<<allowed_limit);

      if ( (allowed_limit > 0) && (used_size > allowed_limit) && (usr->getEnabled() == SAMSUser::STAT_ACTIVE) )
        {
          usr->setEnabled( SAMSUser::STAT_INACTIVE );
          basic_stringstream < char >mess;

          mess << "User " << *usr << " deactivated.";

          INFO (mess.str ());
          logger->addLog(Logger::LK_USER, mess.str());
        }

      s_enabled = (long)usr->getEnabled();

      if (!query->sendQuery ())
        continue;
    }

  delete query;
}

