#ifndef SAMS_USER_H
#define SAMS_USER_H

#include "defines.h"

enum usrAuthType {
  AUTH_NONE, //! Не используется
  AUTH_NTLM, //! Авторизация в домене Windows через SMB протокол
  AUTH_ADLD, //! Авторизация в домене ActiveDirectory
  AUTH_OPLD, //! Авторизация в OpenLDAP
  AUTH_NCSA, //! Авторизация NCSA
  AUTH_IP    //! Авторизация по сетевому адресу клиента
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
