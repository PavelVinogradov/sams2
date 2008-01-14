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
#include <fstream>
#include <sstream>

#include "config.h"

#ifdef USE_UNIXODBC
#include "odbcconn.h"
#include "odbcquery.h"
#endif

#ifdef USE_MYSQL
#include "mysqlconn.h"
#include "mysqlquery.h"
#endif

#include "squidlogparser.h"
#include "squidlogline.h"
#include "localnetworks.h"
#include "samsusers.h"
#include "samsuser.h"
#include "proxy.h"
#include "samsconfig.h"
#include "datefilter.h"
#include "userfilter.h"
#include "debug.h"
#include "global.h"
#include "tools.h"

SquidLogParser::SquidLogParser (int proxyid)
{
  _proxyid = proxyid;
  _date_filter = NULL;
  _user_filter = NULL;
}


SquidLogParser::~SquidLogParser ()
{
}

void SquidLogParser::setUserFilter (UserFilter * filt)
{
  DEBUG (DEBUG_PARSER, "[" << this << "->" << __FUNCTION__ << "] " << filt);
  _user_filter = filt;
}

void SquidLogParser::setDateFilter (DateFilter * filt)
{
  DEBUG (DEBUG_PARSER, "[" << this << "->" << __FUNCTION__ << "] " << filt);
  _date_filter = filt;
}

void SquidLogParser::parseFile (const string & fname, bool from_begin)
{
  DEBUG (DEBUG_PARSER, "[" << this << "->" << __FUNCTION__ << "] " << fname);

  DBConn *conn = NULL;
  DBQuery *query = NULL;

  DBConn::DBEngine engine = config->getEngine();

  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      conn = new ODBCConn();
      query = new ODBCQuery((ODBCConn*)conn);
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      conn = new MYSQLConn();
      query = new MYSQLQuery((MYSQLConn*)conn);
      #endif
    }
  else
    return;

  if (!conn->connect ())
    {
      delete query;
      delete conn;
      return;
    }

  char s_version[5];
  basic_stringstream < char >sql_cmd;
  sql_cmd << "select s_version from websettings";

  if (!query->bindCol (1, DBQuery::T_CHAR, s_version, sizeof (s_version)))
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->sendQueryDirect (sql_cmd.str()) )
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->fetch ())
    {
      delete query;
      delete conn;
      return;
    }

  if (strcmp (s_version, VERSION) != 0)
    {
      ERROR ("Incompatible database version. Expected " << VERSION << ", but got " << s_version);
      delete query;
      delete conn;
      return;
    }
  else
    {
      DEBUG (DEBUG_PARSER, "[" << this << "->" << __FUNCTION__ << "] " << "Database version ok.");
    }

  Proxy proxy (_proxyid, conn);

  LocalNetworks lnets;
  lnets.load (conn);

  INFO ("Reading file " << fname);

  char s_date[15];
  char s_time[15];
  char s_user[50];
  char s_domain[50];
  long s_size;
  long s_hit;
  char s_ipaddr[15];
  long s_period;
  char s_method[15];
  char s_url[1024];
  struct tm date_time;

  sql_cmd.str("");

  sql_cmd << "insert into squidcache (s_proxy_id, s_date, s_time, s_user, s_domain, s_size, s_hit, s_ipaddr, s_period, s_method, s_url)";
  sql_cmd << " VALUES ("<<_proxyid<<", ?,?,?,?,?,?,?,?,?,?)";

  if (!query->prepareQuery (sql_cmd.str ()))
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->bindParam (1, DBQuery::T_CHAR, s_date, sizeof (s_date)))
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->bindParam (2, DBQuery::T_CHAR, s_time, sizeof (s_time)))
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->bindParam (3, DBQuery::T_CHAR, s_user, sizeof (s_user)))
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->bindParam (4, DBQuery::T_CHAR, s_domain, sizeof (s_domain)))
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->bindParam (5, DBQuery::T_LONG, &s_size, 0))
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->bindParam (6, DBQuery::T_LONG, &s_hit, 0))
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->bindParam (7, DBQuery::T_CHAR, s_ipaddr, sizeof (s_ipaddr)))
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->bindParam (8, DBQuery::T_LONG, &s_period, 0))
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->bindParam (9, DBQuery::T_CHAR, s_method, sizeof (s_method)))
    {
      delete query;
      delete conn;
      return;
    }
  if (!query->bindParam (10, DBQuery::T_CHAR, s_url, sizeof (s_url)))
    {
      delete query;
      delete conn;
      return;
    }

  fstream in;
  in.open (fname.c_str (), ios_base::in);
  if (!in.is_open ())
    {
      ERROR ("Failed to open file " << fname);
      delete query;
      delete conn;
      return;
    }

  in.seekg (0, ios::end);
  long fsize = in.tellg();
  in.seekg (0, ios::beg);

  long fpos = 0;

  if (!from_begin)
    fpos = proxy.getEndValue();

  if (fpos > fsize)
    fpos = 0;

  if (fpos == fsize)
    {
      INFO("No new values");
      delete query;
      delete conn;
      return;
    }



  string line;
  SquidLogLine sll;
  SAMSUser *usr;
  while (in.good ())
    {
      getline (in, line);
      if (line.empty ())
        continue;
      if (sll.setLine (line) != true)
        continue;

      usr = proxy.findUser (sll.getIP (), sll.getIdent ());

      if (usr == NULL)
        continue;

      date_time = sll.getTime ();
      strftime (s_date, sizeof (s_date), "%Y-%m-%d", &date_time);
      strftime (s_time, sizeof (s_time), "%H:%M:%S", &date_time);

      if ((_date_filter != NULL) && (!_date_filter->match (date_time)))
        {
          DEBUG (DEBUG_USER, "Filtered out: " << s_date << " " << s_time << " outside date interval");
          continue;
        }

      if ((_user_filter != NULL) && (!_user_filter->match (usr)))
        {
          DEBUG (DEBUG_USER, "Filtered out: " << *usr << " not in the filter");
          continue;
        }

      if (lnets.isLocalUrl (sll.getUrl ()))
        {
          DEBUG (DEBUG_URL, "Consider url is local");
          continue;
        }

      memset (s_user, 0, sizeof(s_user));
      memset (s_domain, 0, sizeof(s_domain));
      memset (s_ipaddr, 0, sizeof(s_ipaddr));
      memset (s_method, 0, sizeof(s_method));
      memset (s_url, 0, sizeof(s_url));


      s_hit = 0;
      switch (sll.getCacheResult ())
        {
        case SquidLogLine::CR_UNKNOWN:
          ERROR ("Unknown cache result");
          break;
        case SquidLogLine::TCP_DENIED:
        case SquidLogLine::UDP_DENIED:
          break;
        case SquidLogLine::TCP_HIT:
        case SquidLogLine::TCP_MEM_HIT:
        case SquidLogLine::TCP_REFRESH_HIT:
        case SquidLogLine::TCP_REF_FAIL_HIT:
        case SquidLogLine::TCP_IMS_HIT:
        case SquidLogLine::UDP_HIT:
          usr->addHit (sll.getSize ());
          s_hit = sll.getSize ();
        case SquidLogLine::TCP_NEGATIVE_HIT:
        case SquidLogLine::TCP_MISS:
        case SquidLogLine::TCP_REFRESH_MISS:
        case SquidLogLine::TCP_CLIENT_REFRESH:
        case SquidLogLine::TCP_CLIENT_REFRESH_MISS:
        case SquidLogLine::TCP_IMS_MISS:
        case SquidLogLine::TCP_SWAPFAIL:
        case SquidLogLine::UDP_HIT_OBJ:
        case SquidLogLine::UDP_MISS:
        case SquidLogLine::UDP_INVALID:
        case SquidLogLine::UDP_RELOADING:
        case SquidLogLine::ERR_CLIENT_ABORT:
        case SquidLogLine::ERR_NO_CLIENTS:
        case SquidLogLine::ERR_READ_ERROR:
        case SquidLogLine::ERR_CONNECT_FAIL:
          usr->addSize (sll.getSize ());
          s_size = sll.getSize ();
          break;
        }

      sprintf (s_user, "%s", usr->getNick ().c_str ());
      sprintf (s_domain, "%s", usr->getDomain ().c_str ());

      sprintf (s_ipaddr, "%s", sll.getIP ().asString ().c_str ());

      s_period = sll.getBusytime ();

      sprintf (s_url, "%s", sll.getUrl ().c_str ());

      sprintf (s_method, "method");

      if (!query->sendQuery ())
        continue;

      if (!from_begin)
        proxy.setEndValue (in.tellg());
    }
  in.close ();

  proxy.commitChanges ();

  sql_cmd.str("");
  sql_cmd << "delete from cachesum where s_proxy_id=" << _proxyid;

  if (!query->sendQueryDirect (sql_cmd.str ()))
    {
      delete query;
      delete conn;
      return;
    }

  sql_cmd.str("");
  sql_cmd << "insert into cachesum select " << _proxyid;
  sql_cmd << ", s_date, s_user, s_domain, sum(s_size), sum(s_hit)";
  sql_cmd << " from squidcache where s_proxy_id=" << _proxyid;
  sql_cmd << " group by s_date, s_domain, s_user";

  query->sendQueryDirect (sql_cmd.str ());
  delete query;
  delete conn;
  return;
}
