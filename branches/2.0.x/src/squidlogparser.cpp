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

void SquidLogParser::parseFile (const string & fname)
{
  DEBUG (DEBUG_PARSER, "[" << this << "->" << __FUNCTION__ << "] " << fname);

  #ifdef USE_UNIXODBC
  ODBCConn *connODBC = NULL;
  ODBCQuery *queryODBC = NULL;
  #endif

  #ifdef USE_MYSQL
  MYSQLConn *connMYSQL = NULL;
  MYSQLQuery *queryMYSQL = NULL;
  #endif

  DBConn::DBEngine engine = config->getEngine();

  char s_version[5];

  //После if'ов переменная s_version должна содержать значение, взятое из БД
  basic_stringstream < char >sql_cmd;
  sql_cmd << "select s_version from websettings";
  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      connODBC = new ODBCConn();
      if (!connODBC->connect ())
        {
          delete connODBC;
          return;
        }
      queryODBC = new ODBCQuery(connODBC);
      if (!queryODBC->bindCol (1, SQL_C_CHAR, s_version, sizeof (s_version)))
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      if (!queryODBC->sendQueryDirect (sql_cmd.str()) )
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      if (!queryODBC->fetch ())
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      connMYSQL = new MYSQLConn();
      MYSQL_RES *res = NULL;
      MYSQL_ROW row = NULL;
      if (!connMYSQL->connect ())
        {
          delete connMYSQL;
          return;
        }
      queryMYSQL = new MYSQLQuery(connMYSQL);
      if (!queryMYSQL->bindCol (1, MYSQL_TYPE_STRING, s_version, sizeof (s_version)))
        {
          delete connMYSQL;
          delete queryMYSQL;
          return;
        }
      if (!queryMYSQL->sendQueryDirect (sql_cmd.str()) )
        {
          delete connMYSQL;
          delete queryMYSQL;
          return;
        }
      if (!queryMYSQL->fetch ())
        {
          delete connMYSQL;
          delete queryMYSQL;
          return;
        }
      #endif
    }
  else
    {
      return;
    }


  if (strcmp (s_version, VERSION) != 0)
    {
      ERROR ("Incompatible database version. Expected " << VERSION << ", but got " << s_version);
      return;
    }
  else
    {
      DEBUG (DEBUG_PARSER, "[" << this << "->" << __FUNCTION__ << "] " << "Database version ok.");
    }

  Proxy *proxy = NULL;

  if (engine == DBConn::DB_UODBC)
    proxy = new Proxy (_proxyid, connODBC);
  else if (engine == DBConn::DB_MYSQL)
    proxy = new Proxy (_proxyid, connMYSQL);
  else
    {
      return;
    }


  LocalNetworks lnets;
  if (engine == DBConn::DB_UODBC)
    lnets.load (connODBC);
  else if (engine == DBConn::DB_MYSQL)
    lnets.load (connMYSQL);
  else
    {
      return;
    }


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
  sql_cmd << "insert into squidcache set";
  sql_cmd << " s_proxy_id=" << _proxyid;
  sql_cmd << ",s_date=?";
  sql_cmd << ",s_time=?";
  sql_cmd << ",s_user=?";
  sql_cmd << ",s_domain=?";
  sql_cmd << ",s_size=?";
  sql_cmd << ",s_hit=?";
  sql_cmd << ",s_ipaddr=?";
  sql_cmd << ",s_period=?";
  sql_cmd << ",s_method=?";
  sql_cmd << ",s_url=?";

  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      if (!queryODBC->prepareQuery (sql_cmd.str ()))
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (1, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_DATE, s_date, sizeof (s_date)))
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (2, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_DATE, s_time, sizeof (s_time)))
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (3, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_VARCHAR, s_user, sizeof (s_user)))
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (4, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_VARCHAR, s_domain, sizeof (s_domain)))
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (5, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, &s_size, 0))
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (6, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, &s_hit, 0))
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (7, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_VARCHAR, s_ipaddr, sizeof (s_ipaddr)))
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (8, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, &s_period, 0))
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (9, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_VARCHAR, s_method, sizeof (s_method)))
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      if (!queryODBC->bindParam (10, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_VARCHAR, s_url, sizeof (s_url)))
        {
          delete connODBC;
          delete queryODBC;
          return;
        }
      #endif
    }



  fstream in;
  in.open (fname.c_str (), ios_base::in);
  if (!in.is_open ())
    {
      ERROR ("Failed to open file " << fname);
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

      usr = proxy->findUser (sll.getIP (), sll.getIdent ());

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

      if (engine == DBConn::DB_UODBC)
        {
          #ifdef USE_UNIXODBC
          if (!queryODBC->sendQuery ())
              continue;
          #endif
        }
    }
  in.close ();

  proxy->commitChanges ();

  sql_cmd.str("");
  sql_cmd << "delete from cachesum where s_proxy_id=" << _proxyid;


  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      if (!queryODBC->sendQueryDirect (sql_cmd.str ()))
        {
          delete connODBC;
          return;
        }
      #endif
    }

  sql_cmd.str("");
  sql_cmd << "insert into cachesum select " << _proxyid;
  sql_cmd << ", s_date, s_user, s_domain, sum(s_size), sum(s_hit)";
  sql_cmd << " from squidcache where s_proxy_id=" << _proxyid;
  sql_cmd << " group by s_date, s_domain, s_user";


  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      queryODBC->sendQueryDirect (sql_cmd.str ());
      delete connODBC;
      return;
      #endif
    }


  delete proxy;

}
