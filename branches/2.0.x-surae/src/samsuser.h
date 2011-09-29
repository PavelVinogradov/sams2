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
#ifndef SAMSUSER_H
#define SAMSUSER_H

using namespace std;

#include <string>

#include "ip.h"


/**
 * @brief Пользователь SAMS
 */
class SAMSUser
{
friend class SAMSUserList;
public:
  /**
  * @brief Статус пользователя
  */
  enum usrStatus
  {
    STAT_OFF = -1,                ///< Пользователь отключен
    STAT_INACTIVE,                ///< Пользователь превысил лимит
    STAT_ACTIVE,                  ///< Пользователь активен
    STAT_LIMITED                  ///< Пользователь превысил лимит и переведен в другой шаблон
  };

  /**
  * @brief Преобразование статуса пользователя в строку
  *
  * @param s Статус пользователя
  * @return Статус пользователя в виде строки
  */
  static string toString (usrStatus s);

  /**
   * @brief Конструктор
   */
  SAMSUser ();

  /**
   * @brief Деструктор
   */
  ~SAMSUser ();

  /**
   * @brief Устанавливает идентификатор пользователя в БД
   *
   * @param id Идентификатор пользователя
   */
  void setId (long id);

  /**
   * @brief Возвращает идентификатор пользователя в БД
   *
   * @return Идентификатор пользователя
   */
  long getId () const;

  /**
   * @brief Устанавливает ник (логин) пользователя
   *
   * @param nick Ник пользователя
   */
  void setNick (const string & nick);

  /**
   * @brief Возвращает ник (логин) пользователя
   *
   * @return Ник пользователя
   */
  string getNick () const;

  /**
   * @brief Устанавливает домен
   *
   * @param domain Домен
   */
  void setDomain (const string & domain);

  /**
   * @brief Возвращает домен
   *
   * @return Домен
   */
  string getDomain () const;

  /**
   * @brief Устанавливает IP адрес
   *
   * @param ip IP адрес
   */
  void setIP (const string & ip);

  /**
   * @brief Возвращает IP адрес
   *
   * @return IP адрес
   */
  IP getIP () const;

  /**
   * @brief Возвращает IP адрес в виде строки
   *
   * @return IP адрес в виде строки
   */
  string getIPasString () const;

  /**
   * @brief Устанавливает пароль пользователя
   *
   * Предполагается что пароль передается уже в зашифрованном виде.
   *
   * @param pass Пароль пользователя
   */
  void setPassword (const string & pass);

  /**
   * @brief Возвращает пароль пользователя
   *
   * @return Пароль пользователя
   */
  string getPassword () const;

protected:
  /**
   * @brief Устанавливает флаг активности
   *
   * @param enabled Флаг активности в виде числа
   */
  void setEnabled (int enabled);

  /**
   * @brief Устанавливает флаг активности
   *
   * @param enabled Флаг активности
   */
  void setEnabled (usrStatus enabled);

public:
  /**
   * @brief Возвращает флаг активности
   *
   * @return Флаг активности
   */
  usrStatus getEnabled () const;

  /**
   * @brief Деактивирует пользователя
   *
   * В зависимости от настроек шаблона, пользователь переходит в неактивное состояние
   * или переводится в другой шаблон.
   */
  void deactivate ();

  /**
   * @brief Устанавливает объем израсходованного трафика
   *
   * @param size Объем
   */
  void setSize (long long size);

  /**
   * @brief Увеличивает объем израсходованного трафика на @a size
   *
   * @param size Объем
   */
  void addSize (long long size);

  /**
   * @brief Возвращает объем израсходованного трафика
   *
   * @return Объем израсходованного трафика.
   */
  long long getSize () const;

  /** @brief Устанавливает объем трафика из кэша
   *
   *  @param hit Объем
   */
  void setHit (long long hit);

  /**
   * @brief Увеличивает объем трафика из кэша на @a hit
   *
   * @param hit Объем
   */
  void addHit (long long hit);

  /**
   * @brief Возвращает объем трафика из кэша
   *
   * @return Объем трафика из кэша
   */
  long long getHit () const;

protected:
  /**
   * @brief Устанавливает квоту трафика в МБ
   *
   * Квота указывается в мегабайтах. Значение -1 означает что квота будет взята из текущего шаблона.
   *
   * @param quote Квота пользователя
   */
  void setQuote (long quote);

  /**
   * @brief Возвращает квоту трафика в МБ
   *
   * Возвращает значение квоты, взятое в БД
   *
   * @sa getQuote
   * @return Квоту пользователя
   */
  long getRealQuote () const;

public:
  /**
   * @brief Возвращает квоту трафика в МБ
   *
   * При установленной квоте -1, возвращает значение квоты текущего шаблона
   *
   * @sa getCurrentTemplateId
   * @return Квоту пользователя
   */
  long getQuote () const;

  /**
   * @brief Устанавливает идентификатор основного шаблона пользователя
   *
   * Основной шаблон пользователя используется если статус пользователя не STAT_LIMITED.
   * Если же статус STAT_LIMITED, то используется вторичный шаблон.
   *
   * @sa setLimitedTemplateId
   * @param id Идентификатор основного шаблона
   */
  void setActiveTemplateId (long id);

  /**
   * @brief Устанавливает идентификатор вторичного шаблона пользователя
   *
   * Вторичный шаблон пользователя используется только если статус пользователя STAT_LIMITED.
   *
   * @param id Идентификатор вторичного шаблона
   */
  void setLimitedTemplateId (long id);

  /**
   * @brief Возвращает идентификатор текущего шаблона пользователя
   *
   * @sa setActiveTemplateId, setLimitedTemplateId
   * @return Идентификатор текущего шаблона пользователя
   */
  long getCurrentTemplateId () const;

  /**
   * @brief Устанавливает идентификатор группы пользователя
   *
   * @param id Идентификатор группы
   */
  void setGroupId (long id);

  /**
   * @brief Возвращает идентификатор группы пользователя
   *
   * @return Идентификатор группы пользователя
   */
  long getGroupId() const;

  /**
   * @brief Формирует значения экземпляра класса в виде строки
   *
   * @return Значения экземпляра класса в виде строки
   */
  string asString () const;

  /**
   * @brief Оператор вывода содержимого экземпляра класса в поток
   * @param out Поток вывода
   * @param user Экземпляр класса
   * @return Поток вывода
   */
  friend ostream & operator<< (ostream & out, const SAMSUser & user);
protected:
  long _id;                     ///< Идентификатор пользователя
  string _nick;                 ///< Ник пользователя
  string _domain;               ///< Домен
  string _passwd;               ///< Шифрованный пароль пользователя
  IP _ip;                       ///< IP адрес пользователя
  usrStatus _enabled;           ///< Тип активности пользователя
  long long _size;              ///< Объем использованного трафика
  long long _hit;               ///< Объем трафика, взятого из кэша
  long _quote;                  ///< Квота
  int _tpl_id;                  ///< Идентификатор основного шаблона
  int _tpl_id_2;                ///< Идентификатор вторичного шаблона
  int _grp_id;                  ///< Идентификатор группы
};

#endif
