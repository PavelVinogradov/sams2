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
#ifndef LOCALNETWORKS_H
#define LOCALNETWORKS_H

using namespace std;

#include <string>
#include <vector>

class DBConn;
class Net;

/**
 * @brief Список локальных сетей и доменов
 */
class LocalNetworks
{
public:
  static void useConnection (DBConn * conn);

  static bool reload();

  static void destroy();

  /**
   * @brief Проверяет является ли @a host локальным
   *
   * @param host Имя или ip адрес хоста
   * @return true если хост является локальным и false в противном случае
   */
  static bool isLocalHost (const string & host);

  /**
   * @brief Проверяет является ли @a url локальным
   *
   * Из url адреса извлекается адрес хоста и проверяется локальность хоста.
   *
   * @param url URL адрес
   * @return true если url является локальным и false в противном случае
   */
  static bool isLocalUrl (const string & url);

protected:
  /**
   * @brief Загружает список локальных сетей из БД
   *
   * @param conn Соединение с БД
   * @return true если ошибок нет и false в противном случае
   */
  static bool load ();

  static bool _loaded;
  static vector < Net * >_nets;      ///< Список локальных сетей
  static DBConn *_conn;
  static bool _connection_owner;
};

#endif
