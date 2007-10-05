#include "samsuser.h"
#include "debug.h"


struct SAMSuser
{
  string   name;
  string   domain;
  int      ip[6];
  int      mask[6];
  int      enabled;
  int      disabled;
  double   size;
  double   hit;
  double   traffic;
  double   quote;
  string   id;
  string   date;
  int      updated;
  AuthType auth;
};


typedef std::vector<SAMSuser> users;

bool
usrAddToList()
{
  return false;
}

bool
usrAddToDB()
{
  return false;
}

bool
usrLoadFromDB(MYSQL *con)
{
  return true;
}

bool
usrAdd(const string name)
{
  return true;
}

void
usrSetAutoCreation(bool autocreation)
{
}

void
usrSetAutoTemplate(usrUseAutoTemplate tplKind, const string tplName)
{
}

void
usrSetAutoGroup(usrUseAutoGroup grpKind, const string grpName)
{
}

