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
#ifndef PGCONN_H
#define PGCONN_H

#include "config.h"

#ifdef USE_PQ

using namespace std;

#include <libpq-fe.h>

#include <string>
#include "dbconn.h"

class DBQuery;

/**
 * @brief Подключение к базе данных через PostgreSQL API
 */
class PgConn : public DBConn
{
friend class PgQuery;

public:
  PgConn();

  ~PgConn();

  bool connect ();

  DBQuery * newQuery ();

  void disconnect ();

private:
  string _dbname;               ///< Имя базы данных
  string _user;                 ///< Логин
  string _pass;                 ///< Пароль
  string _host;                 ///< Имя сервера

  PGconn * _pgconn;
};

#endif // #ifdef USE_PQ

#endif
