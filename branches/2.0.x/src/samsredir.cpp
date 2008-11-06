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

#include "dbconn.h"
#include "samsconfig.h"
#include "debug.h"
#include "samsusers.h"
#include "samsuser.h"
#include "proxy.h"
#include "localnetworks.h"
#include "groups.h"
#include "templates.h"
#include "template.h"
#include "urlgrouplist.h"
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
  cout << "    -C, --config=FILE" << endl;
  cout << "                Use config file FILE." << endl;
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
  uint dbglevel = 0;
  string optname = "";
  bool must_fork = true;
  bool use_must_fork = false;
  bool verbose = false;
  string log_engine = "";
  string config_file = SYSCONFDIR;
  config_file += "/sams2.conf";

  static struct option long_options[] = {
    {"help",     0, 0, 'h'},     // Показывает справку по опциям командной строки и завершает работу
    {"version",  0, 0, 'V'},     // Показывает версию программы и завершает работу
    {"verbose",  0, 0, 'v'},     // Устанавливает режим многословности
    {"debug",    1, 0, 'd'},     // Устанавливает уровень отладки
    {"fork",     0, 0, 'f'},     // Запускать в фоновом режиме
    {"no-fork",  0, 0, 'F'},     // Не запускать в фоновом режиме
    {"logger",   1, 0, 'l'},     // Устанавливает движок вывода сообщений
    {"config",   1, 0, 'C'},     // Использовать альтернативный конфигурационный файл
    {0, 0, 0, 0}
  };

  while (1)
    {
      int option_index = 0;

      c = getopt_long (argc, argv, "hVvd:fFt:l:C:", long_options, &option_index);
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
        case 'v':
          verbose = true;
          break;
        case 'd':
          if (sscanf (optarg, "%d", &dbglevel) != 1)
            dbglevel = 0;
          break;
        case 'f':
          must_fork = true;
          use_must_fork = true;
          break;
        case 'F':
          must_fork = false;
          use_must_fork = true;
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

  Logger::setSender("samsredir");
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


/*
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
*/


  DBConn *conn = NULL;

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

  SAMSUsers::useConnection (conn);
  LocalNetworks::useConnection (conn);
  Groups::useConnection (conn);
  Proxy::useConnection (conn);
  Templates::useConnection (conn);
  UrlGroupList::useConnection (conn);

  SAMSUsers::reload ();
  LocalNetworks::reload ();
  Groups::reload ();
  Proxy::reload ();
  Templates::reload ();
  UrlGroupList::reload();

  // select t.s_shablon_id, r.s_type, u.s_url
  // from shablon t, sconfig t_r, redirect r, url u
  // where t_r.s_shablon_id=t.s_shablon_id
  //       and t_r.s_redirect_id=r.s_redirect_id
  //       and u.s_redirect_id=r.s_redirect_id;
  //delete conn;
  //conn = NULL;

//  basic_stringstream < char >mess;

//  mess << "Started with pid " << pid << ".";

//  Logger::addLog(Logger::LK_DAEMON, mess.str());


  char line[2048];
  vector < string > fields;
  vector < string > source;
  SAMSUser *usr = NULL;
  Template *tpl = NULL;
  while (true)
    {
      cin.getline(line, sizeof(line));

      // входная строка имеет вид
      // URL ip-address/fqdn ident method
      Split (line, "\n\t ", fields);

      if (fields.size () == 0)
        break;

      INFO ("Input: " << line);

      Split (fields[1], "/", source);

      // Мы незнаем что такое попалось, но на всякий случай ничего менять не будем
      if (fields.size () < 4)
        {
          INFO ("Invalid fields count: " << fields.size());
          INFO ("Output: " << line);
          cout << line << endl << flush;
          continue;
        }

      // url считается локальным и неважно какой пользователь обратился, разрешаем доступ
      if (LocalNetworks::isLocalUrl(fields[0]))
        {
          INFO ("Url is local");
          INFO ("Output: " << line);
	  cout << line << endl << flush;
          continue;
        }

      usr = Proxy::findUser (source[0], fields[2]);

      // Пользователь не найден, блокируем доступ
      if (!usr)
        {
          INFO ("User not found");
          if (fields[2] != "-")
            {
              INFO ("Output: " << Proxy::getDenyAddr () << "/blocked.php?action=usernotfound&id=" << fields[2] << " " << fields[1] << " " << fields[2] << " " << fields[3]);
              cout << Proxy::getDenyAddr () << "/blocked.php?action=usernotfound&id=" << fields[2];
              cout << " " << fields[1] << " " << fields[2] << " " << fields[3] << endl << flush;
            }
          else
            {
              INFO ("Output: " << Proxy::getDenyAddr () << "/blocked.php?action=usernotfound&id=" << source[0] << " " << fields[1] << " " << fields[2] << " " << fields[3]);
              cout << Proxy::getDenyAddr () << "/blocked.php?action=usernotfound&id=" << source[0];
              cout << " " << fields[1] << " " << fields[2] << " " << fields[3] << endl << flush;
            }
          continue;
        }

      DEBUG(DEBUG_REDIR, "[" << __FUNCTION__ << "] Found user: " << *usr);

      if ( usr->getEnabled () != SAMSUser::STAT_ACTIVE )
        {
          INFO ("User not active (disabled or blocked)");
          INFO ("Output: " << Proxy::getDenyAddr () << "/blocked.php?action=userdisabled&id=" << *usr << " " << fields[1] << " " << fields[2] << " " << fields[3]);
          cout << Proxy::getDenyAddr () << "/blocked.php?action=userdisabled&id=" << *usr;
          cout << " " << fields[1] << " " << fields[2] << " " << fields[3] << endl << flush;
          continue;
        }

      // нарушена целостность БД (отсутствует шаблон пользователя), блокируем доступ
      tpl = Templates::getTemplate (usr->getShablonId ());
      if (!tpl)
        {
          INFO ("Nothing to do without template");
          INFO ("Output: " << Proxy::getDenyAddr () << "/blocked.php?action=templatenotfound&id=" << *usr << " " << fields[1] << " " << fields[2] << " " << fields[3]);
          cout << Proxy::getDenyAddr () << "/blocked.php?action=templatenotfound&id=" << *usr;
          cout << " " << fields[1] << " " << fields[2] << " " << fields[3] << endl << flush;
          continue;
        }

      // Если url существует в белом списке, разрешаем доступ
      if ( tpl->isUrlWhitelisted (fields[0]) )
        {
          INFO ("In white list");
          INFO ("Output: " << line);
	  cout << line << endl << flush;
          continue;
        }

      // Если url существует в черном списке, блокируем доступ
      if ( tpl->isUrlBlacklisted (fields[0]) )
        {
          INFO ("In black list");
          INFO ("Output: " << Proxy::getDenyAddr () << "/blocked.php?action=urldenied&id=" << *usr << " " << fields[1] << " " << fields[2] << " " << fields[3]);
          cout << Proxy::getDenyAddr () << "/blocked.php?action=urldenied&id=" << *usr;
          cout << " " << fields[1] << " " << fields[2] << " " << fields[3] << endl << flush;
          continue;
        }

      // Если url существует в списке регулярных выражений, блокируем доступ
      if ( tpl->isUrlMatchRegex (fields[0]) )
        {
          INFO ("In regular expression list");
          INFO ("Output: " << Proxy::getDenyAddr () << "/blocked.php?action=urldenied&id=" << *usr << " " << fields[1] << " " << fields[2] << " " << fields[3]);
          cout << Proxy::getDenyAddr () << "/blocked.php?action=urldenied&id=" << *usr;
          cout << " " << fields[1] << " " << fields[2] << " " << fields[3] << endl << flush;
          continue;
        }

      // Если url в текущее время не разрешен, блокируем доступ
      if ( tpl->isTimeDenied (fields[0]) )
        {
          INFO ("Denied due to time restrictions");
          INFO ("Output: " << Proxy::getDenyAddr () << "/blocked.php?action=timedenied&id=" << *usr << " " << fields[1] << " " << fields[2] << " " << fields[3]);
          cout << Proxy::getDenyAddr () << "/blocked.php?action=timedenied&id=" << *usr;
          cout << " " << fields[1] << " " << fields[2] << " " << fields[3] << endl << flush;
          continue;
        }

      if ( tpl->getAllDeny () )
        {
          INFO ("Denied to all and not whitelisted");
          INFO ("Output: " << Proxy::getDenyAddr () << "/blocked.php?action=urldenied&id=" << *usr << " " << fields[1] << " " << fields[2] << " " << fields[3]);
          cout << Proxy::getDenyAddr () << "/blocked.php?action=urldenied&id=" << *usr;
          cout << " " << fields[1] << " " << fields[2] << " " << fields[3] << endl << flush;
          continue;
        }

      // Все проверки пройдены успешно, разрешаем доступ
      INFO ("Access granted");
      INFO ("Output: " << line);
      cout << line << endl << flush;
    }

  return 0;
}
