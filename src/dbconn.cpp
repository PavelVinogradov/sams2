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
#include <ctype.h>

#include "config.h"

#include "dbconn.h"
#include "dbquery.h"
#include "samsconfig.h"

#include "debug.h"

DBConn::DBConn (DBEngine engine)
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
  _connected = false;
  _engine = engine;
}


DBConn::~DBConn ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
}


bool DBConn::connect ()
{
  return false;
}


void DBConn::newQuery (DBQuery *& query)
{
  query = NULL;
  return;
}


bool DBConn::isConnected ()
{
  return _connected;
}


DBConn::DBEngine DBConn::getEngine()
{
  return _engine;
}


void DBConn::disconnect ()
{
}

void DBConn::registerQuery(DBQuery * query)
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  if (query == NULL)
    return;

  basic_stringstream < char >key;
  map < string, DBQuery * >::iterator it;

  key << query;

  it = _queries.find (key.str ());
  if (it != _queries.end ())
    {
      WARNING ("[" << this << "->" << __FUNCTION__ << "] " << "Query " << key.str () << " already registered.");
      return;
    }

  _queries[key.str ()] = query;

//  query->_conn = this;

  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Query " << key.str () << " registered.");
}

void DBConn::unregisterQuery(DBQuery * query)
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  if (query == NULL)
    return;

  basic_stringstream < char >key;
  map < string, DBQuery * >::iterator it;

  key << query;

  it = _queries.find (key.str ());
  if (it == _queries.end ())
    {
      WARNING ("[" << this << "->" << __FUNCTION__ << "] " << "Query " << key.str () << " is not registered.");
      return;
    }
  _queries.erase (it);
//  query->destroy ();
  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Query " << key.str () << " unregistered.");
}

void DBConn::unregisterAllQueries ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  map < string, DBQuery * > tmp = _queries;
  map < string, DBQuery * >::iterator it;

  for (it = tmp.begin (); it != tmp.end (); it++)
    {
      delete (*it).second;
    }
  _queries.clear ();
  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "All queries unregistered.");
}
