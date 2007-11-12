#include <unistd.h>
#include <getopt.h>


#include "debug.h"
#include "samsconfig.h"
#include "samsfile.h"
#include "tools.h"
#include "samsdb.h"
#include "samsuser.h"
#include "samshosts.h"
#include "logparser.h"

/*!
 *  Выводит список опций командной строки с кратким описанием
 */
void usage ()
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
void version ()
{
  //cout << "samsparser " << VERSION << endl;
  cout << "samsparser VERSION" << endl;
  cout << "Written by anonymous." << endl;
  cout << endl;
  cout << "This program comes with NO WARRANTY; not even for MERCHANTABILITY" << endl;
  cout << "or FITNESS FOR A PARTICULAR PURPOSE." << endl;
}


int main (int argc, char *argv[])
{
  string cmd = "";
  int parse_errors = 0;
  int c;
  int err;
  uint dbglevel;
  string optname = "";
  DB db;
  DBQuery query (&db);

  // Сначала прочитаем конфигурацию, параметры командной строки
  // имеют приоритет, потому анализируются позже
  cfgRead ("/etc/sams.conf");

  dbglevel = cfgGetInt (SAMSDEBUG, err);

  if (err == ERR_OK)
    dbgSetLevel (dbglevel);


  static struct option long_options[] = {
    // Commands
    {"help", 1, 0, 'h'},        // Show help screen
    {"version", 0, 0, 'V'},     // Show version
    // General options
    {"verbose", 0, 0, 'v'},     // Print more informaion
    {"debug", 1, 0, 'd'},       // Print lots of debugging messages
    {0, 0, 0, 0}
  };

  while (1)
    {
//      int this_option_optind = optind ? optind : 1;
      int option_index = 0;

      c = getopt_long (argc, argv, "hVvd:", long_options, &option_index);
      if (c == -1)              // no more options
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
          dbgSetVerbose (true);
          break;
        case 'd':
          if (sscanf (optarg, "%d", &dbglevel) != 1)
            dbglevel = 0;
          dbgSetLevel (dbglevel);
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

  string datasource = cfgGetString (defDBSOURCE, err);
  string user = cfgGetString (defDBUSER, err);
  string pass = cfgGetString (defDBPASSWORD, err);

  string squidlogdir = cfgGetString (defSQUIDLOGDIR, err);
  string squidcachefile = cfgGetString (defSQUIDCACHEFILE, err);

  if (squidlogdir.empty () || squidcachefile.empty ())
    {
      ERROR ("Either " << defSQUIDLOGDIR << " or " << defSQUIDCACHEFILE << " not defined. Check SAMS config file.");
      exit (1);
    }

  if (datasource.empty ())
    {
      ERROR ("No datasource defined");
      exit (1);
    }


  if (datasource.empty ())
    {
      ERROR ("No datasource defined");
      exit (1);
    }

  if (db.Connect (datasource, user, pass) != true)
    {
      exit (1);
    }

  Config cfg;
  cfg.Read (&db);



/*
// Проверка различных видов url. Общий формат записи вот таой:
// [protocol://][user[@password]:]<canonical.name.dom|ip.address>[:port][/path]
// вроде ничего не пропустил :)

 std::vector<string> testUrl;
 testUrl.push_back("www.domain.com");
 testUrl.push_back("www.domain.com:3128");
 testUrl.push_back("www.domain.com:3128/path/to/something");
 testUrl.push_back("ftp://www.domain.com");
 testUrl.push_back("http://www.domain.com:3128");
 testUrl.push_back("smb://www.domain.com:3128/path/to/something");
 testUrl.push_back("user:www.domain.com");
 testUrl.push_back("user:www.domain.com:3128");
 testUrl.push_back("user:www.domain.com:3128/path/to/something");
 testUrl.push_back("ftp://user:www.domain.com");
 testUrl.push_back("http://user:www.domain.com:3128");
 testUrl.push_back("smb://user:www.domain.com:3128/path/to/something");
 testUrl.push_back("user@password:www.domain.com");
 testUrl.push_back("user@password:www.domain.com:3128");
 testUrl.push_back("user@password:www.domain.com:3128/path/to/something");
 testUrl.push_back("ftp://user@password:www.domain.com");
 testUrl.push_back("http://user@password:www.domain.com:3128");
 testUrl.push_back("smb://user@password:www.domain.com:3128/path/to/something");

  std::vector<string>::iterator it;
  Url *u = new Url();
  for(it=testUrl.begin(); it != testUrl.end(); it++)
    {
      INFO("Testing " << (*it));
      u->setUrl( (*it) );
      INFO("proto:    " << u->getProto());
      INFO("user:     " << u->getUser());
      INFO("password: " << u->getPass());
      INFO("address:  " << u->getAddress());
      INFO("port:     " << u->getPort());
      INFO("path:     " << u->getPath());
      INFO("");
      INFO("");
    }
*/













  Users users;
  users.Read (&db);
  users.Print ();

  LocalNets local;
  local.Read (&db);
  local.Print ();

  FILE *finp;
  string access_log_path = squidlogdir + "/" + squidcachefile;
  finp = fopen (access_log_path.c_str (), "r");
  if (finp == NULL)
    {
      ERROR ("Cannot open input file");
      exit (1);
    }

  string line;
  char buf[1024];
  SquidLogLine sll;
  SAMSuser *usr;
  while (!feof (finp))
    {
      if (fgets (&buf[0], 1023, finp) == NULL)
        continue;
      line = buf;
      if (sll.setLine (line) != true)
        continue;

      INFO ("-----------------------------------");
      INFO ("Parsing " << line);
      usr = users.findByIdent (sll.getIdent ());

      if (usr == NULL)
        continue;

      INFO ("Found user " << usr->asString ());

      if (local.isLocal (sll.getUrl ()))
        {
          INFO ("Consider url is local");
          continue;
        }

      switch (sll.getCacheResult ())
        {
        case CR_UNKNOWN:
          ERROR ("Unknown cache result");
          break;
        case TCP_DENIED:
        case UDP_DENIED:
          break;
        case TCP_HIT:
        case TCP_MEM_HIT:
        case TCP_REFRESH_HIT:
        case TCP_REF_FAIL_HIT:
        case TCP_IMS_HIT:
        case UDP_HIT:
          usr->addHit (sll.getSize ());
        case TCP_NEGATIVE_HIT:
        case TCP_MISS:
        case TCP_REFRESH_MISS:
        case TCP_CLIENT_REFRESH:
        case TCP_CLIENT_REFRESH_MISS:
        case TCP_IMS_MISS:
        case TCP_SWAPFAIL:
        case UDP_HIT_OBJ:
        case UDP_MISS:
        case UDP_INVALID:
        case UDP_RELOADING:
        case ERR_CLIENT_ABORT:
        case ERR_NO_CLIENTS:
        case ERR_READ_ERROR:
        case ERR_CONNECT_FAIL:
          usr->addSize (sll.getSize ());
          break;
        }

    }

  INFO ("End of parsing");

  users.saveToDB (&db);

  users.Print ();


  db.Disconnect ();
}
