#ifndef SAMS_USER_H
#define SAMS_USER_H

#include "defines.h"

enum usrAuthType {
  //! �� ������������
  AUTH_NONE,
  //! ����������� � ������ Windows ����� SMB ��������
  AUTH_NTLM,
  //! ����������� � ������ ActiveDirectory
  AUTH_ADLD,
  //! ����������� � OpenLDAP
  AUTH_OPLD,
  //! ����������� NCSA
  AUTH_NCSA,
  //! ����������� �� �������� ������ �������
  AUTH_IP
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
