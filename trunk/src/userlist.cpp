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

#include <sstream>
#include <limits.h>

#include <sys/types.h>
#include <pwd.h>

#include "userlist.h"
#include "samsldap.h"
#include "samsconfig.h"
#include "debug.h"

bool UserList::userExist(Proxy::usrAuthType auth, const string &domain, const string &nick)
{
  switch (auth)
    {
      case Proxy::AUTH_NONE:
      case Proxy::AUTH_IP:
      case Proxy::AUTH_HOST:
      case Proxy::AUTH_NCSA:
        return true;
      case Proxy::AUTH_NTLM:
      case Proxy::AUTH_ADLD:
        return checkPasswd (nick);
      case Proxy::AUTH_LDAP:
#ifdef USE_LDAP
        return checkLDAP (nick);
#else
        return checkPasswd (nick);;
#endif
    }

  return false;
}

bool UserList::checkPasswd(const string &nick)
{
  struct passwd *pwd = NULL;

  pwd = getpwnam (nick.c_str ());
  if (!pwd)
    {
      DEBUG (DEBUG9, "[" << __FUNCTION__ << "] User " << nick << " not found in passwd database.");
      return false;
    }
  return true;
}

bool UserList::checkLDAP(const string &nick)
{
#ifdef USE_LDAP
  bool res;
  SamsLDAP *ldap = NULL;
  SamsLDAPResult *result = NULL;
  int err;
  int ldapEnabled;
  string ldapServer;
  string ldapBaseDN;
  string ldapBindDN;
  string ldapBindPw;
  string ldapUsersRDN;

  ldapEnabled = SamsConfig::getInt (defLDAPENABLED, err);
  if (err != ERR_OK)
    {
      ERROR ("LDAP auth not enabled.");
      return false;
    }
  ldapServer = SamsConfig::getString (defLDAPSERVER, err);
  if (err != ERR_OK)
    {
      ERROR ("LDAP server not defined.");
      return false;
    }
  ldapBaseDN = SamsConfig::getString (defLDAPBASEDN, err);
  if (err != ERR_OK)
    {
      ERROR ("LDAP base DN not defined.");
      return false;
    }
  ldapBindDN = SamsConfig::getString (defLDAPBINDDN, err);
  if (err != ERR_OK)
    {
      ERROR ("LDAP bind DN not defined.");
      return false;
    }
  ldapBindPw = SamsConfig::getString (defLDAPBINDPW, err);
  if (err != ERR_OK)
    {
      ERROR ("LDAP bind password not defined.");
      return false;
    }
  ldapUsersRDN = SamsConfig::getString (defLDAPUSERSRDN, err);
  if (err != ERR_OK)
    {
      ERROR ("LDAP users RDN not defined.");
      return false;
    }

  if (!ldapEnabled)
    return false;

  ldap = new SamsLDAP();
  if (!ldap->connect (ldapServer, ldapBindDN, ldapBindPw))
    {
      ERROR(ldap->errorstr());
      delete ldap;
      return false;
    }
  result = ldap->searchSubtree(ldapUsersRDN + "," + ldapBaseDN, "(uid=" + nick + ")", "uid");

  if (result->size() > 0)
    res = true;
  else
    res = false;

  delete result;
  delete ldap;

  return res;
#else
  WARNING ("LDAP support not enabled.");
  return false;
#endif
}

