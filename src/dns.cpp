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
#include "dns.h"
#include "debug.h"

#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>

#include <strings.h>
#include <netdb.h>

//map<string, vector<string> > DNS::entries;

bool DNS::getNamesByAddr(const string &address, vector<string> &names)
{
  int j;
  int ok;
  struct hostent *h;
  struct in_addr addr;

  DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << address << ")]");

/*
  map<string, vector<string> >::const_iterator it;
  it = entries.find (address);
  if (it != entries.end ())
    {
      DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << address << ")] Found in the cache");
      names = it->second;
      return true;
    }
*/

  ok = inet_aton(address.c_str (), &addr);
  if (!ok)
    {
      return false;
    }

  h = gethostbyaddr(&addr, sizeof(struct in_addr), AF_INET);
  if (!h)
    {
      return false;
    }

  DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << h->h_name);
  names.push_back (h->h_name);
  j = 0;
  while (h->h_aliases[j])
    {
      DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << h->h_aliases[j]);
      names.push_back (h->h_aliases[j]);
      j++;
    }

  //entries[address] = names;

  return true;
}

bool DNS::getAddrsByName(const string &name, vector<string> &addrs)
{
  struct hostent *h = NULL;
  struct in_addr a;
  char *str = NULL;

  DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << name << ")]");

/*
  map<string, vector<string> >::const_iterator it;
  it = entries.find (name);
  if (it != entries.end ())
    {
      DEBUG (DEBUG8, "[" << __FUNCTION__ << "(" << name << ")] Found in the cache");
      addrs = it->second;
      return true;
    }
*/

  h = gethostbyname(name.c_str ());
  if (!h)
    {
      return false;
    }

  while (*h->h_addr_list)
    {
      bcopy(*h->h_addr_list++, (char *) &a, sizeof(a));

      str = inet_ntoa (a);
      DEBUG (DEBUG9, "[" << __FUNCTION__ << "] " << str);
      addrs.push_back (str);
    }

  //entries[name] = addrs;

  return true;
}
