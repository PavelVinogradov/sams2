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

#include "config.h"

#ifdef USE_UNIXODBC
#include "odbcconn.h"
#include "odbcquery.h"
#endif

#include "dbconn.h"
#include "samsusers.h"
#include "samsuser.h"
#include "debug.h"

SAMSUsers::SAMSUsers ()
{
  DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] ");
}


SAMSUsers::~SAMSUsers ()
{
}

bool SAMSUsers::load (DBConn * conn)
{
  SAMSUser *usr;

  long s_user_id;
  int s_group_id;
  int s_shablon_id;
  char s_nick[50];
  char s_domain[50];
  int s_quote;
  long s_size;
  long s_hit;
  long s_enabled;
  char s_ip[15];

  DEBUG (DEBUG_USER, "[" << this << "->" << __FUNCTION__ << "] " << conn);

  if (conn->getEngine() == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      ODBCQuery queryODBC( (ODBCConn*)conn );
      if (!queryODBC.bindCol (1, SQL_C_LONG, &s_user_id, 0))
        return false;
      if (!queryODBC.bindCol (2, SQL_C_LONG, &s_group_id, 0))
        return false;
      if (!queryODBC.bindCol (3, SQL_C_LONG, &s_shablon_id, 0))
        return false;
      if (!queryODBC.bindCol (4, SQL_C_CHAR, s_nick, sizeof(s_nick)))
        return false;
      if (!queryODBC.bindCol (5, SQL_C_CHAR, s_domain, sizeof(s_domain)))
        return false;
      if (!queryODBC.bindCol (6, SQL_C_LONG, &s_quote, 0))
        return false;
      if (!queryODBC.bindCol (7, SQL_C_LONG, &s_size, 0))
        return false;
      if (!queryODBC.bindCol (8, SQL_C_LONG, &s_hit, 0))
        return false;
      if (!queryODBC.bindCol (9, SQL_C_LONG, &s_enabled, 0))
        return false;
      if (!queryODBC.bindCol (10, SQL_C_CHAR, s_ip, sizeof(s_ip)))
        return false;

      if (!queryODBC.sendQueryDirect ("select s_user_id, s_group_id, s_shablon_id, s_nick, s_domain, s_quote, s_size, s_hit, s_enabled, s_ip from squiduser"))
        return false;

      while (queryODBC.fetch ())
        {
          usr = new SAMSUser ();
          usr->setId (s_user_id);
          usr->setGroupId (s_group_id);
          usr->setShablonId (s_shablon_id);
          usr->setNick (s_nick);
          usr->setDomain (s_domain);
          usr->setQuote (s_quote);
          usr->setSize (s_size);
          usr->setHit (s_hit);
          usr->setEnabled (s_enabled);
          usr->setIP (s_ip);

          _users.push_back (usr);
        }

      #endif
    }

  return true;
}


SAMSUser *SAMSUsers::findUserByNick (const string & domain, const string & nick)
{
  if (nick == "-")
    return NULL;

  SAMSUser *usr = NULL;
  vector < SAMSUser * >::iterator it;
  for (it = _users.begin (); it != _users.end (); it++)
    {
      if (((*it)->getNick () == nick) && ((*it)->getDomain () == domain))
        {
          usr = (*it);
          break;
        }
    }
  return usr;
}

SAMSUser *SAMSUsers::findUserByIP (const IP & ip)
{
  SAMSUser *usr = NULL;
  vector < SAMSUser * >::iterator it;
  for (it = _users.begin (); it != _users.end (); it++)
    {
      if ((*it)->getIP ().equal (ip))
        {
          usr = (*it);
          break;
        }
    }
  return usr;
}
