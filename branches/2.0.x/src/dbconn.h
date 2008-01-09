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
#ifndef DBCONN_H
#define DBCONN_H

using namespace std;

#include <string>
#include <map>

/**
 * @brief Базовый класс для различных способов подключений к БД
 */
class DBConn
{
public:
  enum DBEngine
  {
    DB_MYSQL,                   ///< Использовать функции MySQL для доступа к БД
//    DB_PGSQL,                   ///< Использовать функции PostgreSQL для доступа к БД
    DB_UODBC                    ///< Использовать функции unixODBC для доступа к БД
  };

  DBConn (DBEngine engine);

  virtual ~DBConn ();

  virtual bool connect ();

  /**
   * @brief Возвращает состояние соединения
   *
   * @return true если соединение уже установлено и false в противном случае
   */
  bool isConnected ();

  DBEngine getEngine();

  /**
   * Закрывает соединение с источником данных и освобождает все используемые ресурсы
   */
  virtual void disconnect ();

private:

protected:
  bool _connected;            ///< Guess yourself
  DBEngine _engine;
//  map < string, DBQuery * >_queries;  ///< Список подключенных запросов
};

#endif
