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
#ifndef MYSQLCONN_H
#define MYSQLCONN_H

#include "config.h"

#ifdef USE_MYSQL

using namespace std;

#include <mysql.h>
#include <string>
#include "dbconn.h"

/**
 * @brief Подключение к базе данных через MYSQL API
 */
class MYSQLConn : public DBConn
{
friend class MYSQLQuery;

public:
  MYSQLConn ();

  ~MYSQLConn ();

  bool connect ();

  void disconnect ();

  MYSQL *_mysql;

private:

protected:
  string _dbname;               ///< Имя базы данных
  string _user;                 ///< Логин
  string _pass;                 ///< Пароль
  string _host;                 ///< Имя сервера
};

#endif // #ifdef USE_MYSQL

#endif
