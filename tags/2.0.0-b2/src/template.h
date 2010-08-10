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

class UrlGroup;

/**
 * @brief Шаблон пользователей
 */
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
    PERIOD_DAY,     ///< День
    PERIOD_CUSTOM   ///< Указанное количество дней
  };

  /**
   * @brief Конструктор
   *
   * @param id Идентификатор шаблона
   * @param id2 Идентификатор вторичного шаблона
   */
  Template (const long & id, const long & id2);

  /**
   * @brief Деструктор
   *
   */
  ~Template ();

  /**
   * @brief Возвращает идентификатор шаблона
   *
   * @return Идентификатор шаблона
   */
  long getId () const;

  /**
   * @brief Возвращает идентификатор вторичного шаблона
   *
   * @return Идентификатор вторичного шаблона
   */
  long getLimitedId () const;

  /**
   * @brief Устанавливает тип авторизации
   *
   * Тип авторизации может быть: ip, ncsa, ntlm, adld, ldap
   *
   * @param auth Тип авторизации в виде строки
   */
  void setAuth (const string & auth);

  /**
   * @brief Устанавливает тип авторизации
   *
   * @param auth Тип авторизации
   */
  void setAuth (const Proxy::usrAuthType & auth);

  /**
   * @brief Возвращает тип авторизации шаблона
   *
   * @return Тип авторизации
   */
  Proxy::usrAuthType getAuth () const;

  /**
   * @brief Устанавливает ограничение трафика для каждого пользователя шаблона
   *
   * @param quote Ограничение трафика
   */
  void setQuote (const long & quote);

  /**
   * @brief Возвращает размер ограничения трафика
   *
   * @return Размер ограничения трафика
   */
  long getQuote () const;

  /**
   * @brief Устанавливает период ограничения трафика
   *
   * Если тип периода стандартный (месяц, неделя, день), то количество дней игнорируется.
   * При нестандартном периоде необходимо установить день очистки счетчиков.
   *
   * @sa setClearDate
   * @param ptype Тип периода
   * @param days Количество дней
   */
  void setPeriod (const Template::PeriodType & ptype, const long & days);

  /**
   * @brief Возвращает тип периода ограничения трафика
   *
   * @return Тип периода ограничения трафика
   */
  Template::PeriodType getPeriodType () const;

  /**
   * @brief Устанавливает дату очистки счетчиков
   *
   * Эта функция должна использоваться только при нестандартном периоде ограничения трафика.
   * В противном случае она будет проигнорирована.
   *
   * @param dateSpec Дата очистки счетчиков в формате YYYY-MM-DD
   */
  void setClearDate (const string & dateSpec);

  /**
   * @brief Возвращает дату очистки счетчиков
   *
   * Если функция возвращает false, значит установлен стандартный период ограничения трафика
   * или дата не была установлена или установлена неверная дата.
   *
   * @param clear_date Структура для сохранения даты
   * @return false при ошибке и true при успешном выполнении
   */
  bool getClearDate (struct tm & clear_date) const;

  /**
   * @brief Возвращает дату очистки счетчиков в виде строки
   *
   * Если функция возвращает false, значит установлен стандартный период ограничения трафика
   * или дата не была установлена или установлена неверная дата.
   *
   * @param date_str Строка для сохранения даты
   * @return false при ошибке и true при успешном выполнении
   */
  bool getClearDateStr (string & date_str) const;

  /**
   * @brief Изменяет дату очистки счетчиков
   *
   * Добавляет к текущему значению даты очистки счетчиков количество дней периода.
   * Эта функция должна использоваться только при нестандартном периоде ограничения трафика.
   * В противном случае она будет проигнорирована. Новая дата очистки счетчиков не может быть
   * в прошлом, поэтому количество дней периода добавляются до тех пор, пока новая дата не
   * станет в будущем.
   * Дата меняется только в экземпляре класса, в БД изменения не вносятся.
   *
   * @sa TemplateList::saveClearDates
   */
  void adjustClearDate ();

  /**
   * @brief Определяет, входит ли дата в текущий период
   *
   * Если период месяц, то проверяется в текущем ли месяце \a date_time
   * Если период неделя, то в текущей ли неделе.
   * Если нестандартный период, то от даты следующей очистки счетчиков
   * отнимается количество дней периода и найденная дата считается началом периода.
   *
   * @param tm Дата и время, которое необходимо проверить
   * @return true если \a date_time внутри текущего периода
   */
  bool insidePeriod(struct tm &date_time) const;

  /**
   * @brief Устанавливает флаг запрета ко всем ресурсам, кроме явно разрешенных
   *
   * @param alldeny Если true, то запрещать ресурс, если он не разрешен
   */
  void setAllDeny(const bool & alldeny);

  /**
   * @brief Возвращает флаг запрета ко всем ресурсам, кроме явно разрешенных
   *
   * @return true если доступ ко всем ресурсам запрещен
   */
  bool getAllDeny () const;

  /**
   * @brief Добавляет идентификатор временного ограничения
   *
   * @param id Идентификатор временного ограничения
   */
  void addTimeRange (const long & id);

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
  void addUrlGroup (const long & id);

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
   * @brief Возвращает признак недоступности ресурса
   *
   * @param url url адрес ресурса
   * @return true если ресурс присутствует в списках запрета и false в противном случае
   */
  bool isUrlBlacklisted (const string & url) const;

  /**
   * @brief Возвращает признак присутствия запрещенного расширения файла
   *
   * @param url url адрес ресурса
   * @return true если ресурс содержит запрещенное расширение файла и false в противном случае
   */
  bool isUrlHasFileExt (const string & url) const;

  /**
   * @brief Возвращает признак совпадения ресурса со списком регулярных выражений
   *
   * @param url url адрес ресурса
   * @return true если ресурс совпадает с одним из элементов списка регулярных выражений и false в противном случае
   */
  bool isUrlMatchRegex (const string & url) const;

  /**
   * @brief Изменяет url адрес в зависимости от подключенных к шаблону групп адресов
   *
   * @param url url адрес ресурса
   * @return Измененный адрес или пустую строку если адрес не должен быть изменен.
   *
   * @sa UrlGroup::modifyUrl
   */
  string modifyUrl (const string & url) const;

private:
  long _id;                           ///< Идентификатор шаблона
  long _id2;                          ///< Идентификатор вторичного шаблона
  Proxy::usrAuthType _auth;           ///< Тип авторизации
  long _quote;                        ///< Лимит трафика
  Template::PeriodType _period_type;  ///< Тип периода
  long _period_days;                  ///< Количество дней у нестандартного периода
  string _clear_date;                 ///< День следующей очистки счетчиков у нестандартного периода
  bool _alldeny;                      ///< Флаг для отказа в доступе ко всем адресам, кроме явно разрешенных
  vector <long> _times;               ///< Список идентификаторов временных границ
  //vector <long> _urlgroups;
  vector <UrlGroup *> _urlgroups;     ///< Список групп адресов
};

#endif
