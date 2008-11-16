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
#ifndef GROUPS_H
#define GROUPS_H

using namespace std;

#include <map>
#include <string>

class Groups{
public:
  static void useConnection (DBConn * conn);
  static bool reload();
  static void destroy();

  static int getGroupId(const string & name);

private:
  static bool load();

  static bool _loaded;
  static DBConn * _conn;                     ///< Используемое соединение с БД
  static bool _connection_owner;
  static map<string, int> _list;
};

#endif
