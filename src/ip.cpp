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
#include <sstream>

#include "ip.h"
#include "debug.h"


IP::IP ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
  _ip.s_addr = htonl (IP_ANY);
  _str = "";
}


IP::~IP ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
}

IP *IP::fromString (const string & str)
{
  IP *obj = new IP ();

  if (obj->parseString (str))
    return obj;
  else
    {
      delete obj;
      return NULL;
    }
}

bool IP::parseString (const string & str)
{
  int ok;

  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << str << ")]");

  if (!str.empty ())
    {
      ok = inet_aton (str.c_str (), &_ip);
      if (ok == 0)
        {
          WARNING ("Incorrect IP: " << str);
          return false;
        }
      _str = str;
    }
  else
    {
      _ip.s_addr = htonl (IP_ANY);
    }
  return true;
}

bool IP::equal (const IP & ip)
{
  return (_ip.s_addr == ip._ip.s_addr);
}

string IP::asString () const
{
/*
  basic_stringstream < char >s;

  s << inet_ntoa (_ip);

  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << s.str ());

  return s.str ();
*/
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _str);
  return _str;
}

ostream & operator<< (ostream & out, const IP & ip)
{
  out << ip.asString ();
  return out;
}
