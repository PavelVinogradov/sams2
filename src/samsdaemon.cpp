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
#include <unistd.h>
#include <getopt.h>
#include <signal.h>

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

#include "debug.h"
#include "samsconfig.h"
#include "processmanager.h"
#include "squidlogparser.h"
#include "samsusers.h"
#include "proxy.h"
#include "localnetworks.h"
#include "groups.h"
#include "templates.h"
#include "tools.h"

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
  cout << "    -h, --help" << endl;
  cout << "                Show this help screen and exit." << endl;
  cout << "    -V, --version" << endl;
  cout << "                Print program version number and exit." << endl;
  cout << endl;
  cout << "Mandatory arguments to long options are mandatory for short options too." << endl;
  cout << "The following options are available:" << endl;
  cout << endl;
  cout << "OPTIONS" << endl;
  cout << "        --fork" << endl;
  cout << "                Force to start in background mode." << endl;
  cout << "        --no-fork" << endl;
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

void reload (int signal_number)
{
  DEBUG (DEBUG_DAEMON, "Reloading");
  Templates::reload ();
  Proxy::reload ();
  LocalNetworks::reload();
  Groups::reload();
  SAMSUsers::reload ();
}


/** @todo Выбирать путь к pid файлу из опций configure
*/
int main (int argc, char *argv[])
{
  int parse_errors = 0;
  int c;
  int err;
  uint dbglevel;
  int reconnect_timeout = 3600;
  string optname = "";
  bool must_fork = true;
  bool use_must_fork = false;

  Logger::setSender("samsdaemon");

  // Сначала прочитаем конфигурацию, параметры командной строки
  // имеют приоритет, потому анализируются позже

  dbglevel = SamsConfig::getInt (defDEBUG, err);

  if (err == ERR_OK)
    Logger::setDebugLevel (dbglevel);

  static struct option long_options[] = {
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

      c = getopt_long (argc, argv, "hVvd:fFt:l:", long_options, &option_index);
      if (c == -1)              // no more options
        break;
      switch (c)
        {
        case 0:
          optname = long_options[option_index].name;
          DEBUG (DEBUG_CMDARG, "option: " << optname << "=" << optarg);
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

  // Интервал в секунах, через который нужно проверять наличие команд для демона
  int check_interval = SamsConfig::getInt (defSLEEPTIME, err);
  if (err != ERR_OK)
    {
      ERROR ("Cannot get sleep time for daemon. See debugging messages for more details.");
      exit (1);
    }

  // Интервал в минутах, через который нужно считывать данные из access.log
  int steptime = SamsConfig::getInt (defDAEMONSTEP, err);
  if (err != ERR_OK)
    {
      ERROR ("Cannot get step time for daemon. See debugging messages for more details.");
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

//  struct sigaction sigchld_action;
//  memset (&sigchld_action, 0, sizeof (sigchld_action));
//  sigchld_action.sa_handler = &clean_up_child_process;
//  sigaction (SIGCHLD, &sigchld_action, NULL);

  pid_t childpid=0;

  if ( (use_must_fork && must_fork) || (dbglevel == 0 && !use_must_fork) )
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
  else
    return 1;

  if (!conn->connect ())
    {
      delete conn;
      return 1;
    }

  SAMSUsers::useConnection (conn);
  LocalNetworks::useConnection (conn);
  Groups::useConnection (conn);
  Proxy::useConnection (conn);
  Templates::useConnection (conn);
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
  static string action_shutdown = "shutdown";
  static string action_reload = "reload";
  static string action_reconfig = "reconfig";

  string reconfiguresquid = squidbindir + "/squid -k reconfigure";

  time_t loop_start = time (NULL);
  time_t loop_end = time (NULL);
  int looptime;
  int sleeptime;
  while (true)
    {
      looptime = (int) difftime(loop_start, loop_end);
      sleeptime = check_interval - looptime;
      if (sleeptime < 0)
        sleeptime = 0;

      if (sleeptime > 0)
        sleep (sleeptime);

      seconds_to_parse     -= (looptime + sleeptime);
      if (seconds_to_parse < 0)
        seconds_to_parse = 0;
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

      DEBUG (DEBUG_DAEMON, "Process " << squidcachefile << " in " << seconds_to_parse << " second[s]");

      if (!query->sendQueryDirect (cmd_check.str()) )
        {
          DEBUG (DEBUG_DAEMON, "Reconnect in " << seconds_to_reconnect << " second[s]");
          continue;
        }

      loop_start = time (NULL);

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
          if (s_service == service_proxy && s_action == action_reload)
            {
              cmd_del.str("");
              cmd_del << "delete from reconfig where s_proxy_id=" << proxyid;
              cmd_del << " and s_service='" << service_proxy << "'";
              cmd_del << " and s_action='" << action_reload << "'";
              query->sendQueryDirect (cmd_del.str());
              reload(-1);
            }
          if (s_service == service_squid && s_action == action_reconfig)
            {
              cmd_del.str("");
              cmd_del << "delete from reconfig where s_proxy_id=" << proxyid;
              cmd_del << " and s_service='" << service_squid << "'";
              cmd_del << " and s_action='" << action_reconfig << "'";
              query->sendQueryDirect (cmd_del.str());

              Logger::addLog(Logger::LK_DAEMON, "Got request to reconfigure SQUID");

              // Create extenal files
              // ...

              // Change config files
              // ...

              // Reconfigure squid
              msg.str("");
              err = system (reconfiguresquid.c_str());
              if (err)
                msg << "Failed to restart SQUID: " << err;
              else
                msg << "Reconfigure & restart SQUID: ok";
              Logger::addLog (Logger::LK_DAEMON, msg.str ());
            }
        }

      if (seconds_to_parse == 0)
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
