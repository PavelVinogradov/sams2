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
#include "userfilter.h"
#include "samsuser.h"
#include "tools.h"

UserFilter::UserFilter ():Filter ()
{
  setUsersList ("");
}

UserFilter::UserFilter (const string & usersList):Filter ()
{
  setUsersList (usersList);
}

UserFilter::~UserFilter ()
{
}

bool UserFilter::setUsersList (const string & usersList)
{
  _validity = true;

  Split (usersList, ",", _tblUsers);

  return _validity;
}

bool UserFilter::match (SAMSUser * user)
{
  vector < string >::iterator it;
  vector < string > tblSpec;
  string strDomain;
  string strUser;
  string strUserIP;
  bool matched = false;

  if (user == NULL)
    return false;

  if (!_validity)
    return true;

  strUserIP = user->getIP ().asString ();

  for (it = _tblUsers.begin (); it != _tblUsers.end (); it++)
    {
      Split ((*it), DOMAIN_SEPARATORS, tblSpec);
      if (tblSpec.size () == 2)
        {
          strDomain = tblSpec[0];
          strUser = tblSpec[1];
        }
      else if (tblSpec.size () == 1)
        {
          strUser = tblSpec[0];
        }

      if (user->getNick () == strUser || strUserIP == strUser)
        {
          matched = true;
          break;
        }
    }

  return matched;
}
