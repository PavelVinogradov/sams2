#ifndef SAMS_DB_H
#define SAMS_DB_H

/*
 * ������ ������������ ��� ������ � ���� ����� ODBC
 *
 *
 */

#include <strings.h>
#include <sql.h>
#include <sqlext.h>
#include <sqltypes.h>

#include "defines.h"

class DB {
friend class DBQuery;
public:

  /*! ����������� ������.
   *
   */
  DB();

  /*! ���������� ������. �������������� ��������� ����������,
   *  ���� ��� ���� �����������, � ����������� ��� ������������ �������.
   *
   */
  ~DB();

  /*! ������������ � \c datasource ��������� \c username � \c password.
   *  ���� ������ �������� �������� � �������������� ����� ������������
   *  � ������ �� ���������, �� �� ����� ������� �������.
   *
   *  \code
   *  DB mydb;
   *  mydb.Connect("datasource", "user", "password");
   *  mydb.Disconnect();
   *  \endcode
   *
   *  \param datasource �������� ������, ��� �������� � ODBC.
   *  \param username ��� ������������.
   *  \param password ������.
   *  \retval true ���� ����������� ������ �������.
   *  \retval false ��� ������.
   */
  bool Connect(const string datasource, const string username, const string password);

  /*! ���������� ��������� ����������.
   *
   *  \retval true ���������� �����������.
   *  \retval false ���������� �����������.
   */
  bool isConnected();

  /*! ��������� ���������� � ���������� ������ � ����������� ��� ������������ �������.
   *
   */
  void Disconnect();

  /*! ���������� ��������� � ��������� ������.
   *
   * \param handleType ��� ��������������.
   *        ��������� ��������: SQL_HANDLE_ENV, SQL_HANDLE_DBC, SQL_HANDLE_STMT, SQL_HANDLE_DESC
   * \param handle ��������� �� �������������.
   * \return ��������� �� ������ ��� ������ ������.
   */
  static string getErrorMessage(SQLSMALLINT handleType, SQLHANDLE handle);

private:
  //! Handle for a connection
  SQLHDBC  conn;
  //! Guess yourself
  bool     connected;

protected:

  /*! ���������� ��������� ������ � �������������� �������� ��� ������������ ��������.
   *
   */
  void     reset();

  //! ODBC Datasource
  string   source;
  //! Username to use for connection
  string   user;
  //! Password to use for connection
  string   pass;
  //! Handle for environment
  SQLHENV  env;
};


class DBQuery
{
public:
  /*! ����������� ������.
   *
   */
  DBQuery(DB *database);

  /*! ���������� ������.
   *
   */
  ~DBQuery();

  /*! \brief ����������� ������ ������ ���������� �������� � �������� � ������ �����������.
   *
   *  ��������, ��������� ��� ������������ ��� ������ �������� �������:
   *  \code
   *  long num;
   *  char desc[50];
   *  DBQuery query(mydb);
   *  query.BindCol( 1, SQL_C_LONG, &num,     10);
   *  query.BindCol( 2, SQL_C_CHAR, &desc[0], 50);
   *  query.SendQuery("SELECT id, name from my_test");
   *  while (query.Fetch() != SQL_NO_DATA)
   *  {
   *    printf("id=%d, name=%s\n", num, desc);
   *  }
   *  \endcode
   *
   *  \param colNum ����� ���������, ������������� ���������������
   *         � ��������������� �������, ������������ � 1.
   *  \param dstType ��� ������ ��� C ���������. �������� ����� ������������ ��������:
   *         SQL_C_CHAR, SQL_C_LONG, SQL_C_SHORT, SQL_C_FLOAT, SQL_C_DOUBLE,
   *         SQL_C_NUMERIC, SQL_C_DATE, SQL_C_TIME, SQL_C_TIMESTAMP,
   *         SQL_C_BINARY, SQL_C_BIT, SQL_C_SBIGINT, SQL_C_UBIGINT,
   *         SQL_C_TINYINT, SQL_C_ULONG, SQL_C_USHORT, SQL_C_UTINYINT
   *  \param dstValue ��������� �� ����� ��� ������ ���������.
   *  \param dstLength ����� ������ \c dstValue � ������.
   *  \retval true ��� �������� ���������� �������.
   *  \retval false ��� ������������� ������.
   *  \sa SendQuery(), Fetch()
   */
  bool BindCol(SQLUSMALLINT colNum, SQLSMALLINT dstType, SQLPOINTER dstValue, SQLLEN dstLength);

  /*! \brief ����������� ����� � ������� ��������� � ���������� SQL.
   *
   *  \param colNum ����� ���������, ������������� ���������������
   *         � ��������������� �������, ������������ � 1.
   *  \param ioType ��� ���������.
   *         ��������� ��������: SQL_PARAM_INPUT, SQL_PARAM_INPUT_OUTPUT, SQL_PARAM_OUTPUT.
   *  \param dstType ��� ������ ��� C ���������. �������� ����� ������������ ��������:
   *         SQL_C_CHAR, SQL_C_LONG, SQL_C_SHORT, SQL_C_FLOAT, SQL_C_DOUBLE,
   *         SQL_C_NUMERIC, SQL_C_DATE, SQL_C_TIME, SQL_C_TIMESTAMP,
   *         SQL_C_BINARY, SQL_C_BIT, SQL_C_SBIGINT, SQL_C_UBIGINT,
   *         SQL_C_TINYINT, SQL_C_ULONG, SQL_C_USHORT, SQL_C_UTINYINT
   *  \param srcType SQL-��� ���������.
   *  \param colSize ������ ������� ��� ���������, ���������������� ������� ���������.
   *  \param numDigits ���������� ����� ������� ��� ��������� ���������������� ������� ���������.
   *  \param dstValue ��������� �� ����� ��� ������ ���������.
   *  \param dstLength ����� ������ \c dstValue � ������.
   *  \retval true ������ �������� �������.
   *  \retval false ��������� ������ ��� ���������� �������.
   *  \sa PrepareQuery(), SendQuery()
   */
  bool BindParam(SQLUSMALLINT colNum, SQLSMALLINT ioType, SQLSMALLINT dstType, SQLSMALLINT srcType, SQLUINTEGER colSize, SQLSMALLINT numDigits, SQLPOINTER dstValue, SQLLEN dstLength);

  /*! \brief ������� ������� SQL � ����������.
   *
   *  \param query SQL ������.
   *  \retval true ���������� ��������� �������.
   *  \retval false ��������� ������ ��� ���������� �������.
   *  \sa BindParam(), SendQuery()
   */
  bool PrepareQuery(const string query);

  /*! \brief ��������� �������������� ����������.
   *
   *  ���������� ������� (����������) �������� ���������� ������� ���������,
   *  ���� ������� ���������� ���������� � ����������.
   *
   *  ���� ������ ���������, ��� ���������� ��������� ����� ������������
   *  �������������� ����������. ������� ������� ���������� INSERT
   *  � ��������� 100 ����� ������, ������� �������� ��������.
   *  \code
   *  SQLINTEGER id;
   *  SQLCHAR name[30];
   *  DBQuery query(mydb);
   *  query.PrepareQuery("INSERT INTO EMP(ID, NAME) VALUES(?, ?)");
   *  query.BindParam(1, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, 0, 0, &id, 0);
   *  query.BindParam(2, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_VARCHAR, 0, 0, name, sizeof(name));
   *  for (id=1; id<=100; id++)
   *  {
   *    sprintf(name, "id is %d", id);
   *    query.SendQuery();
   *  }
   *  \endcode
   *
   *  \retval true ������ �������� �������.
   *  \retval false ��������� ������ ��� ���������� �������.
   *  \sa PrepareQuery(), BindParam()
   */
  bool SendQuery();

  /*! \brief ��������� ������ ������ \c query.
   *
   *  ������ ���������� ������������ ����� ����� ������� ������ ��������� ����������.
   *  ������ ���������� ������ ������������ �������������� ����������� �����������,
   *  ������� ��������� � ��������� ���������� �� ����� ����������.
   *  ��������, ��������� ��� ��������� ���������� SQL � ��������� �� ���� ���:
   *  \code
   *  DBQuery query(mydb);
   *  query.SendQueryDirect("CREATE TABLE my_test(id int, name text)");
   *  query.SendQueryDirect("INSERT INTO my_test VALUES(20, 'hello')");
   *  \endcode
   *
   *  \param query SQL ������.
   *  \retval true ������ �������� �������.
   *  \retval false ��������� ������ ��� ���������� �������.
   */
  bool SendQueryDirect(const string query);

  /*! �������� ��������� ������ ������ �� ������ �����������
   *  � ���������� ������ ��� ���� ��������� ��������
   *  \retval SQL_NO_DATA ���� � ������ ����������� �� �������� ����� � �������.
   */
  SQLRETURN Fetch();

  /*! ���������� ���������� �����, �� ������� ������������ ���������� UPDATE, INSERT ��� DELETE.
   *
   */
  SQLINTEGER RowsCount();

  void reset();
protected:
  bool     CreateStatement();
  void     DestroyStatement();

  //! Database to use
  DB *db;
  //! Handle for a statement
  SQLHSTMT statement;
};


#endif /* #ifndef SAMS_DB_H */
