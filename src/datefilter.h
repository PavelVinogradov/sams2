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
#ifndef DATEFILTER_H
#define DATEFILTER_H

using namespace std;

#include <time.h>

#include <string>

#include "filter.h"

/**
 * @brief Фильтр по датам
 */
class DateFilter:public Filter
{
public:
  /**
   * @brief Конструктор
   */
  DateFilter ();

  /**
   * @brief Конструктор с определением интервала дат
   *
   * @param dateSpec Интервал дат
   * @sa setDateInterval
   */
  DateFilter (const string & dateSpec);

  /**
   * @brief Деструктор
   */
  ~DateFilter ();

  /**
   * @brief Устанавливает интервал дат
   *
   * Даты указываются в формате YYYY-MM-DD через запятую.
   * Конечная дата включается в интервал.
   * Если дата начала не указана, то началом считается 1 января 2000 года.
   * Если дата окончания не указана, то окончанием считается текущее число.
   * Если запятой нет, то началом и окончанием считается указанная дата.
   * Например:
   * @code
   * DateFilter flt;
   * flt.setDateInterval("2007-11-01,2007-11-30");
   * flt.setDateInterval(",2007-12-31"); // c 1 января 2000 по 31 декабря 2007
   * flt.setDateInterval("2007-01-01,"); // с 1 января 2007 до текущего дня
   * flt.setDateInterval("2007-10-15");  // только 15 октября 2007
   * @endcode
   *
   * @param dateSpec Интервал дат
   * @return
   */
  bool setDateInterval (const string & dateSpec);

  /**
   * @brief Проверяет, подпадает ли под фильтр дата @a date
   *
   * @param date Дата, которую нужно проверить
   * @return true если дата подпадает под фильтр и false в противном случае
   */
  bool match (struct tm &date);

  /**
   * @brief Возвращает значение начала интервала в виде строки
   *
   * @return Начало интервала в виде строки
   */
  string getStartDateAsString () const;

  /**
   * @brief Возвращает значение окончания интервала в виде строки
   *
   * @return Окончание интервала в виде строки
   */
  string getEndDateAsString () const;

protected:
  time_t _date_start;           ///< Начало интервала
  time_t _date_end;             ///< Окончание интервала
};

#endif

