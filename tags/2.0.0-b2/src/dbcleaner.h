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
#ifndef DBCLEANER_H
#define DBCLEANER_H

using namespace std;

#include <string>
#include <vector>

class SamsConfig;
class UserFilter;
class DateFilter;
class SAMSUser;

/**
 * @brief Очистка таблиц кэша и счетчиков пользователей
 */
class DBCleaner
{
public:
  /**
   * @brief Конструктор
   */
  DBCleaner ();

  /**
   * @brief Деструктор
   */
   ~DBCleaner ();

  /**
   * @brief Устанавливает фильтр по пользователям
   *
   * Все действия производятся только
   * над пользователями, присутствующими в фильтре.
   * Дополняет метод @a setTemplateFilter
   * Если использовать оба метода установки фильтра по пользователям,
   * то будут затронуты пользователи, удовлетворящие обоим условиям
   * @param filt Фильтр пользователей или NULL для снятия фильтра
   */
  void setUserFilter (UserFilter * filt);

  /**
   * @brief Устанавливает фильтр по идентификатору шаблона
   *
   * Все действия производятся только
   * над пользователями, у которых установлен этот шаблон.
   * Дополняет метод @a setUserFilter
   * Если использовать оба метода установки фильтра по пользователям,
   * то будут затронуты пользователи, удовлетворящие обоим условиям
   * @param tpl_id Идентификатор шаблона или -1 для снятия фильтра
   */
  void setTemplateFilter (long tpl_id);

  /**
   * @brief Устанавливает фильтр по датам
   *
   * Все действия производятся только
   * в указанном интервале дат.
   * @param filt Фильтр по датам или NULL для снятия фильтра
   */
  void setDateFilter (DateFilter * filt);

  /**
   * @brief Устанавливает фильтр по датам
   *
   * Все действия производятся только в указанном интервале дат.
   * Формат интервала смотреть в @a DateFilter::DateFilter
   * @param dateSpec Интервал дат
   */
  void setDateFilter (const string & dateSpec);

  /**
   * @brief Очищает счетчики пользователей
   *
   * Если установлен фильтр по пользователям, то очищаются счетчики
   * только тех пользователей, которые перечислены в фильтре
   * @sa setUserFilter, setTemplateFilter
   */
  void clearCounters ();

  /**
   * @brief Очищает кэш протоколов доступа squid
   *
   * Если установлен фильтр по пользователям, то очищается кеш
   * только тех пользователей, которые перечислены в фильтре.
   * Если установлен фильтр по датам, то очищается кеш только
   * в указанном интервале дат.
   */
  void clearCache ();

  /**
   * @brief Очищает кэш протоколов доступа squid
   *
   * Удаляются только записи старше @a nmonth месяцев. Фильтры игнорируются.
   *
   * @param nmonth Количество месяцев
   */
  void clearOldCache (int nmonth);

protected:
  DateFilter *_date_filter;       ///< Текущий фильтр по датам
  bool        _date_filter_owner; ///< Флаг, указывающий нужно ли удалять фильтр в деструкторе
  UserFilter *_user_filter;       ///< Текущий фильтр по пользователям
  long        _tpl_id;            ///< Текущий фильтр по шаблону
};

#endif
