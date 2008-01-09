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
#ifndef PROXY_H
#define PROXY_H

using namespace std;

#include <string>

#include "ip.h"

class DBConn;
class SAMSUsers;
class SAMSUser;


class Proxy
{
public:
  /**
   * @brief Тип учитываемого трафика
   */
  enum TrafficType
  {
    TRAF_REAL,                  ///< Реально полученный (не учитывая кэш)
    TRAF_FULL                   ///< Полный трафик (включая кэш)
  };

  /**
   * @brief Преобразование типа учитываемого трафика в строку
   * @param t Тип учитываемого трафика
   * @return Тип учитываемого трафика в виде строки
   */
  string toString (TrafficType t);

  /**
   * @brief Способ авторизации пользователя
   */
  enum usrAuthType
  {
    AUTH_NONE,                  ///< Не используется
    AUTH_NTLM,                  ///< Авторизация в домене Windows через SMB протокол
    AUTH_ADLD,                  ///< Авторизация в домене ActiveDirectory
    AUTH_LDAP,                  ///< Авторизация в OpenLDAP
    AUTH_NCSA,                  ///< Авторизация NCSA
    AUTH_IP                     ///< Авторизация по сетевому (IP) адресу
  };

  /**
   * @brief Преобразование способа авторизации в строку
   * @param t Способ авторизации
   * @return Способ авторизации в виде строки
   */
  string toString (usrAuthType t);

  /**
   * @brief Конструктор
   *
   * @param id Идентификатор прокси
   * @param conn Соединение с БД
   */
    Proxy (long id, DBConn * connection);

  /**
   * @brief Деструктор
   */
   ~Proxy ();

  /**
   * @brief Возвращает идентификатор прокси
   *
   * @return Идентификатор прокси
   */
  long getId ();

  /**
   * @brief Поиск пользователя SAMS
   *
   * Поиск происходит в зависимости от настроек прокси.
   * Если настроено автоматическое создание пользователей, то будет
   * произведена попытка создать его.
   *
   * @param ip IP адрес
   * @param ident Ник пользователя, включая домен
   * @return Указатель на найденного пользователя или NULL при его отсутствии
   */
  SAMSUser *findUser (const IP & ip, const string & ident);

  /**
   * @brief Записывает в БД измененные счетчики и статусы пользователей
   */
  void commitChanges ();

protected:
  /**
   * @brief Загружает настройки
   */
  void load ();

  usrAuthType _auth;            ///< Способ авторизации пользователей
  TrafficType _trafType;        ///< Тип учитываемого трафика
  long _id;                     ///< Идентификатор прокси
  long _kbsize;                 ///< Размер килобайта
  long _mbsize;                 ///< Размер мегабайта
  bool _needResolve;            ///< Необходимость обращения к DNS
  DBConn *_conn;                ///< Соединение с БД
  SAMSUsers *_users;            ///< Список пользователей
};

#endif
