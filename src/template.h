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
#ifndef TEMPLATE_H
#define TEMPLATE_H

using namespace std;

#include <string>

#include "proxy.h"

class Template{
public:

  Template (long id, const string & name);

  ~Template ();

  long getId () const;

  void setAuth (const string & auth);

  void setAuth (Proxy::usrAuthType auth);

  Proxy::usrAuthType getAuth () const;

  void setQuote (long quote);

  long getQuote () const;

  void setAllDeny(bool alldeny);

  void addRestriction (const string & t, const string & u);

  bool isUrlAllowed (const string & url) const;

private:
  long _id;
  string _name;
  Proxy::usrAuthType _auth;
  long _quote;
  bool _alldeny;
};

#endif
