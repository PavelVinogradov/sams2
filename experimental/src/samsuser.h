#ifndef SAMS_USER_H
#define SAMS_USER_H

#include "defines.h"

enum usrAuthType {
  //! Не используется
  AUTH_NONE,
  //! Авторизация в домене Windows через SMB протокол
  AUTH_NTLM,
  //! Авторизация в домене ActiveDirectory
  AUTH_ADLD,
  //! Авторизация в OpenLDAP
  AUTH_OPLD,
  //! Авторизация NCSA
  AUTH_NCSA,
  //! Авторизация по сетевому адресу клиента
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
