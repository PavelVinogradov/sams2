#include <unistd.h>
#include <getopt.h>


#include "debug.h"
#include "samsconfig.h"
#include "samsfile.h"
#include "tools.h"
#include "samsdb.h"


/*!
 *  Выводит список опций командной строки с кратким описанием
 */
void
usage ()
{
  cout << endl;
  cout << "NAME" << endl;
  cout << "    samsparser - parse squid log file and update MySQL database." << endl;
  cout << endl;
  cout << "SYNOPSIS" << endl;
  cout << "    samsparser [COMMAND] [OPTION]..." << endl;
  cout << endl;
  cout << "COMMANDS" << endl;
  cout << "    Only one command allowed at a time." << endl;
  cout << endl;
  cout << "    -h, --help" << endl;
  cout << "                Show this help screen and exit." << endl;
  cout << "    -V, --version" << endl;
  cout << "                Print program version number and exit." << endl;
  cout << endl;
  cout << "Mandatory arguments to long options are mandatory for short options too." << endl;
  cout << "The following options are available:" << endl;
  cout << endl;
  cout << "GENERAL OPTIONS" << endl;
  cout << "    -v, --verbose" << endl;
  cout << "                Produce more output." << endl;
  cout << "    -d, --debug=LEVEL" << endl;
  cout << "                Produce lots of debugging information depend on LEVEL." << endl;
  cout << "                For more information about possible LEVEL values refer to developer." << endl;
}

/*!
 *  Выводит версию программы и немного информации
 */
void
version ()
{
  //cout << "samsparser " << VERSION << endl;
  cout << "samsparser VERSION" << endl;
  cout << "Written by anonymous." << endl;
  cout << endl;
  cout << "This program comes with NO WARRANTY; not even for MERCHANTABILITY"
    << endl;
  cout << "or FITNESS FOR A PARTICULAR PURPOSE." << endl;
}


int
main (int argc, char *argv[])
{
  string cmd = "";
  int parse_errors = 0;
  int c;
  int err;
  uint dbglevel;
  string optname = "";
  DB db;


  // Сначала прочитаем конфигурацию, параметры командной строки
  // имеют приоритет, потому анализируются позже
  cfgRead("/etc/sams.conf");

  dbglevel = cfgGetInt(SAMSDEBUG, err);

  if (err == ERR_OK)
    dbgSetLevel(dbglevel);


  static struct option long_options[] = {
    // Commands
    {"help", 1, 0, 'h'},	// Show help screen
    {"version", 0, 0, 'V'},	// Show version
    // General options
    {"verbose", 0, 0, 'v'},	// Print more informaion
    {"debug", 1, 0, 'd'},	// Print lots of debugging messages
    {0, 0, 0, 0}
  };

  while (1)
    {
      int this_option_optind = optind ? optind : 1;
      int option_index = 0;

      c = getopt_long (argc, argv, "hVvd:", long_options, &option_index);
      if (c == -1)		// no more options
	break;
      switch (c)
	{
	case 0:
	  optname = long_options[option_index].name;
	  DEBUG (DEBUG2, "option: " << optname << "=" << optarg);
	  break;
	case 'h':
	  DEBUG (DEBUG2, "option: --help");
	  usage ();
	  exit (0);
	  break;
	case 'V':
	  DEBUG (DEBUG2, "option: --version");
	  version ();
	  exit (0);
	  break;
	case 'v':
	  DEBUG (DEBUG2, "option: --verbose");
	  dbgSetVerbose(true);
	  break;
	case 'd':
	  if (sscanf (optarg, "%d", &dbglevel) != 1)
	    dbglevel = 0;
          dbgSetLevel(dbglevel);
	  DEBUG (DEBUG2, "option: --debug=" << dbglevel);
	  break;
	case '?':
	  break;

	default:
	  printf ("?? getopt returned character code 0%o ??\n", c);
	}
    }

  if (parse_errors > 0)
    {
      usage ();
      exit (parse_errors);
    }



  /* Далее идут просто примеры, тесты различных функций
   * никакой смысловой нагрузки они не несут
   * и не соответствуют никакому стилю
   */


  db.Connect("sams", "sams", "qwerty");

  char username[30];
  char userpass[70];
  long accessid;
  db.AddCol(1, SQL_C_CHAR, &username[0], 30);
  db.AddCol(2, SQL_C_CHAR, &userpass[0], 70);
  db.AddCol(3, SQL_C_LONG, &accessid,    15);

  if (db.SendQuery("SELECT user, pass, access from passwd"))
  {
    INFO("Rows: " << db.RowsCount());
    while (db.Fetch() != SQL_NO_DATA)
    {
      INFO("user: " << username);
      INFO("pass: " << userpass);
      INFO("access: " << accessid);
    }
  }


  
  db.Disconnect();

}
