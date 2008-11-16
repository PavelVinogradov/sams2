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
  static void useConnection (DBConn * conn);

  static bool reload();

  static void destroy();

  static vector<long> getAllowGroupIds ();

  static vector<long> getDenyGroupIds ();

  static UrlGroup* getUrlGroup (long id);
private:
  /**
   * @brief Загружает группы из БД
   *
   * @return true при успешном завершении и false при любой ошибке
   */
  static bool load ();

  static bool _loaded;
  static vector < UrlGroup * >_groups;       ///< список групп
  static DBConn * _conn;                     ///< Используемое соединение с БД
  static bool _connection_owner;
};

#endif
