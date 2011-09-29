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
#ifndef ODBCQUERY_H
#define ODBCQUERY_H

#include "config.h"

#ifdef USE_UNIXODBC

using namespace std;

#include <string>
#include <sql.h>
#include <sqlext.h>
#include <sqltypes.h>

#include "dbquery.h"

/// Размер буфера для строковых переменных. Значение в БД не может быть длиннее.
#define COLUMN_BUFFER_SIZE 1024

class ODBCConn;

/**
 * @brief Выполненяет запросы к базе данных через ODBC
 */
class ODBCQuery : public DBQuery
{
  friend class ODBCConn;

public:
  /**
   * @brief Конструктор
   *
   * @param conn Используемое соединение
   */
    ODBCQuery (ODBCConn * conn);

  /**
   * @brief Деструктор
   */
   ~ODBCQuery ();


//  bool bindCol (uint colNum, VarType dstType, void *buf, int bufLen);

//  bool bindParam (uint num, VarType dstType, void *buf, int bufLen);

  bool prepareQuery (const string & query);

  bool sendQuery ();

  bool sendQueryDirect (const string & query);

  bool fetch ();

  long affectedRows ();

  bool bindCol (uint colNum, DBQuery::VarType dstType, void *buf, int bufLen);

  /**
   * @brief Привязывает буферы данных прикладных программ к столбцам в наборе результатов
   *
   * @param colNum Номер параметра, упорядоченный последовательно
   *        в увеличивающемся порядке, начинающемся с 1.
   * @param dstType Тип данных для C параметра. Наиболее часто используемые значения:
   *        SQL_C_CHAR, SQL_C_LONG, SQL_C_SHORT, SQL_C_FLOAT, SQL_C_DOUBLE,
   *        SQL_C_NUMERIC, SQL_C_DATE, SQL_C_TIME, SQL_C_TIMESTAMP,
   *        SQL_C_BINARY, SQL_C_BIT, SQL_C_SBIGINT, SQL_C_UBIGINT,
   *        SQL_C_TINYINT, SQL_C_ULONG, SQL_C_USHORT, SQL_C_UTINYINT
   * @param dstValue Указатель на буфер для данных параметра.
   * @param dstLength Длина буфера @a dstValue в байтах.
   * @retval true При успешном добавлении колонки.
   * @retval false При возникновении ошибки.
   * @sa sendQuery(), fetch()
   */
  bool bindCol (SQLUSMALLINT colNum, SQLSMALLINT dstType, SQLPOINTER dstValue, SQLLEN dstLength);

  bool bindParam (uint num, DBQuery::VarType dstType, void *buf, int bufLen);

  /**
   * @brief Привязывает буфер к маркеру параметра в инструкции SQL
   *
   * @param num Номер параметра, упорядоченный последовательно
   *        в увеличивающемся порядке, начинающемся с 1.
   * @param dstType Тип данных для C параметра. Наиболее часто используемые значения:
   *        SQL_C_CHAR, SQL_C_LONG, SQL_C_SHORT, SQL_C_FLOAT, SQL_C_DOUBLE,
   *        SQL_C_NUMERIC, SQL_C_DATE, SQL_C_TIME, SQL_C_TIMESTAMP,
   *        SQL_C_BINARY, SQL_C_BIT, SQL_C_SBIGINT, SQL_C_UBIGINT,
   *        SQL_C_TINYINT, SQL_C_ULONG, SQL_C_USHORT, SQL_C_UTINYINT
   * @param srcType SQL-тип параметра.
   * @param dstValue Указатель на буфер для данных параметра.
   * @param dstLength Длина буфера @a dstValue в байтах.
   * @retval true Запрос выполнен успешно.
   * @retval false Произошла ошибка при выполнении запроса.
   * @sa prepareQuery(), sendQuery()
   */
  bool bindParam (SQLUSMALLINT num, SQLSMALLINT dstType, SQLSMALLINT srcType, SQLPOINTER dstValue, SQLLEN dstLength);

private:
  /**
   * @brief Выделяет память для запроса
   *
   * @return true если память выделена успешно и false в противном случае
   */
  bool createStatement ();

  /**
   * @brief Освобождает выделенную память для запроса
   */
  void destroy ();

  SQLHSTMT statement;           ///< Дескриптор запроса

protected:
  ODBCConn * _conn;             ///< Используемое подключение к БД
};

#endif // #ifdef USE_UNIXODBC

#endif
