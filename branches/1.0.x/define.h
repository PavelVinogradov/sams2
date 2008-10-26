#define def_host_name NULL
#define def_user_name NULL
#define def_password NULL
#define def_db_name NULL
#define def_socket_name NULL
#define def_port_num 0
#define BUFFER_SIZE             (16*1024)
#define URL_LEN 50

struct samsconf {
  char *samsdb;     
  char *logdb;
  char *host;
  char *user;
  char *passwd;
  char *logfile;
  char *squidrootdir;
  char *logdir;
  char *samspath;
  char *squidpath;
  char *cachedir;
  char *sglogpath;
  char *sgdbpath;
  char *recode;
  char *rejikpath;
  char *redirpath;
  char *deniedpath;
  char *lang;
  char adminaddr[61];
  char *shutdown;
  int  createpdf;
  int  cachenum;
//  int  clrtraffic;
//  int  clryear;
//  int  clrmonth;
//  int  clrdate;
} conf;

int PRINT,DEBUG,SLEEP,REQUEST,SDELAY,LOGLEVEL;
int IP,NTLM,NCSA,NTLMDOMAIN,FIFO,PACKET,BIGU,BIGD;
int RSAMS,RGUARD,RSQUID,RNONE,RREJIK;
char buf[BUFFER_SIZE];
char *SEPARATOR;



