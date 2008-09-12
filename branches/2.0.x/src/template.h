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
#ifndef TEMPLATE_H
#define TEMPLATE_H

using namespace std;

#include <string>
#include <vector>

#include "proxy.h"

class Template
{
public:

  /**
   * @brief Период лимита трафика
   */
  enum PeriodType
  {
    PERIOD_MONTH,   ///< Месяц
    PERIOD_WEEK,    ///< Неделя
    PERIOD_CUSTOM   ///< Указанное количество дней
  };

  Template (long id);

  ~Template ();

  /**
   * @brief Возвращает идентификатор шаблона
   *
   * @return Идентификатор шаблона
   */
  long getId () const;

  void setAuth (const string & auth);

  void setAuth (Proxy::usrAuthType auth);

  /**
   * @brief Возвращает тип авторизации шаблона
   *
   * @return Тип авторизации
   */
  Proxy::usrAuthType getAuth () const;

  void setQuote (long quote);

  /**
   * @brief Возвращает размер ограничения трафика
   *
   * @return Размер ограничения трафика
   */
  long getQuote () const;

  /**
   * @brief Устанавливает период ограничения трафика
   *
   * Если тип периода месяц или неделя, то количество дней игнорируется.
   * При нестандартном периоде необходимо установить день очистки счетчиков.
   * @sa setClearDay
   * @param ptype Тип периода
   * @param days Количество дней
   */
  void setPeriod (Template::PeriodType ptype, long days);

  /**
   * @brief Возвращает тип периода ограничения трафика
   * @return Тип периода ограничения трафика
   */
  Template::PeriodType getPeriodType () const;

  /**
   * @brief Устанавливает дату очистки счетчиков
   *
   * Эта функция должна использоваться только при нестандартном периоде ограничения трафика.
   * В противном случае она будет проигнорирована.
   * @param dateSpec Дата очистки счетчиков в формате YYYY-MM-DD
   */
  void setClearDate (const string & dateSpec);

  /**
   * @brief Возвращает дату очистки счетчиков
   *
   * Если функция возвращает false, значит установлен стандартный период ограничения трафика
   * или дата не была установлена или установлена неверная дата.
   * @return false при ошибке и true при успешном выполнении
   */
  bool getClearDate (struct tm & clear_date) const;

  /**
   * @brief Устанавливает флаг запрета ко всем ресурсам, кроме разрешенных
   *
   * @param alldeny Если true, то запрещать ресурс, если он не разрешен
   */
  void setAllDeny(bool alldeny);

  bool getAllDeny () const;

  /**
   * @brief Добавляет идентификатор временного ограничения
   *
   * @param id Идентификатор временного ограничения
   */
  void addTimeRange (long id);

  /**
   * @brief Возвращает список идентификаторов временных границ, когда доступ разрешен
   *
   * @return Список идентификаторов временных границ
   */
  vector <long> getTimeRangeIds () const;

  /**
   * @brief Добавляет идентификатор групы ресурсов
   *
   * @param id Идентификатор группы ресурсов
   */
  void addUrlGroup (long id);

  /**
   * @brief Возвращает список идентификаторов групп ресурсов
   *
   * @return Список идентификаторов групп ресурсов
   */
  vector <long> getUrlGroupIds () const;

  /**
   * @brief Возвращает возможность подключения в текущее время
   *
   * На текущей реализации параметр \a url игнорируется, т.к. не позволяет структура БД
   *
   * @param url url адрес ресурса
   * @return true если ресурс в текущее время доступен и false в противном случае
   */
  bool isTimeDenied (const string & url) const;

  /**
   * @brief Возвращает признак доступности ресурса
   *
   * @param url url адрес ресурса
   * @return true если ресурс присутствует в "белых" списках и false в противном случае
   */
  bool isUrlWhitelisted (const string & url) const;

  /**
   * @brief Возвращает признак доступности ресурса
   *
   * @param url url адрес ресурса
   * @return true если ресурс присутствует в списках запрета и false в противном случае
   */
  bool isUrlBlacklisted (const string & url) const;

private:
  long _id;
  Proxy::usrAuthType _auth;
  long _quote;
  Template::PeriodType _period_type;
  long _period_days;
  string _clear_date;
  bool _alldeny;
  vector <long> _times;
  vector <long> _urlgroups;
};

#endif
