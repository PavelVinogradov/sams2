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
#include <stdlib.h>
#include <string.h>
#include <getopt.h>
#include <signal.h>

#include <fstream>
#include <sstream>

#include "config.h"
#include "configmake.h"

#include "dbconn.h"
#include "dbquery.h"
#include "debug.h"
#include "samsconfig.h"
#include "processmanager.h"
#include "squidlogparser.h"
#include "samsuserlist.h"
#include "urlgrouplist.h"
#include "proxy.h"
#include "localnetworks.h"
//#include "grouplist.h"
#include "templatelist.h"
#include "template.h"
#include "timerangelist.h"
#include "delaypoollist.h"
#include "tools.h"
#include "squidconf.h"
#include "dbcleaner.h"
#include "dbexporter.h"
#include "samsconfig.h"
#include "pluginlist.h"

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
  cout << "                In case of file you can set filename for output (DEFAULT: samsdaemon.log)." << endl;
  cout << "                E.g. -l syslog" << endl;
  cout << "                E.g. -l file:/path/to/file" << endl;
  cout << "    -C, --config=FILE" << endl;
  cout << "                Use config file FILE." << endl;
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
string cmdreconfiguresquid; // Команда для реконфигурирования squid
uint dbglevel_cmd = 0;

void reload (int signal_number)
{
  DEBUG (DEBUG2, "Reload...");

  SamsConfig::reload ();
  TimeRangeList::reload ();
  TemplateList::reload ();
  Proxy::reload ();
  LocalNetworks::reload();
  //GroupList::reload (); // not used anywhere
  SAMSUserList::reload ();
  UrlGroupList::reload ();
  DelayPoolList::reload ();
  PluginList::reload ();

  int err;

  check_interval = SamsConfig::getInt (defSLEEPTIME, err);
  if (err != ERR_OK)
    {
      ERROR ("Cannot get sleep time for daemon. See debugging messages for more details.");
      exit (1);
    }

  Proxy::getParserType (parserType, steptime);

  uint dbglevel;
  int dbglevel_db = SamsConfig::getInt (defDEBUG, err);
  if ((dbglevel_cmd == 0) && (err == ERR_OK) )
    {
      dbglevel = (uint)dbglevel_db;
    }
  else
    {
      dbglevel = dbglevel_cmd;
    }

  Logger::setDebugLevel (dbglevel);
}

void reconfigureSQUID ()
{
  int err;
  basic_stringstream < char >msg;

  DEBUG (DEBUG2, "Reconfigure Squid...");

  // Если получили запрос на реконфигурирование, то скорей всего в БД что-то изменилось
  // значит нужно получить эти изменения
  reload (-1);

  // Изменить squid.conf и если ошибок нет, то перезапустить squid
  msg.str ("");
  if (SquidConf::defineAccessRules ())
    {
      err = system (cmdreconfiguresquid.c_str ());
      if (err)
        msg << "Failed to restart SQUID: " << err;
      else
        msg << "Reconfigure & restart SQUID: ok";
    }
  else
    msg << "Failed to change squid.conf";

  Logger::addLog (Logger::LK_DAEMON, msg.str ());
}

/** @todo Выбирать путь к pid файлу из опций configure
*/
int main (int argc, char *argv[])
{
  int parse_errors = 0;
  int c;
  int err;
  int reconnect_timeout = 1800;
  string optname = "";
  bool must_fork = true;
  bool use_must_fork = false;
  bool stop_it = false;
  bool verbose = false;
  string log_engine = "";
  string config_file = SYSCONFDIR;
  config_file += "/sams2.conf";

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
    {"config",   1, 0, 'C'},     // Использовать альтернативный конфигурационный файл
    {0, 0, 0, 0}
  };

  while (1)
    {
      int option_index = 0;

      c = getopt_long (argc, argv, "shVvd:fFt:l:C:", long_options, &option_index);
      if (c == -1)              // no more options
        break;
      switch (c)
        {
        case 0:
          optname = long_options[option_index].name;
          break;
        case 's':
          stop_it = true;
          break;
        case 'h':
          usage ();
          exit (0);
          break;
        case 'V':
          version ();
          exit (0);
          break;
        case 'v':
          verbose = true;
          break;
        case 'd':
          if (sscanf (optarg, "%d", &dbglevel_cmd) != 1)
            dbglevel_cmd = 0;
          break;
        case 'f':
          must_fork = true;
          use_must_fork = true;
          break;
        case 'F':
          must_fork = false;
          use_must_fork = true;
          break;
        case 't':
          if (sscanf (optarg, "%d", &reconnect_timeout) != 1)
            reconnect_timeout = 1800;
          break;
        case 'l':
          log_engine = optarg;
          break;
        case 'C':
          config_file = optarg;
          break;
        case '?':
          break;
        default:
          printf ("?? getopt return character code 0%o ??\n", c);
        }
    }

  Logger::setSender("samsdaemon");
  SamsConfig::useFile (config_file);

  // Сначала прочитаем конфигурацию, параметры командной строки
  // имеют приоритет, потому анализируются позже
  SamsConfig::reload ();

  uint dbglevel;
  int dbglevel_db = SamsConfig::getInt (defDEBUG, err);
  if ((dbglevel_cmd == 0) && (err == ERR_OK) )
    {
      dbglevel = (uint)dbglevel_db;
    }
  else
    {
      dbglevel = dbglevel_cmd;
    }

  Logger::setEngine (log_engine);
  Logger::setVerbose (verbose);
  Logger::setDebugLevel (dbglevel);

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

  pid_t found_pid = ProcessManager::isRunning ("sams2daemon");
  if ( stop_it && !found_pid )
    {
      WARNING ("Not running");
      exit (1);
    }
  else if (!stop_it && found_pid)
    {
      ERROR ("Already running with pid " << found_pid);
      exit (1);
    }

  pid_t childpid=0;

  DEBUG (DEBUG_DAEMON, "dbglevel="<<dbglevel<<", use_must_fork="<<use_must_fork<<", must_fork="<<must_fork);
  if ( (use_must_fork && must_fork) || ( (dbglevel_cmd == 0) && !use_must_fork) )
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
  DBQuery *query2 = NULL;

  conn = SamsConfig::newConnection ();

  if (!conn)
    {
      ERROR ("Unable to create connection.");
      return 1;
    }

  if (!conn->connect ())
    {
      delete conn;
      return 1;
    }

  static string service_proxy = "proxy";
  static string service_squid = "squid";
  static string service_dbase = "database";
  static string service_daemon = "samsdaemon";
  static string action_shutdown = "shutdown";
  static string action_reload = "reload";
  static string action_reconfig = "reconfig";
  static string action_export = "export";

  // Программу запустили только для остановки демона
  // Поэтому заносим в БД команду остановки и выходим
  if (stop_it)
    {
      DBQuery *q = NULL;
      conn->newQuery (q);
      if (!q)
        {
          ERROR("Unable to create query.");
          delete conn;
          return 2;
        }
      basic_stringstream < char >cmd_stop;
      cmd_stop << "insert into reconfig (s_proxy_id, s_service, s_action) values (" << proxyid << ", '" << service_daemon << "', '" << action_shutdown << "')";
      bool res = q->sendQueryDirect (cmd_stop.str());

      delete q;
      delete conn;

      if (res)
        return 0;

      return 2;
    }

  SAMSUserList::useConnection (conn);
  LocalNetworks::useConnection (conn);
  //GroupList::useConnection (conn); // not used anywhere
  Proxy::useConnection (conn);
  TemplateList::useConnection (conn);
  TimeRangeList::useConnection (conn);
  Logger::useConnection (conn);
  UrlGroupList::useConnection (conn);
  DelayPoolList::useConnection (conn);
  PluginList::useConnection (conn);

  ProcessManager process;

  if (!process.start ("samsdaemon"))
    {
      delete conn;
      exit (1);
    }

  char s_service[15];
  char s_action[10];

  basic_stringstream < char >cmd_check;
  basic_stringstream < char >cmd_del;
  basic_stringstream < char >msg;

  cmd_check << "select distinct s_service, s_action from reconfig where s_proxy_id=" << proxyid;

  SquidLogParser *parser = NULL;
  int seconds_to_parse = 0;
  int seconds_to_reconnect = reconnect_timeout;

  cmdreconfiguresquid = squidbindir + "/squid -k reconfigure";

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
  char str_prev_month[25];
  string backup_fname;
  string shutdown_cmd;

  if (check_interval == 0)
    check_interval = 1;

  while (true)
    {
      looptime = (int) difftime(loop_end, loop_start);
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

      loop_start = time (NULL);
      time_now = localtime (&loop_start);

      if (seconds_to_reconnect <= 0)
        {
          DEBUG (DEBUG_DAEMON, "Reconnecting to database");

          if (query)
            {
              delete query;
              query = NULL;
            }
          if (query2)
            {
              delete query2;
              query2 = NULL;
            }

          conn->disconnect();

          if (!conn->connect ())
            {
              seconds_to_reconnect = 60;
              loop_end = time (NULL);
              continue;
            }

          seconds_to_reconnect = reconnect_timeout;
        }

      if (conn->isConnected () && (!query || !query2))
        {
          if (!query)
            conn->newQuery (query);

          if (!query2)
            conn->newQuery (query2);

          if (!query || !query2)
          {
            loop_end = time (NULL);
            continue;
          }

          if (!query->bindCol (1, DBQuery::T_CHAR, s_service, sizeof (s_service)))
            {
              delete query;
              query = NULL;
              loop_end = time (NULL);
              continue;
            }
          if (!query->bindCol (2, DBQuery::T_CHAR, s_action, sizeof (s_action)))
            {
              delete query;
              query = NULL;
              loop_end = time (NULL);
              continue;
            }
        }

      if (!conn->isConnected () || !query || !query2)
        {
          loop_end = time (NULL);
          continue;
        }

      if (!query->sendQueryDirect (cmd_check.str()) )
        {
          delete query;
          delete query2;
          query = NULL;
          query2 = NULL;

          conn->disconnect();

          if (seconds_to_reconnect > 60)
            seconds_to_reconnect = 60;
          DEBUG (DEBUG_DAEMON, "Reconnect in " << seconds_to_reconnect << " second[s]");
          loop_end = time (NULL);
          continue;
        }

      if (parserType == Proxy::PARSE_DISCRET)
        {
          DEBUG (DEBUG_DAEMON, "Process " << squidcachefile << " in " << seconds_to_parse << " second[s]");
        }


      // Если начался новый день, то, возможно, нужно очищать счетчики пользователей
      if (Proxy::needClearCounters() && (time_was.tm_mday != -1) && (time_was.tm_mday != time_now->tm_mday))
        {
          DEBUG (DEBUG_DAEMON, "New day detected");
          DBCleaner *cleaner = NULL;
          DBExporter *exporter = NULL;
          bool need_reconfig = false;
          tpl_ids = TemplateList::getIds ();
          for (i = 0; i < tpl_ids.size (); i++)
            {
              tpl = TemplateList::getTemplate (tpl_ids[i]);
              if (!tpl)
                continue;
              // У шаблона месячный период и начался новый месяц
              // или у шаблона недельный период и начался понедельник
              // или у шаблона суточный период
              // или шаблон имеет нестандартный период, и настал день очистки счетчиков
              if  ( ((tpl->getPeriodType () == Template::PERIOD_MONTH) && (time_now->tm_mday == 1)) ||
                    ((tpl->getPeriodType () == Template::PERIOD_WEEK) && (time_now->tm_wday == 1))  ||
                    (tpl->getPeriodType () == Template::PERIOD_DAY) ||
                    (tpl->getClearDate (time_clear) && (time_now->tm_year == time_clear.tm_year) && (time_now->tm_yday == time_clear.tm_yday))
                  )
                {
                  DEBUG (DEBUG_DAEMON, "Clear counters for template " << tpl_ids[i]);
                  if (!cleaner)
                    cleaner = new DBCleaner ();
                  cleaner->setTemplateFilter (tpl_ids[i]);
                  cleaner->clearCounters ();
                  tpl->adjustClearDate ();
                  need_reconfig = true;
                }
            }

          TemplateList::saveClearDates ();

          if (need_reconfig)
            reconfigureSQUID ();

          // Если начался новый месяц, то сбрасываем логи в файл и очищаем старый кеш
          if (time_now->tm_mday == 1)
            {
              if (!exporter)
                exporter = new DBExporter ();

              strftime (str_prev_month, sizeof (str_prev_month), "%Y-%m-01,%Y-%m-%d", &time_was);
              exporter->setDateFilter (str_prev_month);

              strftime (str_prev_month, sizeof (str_prev_month), "%Y-%m", &time_was);
              backup_fname = PKGDATADIR;
              backup_fname += "/backup/samscache.";
              backup_fname += str_prev_month;
              backup_fname += ".log";
              exporter->exportToFile (backup_fname);

              if (!cleaner)
                cleaner = new DBCleaner ();
              cleaner->clearOldCache (Proxy::getCacheAge ());
            }
          if (cleaner)
            delete cleaner;
          if (exporter)
            delete exporter;
          cleaner = NULL;
          exporter = NULL;
        }

      memcpy (&time_was, time_now, sizeof (struct tm));

      // обрабатываем команды, поступившие извне (если они есть)
      bool has_cmd;
      while ( (has_cmd=query->fetch ()) )
        {
          DEBUG (DEBUG_DAEMON, "Has got some command");

          if (s_service == service_proxy && s_action == action_shutdown)
            {
              cmd_del.str("");
              cmd_del << "delete from reconfig where s_proxy_id=" << proxyid;
              cmd_del << " and s_service='" << service_proxy << "'";
              cmd_del << " and s_action='" << action_shutdown << "'";
              if (!query2->sendQueryDirect (cmd_del.str()))
                continue;

              DEBUG (DEBUG_DAEMON, "Shutdown proxy server");
              Logger::addLog (Logger::LK_DAEMON, "Got request to execute " + shutdown_cmd);

              shutdown_cmd = SamsConfig::getString (defSHUTDOWNCMD, err);
              if (err != ERR_OK)
                {
                  ERROR (defSHUTDOWNCMD << " not found in config file.");
                  continue;
                }
              int ret = system (shutdown_cmd.c_str ());
              if (ret == -1)
                {
                  ERROR (" Unable to execute " << shutdown_cmd);
                  continue;
                }
            }
          if (s_service == service_daemon && s_action == action_shutdown)
            {
              cmd_del.str("");
              cmd_del << "delete from reconfig where s_proxy_id=" << proxyid;
              cmd_del << " and s_service='" << service_daemon << "'";
              cmd_del << " and s_action='" << action_shutdown << "'";
              if (!query2->sendQueryDirect (cmd_del.str()))
                continue;

              DEBUG (DEBUG_DAEMON, "Shutdown");

              process.stop();
              Proxy::destroy();
              LocalNetworks::destroy ();
              SAMSUserList::destroy ();
              TemplateList::destroy ();
              UrlGroupList::destroy ();
              PluginList::destroy ();
              Logger::destroy (); // всегда уничтожаем его последним
              delete conn;
              exit(0);
            }
          if (s_service == service_daemon && s_action == action_reload)
            {
              cmd_del.str ("");
              cmd_del << "delete from reconfig where s_proxy_id=" << proxyid;
              cmd_del << " and s_service='" << service_daemon << "'";
              cmd_del << " and s_action='" << action_reload << "'";
              if (!query2->sendQueryDirect (cmd_del.str()))
                continue;

              Logger::addLog (Logger::LK_DAEMON, "Got request to reload daemon");

              reload (-1);
            }
          if (s_service == service_squid && s_action == action_reconfig)
            {
              cmd_del.str ("");
              cmd_del << "delete from reconfig where s_proxy_id=" << proxyid;
              cmd_del << " and s_service='" << service_squid << "'";
              cmd_del << " and s_action='" << action_reconfig << "'";
              if (!query2->sendQueryDirect (cmd_del.str()))
                continue;

              Logger::addLog (Logger::LK_DAEMON, "Got request to reconfigure SQUID");

              reconfigureSQUID ();
            }
          if (s_service == service_dbase && s_action == action_export)
            {
              cmd_del.str ("");
              cmd_del << "delete from reconfig where s_proxy_id=" << proxyid;
              cmd_del << " and s_service='" << service_dbase << "'";
              cmd_del << " and s_action='" << action_export << "'";
              if (!query2->sendQueryDirect (cmd_del.str()))
                continue;

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
          PluginList::updateInfo ();
          seconds_to_parse = (60 * steptime);
        }

      loop_end = time (NULL);
    }

}
