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
#include "template.h"

#include "debug.h"

Template::Template (long id)
{
  _id = id;
  _period_type = Template::PERIOD_MONTH;
}


Template::~Template ()
{
  _times.clear();
}

long Template::getId () const
{
  return _id;
}

void Template::setAuth (const string & auth)
{
  if (auth == "ip")
    _auth = Proxy::AUTH_IP;
  else if (auth == "ncsa")
    _auth = Proxy::AUTH_NCSA;
  else if (auth == "ntlm")
    _auth = Proxy::AUTH_NTLM;
  else if (auth == "adld")
    _auth = Proxy::AUTH_ADLD;
  else if (auth == "ldap")
    _auth = Proxy::AUTH_LDAP;
  else
    {
      ERROR ("Unknown authentication scheme: " << auth);
    }
}

void Template::setAuth (Proxy::usrAuthType auth)
{
  _auth = auth;
}

Proxy::usrAuthType Template::getAuth () const
{
  return _auth;
}

void Template::setQuote (long quote)
{
  _quote = quote;
}

long Template::getQuote () const
{
  return _quote;
}

void Template::setPeriod (Template::PeriodType ptype, long days)
{
  _period_type = ptype;
  _period_days = days;
}

Template::PeriodType Template::getPeriodType ()
{
  return _period_type;
}

void Template::setClearDate (const string & dateSpec)
{
  _clear_date = dateSpec + " 00:00:00";
}

bool Template::getClearDate (struct tm & clear_date)
{
  if (_period_type != Template::PERIOD_CUSTOM)
    return false;
  if (_clear_date.empty ())
    return false;

  char *rest;
  rest = strptime (_clear_date.c_str (), "%Y-%m-%d %H:%M:%S", &clear_date);
  if (rest == NULL)
    {
      ERROR ("Invalid date specification: " << _clear_date);
      return false;
    }
  return true;
}

void Template::setAllDeny (bool alldeny)
{
  _alldeny = alldeny;
}

void Template::addTimeRange (long id)
{
  DEBUG (DEBUG_TPL, "[" << __FUNCTION__ << "] " << id);
  _times.push_back (id);
}

vector <long> Template::getTimeRangeIds ()
{
  return _times;
}

bool Template::isUrlAllowed (const string &url) const
{
  return true;
}
