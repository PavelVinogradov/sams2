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

#include "odbcconn.h"

#ifdef USE_UNIXODBC

#include <sstream>
#include <string.h>

#include "samsconfig.h"
#include "odbcquery.h"
#include "debug.h"

ODBCConn::ODBCConn () : DBConn(DBConn::DB_UODBC)
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  _source = "";
  _user = "";
  _pass = "";
  _env = SQL_NULL_HENV;
  _hdbc = SQL_NULL_HDBC;
}


ODBCConn::~ODBCConn ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  disconnect ();
}


bool ODBCConn::connect ()
{
  int err;
  string errmsg;

  string datasource = SamsConfig::getString (defDBSOURCE, err);
  string username = SamsConfig::getString (defDBUSER, err);
  string password = SamsConfig::getString (defDBPASSWORD, err);

  DEBUG (DEBUG3, "[" << this << "->" << __FUNCTION__ << "] " << "Connecting to " << datasource << " as " << username);

  err = SQLAllocHandle (SQL_HANDLE_ENV, SQL_NULL_HANDLE, &_env);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      ERROR ("SQLAllocHandle " << err);
      _env = SQL_NULL_HENV;
      return false;
    }
  err = SQLSetEnvAttr (_env, SQL_ATTR_ODBC_VERSION, (void *) SQL_OV_ODBC3, 0);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      ERROR ("SQLSetEnvAttr " << err);
      SQLFreeHandle (SQL_HANDLE_ENV, _env);
      _env = SQL_NULL_HENV;
      _hdbc = SQL_NULL_HDBC;
      return false;
    }
  // 2. allocate connection handle, set timeout
  err = SQLAllocHandle (SQL_HANDLE_DBC, _env, &_hdbc);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      ERROR ("SQLAllocHandle " << err);
      SQLFreeHandle (SQL_HANDLE_ENV, _env);
      _env = SQL_NULL_HENV;
      _hdbc = SQL_NULL_HDBC;
      return false;
    }
  SQLSetConnectAttr (_hdbc, SQL_LOGIN_TIMEOUT, (SQLPOINTER *) 5, 0);
  // 3. Connect to a datasource
  err = SQLConnect (_hdbc, (SQLCHAR *) datasource.c_str (), SQL_NTS, (SQLCHAR *) username.c_str (), SQL_NTS, (SQLCHAR *) password.c_str (), SQL_NTS);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      errmsg = getErrorMessage (SQL_HANDLE_DBC, _hdbc);
      if (!errmsg.empty ())
        {
          ERROR ("SQLConnect [" << err << "] " << errmsg);
        }
      SQLFreeHandle (SQL_HANDLE_DBC, _hdbc);
      SQLFreeHandle (SQL_HANDLE_ENV, _env);
      _env = SQL_NULL_HENV;
      _hdbc = SQL_NULL_HDBC;
      return false;
    }
  _connected = true;
  _source = datasource;
  _user = username;
  _pass = password;

  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] " << "Connected.");

  return true;
}


void ODBCConn::newQuery (DBQuery *& query)
{
  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "]");

  query = NULL;

  if (!_connected)
    return;

  query = new ODBCQuery( this );

  if (query)
    {
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] = " << query);
    }
  else
    {
      ERROR ("Unable to create new query.");
    }
}


void ODBCConn::disconnect ()
{
  if (!_connected)
    return;

  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] " << "Disconnecting from " << _user << "@" << _source);

  unregisterAllQueries ();

  SQLDisconnect (_hdbc);
  SQLFreeHandle (SQL_HANDLE_DBC, _hdbc);
  SQLFreeHandle (SQL_HANDLE_ENV, _env);

  _source = "";
  _user = "";
  _pass = "";
  _env = SQL_NULL_HENV;
  _hdbc = SQL_NULL_HDBC;
  _connected = false;
}


bool ODBCConn::isConnected ()
{
  return _connected;
}


string ODBCConn::getErrorMessage (SQLSMALLINT handleType, SQLHANDLE handle)
{
  SQLCHAR ODBC_status[10];
  SQLINTEGER ODBC_err;
  SQLCHAR ODBC_msg[200];
  SQLSMALLINT ODBC_msg_len;
  string mess;
  const char *ptr;

  ODBC_err = 0;
  ODBC_msg_len = 0;
  memset (&ODBC_status[0], 0, 10);
  memset (&ODBC_msg[0], 0, 200);

  SQLGetDiagRec (handleType, handle, 1, ODBC_status, &ODBC_err, ODBC_msg, 100, &ODBC_msg_len);
  ptr = (const char *) &ODBC_msg[0];
  mess = ptr;
  return mess;
}

#endif // #ifdef USE_UNIXODBC
