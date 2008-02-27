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

using namespace std;

#include <unistd.h>
#include <getopt.h>
#include <signal.h>

#include <fstream>
#include <sstream>

#include "config.h"
#include "configmake.h"

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

#include "debug.h"
#include "samsconfig.h"
#include "processmanager.h"
#include "squidlogparser.h"
#include "samsusers.h"
#include "proxy.h"
#include "localnetworks.h"
#include "groups.h"
#include "templates.h"
#include "template.h"
#include "timeranges.h"
#include "tools.h"
#include "squidconf.h"
#include "dbcleaner.h"
#include "dbexporter.h"

/**
 *  Выводит список опций командной строки с кратким описанием
 */
void usage ()
{
  cout << endl;
  cout << "NAME" << endl;
  cout << "    samsdaemon - periodicaly parse squid log file and process user commands." << endl;
  cout << endl;
  cout << "SYNOPSIS" << endl;
  cout << "    samsdaemon [COMMAND] [OPTION]..." << endl;
  cout << endl;
  cout << "COMMANDS" << endl;
  cout << "    -s, --stop" << endl;
  cout << "                Stop samsdaemon." << endl;
  cout << "    -h, --help" << endl;
  cout << "                Show this help screen and exit." << endl;
  cout << "    -V, --version" << endl;
  cout << "                Print program version number and exit." << endl;
  cout << endl;
  cout << "Mandatory arguments to long options are mandatory for short options too." << endl;
  cout << "The following options are available:" << endl;
  cout << endl;
  cout << "OPTIONS" << endl;
  cout << "    -f, --fork" << endl;
  cout << "                Force to start in background mode." << endl;
  cout << "    -F, --no-fork" << endl;
  cout << "                Force to start in foreground mode." << endl;
  cout << "    -t, --timeout=SECONDS" << endl;
  cout << "                Always reconnect every SECONDS. Default is 3600 (one hour)." << endl;
  cout << "    -v, --verbose" << endl;
  cout << "                Produce more output." << endl;
  cout << "    -d, --debug=LEVEL" << endl;
  cout << "                Produce lots of debugging information depend on LEVEL." << endl;
  cout << "                If LEVEL greater than zero, start in foreground mode (unless --fork specified)." << endl;
  cout << "                For more information about possible LEVEL values refer to a developer." << endl;
  cout << "    -l, --logger=LOGGER" << endl;
  cout << "                Set engine for messages output. Possible values for LOGGER are: console, syslog, file." << endl;
  cout << "                In case of file you can set filename for output (DEFAULT: samsparser.log)." << endl;
  cout << "                E.g. -l syslog" << endl;
  cout << "                E.g. -l file:/path/to/file" << endl;
  cout << endl;
}

/**
 *  Выводит версию программы и немного информации
 */
void version ()
{
  cout << "samsdaemon " << VERSION << endl;
  cout << "Written by anonymous." << endl;
  cout << endl;
  cout << "This program comes with NO WARRANTY; not even for MERCHANTABILITY" << endl;
  cout << "or FITNESS FOR A PARTICULAR PURPOSE." << endl;
}

int check_interval; //Интервал в секунах, через который нужно проверять наличие команд для демона
long steptime;      //Интервал в минутах, через который нужно обрабатывать лог squid
Proxy::ParserType parserType; // Тип обработки лог файла squid

void reload (int signal_number)
{
  DEBUG (DEBUG_DAEMON, "Reloading");
  SamsConfig::reload ();
  TimeRanges::reload ();
  Templates::reload ();
  Proxy::reload ();
  LocalNetworks::reload();
  Groups::reload ();
  SAMSUsers::reload ();

  int err;

  check_interval = SamsConfig::getInt (defSLEEPTIME, err);
  if (err != ERR_OK)
    {
      ERROR ("Cannot get sleep time for daemon. See debugging messages for more details.");
      exit (1);
    }

  Proxy::getParserType (parserType, steptime);

}


/** @todo Выбирать путь к pid файлу из опций configure
*/
int main (int argc, char *argv[])
{
  int parse_errors = 0;
  int c;
  int err;
  uint dbglevel = 0;
  int reconnect_timeout = 3600;
  string optname = "";
  bool must_fork = true;
  bool use_must_fork = false;
  bool stop_it = false;
  Logger::setSender("samsdaemon");

  // Сначала прочитаем конфигурацию, параметры командной строки
  // имеют приоритет, потому анализируются позже
  SamsConfig::reload ();

  static struct option long_options[] = {
    {"stop",     0, 0, 's'},     // Показывает справку по опциям командной строки и завершает работу
    {"help",     0, 0, 'h'},     // Показывает справку по опциям командной строки и завершает работу
    {"version",  0, 0, 'V'},     // Показывает версию программы и завершает работу
    {"verbose",  0, 0, 'v'},     // Устанавливает режим многословности
    {"debug",    1, 0, 'd'},     // Устанавливает уровень отладки
    {"fork",     0, 0, 'f'},     // Запускать в фоновом режиме
    {"no-fork",  0, 0, 'F'},     // Не запускать в фоновом режиме
    {"timeout",  1, 0, 't'},     // Устанавливает время переподключения к БД
    {"logger",   1, 0, 'l'},     // Устанавливает движок вывода сообщений
    {0, 0, 0, 0}
  };

  while (1)
    {
      int option_index = 0;

      c = getopt_long (argc, argv, "shVvd:fFt:l:", long_options, &option_index);
      if (c == -1)              // no more options
        break;
      switch (c)
        {
        case 0:
          optname = long_options[option_index].name;
          DEBUG (DEBUG_CMDARG, "option: " << optname << "=" << optarg);
          break;
        case 's':
          DEBUG (DEBUG_CMDARG, "option: --stop");
          stop_it = true;
          break;
        case 'h':
          DEBUG (DEBUG_CMDARG, "option: --help");
          usage ();
          exit (0);
          break;
        case 'V':
          DEBUG (DEBUG_CMDARG, "option: --version");
          version ();
          exit (0);
          break;
        case 'v':
          DEBUG (DEBUG_CMDARG, "option: --verbose");
          Logger::setVerbose (true);
          break;
        case 'd':
          if (sscanf (optarg, "%d", &dbglevel) != 1)
            dbglevel = 0;
          Logger::setDebugLevel (dbglevel);
          DEBUG (DEBUG_CMDARG, "option: --debug=" << dbglevel);
          break;
        case 'f':
          DEBUG (DEBUG_CMDARG, "option: --fork");
          must_fork = true;
          use_must_fork = true;
          break;
        case 'F':
          DEBUG (DEBUG_CMDARG, "option: --no-fork");
          must_fork = false;
          use_must_fork = true;
          break;
        case 't':
          if (sscanf (optarg, "%d", &reconnect_timeout) != 1)
            reconnect_timeout = 3600;
          DEBUG (DEBUG_CMDARG, "option: --timeout=" << reconnect_timeout);
          break;
        case 'l':
          DEBUG (DEBUG_CMDARG, "option: --logger=" << optarg);
          Logger::setEngine (optarg);
          break;
        case '?':
          break;
        default:
          printf ("?? getopt return character code 0%o ??\n", c);
        }
    }

  if (parse_errors > 0)
    {
      usage ();
      exit (parse_errors);
    }

  int proxyid = SamsConfig::getInt (defPROXYID, err);
  if (err != ERR_OK)
    {
      ERROR ("No proxyid defined. Check " << defPROXYID << " in config file.");
      exit (1);
    }

  check_interval = SamsConfig::getInt (defSLEEPTIME, err);
  if (err != ERR_OK)
    {
      ERROR ("Cannot get sleep time for daemon. See debugging messages for more details.");
      exit (1);
    }

  string squidlogdir = SamsConfig::getString (defSQUIDLOGDIR, err);
  string squidcachefile = SamsConfig::getString (defSQUIDCACHEFILE, err);

  if (squidlogdir.empty () || squidcachefile.empty ())
    {
      ERROR ("Either " << defSQUIDLOGDIR << " or " << defSQUIDCACHEFILE << " not defined. Check config file.");
      exit (1);
    }

  string squidbindir = SamsConfig::getString (defSQUIDBINDIR, err);
  if (!fileExist (squidbindir + "/squid"))
    {
      ERROR ("Invalid " << defSQUIDBINDIR << ". Check config file.");
      exit (1);
    }


  pid_t childpid=0;

  DEBUG (DEBUG_DAEMON, "dbglevel="<<dbglevel<<", use_must_fork="<<use_must_fork<<", must_fork="<<must_fork);
  if ( (use_must_fork && must_fork) || ( (dbglevel == 0) && !use_must_fork) )
    {
      childpid = fork ();
    }

  if (childpid == -1)
    {
      exit(3);
    }

  if (childpid > 0)
    {
      exit(0);
    }

  Proxy::getParserType (parserType, steptime);

  struct sigaction sighup_action;
  memset (&sighup_action, 0, sizeof (sighup_action));
  sighup_action.sa_handler = &reload;
  sigaction (SIGHUP, &sighup_action, NULL);

  DBConn *conn = NULL;
  DBQuery *query = NULL;

  DBConn::DBEngine engine = SamsConfig::getEngine();

  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      conn = new ODBCConn();
      #else
      return 1;
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      conn = new MYSQLConn();
      #else
      return 1;
      #endif
    }
  else if (engine == DBConn::DB_PGSQL)
    {
      #ifdef USE_PQ
      conn = new PgConn();
      #else
      return 1;
      #endif
    }
  else
    return 1;

  if (!conn->connect ())
    {
      delete conn;
      return 1;
    }

  // Программу запустили только для остановки демона
  // Поэтому заносим в БД команду остановки и выходим
  if (stop_it)
    {
      DBQuery *q = NULL;
      if (engine == DBConn::DB_UODBC)
        {
          #ifdef USE_UNIXODBC
          q = new ODBCQuery((ODBCConn*)conn);
          #endif
        }
      else if (engine == DBConn::DB_MYSQL)
        {
          #ifdef USE_MYSQL
          q = new MYSQLQuery((MYSQLConn*)conn);
          #endif
        }
      else if (engine == DBConn::DB_PGSQL)
        {
          #ifdef USE_PQ
          q = new PgQuery((PgConn*)conn);
          #endif
        }
      basic_stringstream < char >cmd_stop;
      cmd_stop << "insert into reconfig set s_proxy_id=" << proxyid << ", s_service='proxy', s_action='shutdown'";
      bool res = q->sendQueryDirect (cmd_stop.str());

      delete q;
      delete conn;

      if (res)
        return 0;

      return 2;
    }

  SAMSUsers::useConnection (conn);
  LocalNetworks::useConnection (conn);
  Groups::useConnection (conn);
  Proxy::useConnection (conn);
  Templates::useConnection (conn);
  TimeRanges::useConnection (conn);
  Logger::useConnection (conn);

  ProcessManager process;

  if (!process.start ("samsdaemon"))
    {
      delete conn;
      exit (0);
    }

  char s_service[15];
  char s_action[10];

  basic_stringstream < char >cmd_check;
  basic_stringstream < char >cmd_del;
  basic_stringstream < char >msg;

  cmd_check << "select s_service, s_action from reconfig where s_proxy_id=" << proxyid;

  SquidLogParser *parser = NULL;
  int seconds_to_parse = 0;
  int seconds_to_reconnect = reconnect_timeout;
  static string service_proxy = "proxy";
  static string service_squid = "squid";
  static string service_dbase = "database";
  static string action_shutdown = "shutdown";
  static string action_reload = "reload";
  static string action_reconfig = "reconfig";
  static string action_export = "export";

  string reconfiguresquid = squidbindir + "/squid -k reconfigure";

  time_t loop_start = time (NULL);
  time_t loop_end = time (NULL);
  int looptime;
  int sleeptime;
  struct tm *time_now;
  struct tm time_clear;
  struct tm time_was;
  vector <long> tpl_ids;
  Template *tpl = NULL;
  uint i;
  time_was.tm_mday = -1;
  string dateEnd;
  string dateStart;
  while (true)
    {
      looptime = (int) difftime(loop_start, loop_end);
      sleeptime = check_interval - looptime;
      if (sleeptime < 0)
        sleeptime = 0;

      if (sleeptime > 0)
        sleep (sleeptime);

      if (parserType == Proxy::PARSE_DISCRET)
        {
          seconds_to_parse -= (looptime + sleeptime);
          if (seconds_to_parse < 0)
            seconds_to_parse = 0;
        }
      seconds_to_reconnect -= (looptime + sleeptime);

      if (seconds_to_reconnect <= 0)
        {
          delete query;
          query = NULL;
          conn->disconnect();
          if (!conn->connect ())
            {
              continue;
            }
          seconds_to_reconnect = reconnect_timeout;
        }

      if (!query)
        {
          if (engine == DBConn::DB_UODBC)
            {
              #ifdef USE_UNIXODBC
              query = new ODBCQuery((ODBCConn*)conn);
              #endif
            }
          else if (engine == DBConn::DB_MYSQL)
            {
              #ifdef USE_MYSQL
              query = new MYSQLQuery((MYSQLConn*)conn);
              #endif
            }
          else if (engine == DBConn::DB_PGSQL)
            {
              #ifdef USE_PQ
              query = new PgQuery((PgConn*)conn);
              #endif
            }
          if (!query->bindCol (1, DBQuery::T_CHAR, s_service, sizeof (s_service)))
            {
              delete query;
              query = NULL;
              continue;
            }
          if (!query->bindCol (2, DBQuery::T_CHAR, s_action, sizeof (s_action)))
            {
              delete query;
              query = NULL;
              continue;
            }
        }

      if (parserType == Proxy::PARSE_DISCRET)
        {
          DEBUG (DEBUG_DAEMON, "Process " << squidcachefile << " in " << seconds_to_parse << " second[s]");
        }

      if (!query->sendQueryDirect (cmd_check.str()) )
        {
          DEBUG (DEBUG_DAEMON, "Reconnect in " << seconds_to_reconnect << " second[s]");
          continue;
        }

      loop_start = time (NULL);

      time_now = localtime (&loop_start);
      // Если начался новый день, то, возможно, нужно очищать счетчики пользователей
      if ((time_was.tm_mday != -1) && (time_was.tm_mday != time_now->tm_mday))
        {
          DBCleaner *cleaner = NULL;
          vector<SAMSUser *> lstUsers;
          tpl_ids = Templates::getIds ();
          for (i = 0; i < tpl_ids.size (); i++)
            {
              tpl = Templates::getTemplate (tpl_ids[i]);
              // У шаблона месячный период и начался новый месяц
              // или у шаблона недельный период и начался понедельник
              // или шаблон имеет нестандартный период, и настал день очистки счетчиков
              if  ( ((tpl->getPeriodType () == Template::PERIOD_MONTH) && (time_now->tm_mday == 1)) ||
                    ((tpl->getPeriodType () == Template::PERIOD_WEEK) && (time_now->tm_wday == 1))  ||
                    (tpl->getClearDate (time_clear) && (time_now->tm_year == time_clear.tm_year) && (time_now->tm_yday == time_clear.tm_yday))
                  )
                {
                  DEBUG (DEBUG_DAEMON, "Clear counters for template " << tpl_ids[i]);
                  SAMSUsers::getUsersByTemplate (tpl_ids[i], lstUsers);
                  if (lstUsers.size () > 0)
                    {
                      if (!cleaner)
                        cleaner = new DBCleaner ();
                      cleaner->setUserFilter (lstUsers);
                      cleaner->clearCounters ();
                    }
                }
            }
        }

      memcpy (&time_was, time_now, sizeof (struct tm));

      // обрабатываем команды, поступившие извне (если они есть)
      while (query->fetch ())
        {
          //insert into reconfig set s_proxy_id=1, s_service='proxy', s_action='shutdown'
          if (s_service == service_proxy && s_action == action_shutdown)
            {
              cmd_del.str("");
              cmd_del << "delete from reconfig where s_proxy_id=" << proxyid;
              cmd_del << " and s_service='" << service_proxy << "'";
              cmd_del << " and s_action='" << action_shutdown << "'";
              query->sendQueryDirect (cmd_del.str());
              DEBUG (DEBUG_DAEMON, "Shutdown");
              process.stop();
              Proxy::destroy();
              LocalNetworks::destroy();
              SAMSUsers::destroy();
              Templates::destroy();
              Logger::destroy();
              delete query;
              delete conn;
              exit(0);
            }
          //insert into reconfig set s_proxy_id=1, s_service='proxy', s_action='reload'
          if (s_service == service_proxy && s_action == action_reload)
            {
              cmd_del.str("");
              cmd_del << "delete from reconfig where s_proxy_id=" << proxyid;
              cmd_del << " and s_service='" << service_proxy << "'";
              cmd_del << " and s_action='" << action_reload << "'";
              query->sendQueryDirect (cmd_del.str());
              reload(-1);
            }
          //insert into reconfig set s_proxy_id=1, s_service='squid', s_action='reconfig'
          if (s_service == service_squid && s_action == action_reconfig)
            {
              cmd_del.str("");
              cmd_del << "delete from reconfig where s_proxy_id=" << proxyid;
              cmd_del << " and s_service='" << service_squid << "'";
              cmd_del << " and s_action='" << action_reconfig << "'";
              query->sendQueryDirect (cmd_del.str());

              Logger::addLog(Logger::LK_DAEMON, "Got request to reconfigure SQUID");

              // Изменить squid.conf и если ошибок нет, то перезапустить squid
              msg.str("");
              if (SquidConf::defineAccessRules())
                {
                  err = system (reconfiguresquid.c_str());
                  if (err)
                    msg << "Failed to restart SQUID: " << err;
                  else
                    msg << "Reconfigure & restart SQUID: ok";
                }
              else
                msg << "Failed to change squid.conf";

              Logger::addLog (Logger::LK_DAEMON, msg.str ());
            }
          //insert into reconfig set s_proxy_id=1, s_service='database', s_action='export'
          if (s_service == service_dbase && s_action == action_export)
            {
              cmd_del.str("");
              cmd_del << "delete from reconfig where s_proxy_id=" << proxyid;
              cmd_del << " and s_service='" << service_dbase << "'";
              cmd_del << " and s_action='" << action_export << "'";
              query->sendQueryDirect (cmd_del.str());
              DBExporter *exporter = new DBExporter ();
              exporter->setDateFilter ("2007-10-22");
              exporter->exportToFile ("/tmp/sams-2007-10-22.txt");
              delete exporter;
            }
        }

      // Если дискретная обработка лог файла и время пришло, то запускаем обработку
      if ( (parserType == Proxy::PARSE_DISCRET) && (seconds_to_parse == 0) )
        {
          DEBUG (DEBUG_DAEMON, "Processing " << squidcachefile << " ...");
          parser = new SquidLogParser (proxyid);
          parser->parseFile (conn, squidlogdir + "/" + squidcachefile, false);
          delete parser;
          seconds_to_parse = (60 * steptime);
        }

      loop_end = time (NULL);

    }

}
