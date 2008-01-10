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

#define defDEBUG              "DEBUGLEVEL"
#define defDBSERVER           "DB_SERVER"
#define defDBENGINE           "DB_ENGINE"
#define defSAMSDB             "SAMS_DB"
#define defDBSOURCE           "ODBCSOURCE"
#define defDBUSER             "DB_USER"
#define defDBPASSWORD         "DB_PASSWORD"
#define defSQUIDLOGDIR        "SQUIDLOGDIR"
#define defSQUIDCACHEFILE     "SQUIDCACHEFILE"
#define defPROXYID            "CACHENUM"
#define defSLEEPTIME          "s_sleep"
#define defDAEMONSTEP         "s_parser_time"

#include "dbconn.h"

/**
 * @brief Чтение и запись настроек, используя файл и БД
 *
 * @todo Добавить функцию reload, которая перечитывает настройки
 */
class SamsConfig
{
public:
  /**
   * @brief Конструктор
   */
  SamsConfig ();

  /**
   * @brief Деструктор
   */
  ~SamsConfig ();

    /**
     * @brief Чтение файла конфигурации
     *
     * Имя файла берется из параметров configure (sysconfdir/sams2.conf)
     *
     * @return true при отсутствии ошибок и false в противном случае
     */
  bool readFile ();

    /**
     * @brief Загрузка конфигурации из БД
     *
     * Параметры подключения к БД берутся из файла конфигурации.
     * Этот метод должен вызываться ПОСЛЕ метода readFile
     *
     * @return true при отсутствии ошибок и false в противном случае
     */
  bool readDB ();

    /**
     *  @brief Возвращает строковое значение параметра @a attrname.
     *
     *  При возникновении ошибки @a err устанавливается в ненулевое значение.
     *
     * @param attrname  Интересуемое имя параметра
     * @param err Код ошибки
     * @return Значение параметра
     */
  string getString (const string & attrname, int &err);

    /**
     * @brief Возвращает целочисленное значение параметра @a attrname.
     *
     * При возникновении ошибки @a err устанавливается в ненулевое значение.
     *
     * @param attrname Интересуемое имя параметра
     * @param err Код ошибки
     * @return Значение параметра
     */
  int getInt (const string & attrname, int &err);

    /**
     * @brief Возвращает дробное значение параметра @a attrname.
     *
     * При возникновении ошибки @a err устанавливается в ненулевое значение.
     *
     * @param attrname Интересуемое имя параметра
     * @param err Код ошибки
     * @return Значение параметра
     */
  double getDouble (const string & attrname, int &err);

    /**
     * @brief Возвращает логическое значение параметра @a attrname.
     *
     * При возникновении ошибки @a err устанавливается в ненулевое значение.
     *
     * @param attrname Интересуемое имя параметра
     * @param err Код ошибки
     * @return Значение параметра
     */
  bool getBool (const string & attrname, int &err);

    /**
     * @brief Устанавливает строковый параметр @a attrname в значение @a value.
     *
     * @param attrname Имя параметра
     * @param value Значение параметра
     */
  void setString (const string & attrname, const string & value);

    /**
     * @brief Устанавливает целочисленный параметр @a attrname в значение @a value.
     *
     * @param attrname Имя параметра
     * @param value Значение параметра
     */
  void setInt (const string & attrname, const int &value);

    /**
     * @brief Устанавливает дробный параметр @a attrname в значение @a value.
     *
     * @param attrname Имя параметра
     * @param value Значение параметра
     */
  void setDouble (const string & attrname, const double &value);

    /**
     * @brief Устанавливает логический параметр @a attrname в значение @a value.
     *
     * @param attrname Имя параметра
     * @param value Значение параметра
     */
  void setBool (const string attrname, const bool value);

  DBConn::DBEngine getEngine();

private:
  DBConn::DBEngine _engine;
  map < string, string > attributes;  ///< Список параметров и их значений
};

#endif
