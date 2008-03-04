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
  static string toString (TrafficType t);

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
   * @brief Способ обработки лог файла squid
   */
  enum ParserType
  {
    PARSE_NONE,                 ///< Не обрабатывать
    PARSE_DISCRET,              ///< Обрабатывать через N минут
    PARSE_IMMEDIATE             ///< Обрабатывать непрерывно
  };

  /**
   * @brief Преобразование способа авторизации в строку
   * @param t Способ авторизации
   * @return Способ авторизации в виде строки
   */
  static string toString (usrAuthType t);

  static void useConnection (DBConn * conn);

  static bool reload ();

  static void destroy();

  /**
   * @brief Возвращает идентификатор прокси
   *
   * @return Идентификатор прокси
   */
  static long getId ();

  /**
   * @brief Возвращает смещение в файле, откуда нужно читать значения
   *
   * @return Смещение в файле
   */
  static long getEndValue ();

  static long getKbSize ();

  static void getParserType (ParserType & ptype, long & ptime);

  static TrafficType getTrafficType ();

  static string getRedirectAddr ();

  static long getCacheAge ();

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
  static SAMSUser *findUser (const IP & ip, const string & ident);

  /**
   * @brief Поиск пользователя SAMS
   *
   * Поиск происходит в зависимости от настроек прокси.
   * Если настроено автоматическое создание пользователей, то будет
   * произведена попытка создать его.
   *
   * @param ip IP адрес в виде строки
   * @param ident Ник пользователя, включая домен
   * @return Указатель на найденного пользователя или NULL при его отсутствии
   */
  static SAMSUser *findUser (const string & ip, const string & ident);

protected:
  /**
   * @brief Загружает настройки
   */
  static bool load ();

  static bool _loaded;                 ///< Флаг, показывающий загружены ли параметры из БД
  static usrAuthType _auth;            ///< Способ авторизации пользователей
  static TrafficType _trafType;        ///< Тип учитываемого трафика
  static long _id;                     ///< Идентификатор прокси
  static long _kbsize;                 ///< Размер килобайта
  static long _endvalue;               ///< Смещение в файле, начиная с которого нужно считывать данные
  static bool _needResolve;            ///< Необходимость обращения к DNS
  static bool _usedomain;              ///< Использовать или нет домен по умолчанию
  static string _defaultdomain;        ///< Домен по умолчанию
  static ParserType _parser_type;      ///< Тип обработки лог файла squid
  static long _parser_time;            ///< Интервал времени, через который обрабатывать лог файл squid
  static bool _autouser;               ///< Создавать или нет пользователя, если он не существует
  static long _defaulttpl;             ///< Идентификатор шаблона, используемого при автоматическом создании пользователя
  static long _defaultgrp;             ///< Идентификатор группы, используемой при автоматическом создании пользователя
  static long _squidbase;              ///< Хранить данные о трафике за последние @a _squidbase месяцев
  static DBConn *_conn;                ///< Соединение с БД
  static bool _connection_owner;       ///< Флаг, показывающий нужно ли закрывать соединение в методе @a destroy
};

#endif
