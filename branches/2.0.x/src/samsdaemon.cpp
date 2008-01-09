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

#include "config.h"
#include "configmake.h"

#include "debug.h"
#include "samsconfig.h"
#include "processmanager.h"

#include "global.h"

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
  cout << "    Some commands may be used at one runtime." << endl;
  cout << endl;
  cout << "    -h, --help" << endl;
  cout << "                Show this help screen and exit." << endl;
  cout << "    -V, --version" << endl;
  cout << "                Print program version number and exit." << endl;
  cout << endl;
  cout << "Mandatory arguments to long options are mandatory for short options too." << endl;
  cout << "The following options are available:" << endl;
  cout << endl;
  cout << "OPTIONS" << endl;
  cout << "    -v, --verbose" << endl;
  cout << "                Produce more output." << endl;
  cout << "    -d, --debug=LEVEL" << endl;
  cout << "                Produce lots of debugging information depend on LEVEL." << endl;
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


/** @todo Выбирать путь к pid файлу из опций configure
*/
int main (int argc, char *argv[])
{
//  int commands;
  int parse_errors = 0;
  int c;
  int err;
  uint dbglevel;
  string optname = "";

  config = new SamsConfig ();
  logger = new Logger ();

  // Сначала прочитаем конфигурацию, параметры командной строки
  // имеют приоритет, потому анализируются позже
  config->readFile ();
  config->readDB ();

  dbglevel = config->getInt (defDEBUG, err);

  if (err == ERR_OK)
    logger->setDebugLevel (dbglevel);

  static struct option long_options[] = {
    {"help", 0, 0, 'h'},        // Показывает справку по опциям командной строки и завершает работу
    {"version", 0, 0, 'V'},     // Показывает версию программы и завершает работу
    {"verbose", 0, 0, 'v'},     // Устанавливает режим многословности
    {"debug", 1, 0, 'd'},       // Устанавливает уровень отладки
    {"logger", 1, 0, 'l'},      // Устанавливает движок вывода сообщений
    {0, 0, 0, 0}
  };

  while (1)
    {
      int option_index = 0;

      c = getopt_long (argc, argv, "hVvd:l:", long_options, &option_index);
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

  int proxyid = config->getInt (defPROXYID, err);
  if (err != ERR_OK)
    {
      ERROR ("No proxyid defined. Check " << defPROXYID << " in config file.");
      exit (1);
    }

  string dbsrc = config->getString (defDBSOURCE, err);
  string dbuser = config->getString (defDBUSER, err);
  string dbpass = config->getString (defDBPASSWORD, err);
  if (dbsrc.empty ())
    {
      ERROR ("No datasource defined. Check " << defDBSOURCE << " in config file.");
      exit (1);
    }

  int sleeptime = config->getInt (defSLEEPTIME, err);
  if (err != ERR_OK)
    {
      ERROR ("Cannot get sleep time for daemon. See debugging messages for more details.");
      exit (1);
    }

  int steptime = config->getInt (defDAEMONSTEP, err);
  if (err != ERR_OK)
    {
      ERROR ("Cannot get step time for daemon. See debugging messages for more details.");
      exit (1);
    }

  ProcessManager process;

  if (!process.start ("samsdaemon"))
    {
      exit (2);
    }

//  struct sigaction sigchld_action;
//  memset (&sigchld_action, 0, sizeof (sigchld_action));
//  sigchld_action.sa_handler = &clean_up_child_process;
//  sigaction (SIGCHLD, &sigchld_action, NULL);

  basic_stringstream < char >cmd_check;
  basic_stringstream < char >cmd_del;

  cmd_check << "select s_service, s_action from reconfig where s_proxy_id=" << proxyid << " and service=? and action=?";
  cmd_del << "delete from reconfig where s_proxy_id=" << proxyid << " and service=? and action=?";

  int seconds_left = 0;
  while (true)
    {
      if (seconds_left < 0)
        seconds_left = 0;

      DEBUG (DEBUG_DAEMON, "Countdown: " << seconds_left);


      if (seconds_left == 0)
        {
          DEBUG (DEBUG_DAEMON, "Processing access.log ...");
          seconds_left = (60 * steptime);
        }

      sleep (sleeptime);
      seconds_left -= sleeptime;
    }


  delete config;
}
