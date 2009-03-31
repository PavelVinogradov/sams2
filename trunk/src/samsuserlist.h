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
  static void useConnection (DBConn * conn);

  static bool reload();

  static void destroy();

  /**
   * @brief Поиск пользователя по нику и домену
   *
   * @a domain должен четко совпадать с искомым
   *
   * @param domain Имя домена
   * @param nick Ник
   * @return Найденного пользователя или NULL при его отсутствии
   */
  static SAMSUser *findUserByNick (const string & domain, const string & nick);

  /**
   * @brief Поиск пользователя по ip адресу
   *
   * @param ip ip адрес
   * @return Найденного пользователя или NULL при его отсутствии
   */
  static SAMSUser *findUserByIP (const IP & ip);

  static void getUsersByTemplate (long id, vector<SAMSUser *> &lst);

  /**
   * @brief Добавление пользователя, не существующего в БД
   *
   * @param user Пользователь
   * @return true если пользователь успешно добавлен и false в противном случае
   */
  static bool addNewUser(SAMSUser *user);

  static long activeUsersInTemplate (long template_id);
private:
  /**
   * @brief Загружает пользователей из БД
   *
   * @param conn Соединение с БД
   * @return true при успешном завершении и false при любой ошибке
   */
  static bool load ();

  static bool _loaded;
  static vector < SAMSUser * >_users;        ///< список пользователей
  static DBConn * _conn;                     ///< Используемое соединение с БД
  static bool _connection_owner;
};

#endif
