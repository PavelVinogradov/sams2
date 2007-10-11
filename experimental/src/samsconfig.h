#ifndef SAMS_CONFIG_H
#define SAMS_CONFIG_H

#include "defines.h"

#define SQUID_DB          "SQUID_DB"
#define SAMS_DB           "SAMS_DB"
#define SAMSDEBUG         "SAMSDEBUG"
#define SQUIDCACHEFILE    "SQUIDCACHEFILE"
#define SQUIDROOTDIR      "SQUIDROOTDIR"
#define SQUIDLOGDIR       "SQUIDLOGDIR"
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
 *  ������ ����� ������������.
 *  \return true ��� ���������� ������ � false � ��������� ������
 *  \param filename ��� ����� ��������.
 *  \retval true
 *  \retval false
 */
bool cfgRead (const string filename);

/*!
 *  ���������� ��������� �������� ��������� \c attrname.
 *  ��� ������������� ������ \c err ��������������� � ��������� ��������.
 *  \param attrname ������������ ��� ���������
 *  \param err ��� ������
 *  \return �������� ���������
 */
string cfgGetString (const string attrname, int &err);

/*!
 *  ���������� ������������� �������� ��������� \c attrname.
 *  ��� ������������� ������ \c err ��������������� � ��������� ��������.
 *  \param attrname ������������ ��� ���������
 *  \param err ��� ������
 *  \return �������� ���������
 */
int cfgGetInt (const string attrname, int &err);

/*!
 *  ���������� ������� �������� ��������� \c attrname.
 *  ��� ������������� ������ \c err ��������������� � ��������� ��������.
 *  \param attrname ������������ ��� ���������
 *  \param err ��� ������
 *  \return �������� ���������
 */
double cfgGetDouble (const string attrname, int &err);

/*!
 *  ���������� ���������� �������� ��������� \c attrname.
 *  ��� ������������� ������ \c err ��������������� � ��������� ��������.
 *  \param attrname ������������ ��� ���������
 *  \param err ��� ������
 *  \return �������� ���������
 */
bool cfgGetBool (const string attrname, int &err);

/*!
 *  ������������� ��������� �������� \c attrname � �������� \c value.
 *  \param attrname ��� ���������
 *  \param value �������� ���������
 */
void cfgSetString (const string attrname, const string value);

/*!
 *  ������������� ������������� �������� \c attrname � �������� \c value.
 *  \param attrname ��� ���������
 *  \param value �������� ���������
 */
void cfgSetInt (const string attrname, const int value);

/*!
 *  ������������� ������� �������� \c attrname � �������� \c value.
 *  \param attrname ��� ���������
 *  \param value �������� ���������
 */
void cfgSetDouble (const string attrname, const double value);

/*!
 *  ������������� ���������� �������� \c attrname � �������� \c value.
 *  \param attrname ��� ���������
 *  \param value �������� ���������
 */
void cfgSetBool (const string attrname, const bool value);

#endif /* #ifndef SAMS_CONFIG_H */
