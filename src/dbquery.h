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
#ifndef DBQUERY_H
#define DBQUERY_H

using namespace std;

#include <string>

class DBConn;

/**
 * @brief Базовый класс для различных способов запросов к БД
 */
class DBQuery
{
  friend class DBConn;

public:

  enum VarType
  {
    T_LONG,
    T_CHAR,
    T_DATE,
    T_TIME,
    T_DATETIME,
    T_TIMESTAMP
  };

  static string toString (VarType t);

  virtual ~ DBQuery ();

  /**
   * @brief Выполняет прямой запрос @a query
   *
   * Прямое выполнение представляет собой самый простой способ выполнить инструкцию.
   * Например, следующий код формирует инструкции SQL и выполняет их один раз:
   * @code
   * DBConn conn;
   * DBQuery * query = conn.newQuery();
   * conn.connect();
   * query->sendQueryDirect("CREATE TABLE my_test(id int, name text)");
   * query->sendQueryDirect("INSERT INTO my_test VALUES(20, 'hello')");
   * @endcode
   *
   * @param query SQL запрос.
   * @retval true Запрос выполнен успешно.
   * @retval false Произошла ошибка при выполнении запроса.
   */
  virtual bool sendQueryDirect (const string & query);

  /**
   * @brief Привязывает буферы данных прикладных программ к столбцам в наборе результатов
   *
   * Например, следующий код используется для печати значений таблицы:
   * @code
   * long num;
   * char desc[50];
   * DBConn conn;
   * conn.connect();
   * DBQuery *query = conn.newQuery();
   * query->bindCol( 1, DBQuery::T_LONG, &num, 0);
   * query->bindCol( 2, DBQuery::T_CHAR, desc, sizeof(desc));
   * query->sendQuery("SELECT id, name from my_test");
   * while (query->fetch())
   * {
   *   printf("id=%ld, name=%s\n", num, desc);
   * }
   * conn.disconnect();
   * @endcode
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
   * @sa sendQuery(), fetch(), useConnection()
   */
  virtual bool bindCol (uint colNum, VarType dstType, void *buf, int bufLen);

  virtual bool bindParam (uint num, VarType dstType, void *buf, int bufLen);

  /**
   * @brief Готовит команду SQL к выполнению
   *
   * @param query SQL запрос.
   * @retval true Подготовка выполнена успешно.
   * @retval false Произошла ошибка при подготовке запроса.
   * @sa bindParam(), sendQuery()
   */
  virtual bool prepareQuery (const string & query);

  /**
   * @brief Выполняет подготовленную инструкцию
   *
   * Использует текущие (актуальные) значения переменных маркера параметра,
   * если маркеры параметров существуют в инструкции.
   *
   * Этот пример объясняет, как прикладная программа может использовать
   * подготовленное выполнение. Выборка готовит инструкцию INSERT
   * и вставляет 100 строк данных, заменяя буферные значения.
   * @code
   * long id;
   * char name[30];
   * DBConn conn;
   * conn.connect();
   * DBQuery *query = conn.newQuery();
   * query->prepareQuery("INSERT INTO EMP(ID, NAME) VALUES(?, ?)");
   * query->bindParam(1, DBQuery::T_LONG, &id,  0);
   * query->bindParam(2, DBQuery::T_CHAR, name, sizeof(name));
   * for (id=1; id<=100; id++)
   * {
   *   sprintf(name, "id is %d", id);
   *   query->sendQuery();
   * }
   * @endcode
   *
   * @retval true Запрос выполнен успешно.
   * @retval false Произошла ошибка при выполнении запроса.
   */
  virtual bool sendQuery ();

  /**
   *
   * Выбирает следующую строку данных из набора результатов
   * и возвращает данные для всех связанных столбцов
   *
   * @retval true Если следующая строка из выборки успешно получена
   * @retval false Если в наборе результатов не осталось строк с данными или произошла ошибка
   * @sa sendQuery(), sendQueryDirect()
   */
  virtual bool fetch ();

protected:
  DBQuery ();

  virtual void destroy ();

  DBConn *_conn;
};

#endif
