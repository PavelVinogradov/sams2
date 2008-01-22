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
#ifndef SQUIDLOGPARSER_H
#define SQUIDLOGPARSER_H

using namespace std;

#include <string>

class SamsConfig;
class UserFilter;
class DateFilter;
class DBConn;

/**
 * @brief Анализатор файла протокола доступа squid
 */
class SquidLogParser
{
public:
  /**
   * @brief Конструктор
   *
   * @param proxyid Идентификатор прокси
   */
  SquidLogParser (int proxyid);

  /**
   * @brief Деструктор
   */
   ~SquidLogParser ();

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

  /**
   * @brief Импортирует данные из файла в БД
   *
   * @param fname Имя файла для импорта
   * @param from_begin Если true, то файл анализируется с самого начала.
   *                   Если false, то смещение в файле берется из базы данных
   */
  void parseFile (const string & fname, bool from_begin);

  /**
   * @brief Импортирует данные из файла в БД, используя установленное соединение
   *
   * @param conn Установленное соединение
   * @param fname Имя файла для импорта
   * @param from_begin Если true, то файл анализируется с самого начала.
   *                   Если false, то смещение в файле берется из базы данных
   */
  void parseFile (DBConn *conn, const string & fname, bool from_begin);

protected:
  int _proxyid;                 ///< Идентификатор прокси
  DateFilter *_date_filter;     ///< Текущий фильтр по датам
  UserFilter *_user_filter;     ///< Текущий фильтр по пользователям
};

#endif
