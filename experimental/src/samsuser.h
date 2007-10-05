#ifndef SAMS_USER_H
#define SAMS_USER_H

#include <mysql.h>
#include "defines.h"

enum usrAuthType {
  NONE, //! �� ������������
  NTLM, //! ����������� � ������ Windows ����� SMB ��������
  ADLD, //! ����������� � ������ ActiveDirectory
  OPLD, //! ����������� � OpenLDAP
  NCSA, //! ����������� NCSA
  IP    //! ����������� �� �������� ������ �������
};

enum usrUseAutoTemplate {
  DEFAULT,
  SPECIFIED,
  TAKE_FROM_GROUP
};

enum usrUseAutoGroup {
  DEFAULT,
  SPECIFIED,
  TAKE_FROM_GROUP
};

bool usrLoadFromDB(MYSQL *con);
bool usrAdd(const string name);
void usrSetAutoCreation(bool autocreation);
void usrSetAutoTemplate(usrUseAutoTemplate tplKind, const string tplName);
void usrSetAutoGroup(usrUseAutoGroup grpKind, const string grpName);

#endif /* SAMS_USER_H */
