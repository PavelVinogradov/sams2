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
#ifndef TIMERANGE_H
#define TIMERANGE_H

using namespace std;

#include <string>

#include <time.h>

/**
 * @brief Временной интервал
 */
class TimeRange
{
public:
  /**
   * @brief Конструктор
   *
   * @param id Идентификатор временного интервала
   */
  TimeRange(long id);

  /**
   * @brief Деструктор
   *
   */
  ~TimeRange();

  /**
   * @brief Возвращает идентификатор временного интервала
   *
   * @return Идентификатор временного интервала
   */
  long getId () const;

  /**
   * @brief Устанавливает параметры временного интервала
   *
   * @param days Дни недели
   * @param tstart Начало интервала в формате HH:MM:SS
   * @param tend Конец интервала в формате HH:MM:SS
   */
  void setTimeRange(const string &days, const string &tstart, const string &tend);

  /**
   * @brief Определяет попадает ли текущее время в заданный интервал
   *
   * @retval true Текущее время попадает в заданный интервал
   * @retval false Текущее время не попадает в заданный интервал
   */
  bool hasNow () const;

  /**
   * @brief Определяет попадает ли полночь в заданный интервал
   *
   * @retval true Полночь попадает в заданный интервал
   * @retval false Полночь не попадает в заданный интервал
   */
  bool hasMidnight () const;

  /**
   * @brief Определяет задан ли интервал полностью на сутки
   *
   * @retval true Интервал задан полностью на сутки
   * @retval false Интервал задан на часть суток
   */
  bool isFullDay () const;

  /**
   * @brief Возвращает дни недели, для которых действует интервал времени
   *
   * Каждый день недели кодируется одним из символов SMTWHFA (с воскресенья по субботу соответственно)
   *
   * @return Дни недели
   */
  string getDays () const;

  /**
   * @brief Возвращает начало временного интервала в виде строки
   *
   * @return Начало временного интервала в формате HH:MM:SS
   */
  string getStartTimeStr () const;

  /**
   * @brief Возвращает конец временного интервала в виде строки
   *
   * @return Конец временного интервала в формате HH:MM:SS
   */
  string getEndTimeStr () const;

private:
  long  _id;                    ///< Идентификатор временного интервала
  string _days;                 ///< Дни недели
  string _tstart;               ///< Начало интервала в формате HH:MM:SS
  string _tend;                 ///< Конец интервала в формате HH:MM:SS
  time_t _time_start;           ///< Начало интервала
  time_t _time_end;             ///< Конец интервала
  bool _hasMidnight;            ///< Флаг, определяющий есть ли полночь во временном интервале
};

#endif
