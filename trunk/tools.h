//static char *stripws( char *str );
char *xstrdup(char *str);
void readconf( void );
MYSQL *do_connect(char *host_name,char *user_name, char *password,
                   char *db_name,
		   unsigned int port_num, char *socket_name,
		   unsigned int flags);
void do_disconnect(MYSQL *conn);
int SavePID(int pid, char *filename);
int TestPID(char *filename);
void RmPID(char *filename);
char *url_decode(char *str); 
void AddLog(MYSQL *conn, int level, char *demon, char *str);
int LocalIPAddr(char *url, int *od, int *om);
void str2upper(char *string);
void Str2Lower(char *str);
void freeconf();
int UnlinkFiles(char *path,char *filemask);
int send_mysql_query(MYSQL *conn, char *str);
char *MallocMemory(char *str);

float KBSIZE;
float MBSIZE;
struct local_url {
  char url[50];
  int  ip[6];
  int  mask[6];
  int ipflag;
};

