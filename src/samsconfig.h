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

#include "dbconn.h"

#define defDEBUG              "DEBUGLEVEL"
#define defDBSERVER           "DB_SERVER"
#define defDBENGINE           "DB_ENGINE"
#define defSAMSDB             "SAMS_DB"
#define defDBSOURCE           "ODBCSOURCE"
#define defDBUSER             "DB_USER"
#define defDBPASSWORD         "DB_PASSWORD"
#define defSQUIDBINDIR        "SQUIDPATH"
#define defSQUIDLOGDIR        "SQUIDLOGDIR"
#define defSQUIDCONFDIR       "SQUIDROOTDIR"
#define defSQUIDCACHEFILE     "SQUIDCACHEFILE"
#define defPROXYID            "CACHENUM"
#define defSLEEPTIME          "s_sleep"
#define defDAEMONSTEP         "s_parser_time"

/**
 * @brief Чтение и запись настроек, используя файл и БД
 *
 * @todo Добавить функцию reload, которая перечитывает настройки
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

  static bool reload ();

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

  static DBConn * newConnection ();

  static DBConn::DBEngine getEngine();

private:
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

  static string _config_file;
  static bool _file_loaded;
  static bool _db_loaded;
  static bool _internal;
  static DBConn::DBEngine _engine;
  static map < string, string > _attributes;  ///< Список параметров и их значений
};

#endif
