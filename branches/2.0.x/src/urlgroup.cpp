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

using namespace std;

#include "urlgroup.h"
#include "debug.h"

UrlGroup::UrlGroup (const long &id, const UrlGroup::accessType &access)
{
  _id = id;
  _type = access;
}

UrlGroup::~UrlGroup ()
{
}

long UrlGroup::getId ()
{
  DEBUG(DEBUG8, "[" << this << "->" << __FUNCTION__ << "] " << _id);
  return _id;
}

UrlGroup::accessType UrlGroup::getAccessType ()
{
  DEBUG(DEBUG8, "[" << this << "->" << __FUNCTION__ << "] " << ((_type==UrlGroup::ACC_ALLOW)?"allow":"deny"));
  return _type;
}

void UrlGroup::addUrl (const string & url)
{
  DEBUG(DEBUG8, "[" << this << "->" << __FUNCTION__ << "] " << url);
  _list.push_back (url);
}

string UrlGroup::asString () const
{
  string res = "";
  vector<string>::const_iterator it;

  for (it = _list.begin (); it != _list.end (); it++)
    {
      if ( ! res.empty () )
        res += " ";
      res += (*it);
    }

  return res;
}

ostream & operator<< (ostream & out, const UrlGroup & grp)
{
  out << grp.asString();
  return out;
}
