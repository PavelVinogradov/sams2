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

class DBQuery;

/**
 * @brief Базовый класс для различных способов подключений к БД
 */
class DBConn
{
public:
  /**
   * @brief Используемый API для работы с БД
   */
  enum DBEngine
  {
    DB_UNKNOWN,                 ///< Неизвестный способ доступа к БД
    DB_MYSQL,                   ///< Использовать функции MySQL для доступа к БД
    DB_PGSQL,                   ///< Использовать функции PostgreSQL для доступа к БД
    DB_UODBC                    ///< Использовать функции unixODBC для доступа к БД
  };

  /**
   * @brief Деструктор
   *
   * При вызове этого деструктора, автоматически вызывается деструктор наследника
   */
  virtual ~DBConn ();

  /**
   * @brief Подключение к БД
   *
   * Метод должен быть переопределен у наследника.
   *
   * @return true если соединение установлено и false при какой либо ошибке
   */
  virtual bool connect ();

  /**
   * @brief Создание нового запроса
   *
   * Метод должен быть переопределен у наследника.
   *
   * @param query Указатель на экземпляр класса объекта DBQuery или NULL при какой либо ошибке
   */
  virtual void newQuery (DBQuery *& query);

  /**
   * @brief Возвращает состояние соединения
   *
   * @return true если соединение уже установлено и false в противном случае
   */
  bool isConnected ();

  /**
   * @brief Возвращает используемый API
   *
   * @return true если соединение уже установлено и false в противном случае
   */
  DBEngine getEngine();

  /**
   * @brief Закрывает подключение
   *
   * Метод должен быть переопределен у наследника.
   */
  virtual void disconnect ();

  /**
   * @brief Регистрирует запрос
   *
   * Добавляет запрос к списку, если указатель не NULL
   *
   * @param query Указатель на экземпляр класса
   */
  virtual void registerQuery(DBQuery * query);

  /**
   * @brief Разрегистрирует запрос
   *
   * Удаляет запрос из списка, если указатель не NULL
   *
   * @param query Указатель на экземпляр класса
   */
  virtual void unregisterQuery(DBQuery * query);

  /**
   * @brief Разрегистрирует и уничтожает все зарегистрированные запросы
   */
  virtual void unregisterAllQueries ();

private:

protected:
  /**
   * @brief Конструктор
   *
   * @param engine способ работы с БД
   */
  DBConn (DBEngine engine);

  bool _connected;            ///< Guess yourself
  DBEngine _engine;           ///< Используемый API
  map < string, DBQuery * >_queries;  ///< Список подключенных запросов
};

#endif

