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
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  _id = -1;
  _size = 0;
  _hit = 0;
}

SAMSUser::~SAMSUser ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
}

void SAMSUser::setId (long id)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << id << ")]");

  _id = id;
}

long SAMSUser::getId () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _id);
  return _id;
}

void SAMSUser::setNick (const string & nick)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << nick << ")]");
  _nick = nick;
}

string SAMSUser::getNick () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _nick);
  return _nick;
}

void SAMSUser::setDomain (const string & domain)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << domain << ")]");
  _domain = domain;
}

string SAMSUser::getDomain () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _domain);
  return _domain;
}

void SAMSUser::setIP (const string & ip)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << ip << ")]");
  _ip.parseString (ip);
}

IP SAMSUser::getIP () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _ip);
  return _ip;
}

void SAMSUser::setPassword (const string & pass)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << pass << ")]");
  _passwd = pass;
}

string SAMSUser::getPassword () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _passwd);
  return _passwd;
}

void SAMSUser::setEnabled (int enabled)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << enabled << ")]");
  _enabled = (usrStatus) enabled;
}

void SAMSUser::setEnabled (usrStatus enabled)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << (int) enabled << ")]");
  _enabled = enabled;
}

SAMSUser::usrStatus SAMSUser::getEnabled () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << (int) _enabled);
  return _enabled;
}

void SAMSUser::setSize (long long size)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << size << ")]");
  _size = size;
}

void SAMSUser::addSize (long size)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << size << ")]");
  _size += size;
  if (_hit > _size)
    {
      WARNING ("hit more then size (" << _hit << ">" << _size << ")");
    }
}

long long SAMSUser::getSize () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _size);
  return _size;
}

void SAMSUser::setHit (long long hit)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << hit << ")]");
  _hit = hit;
}

void SAMSUser::addHit (long hit)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << hit << ")]");
  _hit += hit;
}

long long SAMSUser::getHit () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _hit);
  return _hit;
}

void SAMSUser::setQuote (long quote)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << quote << ")]");
  _quote = quote;
}

long SAMSUser::getQuote () const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _quote);
  return _quote;
}

void SAMSUser::setShablonId (long id)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << id << ")]");
  _tpl_id = id;
}

long SAMSUser::getShablonId() const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _tpl_id);
  return _tpl_id;
}

void SAMSUser::setGroupId (long id)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << id << ")]");
  _grp_id = id;
}

long SAMSUser::getGroupId() const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _grp_id);
  return _grp_id;
}

string SAMSUser::asString () const
{
  string res = "";
  Template *tpl = Templates::getTemplate (getShablonId());
  if (!tpl)
    {
      WARNING ("User with id " << _id << " lost template");
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
}

ostream & operator<< (ostream & out, const SAMSUser & user)
{
  out << user.asString();
  return out;
}
