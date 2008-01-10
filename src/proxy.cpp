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

  _users = new SAMSUsers ();
  _users->load (_conn);
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
  basic_stringstream < char >sqlcmd;

  sqlcmd << "select s_auth, s_checkdns, s_realsize, s_kbsize from proxy";
  sqlcmd << " where s_proxy_id=" << _id;
  if (_conn->getEngine() == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      ODBCQuery queryODBC( (ODBCConn*)_conn );

      if (!queryODBC.bindCol (1, SQL_C_CHAR, s_auth, sizeof (s_auth)))
          return;
      if (!queryODBC.bindCol (2, SQL_C_LONG, &s_checkdns, 0))
          return;
      if (!queryODBC.bindCol (3, SQL_C_CHAR, s_realsize, sizeof (s_realsize)))
          return;
      if (!queryODBC.bindCol (4, SQL_C_LONG, &_kbsize, 0))
          return;

      if (!queryODBC.sendQueryDirect (sqlcmd.str ()))
          return;
      if (!queryODBC.fetch ())
          return;
      #endif
    }
  else if (_conn->getEngine() == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      MYSQLQuery queryMYSQL( (MYSQLConn*)_conn );

      if (!queryMYSQL.bindCol (1, MYSQL_TYPE_STRING, s_auth, sizeof (s_auth)))
          return;
      if (!queryMYSQL.bindCol (2, MYSQL_TYPE_LONG, &s_checkdns, 0))
          return;
      if (!queryMYSQL.bindCol (3, MYSQL_TYPE_STRING, s_realsize, sizeof (s_realsize)))
          return;
      if (!queryMYSQL.bindCol (4, MYSQL_TYPE_LONG, &_kbsize, 0))
          return;

      if (!queryMYSQL.sendQueryDirect (sqlcmd.str ()))
          return;
      if (!queryMYSQL.fetch ())
          return;
      #endif
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
}


void Proxy::commitChanges ()
{
  vector < SAMSUser * >::iterator it;
  SAMSUser *usr;
  long allowed_limit;
  long s_size;
  long s_hit;
  long s_enabled;
  long s_user_id;
  basic_stringstream < char > update_cmd;

  update_cmd << "update squiduser set";
  update_cmd << " s_size=?";
  update_cmd << ",s_hit=?";
  update_cmd << ",s_enabled=?";
  update_cmd << " where s_user_id=?";

  #ifdef USE_UNIXODBC
  ODBCQuery *queryODBC = NULL;
  #endif

  #ifdef USE_UNIXODBC
  MYSQLQuery *queryMYSQL = NULL;
  #endif

  DBConn::DBEngine engine = _conn->getEngine();
  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      queryODBC = new ODBCQuery((ODBCConn*)_conn);
      if (!queryODBC->prepareQuery (update_cmd.str ()))
        {
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (1, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, &s_size, 0))
        {
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (2, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, &s_hit, 0))
        {
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (3, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, &s_enabled, 0))
        {
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (4, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, &s_user_id, 0))
        {
          delete queryODBC;
          return;
        }
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      queryMYSQL = new MYSQLQuery((MYSQLConn*)_conn);
      if (!queryMYSQL->prepareQuery (update_cmd.str ()))
        {
          delete queryMYSQL;
          return;
        }
      if (!queryMYSQL->bindParam (1, MYSQL_TYPE_LONG, &s_size, 0))
        {
          delete queryMYSQL;
          return;
        }
      if (!queryMYSQL->bindParam (2, MYSQL_TYPE_LONG, &s_hit, 0))
        {
          delete queryMYSQL;
          return;
        }
      if (!queryMYSQL->bindParam (3, MYSQL_TYPE_LONG, &s_enabled, 0))
        {
          delete queryMYSQL;
          return;
        }
      if (!queryMYSQL->bindParam (4, MYSQL_TYPE_LONG, &s_user_id, 0))
        {
          delete queryMYSQL;
          return;
        }
      #endif
    }



  long used_size;
  for (it=_users->_users.begin(); it != _users->_users.end(); it++)
    {
      usr = *it;
      allowed_limit = usr->getQuote() * _kbsize * _kbsize;
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

      if ( (allowed_limit > 0) && (used_size > allowed_limit) && (usr->getEnabled() == STAT_ACTIVE) )
        {
          usr->setEnabled( STAT_INACTIVE );
          INFO("User " << *usr << " deactivated.");
        }

      s_enabled = (long)usr->getEnabled();
      if (engine == DBConn::DB_UODBC)
        {
          #ifdef USE_UNIXODBC
          if (!queryODBC->sendQuery ())
              continue;
          #endif
        }
      else if (engine == DBConn::DB_MYSQL)
        {
          #ifdef USE_MYSQL
          if (!queryMYSQL->sendQuery ())
              continue;
          #endif
        }
    }

}
