/*      SAMS (Squid Account Management System
 *      Author: Dmitry Chemerik chemerik@mail.ru
 *
 *	This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, write to the Free Software
 *	Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */
#include <string.h>
#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <ctype.h>
#include <mysql.h>
#include <math.h>
#include <fcntl.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <sys/time.h>
#include <syslog.h>
#include <dirent.h>
#include <sys/un.h>
#include <unistd.h>
#include "config.h"
#include "define.h"
#include "tools.h"

int LocalIPAddr(char *url, int *od, int *om)
{
  int i=0,ocount=0,slashe=0,length=0;
  int t1=0,j=0;
  int bit,bit2;
  char octet[50];

  for(i=0;i<6;i++)
    {
      od[i]=0;
      om[i]=0;
    }  
  length=strlen(url);
  i=0;
  while(slashe<1&&i<length)
    {
      if(isdigit(url[i])==0&&url[i]!='.'&&url[i]!='/')
        {
	  return(0);
	}  
      else if(url[i]=='.')
	{
          strncpy(&octet[0],url+t1,i-t1);
	  strcpy(&octet[i-t1],"\0");
          if(atof(&octet[0])>255)
	    return(0);
	  else  
	    od[ocount]=atoi(&octet[0]);
	  t1=i+1;
          ocount++;
	}  
      else if(url[i]=='/')
        {
	  slashe++;
          strncpy(&octet[0],url+t1,i-t1);
	  strcpy(&octet[i-t1],"\0");
          if(atof(&octet[0])>255)
	    return(0);
	  else  
	    od[ocount]=atoi(&octet[0]);
	  t1=i+1;
	}
      i++;
    }
  if(slashe==0)
    {
      strncpy(&octet[0],url+t1,i-t1);
      strcpy(&octet[i-t1],"\0");
      if(atof(&octet[0])>255)
	return(0);
      else  
	od[ocount]=atoi(&octet[0]);
	t1=i+1;
    }  
  slashe=0;
  
  /****  Маска  **/
  t1=i;
  ocount=0;
  if(length-i<=2&&length-i>0)
    {
       /* если маска задана количеством битов*/
       strncpy(&octet[0],url+i,length-i);
       strcpy(&octet[length-i],"\0");
       if(atof(&octet[0])>48)
	 return(0);
       else  
	 bit=atoi(&octet[0]);
       //bit2=abs((32-bit)/8);
       bit2=bit/8;
       for(j=0;j<bit2;j++)
         {
           om[j]=255;
	 }
	om[j]=255&((char)255<<(8-(bit-bit2*8)));
    }
  else if(length-i>0)
    {
      while(slashe<1&&i<length)
        {
          if(url[i]=='.')
	    {
              strncpy(&octet[0],url+t1,i-t1);
	      strcpy(&octet[i-t1],"\0");
              if(atof(&octet[0])>255)
	        return(0);
	      else  
	        om[ocount]=atoi(&octet[0]);
	      t1=i+1;
              ocount++;
	    }  
          else if(url[i]=='/')
            {
	      slashe++;
              strncpy(&octet[0],url+t1,i-t1);
	      strcpy(&octet[i-t1],"\0");
              if(atof(&octet[0])>255)
	        return(0);
	      else  
	        om[ocount]=atoi(&octet[0]);
	      //printf("%ld \n",om[ocount]);
	      t1=i+1;
	    }
          i++;
        }
      if(slashe==0)
        {
          strncpy(&octet[0],url+t1,i-t1);
          strcpy(&octet[i-t1],"\0");
          if(atof(&octet[0])>255)
	    return(0);
          else  
	    om[ocount]=atoi(&octet[0]);
          //printf("%ld\n",om[ocount]);
	    t1=i+1;
        }  
    }
  else
    {
      om[0]=om[1]=om[2]=om[3]=om[4]=om[5]=255;
    }  
 return(1);
}


int send_mysql_query(MYSQL *conn, char *str)
{
 int flag;
 char *error;
 flag=mysql_query(conn,&str[0]);
 if(flag!=0)
   {
     error=mysql_error(conn);
     if(strlen(error)>0)
       {
	 sprintf(&str[0],"MySQL query error: %s", error);
         syslog(LOG_LOCAL0|LOG_INFO,&str[0]);
         printf("Error\n %s\n",error);
         if(DEBUG>0)
           printf(" into MySQL query:\n %s\n",str);
       } 
     exit(1);
   }  
 return(flag);
}

void AddLog(MYSQL *conn, int level, char *demon, char *str)
{
  time_t tt;
  struct tm *t;
  char log[256];
  if(LOGLEVEL>=level)
    {
      tt=time(NULL);
      t=localtime(&tt);
      syslog(LOG_LOCAL0|LOG_INFO, str);
      sprintf(&log[0],"INSERT INTO %s.log SET user='%s',date='%d-%d-%d',time='%d:%d:%d',value='%s',code='10'",conf.samsdb,demon,t->tm_year+1900,t->tm_mon+1,t->tm_mday,t->tm_hour,t->tm_min,t->tm_sec,str);
      send_mysql_query(conn,&log[0]);
    }  

}

char *url_decode(char *str) 
{
char *strold;
char *buf;
char *p;
int n;
unsigned char c;
char *_sf_urld_buf = NULL;
ssize_t _sf_urld_buflen = 0;

strold=str;

n = (str ? strlen(str) : 0) + 1;
if(n < _sf_urld_buflen)
  n = _sf_urld_buflen;
  p = buf = (char *)malloc(n);
if(p == NULL)
   return NULL;

/*  * If string is NULL, then return empty string.  */

if(str == NULL) 
  {
    if(_sf_urld_buf)
       free(_sf_urld_buf);
    _sf_urld_buflen = n;
    *p = '\0';
    return (_sf_urld_buf = buf);
  }

for(p; (c = *str); str++, p++) 
    {
//printf("%s\n",str);
       if(c == '%') 
         {
           char a, b;
           if( !(a = str[1]) || !(b = str[2]) ) 
             {
                /* Incomplete, but valid */
               *p = '%';
               continue;
             }
           if(a >= 'a') 
              a -= 32;
           if(b >= 'a') 
              b -= 32;
           if(a >= '0' && a <= '9')
              c = a - '0';
           else if( a >= 'A' && a <= 'F')
              c = 10 + a - 'A';
           else 
              {
                 /* Strange, but valid */
                 *p = '%';
                  continue;
              }
           c *= 16;
           if(b >= '0' && b <= '9')
              c += b - '0';
           else if( b >= 'A' && b <= 'F')
              c += 10 + b - 'A';
           else 
              {
                 *p = '%';
                  /* Strange, but valid */
                  continue;
              }
           str+= 2;
         } 
//      else if(c == '+') 
//         {
//           *p = ' ';
//           continue;
//         }
      *p = c;
     }

 *p = '\0';

 if(_sf_urld_buf)
   free(_sf_urld_buf);
 _sf_urld_buflen = n;
        
strcpy(strold,buf);
return NULL;
}


int UnlinkFiles(char *path,char *filemask)
{
  DIR *dirname;
  register struct dirent *dirbuf;
  char buf[BUFFER_SIZE];

  if((dirname = opendir (path)) == NULL)
    {
      fprintf(stderr, "Can't read directory %s\n",path);
      return 1;
    }
  while ((dirbuf = readdir (dirname)) != NULL ) 
    {
       if(strstr( dirbuf->d_name, filemask )!=0) 
          { 
	    sprintf(&buf[0],"%s/%s",path,dirbuf->d_name);
            unlink(&buf[0]);
	  }  
    }
  closedir (dirname);
  return(0);
}


int SavePID(int pid, char *filename)
{
  FILE *fout;
  if((fout=fopen(filename, "wt" ))==NULL)
    {
      printf("Don't save pid file %s... Exit.\n",filename);
      return(-1);
    }
  fprintf(fout,"%d",pid);
  fclose(fout);
  return(0);
}

int TestPID(char *filename)
{
  char buf[BUFFER_SIZE];
  FILE *finp;
  pid_t pid;
    
  if((finp=fopen(filename, "rt" ))==NULL)
    {
      printf("ERROR: pid file %s is busy... Exit.\n",filename);
      return(-1);
    }
  fgets( &buf[0], BUFFER_SIZE-1, finp );
  fclose(finp);
  pid=atoi(&buf[0]);
  if(kill(pid,0)==0)
    {
      printf("ERROR: program with PID %s is running: pid = %d\n",filename,pid);
      return(-1);
    }
  
  return(0);


}
void RmPID(char *filename)
{
  unlink(filename);
}



void Str2Lower(char *str)
{
  int i;
  for(i=0;i<strlen(str);i++)
     {
       str[i]=tolower(str[i]);
     }
}

void str2upper(char *string)
{
 while(*string)
     {
        *string=toupper(*string);
        string++;
     }
}


static char *stripws( char *str )
{
int start, end;

if( !str ) return( NULL );
    for( start=0 ; str[start]==' ' ; start++ );
    if( str[start]=='\0' )
    {
	str[0]='\0';
	return( str );
    }
    for( end=strlen( str ) ; ((str[end]==' ') || (str[end]=='\0') || (str[end]=='\n') || (str[end]=='\r')) && end>-1 ; end-- ) str[end]='\0';
    if( end<1 ) return( str );
    memmove( str, str+start, end-start );
    str[ end-start+1 ]='\0';
    return( str );
}
char *xstrdup(char *str)
{
	char *s = strdup(str);
	if (s) return s;
	abort();
	return NULL;
}

/* read config from config file */

char *MallocMemory(char *str)
{
  char *addr;
  int length;
  length=strlen(str);
  if((addr=(char *)malloc(sizeof(char)*(length+5)))==NULL)
     {
       printf("Not enought memory to allocate buffer\n");
       exit(1);
     }
  sprintf(addr,"%s",str);
  return(addr);
}

void readconf( void )
{
	static char buf[BUFFER_SIZE], *s;
	FILE *fp=NULL;

        conf.cachenum=0;
	if((fp=fopen( CONFIG_FILE, "r" )))
	{
		while( fgets( buf, BUFFER_SIZE-1, fp ) )
		{
			/* First strip off comments */
			if((s=strchr(buf, '#')))
			{
				s[0]='\0';
			}
			/* Strip of whitespaces */
			if((s=strchr(buf, '=')))
			{
				s[0]='\0';
				s++;
				stripws(buf);
				stripws(s);

				if( !strcasecmp( buf, "sams_db" ) )
                                        conf.samsdb=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "squid_db" ) )
                                        conf.logdb=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "mysqlhostname" ) )
                                        conf.host=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "mysqluser" ) )
                                        conf.user=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "mysqlpassword" ) )
                                        conf.passwd=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "squidcachefile" ) )
                                        conf.logfile=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "squidrootdir" ) )
                                        conf.squidrootdir=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "squidlogdir" ) )
                                        conf.logdir=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "squidcachedir" ) )
                                        conf.cachedir=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "samspath" ) )
                                        conf.samspath=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "squidpath" ) )
                                        conf.squidpath=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "squidguardlogpath" ) )
                                        conf.sglogpath=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "squidguarddbpath" ) )
                                        conf.sgdbpath=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "recodecommand" ) )
                                        conf.recode=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "rejikpath" ) )
                                        conf.rejikpath=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "shutdowncommand" ) )
                                        conf.shutdown=MallocMemory(xstrdup(s));
				else if( !strcasecmp( buf, "cachenum" ) )
				        conf.cachenum=atoi(xstrdup(s));
			}
		}
          fclose(fp);
	}       
/*
printf("MySQL DB            = %s \n",&conf.samsdb[0]);
printf("SQUID log DB        = %s \n",&conf.logdb[0]);
printf("MYSQL host          = %s \n",&conf.host[0]);
printf("User of  MySQL      = %s \n",&conf.user[0]);
printf("MySQL user passwd   = %s \n",&conf.passwd[0]);
printf("Squid log file      = %s \n",&conf.logfile[0]);
printf("Squid root dir      = %s \n",&conf.squidrootdir[0]);
printf("Squid log dir       = %s \n",&conf.logdir[0]);
printf("Path of the SAMS    = %s \n",&conf.samspath[0]);
printf("Path of the SQUID   = %s \n",&conf.squidpath[0]);
printf("SquidGuard log path = %s \n",&conf.sglogpath[0]);
printf("SquidGuard db path  = %s \n",&conf.sgdbpath[0]);
printf("Recode file string  = %s \n",&conf.recode[0]);
printf("Cache number        = %d \n",conf.cachenum);
*/

}

void freeconf()
{
  free(conf.samsdb);
  free(conf.logdb);
  free(conf.host);
  free(conf.user);
  free(conf.passwd);
  free(conf.logfile);
  free(conf.squidrootdir);
  free(conf.logdir);
  free(conf.cachedir);
  free(conf.samspath);
  free(conf.squidpath);
  free(conf.sglogpath);
  free(conf.sgdbpath);
  free(conf.recode);
  free(conf.rejikpath);
  free(conf.redirpath);
  free(conf.deniedpath);
  free(conf.lang);
  free(conf.shutdown);
}


MYSQL *do_connect(char *host_name,char *user_name, char *password,
                   char *db_name,
		   unsigned int port_num, char *socket_name,
		   unsigned int flags)
{
  MYSQL *conn;
  int connect,count;
  if(DEBUG==1)
    {
      printf("Connected database: %s:%s user=%s\n",db_name,host_name, user_name);
    }
  count=0;
  while(count<=5)
    {
      conn = mysql_init(NULL);
      if(conn==NULL)
        {
          if(DEBUG>0)
	    printf("%d: mysql_init() error. no open database %s, DELAY 3 sec\n", count, db_name);
//	  fprintf(stderr,"%d: mysql_init() error. no open database %s, DELAY 3 sec\n",db_name);
	  fprintf(stderr,"mysql_init() error. no open database %s, DELAY 3 sec\n",db_name);
        }  	
      count++;
    }
  if(conn==NULL)
    {
      fprintf(stderr,"mysql_init() error. no open database %s, EXIT\n",db_name);
      return(NULL);
    }  	
  count=0;
  while(count<=5)
    {
      //printf("count=%d from %d\n",count,SDELAY);
      connect=mysql_real_connect(conn, host_name, user_name, password, db_name, 0, NULL, 0);
      if(connect==0)
        { 
          fprintf(stderr,"mysql_real_connect() error %d. no open database %s, DELAY 3 sec\n", count, db_name);
          sleep(10);
        }
      else
        {
	  return(conn);
	} 	
      count++;
      //printf("COUNT %d\n",count);  
    }	
  return(NULL);
}		     
		     ;
void do_disconnect(MYSQL *conn)
{        
 mysql_close(conn);
}		   

