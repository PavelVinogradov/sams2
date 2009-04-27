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
#ifndef PGQUERY_H
#define PGQUERY_H

#include "config.h"

#ifdef USE_PQ

using namespace std;

#include <vector>
#include <string>

#include <libpq-fe.h>

#include "dbquery.h"

class PgConn;

/**
 * @brief Выполненяет запросы к базе данных через PosgreSQL API
 */
class PgQuery : public DBQuery
{
public:
  /**
   * @brief Конструктор
   *
   * @param conn Используемое подключение к БД
   */
  PgQuery(PgConn *conn);

  /**
   * @brief Деструктор
   */
  ~PgQuery();

  bool sendQueryDirect (const string & query);
  bool bindCol (uint colNum, VarType dstType, void *buf, int bufLen);
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
    VarType t;    ///< Тип данных для C параметра
    void *dst;    ///< Указатель на буфер для данных столбца
    int len;      ///< Длина буфера в байтах
  };

  /**
   * @brief Структура для привязки к маркерам для выполнения предварительно подготовленных запросов
   */
  struct Param
  {
    VarType t;    ///< Тип данных для C параметра
    void *dst;    ///< Указатель на буфер для данных маркера
    int len;      ///< Длина буфера в байтах
  };

  /**
   * @brief Преобразовывает метки маркера в необходимый формат для libpq API
   *
   * @param cmd Строка подготовленного SQL запроса
   */
  string convert (const string & cmd);

  PgConn                  *_conn;             ///< Используемое подключение к БД
  PGresult                *_res;              ///< Дескриптор результата запроса
  vector<struct Column>   _columns;           ///< Список столбцов
  vector<struct Param>    _params;            ///< Список маркеров
  bool                    _prepeared;         ///< Флаг, указывающий была ли выполнена подготовка SQL запроса
  int                     _current_row;       ///< Текущий номер строки в выборке
  string                  _prepeared_query;   ///< Строка подготовленного SQL запроса, преобразованная для libpq API
  string                  _query_name;        ///< Имя запроса (любая уникальная строка)
  char                    **_param_values;    ///< Значения маркеров
  int                     *_param_real_len;   ///< Список реальных размеров текущего значения маркеров
  int                     *_param_formats;    ///< Формат маркеров (заглушка)

protected:
  void destroy ();
};

#endif // #ifdef USE_PQ

#endif
