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
class SAMSUser;

/**
 * @brief Прокси сервер
 */
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
    AUTH_IP,                    ///< Авторизация по сетевому (IP) адресу
    AUTH_HOST,                  ///< Авторизация по сетевому имени
    AUTH_NTLM,                  ///< Авторизация в домене Windows через SMB протокол
    AUTH_ADLD,                  ///< Авторизация в домене ActiveDirectory
    AUTH_LDAP,                  ///< Авторизация в OpenLDAP
    AUTH_NCSA                   ///< Авторизация NCSA
  };

  /**
   * @brief Преобразование способа авторизации в строку
   * @param t Способ авторизации
   * @return Способ авторизации в виде строки
   */
  static string toString (usrAuthType t);

  /**
   * @brief Преобразование строки в способ авторизации
   * @param s Способ авторизации в виде строки
   * @return Способ авторизации
   */
  static usrAuthType toAuthType(const string & s);

  /**
   * @brief Используемый редиректор
   *
   * Используется тип "перечисление" только для гипотетической возможности
   * выполнять различные действия при различных редиректорах. На данный момент
   * используется только для определения нужно ли вносить списки доступа и
   * ограничения в файл squid.conf
   */
  enum RedirType
  {
    REDIR_NONE,                 ///< Не используется встроенный (sams) редиректор
    REDIR_INTERNAL,             ///< Используется встроенный (sams) редиректор
    REDIR_EXTERNAL              ///< Используется сторонний редиректор
  };

  /**
   * @brief Преобразование типа используемого редиректора в строку
   * @param t Тип редиректора
   * @return Тип редиректора в виде строки
   */
  static string toString (RedirType t);

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
   * @brief Регистр букв
   */
  enum CharCase
  {
    CASE_UPPER,                 ///< Заглавные буквы
    CASE_LOWER,                 ///< Строчные буквы
    CASE_ORIGINAL               ///< Без изменения регистра
  };

  /**
   * @brief Устанавливает использование существующего подключения к БД
   *
   * Метод должен быть использован до вызова reload и load. Иначе будет создано
   * новое подключение к БД.
   *
   * @param conn Существующее подключение к БД
   */
  static void useConnection (DBConn * conn);

  /**
   * @brief Перезагружает параметры из БД
   *
   * @return true при успешном завершении и false в противном случае
   */
  static bool reload ();

  /**
   * @brief Освобождает все ресурсы, выделенные во время работы экземпляра класса
   *
   * Используется для сброса всех переменных в начальное значение
   * без уничтожения экземпляра класса
   */
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
   * Берет значение из БД только один раз, иначе нужно вызывать функию reload()
   *
   * @return Смещение в файле
   */
  static long getEndValue ();

  /**
   * @brief Устанавливает смещение в log файле squid, откуда нужно читать значения
   *
   * Не записывает значение в БД!!! Нарушение уровней с функцией getEndValue()
   *
   * @param val Новое смещение в log файле squid
   */
  static void setEndValue (long val);

  /**
   * @brief Возвращает размер килобайта в байтах
   *
   * @return Размер килобайта в байтах
   */
  static long getKbSize ();

  /**
   * @brief Возвращает способ обработки лог файла squid
   *
   * @param ptype Способ обработки лог файла squid
   * @param ptime Интервал времени, через который обрабатывать лог файл squid
   */
  static void getParserType (ParserType & ptype, long & ptime);

  /**
   * @brief Возвращает тип учитываемого трафика
   *
   * @return Тип учитываемого трафика
   */
  static TrafficType getTrafficType ();

  /**
   * @brief Возвращает url адрес для подстановки вместо баннеров
   *
   * @return url адрес для подмены баннеров
   */
  static string getRedirectAddr ();
  static string getSeparator ();

  /**
   * @brief Возвращает url адрес, используемый при блокировке доступа в редиректоре
   *
   * @return url адрес, используемый при блокировке доступа
   */
  static string getDenyAddr ();

  /**
   * @brief Возвращает электронный адрес администратора
   *
   * @return Электронный адрес администратора
   */
  static string getAdminAddr ();

  /**
   * @brief Возвращает необходимость использования DNS
   *
   * @retval true При необходимости, отправлять запросы DNS серверу
   * @retval false Не отправлять запросы DNS серверу
   */
  static bool isUseDNS ();

  /**
   * @brief Возвращает необходимость использования имени домена
   *
   * @retval true Имя домена используется
   * @retval false Имя домена игнорируется
   */
  static bool useDomain ();

  /**
   * @brief Возвращает используемый регистр букв в имени домена
   *
   * @return Регистр букв
   */
  static CharCase getDomainCase ();

  /**
   * @brief Возвращает используемый регистр букв в имени пользователя
   *
   * @return Регистр букв
   */
  static CharCase getUsernameCase ();

  /**
   * @brief Возвращает тип используемого редиректора
   *
   * @return Тип используемого редиректора
   */
  static RedirType getRedirectType ();

  /**
   * @brief Возвращает количество месяцев для хранения в БД
   *
   * @return Количество месяцев
   */
  static long getCacheAge ();

  /*
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
//  static SAMSUser *findUser (const IP & ip, const string & ident);

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

  /**
   * @brief Необходимость очищения счетчиков пользователей
   *
   * @return true, если необходимо очищать счетчики и false в противном случае
   */
  static bool needClearCounters ();

  /**
   * @brief Необходимость использования ограничения скорости
   *
   * @return true, если необходимо ограничивать скорость и false в противном случае
   */
  static bool useDelayPools ();

  static string createUserHash (const string &auth, const string &ip, const string &domain, const string &nick);
protected:
  /**
   * @brief Загружает настройки
   *
   * Если настройки уже были загружены, то ничего не происходит.
   *
   * @sa reload
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
  static RedirType _redir_type;        ///< Тип используемого редиректора
  static string _deny_addr;            ///< Адрес перенаправления при блокировке доступа
  static string _redir_addr;           ///< Адрес перенаправления для баннеров
  static string _admin_addr;           ///< Электронный адрес администратора для уведомления о блокировке пользователя
  static long _parser_time;            ///< Интервал времени, через который обрабатывать лог файл squid
  static bool _autouser;               ///< Создавать или нет пользователя, если он не существует
  static long _defaulttpl;             ///< Идентификатор шаблона, используемого при автоматическом создании пользователя
  static long _defaultgrp;             ///< Идентификатор группы, используемой при автоматическом создании пользователя
  static long _squidbase;              ///< Хранить данные о трафике за последние @a _squidbase месяцев
  static CharCase _domain_case;        ///< Регистр букв в названии домена
  static CharCase _username_case;      ///< Регистр букв в имени пользователя
  static DBConn *_conn;                ///< Используемое подключение к БД
  static bool _connection_owner;       ///< true если владельцем подключения является экземпляр класса
  static bool _auto_clean_counters;    ///< Очищать или нет счетчики пользователей после окончания периода ограничения
  static bool _use_delay_pools;        ///< Использовать или нет ограничение скорости
  static string _separator;            ///< Используемый сепаратор
};

#endif
