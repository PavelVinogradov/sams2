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
#ifndef USERFILTER_H
#define USERFILTER_H

using namespace std;

#include <string>
#include <vector>

#include "filter.h"

class SAMSUser;

/**
 * @brief Фильтр по пользователям
 */
class UserFilter:public Filter
{
public:
  /**
   * @brief Конструктор
   */
  UserFilter ();

  /**
   * @brief Конструктор с определением списка пользователей
   *
   * @param usersList Список пользователей
   */
  UserFilter (const string & usersList);

  /**
   * @brief Деструктор
   */
   ~UserFilter ();

  /**
   * @brief Устанавливает список пользователей
   *
   * В списке каждый пользователь указывается через запятую
   * и могут быть указаны как ip адреса, так и ники пользователей одновременно
   * (пользователь может быть указан с доменом или без него).
   * Разделители домена указаны в DOMAIN_SEPARATORS.
   * Например,
   * @code
   * UserFilter flt;
   * flt.setUsersList ( "DOMAIN+user1,192.168.1.1,user2" );
   * @endcode
   *
   * @param usersList Список пользователей
   * @retval true Список не содержит ошибок
   * @retval false Найдены ошибки в списке
   */
  bool setUsersList (const string & usersList);

  /**
   * @brief Устанавливает список пользователей
   *
   * @param usersList Список пользователей
   */
  bool setUsersList (const vector<SAMSUser *> & usersList);

  /**
   * @brief Проверяет, подпадает ли под фильтр пользователь @a user
   *
   * @param user Пользователь, которого нужно проверить
   * @return true если пользователь подпадает под фильтр и false в противном случае
   */
  bool match (SAMSUser * user);

  /**
   * @brief Возвращает список пользователей
   *
   * @return Список пользователей
   */
  vector <string> getUsersList ();
protected:
    vector < string > _tblUsers;        ///< Список пользователей

};

#endif
