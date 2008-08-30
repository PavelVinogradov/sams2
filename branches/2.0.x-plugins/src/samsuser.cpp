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

#include "samsuser.h"
#include "debug.h"
#include "templates.h"
#include "template.h"
#include "proxy.h"

string SAMSUser::toString (usrStatus s)
{
  string res;
  switch (s)
    {
    case STAT_OFF:
      res = "off";
      break;
    case STAT_INACTIVE:
      res = "inactive";
      break;
    case STAT_ACTIVE:
      res = "active";
      break;
    default:
      res = "unknown";
      break;
    }
  return res;
}

SAMSUser::SAMSUser ()
{
  _id = -1;
  _size = 0;
  _hit = 0;
}

SAMSUser::~SAMSUser ()
{
}

void SAMSUser::setId (long id)
{
  _id = id;
}

long SAMSUser::getId () const
{
  return _id;
}

void SAMSUser::setNick (const string & nick)
{
  _nick = nick;
}

string SAMSUser::getNick () const
{
  return _nick;
}

void SAMSUser::setDomain (const string & domain)
{
  _domain = domain;
}

string SAMSUser::getDomain () const
{
  return _domain;
}

void SAMSUser::setIP (const string & ip)
{
  DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] " << ip);
  _ip.parseString (ip);
}

IP SAMSUser::getIP () const
{
  return _ip;
}

void SAMSUser::setEnabled (int enabled)
{
  DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] " << enabled);
  _enabled = (usrStatus) enabled;
}

void SAMSUser::setEnabled (usrStatus enabled)
{
  DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] " << (int) enabled);
  _enabled = enabled;
}

SAMSUser::usrStatus SAMSUser::getEnabled () const
{
  return _enabled;
}

void SAMSUser::setSize (long long size)
{
  DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] " << size);
  _size = size;
}

void SAMSUser::addSize (long size)
{
  _size += size;
  if (_hit > _size)
    {
      WARNING ("hit more then size (" << _hit << ">" << _size << ")");
    }
}

long long SAMSUser::getSize () const
{
  return _size;
}

void SAMSUser::setHit (long long hit)
{
  DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] " << hit);
  _hit = hit;
}

void SAMSUser::addHit (long hit)
{
  _hit += hit;
}

long long SAMSUser::getHit () const
{
  return _hit;
}

void SAMSUser::setQuote (long quote)
{
  DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] " << quote);
  _quote = quote;
}

long SAMSUser::getQuote () const
{
  return _quote;
}

void SAMSUser::setShablonId (long id)
{
  DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] " << id);
  _tpl_id = id;
}

long SAMSUser::getShablonId() const
{
  return _tpl_id;
}

void SAMSUser::setGroupId (long id)
{
  DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] " << id);
  _grp_id = id;
}

long SAMSUser::getGroupId() const
{
  return _grp_id;
}

string SAMSUser::asString () const
{
  string res = "";
  Template *tpl = Templates::getTemplate (getShablonId());
  if (!tpl)
    {
      WARNING ("User " << getId() << "lost template");
      return res;
    }

  if (tpl->getAuth() == Proxy::AUTH_IP)
    {
      res = _ip.asString ();
    }
  else
    {
      if (!_domain.empty ())
        {
          res = _domain + "\\";
        }
      res += _nick;
    }

  return res;
/*
  basic_stringstream < char >s;

  if (!_domain.empty ())
    {
      s << _domain << "\\";
    }

  s << _nick << " ";
  s << _ip.asString () << " ";
  s << _quote << " ";
  s << _size << " ";
  s << _hit << " ";

  return s.str ();
*/
}

ostream & operator<< (ostream & out, const SAMSUser & user)
{
/*
  Template *tpl = Templates::getTemplate (user.getShablonId());
  if (!tpl)
    {
      out << "user lost template";
      return out;
    }

  if (tpl->getAuth() == Proxy::AUTH_IP)
    {
      out << user._ip.asString ();
    }
  else
    {
      if (!user._domain.empty ())
        {
          out << user._domain << "\\";
        }
      out << user._nick;
    }
*/
  out << user.asString();
  return out;
}
