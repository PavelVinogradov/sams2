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

#include "squidlogline.h"
#include "debug.h"
#include "tools.h"
#include "stdlib.h"

#include <vector>

SquidLogLine::SquidLogLine ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
  _valid = false;
}


SquidLogLine::~SquidLogLine ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
}


bool SquidLogLine::setLine (const string & line)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << line << ")]");

  _valid = false;
  _line = line;
  parseLine ();
  return _valid;
}

struct tm SquidLogLine::getTime ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = ...");
  return _time;
}

int SquidLogLine::getBusytime ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _busytime);
  return _busytime;
}

string SquidLogLine::getIP ()
{
  return _ip;
}

string SquidLogLine::getIdent ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _ident);
  return _ident;
}

string SquidLogLine::getUrl ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _url);
  return _url;
}

string SquidLogLine::getMethod ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _method);
  return _method;
}

long SquidLogLine::getSize ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _size);
  return _size;
}

SquidLogLine::logCacheResult SquidLogLine::getCacheResult ()
{
  return _cacheResult;
}

SquidLogLine::logCacheResult SquidLogLine::parseCacheResult (const string & cr)
{
  logCacheResult res;

  if (cr == "TCP_HIT")
    res = TCP_HIT;
  else if (cr == "TCP_MEM_HIT")
    res = TCP_MEM_HIT;
  else if (cr == "TCP_NEGATIVE_HIT")
    res = TCP_NEGATIVE_HIT;
  else if (cr == "TCP_MISS")
    res = TCP_MISS;
  else if (cr == "TCP_REFRESH_HIT")
    res = TCP_REFRESH_HIT;
  else if (cr == "TCP_REFRESH_MODIFIED")
    res = TCP_REFRESH_MODIFIED;
  else if (cr == "TCP_REFRESH_UNMODIFIED")
    res = TCP_REFRESH_UNMODIFIED;
  else if (cr == "TCP_REF_FAIL_HIT")
    res = TCP_REF_FAIL_HIT;
  else if (cr == "TCP_REFRESH_MISS")
    res = TCP_REFRESH_MISS;
  else if (cr == "TCP_CLIENT_REFRESH")
    res = TCP_CLIENT_REFRESH;
  else if (cr == "TCP_CLIENT_REFRESH_MISS")
    res = TCP_CLIENT_REFRESH_MISS;
  else if (cr == "TCP_IMS_HIT")
    res = TCP_IMS_HIT;
  else if (cr == "TCP_IMS_MISS")
    res = TCP_IMS_MISS;
  else if (cr == "TCP_SWAPFAIL")
    res = TCP_SWAPFAIL;
  else if (cr == "TCP_SWAPFAIL_MISS")
    res = TCP_SWAPFAIL_MISS;
  else if (cr == "TCP_DENIED")
    res = TCP_DENIED;
  else if (cr == "UDP_HIT")
    res = UDP_HIT;
  else if (cr == "UDP_HIT_OBJ")
    res = UDP_HIT_OBJ;
  else if (cr == "UDP_MISS")
    res = UDP_MISS;
  else if (cr == "UDP_DENIED")
    res = UDP_DENIED;
  else if (cr == "UDP_INVALID")
    res = UDP_INVALID;
  else if (cr == "UDP_RELOADING")
    res = UDP_RELOADING;
  else if (cr == "ERR_CLIENT_ABORT")
    res = ERR_CLIENT_ABORT;
  else if (cr == "ERR_NO_CLIENTS")
    res = ERR_NO_CLIENTS;
  else if (cr == "ERR_READ_ERROR")
    res = ERR_READ_ERROR;
  else if (cr == "ERR_CONNECT_FAIL")
    res = ERR_CONNECT_FAIL;
  else
    {
      WARNING ("Unknown cache result " << cr);
      res = CR_UNKNOWN;
    }

  return res;
}

SquidLogLine::logPeerStatus SquidLogLine::parsePeerStatus (const string & ps)
{
  logPeerStatus res;

  if (ps == "DIRECT")
    res = DIRECT;
  else if (ps == "FIREWALL_IP_DIRECT")
    res = FIREWALL_IP_DIRECT;
  else if (ps == "FIRST_PARENT_MISS")
    res = FIRST_PARENT_MISS;
  else if (ps == "FIRST_UP_PARENT")
    res = FIRST_UP_PARENT;
  else if (ps == "LOCAL_IP_DIRECT")
    res = LOCAL_IP_DIRECT;
  else if (ps == "SIBLING_HIT")
    res = SIBLING_HIT;
  else if (ps == "NO_DIRECT_FAIL")
    res = NO_DIRECT_FAIL;
  else if (ps == "NO_PARENT_DIRECT")
    res = NO_PARENT_DIRECT;
  else if (ps == "PARENT_HIT")
    res = PARENT_HIT;
  else if (ps == "SINGLE_PARENT")
    res = SINGLE_PARENT;
  else if (ps == "SOURCE_FASTEST")
    res = SOURCE_FASTEST;
  else if (ps == "PARENT_UDP_HIT_OBJ")
    res = PARENT_UDP_HIT_OBJ;
  else if (ps == "SIBLING_UDP_HIT_OBJ")
    res = SIBLING_UDP_HIT_OBJ;
  else if (ps == "PASSTHROUGH_PARENT")
    res = PASSTHROUGH_PARENT;
  else if (ps == "SSL_PARENT_MISS")
    res = SSL_PARENT_MISS;
  else if (ps == "DEFAULT_PARENT")
    res = DEFAULT_PARENT;
  else if (ps == "ROUNDROBIN_PARENT")
    res = ROUNDROBIN_PARENT;
  else if (ps == "CLOSEST_PARENT_MISS")
    res = CLOSEST_PARENT_MISS;
  else if (ps == "CLOSEST_DIRECT")
    res = CLOSEST_DIRECT;
  else
    res = PS_UNKNOWN;

  return res;
}

void SquidLogLine::parseLine ()
{
  vector < string > fields;
  vector < string > cacheResVal;
  int cnt_fields;

  if (_line.size () < 60)
    {
      ERROR ("Input string has less then 60 chars (" << _line.size () << ")");
      return;
    }
  if (_line[10] != '.' || _line[14] != ' ')
    {
      ERROR ("Input string has wrong format: " << _line);
      return;
    }
  Split (_line, " \t", fields);
  cnt_fields = fields.size ();
  if (cnt_fields < 10)
    {
      ERROR ("Input string has less then 10 fields (" << cnt_fields << ")");
      return;
    }
  if (cnt_fields > 10)
    {
      ERROR ("Input string has more then 10 fields (" << cnt_fields << ")");
      return;
    }

  _valid = true;

  char *rest;
  rest = strptime (fields[0].c_str (), "%s", &_time);
  if (rest == NULL)
    {
      ERROR ("Invalid date and time specification: " << fields[0]);
    }

  _busytime = atoi (fields[1].c_str ());

  _ip = fields[2];

  Split (fields[3], "/", cacheResVal);
  _cacheResult = parseCacheResult (cacheResVal[0]);

  _size = atol (fields[4].c_str ());

  _method = fields[5];

  _url = fields[6];

  _ident = url_decode(fields[7]);

//1) 1192923767.512
//   UNIX time stamp as Coordinated Universal Time (UTC) seconds with a millisecond resolution.
//2) 215
//   Length of time in milliseconds that the cache was busy with the transaction.
//   Note: Entries are logged after the reply has been sent,
//         not during the lifetime of the transaction.
//3) 192.168.1.88
//   IP address of the requesting instance.
//4) TCP_MISS/200
//   TCP_CLIENT_REFRESH_MISS, TCP_DENIED, TCP_HIT, TCP_IMS_HIT, TCP_MEM_HIT
//   TCP_MISS, TCP_NEGATIVE_HIT, TCP_REFRESH_HIT, TCP_REFRESH_MISS

//    Two entries separated by a slash.
//    The first entry contains information on the result of the transaction:
//    the kind of request, how it was satisfied, or in what way it failed.
//    The second entry contains the HTTP result codes.
//5) 708
//   Amount of data delivered to the client.
//   This does not constitute the net object size, because headers are also counted.
//   Also, failed requests may deliver an error page, the size of which is also logged here.
//6) CONNECT, GET, HEAD, OPTIONS, POST, PROPFIND
//   Request method to obtain an object for example, GET.
//7) http://www.mtsoft.ru/advert/?
//   URL requested.
//8) fred
//   Contains the authentication server's identification or lookup names of the requesting client.
//   This field will always be a "-" (dash).
//9) DIRECT/217.16.16.116
//   Two entries separated by a slash.
//   The first entry represents a code that explains how the request was handled,
//   for example, by forwarding it to a peer, or returning the request to the source.
//   The second entry contains the name of the host from which the object was requested.
//   This host may be the origin site, a parent, or any other peer.
//   Also note that the host name may be numerical.
//10) text/html
//    Content type of the object as seen in the HTTP reply header.
//    In the ACNS 4.1 software, this field will always contain a "-" (dash).
}

string SquidLogLine::toString (logCacheResult cr)
{
  string res;

  if (cr == TCP_HIT)
    res = "TCP_HIT";
  else if (cr == TCP_MEM_HIT)
    res = "TCP_MEM_HIT";
  else if (cr == TCP_NEGATIVE_HIT)
    res = "TCP_NEGATIVE_HIT";
  else if (cr == TCP_MISS)
    res = "TCP_MISS";
  else if (cr == TCP_REFRESH_HIT)
    res = "TCP_REFRESH_HIT";
  else if (cr == TCP_REFRESH_MODIFIED)
    res = "TCP_REFRESH_MODIFIED";
  else if (cr == TCP_REFRESH_UNMODIFIED)
    res = "TCP_REFRESH_UNMODIFIED";
  else if (cr == TCP_REF_FAIL_HIT)
    res = "TCP_REF_FAIL_HIT";
  else if (cr == TCP_REFRESH_MISS)
    res = "TCP_REFRESH_MISS";
  else if (cr == TCP_CLIENT_REFRESH)
    res = "TCP_CLIENT_REFRESH";
  else if (cr == TCP_CLIENT_REFRESH_MISS)
    res = "TCP_CLIENT_REFRESH_MISS";
  else if (cr == TCP_IMS_HIT)
    res = "TCP_IMS_HIT";
  else if (cr == TCP_IMS_MISS)
    res = "TCP_IMS_MISS";
  else if (cr == TCP_SWAPFAIL)
    res = "TCP_SWAPFAIL";
  else if (cr == TCP_SWAPFAIL_MISS)
    res = "TCP_SWAPFAIL_MISS";
  else if (cr == TCP_DENIED)
    res = "TCP_DENIED";
  else if (cr == UDP_HIT)
    res = "UDP_HIT";
  else if (cr == UDP_HIT_OBJ)
    res = "UDP_HIT_OBJ";
  else if (cr == UDP_MISS)
    res = "UDP_MISS";
  else if (cr == UDP_DENIED)
    res = "UDP_DENIED";
  else if (cr == UDP_INVALID)
    res = "UDP_INVALID";
  else if (cr == UDP_RELOADING)
    res = "UDP_RELOADING";
  else if (cr == ERR_CLIENT_ABORT)
    res = "ERR_CLIENT_ABORT";
  else if (cr == ERR_NO_CLIENTS)
    res = "ERR_NO_CLIENTS";
  else if (cr == ERR_READ_ERROR)
    res = "ERR_READ_ERROR";
  else if (cr == ERR_CONNECT_FAIL)
    res = "ERR_CONNECT_FAIL";
  else
    res = "CR_UNKNOWN";

  return res;
}
