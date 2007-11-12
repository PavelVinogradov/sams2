#include "samsuser.h"
#include "debug.h"
#include "tools.h"




SAMSuser::SAMSuser ()
{
  DEBUG (DEBUG4, "New instance " << this);
  _changed = false;
  _was_disabled = false;
}

SAMSuser::~SAMSuser ()
{
  DEBUG (DEBUG4, "Destroy instance " << this);
}

/*! \todo Преобразовывать в нижний регистр
 */
void SAMSuser::setName (const string name)
{
  DEBUG (DEBUG4, "[" << this << "] " << name);
  _name = name;
}

string SAMSuser::getName ()
{
  return _name;
}

/*! \todo Преобразовывать в нижний регистр
 */
void SAMSuser::setDomain (const string domain)
{
  DEBUG (DEBUG4, "[" << this << "] " << domain);
  _domain = domain;
}

void SAMSuser::setIP (const string ip, const string ipmask)
{
  DEBUG (DEBUG4, "[" << this << "] " << ip << ", " << ipmask);
  _octets = StringToIP (ip + "/" + ipmask, &_ip[0], &_mask[0]);
}

void SAMSuser::parseEnabled (int enabled)
{
  DEBUG (DEBUG4, "[" << this << "] " << enabled);
  _enabled = (usrStatus) enabled;
}

void SAMSuser::setEnabled (usrStatus enabled)
{
  DEBUG (DEBUG4, "[" << this << "] " << toString (enabled));
  if (_enabled != enabled)
    _changed = true;
  _enabled = enabled;
}

usrStatus SAMSuser::getEnabled ()
{
  return _enabled;
}

void SAMSuser::setSize (long size)
{
  DEBUG (DEBUG4, "[" << this << "] " << size);
  _size = size;
}

/*!
 * \todo Менять флаг активности в зависимости от трафика и квоты
 */
void SAMSuser::addSize (long size)
{
  DEBUG (DEBUG4, "[" << this << "] " << size);
  _size += size;
  _changed = true;
  if (_hit > _size)
    {
      WARNING ("hit more then size");
    }
}

long SAMSuser::getSize ()
{
  return _size;
}

void SAMSuser::setQuote (long quote)
{
  DEBUG (DEBUG4, "[" << this << "] " << quote);
  _quote = quote;
}

void SAMSuser::setID (const string id)
{
  DEBUG (DEBUG4, "[" << this << "] " << id);
  _id = id;
}

string SAMSuser::getID ()
{
  return _id;
}

void SAMSuser::setHit (long hit)
{
  DEBUG (DEBUG4, "[" << this << "] " << hit);
  _hit = hit;
}

void SAMSuser::addHit (long hit)
{
  DEBUG (DEBUG4, "[" << this << "] " << hit);
  _hit += hit;
  _changed = true;
}

long SAMSuser::getHit ()
{
  return _hit;
}

void SAMSuser::setShablon (const string shablon)
{
  DEBUG (DEBUG4, "[" << this << "] " << shablon);
  _shablon = shablon;
}

void SAMSuser::parseAuth (const string auth)
{
  DEBUG (DEBUG4, "[" << this << "] " << auth);
  if (auth == "none")
    _auth = AUTH_NONE;
  else if (auth == "ntlm")
    _auth = AUTH_NTLM;
  else if (auth == "adld")
    _auth = AUTH_ADLD;
  else if (auth == "opld")
    _auth = AUTH_OPLD;
  else if (auth == "ncsa")
    _auth = AUTH_NCSA;
  else if (auth == "ip")
    _auth = AUTH_IP;
}

void SAMSuser::setAuth (usrAuthType auth)
{
  DEBUG (DEBUG4, "[" << this << "] " << toString (auth));
  _auth = (usrAuthType) auth;
}

bool SAMSuser::hasChanged ()
{
  return _changed;
}

char s_res[DEBUG_LINE_SIZE];
string SAMSuser::asString ()
{
  string res;

  sprintf (&s_res[0],
           "SAMSuser[%p] %-15s %-10s %s %-10s %10ld %10ld %-10s %-5s",
           this, _name.c_str (), _domain.c_str (), IPToString (_ip, _mask, _octets).c_str (), toString (_enabled).c_str (), _size, _quote, _id.c_str (), toString (_auth).c_str ());

  res = s_res;

  return res;
}

string toString (usrAuthType obj)
{
  string res = " ";
  switch (obj)
    {
    case AUTH_NONE:
      res = "none";
      break;
    case AUTH_NTLM:
      res = "ntlm";
      break;
    case AUTH_ADLD:
      res = "adld";
      break;
    case AUTH_OPLD:
      res = "opld";
      break;
    case AUTH_NCSA:
      res = "ncsa";
      break;
    case AUTH_IP:
      res = "ip";
      break;
    };
  return res;
}

string toString (usrStatus obj)
{
  string res = " ";
  switch (obj)
    {
    case STAT_OFF:
      res = "off";
      break;
    case STAT_INACTIVE:
      res = "inactive";
      break;
    case STAT_ACTIVE:
      res = "active";
      break;
    };
  return res;
}


Users::Users ()
{
}

Users::~Users ()
{
}

bool Users::Read (DB * database)
{
  int idx;
  DBQuery query (database);

  DEBUG (DEBUG4, "Start loading users from DB");

  string SQLcmd =
    "SELECT squidusers.s_nick, squidusers.s_domain, squidusers.s_ip, squidusers.s_ipmask, squidusers.s_enabled, squidusers.s_size, squidusers.s_quotes, squidusers.s_id, squidusers.s_hit, squidusers.s_shablon, shablons.s_auth FROM squidusers LEFT JOIN shablons ON squidusers.s_shablon=shablons.s_name";

  char s_nick[30];
  char s_domain[30];
  char s_ip[20];
  char s_ipmask[20];
  long s_enabled;
  long s_size;
  long s_quotes;
  char s_id[30];
  long s_hit;
  char s_shablon[30];
  char s_auth[10];
  SAMSuser *usr;

  if (query.BindCol (1, SQL_C_CHAR, &s_nick[0], 30) != true)
    {
      return false;
    }
  if (query.BindCol (2, SQL_C_CHAR, &s_domain[0], 30) != true)
    {
      return false;
    }
  if (query.BindCol (3, SQL_C_CHAR, &s_ip[0], 20) != true)
    {
      return false;
    }
  if (query.BindCol (4, SQL_C_CHAR, &s_ipmask[0], 20) != true)
    {
      return false;
    }
  if (query.BindCol (5, SQL_C_LONG, &s_enabled, 0) != true)
    {
      return false;
    }
  if (query.BindCol (6, SQL_C_UBIGINT, &s_size, 0) != true)
    {
      return false;
    }
  if (query.BindCol (7, SQL_C_UBIGINT, &s_quotes, 0) != true)
    {
      return false;
    }
  if (query.BindCol (8, SQL_C_CHAR, &s_id[0], 30) != true)
    {
      return false;
    }
  if (query.BindCol (9, SQL_C_LONG, &s_hit, 0) != true)
    {
      return false;
    }
  if (query.BindCol (10, SQL_C_CHAR, &s_shablon[0], 30) != true)
    {
      return false;
    }
  if (query.BindCol (11, SQL_C_CHAR, &s_auth[0], 10) != true)
    {
      return false;
    }
  if (query.SendQueryDirect (SQLcmd) != true)
    {
      return false;
    }
  idx = 0;

  memset (&s_nick[0], 0, 30);
  memset (&s_domain[0], 0, 30);
  memset (&s_ip[0], 0, 20);
  memset (&s_ipmask[0], 0, 20);
  memset (&s_id[0], 0, 30);
  memset (&s_shablon[0], 0, 30);
  memset (&s_auth[0], 0, 10);

  while (query.Fetch () != SQL_NO_DATA)
    {
      idx++;
      usr = new SAMSuser ();

      usr->setName (s_nick);
      usr->setDomain (s_domain);
      usr->setIP (s_ip, s_ipmask);
      usr->parseEnabled (s_enabled);
      usr->setSize (s_size);
      usr->setQuote (s_quotes);
      usr->setID (s_id);
      usr->setHit (s_hit);
      usr->setShablon (s_shablon);
      usr->parseAuth (s_auth);

      _users.push_back (usr);

      memset (&s_nick[0], 0, 30);
      memset (&s_domain[0], 0, 30);
      memset (&s_ip[0], 0, 20);
      memset (&s_ipmask[0], 0, 20);
      memset (&s_id[0], 0, 30);
      memset (&s_shablon[0], 0, 30);
      memset (&s_auth[0], 0, 10);
    }

  return true;
}

/*!
 *  \todo проверить тип идентификации (DOMAIN/username, username, IP)
 *  \todo при отсутствии в списке проверить в БД
 *  \todo при отсутствии в БД проверить состояние флагов автоматического создания
 *        пользователей и, если необходимо, создать
 */
SAMSuser *Users::findByIdent (const string ident)
{
  std::vector < SAMSuser * >::iterator it;
  SAMSuser *res = NULL;
  DEBUG (DEBUG4, "Looking for " << ident);
  for (it = _users.begin (); it != _users.end (); it++)
    {
//      DEBUG (DEBUG4, "Trying " << (*it)->getName ());
      if ((*it)->getName () == ident)
        {
          DEBUG (DEBUG4, "Found " << (*it)->getName ());
          res = (*it);
          break;
        }
    }
  return res;
}

void Users::saveToDB (DB * database)
{
  std::vector < SAMSuser * >::iterator it;
  int enabled;
  long size;
  long hit;
  char id[30];

  DEBUG (DEBUG4, "Start saving to DB");

  DBQuery query (database);

//  query.PrepareQuery("UPDATE squidusers SET s_enabled=?, s_size=?, s_hit=? WHERE s_id='?'");
  query.PrepareQuery ("UPDATE squidusers SET s_size=?, s_hit=? WHERE s_id=?");
//  query.BindParam(1, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, 0, 0, &enabled, 0);
  query.BindParam (1, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, 0, 0, &size, 0);
  query.BindParam (2, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, 0, 0, &hit, 0);
  query.BindParam (3, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_VARCHAR, 0, 0, &id[0], sizeof (id));

  for (it = _users.begin (); it != _users.end (); it++)
    {
//      DEBUG (DEBUG4, "Processing " << (*it)->getName ());
      size = (*it)->getSize ();
      hit = (*it)->getHit ();
      enabled = (int) (*it)->getEnabled ();
      sprintf (id, "%s", (*it)->getID ().c_str ());

      DEBUG (DEBUG4, "size=" << size);
      DEBUG (DEBUG4, "hit=" << hit);
      DEBUG (DEBUG4, "enabled=" << enabled);
      DEBUG (DEBUG4, "id=" << id);

      query.SendQuery ();
      DEBUG (DEBUG4, "Affected rows:  " << query.RowsCount ());
    }
}

void Users::Print ()
{
  std::vector < SAMSuser * >::iterator it;
  int idx;
  INFO ("Total " << _users.size () << " user[s] available:");
  idx = 0;
  for (it = _users.begin (); it != _users.end (); it++)
    {
      INFO (std::setw (3) << ++idx << " " << (*it)->asString ());
    }
}

void Users::setAutoCreation (bool autocreation)
{
}

void Users::setAutoTemplate (usrUseAutoTemplate tplKind, const string tplName)
{
}

void Users::setAutoGroup (usrUseAutoGroup grpKind, const string grpName)
{
}
