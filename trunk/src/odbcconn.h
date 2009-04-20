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
#ifndef ODBCCONN_H
#define ODBCCONN_H

#include "config.h"

#ifdef USE_UNIXODBC

using namespace std;

#include <string>
#include <map>

#include <sql.h>
#include <sqlext.h>
#include <sqltypes.h>

#include "dbconn.h"

class ODBCQuery;

/**
 * @brief Подключение к базе данных через ODBC
 */
class ODBCConn : public DBConn
{
  friend class ODBCQuery;
public:
  /**
   * @brief Конструктор
   */
  ODBCConn ();

  /**
   * @brief Деструктор
   *
   * Самостоятельно закрывает соединение, если оно было установлено,
   * и освобождает все используемые ресурсы, включая запросы, использующие это соединение.
   */
   ~ODBCConn ();

  /**
   * @brief Подключается к БД
   *
   * @return true если подключение прошло успешно и false в противном случае
   */
  bool connect ();

  void newQuery (DBQuery *& query);

  void disconnect ();

  bool isConnected ();

  /**
   * Возвращает сообщение о последней ошибке
   *
   * @param handleType Тип идентификатора.
   *        Возможные значения: SQL_HANDLE_ENV, SQL_HANDLE_DBC, SQL_HANDLE_STMT, SQL_HANDLE_DESC
   * @param handle Указатель на идентификатор
   * @return Сообщение об ошибке или пустую строку
   */
  static string getErrorMessage (SQLSMALLINT handleType, SQLHANDLE handle);

protected:

private:
  SQLHDBC _hdbc;                ///< Handle for a connection
  string _source;               ///< Источник данных ODBC
  string _user;                 ///< Логин
  string _pass;                 ///< Пароль
  SQLHENV _env;                 ///< Handle for environment
  bool _connected;

};

#endif // #ifdef USE_UNIXODBC

#endif
