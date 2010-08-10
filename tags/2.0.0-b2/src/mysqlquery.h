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
#ifndef MYSQLQUERY_H
#define MYSQLQUERY_H

#include "config.h"

#ifdef USE_MYSQL

using namespace std;

#include <vector>
#include <string>

#include <mysql.h>

#include "dbquery.h"

class MYSQLConn;

/**
 * @brief Выполненяет запросы к базе данных через MySQL API
 */
class MYSQLQuery : public DBQuery
{
public:
  /**
   * @brief Конструктор
   *
   * @param conn Используемое подключение к БД
   */
  MYSQLQuery(MYSQLConn *conn);

  /**
   * @brief Деструктор
   */
  ~MYSQLQuery();

  bool sendQueryDirect (const string & query);
  bool bindCol (uint colNum, DBQuery::VarType dstType, void *buf, int bufLen);

  bool prepareQuery (const string & query);
  bool bindParam (uint num, DBQuery::VarType dstType, void *buf, int bufLen);
  bool sendQuery ();

  bool fetch ();

  long affectedRows ();

private:
  /**
   * @brief Структура для привязки к столбцам в наборе результатов
   */
  struct Column
  {
    enum_field_types t;   ///< Тип данных для C параметра
    void *dst;            ///< Указатель на буфер для данных столбца
    int len;              ///< Длина буфера в байтах
  };

  /**
   * @brief Структура для привязки к маркерам для выполнения предварительно подготовленных запросов
   */
  struct Param
  {
    enum_field_types t;   ///< Тип данных для C параметра
    void *dst;            ///< Указатель на буфер для данных маркера
    int len;              ///< Длина буфера в байтах
  };
  bool                  _prepeared_statement;   ///< Флаг, указывающий тип запроса (прямой или предварительно подготовленный)
  MYSQL_STMT            *_statement;            ///< Дескриптор запроса
  vector<struct Column> _columns;               ///< Список столбцов
  vector<struct Param>  _params;                ///< Список маркеров
  MYSQL_BIND            *_bind_param;           ///< Список маркеров для привязки
  MYSQL_BIND            *_bind_column;          ///< Список столбцов для привязки
  unsigned long         *_param_real_len;       ///< Список реальных размеров текущего значения маркеров
  unsigned long         *_columns_real_len;     ///< Список реальных размеров текущего значения столбцов
  bool                  _param_binded;          ///< Флаг, указывающий были ли привязаны маркеры
  bool                  _col_binded;            ///< Флаг, указывающий были ли привязаны столбцы
  MYSQL_RES             *_res;                  ///< Дескриптор результата
  MYSQLConn             *_conn;                 ///< Используемое подключение к БД

  /**
   * @brief Выделяет память для запроса
   *
   * Если ранее память была выделена, то предварительно освобождает ее.
   *
   * @return true если память выделена успешно и false в противном случае
   */
  bool createStatement ();

protected:
  /**
   * @brief Освобождает все ресурсы и переводит экземпляр класса в исходное состояние
   */
  void destroy ();

};

#endif // #ifdef USE_MYSQL

#endif
