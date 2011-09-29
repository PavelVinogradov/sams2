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
#ifndef URLGROUPLIST_H
#define URLGROUPLIST_H

using namespace std;

#include <vector>

class DBConn;
class UrlGroup;

/**
 * @brief Список групп разрешенных и запрещенных ресурсов
 */
class UrlGroupList
{
  friend class Proxy;
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
   * @brief Возвращает список всех групп
   *
   * @return Список групп
   */
  static vector < UrlGroup * > getAllGroups ();

  /**
   * @brief Возвращает группу url по заданному идентификатору
   *
   * @param id Идентификатор группы
   * @return Указатель на экземпляр класса или NULL если группа с таким идентификатором не найдена
   */
  static UrlGroup* getUrlGroup (long id);

  //static string modifyUrl (const string &url);
private:
  /**
   * @brief Загружает группы из БД
   *
   * @return true при успешном завершении и false при любой ошибке
   */
  static bool load ();

  static bool _loaded;                      ///< Был ли загружен список из БД
  static vector < UrlGroup * >_groups;      ///< Cписок групп
  static DBConn * _conn;                    ///< Используемое подключение к БД
  static bool _connection_owner;            ///< true если владельцем подключения является экземпляр класса
};

#endif
