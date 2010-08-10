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
#ifndef SAMSUSERLIST_H
#define SAMSUSERLIST_H

using namespace std;

#include <vector>
#include <map>

#include "ip.h"

class DBConn;
class SAMSUser;

/**
 * @brief Список пользователей SAMS
 */
class SAMSUserList
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
   * @brief Поиск пользователя
   *
   * @param auth Тип авторизации
   * @param ip IP адрес
   * @param domain Имя домена
   * @param nick Ник
   * @return Найденного пользователя или NULL при его отсутствии
   */
  static SAMSUser *findUser (const string & auth, const string & ip, const string & domain, const string & nick);

  /**
   * @brief Возвращает список пользователей, входящих в шаблон с идентификатором @a id
   *
   * @param id Идентификатор шаблона
   * @param lst Список пользователей
   */
  static void getUsersByTemplate (long id, vector<SAMSUser *> &lst);

  /**
   * @brief Добавление пользователя, не существующего в БД
   *
   * @param user Пользователь
   * @return true если пользователь успешно добавлен и false в противном случае
   */
  static bool addNewUser(const string & auth, SAMSUser *user);

  /**
   * @brief Возвращает количество активных пользователей в шаблоне с идентификатором @a template_id
   *
   * @param template_id Идентификатор шаблона
   * @return lst Количество активных пользователей
   */
  static long activeUsersInTemplate (long template_id);
private:
  /**
   * @brief Загружает пользователей из БД
   *
   * Если список уже был загружен ранне, то ничего не происходит.
   *
   * @return true при успешном завершении и false при любой ошибке
   *
   * @sa reload
   */
  static bool load ();

  static bool _loaded;                  ///< Был ли загружен список из БД
  static map < string, SAMSUser * >_users;   ///< Список пользователей
//  static vector < SAMSUser * >_users;   ///< Список пользователей
  static DBConn * _conn;                ///< Используемое подключение к БД
  static bool _connection_owner;        ///< true если владельцем подключения является экземпляр класса
};

#endif
