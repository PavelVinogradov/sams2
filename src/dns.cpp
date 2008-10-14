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

#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>

#include <netdb.h>
//extern int h_errno;

bool DNS::getNamesByAddr(const string &address, vector<string> &names)
{
  int j;
  int ok;
  struct hostent *h;
  struct in_addr addr;

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

  names.push_back (h->h_name);
  j = 0;
  while (h->h_aliases[j])
    {
      names.push_back (h->h_aliases[j]);
      j++;
    }

  return true;
}

bool DNS::getAddrsByName(const string &name, vector<string> &addrs)
{
  int i;
  struct hostent *h;

  h = gethostbyname(name.c_str ());
  if (!h)
    {
      return false;
    }

  for (i=0; i<h->h_length; i++)
    {
      addrs.push_back (h->h_addr_list[i]);
    }

  return true;
}
