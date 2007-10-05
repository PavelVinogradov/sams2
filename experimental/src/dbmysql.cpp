#include <unistd.h>

#include "dbmysql.h"
#include "debug.h"

#define NUM_RETRIES    5
#define DELAY_ON_ERROR 3

/*
 * 
 */
MYSQL *
dbMySQLConnect (string host_name, string user_name, string password,
		string db_name, uint port_num, string socket_name, uint flags)
{
  MYSQL *mysql;
  MYSQL *connect;
  int count;

  DEBUG (DEBUG3,
	 "Connecting to a database: " << db_name << "@" << host_name <<
	 " as user " << user_name);
  count = 0;
  while (count < NUM_RETRIES)
    {
      mysql = mysql_init (NULL);
      if (mysql == NULL)
	{
	  WARNING (count << ": mysql_init(). Delay for " << DELAY_ON_ERROR <<
		   " seconds.");
	  sleep (3);
	}
      count++;
    }
  if (mysql == NULL)
    {
      ERROR ("mysql_init()");
      return (NULL);
    }
  count = 0;
  while (count < NUM_RETRIES)
    {
      connect =
	mysql_real_connect (mysql, host_name.c_str (), user_name.c_str (),
			    password.c_str (), db_name.c_str (), 0, NULL, 0);
      if (connect == NULL)
	{
	  WARNING (count << ": mysql_init(). Delay for " << DELAY_ON_ERROR <<
		   " seconds.");
	  sleep (3);
	}
      else
	{
          DEBUG (DEBUG3, "Connection established [ptr=" << connect << "]");
	  return (connect);
	}
      count++;
    }

  ERROR ("mysql_real_connect: " << mysql_error (mysql));

  return NULL;
}

void
dbMySQLDisconnect (MYSQL * conn)
{
  if (conn == NULL)
    {
      WARNING ("Null pointer passed");
    }
  else
    {
      DEBUG(DEBUG3, "Connection closed [ptr=" << conn << "]");
      mysql_close (conn);
    }
}

bool dbMySQLSendQuery(MYSQL *conn, const string str)
{
  int err;
  string errstr;

  DEBUG(DEBUG3, str);

  err = mysql_query(conn, str.c_str());
  if (err != 0)
    {
      errstr = mysql_error(conn);
      if(!errstr.empty())
        {
          ERROR("mysql_query: " << errstr);
        }
      return false;
    }
  return true;
}

