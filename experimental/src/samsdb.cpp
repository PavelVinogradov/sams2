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
  long           err;         // result of functions
  string         errmsg;

  source = datasource;
  user = username;
  pass = password;

  DEBUG(DEBUG3, "Connecting to " << user << "@" << source);

  // 1. allocate Environment handle and register version
  err = SQLAllocHandle(SQL_HANDLE_ENV, SQL_NULL_HANDLE, &env);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
     ERROR("SQLAllocHandle " << err);
     return false;
  }
  err = SQLSetEnvAttr(env, SQL_ATTR_ODBC_VERSION, (void*)SQL_OV_ODBC3, 0);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
     ERROR("SQLSetEnvAttr " << err);
     SQLFreeHandle(SQL_HANDLE_ENV, env);
     return false;
  }
  // 2. allocate connection handle, set timeout
  err = SQLAllocHandle(SQL_HANDLE_DBC, env, &conn);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
     ERROR("SQLAllocHandle " << err);
     SQLFreeHandle(SQL_HANDLE_ENV, env);
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
     return false;
  }
  connected = true;
  DEBUG(DEBUG3, "Connected.");
  return true;
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

bool DB::isConnected()
{
  return connected;
}

void DB::reset()
{
  source = "";
  user = "";
  pass = "";
  env = SQL_TYPE_NULL;
  conn = SQL_TYPE_NULL;
  statement = SQL_TYPE_NULL;
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

bool DB::AddCol(SQLUSMALLINT colNum, SQLSMALLINT dstType, SQLPOINTER dstValue, SQLLEN dstLength)
{
  long err;
  if (!connected)
  {
      ERROR("Not connected to a DB.");
      return false;
  }
  if (statement == SQL_TYPE_NULL)
  {
    err = SQLAllocHandle(SQL_HANDLE_STMT, conn, &statement);
    if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
    {
      ERROR("SQLAllocHandle [" << err << "] " << getErrorMessage(SQL_HANDLE_DBC, conn) );
      return false;
    }
  }

  err = SQLBindCol(statement, colNum, dstType, dstValue, dstLength, 0);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
    ERROR("SQLBindCol [" << err << "] " << getErrorMessage(SQL_HANDLE_STMT, statement) );
    return false;
  }

  return true;
}

bool DB::SendQuery(const string query)
{
  long err;

  if (!connected)
  {
      ERROR("Not connected to a DB.");
      return false;
  }
  err = SQLExecDirect(statement, (SQLCHAR*)query.c_str(), SQL_NTS);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
    ERROR("SQLExecDirect [" << err << "] " << getErrorMessage(SQL_HANDLE_STMT, statement) );
    return false;
  }
  return true;
}

SQLRETURN DB::Fetch()
{
  if (!connected)
  {
      ERROR("Not connected to a DB.");
      return false;
  }
  return SQLFetch(statement);
}

SQLINTEGER DB::RowsCount()
{
  long           err;
  SQLINTEGER     count;

  err = SQLRowCount(statement, &count);
  if ((err != SQL_SUCCESS) && (err != SQL_SUCCESS_WITH_INFO))
  {
     ERROR("SQLRowCount [" << err << "] " << getErrorMessage(SQL_HANDLE_STMT, statement) );
     count = 0;
  }
  return count;
}

