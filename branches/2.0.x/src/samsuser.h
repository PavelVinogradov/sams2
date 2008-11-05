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
public:
  /**
  * @brief Статус пользователя
  */
  enum usrStatus
  {
    STAT_OFF = -1,                ///< Пользователь отключен
    STAT_INACTIVE,                ///< Пользователь превысил лимит
    STAT_ACTIVE                   ///< Пользователь активен
  };

  /**
  * @brief Преобразование статуса пользователя в строку
  * @param s Статус пользователя
  * @return Статус пользователя в виде строки
  */
  string toString (usrStatus s);

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

  /**
   * @brief Возвращает флаг активности
   *
   * @return Флаг активности
   */
  usrStatus getEnabled () const;

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
  void addSize (long size);

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
  void addHit (long hit);

  /**
   * @brief Возвращает объем трафика из кэша
   *
   * @return Объем трафика из кэша
   */
  long long getHit () const;

  /**
   * @brief Устанавливает квоту трафика в МБ
   *
   * @param quote Квота
   */
  void setQuote (long quote);

  /**
   * @brief Возвращает квоту трафика в МБ
   *
   * @return Квоту трафика
   */
  long getQuote () const;

  /**
   * @brief Устанавливает идентификатор шаблона пользователя
   *
   * @param id Идентификатор шаблона
   */
  void setShablonId (long id);

  /**
   * @brief Возвращает идентификатор шаблона пользователя
   *
   * @return Идентификатор шаблона пользователя
   */
  long getShablonId() const;

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
  int _tpl_id;                  ///< Идентификатор шаблона
  int _grp_id;                  ///< Идентификатор группы
};

#endif
