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

  /*! ��������� ������� � ��������� �������. ������������ � �������� ���� SELECT.
   *
   *  \param colNum ����� �������, ������� � 1.
   *  \param dstType ��� ���������. �������� ����� ������������ ��������:
   *         SQL_C_CHAR, SQL_C_LONG, SQL_C_SHORT, SQL_C_FLOAT, SQL_C_DOUBLE,
   *         SQL_C_NUMERIC, SQL_C_DATE, SQL_C_TIME, SQL_C_TIMESTAMP,
   *         SQL_C_BINARY, SQL_C_BIT, SQL_C_SBIGINT, SQL_C_UBIGINT,
   *         SQL_C_TINYINT, SQL_C_ULONG, SQL_C_USHORT, SQL_C_UTINYINT,
   *  \param dstValue ����� ����������, ���� ���������� ��������.
   *  \param dstLength ����� ����������.
   *  \retval true ��� �������� ���������� �������.
   *  \retval false ��� ������������� ������.
   *  \sa SendQuery, Fetch
   */
  bool AddCol(SQLUSMALLINT colNum, SQLSMALLINT dstType, SQLPOINTER dstValue, SQLLEN dstLength);

  /*! ��������� ������ \c query.
   *
   *  \param query SQL ������.
   *  \retval true ������ �������� �������.
   *  \retval false ��������� ������ ��� ���������� �������.
   *  \sa AddCol, Fetch
   */
  bool SendQuery(const string query);

  /*! �������� ��������� ������ ������ �� ������ �����������
   *  � ���������� ������ ��� ���� ��������� ��������
   *  \retval SQL_NO_DATA ���� � ������ ����������� �� �������� ����� � �������.
   */
  SQLRETURN Fetch();

  /*! ���������� ���������� �����, �� ������� ������������ ���������� UPDATE, INSERT ��� DELETE.
   *
   */
  SQLINTEGER RowsCount();

protected:

  /*! ���������� ��������� ������ � �������������� �������� ��� ������������ ��������.
   *
   */
  void     reset();

  /*! ���������� ��������� � ��������� ������.
   *
   * \param handleType ��� ��������������.
   *        ��������� ��������: SQL_HANDLE_ENV, SQL_HANDLE_DBC, SQL_HANDLE_STMT, SQL_HANDLE_DESC
   * \param handle ��������� �� �������������.
   * \return ��������� �� ������ ��� ������ ������.
   */
  string   getErrorMessage(SQLSMALLINT handleType, SQLHANDLE handle);

  //! Guess yourself
  bool     connected;
  //! ODBC Datasource
  string   source;
  //! Username to use for connection
  string   user;
  //! Password to use for connection
  string   pass;
  //! Handle for environment
  SQLHENV  env;
  //! Handle for a connection
  SQLHDBC  conn;
  //! Handle for a statement
  SQLHSTMT statement;
};


#endif /* #ifndef SAMS_DB_H */
