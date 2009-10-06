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
#ifndef DELAYPOOLLIST_H
#define DELAYPOOLLIST_H

using namespace std;

#include <map>
#include <vector>
#include <string>

class DelayPool;
class DBConn;

/**
 * @brief Список классов ограничения скорости
 */
class DelayPoolList
{
public:
  /**
   * @brief Перезагружает список из БД
   *
   * @return true при успешном завершении и false в противном случае
   */
  static bool reload();

  /**
   * @brief Устанавливает использование существующего подключения к БД
   *
   * Метод должен быть использован до вызова reload и load. Иначе будет создано
   * новое подключение к БД.
   *
   * @param conn Существующее подключение к БД
   */
  static void useConnection(DBConn *conn);

  /**
   * @brief Освобождает все ресурсы, выделенные во время работы экземпляра класса
   *
   * Используется для сброса всех переменных в начальное значение
   * без уничтожения экземпляра класса
   */
  static void destroy();

  /**
   * @brief Возвращает класс по заданному идентификатору
   *
   * @param id Идентификатор класса
   * @return Указатель на экземпляр класса или NULL если класс с таким идентификатором не найден
   */
  static DelayPool * getDelayPool(long id);

  /**
   * @brief Возвращает список идентификаторов всех классов
   *
   * @return Список идентификаторов классов
   */
  static vector<long> getIds();

  /**
   * @brief Возвращает список всех классов
   *
   * @return Список классов
   */
  static vector<DelayPool*> getList ();

  /**
   * @brief Возвращает количество классов
   *
   * @return Количество классов
   */
  static long count ();
private:
  /**
   * @brief Загружает список классов из БД
   *
   * Если список уже был загружен, то ничего не делает.
   *
   * @return true при успешном завершении и false в противном случае
   */
  static bool load();

  static bool _loaded;                  ///< Был ли загружен список из БД
  static map<long, DelayPool*> _list;  ///< Cписок классов
  static DBConn *_conn;                 ///< Используемое подключение к БД
  static bool _connection_owner;        ///< true если владельцем подключения является экземпляр класса
};

#endif
