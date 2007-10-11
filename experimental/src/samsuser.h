#ifndef SAMS_USER_H
#define SAMS_USER_H

#include "defines.h"

enum usrAuthType {
  AUTH_NONE, //! �� ������������
  AUTH_NTLM, //! ����������� � ������ Windows ����� SMB ��������
  AUTH_ADLD, //! ����������� � ������ ActiveDirectory
  AUTH_OPLD, //! ����������� � OpenLDAP
  AUTH_NCSA, //! ����������� NCSA
  AUTH_IP    //! ����������� �� �������� ������ �������
};

enum usrUseAutoTemplate {
  TPL_DEFAULT,
  TPL_SPECIFIED,
  TPL_TAKE_FROM_GROUP
};

enum usrUseAutoGroup {
  GRP_DEFAULT,
  GRP_SPECIFIED,
  GRP_TAKE_FROM_GROUP
};

bool usrLoadFromDB();
bool usrAdd(const string name);
void usrSetAutoCreation(bool autocreation);
void usrSetAutoTemplate(usrUseAutoTemplate tplKind, const string tplName);
void usrSetAutoGroup(usrUseAutoGroup grpKind, const string grpName);

#endif /* SAMS_USER_H */
