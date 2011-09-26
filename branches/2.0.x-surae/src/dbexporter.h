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
#ifndef DBEXPORTER_H
#define DBEXPORTER_H

using namespace std;

#include <string>

class SamsConfig;
class UserFilter;
class DateFilter;

class DBExporter
{
public:
  DBExporter ();

  ~DBExporter ();

  /**
   * @brief Устанавливает фильтр по пользователям
   *
   * @param filt Фильтр пользователей
   */
  void setUserFilter (UserFilter * filt);

  /**
   * @brief Устанавливает фильтр по датам
   *
   * @param filt Фильтр по датам
   */
  void setDateFilter (DateFilter * filt);

  void setDateFilter (const string & dateSpec);

  bool exportToFile (const string &fname);

protected:
  DateFilter *_date_filter;       ///< Текущий фильтр по датам
  bool        _date_filter_owner; ///< Флаг, указывающий нужно ли удалять фильтр в деструкторе
  UserFilter *_user_filter;       ///< Текущий фильтр по пользователям
};

#endif
