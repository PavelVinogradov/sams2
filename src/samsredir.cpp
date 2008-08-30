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
#include <getopt.h>
#include <stdlib.h>

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

#include "samsconfig.h"
#include "debug.h"
#include "processmanager.h"
#include "samsusers.h"
#include "samsuser.h"
#include "proxy.h"
#include "localnetworks.h"
#include "groups.h"
#include "templates.h"
#include "template.h"
#include "tools.h"

/**
 *  Выводит список опций командной строки с кратким описанием
 */
void usage ()
{
  cout << endl;
  cout << "NAME" << endl;
  cout << "    samsredir - redirector." << endl;
  cout << endl;
  cout << "SYNOPSIS" << endl;
  cout << "    samsredir [COMMAND] [OPTION]..." << endl;
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
  cout << "samsredir " << VERSION << endl;
  cout << "Written by anonymous." << endl;
  cout << endl;
  cout << "This program comes with NO WARRANTY; not even for MERCHANTABILITY" << endl;
  cout << "or FITNESS FOR A PARTICULAR PURPOSE." << endl;
}

int main (int argc, char *argv[])
{
  int parse_errors = 0;
  int c;
  uint dbglevel;
  string optname = "";
  bool must_fork = true;
  bool use_must_fork = false;

  // Сначала прочитаем конфигурацию, параметры командной строки
  // имеют приоритет, потому анализируются позже
  if (!SamsConfig::reload ())
    {
      return false;
    }

  Logger::setSender("samsredir");

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


  DBConn *conn = NULL;

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

  SAMSUsers::reload ();
  LocalNetworks::reload ();
  Groups::reload ();
  Proxy::reload ();
  Templates::reload ();

  // select t.s_shablon_id, r.s_type, u.s_url
  // from shablon t, sconfig t_r, redirect r, url u
  // where t_r.s_shablon_id=t.s_shablon_id
  //       and t_r.s_redirect_id=r.s_redirect_id
  //       and u.s_redirect_id=r.s_redirect_id;
  delete conn;
  conn = NULL;

  ProcessManager process;

  if (!process.start ("samsdaemon"))
    {
      exit (0);
    }

  char line[2048];
  vector < string > fields;
  vector < string > source;
  SAMSUser *usr;
  Template *tpl;
  while (1)
    {
      cin.getline(line, sizeof(line));

      // входная строка имеет вид
      // URL ip-address/fqdn ident method
      Split (line, "\n\t ", fields);

      if (fields.size () == 0)
        break;

      DEBUG(DEBUG_REDIR, "[" << __FUNCTION__ << "] Input: " << line);

      Split (fields[1], "/", source);

      // Мы незнаем что такое попалось, но на всякий случай ничего менять не будем
      if (fields.size () != 4)
        {
          DEBUG(DEBUG_REDIR, "[" << __FUNCTION__ << "] Invalid fields count: " << fields.size() << endl);
          cout << endl;
          continue;
        }

      // url считается локальным и неважно какой пользователь обратился, разрешаем доступ
      if (LocalNetworks::isLocalUrl(fields[0]))
        {
          DEBUG(DEBUG_REDIR, "[" << __FUNCTION__ << "] Url is local" << endl);
          cout << endl;
          continue;
        }

      usr = Proxy::findUser (source[0], fields[2]);

      // Пользователь не найден, блокируем доступ
      if (!usr)
        {
          DEBUG(DEBUG_REDIR, "[" << __FUNCTION__ << "] User not found" << endl);
          if (fields[2] != "-")
            cout << Proxy::getRedirectAddr () << "/blocked.php?action=usernotfound&id=" << fields[2] << endl;
          else
            cout << Proxy::getRedirectAddr () << "/blocked.php?action=usernotfound&id=" << source[0] << endl;
          continue;
        }

      // нарушена целостность БД (отсутствует шаблон пользователя), блокируем доступ
      tpl = Templates::getTemplate (usr->getShablonId ());
      if (!tpl)
        {
          DEBUG(DEBUG_REDIR, "[" << __FUNCTION__ << "] Nothing to do without template" << endl);
          cout << Proxy::getRedirectAddr () << "/blocked.php?action=templatenotfound&id=" << *usr << endl;
          continue;
        }

      DEBUG(DEBUG_REDIR, "[" << __FUNCTION__ << "] Found user: " << *usr);

      // Если url по каким-то причинам не разрешен, блокируем доступ
      if (!tpl->isUrlAllowed (fields[0]))
        {
          cout << Proxy::getRedirectAddr () << "/blocked.php?action=urldenied&id=" << *usr << endl;
          continue;
        }

      // Все проверки пройдены успешно, разрешаем доступ
      DEBUG(DEBUG_REDIR, "[" << __FUNCTION__ << "] Access granted" << endl);
      cout << endl;
    }

  return 0;
}
