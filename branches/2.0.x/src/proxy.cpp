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

Proxy::Proxy (long id, DBConn * connection)
{
  _id = id;
  _conn = connection;

  DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] " << "using connection " << _conn);

  _users = new SAMSUsers (_conn);
  load ();
}


Proxy::~Proxy ()
{
  delete _users;
}


long Proxy::getId ()
{
  return _id;
}

void Proxy::setEndValue(long endvalue)
{
  DEBUG (DEBUG_PROXY, "[" << this << "->" << __FUNCTION__ << "] " << endvalue);

  _endvalue = endvalue;

  if (_endvalue < 0)
    _endvalue = 0;
}

long Proxy::getEndValue()
{
  return _endvalue;
}

SAMSUser *Proxy::findUser (const IP & ip, const string & ident)
{
  SAMSUser *usr;
  string usrDomain;
  string usrNick;
  vector < string > identTbl;

  DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] " << ip << ":" << ident);

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

  if (_auth == AUTH_IP)
    usr = _users->findUserByIP (ip);
  else
    usr = _users->findUserByNick (usrDomain, usrNick);

  if (usr == NULL)
    {
      if (_auth == AUTH_IP)
        usr = _users->findUserByNick (usrDomain, usrNick);
      else
        usr = _users->findUserByIP (ip);
    }

  if (usr == NULL)
    {
      DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] " << ip << ":" << ident << " not found");
    }

  return usr;
}

void Proxy::load ()
{
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
      #endif
    }
  else if (_conn->getEngine() == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      query = new MYSQLQuery((MYSQLConn*)_conn);
      #endif
    }
  else
    return;

  if (!query->bindCol (1, DBQuery::T_CHAR, s_auth, sizeof (s_auth)))
    {
      delete query;
      return;
    }
  if (!query->bindCol (2, DBQuery::T_LONG, &s_checkdns, 0))
    {
      delete query;
      return;
    }
  if (!query->bindCol (3, DBQuery::T_CHAR, s_realsize, sizeof (s_realsize)))
    {
      delete query;
      return;
    }
  if (!query->bindCol (4, DBQuery::T_LONG, &_kbsize, 0))
    {
      delete query;
      return;
    }
  if (!query->bindCol (5, DBQuery::T_LONG, &_endvalue, 0))
    {
      delete query;
      return;
    }
  if (!query->bindCol (6, DBQuery::T_LONG, &s_usedomain, 0))
    {
      delete query;
      return;
    }
  if (!query->bindCol (7, DBQuery::T_CHAR, s_defaultdomain, sizeof(s_defaultdomain)))
    {
      delete query;
      return;
    }
  if (!query->bindCol (8, DBQuery::T_LONG, &s_autouser, 0))
    {
      delete query;
      return;
    }
  if (!query->bindCol (9, DBQuery::T_LONG, &s_autotpl, 0))
    {
      delete query;
      return;
    }
  if (!query->bindCol (10, DBQuery::T_CHAR, s_autotpl_val, sizeof(s_autotpl_val)))
    {
      delete query;
      return;
    }
  if (!query->bindCol (11, DBQuery::T_LONG, &s_autogrp, 0))
    {
      delete query;
      return;
    }
  if (!query->bindCol (12, DBQuery::T_CHAR, s_autogrp_val, sizeof(s_autogrp_val)))
    {
      delete query;
      return;
    }

  sqlcmd << "select s_auth, s_checkdns, s_realsize, s_kbsize, s_endvalue, s_usedomain, s_defaultdomain";
  sqlcmd << ", s_autouser, s_autotpl, s_autotpl_val, s_autogrp, s_autogrp_val";
  sqlcmd << " from proxy where s_proxy_id=" << _id;

  if (!query->sendQueryDirect (sqlcmd.str ()))
    {
      delete query;
      return;
    }
  if (!query->fetch ())
    {
      delete query;
      return;
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


  DBQuery *query = NULL;

  DBConn::DBEngine engine = _conn->getEngine();
  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      query = new ODBCQuery((ODBCConn*)_conn);
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      query = new MYSQLQuery((MYSQLConn*)_conn);
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
  for (it=_users->_users.begin(); it != _users->_users.end(); it++)
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
          INFO("User " << *usr << " deactivated.");
        }

      s_enabled = (long)usr->getEnabled();

      if (!query->sendQuery ())
        continue;
    }

  delete query;
}

long Proxy::getShablonId(const string &name) const
{
  DBQuery *query = NULL;

  if (_conn->getEngine() == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      query = new ODBCQuery((ODBCConn*)_conn);
      #endif
    }
  else if (_conn->getEngine() == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      query = new MYSQLQuery((MYSQLConn*)_conn);
      #endif
    }
  else
    return -1;

  long s_shablon_id;
  if (!query->bindCol(1, DBQuery::T_LONG, &s_shablon_id, 0))
    {
      delete query;
      return -1;
    }
  basic_stringstream < char >sqlcmd;

  sqlcmd << "select s_shablon_id from shablon where s_name=" << name;

  if (!query->sendQueryDirect (sqlcmd.str ()))
    {
      delete query;
      return -1;
    }
  if (!query->fetch ())
    {
      delete query;
      return -1;
    }

  delete query;

  return s_shablon_id;
}

long Proxy::getGroupId(const string &name) const
{
  DBQuery *query = NULL;

  if (_conn->getEngine() == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      query = new ODBCQuery((ODBCConn*)_conn);
      #endif
    }
  else if (_conn->getEngine() == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      query = new MYSQLQuery((MYSQLConn*)_conn);
      #endif
    }
  else
    return -1;

  long s_group_id;
  if (!query->bindCol(1, DBQuery::T_LONG, &s_group_id, 0))
    {
      delete query;
      return -1;
    }
  basic_stringstream < char >sqlcmd;

  sqlcmd << "select s_group_id from sgroup where s_name=" << name;

  if (!query->sendQueryDirect (sqlcmd.str ()))
    {
      delete query;
      return -1;
    }
  if (!query->fetch ())
    {
      delete query;
      return -1;
    }

  delete query;

  return s_group_id;
}
