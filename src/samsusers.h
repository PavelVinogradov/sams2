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
#ifndef SAMSUSERS_H
#define SAMSUSERS_H

using namespace std;

#include <vector>

#include "ip.h"

class DBConn;
class SAMSUser;

/**
 * @brief Список пользователей SAMS
 */
class SAMSUsers
{
  friend class Proxy;
public:
  /**
   * @brief Конструктор
   */
    SAMSUsers ();

  /**
   * @brief Деструктор
   */
   ~SAMSUsers ();

  /**
   * @brief Загружает пользователей из БД
   *
   * @param conn Соединение с БД
   * @return true при успешном завершении и false при любой ошибке
   */
  bool load (DBConn * conn);

  /**
   * @brief Поиск пользователя по нику и домену
   *
   * @a domain должен четко совпадать с искомым
   *
   * @param domain Имя домена
   * @param nick Ник
   * @return Найденного пользователя или NULL при его отсутствии
   */
  SAMSUser *findUserByNick (const string & domain, const string & nick);

  /**
   * @brief Поиск пользователя по ip адресу
   *
   * @param ip ip адрес
   * @return Найденного пользователя или NULL при его отсутствии
   */
  SAMSUser *findUserByIP (const IP & ip);

private:
    vector < SAMSUser * >_users;        ///< список пользователей
};

#endif
