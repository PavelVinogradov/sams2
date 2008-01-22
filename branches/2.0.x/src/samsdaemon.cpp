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

/**
 *  Выводит список опций командной строки с кратким описанием
 */
void usage ()
{
  cout << endl;
  cout << "NAME" << endl;
  cout << "    samsparser - parse squid log file and update database." << endl;
  cout << endl;
  cout << "SYNOPSIS" << endl;
  cout << "    samsparser [COMMAND] [OPTION]..." << endl;
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
  Proxy::reload ();
  LocalNetworks::reload();
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
  string optname = "";
  bool must_fork = true;
  bool use_must_fork = false;

  logger = new Logger ();

  logger->setSender("samsdaemon");

  // Сначала прочитаем конфигурацию, параметры командной строки
  // имеют приоритет, потому анализируются позже

  dbglevel = SamsConfig::getInt (defDEBUG, err);

  if (err == ERR_OK)
    logger->setDebugLevel (dbglevel);

  static struct option long_options[] = {
    {"help",     0, 0, 'h'},     // Показывает справку по опциям командной строки и завершает работу
    {"version",  0, 0, 'V'},     // Показывает версию программы и завершает работу
    {"verbose",  0, 0, 'v'},     // Устанавливает режим многословности
    {"debug",    1, 0, 'd'},     // Устанавливает уровень отладки
    {"fork",     0, 0, 'f'},     // Запускать в фоновом режиме
    {"no-fork",  0, 0, 'F'},     // Не запускать в фоновом режиме
    {"logger",   1, 0, 'l'},     // Устанавливает движок вывода сообщений
    {0, 0, 0, 0}
  };

  while (1)
    {
      int option_index = 0;

      c = getopt_long (argc, argv, "hVvd:fFl:", long_options, &option_index);
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
          logger->setVerbose (true);
          break;
        case 'd':
          if (sscanf (optarg, "%d", &dbglevel) != 1)
            dbglevel = 0;
          logger->setDebugLevel (dbglevel);
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
        case 'l':
          DEBUG (DEBUG_CMDARG, "option: --logger=" << optarg);
          logger->setEngine (optarg);
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

  string dbsrc = SamsConfig::getString (defDBSOURCE, err);
  string dbuser = SamsConfig::getString (defDBUSER, err);
  string dbpass = SamsConfig::getString (defDBPASSWORD, err);
  if (dbsrc.empty ())
    {
      ERROR ("No datasource defined. Check " << defDBSOURCE << " in config file.");
      exit (1);
    }

  int sleeptime = SamsConfig::getInt (defSLEEPTIME, err);
  if (err != ERR_OK)
    {
      ERROR ("Cannot get sleep time for daemon. See debugging messages for more details.");
      exit (1);
    }

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


  ProcessManager process;

  if (!process.start ("samsdaemon"))
    {
      exit (2);
    }

  DBConn *conn = NULL;
  DBQuery *query = NULL;

  DBConn::DBEngine engine = SamsConfig::getEngine();

  if (engine == DBConn::DB_UODBC)
    {
      #ifdef USE_UNIXODBC
      conn = new ODBCConn();
      query = new ODBCQuery((ODBCConn*)conn);
      #else
      return 1;
      #endif
    }
  else if (engine == DBConn::DB_MYSQL)
    {
      #ifdef USE_MYSQL
      conn = new MYSQLConn();
      query = new MYSQLQuery((MYSQLConn*)conn);
      #else
      return 1;
      #endif
    }
  else
    return 1;

  if (!conn->connect ())
    {
      delete query;
      delete conn;
      return 1;
    }



  struct sigaction sighup_action;
  memset (&sighup_action, 0, sizeof (sighup_action));
  sighup_action.sa_handler = &reload;
  sigaction (SIGHUP, &sighup_action, NULL);

  SAMSUsers::useConnection (conn);
  LocalNetworks::useConnection (conn);
  Proxy::useConnection (conn);

  char s_service[15];
  char s_action[10];

  if (!query->bindCol (1, DBQuery::T_CHAR, s_service, sizeof (s_service)))
    {
      delete query;
      delete conn;
      return 1;
    }
  if (!query->bindCol (2, DBQuery::T_CHAR, s_action, sizeof (s_action)))
    {
      delete query;
      delete conn;
      return 1;
    }

  basic_stringstream < char >cmd_check;
  basic_stringstream < char >cmd_del;

  cmd_check << "select s_service, s_action from reconfig where s_proxy_id=" << proxyid;
  cmd_del << "delete from reconfig where s_proxy_id=" << proxyid << " and service=? and action=?";

  SquidLogParser *parser = NULL;
  int seconds_left = 0;
  static string service_proxy = "proxy";
  static string action_shutdown = "shutdown";
  static string action_reload = "reload";

  while (true)
    {
      if (seconds_left < 0)
        seconds_left = 0;

      DEBUG (DEBUG_DAEMON, "Countdown: " << seconds_left);

      if (!query->sendQueryDirect (cmd_check.str()) )
        {
          delete query;
          delete conn;
          return 1;
        }
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
              Proxy::destroy();
              LocalNetworks::destroy();
              SAMSUsers::destroy();
              delete query;
              delete conn;
              DEBUG (DEBUG_DAEMON, "Shutdown");
              return 0;
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
        }

      if (seconds_left == 0)
        {
          DEBUG (DEBUG_DAEMON, "Processing access.log ...");
          parser = new SquidLogParser (proxyid);
          parser->parseFile (conn, squidlogdir + "/" + squidcachefile, false);
          delete parser;
          seconds_left = (60 * steptime);
        }

      sleep (sleeptime);
      seconds_left -= sleeptime;
    }

}
