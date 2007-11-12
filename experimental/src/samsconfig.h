#ifndef SAMS_CONFIG_H
#define SAMS_CONFIG_H

#include "defines.h"
#include "samsdb.h"

#define defDBSOURCE          "DBSOURCE"
#define defDBUSER            "DBUSER"
#define defDBPASSWORD        "DBPASSWORD"
#define defSQUIDLOGDIR       "SQUIDLOGDIR"
#define defSQUIDCACHEFILE    "SQUIDCACHEFILE"

#define SQUID_DB          "SQUID_DB"
#define SAMS_DB           "SAMS_DB"
#define SAMSDEBUG         "SAMSDEBUG"
#define SQUIDROOTDIR      "SQUIDROOTDIR"
#define SAMSPATH          "SAMSPATH"
#define SQUIDPATH         "SQUIDPATH"
#define SQUIDGUARDLOGPATH "SQUIDGUARDLOGPATH"
#define SQUIDGUARDDBPATH  "SQUIDGUARDDBPATH"
#define RECODECOMMAND     "RECODECOMMAND"
#define LDAPSERVER        "LDAPSERVER"
#define LDAPBASEDN        "LDAPBASEDN"
#define LDAPUSER          "LDAPUSER"
#define LDAPUSERPASSWD    "LDAPUSERPASSWD"
#define LDAPUSERSGROUP    "LDAPUSERSGROUP"
#define REJIKPATH         "REJIKPATH"
#define SHUTDOWNCOMMAND   "SHUTDOWNCOMMAND"

/*!
 *  Чтение файла конфигурации.
 *  \return true при отсутствии ошибок и false в противном случае
 *  \param filename Имя файла настроек.
 *  \retval true
 *  \retval false
 */
bool cfgRead (const string filename);

/*!
 *  Возвращает строковое значение параметра \c attrname.
 *  При возникновении ошибки \c err устанавливается в ненулевое значение.
 *  \param attrname Интересуемое имя параметра
 *  \param err Код ошибки
 *  \return Значение параметра
 */
string cfgGetString (const string attrname, int &err);

/*!
 *  Возвращает целочисленное значение параметра \c attrname.
 *  При возникновении ошибки \c err устанавливается в ненулевое значение.
 *  \param attrname Интересуемое имя параметра
 *  \param err Код ошибки
 *  \return Значение параметра
 */
int cfgGetInt (const string attrname, int &err);

/*!
 *  Возвращает дробное значение параметра \c attrname.
 *  При возникновении ошибки \c err устанавливается в ненулевое значение.
 *  \param attrname Интересуемое имя параметра
 *  \param err Код ошибки
 *  \return Значение параметра
 */
double cfgGetDouble (const string attrname, int &err);

/*!
 *  Возвращает логическое значение параметра \c attrname.
 *  При возникновении ошибки \c err устанавливается в ненулевое значение.
 *  \param attrname Интересуемое имя параметра
 *  \param err Код ошибки
 *  \return Значение параметра
 */
bool cfgGetBool (const string attrname, int &err);

/*!
 *  Устанавливает строковый параметр \c attrname в значение \c value.
 *  \param attrname Имя параметра
 *  \param value Значение параметра
 */
void cfgSetString (const string attrname, const string value);

/*!
 *  Устанавливает целочисленный параметр \c attrname в значение \c value.
 *  \param attrname Имя параметра
 *  \param value Значение параметра
 */
void cfgSetInt (const string attrname, const int value);

/*!
 *  Устанавливает дробный параметр \c attrname в значение \c value.
 *  \param attrname Имя параметра
 *  \param value Значение параметра
 */
void cfgSetDouble (const string attrname, const double value);

/*!
 *  Устанавливает логический параметр \c attrname в значение \c value.
 *  \param attrname Имя параметра
 *  \param value Значение параметра
 */
void cfgSetBool (const string attrname, const bool value);

class Config
{
public:
  Config ();
  ~Config ();
  bool Read (DB * database);
  float getKBSize ();
  float getMBSize ();
  bool useRealTraf ();
protected:
    DB * db;
  float kb_size;
  float mb_size;
};
#endif /* #ifndef SAMS_CONFIG_H */
