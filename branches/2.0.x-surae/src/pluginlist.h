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
#ifndef PLUGINLIST_H
#define PLUGINLIST_H

using namespace std;

#include <string>
#include <vector>

class DBConn;

struct Plugin
{
  void *handle;
  string (*getInfo)();
  string (*getName)();
  string (*getVersion)();
  string (*getAuthor)();
};

/**
 * @brief Список плагинов
 */
class PluginList
{
public:
  static bool reload ();
  static void useConnection (DBConn *conn);
  static void destroy ();
  static bool updateInfo ();
protected:
  /**
   * @brief Загружает и инициализирует плагины
   *
   * @return true если ошибок нет и false в противном случае
   */
  static bool load ();

  static bool loadPlugin (const string &path);

  static bool _loaded;
  static DBConn *_conn;                ///< Соединение с БД
  static bool _connection_owner;
  static vector < Plugin * >_plugins;      ///< Список плагинов
};

#endif
