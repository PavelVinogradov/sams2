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
#include "configmake.h"
#include <string.h>
#include <stdlib.h>

#include "dbconn.h"
#include "dbquery.h"
#include "squidlogparser.h"
#include "squidlogline.h"
#include "localnetworks.h"
#include "samsuser.h"
#include "template.h"
#include "templatelist.h"
#include "proxy.h"
#include "samsconfig.h"
#include "datefilter.h"
#include "userfilter.h"
#include "debug.h"
#include "tools.h"

SquidLogParser::SquidLogParser (int proxyid)
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  _proxyid = proxyid;
  _date_filter = NULL;
  _user_filter = NULL;
}


SquidLogParser::~SquidLogParser ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
}

void SquidLogParser::setUserFilter (UserFilter * filt)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << filt << ")]");
  _user_filter = filt;
}

void SquidLogParser::setDateFilter (DateFilter * filt)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << filt << ")]");
  _date_filter = filt;
}

void SquidLogParser::parseFile (const string & fname, bool from_begin)
{
  //DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << fname << ", " << from_begin << ")]");

  DBConn *conn = NULL;

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

  parseFile (conn, fname, from_begin);

  delete conn;
}

void SquidLogParser::parseFile (DBConn *conn, const string & fname, bool from_begin)
{
  DEBUG (DEBUG_PARSER, "[" << this << "->" << __FUNCTION__ << "] " << fname << ", " << from_begin);

  int err;

  string str_db_ver = SamsConfig::getString (defDBVERSION, err);
  if (err != ERR_OK)
    {
      ERROR ("Unable to get database version.");
      return;
    }

  string str_pkg_ver = VERSION;

  if (str_db_ver.compare (2, 3, "9.9") == 0)
    {
      DEBUG (DEBUG3, "[" << this << "->" << __FUNCTION__ << "] " << "Internal database version.");
    }
  else if (str_db_ver.compare (0, 3, str_pkg_ver, 0, 3) != 0)
    {
      DEBUG (DEBUG3, "[" << this << "->" << __FUNCTION__ << "] " << "Database version accepted.");
    }
  else if (str_db_ver.compare (0, 5, str_pkg_ver, 0, 5) != 0)
    {
      ERROR ("Incompatible database version. Expected " << str_pkg_ver.substr (0, 5) << ", but got " << str_db_ver.substr (0, 5));
      return;
    }
  else
    {
      DEBUG (DEBUG3, "[" << this << "->" << __FUNCTION__ << "] " << "Database version ok.");
    }

  DEBUG (DEBUG3, "[" << this << "->" << __FUNCTION__ << "] " << "Reading file " << fname);

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
    {
      fpos = 0;
      DEBUG (DEBUG3, "[" << this << "->" << __FUNCTION__ << "] " << "Previous position bigger then file size. Process from offset 0");
    }

  DEBUG (DEBUG3, "[" << this << "->" << __FUNCTION__ << "] " << "file size " << fsize << ", use offset " << fpos);

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
  char s_ipaddr[16];
  long s_period;
  char s_method[15];
  char s_url[1024];
  struct tm date_time;
  long s_enabled;
  long s_user_id;


  DBQuery *updUserQuery = NULL;
  conn->newQuery (updUserQuery);
  if (!updUserQuery)
    {
      ERROR("Unable to create query.");
      return;
    }
  basic_stringstream < char > user_update_cmd;
  user_update_cmd << "update squiduser set s_size=?, s_hit=?, s_enabled=? where s_user_id=?";
  if (!updUserQuery->prepareQuery (user_update_cmd.str ()))
    {
      return;
    }
  if (!updUserQuery->bindParam (1, DBQuery::T_LONGLONG, &s_size, 0))
    {
      return;
    }
  if (!updUserQuery->bindParam (2, DBQuery::T_LONGLONG, &s_hit, 0))
    {
      return;
    }
  if (!updUserQuery->bindParam (3, DBQuery::T_LONG, &s_enabled, 0))
    {
      return;
    }
  if (!updUserQuery->bindParam (4, DBQuery::T_LONG, &s_user_id, 0))
    {
      return;
    }


  DBQuery *updCacheQuery = NULL;
  conn->newQuery (updCacheQuery);
  if (!updCacheQuery)
    {
      ERROR("Unable to create query.");
      return;
    }
  basic_stringstream < char > cache_update_cmd;
  cache_update_cmd << "insert into squidcache (s_proxy_id, s_date, s_time, s_user, s_domain, s_size, s_hit, s_ipaddr, s_period, s_method, s_url)";
  cache_update_cmd << " VALUES ("<<_proxyid<<", ?,?,?,?,?,?,?,?,?,?)";
  if (!updCacheQuery->prepareQuery (cache_update_cmd.str ()))
    {
      return;
    }
  if (!updCacheQuery->bindParam (1, DBQuery::T_CHAR, s_date, sizeof (s_date)))
    {
      return;
    }
  if (!updCacheQuery->bindParam (2, DBQuery::T_CHAR, s_time, sizeof (s_time)))
    {
      return;
    }
  if (!updCacheQuery->bindParam (3, DBQuery::T_CHAR, s_user, sizeof (s_user)))
    {
      return;
    }
  if (!updCacheQuery->bindParam (4, DBQuery::T_CHAR, s_domain, sizeof (s_domain)))
    {
      return;
    }
  if (!updCacheQuery->bindParam (5, DBQuery::T_LONGLONG, &s_size, 0))
    {
      return;
    }
  if (!updCacheQuery->bindParam (6, DBQuery::T_LONGLONG, &s_hit, 0))
    {
      return;
    }
  if (!updCacheQuery->bindParam (7, DBQuery::T_CHAR, s_ipaddr, sizeof (s_ipaddr)))
    {
      return;
    }
  if (!updCacheQuery->bindParam (8, DBQuery::T_LONG, &s_period, 0))
    {
      return;
    }
  if (!updCacheQuery->bindParam (9, DBQuery::T_CHAR, s_method, sizeof (s_method)))
    {
      return;
    }
  if (!updCacheQuery->bindParam (10, DBQuery::T_CHAR, s_url, sizeof (s_url)))
    {
      return;
    }


  DBQuery *updProxyQuery = NULL;
  conn->newQuery (updProxyQuery);
  if (!from_begin)
  {
    if (!updProxyQuery)
      {
        ERROR("Unable to create query.");
        return;
      }
    basic_stringstream < char > proxy_update_cmd;
    proxy_update_cmd << "update proxy set s_endvalue=? where s_proxy_id=" << _proxyid;
    if (!updProxyQuery->prepareQuery (proxy_update_cmd.str ()))
      {
        return;
      }
    if (!updProxyQuery->bindParam (1, DBQuery::T_LONG, &fpos, 0))
      {
        return;
      }
  }

  DBQuery *selCachesumQuery = NULL;
  conn->newQuery (selCachesumQuery);
  if (!selCachesumQuery)
    {
      return;
    }
  basic_stringstream < char > cachesum_select_cmd;

  if (!selCachesumQuery->bindCol (1, DBQuery::T_LONGLONG, &cachesum_size, 0))
    {
      return;
    }
  if (!selCachesumQuery->bindCol (2, DBQuery::T_LONGLONG, &cachesum_hit, 0))
    {
      return;
    }

  DBQuery *insCachesumQuery = NULL;
  conn->newQuery (insCachesumQuery);
  if (!insCachesumQuery)
    {
      return;
    }
  basic_stringstream < char > cachesum_insert_cmd;
  cachesum_insert_cmd << "insert into cachesum (s_proxy_id, s_date, s_domain, s_user, s_size, s_hit)";
  cachesum_insert_cmd << " values (" << _proxyid << ", ?, ?, ?, ?, ?)";
  if (!insCachesumQuery->prepareQuery (cachesum_insert_cmd.str ()))
    {
      return;
    }
  if (!insCachesumQuery->bindParam (1, DBQuery::T_CHAR, s_date, sizeof (s_date)))
    {
      return;
    }
  if (!insCachesumQuery->bindParam (2, DBQuery::T_CHAR, s_domain, sizeof (s_domain)))
    {
      return;
    }
  if (!insCachesumQuery->bindParam (3, DBQuery::T_CHAR, s_user, sizeof (s_user)))
    {
      return;
    }
  if (!insCachesumQuery->bindParam (4, DBQuery::T_LONGLONG, &cachesum_size, 0))
    {
      return;
    }
  if (!insCachesumQuery->bindParam (5, DBQuery::T_LONGLONG, &cachesum_hit, 0))
    {
      return;
    }


  DBQuery *updCachesumQuery = NULL;
  conn->newQuery (updCachesumQuery);
  if (!updCachesumQuery)
    {
      ERROR("Unable to create query.");
      return;
    }
  basic_stringstream < char > cachesum_update_cmd;
  cachesum_update_cmd << "update cachesum set s_size=?, s_hit=? where s_date=? and s_domain=? and s_user=? and s_proxy_id=" << _proxyid;
  if (!updCachesumQuery->prepareQuery (cachesum_update_cmd.str ()))
    {
      return;
    }
  if (!updCachesumQuery->bindParam (1, DBQuery::T_LONGLONG, &cachesum_size, 0))
    {
      return;
    }
  if (!updCachesumQuery->bindParam (2, DBQuery::T_LONGLONG, &cachesum_hit, 0))
    {
      return;
    }
  if (!updCachesumQuery->bindParam (3, DBQuery::T_CHAR, s_date, sizeof (s_date)))
    {
      return;
    }
  if (!updCachesumQuery->bindParam (4, DBQuery::T_CHAR, s_domain, sizeof (s_domain)))
    {
      return;
    }
  if (!updCachesumQuery->bindParam (5, DBQuery::T_CHAR, s_user, sizeof (s_user)))
    {
      return;
    }


  Proxy::TrafficType trafType = Proxy::getTrafficType ();
  long kbsize = Proxy::getKbSize ();
  string line;
  SquidLogLine sll;
  SAMSUser *usr;
  long long used_size;
  long long allowed_limit;
  bool need_reconfig = false;
  while (in.good ())
    {
      getline (in, line);

      // Игнорируем пустые строки
      if (line.empty ())
        continue;

      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Incoming line: " << line);

      if (!from_begin) // Запомним сразу что строка уже прочитана
        {
          fpos = in.tellg ();
          DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Store file position: " << fpos);
          if (!updProxyQuery->sendQuery ())
            break;
          Proxy::setEndValue(fpos);
        }

      // Игнорируем строки с неверным форматом
      if (sll.setLine (line) != true)
        continue;

      if (LocalNetworks::isLocalUrl (sll.getUrl ()))
        {
          DEBUG (DEBUG9, "Consider url is local");
          continue;
        }

      // Ищем пользователя, а если его нет и настроен autouser, то создаем
      usr = Proxy::findUser (sll.getIP (), sll.getIdent ());

      // Если пользователь по каким-то причинам не найден, то переходим к следующей строке
      if (usr == NULL)
        {
            continue;
        }

      // Применяем различные фильтры
      date_time = sll.getTime ();
      strftime (s_date, sizeof (s_date), "%Y-%m-%d", &date_time);
      strftime (s_time, sizeof (s_time), "%H:%M:%S", &date_time);
      if ((_date_filter != NULL) && (!_date_filter->match (date_time)))
        {
          DEBUG (DEBUG9, "Filtered out: " << s_date << " " << s_time << " outside date interval");
          continue;
        }
      if ((_user_filter != NULL) && (!_user_filter->match (usr)))
        {
          DEBUG (DEBUG9, "Filtered out: " << *usr << " not in the filter");
          continue;
        }

      memset (s_user, 0, sizeof(s_user));
      memset (s_domain, 0, sizeof(s_domain));
      memset (s_ipaddr, 0, sizeof(s_ipaddr));
      memset (s_method, 0, sizeof(s_method));
      memset (s_url, 0, sizeof(s_url));

      // Анализируем входную строку
      s_hit = 0;
      s_size = 0;
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
        case SquidLogLine::TCP_REFRESH_UNMODIFIED:
        case SquidLogLine::TCP_REF_FAIL_HIT:
        case SquidLogLine::TCP_IMS_HIT:
        case SquidLogLine::UDP_HIT:
          s_hit = sll.getSize ();
          usr->addHit (s_hit);
        case SquidLogLine::TCP_NEGATIVE_HIT:
        case SquidLogLine::TCP_MISS:
        case SquidLogLine::TCP_REFRESH_MISS:
        case SquidLogLine::TCP_REFRESH_MODIFIED:
        case SquidLogLine::TCP_CLIENT_REFRESH:
        case SquidLogLine::TCP_CLIENT_REFRESH_MISS:
        case SquidLogLine::TCP_IMS_MISS:
        case SquidLogLine::TCP_SWAPFAIL:
        case SquidLogLine::TCP_SWAPFAIL_MISS:
        case SquidLogLine::UDP_HIT_OBJ:
        case SquidLogLine::UDP_MISS:
        case SquidLogLine::UDP_INVALID:
        case SquidLogLine::UDP_RELOADING:
        case SquidLogLine::ERR_CLIENT_ABORT:
        case SquidLogLine::ERR_NO_CLIENTS:
        case SquidLogLine::ERR_READ_ERROR:
        case SquidLogLine::ERR_CONNECT_FAIL:
          s_size = sll.getSize ();
          usr->addSize (s_size);
          break;
        }

      if (s_size == 0)
        {
          DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "s_size=0, drop processing log line");
          continue;
        }

      sprintf (s_user, "%s", usr->getNick ().c_str ());
      sprintf (s_domain, "%s", usr->getDomain ().c_str ());
      sprintf (s_ipaddr, "%s", sll.getIP ().c_str ());
      s_period = sll.getBusytime ();
      strncpy (s_url, sll.getUrl ().c_str (), 1024);
      if (sll.getUrl ().length () >= 1024)
        s_url[1023] = 0;
      //sprintf (s_url, "%s", sll.getUrl ().c_str ());
      sprintf (s_method, "%s", sll.getMethod ().c_str ());

      // Обновляем squidcache
      if (!updCacheQuery->sendQuery ())
        break;


      // Обновляем cachesum
      ///< @todo Для поиска значений в cachesum вместо прямого запроса выполнять подготовленный
      cachesum_select_cmd.str("");
      cachesum_select_cmd << "select s_size, s_hit from cachesum where s_proxy_id=" << _proxyid;
      cachesum_select_cmd << " and s_date='" << s_date <<"' and s_domain='" << s_domain << "' and s_user='" << s_user << "'";
      if (!selCachesumQuery->sendQueryDirect (cachesum_select_cmd.str ()))
        break;

      if (!selCachesumQuery->fetch ()) // такой записи еще нет, нужно добавлять
        {
          cachesum_size = s_size;
          cachesum_hit = s_hit;
          DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Set cachesum for "<<s_user<<": cachesum_size=" << cachesum_size << ", cachesum_hit=" << cachesum_hit);
          if (!insCachesumQuery->sendQuery ())
            break;
        }
      else //такая запись существует, нужно обновлять
        {
          DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Got cachesum for "<<s_user<<": cachesum_size=" << cachesum_size << ", cachesum_hit=" << cachesum_hit);
          cachesum_size += s_size;
          cachesum_hit += s_hit;
          DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Update cachesum for "<<s_user<<": cachesum_size=" << cachesum_size << ", cachesum_hit=" << cachesum_hit);
          if (!updCachesumQuery->sendQuery ())
            break;
        }

      // Данные считываются из нестандартного внешнего файла, поэтому желательно проверить
      // попадают ли они в текущий временной период шаблона пользователя
      if (from_begin)
        {
          Template *tpl = TemplateList::getTemplate(usr->getCurrentTemplateId ());
          if (!tpl) // нарушена целостность базы
            continue;
          if (!tpl->insidePeriod(date_time))
            continue;
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
      if ( (allowed_limit > 0) && (used_size > allowed_limit) )
        {
          if ((usr->getEnabled () == SAMSUser::STAT_ACTIVE) || (usr->getEnabled () == SAMSUser::STAT_LIMITED))
            {
              usr->deactivate ();
              basic_stringstream < char >mess;
              switch (usr->getEnabled ())
                {
                  case SAMSUser::STAT_OFF:
                  case SAMSUser::STAT_ACTIVE:
                    // Этого не может быть
                    break;
                  case SAMSUser::STAT_INACTIVE:
                    mess << "User " << *usr << " deactivated.";
                    break;
                  case SAMSUser::STAT_LIMITED:
                    mess << "User " << *usr << " moved to temporary template.";
                    break;
                }
              INFO (mess.str ());
              Logger::addLog(Logger::LK_USER, mess.str());
              string adminaddr = Proxy::getAdminAddr ();
              if (!adminaddr.empty ())
                {
                  int err;
                  string cmd = BINDIR;
                  cmd += "/sams_send_email " + adminaddr;
                  cmd += " " + usr->asString ();
                  cmd += " '" + mess.str () + "'";
                  DEBUG (DEBUG5, "[" << this << "->" << __FUNCTION__ << "] " << "Executing " << cmd);
                  err = system (cmd.c_str ());
                  ERROR ("Failed to send e-mail: " << err);
                }
              need_reconfig = true;
            }
        }
      s_enabled = (long)usr->getEnabled();
      if (!updUserQuery->sendQuery ())
        break;
    }
  in.close ();

  if (need_reconfig)
    {
      DBQuery *reconfigQuery = NULL;
      conn->newQuery (reconfigQuery);
      if (!reconfigQuery)
        {
          ERROR("Unable to create query.");
          return;
        }
      basic_stringstream < char > reconfig_cmd;
      reconfig_cmd << "insert into reconfig (s_proxy_id, s_service, s_action)";
      reconfig_cmd << " VALUES ("<<_proxyid<<", 'squid', 'reconfig')";
      reconfigQuery->sendQueryDirect (reconfig_cmd.str());
      Logger::addLog(Logger::LK_DAEMON, "Send request to reconfigure SQUID (a user state changed).");
      delete reconfigQuery;
    }

  return;
}
