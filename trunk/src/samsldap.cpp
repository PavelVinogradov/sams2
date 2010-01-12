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

#include "samsldap.h"
#include "debug.h"

#ifdef USE_LDAP

SamsLDAP::SamsLDAP()
{
  _connected = false;
}

SamsLDAP::~SamsLDAP()
{
  if (_connected)
    disconnect ();
}

bool SamsLDAP::connect (const string &host, const string &binddn, const string &pass)
{
  struct berval cred;

  if (_connected)
    disconnect ();

  DEBUG(DEBUG8, "Connecting to " << host << " as " << binddn);

  string uri = "ldap://" + host;
  _err = ldap_initialize (&_handle, uri.c_str());
  if (_err)
    return false;

  cred.bv_val = (char*)pass.c_str ();
  cred.bv_len = pass.size ();

  _err = ldap_sasl_bind_s (_handle, binddn.c_str(), NULL, &cred, NULL, NULL, NULL);
  if (_err)
    return false;

  _connected = true;
  return true;
}

void SamsLDAP::disconnect ()
{
  _err = ldap_unbind_ext_s(_handle, NULL, NULL);
  _connected = false;
}

SamsLDAPResult * SamsLDAP::searchSubtree(const string &base_dn, const string &filter, const string &attrs)
{
  struct timeval timeout;
  LDAPMessage *message;

  timeout.tv_sec = 15;
  timeout.tv_usec = 0;

  _err = ldap_search_ext_s (
        _handle, base_dn.c_str(), LDAP_SCOPE_SUBTREE, filter.c_str(),
        NULL, 0, NULL, NULL,
        &timeout, 0, &message);

  SamsLDAPResult *result = new SamsLDAPResult(this, message);
  return result;
}

string SamsLDAP::errorstr ()
{
  return ldap_err2string(_err);
}




SamsLDAPResult::SamsLDAPResult(SamsLDAP *ldap, LDAPMessage *result)
{
  _ldap = ldap;
  _result = result;
}

int SamsLDAPResult::size ()
{
  return ldap_count_entries (_ldap->_handle, _result);
}

#endif // #ifdef USE_LDAP

