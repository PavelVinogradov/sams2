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
#include "mysqlconn.h"

#ifdef USE_MYSQL

#include "debug.h"
#include "samsconfig.h"

MYSQLConn::MYSQLConn ():DBConn (DBConn::DB_MYSQL)
{
  _mysql = NULL;
}


MYSQLConn::~MYSQLConn ()
{
  disconnect ();
}

bool MYSQLConn::connect ()
{
  int err;
  _host = SamsConfig::getString (defDBSERVER, err);
  _dbname = SamsConfig::getString (defSAMSDB, err);
  _user = SamsConfig::getString (defDBUSER, err);
  _pass = SamsConfig::getString (defDBPASSWORD, err);

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "Connecting to " << _dbname << "@" << _host << " as " << _user);

  _mysql = mysql_init (NULL);
  if (!_mysql)
    {
      ERROR ("Unable to initialize MYSQL handle.");
      return false;
    }

  if (!mysql_real_connect (_mysql, _host.c_str (), _user.c_str (), _pass.c_str (), _dbname.c_str (), 0, NULL, 0))
    {
      ERROR ("mysql_real_connect: " << mysql_error (_mysql));
      return false;
    }

  _connected = true;

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "Connected.");

  return true;
}

void MYSQLConn::disconnect ()
{
  if (!_connected)
    return;

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "Disconnecting from " << _dbname << "@" << _host);

  mysql_close (_mysql);
  _mysql = NULL;
  _connected = false;

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "Disconnected.");
}

#endif // #ifdef USE_MYSQL
