#ifndef SAMS_DB_H
#define SAMS_DB_H

/*
 * Модуль предназначен для работы с СУБД через ODBC
 *
 *
 */

#include <strings.h>
#include <sql.h>
#include <sqlext.h>
#include <sqltypes.h>

#include "defines.h"

class DB {
public:
  DB();
  ~DB();
  bool Connect(const string datasource, const string username, const string password);
  bool isConnected();
  void Disconnect();
  bool AddCol(SQLUSMALLINT colNum, SQLSMALLINT dstType, SQLPOINTER dstValue, SQLLEN dstLength);
  bool SendQuery(const string query);
  SQLRETURN Fetch();
  SQLINTEGER RowsCount();

protected:
  void     reset();
  string   getErrorMessage(SQLSMALLINT handleType, SQLHANDLE handle);

  bool     connected;
  string   source;           //! ODBC Datasource
  string   user;             //! Username to use for connection
  string   pass;             //! Password to use for connection
  SQLHENV  env;              //! Handle for environment
  SQLHDBC  conn;             //! Handle for a connection
  SQLHSTMT statement;        //! Handle for a statement
};


#endif /* #ifndef SAMS_DB_H */
