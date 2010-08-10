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
#include <stdlib.h>

#include "config.h"
#include "configmake.h"

#include "debug.h"
#include "samsconfig.h"
#include "squidlogparser.h"
#include "dbcleaner.h"
#include "datefilter.h"
#include "userfilter.h"
#include "processmanager.h"
#include "localnetworks.h"
#include "samsuserlist.h"
#include "templatelist.h"
#include "proxy.h"

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
  cout << "    -u, --update" << endl;
  cout << "                Update database (DEFAULT)." << endl;
  cout << "                If --file option is present, this file is used for input, instead of real log file." << endl;
  cout << "    -e, --export" << endl;
  cout << "                Export cache from a database to a file." << endl;
  cout << "                If --file option is not present, squid.log is used for output." << endl;
  cout << "    -c, --clear" << endl;
  cout << "                Clear user's counters." << endl;
  cout << "    -t, --truncate" << endl;
  cout << "                Truncate cache in database." << endl;
  cout << endl;
  cout << "Mandatory arguments to long options are mandatory for short options too." << endl;
  cout << "The following options are available:" << endl;
  cout << endl;
  cout << "OPTIONS" << endl;
  cout << "    -f, --file=FILE" << endl;
  cout << "                Use FILE for update or export (DEFAULT: squid.log)." << endl;
  cout << "    -U, --user=LIST" << endl;
  cout << "                Perform all operations for listed users only (DEFAULT: all)." << endl;
  cout << "                LIST is a list of users, separated by comma (e.g. DOMAIN+user1,192.168.1.1,user2)." << endl;
  cout << "    -D, --date=DATE_INTERVAL" << endl;
  cout << "                Perform all operations for listed date interval only (DEFAULT: all)." << endl;
  cout << "                DATE_INTERVAL is two dates, separated by comma." << endl;
  cout << "                The date specification is YYYY-MM-DD" << endl;
  cout << "                E.g. -D 2007-01-01,2007-01-31." << endl;
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
  cout << "    -C, --config=FILE" << endl;
  cout << "                Use config file FILE." << endl;
  cout << "    -w, --wait-myself" << endl;
  cout << "                If already running program found, do not exit immediatly," << endl;
  cout << "                but wait until it ends and finish the task." << endl;
  cout << endl;
  cout << "DESCRIPTION" << endl;
  cout << "    If no one command is listed, program updates database (--update)." << endl;
  cout << "    If more then one command is listed, program performs all operations in the following order:" << endl;
  cout << "      1) Update database (--update command)." << endl;
  cout << "      2) Export cache to a file (--export command)." << endl;
  cout << "      3) Clear user's counters (--clear command)." << endl;
  cout << "      4) Truncate cache in database (--truncate command)." << endl;
  cout << "    For all operations the filters (--user and --date) are applied, if any." << endl;
  cout << endl;
}

/**
 *  Выводит версию программы и немного информации
 */
void version ()
{
  cout << "samsparser " << VERSION << endl;
  cout << "Written by anonymous." << endl;
  cout << endl;
  cout << "This program comes with NO WARRANTY; not even for MERCHANTABILITY" << endl;
  cout << "or FITNESS FOR A PARTICULAR PURPOSE." << endl;
}

#define CMD_NONE      0x0
#define CMD_UPDATE    0x1
#define CMD_EXPORT    0x2
#define CMD_CLEAR     0x4
#define CMD_TRUNCATE  0x8

/** @todo Выбирать путь к pid файлу из опций configure
*/
int main (int argc, char *argv[])
{
  int commands;
  int parse_errors = 0;
  int c;
  int err;
  uint dbglevel = 0;
  bool wait_myself = false;
  string optname = "";
  string logfilename = "";
  DateFilter *dateFilter = NULL;
  UserFilter *userFilter = NULL;
  bool verbose = false;
  string log_engine = "";
  string config_file = SYSCONFDIR;
  config_file += "/sams2.conf";

  static struct option long_options[] = {
    {"help",        0, 0, 'h'},    // Показывает справку по опциям командной строки и завершает работу
    {"version",     0, 0, 'V'},    // Показывает версию программы и завершает работу
    {"update",      0, 0, 'u'},    // Обновляет кэш в БД и счетчики пользователей
    {"export",      0, 0, 'e'},    // Экспортирует кэш в файл
    {"clear",       0, 0, 'c'},    // Очищает счетчики пользователей
    {"truncate",    0, 0, 't'},    // Очищает кэш в БД
    {"file",        1, 0, 'f'},    // Имя файла для импорта или экспорта
    {"user",        1, 0, 'U'},    // Фильтр по пользователям
    {"date",        1, 0, 'D'},    // Фильтр по датам
    {"verbose",     0, 0, 'v'},    // Устанавливает режим многословности
    {"debug",       1, 0, 'd'},    // Устанавливает уровень отладки
    {"logger",      1, 0, 'l'},    // Устанавливает движок вывода сообщений
    {"config",      1, 0, 'C'},    // Использовать альтернативный конфигурационный файл
    {"wait-myself", 0, 0, 'w'},    // Устанавливает движок вывода сообщений
    {0, 0, 0, 0}
  };

  commands = CMD_NONE;
  while (1)
    {
      int option_index = 0;

      c = getopt_long (argc, argv, "hVuectf:U:D:vd:l:C:w", long_options, &option_index);
      if (c == -1)              // no more options
        break;
      switch (c)
        {
        case 0:
          optname = long_options[option_index].name;
          break;
        case 'h':
          usage ();
          exit (0);
          break;
        case 'V':
          version ();
          exit (0);
          break;
        case 'u':
          commands += CMD_UPDATE;
          break;
        case 'e':
          commands += CMD_EXPORT;
          break;
        case 'c':
          commands += CMD_CLEAR;
          break;
        case 't':
          commands += CMD_TRUNCATE;
          break;
        case 'f':
          logfilename = optarg;
          break;
        case 'U':
          WARNING ("Filter for users is NOT implemented.");
          userFilter = new UserFilter (optarg);
          break;
        case 'D':
          dateFilter = new DateFilter (optarg);
          break;
        case 'v':
          verbose = true;
          break;
        case 'd':
          if (sscanf (optarg, "%d", &dbglevel) != 1)
            dbglevel = 0;
          break;
        case 'l':
          log_engine = optarg;
          break;
        case 'C':
          config_file = optarg;
          break;
        case 'w':
          wait_myself = true;
          break;
        case '?':
          break;
        default:
          printf ("?? getopt return character code 0%o ??\n", c);
        }
    }

  Logger::setSender("samsparser");
  SamsConfig::useFile (config_file);

  // Сначала прочитаем конфигурацию, параметры командной строки
  // имеют приоритет, потому анализируются позже
  SamsConfig::reload ();

  Logger::setEngine (log_engine);
  Logger::setVerbose (verbose);
  Logger::setDebugLevel (dbglevel);

  if (parse_errors > 0)
    {
      usage ();
      exit (parse_errors);
    }

  string squidlogdir = SamsConfig::getString (defSQUIDLOGDIR, err);
  string squidcachefile = SamsConfig::getString (defSQUIDCACHEFILE, err);

  if (squidlogdir.empty () || squidcachefile.empty ())
    {
      ERROR ("Either " << defSQUIDLOGDIR << " or " << defSQUIDCACHEFILE << " not defined. Check config file.");
      exit (1);
    }

  int proxyid = SamsConfig::getInt (defPROXYID, err);
  if (err != ERR_OK)
    {
      ERROR ("No proxyid defined. Check " << defPROXYID << " in config file.");
      exit (1);
    }

  ProcessManager process;

  if (!process.start ("samsparser", wait_myself))
    {
      exit (2);
    }

  SquidLogParser *parser = NULL;
  DBCleaner *cleaner = NULL;

  if ((commands & CMD_CLEAR) == CMD_CLEAR || (commands & CMD_TRUNCATE) == CMD_TRUNCATE)
    {
      cleaner = new DBCleaner ();
      cleaner->setDateFilter (dateFilter);
      cleaner->setUserFilter (userFilter);
    }

  if (commands == CMD_NONE)
    commands += CMD_UPDATE;

  bool parseFromBegin = true;
  while (commands != CMD_NONE)
    {
      if ((commands & CMD_UPDATE) == CMD_UPDATE)
        {
          INFO ("+++ Updating database");
          if (logfilename.empty ())
            {
              logfilename = squidlogdir + "/" + squidcachefile;
              parseFromBegin = false;
            }

          parser = new SquidLogParser (proxyid);

          parser->setDateFilter (dateFilter);
          parser->setUserFilter (userFilter);
          parser->parseFile (logfilename, parseFromBegin);

          delete parser;

          commands -= CMD_UPDATE;
        }
      else if ((commands & CMD_EXPORT) == CMD_EXPORT)
        {
          INFO ("+++ Exporting to the file");
          if (logfilename.empty ())
            logfilename = "squid.log";

          commands -= CMD_EXPORT;
        }
      else if ((commands & CMD_CLEAR) == CMD_CLEAR)
        {
          INFO ("+++ Clearing user's counters");
          cleaner->clearCounters ();
          commands -= CMD_CLEAR;
        }
      else if ((commands & CMD_TRUNCATE) == CMD_TRUNCATE)
        {
          INFO ("+++ Truncating cache in the database");
          cleaner->clearCache ();
          commands -= CMD_TRUNCATE;
        }
      else if (commands != CMD_NONE)
        {
          WARNING ("Unknown command[s] " << commands << " left unprocessed.");
          commands = CMD_NONE;
        }
    }

  if (cleaner != NULL)
    delete cleaner;

  process.stop ();

  LocalNetworks::destroy ();
  TemplateList::destroy ();
  SAMSUserList::destroy ();
  Proxy::destroy ();
  Logger::destroy ();
  SamsConfig::destroy ();
}
