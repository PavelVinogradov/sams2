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

#include <time.h>
#include <fstream>
#include <sstream>

#include "config.h"

#include "dbconn.h"
#include "dbquery.h"
#include "dbexporter.h"
#include "samsconfig.h"
#include "datefilter.h"
#include "userfilter.h"
#include "tools.h"
#include "debug.h"

DBExporter::DBExporter ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
  _date_filter = NULL;
  _user_filter = NULL;
  _date_filter_owner = false;
}


DBExporter::~DBExporter ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
  if (_date_filter && _date_filter_owner)
    delete _date_filter;
}


void DBExporter::setUserFilter (UserFilter * filt)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << filt);
  _user_filter = filt;
}

void DBExporter::setDateFilter (DateFilter * filt)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << filt);

  if (_date_filter && _date_filter_owner)
    delete _date_filter;

  _date_filter = filt;
  _date_filter_owner = false;
}

void DBExporter::setDateFilter (const string & dateSpec)
{
  if (_date_filter && _date_filter_owner)
    delete _date_filter;

  _date_filter = new DateFilter (dateSpec);
  _date_filter_owner = true;
}

bool DBExporter::exportToFile (const string &fname)
{
  int err;
  int proxy_id = SamsConfig::getInt (defPROXYID, err);
  if (err != ERR_OK)
    {
      ERROR ("No proxyid defined. Check " << defPROXYID << " in config file.");
      return false;
    }

  DBConn *conn = NULL;
  DBQuery *query = NULL;

  conn = SamsConfig::newConnection ();
  if (!conn)
    {
      ERROR ("Unable to create connection.");
      return false;
    }

  if (!conn->connect ())
    {
      delete conn;
      return false;
    }

  conn->newQuery (query);
  if (!query)
    {
      ERROR("Unable to create query.");
      delete conn;
      return false;
    }

  ofstream _fout;
  _fout.open (fname.c_str (), ios::out);
  if (!_fout.is_open ())
    {
      ERROR ("Unable to open file " << fname);
      delete query;
      delete conn;
      return false;
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
          if (!strDomain.empty ())
            strUserFilter << "s_domain='" << strDomain << "' and ";
          strUserFilter << "s_user='" << strUser << "'";
          strUserFilter << ")";
        }
    }



  // Составляем sql команду
  basic_stringstream < char > sqlcmd;
  sqlcmd << "select s_date, s_time, s_domain, s_user, s_size, s_hit, s_ipaddr, s_period, s_method, s_url from squidcache";
  sqlcmd << " where s_proxy_id=" << proxy_id;

  if (!strDateFilter.str ().empty ())
    sqlcmd << " and (" << strDateFilter.str () << ")";

  if (!strUserFilter.str ().empty ())
    sqlcmd << " and (" << strUserFilter.str () << ")";

  sqlcmd << " order by s_date asc, s_time asc";


  char s_date[15];
  char s_time[15];
  char s_user[60];
  char s_domain[60];
  long s_size;
  long s_hit;
  char s_ipaddr[20];
  long s_period;
  char s_method[20];
  char s_url[1024];

  if (!query->bindCol (1, DBQuery::T_CHAR, s_date, sizeof (s_date)))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->bindCol (2, DBQuery::T_CHAR, s_time, sizeof (s_time)))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->bindCol (3, DBQuery::T_CHAR, s_domain, sizeof (s_domain)))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->bindCol (4, DBQuery::T_CHAR, s_user, sizeof (s_user)))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->bindCol (5, DBQuery::T_LONG, &s_size, 0))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->bindCol (6, DBQuery::T_LONG, &s_hit, 0))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->bindCol (7, DBQuery::T_CHAR, s_ipaddr, sizeof (s_ipaddr)))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->bindCol (8, DBQuery::T_LONG, &s_period, 0))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->bindCol (9, DBQuery::T_CHAR, s_method, sizeof (s_method)))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->bindCol (10, DBQuery::T_CHAR, s_url, sizeof (s_url)))
    {
      delete query;
      delete conn;
      return false;
    }
  if (!query->sendQueryDirect (sqlcmd.str ()) )
    {
      delete query;
      delete conn;
      return false;
    }

  struct tm date_time;
  time_t timestamp;
  char *rest;
  string str_date_time;

  while (query->fetch ())
    {
      str_date_time = s_date;
      str_date_time += " ";
      str_date_time += s_time;
      rest = strptime (str_date_time.c_str (), "%Y-%m-%d %H:%M:%S", &date_time);
      timestamp = mktime (&date_time);

      _fout << timestamp << ".000";
      _fout << " " << setw (6) << s_period;
      _fout << " " << s_ipaddr;
      _fout << " " << "-/-"; // HTTP_STATUS/HTTP_CODE
      _fout << " " << s_size;
      _fout << " " << s_method;
      _fout << " " << s_url;
      _fout << " ";
      if (s_domain[0] != 0)
          _fout << s_domain << "\\";
      _fout << s_user;
      _fout << " " << "-/-"; //DIRECT/destination.ip.address
      _fout << " " << "-/-"; //mime/code
      _fout << endl;
    }

  _fout.close ();

  delete query;
  delete conn;

  return true;
}
