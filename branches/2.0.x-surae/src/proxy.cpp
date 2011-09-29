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
#include <string.h>
//#include <sys/types.h>

#include "config.h"

#include "dbconn.h"
#include "dbquery.h"
#include "proxy.h"
#include "debug.h"
#include "tools.h"
#include "userlist.h"
#include "samsuserlist.h"
#include "samsuser.h"
#include "samsconfig.h"
#include "templatelist.h"
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
Proxy::ParserType Proxy::_parser_type;
Proxy::RedirType Proxy::_redir_type;
string Proxy::_deny_addr;
string Proxy::_redir_addr;
string Proxy::_admin_addr;
long Proxy::_parser_time = 1;
bool Proxy::_autouser = false;
long Proxy::_defaulttpl;
long Proxy::_defaultgrp;
long Proxy::_squidbase;
Proxy::CharCase Proxy::_domain_case = Proxy::CASE_ORIGINAL;
Proxy::CharCase Proxy::_username_case = Proxy::CASE_ORIGINAL;
DBConn *Proxy::_conn = NULL;
bool Proxy::_connection_owner = false;
bool Proxy::_auto_clean_counters = true;
bool Proxy::_use_delay_pools = false;
string Proxy::_separator;

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

Proxy::usrAuthType Proxy::toAuthType(const string & s)
{
  usrAuthType res = AUTH_NONE;
  string auth = ToLower (s);

  if (auth == "ip")
    res = AUTH_IP;
  else if (auth == "ncsa")
    res = AUTH_NCSA;
  else if (auth == "ntlm")
    res = AUTH_NTLM;
  else if (auth == "adld")
    res = AUTH_ADLD;
  else if (auth == "ldap")
    res = AUTH_LDAP;
  else if (auth == "host")
    res = AUTH_HOST;
  else
    {
      ERROR ("Unknown authentication scheme: " << auth);
    }

  return res;
}

string Proxy::toString (RedirType t)
{
  string res;
  switch (t)
    {
    case REDIR_NONE:
      res = "not used";
      break;
    case REDIR_INTERNAL:
      res = "internal";
      break;
    case REDIR_EXTERNAL:
      res = "external";
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

long Proxy::getId ()
{
  load();

  return _id;
}

long Proxy::getEndValue()
{
  load();

  return _endvalue;
}

void Proxy::setEndValue (long val)
{
  _endvalue = val;
}

long Proxy::getKbSize ()
{
  load();

  return _kbsize;
}

void Proxy::getParserType (Proxy::ParserType & ptype, long & ptime)
{
  load();

  ptype = _parser_type;
  ptime = _parser_time;
}

Proxy::TrafficType Proxy::getTrafficType ()
{
  load();

  return _trafType;
}

string Proxy::getRedirectAddr ()
{
  return _redir_addr;
}

string Proxy::getDenyAddr ()
{
  return _deny_addr;
}

string Proxy::getAdminAddr ()
{
  return _admin_addr;
}

bool Proxy::isUseDNS ()
{
  return _needResolve;
}

bool Proxy::useDomain ()
{
  return _usedomain;
}

Proxy::CharCase Proxy::getDomainCase ()
{
  return _domain_case;
}

Proxy::CharCase Proxy::getUsernameCase ()
{
  return _username_case;
}

Proxy::RedirType Proxy::getRedirectType ()
{
  return _redir_type;
}

long Proxy::getCacheAge ()
{
  return _squidbase;
}

string Proxy::getSeparator ()
{
  return _separator;
}

SAMSUser *Proxy::findUser (const string & ip, const string & ident)
{
  load();

  SAMSUser *usr = NULL;
  string usrDomain;
  string usrNick;
  vector < string > identTbl;

  DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] ip:" << ip << ", ident:" << ident);

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

  if (usrNick == "-")
    usr = SAMSUserList::findUser ("ip", ip, usrDomain, usrNick);
  else
    usr = SAMSUserList::findUser (toString (_auth), ip, usrDomain, usrNick);

  if (usr == NULL)
    {
      DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] " << ip << ":" << ident << " not found");

      if (_autouser)
        {
          Template *tpl = NULL;
          tpl = TemplateList::getTemplate (_defaulttpl);

          if (!tpl)
            {
              DEBUG (DEBUG_PROXY, "[" << __FUNCTION__ << "] Template could not be found");
              return NULL;
            }

          usr = new SAMSUser ();
          if (tpl->getAuth() == Proxy::AUTH_IP)
            {
              usr->setNick (ip);
              usr->setIP (ip);
            }
          else if (usrNick == "-")
            {
              DEBUG (DEBUG_USER, "[" << __FUNCTION__ << "] Unable to create user '-' with non IP auth");
              delete usr;
              return NULL;
            }
          else
            {
              if (!UserList::userExist(tpl->getAuth(), usrDomain, usrNick))
                {
                  DEBUG (DEBUG3, "[" << __FUNCTION__ << "] System user " << usrNick << " not found.");
                  delete usr;
                  return NULL;
                }
              usr->setNick (usrNick);
            }
          if (_usedomain)
            usr->setDomain (usrDomain);
          usr->setGroupId (_defaultgrp);
          usr->setActiveTemplateId (_defaulttpl);
          usr->setLimitedTemplateId (tpl->getLimitedId ());
          if (!SAMSUserList::addNewUser (toString (tpl->getAuth ()), usr))
            {
              DEBUG (DEBUG_PROXY, "[" << __FUNCTION__ << "] Failed to create new user.");
              delete usr;
              return NULL;
            }
          DEBUG (DEBUG_PROXY, "[" << __FUNCTION__ << "] User created.");

          // Пользователя создали в автоматическом режиме, проинформируем об этом Squid.
          DBQuery *query = NULL;
          _conn->newQuery (query);
          basic_stringstream < char >sqlcmd;
          if (!query)
            {
              ERROR("Unable to create query.");
            }
          else
            {
              sqlcmd << "insert into reconfig (s_proxy_id, s_service, s_action)";
              sqlcmd << " values (" << _id << ", 'squid', 'reconfig')";
              query->sendQueryDirect (sqlcmd.str ());
              delete query;
            }
        }
    }

  return usr;
}

bool Proxy::needClearCounters ()
{
  return _auto_clean_counters;
}

bool Proxy::useDelayPools ()
{
  return _use_delay_pools;
}

string Proxy::createUserHash (const string &auth, const string &ip, const string &domain, const string &nick)
{
  string hash = "";

  if (auth == "ip")
    {
      hash = ip;
      DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << auth << ", " << ip << ", " << domain << ", " << nick << ")] = " << hash);
      return hash;
    }

  string usrDomain="";
  string usrNick=nick;
  if (_usedomain && (auth == "ntlm" || auth == "adld"))
    {
      if (domain.empty ())
        usrDomain = _defaultdomain;
      else
        usrDomain = domain;

      switch (_domain_case)
        {
          case CASE_UPPER:
            usrDomain = ToUpper (usrDomain);
            break;
          case CASE_LOWER:
            usrDomain = ToLower (usrDomain);
            break;
          default:
            break;
        }
    }

  switch (_username_case)
    {
      case CASE_UPPER:
        usrNick = ToUpper (usrNick);
        break;
      case CASE_LOWER:
        usrNick = ToLower (usrNick);
        break;
      default:
        break;
    }

  hash = usrDomain + "#" + usrNick;

  DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << auth << ", " << ip << ", " << domain << ", " << nick << ")] = " << hash);

  return hash;
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
  char s_redirector[25];
  char s_denied_to[105];
  char s_admin_addr[65];
  long s_autouser;
  long s_autotpl;
  long s_autogrp;
  char s_redirect_to[105];
  long s_count_clean;
  long s_delaypool;
  char s_separator[15];

  DBQuery *query = NULL;
  basic_stringstream < char >sqlcmd;

  _conn->newQuery (query);
  if (!query)
    {
      ERROR("Unable to create query.");
      return false;
    }

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
  if (!query->bindCol (8, DBQuery::T_LONG, &_parser_type, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (9, DBQuery::T_LONG, &_parser_time, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (10, DBQuery::T_LONG, &s_autouser, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (11, DBQuery::T_LONG, &s_autotpl, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (12, DBQuery::T_LONG, &s_autogrp, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (13, DBQuery::T_LONG, &_squidbase, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (14, DBQuery::T_CHAR, s_redirector, sizeof(s_redirector)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (15, DBQuery::T_CHAR, s_denied_to, sizeof(s_denied_to)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (16, DBQuery::T_CHAR, s_redirect_to, sizeof(s_redirect_to)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (17, DBQuery::T_CHAR, s_admin_addr, sizeof(s_admin_addr)))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (18, DBQuery::T_LONG, &_domain_case, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (19, DBQuery::T_LONG, &_username_case, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (20, DBQuery::T_LONG, &s_count_clean, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (21, DBQuery::T_LONG, &s_delaypool, 0))
    {
      delete query;
      return false;
    }
  if (!query->bindCol (22, DBQuery::T_CHAR, s_separator, sizeof(s_separator)))
    {
      delete query;
      return false;
    }

  sqlcmd << "select s_auth, s_checkdns, s_realsize, s_kbsize, s_endvalue, s_usedomain, s_defaultdomain";
  sqlcmd << ", s_parser, s_parser_time";
  sqlcmd << ", s_autouser, s_autotpl, s_autogrp";
  sqlcmd << ", s_squidbase, s_redirector";
  sqlcmd << ", s_denied_to, s_redirect_to, s_adminaddr";
  sqlcmd << ", s_bigd, s_bigu";
  sqlcmd << ", s_count_clean, s_delaypool, s_separator";
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

  _auth = toAuthType (s_auth);

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

  if (strcasecmp (s_redirector, "NONE") == 0)
    _redir_type = REDIR_NONE;
  else if (strcmp (s_redirector, "sams") == 0)
    _redir_type = REDIR_INTERNAL;
  else
    {
      _redir_type = REDIR_EXTERNAL;
    }

  _deny_addr = s_denied_to;
  _redir_addr = s_redirect_to;
  _admin_addr = s_admin_addr;

  if (s_count_clean > 0)
    _auto_clean_counters = true;
  else
    _auto_clean_counters = false;

  if (s_delaypool > 0)
    _use_delay_pools = true;
  else
    _use_delay_pools = false;

  if (strstr (s_separator, "+") != 0)
    _separator = "+";
  else if (strstr (s_separator, "@") != 0)
    _separator = "@";
  else
    _separator = "\\";

  DEBUG (DEBUG3, "Clear counters: " << ((_auto_clean_counters) ? ("true") : ("false")));
  DEBUG (DEBUG3, "Use delay pool: " << ((_use_delay_pools) ? ("true") : ("false")));
  DEBUG (DEBUG3, "Authentication: " << toString (_auth));
  DEBUG (DEBUG3, "DNS Resolving: " << ((_needResolve) ? ("true") : ("false")));
  DEBUG (DEBUG3, "Traffic type: " << toString (_trafType));
  DEBUG (DEBUG3, "Redirector type: " << toString (_redir_type));
  DEBUG (DEBUG3, "Kilobyte size: " << _kbsize);

  if (_usedomain)
    {
      DEBUG (DEBUG3, "Default domain: " << _defaultdomain);
    }

  if (_autouser)
    {
      DEBUG (DEBUG3, "AutoUserTemplate: " << _defaulttpl);
      DEBUG (DEBUG3, "AutoUserGroup: " << _defaultgrp);
    }
  delete query;

  _loaded = true;

  return true;
}

void Proxy::destroy()
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
}

