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
#include "odbcquery.h"

#ifdef USE_UNIXODBC

#include "odbcconn.h"
#include "debug.h"

ODBCQuery::ODBCQuery (ODBCConn * conn)
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  _conn = NULL;
  statement = SQL_NULL_HSTMT;

  if (!conn)
    return;

  _conn = conn;
  _conn->registerQuery (this);
}


ODBCQuery::~ODBCQuery ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  destroy ();

  if (_conn)
    ((ODBCConn *) _conn)->unregisterQuery (this);
}

bool ODBCQuery::bindCol (uint colNum, DBQuery::VarType dstType, void *buf, int bufLen)
{
  SQLSMALLINT dType;
  switch (dstType)
    {
      case DBQuery::T_LONG:
        dType = SQL_C_LONG;
        break;
      case DBQuery::T_LONGLONG:
        dType = SQL_C_UBIGINT;
        break;
      case DBQuery::T_CHAR:
        dType = SQL_C_CHAR;
        break;
      default:
        return false;
        break;
    }

  return bindCol ((SQLUSMALLINT) colNum, dType, (SQLPOINTER) buf, (SQLLEN) bufLen);
}

bool ODBCQuery::bindCol (SQLUSMALLINT colNum, SQLSMALLINT dstType, SQLPOINTER dstValue, SQLLEN dstLength)
{
  long err;

  if (_conn == NULL)
    {
      ERROR ("No connection associated. Invalid query.");
      return false;
    }
  if (!((ODBCConn *) _conn)->_connected)
    {
      ERROR ("Not connected to a DB.");
      return false;
    }

  if (colNum == 1)
    {
      if (createStatement () != true)
        {
          return false;
        }
    }

  if (statement == SQL_NULL_HSTMT)
    {
      ERROR ("SQL statement is NULL. Something goes wrong.");
      return false;
    }

  err = SQLBindCol (statement, colNum, dstType, dstValue, dstLength, 0);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      ERROR ("SQLBindCol [" << err << "] " << ODBCConn::getErrorMessage (SQL_HANDLE_STMT, statement));
      return false;
    }

  return true;
}

bool ODBCQuery::bindParam (uint num, DBQuery::VarType dstType, void *buf, int bufLen)
{
  SQLSMALLINT dType;
  SQLSMALLINT sType;
  switch (dstType)
    {
      case DBQuery::T_LONG:
        dType = SQL_C_LONG;
        sType = SQL_INTEGER;
        break;
      case DBQuery::T_LONGLONG:
        dType = SQL_C_UBIGINT;
        sType = SQL_BIGINT;
        break;
      case DBQuery::T_CHAR:
        dType = SQL_C_CHAR;
        sType = SQL_VARCHAR;
        break;
      default:
        return false;
        break;
    }

  return bindParam ((SQLUSMALLINT) num, dType, sType, (SQLPOINTER) buf, (SQLLEN) bufLen);
}

bool ODBCQuery::bindParam (SQLUSMALLINT num, SQLSMALLINT dstType, SQLSMALLINT srcType, SQLPOINTER dstValue, SQLLEN dstLength)
{
  long err;

  if (_conn == NULL)
    {
      ERROR ("No connection associated. Invalid query.");
      return false;
    }
  if (!((ODBCConn *) _conn)->_connected)
    {
      ERROR ("Not connected to a DB.");
      return false;
    }

  if (statement == SQL_NULL_HSTMT)
    {
      ERROR ("SQL statement is NULL. Probably, PrepareQuery has not been used.");
      return false;
    }

  err = SQLBindParameter (statement, num, SQL_PARAM_INPUT, dstType, srcType, 0, 0, dstValue, dstLength, 0);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      ERROR ("SQLBindParameter [" << err << "] " << ODBCConn::getErrorMessage (SQL_HANDLE_STMT, statement));
      return false;
    }

  return true;
}


bool ODBCQuery::prepareQuery (const string & query)
{
  long err;

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << query);

  if (_conn == NULL)
    {
      ERROR ("No connection associated. Invalid query.");
      return false;
    }
  if (!((ODBCConn *) _conn)->_connected)
    {
      ERROR ("Not connected to a DB.");
      return false;
    }
  if (!createStatement ())
    {
      return false;
    }

  err = SQLPrepare (statement, (SQLCHAR *) query.c_str (), SQL_NTS);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      ERROR ("SQLPrepare [" << err << "] " << ODBCConn::getErrorMessage (SQL_HANDLE_STMT, statement));
      return false;
    }

  return true;
}

bool ODBCQuery::sendQuery ()
{
  long err;

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");

  if (_conn == NULL)
    {
      ERROR ("No connection associated. Invalid query.");
      return false;
    }
  if (!((ODBCConn *) _conn)->_connected)
    {
      ERROR ("Not connected to a DB.");
      return false;
    }
  if (statement == SQL_NULL_HSTMT)
    {
      ERROR ("SQL statement is NULL. Probably, PrepareQuery has not been used.");
      return false;
    }



  err = SQLExecute (statement);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      ERROR ("SQLExecute [" << err << "] " << ODBCConn::getErrorMessage (SQL_HANDLE_STMT, statement));
      return false;
    }
  return true;
}

bool ODBCQuery::sendQueryDirect (const string & query)
{
  long err;

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << query);

  if (_conn == NULL)
    {
      ERROR ("No connection associated. Invalid query.");
      return false;
    }
  if (!((ODBCConn *) _conn)->_connected)
    {
      ERROR ("Not connected to a DB.");
      return false;
    }
  if (statement == SQL_NULL_HSTMT)
    {
      if (createStatement () == false)
        {
          return false;
        }
    }

  err = SQLExecDirect (statement, (SQLCHAR *) query.c_str (), SQL_NTS);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      ERROR ("SQLExecDirect [" << err << "] " << ODBCConn::getErrorMessage (SQL_HANDLE_STMT, statement));
      return false;
    }
  return true;
}

bool ODBCQuery::fetch ()
{

  if (_conn == NULL)
    {
      ERROR ("No connection associated. Invalid query.");
      return false;
    }
  if (!((ODBCConn *) _conn)->_connected)
    {
      ERROR ("Not connected to a DB.");
      return false;
    }
  bool ok;

  if (SQLFetch (statement) != SQL_NO_DATA)
    ok = true;
  else
    ok = false;

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << ((ok) ? ("ok") : ("failed")));
  return ok;
}

long ODBCQuery::affectedRows ()
{
  long err;
  SQLLEN count;

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");

  if (_conn == NULL)
    {
      ERROR ("No connection associated. Invalid query.");
      return false;
    }
  if (!((ODBCConn *) _conn)->_connected)
    {
      ERROR ("Not connected to a DB.");
      return false;
    }
  if (statement == SQL_NULL_HSTMT)
    {
      ERROR ("SQL statement is NULL. Probably, SendQuery or SendQueryDirect have not been used.");
      return false;
    }

  err = SQLRowCount (statement, &count);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      ERROR ("SQLRowCount [" << err << "] " << ODBCConn::getErrorMessage (SQL_HANDLE_STMT, statement));
      count = 0;
    }
  DEBUG (DEBUG_DB, "[" << this << "] Result is " << count);
  return (long) count;
}

bool ODBCQuery::createStatement ()
{
  long err;

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");

  if (_conn == NULL)
    {
      ERROR ("No connection associated. Invalid query.");
      return false;
    }
  if (!((ODBCConn *) _conn)->_connected)
    {
      ERROR ("Not connected to a DB.");
      return false;
    }

  destroy ();

  err = SQLAllocHandle (SQL_HANDLE_STMT, _conn->_hdbc, &statement);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      ERROR ("SQLAllocHandle [" << err << "] " << ODBCConn::getErrorMessage (SQL_HANDLE_DBC, _conn->_hdbc));
      statement = SQL_NULL_HSTMT;       // Just in case
      return false;
    }

  return true;
}

void ODBCQuery::destroy ()
{
  long err;
  if (statement != SQL_NULL_HSTMT)
    {
      DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");
      err = SQLFreeHandle (SQL_HANDLE_STMT, statement);
      if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
        {
          WARNING ("SQLFreeHandle [" << err << "] " << ODBCConn::getErrorMessage (SQL_HANDLE_DBC, _conn->_hdbc));
        }
      statement = SQL_NULL_HSTMT;
    }
}

#endif // #ifdef USE_UNIXODBC
