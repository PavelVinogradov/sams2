#include "samsdb.h"
#include "debug.h"

DB::DB()
{
  reset();
}

DB::~DB()
{
  Disconnect();
}

bool DB::Connect(const string datasource, const string username, const string password)
{
  long           err;
  string         errmsg;

  source = datasource;
  user = username;
  pass = password;

  DEBUG(DEBUG3, "Connecting to " << user << "@" << source);

  err = SQLAllocHandle(SQL_HANDLE_ENV, SQL_NULL_HANDLE, &env);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
     ERROR("SQLAllocHandle " << err);
     reset();
     return false;
  }
  err = SQLSetEnvAttr(env, SQL_ATTR_ODBC_VERSION, (void*)SQL_OV_ODBC3, 0);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
     ERROR("SQLSetEnvAttr " << err);
     SQLFreeHandle(SQL_HANDLE_ENV, env);
     reset();
     return false;
  }
  // 2. allocate connection handle, set timeout
  err = SQLAllocHandle(SQL_HANDLE_DBC, env, &conn);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
     ERROR("SQLAllocHandle " << err);
     SQLFreeHandle(SQL_HANDLE_ENV, env);
     reset();
     return false;
  }
  SQLSetConnectAttr(conn, SQL_LOGIN_TIMEOUT, (SQLPOINTER *)5, 0);
  // 3. Connect to a datasource
  err = SQLConnect(conn, (SQLCHAR*) source.c_str(), SQL_NTS,
                        (SQLCHAR*) user.c_str(), SQL_NTS,
                        (SQLCHAR*) pass.c_str(), SQL_NTS);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
     errmsg = getErrorMessage(SQL_HANDLE_DBC, conn);
     if (!errmsg.empty())
     {
       ERROR("SQLConnect [" << err << "] " << errmsg );
     }
     SQLFreeHandle(SQL_HANDLE_DBC, conn);
     SQLFreeHandle(SQL_HANDLE_ENV, env);
     reset();
     return false;
  }
  connected = true;
  DEBUG(DEBUG3, "Connected.");
  return true;
}

bool DB::isConnected()
{
  return connected;
}

void DB::Disconnect()
{
  if (!connected)
    return;
  DEBUG(DEBUG3, "Disconnecting from " << user << "@" << source);
  SQLDisconnect(conn);
  SQLFreeHandle(SQL_HANDLE_DBC, conn);
  SQLFreeHandle(SQL_HANDLE_ENV, env);
  reset();
}

void DB::reset()
{
  DEBUG(DEBUG3, "Reset to the initial state");
  source = "";
  user = "";
  pass = "";
  env = SQL_NULL_HENV;
  conn = SQL_NULL_HDBC;
  connected = false;
}

string DB::getErrorMessage(SQLSMALLINT handleType, SQLHANDLE handle)
{
  SQLCHAR        ODBC_status[10];
  SQLINTEGER     ODBC_err;
  SQLCHAR        ODBC_msg[200];
  SQLSMALLINT    ODBC_msg_len;
  string         mess;
  const char     *ptr;

  ODBC_err = 0;
  ODBC_msg_len = 0;
  memset(&ODBC_status[0], 0, 10);
  memset(&ODBC_msg[0], 0, 200);

  SQLGetDiagRec(handleType, handle, 1, ODBC_status, &ODBC_err, ODBC_msg, 100, &ODBC_msg_len);
  ptr = (const char*)&ODBC_msg[0];
  mess = ptr;
  return mess;
}















DBQuery::DBQuery(DB *database)
{
  DEBUG(DEBUG3, "");

  db = database;
  statement = SQL_NULL_HSTMT;
}

DBQuery::~DBQuery()
{
  DEBUG(DEBUG3, "");

  DestroyStatement();
}

bool DBQuery::BindCol(SQLUSMALLINT colNum, SQLSMALLINT dstType, SQLPOINTER dstValue, SQLLEN dstLength)
{
  long err;

  DEBUG(DEBUG3, "Column " << colNum);

  if (!db->connected)
  {
      ERROR("Not connected to a DB.");
      return false;
  }

  if (colNum == 1)
  {
    if (CreateStatement() != true)
    {
      return false;
    }
  }

  if (statement == SQL_NULL_HSTMT)
  {
      ERROR("SQL statement is NULL. Something goes wrong.");
      return false;
  }

  err = SQLBindCol(statement, colNum, dstType, dstValue, dstLength, 0);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
    ERROR("SQLBindCol [" << err << "] " << DB::getErrorMessage(SQL_HANDLE_STMT, statement) );
    return false;
  }

  return true;
}

bool DBQuery::BindParam(SQLUSMALLINT colNum, SQLSMALLINT ioType, SQLSMALLINT dstType, SQLSMALLINT srcType, SQLUINTEGER colSize, SQLSMALLINT numDigits, SQLPOINTER dstValue, SQLLEN dstLength)
{
  long err;

  DEBUG(DEBUG3, "Parameter " << colNum);

  if (!db->connected)
  {
      ERROR("Not connected to a DB.");
      return false;
  }

  if (statement == SQL_NULL_HSTMT)
  {
      ERROR("SQL statement is NULL. Probably, PrepareQuery has not been used.");
      return false;
  }

  err = SQLBindParameter(statement, colNum, ioType, dstType , srcType, colSize, numDigits, dstValue, dstLength, 0);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
    ERROR("SQLBindParameter [" << err << "] " << DB::getErrorMessage(SQL_HANDLE_STMT, statement) );
    return false;
  }

  return true;
}

bool DBQuery::PrepareQuery(const string query)
{
  long err;

  DEBUG(DEBUG3, query);

  if (!db->connected)
  {
      ERROR("Not connected to a DB.");
      return false;
  }
  if (CreateStatement() != true)
  {
    return false;
  }

  err = SQLPrepare(statement, (SQLCHAR*)query.c_str(), SQL_NTS);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
    ERROR("SQLPrepare [" << err << "] " << DB::getErrorMessage(SQL_HANDLE_STMT, statement) );
    return false;
  }
}

bool DBQuery::SendQuery()
{
  long err;

  DEBUG(DEBUG3, "");

  if (!db->connected)
  {
      ERROR("Not connected to a DB.");
      return false;
  }
  if (statement == SQL_NULL_HSTMT)
  {
      ERROR("SQL statement is NULL. Probably, PrepareQuery has not been used.");
      return false;
  }
  err = SQLExecute(statement);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
    ERROR("SQLExecute [" << err << "] " << DB::getErrorMessage(SQL_HANDLE_STMT, statement) );
    return false;
  }
  return true;
}

bool DBQuery::SendQueryDirect(const string query)
{
  long err;

  DEBUG(DEBUG3, query);

  if (!db->connected)
  {
      ERROR("Not connected to a DB.");
      return false;
  }
  if (statement == SQL_NULL_HSTMT)
  {
    if (CreateStatement() == false)
    {
      return false;
    }
  }

  err = SQLExecDirect(statement, (SQLCHAR*)query.c_str(), SQL_NTS);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
    ERROR("SQLExecDirect [" << err << "] " << DB::getErrorMessage(SQL_HANDLE_STMT, statement) );
    return false;
  }
  return true;
}

SQLRETURN DBQuery::Fetch()
{
  DEBUG(DEBUG3, "");

  if (!db->connected)
  {
      ERROR("Not connected to a DB.");
      return false;
  }
  return SQLFetch(statement);
}

SQLINTEGER DBQuery::RowsCount()
{
  long           err;
  SQLINTEGER     count;

  DEBUG(DEBUG3, "");

  if (!db->connected)
  {
      ERROR("Not connected to a DB.");
      return false;
  }
  if (statement == SQL_NULL_HSTMT)
  {
      ERROR("SQL statement is NULL. Probably, SendQuery or SendQueryDirect have not been used.");
      return false;
  }

  err = SQLRowCount(statement, &count);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
     ERROR("SQLRowCount [" << err << "] " << DB::getErrorMessage(SQL_HANDLE_STMT, statement) );
     count = 0;
  }
  return count;
}

void DBQuery::reset()
{
  long err;

  DEBUG(DEBUG3, "");

  if (statement != SQL_NULL_HSTMT)
  {
    err = SQLFreeStmt(statement, SQL_CLOSE);
    if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      WARNING("SQLFreeStmt [" << err << "] " << DB::getErrorMessage(SQL_HANDLE_DBC, db->conn) );
    }
    statement = SQL_NULL_HSTMT;
  }
}

bool DBQuery::CreateStatement()
{
  long err;

  DEBUG(DEBUG3, "");

  if (!db->isConnected())
  {
      ERROR("Not connected to a DB.");
      return false;
  }

  DestroyStatement();

  err = SQLAllocHandle(SQL_HANDLE_STMT, db->conn, &statement);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
    ERROR("SQLAllocHandle [" << err << "] " << DB::getErrorMessage(SQL_HANDLE_DBC, db->conn) );
    statement = SQL_NULL_HSTMT; // Just in case
    return false;
  }
  return true;
}

void DBQuery::DestroyStatement()
{
  long err;

  DEBUG(DEBUG3, "");

  if (statement != SQL_NULL_HSTMT)
  {
    err = SQLFreeHandle(SQL_HANDLE_STMT, statement);
    if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      WARNING("SQLFreeStmt [" << err << "] " << DB::getErrorMessage(SQL_HANDLE_DBC, db->conn) );
    }
    statement = SQL_NULL_HSTMT;
  }
}

