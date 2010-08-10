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

#include <netdb.h>
#include <sstream>

#include "net.h"
#include "dns.h"
#include "proxy.h"
#include "debug.h"

Net::Net ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  _domain = false;
  _net = "";
  _ip = NULL;
  _id = -1;
}

Net::~Net ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  if (_ip)
    delete _ip;
  _ip = NULL;
}

void Net::setId(long id)
{
  _id = id;
}

long Net::getId() const
{
  return _id;
}

bool Net::hasHost (const string & host)
{
  bool isname;
  bool res;
  int pos;
  IP *hostIP = NULL;
  DEBUG (DEBUG8, "Check if " << _net << " contains " << host);

  isname = Net::isDomain (host);

  // Сеть и хост определены доменными именами
  if (_domain && isname)
    {
      DEBUG (DEBUG8, "[" << this << "] " << "domain specifications");

      // Если сеть определена как www.mail.ru, то mail.ru никак не может быть хостом в этой сети
      if (_net.size () > host.size ())
        return false;

      pos = host.compare (host.size ()-_net.size (), _net.size (), _net);

      if (pos == 0)
        {
          DEBUG (DEBUG4, "[" << this << "] Host " << host << " is part of net " << _net);
          return true;
        }
      else
        {
          return false;
        }
    }
  // Сеть и хост определены адресами
  else if (!isname && !_domain)
    {
      DEBUG (DEBUG8, "[" << this << "] " << "address specifications");
      hostIP = IP::fromString (host);
      if (hostIP != NULL)
        {
          res = hasIP (*hostIP);
          if (res)
            {
              DEBUG (DEBUG4, "[" << this << "] Host " << host << " is part of net " << _net);
            }
          delete hostIP;
        }
      else
        res = false;

      return res;
    }
  // Различный способ указания
  else
    {
      // Если не преобразовывать имена, то ничего сделать не можем
      // потому просто вернем false
      if (!Proxy::isUseDNS ())
        {
          DEBUG (DEBUG8, "[" << this << "] " << "different specifications, no resolving");
          return false;
        }

      DEBUG (DEBUG8, "[" << this << "] " << "different specifications, need resolving");

      vector<string> hosts;
      vector<string>::iterator it;

      if (isname) // Сеть определена IP адресом, а хост именем
        {
          if (!DNS::getAddrsByName(host, hosts))
            return false;
        }
      else // Сеть определена именем, а хост адресом
        {
          if (!DNS::getNamesByAddr(host, hosts))
            return false;
        }

      for (it = hosts.begin (); it != hosts.end (); it++)
        {
          if (this->hasHost (*it))
            return true;
        }
      return false;
    }
}

bool Net::hasIP (const IP & ip)
{
  bool has_it = false;
  if (_ip == NULL)
    {
      WARNING ("Net does not have information about IP address");
      return has_it;
    }
  has_it = ((ip._ip.s_addr & _mask.s_addr) == _ip->_ip.s_addr);
  return has_it;
}

string Net::asString ()
{
  basic_stringstream < char >s;

  s << _net.c_str ();

  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << s);

  return s.str ();
}


Net *Net::fromString (const string & str)
{
  int slash_pos;
  int ok;
  int n;
  int dot_pos;
  string str_ip;
  string str_mask;
  Net *obj = new Net ();


  obj->_net = str;
  obj->_domain = Net::isDomain (str);
  if (obj->_domain)
    {
      return obj;
    }

  // Разбиваем на две части - адрес и маску
  slash_pos = str.find_first_of ('/');
  if (slash_pos >= 0)
    {
      str_ip = str.substr (0, slash_pos);
      str_mask = str.substr (slash_pos + 1, str.size () - slash_pos);
    }
  else
    {
      str_ip = str;
      str_mask = "";
    }

  obj->_ip = IP::fromString (str_ip);

  if (obj->_ip == NULL)
    return NULL;

  // Анализ маски
  if (!str_mask.empty ())
    {
      ok = 1;
      dot_pos = str_mask.find_first_of (".");
      if (dot_pos != -1)        // mask in the form /nn.nn.nn.nn
        {
          ok = inet_aton (str_mask.c_str (), &obj->_mask);
        }
      else
        {
          if (sscanf (str_mask.c_str (), "%d", &n) == 1 && (n > 0 && n <= 32))  // mask in the form /nn
            {
              ok = 1;
              obj->_mask.s_addr = htonl (IP_ANY << (32 - n));
            }
          else
            ok = 0;
        }
      if (ok == 0)
        {
          WARNING ("Incorrect IP mask: " << str);
          return NULL;
        }
    }
  else
    {
      obj->_mask.s_addr = htonl (IP_ANY);
    }

  return obj;
}

bool Net::isDomain (const string & host)
{
  bool res;
  uint i;

  res = true;
  for (i = 0; i < host.size (); i++)
    {
      if (!isdigit ((int) host[i]) && host[i] != '.' && host[i] != '/')
        {
          res = false;
          break;
        }
    }

  DEBUG (DEBUG9, host << " is " << ((res) ? "IP address" : "domain name"));
  return !res;
}

bool Net::isDomain ()
{
  return _domain;
}

/*
bool Net::resolve (const string &host, IP &ip)
{
  struct hostent *hostinfo;
  int i;

  hostinfo = gethostbyname (host.c_str ());
  if (hostinfo == NULL)
    {
      ERROR ("gethostbyname: " << h_errno);
      return false;
    }
  octets = hostinfo->h_length;
  for (i = 0; i < octets; i++)
    {
      ip[i] = 255 & hostinfo->h_addr[i];
    }
  hostinfo = NULL;
  return true;
}
*/
