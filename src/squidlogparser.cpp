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

#ifdef USE_PQ
#include "pgconn.h"
#include "pgquery.h"
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
  DBConn *conn = NULL;

  DBConn::DBEngine engine = SamsConfig::getEngine();

  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      conn = new ODBCConn();
      #else
      return;
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      conn = new MYSQLConn();
      #else
      return;
      #endif
    }
  else if (engine == DBConn::DB_PGSQL)
    {
      #ifdef USE_PQ
      conn = new PgConn();
      #else
      return;
      #endif
    }
  else
    return;

  if (!conn->connect ())
    {
      delete conn;
      return;
    }

  parseFile (conn, fname, from_begin);

  delete conn;
}

void SquidLogParser::parseFile (DBConn *conn, const string & fname, bool from_begin)
{
  DEBUG (DEBUG_PARSER, "[" << this << "->" << __FUNCTION__ << "] " << fname << ", " << from_begin);

  DBConn::DBEngine engine = SamsConfig::getEngine();

  DBQuery *query = NULL;
  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      query = new ODBCQuery((ODBCConn*)conn);
      #else
      return;
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      query = new MYSQLQuery((MYSQLConn*)conn);
      #else
      return;
      #endif
    }
  else if (engine == DBConn::DB_PGSQL)
    {
      #ifdef USE_PQ
      query = new PgQuery((PgConn*)conn);
      #else
      return;
      #endif
    }
  else
    return;

  char s_version[5];
  basic_stringstream < char >sql_cmd;
  sql_cmd << "select s_version from websettings";

  if (!query->bindCol (1, DBQuery::T_CHAR, s_version, sizeof (s_version)))
    {
      delete query;
      return;
    }
  if (!query->sendQueryDirect (sql_cmd.str()) )
    {
      delete query;
      return;
    }
  if (!query->fetch ())
    {
      return;
    }
  delete query;
  query = NULL;

  string str_ver = TrimSpaces(s_version);
  if (str_ver != VERSION)
    {
      ERROR ("Incompatible database version. Expected " << VERSION << ", but got " << str_ver);
      return;
    }
  else
    {
      DEBUG (DEBUG_PARSER, "[" << this << "->" << __FUNCTION__ << "] " << "Database version ok.");
    }

  DEBUG (DEBUG_PARSER, "[" << this << "->" << __FUNCTION__ << "] " << "Reading file " << fname);

  fstream in;
  in.open (fname.c_str (), ios_base::in);
  if (!in.is_open ())
    {
      ERROR ("Failed to open file " << fname);
      return;
    }

  in.seekg (0, ios::end);
  long fsize = in.tellg();
  in.seekg (0, ios::beg);

  long fpos = 0;

  if (!from_begin)
    fpos = Proxy::getEndValue();

  if (fpos > fsize)
    fpos = 0;

  DEBUG (DEBUG_PARSER, "[" << this << "->" << __FUNCTION__ << "] " << "file size " << fsize << ", use offset " << fpos);

  if (fpos == fsize)
    {
      INFO("No new values");
      in.close();
      return;
    }

  in.seekg (fpos, ios::beg);

  char s_date[15];
  char s_time[15];
  char s_user[50];
  char s_domain[50];
  long long s_size;
  long long s_hit;
  long long cachesum_size;
  long long cachesum_hit;
  char s_ipaddr[15];
  long s_period;
  char s_method[15];
  char s_url[1024];
  struct tm date_time;
  long s_enabled;
  long s_user_id;


  DBQuery *updUserQuery = NULL;
  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      updUserQuery = new ODBCQuery((ODBCConn*)conn);
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      updUserQuery = new MYSQLQuery((MYSQLConn*)conn);
      #endif
    }
  else if (engine == DBConn::DB_PGSQL)
    {
      #ifdef USE_PQ
      updUserQuery = new PgQuery((PgConn*)conn);
      #endif
    }
  basic_stringstream < char > user_update_cmd;
  user_update_cmd << "update squiduser set s_size=?, s_hit=?, s_enabled=? where s_user_id=?";
  if (!updUserQuery->prepareQuery (user_update_cmd.str ()))
    {
      delete updUserQuery;
      return;
    }
  if (!updUserQuery->bindParam (1, DBQuery::T_LONGLONG, &s_size, 0))
    {
      delete updUserQuery;
      return;
    }
  if (!updUserQuery->bindParam (2, DBQuery::T_LONGLONG, &s_hit, 0))
    {
      delete updUserQuery;
      return;
    }
  if (!updUserQuery->bindParam (3, DBQuery::T_LONG, &s_enabled, 0))
    {
      delete updUserQuery;
      return;
    }
  if (!updUserQuery->bindParam (4, DBQuery::T_LONG, &s_user_id, 0))
    {
      delete updUserQuery;
      return;
    }


  DBQuery *updCacheQuery = NULL;
  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      updCacheQuery = new ODBCQuery((ODBCConn*)conn);
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      updCacheQuery = new MYSQLQuery((MYSQLConn*)conn);
      #endif
    }
  else if (engine == DBConn::DB_PGSQL)
    {
      #ifdef USE_PQ
      updCacheQuery = new PgQuery((PgConn*)conn);
      #endif
    }
  basic_stringstream < char > cache_update_cmd;
  cache_update_cmd << "insert into squidcache (s_proxy_id, s_date, s_time, s_user, s_domain, s_size, s_hit, s_ipaddr, s_period, s_method, s_url)";
  cache_update_cmd << " VALUES ("<<_proxyid<<", ?,?,?,?,?,?,?,?,?,?)";
  if (!updCacheQuery->prepareQuery (cache_update_cmd.str ()))
    {
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCacheQuery->bindParam (1, DBQuery::T_CHAR, s_date, sizeof (s_date)))
    {
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCacheQuery->bindParam (2, DBQuery::T_CHAR, s_time, sizeof (s_time)))
    {
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCacheQuery->bindParam (3, DBQuery::T_CHAR, s_user, sizeof (s_user)))
    {
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCacheQuery->bindParam (4, DBQuery::T_CHAR, s_domain, sizeof (s_domain)))
    {
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCacheQuery->bindParam (5, DBQuery::T_LONGLONG, &s_size, 0))
    {
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCacheQuery->bindParam (6, DBQuery::T_LONGLONG, &s_hit, 0))
    {
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCacheQuery->bindParam (7, DBQuery::T_CHAR, s_ipaddr, sizeof (s_ipaddr)))
    {
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCacheQuery->bindParam (8, DBQuery::T_LONG, &s_period, 0))
    {
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCacheQuery->bindParam (9, DBQuery::T_CHAR, s_method, sizeof (s_method)))
    {
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCacheQuery->bindParam (10, DBQuery::T_CHAR, s_url, sizeof (s_url)))
    {
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }


  DBQuery *updProxyQuery = NULL;
  if (!from_begin)
  {
    if (engine == DBConn::DB_UODBC)
      {
        #ifdef USE_UNIXODBC
        updProxyQuery = new ODBCQuery((ODBCConn*)conn);
        #endif
      }
    else if (engine == DBConn::DB_MYSQL)
      {
        #ifdef USE_MYSQL
        updProxyQuery = new MYSQLQuery((MYSQLConn*)conn);
        #endif
      }
    else if (engine == DBConn::DB_PGSQL)
      {
        #ifdef USE_PQ
        updProxyQuery = new PgQuery((PgConn*)conn);
        #endif
      }
    basic_stringstream < char > proxy_update_cmd;
    proxy_update_cmd << "update proxy set s_endvalue=? where s_proxy_id=" << _proxyid;
    if (!updProxyQuery->prepareQuery (proxy_update_cmd.str ()))
      {
        delete updProxyQuery;
        delete updCacheQuery;
        delete updUserQuery;
        return;
      }
    if (!updProxyQuery->bindParam (1, DBQuery::T_LONG, &fpos, 0))
      {
        delete updProxyQuery;
        delete updCacheQuery;
        delete updUserQuery;
        return;
      }
  }

  DBQuery *selCachesumQuery = NULL;
  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      selCachesumQuery = new ODBCQuery((ODBCConn*)conn);
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      selCachesumQuery = new MYSQLQuery((MYSQLConn*)conn);
      #endif
    }
  else if (engine == DBConn::DB_PGSQL)
    {
      #ifdef USE_PQ
      selCachesumQuery = new PgQuery((PgConn*)conn);
      #endif
    }
  basic_stringstream < char > cachesum_select_cmd;
/*
  if (!selCachesumQuery->prepareQuery (cachesum_select_cmd.str ()))
    {
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
*/
  if (!selCachesumQuery->bindCol (1, DBQuery::T_LONGLONG, &cachesum_size, 0))
    {
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!selCachesumQuery->bindCol (2, DBQuery::T_LONGLONG, &cachesum_hit, 0))
    {
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
/*
  if (!selCachesumQuery->bindParam (1, DBQuery::T_CHAR, s_date, sizeof (s_date)))
    {
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!selCachesumQuery->bindParam (2, DBQuery::T_CHAR, s_domain, sizeof (s_domain)))
    {
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!selCachesumQuery->bindParam (3, DBQuery::T_CHAR, s_user, sizeof (s_user)))
    {
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
*/


  DBQuery *insCachesumQuery = NULL;
  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      insCachesumQuery = new ODBCQuery((ODBCConn*)conn);
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      insCachesumQuery = new MYSQLQuery((MYSQLConn*)conn);
      #endif
    }
  else if (engine == DBConn::DB_PGSQL)
    {
      #ifdef USE_PQ
      insCachesumQuery = new PgQuery((PgConn*)conn);
      #endif
    }
  basic_stringstream < char > cachesum_insert_cmd;
  cachesum_insert_cmd << "insert into cachesum (s_proxy_id, s_date, s_domain, s_user, s_size, s_hit)";
  cachesum_insert_cmd << " values (" << _proxyid << ", ?, ?, ?, ?, ?)";
  if (!insCachesumQuery->prepareQuery (cachesum_insert_cmd.str ()))
    {
      delete insCachesumQuery;
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!insCachesumQuery->bindParam (1, DBQuery::T_CHAR, s_date, sizeof (s_date)))
    {
      delete insCachesumQuery;
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!insCachesumQuery->bindParam (2, DBQuery::T_CHAR, s_domain, sizeof (s_domain)))
    {
      delete insCachesumQuery;
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!insCachesumQuery->bindParam (3, DBQuery::T_CHAR, s_user, sizeof (s_user)))
    {
      delete insCachesumQuery;
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!insCachesumQuery->bindParam (4, DBQuery::T_LONGLONG, &cachesum_size, 0))
    {
      delete insCachesumQuery;
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!insCachesumQuery->bindParam (5, DBQuery::T_LONGLONG, &cachesum_hit, 0))
    {
      delete insCachesumQuery;
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }


  DBQuery *updCachesumQuery = NULL;
  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      updCachesumQuery = new ODBCQuery((ODBCConn*)conn);
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      updCachesumQuery = new MYSQLQuery((MYSQLConn*)conn);
      #endif
    }
  else if (engine == DBConn::DB_PGSQL)
    {
      #ifdef USE_PQ
      updCachesumQuery = new PgQuery((PgConn*)conn);
      #endif
    }
  basic_stringstream < char > cachesum_update_cmd;
  cachesum_update_cmd << "update cachesum set s_size=?, s_hit=? where s_date=? and s_domain=? and s_user=? and s_proxy_id=" << _proxyid;
  if (!updCachesumQuery->prepareQuery (cachesum_update_cmd.str ()))
    {
      delete updCachesumQuery;
      delete insCachesumQuery;
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCachesumQuery->bindParam (1, DBQuery::T_LONGLONG, &cachesum_size, 0))
    {
      delete updCachesumQuery;
      delete insCachesumQuery;
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCachesumQuery->bindParam (2, DBQuery::T_LONGLONG, &cachesum_hit, 0))
    {
      delete updCachesumQuery;
      delete insCachesumQuery;
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCachesumQuery->bindParam (3, DBQuery::T_CHAR, s_date, sizeof (s_date)))
    {
      delete updCachesumQuery;
      delete insCachesumQuery;
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCachesumQuery->bindParam (4, DBQuery::T_CHAR, s_domain, sizeof (s_domain)))
    {
      delete updCachesumQuery;
      delete insCachesumQuery;
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }
  if (!updCachesumQuery->bindParam (5, DBQuery::T_CHAR, s_user, sizeof (s_user)))
    {
      delete updCachesumQuery;
      delete insCachesumQuery;
      delete selCachesumQuery;
      delete updProxyQuery;
      delete updCacheQuery;
      delete updUserQuery;
      return;
    }


  Proxy::TrafficType trafType = Proxy::getTrafficType ();
  long kbsize = Proxy::getKbSize ();
  string line;
  SquidLogLine sll;
  SAMSUser *usr;
  long long used_size;
  long long allowed_limit;
  while (in.good ())
    {
      getline (in, line);

      // Игнорируем пустые строки
      if (line.empty ())
        continue;

      // Игнорируем строки с неверным форматом
      if (sll.setLine (line) != true)
        continue;

      // Ищем пользователя, а если его нет и настроен autouser, то создаем
      usr = Proxy::findUser (sll.getIP (), sll.getIdent ());

      // Если пользователь по каким-то причинам не найден, то переходим к следующей строке
      if (usr == NULL)
        continue;

      // Применяем различные фильтры
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
      if (LocalNetworks::isLocalUrl (sll.getUrl ()))
        {
          DEBUG (DEBUG_URL, "Consider url is local");
          continue;
        }

      memset (s_user, 0, sizeof(s_user));
      memset (s_domain, 0, sizeof(s_domain));
      memset (s_ipaddr, 0, sizeof(s_ipaddr));
      memset (s_method, 0, sizeof(s_method));
      memset (s_url, 0, sizeof(s_url));

      // Анализируем входную строку
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
      sprintf (s_method, "%s", sll.getMethod ().c_str ());

      // Обновляем squidcache
      if (!updCacheQuery->sendQuery ())
        continue;


      // Обновляем cachesum
      ///< @todo Для поиска значений в cachesum вместо прямого запроса выполнять подготовленный
      cachesum_select_cmd.str("");
      cachesum_select_cmd << "select s_size, s_hit from cachesum where s_proxy_id=" << _proxyid;
      cachesum_select_cmd << " and s_date='" << s_date <<"' and s_domain='" << s_domain << "' and s_user='" << s_user << "'";
      if (!selCachesumQuery->sendQueryDirect (cachesum_select_cmd.str ()))
        continue;

      if (!selCachesumQuery->fetch ()) // такой записи еще нет, нужно добавлять
        {
          cachesum_size = s_size;
          cachesum_hit = s_hit;
          DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Set cachesum for "<<s_user<<": cachesum_size=" << cachesum_size << ", cachesum_hit=" << cachesum_hit);
          insCachesumQuery->sendQuery ();
        }
      else //такая запись существует, нужно обновлять
        {
          DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Got cachesum for "<<s_user<<": cachesum_size=" << cachesum_size << ", cachesum_hit=" << cachesum_hit);
          cachesum_size += s_size;
          cachesum_hit += s_hit;
          DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Update cachesum for "<<s_user<<": cachesum_size=" << cachesum_size << ", cachesum_hit=" << cachesum_hit);
          updCachesumQuery->sendQuery ();
        }


      // Обновляем счетчики пользователя
      allowed_limit = usr->getQuote();
      allowed_limit *= kbsize * kbsize;
      s_size = usr->getSize();
      s_hit = usr->getHit();
      s_user_id = usr->getId();
      switch (trafType)
        {
          case Proxy::TRAF_REAL:
            used_size = s_size - s_hit;
            break;
          case Proxy::TRAF_FULL:
            used_size = s_size;
            break;
          default:
            used_size = 0;
            break;
        }
      if ( (allowed_limit > 0) && (used_size > allowed_limit) && (usr->getEnabled() == SAMSUser::STAT_ACTIVE) )
        {
          usr->setEnabled( SAMSUser::STAT_INACTIVE );
          basic_stringstream < char >mess;
          mess << "User " << *usr << " deactivated.";
          INFO (mess.str ());
          Logger::addLog(Logger::LK_USER, mess.str());
        }
      s_enabled = (long)usr->getEnabled();
      updUserQuery->sendQuery ();


      // Обновляем смещение в файле access.log
      if (!from_begin)
        {
          fpos = in.tellg ();
          updProxyQuery->sendQuery ();
        }
    }
  in.close ();


  delete selCachesumQuery;
  delete insCachesumQuery;
  delete updCachesumQuery;
  delete updProxyQuery;
  delete updCacheQuery;
  delete updUserQuery;

  return;
}
