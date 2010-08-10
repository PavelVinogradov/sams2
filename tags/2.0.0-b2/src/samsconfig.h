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
#ifndef SAMSCONFIG_H
#define SAMSCONFIG_H

#include "config.h"

using namespace std;

#include <map>
#include <string>
#include <string.h>

#include "dbconn.h"

#define defDEBUG              "DEBUGLEVEL"
#define defDBSERVER           "DB_SERVER"
#define defDBENGINE           "DB_ENGINE"
#define defSAMSDB             "SAMS_DB"
#define defDBSOURCE           "ODBCSOURCE"
#define defDBUSER             "DB_USER"
#define defDBPASSWORD         "DB_PASSWORD"
#define defDBVERSION          "s_version"
#define defSQUIDBINDIR        "SQUIDPATH"
#define defSQUIDLOGDIR        "SQUIDLOGDIR"
#define defSQUIDCONFDIR       "SQUIDROOTDIR"
#define defSQUIDCACHEFILE     "SQUIDCACHEFILE"
#define defPROXYID            "CACHENUM"
#define defSHUTDOWNCMD        "SHUTDOWNCOMMAND"
#define defCHECKPASSWDDB      "CHECKPASSWDDB"
#define defSLEEPTIME          "s_sleep"
#define defDAEMONSTEP         "s_parser_time"
#define defSAMSHOME           "SAMSPATH"

#define defLDAPENABLED        "LDAPEnabled"
#define defLDAPSERVER         "LDAPServer"
#define defLDAPBASEDN         "LDAPBaseDN"
#define defLDAPBINDDN         "LDAPBindDN"
#define defLDAPBINDPW         "LDAPBindPw"
#define defLDAPUSERSRDN       "LDAPUsersRDN"
/**
 * @brief Чтение и запись настроек, используя файл и БД
 *
 */
class SamsConfig
{
public:
    /**
     *  @brief Устанавливает путь к конфигурационному файлу.
     *
     *  Используется только для чтения альтернативного конфигурационного файла.
     *
     * @param fname Полный или относительный путь к конфигурационному файлу.
     */
  static void useFile (const string &fname);

  /**
   * @brief Перезагружает настройки
   *
   * Сначала считываются настройки из конфигурационного файла, затем из БД.
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
  static void destroy ();

    /**
     *  @brief Возвращает строковое значение параметра @a attrname.
     *
     *  При возникновении ошибки @a err устанавливается в ненулевое значение.
     *
     * @param attrname  Интересуемое имя параметра
     * @param err Код ошибки
     * @return Значение параметра
     */
  static string getString (const string & attrname, int &err);

    /**
     * @brief Возвращает целочисленное значение параметра @a attrname.
     *
     * При возникновении ошибки @a err устанавливается в ненулевое значение.
     *
     * @param attrname Интересуемое имя параметра
     * @param err Код ошибки
     * @return Значение параметра
     */
  static int getInt (const string & attrname, int &err);

    /**
     * @brief Возвращает дробное значение параметра @a attrname.
     *
     * При возникновении ошибки @a err устанавливается в ненулевое значение.
     *
     * @param attrname Интересуемое имя параметра
     * @param err Код ошибки
     * @return Значение параметра
     */
  static double getDouble (const string & attrname, int &err);

    /**
     * @brief Возвращает логическое значение параметра @a attrname.
     *
     * При возникновении ошибки @a err устанавливается в ненулевое значение.
     *
     * @param attrname Интересуемое имя параметра
     * @param err Код ошибки
     * @return Значение параметра
     */
  static bool getBool (const string & attrname, int &err);

    /**
     * @brief Устанавливает строковый параметр @a attrname в значение @a value.
     *
     * @param attrname Имя параметра
     * @param value Значение параметра
     */
  static void setString (const string & attrname, const string & value);

    /**
     * @brief Устанавливает целочисленный параметр @a attrname в значение @a value.
     *
     * @param attrname Имя параметра
     * @param value Значение параметра
     */
  static void setInt (const string & attrname, const int &value);

    /**
     * @brief Устанавливает дробный параметр @a attrname в значение @a value.
     *
     * @param attrname Имя параметра
     * @param value Значение параметра
     */
  static void setDouble (const string & attrname, const double &value);

    /**
     * @brief Устанавливает логический параметр @a attrname в значение @a value.
     *
     * @param attrname Имя параметра
     * @param value Значение параметра
     */
  static void setBool (const string attrname, const bool value);

  /**
   * @brief Возвращает новое подключение к БД
   *
   * @return Экземпляр класса подключения к БД или NULL если такой создать невозможно
   */
  static DBConn * newConnection ();

  /**
   * @brief Возвращает движок, используемый для подключения к БД
   *
   * @return Движок, используемый для подключения к БД
   */
  static DBConn::DBEngine getEngine();

private:
  /**
   * @brief Чтение настроек
   *
   * Если настройки были считаны ранее, то ничего не происходит.
   * Сначала считываются настройки из конфигурационного файла, затем из БД.
   *
   * @return true если ошибок не произошло и false в противном случае
   * @sa reload
   */
  static bool load();

    /**
     * @brief Чтение файла конфигурации
     *
     * Имя файла берется из параметров configure ($sysconfdir/sams2.conf)
     *
     * @return true при отсутствии ошибок и false в противном случае
     */
  static bool readFile ();

    /**
     * @brief Загрузка конфигурации из БД
     *
     * Параметры подключения к БД берутся из файла конфигурации.
     * Этот метод должен вызываться ПОСЛЕ метода readFile
     *
     * @return true при отсутствии ошибок и false в противном случае
     */
  static bool readDB ();

  static string _config_file;                 ///< Путь к конфигурационному файлу
  static bool _file_loaded;                   ///< Загружены ли параметры из файла
  static bool _db_loaded;                     ///< Загружены ли параметры из БД
  static bool _internal;                      ///< Используется для предотвращения повторных загрузок из файла
  static DBConn::DBEngine _engine;            ///< Используемый движок для подключения к БД
  static map < string, string > _attributes;  ///< Список параметров и их значений
};

#endif
