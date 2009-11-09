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
#include <sstream>

#include "config.h"

#include "dbconn.h"
#include "dbquery.h"
#include "dbcleaner.h"
#include "samsconfig.h"
#include "datefilter.h"
#include "userfilter.h"
#include "samsuser.h"
#include "tools.h"
#include "proxy.h"
#include "debug.h"

DBCleaner::DBCleaner ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
  _date_filter = NULL;
  _user_filter = NULL;
  _date_filter_owner = false;
  _tpl_id = -1;
}

DBCleaner::~DBCleaner ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
  if (_date_filter && _date_filter_owner)
    delete _date_filter;
}

void DBCleaner::setUserFilter (UserFilter * filt)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << filt);

  _user_filter = filt;
}

void DBCleaner::setTemplateFilter (long tpl_id)
{
  _tpl_id = tpl_id;
}

void DBCleaner::setDateFilter (DateFilter * filt)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << filt);

  if (_date_filter && _date_filter_owner)
    delete _date_filter;

  _date_filter = filt;
  _date_filter_owner = false;
}

void DBCleaner::setDateFilter (const string & dateSpec)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << dateSpec);

  if (_date_filter && _date_filter_owner)
    delete _date_filter;

  _date_filter = new DateFilter (dateSpec);
  _date_filter_owner = true;
}

void DBCleaner::clearCounters ()
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");

  DBConn *conn = NULL;
  DBQuery *query = NULL;

  conn = SamsConfig::newConnection ();
  if (!conn)
    {
      ERROR ("Unable to create connection.");
      return;
    }

  if (!conn->connect ())
    {
      delete conn;
      return;
    }

  conn->newQuery (query);
  if (!query)
    {
      ERROR ("Unable to create query.");
      delete conn;
      return;
    }

  basic_stringstream < char > strUserFilter;
  if (_user_filter)
    {
      vector <string> users = _user_filter->getUsersList ();
      vector <string> tblSpec;
      string strDomain;
      string strUser;
      for (uint i = 0; i < users.size (); i++)
        {
          Split (users[i], DOMAIN_SEPARATORS, tblSpec);
          if (tblSpec.size () == 2)
            {
              strDomain = tblSpec[0];
              strUser = tblSpec[1];
            }
          else if (tblSpec.size () == 1)
            {
              strUser = tblSpec[0];
            }

          if (!strUserFilter.str ().empty ())
            strUserFilter << " or ";

          strUserFilter << "(";
          if (!strDomain.empty () && Proxy::useDomain ())
            strUserFilter << "s_domain='" << strDomain << "' and ";
          strUserFilter << "s_nick='" << strUser << "'";
          strUserFilter << ")";
        }
    }

  // Составляем sql команду
  basic_stringstream < char > sqlcmd;
  basic_stringstream < char > message;
  sqlcmd << "update squiduser set s_size=0, s_hit=0, s_enabled=1 where s_enabled>=0";

  if ( (_tpl_id == -1) && strUserFilter.str ().empty ())
  {
      message.str("");
      message << "Clear counters for all not disabled users";
      Logger::addLog(Logger::LK_USER, message.str());
  }

  if (_tpl_id != -1)
    {
      message.str("");
      message << "Clear counters for users in template with id=" << _tpl_id;
      Logger::addLog(Logger::LK_USER, message.str());
      sqlcmd << " and (s_shablon_id=" << _tpl_id << ")";
    }

  if (!strUserFilter.str ().empty ())
    sqlcmd << " and (" << strUserFilter.str () << ")";

  query->sendQueryDirect (sqlcmd.str ());

  delete query;
  delete conn;
}

void DBCleaner::clearCache ()
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");

  int err;
  int proxy_id = SamsConfig::getInt (defPROXYID, err);
  if (err != ERR_OK)
    {
      ERROR ("No proxyid defined. Check " << defPROXYID << " in config file.");
      return;
    }

  DBConn *conn = NULL;
  DBQuery *query = NULL;

  conn = SamsConfig::newConnection ();
  if (!conn)
    {
      ERROR ("Unable to create connection.");
      return;
    }

  if (!conn->connect ())
    {
      delete conn;
      return;
    }

  conn->newQuery (query);
  if (!query)
    {
      ERROR("Unable to create query.");
      delete conn;
      return;
    }

  basic_stringstream < char > strDateFilter;
  basic_stringstream < char > strUserFilter;

  if (_date_filter)
    {
      strDateFilter << "s_date>='" << _date_filter->getStartDateAsString () << "'";
      strDateFilter << " and s_date<='" << _date_filter->getEndDateAsString () << "'";
    }

  if (_user_filter)
    {
      vector <string> users = _user_filter->getUsersList ();
      vector <string> tblSpec;
      string strDomain;
      string strUser;
      for (uint i = 0; i < users.size (); i++)
        {
          Split (users[i], DOMAIN_SEPARATORS, tblSpec);
          if (tblSpec.size () == 2)
            {
              strDomain = tblSpec[0];
              strUser = tblSpec[1];
            }
          else if (tblSpec.size () == 1)
            {
              strUser = tblSpec[0];
            }

          if (!strUserFilter.str ().empty ())
            strUserFilter << " or ";

          strUserFilter << "(";
          if (!strDomain.empty () && Proxy::useDomain ())
            strUserFilter << "s_domain='" << strDomain << "' and ";
          strUserFilter << "s_nick='" << strUser << "'";
          strUserFilter << ")";
        }
    }

  basic_stringstream < char >sqlcmd;
  basic_stringstream < char >message;

  sqlcmd << "delete from squidcache where s_proxy_id=" << proxy_id;
  if (!strDateFilter.str ().empty ())
    {
      message.str("");
      message << "Delete from cache records between " << _date_filter->getStartDateAsString () <<
                 " and " << _date_filter->getEndDateAsString ();
      Logger::addLog(Logger::LK_CACHE, message.str());
      sqlcmd << " and (" << strDateFilter.str () << ")";
    }
  if (!strUserFilter.str ().empty ())
    sqlcmd << " and (" << strUserFilter.str () << ")";

  if (!query->sendQueryDirect (sqlcmd.str ()))
    {
      delete query;
      delete conn;
      return;
    }

  sqlcmd.str("");

  sqlcmd << "delete from cachesum where s_proxy_id=" << proxy_id;
  if (!strDateFilter.str ().empty ())
    sqlcmd << " and (" << strDateFilter.str () << ")";
  if (!strUserFilter.str ().empty ())
    sqlcmd << " and (" << strUserFilter.str () << ")";

  query->sendQueryDirect (sqlcmd.str ());

  delete query;
  delete conn;

  return;
}

void DBCleaner::clearOldCache (int nmonth)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");

  int err;
  int proxy_id = SamsConfig::getInt (defPROXYID, err);
  if (err != ERR_OK)
    {
      ERROR ("No proxyid defined. Check " << defPROXYID << " in config file.");
      return;
    }

  DBConn *conn = NULL;
  DBQuery *query = NULL;

  conn = SamsConfig::newConnection ();
  if (!conn)
    {
      ERROR ("Unable to create connection.");
      return;
    }

  if (!conn->connect ())
    {
      delete conn;
      return;
    }

  conn->newQuery (query);
  if (!query)
    {
      ERROR("Unable to create query.");
      delete conn;
      return;
    }

  time_t now = time (NULL);
  struct tm *time_now = localtime (&now);
  time_now->tm_mon -= (nmonth+1);

  if (time_now->tm_mon < 0)
    {
      time_now->tm_mon += 12;
      time_now->tm_year -= 1;
    }

  switch (time_now->tm_mon)
    {
      case 0:   // Январь
      case 2:   // Март
      case 4:   // Май
      case 6:   // Июль
      case 7:   // Август
      case 9:   // Октябрь
      case 11:  // Декабрь
        time_now->tm_mday = 31;
        break;
      case 3:   // Апрель
      case 5:   // Июнь
      case 8:   // Сентябрь
      case 10:  // Ноябрь
        time_now->tm_mday = 30;
        break;
      case 1:   // Февраль
        if (time_now->tm_year % 4 == 0)
          time_now->tm_mday = 29;
        else
          time_now->tm_mday = 28;
        break;
    }

  char strbuf[15];
  strftime (strbuf, sizeof (strbuf), "%Y-%m-%d", time_now);
  basic_stringstream < char >sqlcmd;
  basic_stringstream < char >message;

  sqlcmd << "delete from squidcache where s_proxy_id=" << proxy_id;
  sqlcmd << " and s_date<='" << strbuf << "'";

  message.str("");
  message << "Delete from cache records earlier than " << strbuf;
  Logger::addLog(Logger::LK_CACHE, message.str());

  if (!query->sendQueryDirect (sqlcmd.str ()))
    {
      delete query;
      delete conn;
      return;
    }

  sqlcmd.str("");

  sqlcmd << "delete from cachesum where s_proxy_id=" << proxy_id;
  sqlcmd << " and s_date<='" << strbuf << "'";

  query->sendQueryDirect (sqlcmd.str ());

  delete query;
  delete conn;

  return;
}
