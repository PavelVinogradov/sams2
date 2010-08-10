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
#ifndef GROUPLIST_H
#define GROUPLIST_H

using namespace std;

#include <map>
#include <string>

/**
 * @brief Список групп пользователей
 */
class GroupList
{
public:
  /**
   * @brief Устанавливает использование существующего подключения к БД
   *
   * Метод должен быть использован до вызова reload и load. Иначе будет создано
   * новое подключение к БД.
   *
   * @param conn Существующее подключение к БД
   */
  static void useConnection (DBConn * conn);

  /**
   * @brief Перезагружает список из БД
   *
   * @return true при успешном завершении и false в противном случае
   */
  static bool reload();

  /**
   * @brief Освобождает все ресурсы, выделенные во время работы экземпляра класса
   *
   * Используется для сброса всех переменных в начальное значение
   * без уничтожения экземпляра класса
   */
  static void destroy();

  /**
   * @brief Возвращает идентификатор группы
   *
   * @param name Имя группы
   * @return Идентификатор группы или -1 если группа с таким именем не найдена
   */
  static int getGroupId(const string & name);

private:
  /**
   * @brief Загружает список групп из БД
   *
   * Если список был загружен ранее, то ничего не происходит.
   *
   * @return true если ошибок нет и false в противном случае
   * @sa reload
   */
  static bool load();

  static bool _loaded;              ///< Был ли загружен список из БД
  static DBConn * _conn;            ///< Используемое подключение к БД
  static bool _connection_owner;    ///< true если владельцем подключения является экземпляр класса
  static map<string, int> _list;    ///< Список идентификаторов групп пользователей
};

#endif
