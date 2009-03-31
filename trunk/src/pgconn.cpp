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
#include "pgconn.h"

#ifdef USE_PQ

#include "debug.h"
#include "samsconfig.h"
#include "pgquery.h"

PgConn::PgConn():DBConn (DBConn::DB_PGSQL)
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  _pgconn = NULL;
}


PgConn::~PgConn()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  disconnect ();
}

bool PgConn::connect ()
{
  int err;
  _host = SamsConfig::getString (defDBSERVER, err);
  _dbname = SamsConfig::getString (defSAMSDB, err);
  _user = SamsConfig::getString (defDBUSER, err);
  _pass = SamsConfig::getString (defDBPASSWORD, err);

  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] " << "Connecting to " << _dbname << "@" << _host << " as " << _user);

  _pgconn = PQsetdbLogin(_host.c_str (), NULL, NULL, NULL, _dbname.c_str (), _user.c_str (), _pass.c_str ());

  if (!_pgconn)
    {
      ERROR ("Unable to initialize PostgreSQL handle.");
      return false;
    }

  if (PQstatus (_pgconn) != CONNECTION_OK)
    {
      ERROR ("PQsetdbLogin: " << PQerrorMessage (_pgconn));
      return false;
    }

  _connected = true;

  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Connected.");

  return true;
}

void PgConn::newQuery (DBQuery *& query)
{
  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "]");

  query = NULL;

  if (!_connected)
    return;

  query = new PgQuery( this );

  if (query)
    {
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] = " << query);
    }
  else
    {
      ERROR ("Unable to create new query.");
    }
}

void PgConn::disconnect ()
{
  if (!_connected)
    return;

  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] " << "Disconnecting from " << _dbname << "@" << _host);

  PQfinish (_pgconn);
  _pgconn = NULL;
  _connected = false;
}

#endif // #ifdef USE_PQ
