/*      SAMS (Squid Account Management System
 *      Author: Dmitry Chemerik chemerik@mail.ru
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

#include "logtool.c"

 
struct samsusers *users;
struct url_replace *RUCm;

int LCOUNT;
int READNEWCONF;
int RUC;
int RIPC;
int ucount,rcount;
static char str[BUFFER_SIZE];
char rname[150][25];
char rurl[150][25];
char rnew[150][15];
int ucount,rcount;
char *STR[10];
char AUTH[5];
int PRINT,CLEAR,DEBUG,LOADFILE;
MYSQL *tconn;
MYSQL *conn,*conn2;

void LoadSAMSSettings()
{
  int flag;
  MYSQL_RES *res;
  MYSQL_ROW row;
  char real[6];
  
      sprintf(&str[0],"SELECT endvalue,auth,ntlmdomain,realsize,checkdns,loglevel,udscript,adminaddr FROM %s.sams",conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      row=mysql_fetch_row(res);
      IP=0;
      NCSA=0;
      NTLM=0; 
      if(strcmp(row[1],"ncsa")==0)
        {
          NCSA=1;
        }
      if(strcmp(row[1],"ntlm")==0)
        {
          NTLM=1;
        }
      if(strcmp(row[1],"adld")==0)
        {
          NTLM=1;
        }
      if(strcmp(row[1],"ip")==0)
        {
          IP=1;
        }
      if(strcmp(row[2],"Y")==0)
        {
          NTLMDOMAIN=1;
        }
	
      sprintf(&real[0],"%4s",row[3]);
      if(strcmp(&real[0],"real")==0)
        {
          REALTRAF=1;
        }

      sprintf(&real[0],"%1s",row[4]);
      if(memchr(&real[0], 'Y', 1)==NULL)
        {
          NODNSSERVER=1;
        }
      LOGLEVEL=txt2digit(row[5]);

      if(strlen(row[6])==0||strcmp(row[6],"NONE")==0)
        {
          UDSCRIPT=0;
        }
      if(strlen(row[6])>0&&strcmp(row[6],"NONE")!=0)
        {
          UDSCRIPT=1;
          // Скрипт, выполняемый при отключении пользователей 
          if((UDSCRIPTFILE=(char *)malloc((sizeof(char))*(strlen(WEBINTERFACEPATH)+strlen("/src/script/")+strlen(row[6])+1)))==NULL)
            {
              printf("Not enought memory to allocate buffer\n");
              exit(1);
             }
	  sprintf(UDSCRIPTFILE,"%s/src/script/%s",WEBINTERFACEPATH,row[6]);
	  //trim(UDSCRIPTFILE);
        }
      if(DEBUG>0&&UDSCRIPT==1)
        {
	  printf("disable user script = %s\n",UDSCRIPTFILE);
	}
      if(UDSCRIPT>0)
        {
           sprintf(&conf.adminaddr[0],"%s",row[7]);
	   if(DEBUG>0)
	     printf("Administrator address: %s\n",&conf.adminaddr[0]);
        }      

      mysql_free_result(res);

      sprintf(&str[0],"SELECT kbsize,mbsize FROM %s.globalsettings",conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      row=mysql_fetch_row(res);
      KBSIZE=atoi(row[0]);
      MBSIZE=atoi(row[1]);
      if(DEBUG>0)
        {
          printf("ISP Mb size=%.0f, kb size=%.0f\n",MBSIZE,KBSIZE);
          printf("ISP Mb size=%s, kb size=%s\n",row[1],row[0]);
	}  
      mysql_free_result(res);
 
}

void LoadUsersData(MYSQL *conn)
{
  int i,flag,k;
  MYSQL_RES *res;
  MYSQL_ROW row;
  char str[BUFFER_SIZE];
  /* Получаем количество пользователей SAMS */
  samsuserscount=0;
  sprintf(&str[0],"SELECT count(nick) FROM %s.squidusers",conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  row=mysql_fetch_row(res);
  samsuserscount=atoi(row[0]);

  if(DEBUG==1)
    {
       printf("Found %d SAMS users\n",samsuserscount);
    }
  /* Выделяем память под пользователей SAMS */
  if((users=(struct samsusers *)malloc(sizeof(struct samsusers)*(samsuserscount+1)))==NULL)
     {
       printf("Not enought memory to allocate buffer\n");
       exit(1);
     }

  /* Загружаем пользователей SAMS в массив */
//  sprintf(&str[0],"SELECT nick,domain,ip,ipmask,enabled,size,quotes,id,hit FROM %s.squidusers",conf.samsdb);
  sprintf(&str[0],"SELECT squidusers.nick,squidusers.domain,squidusers.ip,squidusers.ipmask,squidusers.enabled,squidusers.size,squidusers.quotes,squidusers.id,squidusers.hit,squidusers.shablon,shablons.auth FROM %s.squidusers LEFT JOIN %s.shablons ON squidusers.shablon=shablons.name",conf.samsdb,conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  for(i=0;i<samsuserscount;i++)
     {
        row=mysql_fetch_row(res);
        strncpy(users[i].user,str2lower(row[0]),25);
        strncpy(users[i].domain,str2lower(row[1]),25);

         sprintf(&str[0],"%s/%s\n",row[2],row[3]);
	 LocalIPAddr(&str[0],&users[i].ip[0],&users[i].mask[0]);
        users[i].enabled=atoi(row[4]);
        users[i].disabled=0;

        if(strlen(row[5])==0)
                              users[i].size=0;
        else
                              users[i].size=atof(row[5]);
        if(strlen(row[8])==0)
                              users[i].hit=0;
        else
                              users[i].hit=atof(row[8]);
        if(REALTRAF==1)
           users[i].traffic=users[i].size-users[i].hit;
        else
           users[i].traffic=users[i].size;
//        users[i].traffic=0;

        users[i].quote=atof(row[6])*MBSIZE;
        strncpy(users[i].id,row[7],25);
        strncpy(users[i].date,"0000-00-00",15);
	users[i].ipauth=0;
	users[i].ntlmauth=0;
	users[i].adldauth=0;
	users[i].ncsaauth=0;

        if(strcmp(row[10],"ip")==0)
	  {
	    users[i].ipauth=1;
	    IP=1;
	  } 
        if(strcmp(row[10],"ntlm")==0)
	  {
	    users[i].ntlmauth=1;
	    NTLM=1;
	  } 
        if(strcmp(row[10],"ncsa")==0)
	  {
	    users[i].ncsaauth=1;
	    NCSA=1;
	  }  
        if(strcmp(row[10],"adld")==0)
	  {
	    users[i].adldauth=1;
	    NTLM=1;
	  }  

        if(DEBUG==1)
          {
             printf("%2d: %15s %15s ",i,users[i].user,users[i].domain);
             for(k=0;k<6;k++)
	       {
		 printf("%d.",users[i].ip[k]);
	       }
             printf("/");
             for(k=0;k<6;k++)
	       {
                 printf("%d.",users[i].mask[k]);
	       }
             printf(" %d %10.0f %12.0f %s\n",users[i].enabled,users[i].size,users[i].quote,users[i].id);
          }
     }
}


void sighup_handler(int signum)
{
//  int flag;
//  struct sigaction sigchld_action,sa;
//  struct itimerval timer;

  if(DEBUG>0)
    printf("Get SIGHUP signal\n");
  READNEWCONF=1;
	  free(users);
          free(local);
          free(RUCm);
          freeconf();
          LoadSAMSSettings();
          LoadUsersData(conn);
//  sa.sa_handler = sighup_handler;
//  sigaction(SIGUSR1, &sa, NULL);
}


//void timer_handler(int signum)
//{
//  int flag;
//  sprintf(&buf[0],"SELECT * FROM %s.sams",conf.samsdb);
//  flag=send_mysql_query(tconn,&buf[0]);
//  if(DEBUG>0)
//    printf("starting timer\n");
//}


void ReadNewData(MYSQL *conn,MYSQL *conn2)
{
  char buf[BUFFER_SIZE];
  char str[BUFFER_SIZE];
  FILE *finp;
  char *user=NULL,*domain=NULL,*status=NULL;
  int i,flag,flag0;
  int str7len=0;
  time_t tt;
  struct tm *t;
  long hitsize;
  double size;
  int samsuser;
  MYSQL_RES *res;
  MYSQL_ROW row;
  int userflag=0;
  long count;

  if((finp=fopen(&path[0],"r+"))<0)
    {
      printf("i can't open FIFO fle %s for reading\n",&path[0]);
      exit(1);
    }

  strcpy(&buf[0],"\0");

  tt=time(NULL);
  t=localtime(&tt);

  count=0;
  while(1)
    {
      fgets(&buf[0],BUFFER_SIZE,finp);

      LOCALURL=0;
      if(buf[0]!='\n')
        {
           if(DEBUG>0||PRINT>0)
             {
               printf("%ld: Read STDIN: \n%s\n",count, &buf[0]);
             }
           count++;
           flag0=ReplaceCHR(&buf[0]);
           if(strstr( &buf[0], "TCP_DENIED" )==0&&strstr( &buf[0], "UDP_DENIED" )==0&&strstr( &buf[0], "NONE/400" )==0&&flag0==0)
             {
                STR[0]=strtok(&buf[0]," ");
                for(i=1;i<9;i++)
                  {
                     STR[i]=strtok(NULL," ");
                  }
                flag=0;
                str7len=0;
                userflag=0;
                hitsize=0;
                samsuser=0;

                str2lower(STR[7]);
                
		if(strcmp(STR[7],"-")!=0)
		  {
		    // аутентификация NTLM domain+user?
		    if(NTLM>0||NCSA>0)
                      {
                        if(strstr(STR[7],"+")!=0)
                          {
                            domain=strtok(STR[7],"+");
                            user=strtok(NULL,"+");
                            samsuser=ReturnSAMSUser(user, domain, STR[2], 1);
                            userflag=1;
                          }
                        else if(strstr(STR[7],"\\")!=0)
                          {
                            domain=strtok(STR[7],"\\");
                            user=strtok(NULL,"\\");
                            samsuser=ReturnSAMSUser(user,domain, STR[2], 1);
                            userflag=1;
                          }
		        else
		          {
                            strcpy(str,STR[7]);
                            if((samsuser=ReturnSAMSUser(str,"", STR[2], 2))>0)
                              {
                                 domain=users[samsuser-1].domain;
                                 user=users[samsuser-1].user;
                                 if(DEBUG!=0)
                                   printf("user found: %s/%s \n",domain,user);
                                 userflag=1;
		              }	  
		          }
                      }
		  }    
                if(userflag==0)
                  {
                     if((samsuser=ReturnSAMSUser("","", STR[2], 0))>0)
                       {
                          domain=users[samsuser-1].domain;
                          user=users[samsuser-1].user;
                          if(DEBUG!=0)
                            printf("user found: %s/%s \n",domain,user);
                          userflag=1;
		       }	  
                  }

                tt=atol(STR[0]);
                t=localtime(&tt);

                status=strtok(STR[3],"/");
                LOCALURL=0;
                LOCALURL=TestLocalURL(STR[6]);

                if(strcmp( status, "TCP_DENIED" )!=0&&strcmp( status, "UDP_DENIED" )!=0&&userflag!=0&&LOCALURL==0)
                  {
                    size=atof(STR[4]);
		    users[samsuser-1].size+=size;
                    if(strstr( status, "_HIT" )!=0&&strstr( status, "_NEGATIVE_" )==0)
                      {
		        users[samsuser-1].hit+=size;
                        hitsize=size;
                      }

                    if(REALTRAF==1)
 		      sprintf(&str[0],"UPDATE %s.squidusers SET size='%12.0f',hit='%12.0f' WHERE id='%s'",conf.samsdb,users[samsuser-1].size,users[samsuser-1].hit,users[samsuser-1].id);
		    else  
 		      sprintf(&str[0],"UPDATE %s.squidusers SET size='%12.0f',hit='%12.0f' WHERE id='%s'",conf.samsdb,users[samsuser-1].size-users[samsuser-1].hit,users[samsuser-1].hit,users[samsuser-1].id);
                    flag=send_mysql_query(conn2,&str[0]);

                    if(users[samsuser-1].quote>0)
		      {
                        if(REALTRAF==1)
			  {
                            users[samsuser-1].traffic+=size-hitsize;
			  }  
                        else
			  {
                            users[samsuser-1].traffic+=size;
			  }  
                        if(users[samsuser-1].size>=users[samsuser-1].quote)
		          {
                            if(users[samsuser-1].disabled==0)
			      {
			        users[samsuser-1].disabled=1;
			        users[samsuser-1].enabled=0;
                                if(DEBUG!=0||PRINT!=0)
                                  printf("User %s/%s disabled.  traffic size = %12.0f quote = %12.0f\n",users[samsuser-1].domain,users[samsuser-1].user,users[samsuser-1].size,users[samsuser-1].quote);
			        sprintf(&str[0],"UPDATE %s.squidusers SET enabled='0' WHERE id='%s'",conf.samsdb,users[samsuser-1].id);
                                flag=send_mysql_query(conn2,&str[0]);
			        if(UDSCRIPT>0)
				  {
				    exec_script(UDSCRIPTFILE, users[samsuser-1].user);
				  }  
			        sprintf(&str[0],"INSERT INTO %s.reconfig SET action='reconfig',service='squid',number='%d'",conf.samsdb,conf.cachenum);
                                flag=send_mysql_query(conn2,&str[0]);
				if(flag==0)
				  {
                                    sprintf(&str[0],"Disable user %s traffic %.0f>%.0f",users[samsuser-1].user,users[samsuser-1].size,users[samsuser-1].quote);
                                    AddLog(conn2,0,"sams",&str[0]);
				  
				  }
			      }  
		          }
		      }	  
                    if(DEBUG!=0)
                      printf(" %s/%s, ip=%d.%d.%d.%d traffic size=%10f traffic=%10.0f quote=%12.0f\n",users[samsuser-1].domain,users[samsuser-1].user,users[samsuser-1].ip[0],users[samsuser-1].ip[1],users[samsuser-1].ip[2],users[samsuser-1].ip[3],size,users[samsuser-1].size,users[samsuser-1].quote);

                    ReplaceURL(STR[6],user,domain);
                    sprintf(&str[0],"INSERT INTO %s.cache SET date='%d-%d-%d',time='%d:%d:%d',size='%s',ipaddr='%s',url='%s',user='%s',domain='%s',hit='%lu'",conf.logdb,t->tm_year+1900,t->tm_mon+1,t->tm_mday,t->tm_hour,t->tm_min,t->tm_sec,STR[4],STR[2],&path[0],user,domain,hitsize);

                    flag=send_mysql_query(conn,&str[0]);
                    if(PRINT!=0||DEBUG!=0)
                       printf("set: %d-%d-%d %d:%d:%d %s/%s %s %s\n",t->tm_year+1900,t->tm_mon+1,t->tm_mday,t->tm_hour,t->tm_min,t->tm_sec,user,domain,STR[4],&path[0]);
                    if(flag!=0)
                      {
                         printf("error: %s\n",&str[0]);
                         printf("Error %u (%s)\n",mysql_errno(conn),mysql_error(conn));
                         exit(1);
                      }

                    sprintf(&str[0],"%d-%d-%d",t->tm_year+1900,t->tm_mon+1,t->tm_mday);

		    if(strcmp(&str[0],users[samsuser-1].date)!=0)
		      {
                         sprintf(&str[0],"SELECT count(*) FROM %s.cachesum WHERE date='%d-%d-%d'&&user='%s'&&domain='%s'",conf.logdb,t->tm_year+1900,t->tm_mon+1,t->tm_mday,user,domain);
                         flag=send_mysql_query(conn,&str[0]);
                         res=mysql_store_result(conn);
                         row=mysql_fetch_row(res);
			 
                         if(atoi(row[0])==0)
                           {
			     strncpy(users[samsuser-1].date,&str[0],10);
                             sprintf(&str[0],"INSERT %s.cachesum SET size='%s',hit='%lu',date='%d-%d-%d',user='%s',domain='%s'",conf.logdb,STR[4],hitsize,t->tm_year+1900,t->tm_mon+1,t->tm_mday,user,domain);
                             flag=send_mysql_query(conn,&str[0]);
			   }  
			 else
			   {
                             sprintf(&str[0],"UPDATE %s.cachesum SET size=size+'%s',hit=hit+'%lu' where date='%d-%d-%d'&&user='%s' && domain='%s'",conf.logdb,STR[4],hitsize,t->tm_year+1900,t->tm_mon+1,t->tm_mday,user,domain);
                             flag=send_mysql_query(conn,&str[0]);
			   }  
                         mysql_free_result(res);
		      
		      }
                    else
		      {
                         sprintf(&str[0],"UPDATE %s.cachesum SET size=size+'%s',hit=hit+'%lu' where date='%d-%d-%d'&&user='%s' && domain='%s'",conf.logdb,STR[4],hitsize,t->tm_year+1900,t->tm_mon+1,t->tm_mday,user,domain);
                         flag=send_mysql_query(conn,&str[0]);
		      
		      } 


                    if(DEBUG>0)
                      {
                          printf("database appended\n");
                      }
		                  
                  }
             }
// printf("Exit program\n");
// exit(0);  
	}	  
    } 
  if(DEBUG>0)
    printf("Close file, READNEWCONF=%d\n", READNEWCONF);      
  fclose(finp);
}



int main (int argc, char *argv[])
{
  int i;
  MYSQL_RES *res;
  MYSQL_ROW row;
  int flag;
  pid_t pid=0,parentpid=0,childpid=0;
  struct sigaction sa;
//  sigchld_action,sa;
//  struct itimerval timer;
  struct stat st;
  time_t tt;
  struct tm *t;
  
  printf("\n");

  for(i=0;i<argc;i++)
     {
       if(strstr(argv[i],"--help")!=0||strstr(argv[i],"-h")!=0)
          {
            printf("Usage: samsf [options]\n");
            printf(" -h, --help       show this message.\n");
            printf(" -p, --print      print additional information.\n");
            printf(" -d, --debug      print debug message.\n");
            printf(" -V, --version    Print version.\n");
            exit(0);
          }
       if(strstr(argv[i],"--version")!=0||strstr(argv[i],"-V")!=0)
          {
            printf("Version %s\n", VERSION);
	    exit(0);
          }
       if(strstr(argv[i],"--print")!=0||strstr(argv[i],"-p")!=0)
          {
            PRINT=1;
          }
       if(strstr(argv[i],"--debug")!=0||strstr(argv[i],"-d")!=0)
          {
            DEBUG=1;
          }
     }

 
  pid = getpid();
  if(DEBUG==1)
    {
      printf("Starting process: pid = %d\n",pid);
    }  
  if(access("/var/run/samsf.pid",F_OK)==0)
    {
      if(TestPID("/var/run/samsf.pid")!=0)
         exit(0);
    }
  readconf();

  conn = do_connect(conf.host, conf.user, conf.passwd, conf.samsdb, def_port_num, def_socket_name, 0);
  if (conn == NULL)
                    exit(1);
  tconn =conn;
  conn2 = do_connect(conf.host, conf.user, conf.passwd, conf.logdb, def_port_num, def_socket_name, 0);
  if (conn2 == NULL)
                    exit(1);
  READNEWCONF=0;
  LoadSAMSSettings();
  AddLog(conn,0,"samsf","Starting");
  LoadUsersData(conn);

/*список локальных хостов*/
  sprintf(&str[0],"SELECT count(*) FROM %s.urls WHERE type='local'",conf.samsdb);
  flag=send_mysql_query(conn2,&str[0]);
  res=mysql_store_result(conn2);
  row=mysql_fetch_row(res);
  LCOUNT=txt2digit(row[0]);
  mysql_free_result(res);
  if(DEBUG>0)
    printf("Found %d localhosts \n",LCOUNT);  


  if((local=(struct local_url *)malloc(sizeof(struct local_url)*(LCOUNT+1)))==NULL)
     {
       printf("Not enought memory to allocate buffer\n");
       exit(1);
     }
  sprintf(&str[0],"SELECT * FROM %s.urls WHERE type='local'",conf.samsdb);
  flag=send_mysql_query(conn2,&str[0]);
  res=mysql_store_result(conn2);
  for(i=0;i<LCOUNT;i++)
     {
         row=mysql_fetch_row(res);
         strncpy(local[i].url,row[0],50);
         local[i].ipflag=LocalIPAddr(row[0],local[i].ip,local[i].mask);
         if(DEBUG>0)
	   {
             printf("%s ",local[i].url);
	     if(local[i].ipflag==1)
	       {
	         //NODNSSERVER=0;
                 printf(">> %d.%d.%d.%d",local[i].ip[0],local[i].ip[1],local[i].ip[2],local[i].ip[3]);
                 printf("/%d.%d.%d.%d",local[i].mask[0],local[i].mask[1],local[i].mask[2],local[i].mask[3]);
               }
	     printf("\n");  
	   }  
     }
  mysql_free_result(res);
/*список локальных хостов*/

  sprintf(&str[0],"SELECT count(*) FROM %s.urlreplace",conf.samsdb);
  if(PRINT>0||DEBUG>0)
    printf("2. %s \n",&str[0]);
  flag=send_mysql_query(conn2,&str[0]);
  res=mysql_store_result(conn2);
  row=mysql_fetch_row(res);
  RUC=txt2digit(row[0]);
  mysql_free_result(res);

  if((RUCm=(struct url_replace *)malloc(sizeof(struct url_replace)*(RUC+1)))==NULL)
     {
       printf("Not enought memory to allocate buffer\n");
       exit(1);
     }
  sprintf(&str[0],"SELECT * FROM %s.urlreplace",conf.samsdb);
  flag=send_mysql_query(conn2,&str[0]);
  res=mysql_store_result(conn2);
  for(i=0;i<RUC;i++)
     {
         row=mysql_fetch_row(res);
         strncpy(RUCm[i].user,row[0],25);
         strncpy(RUCm[i].domain,row[1],25);
         strncpy(RUCm[i].url,row[2],50);
         strncpy(RUCm[i].newurl,row[3],50);
         if(DEBUG!=0)
            printf(" %s %s %s %s\n",RUCm[i].user,RUCm[i].domain,RUCm[i].url,RUCm[i].newurl);
     }
  mysql_free_result(res);
  parentpid=getppid();
  
  sa.sa_flags=SA_RESTART;
  sa.sa_handler = sighup_handler;
  sigaction(SIGUSR1, &sa, NULL);

  if(DEBUG==0)
    childpid=fork();

  if(childpid==0||(childpid!=0&&DEBUG==1))
    {
      if(childpid==0)
         AddLog(conn,0,"samsf","Starting as daemon");
      pid = getpid();
      if(SavePID(pid,"/var/run/samsf.pid")!=0)
          exit(0);
      printf("Starting child process\n");
     
      sprintf(&path[0],"%s/%s",conf.logdir,conf.logfile);
      trim(&path[0]);
  
     stat( &path[0], &st );

      switch(st.st_mode & S_IFMT)
        {
          case S_IFSOCK:
          case S_IFLNK:
          case S_IFCHR:
          case S_IFBLK:
          case S_IFDIR:
          case S_IFREG:
               tt=time(NULL);
               t=localtime(&tt);
               sprintf(&str[0],"%s/%s.%d%d%d",conf.logdir,conf.logfile,t->tm_year+1900,t->tm_mon+1,t->tm_mday);
	       rename(&path[0],&str[0]);
               break;
        }       

      if(access(&path[0],F_OK)!=0)
        {

      #ifdef Linux
          if(DEBUG>0)
            printf("Operating System = Linux\n");
          if(mknod(&path[0],S_IFIFO|0666,0)<0)
      #else
          if(DEBUG>0)
             printf("OS = Non Linux OS\n");
          umask(0);
          if(mkfifo(&path[0],0666)<0)
      #endif
            {
              printf("make FIFO error (%s)\n",&path[0]);
              exit(1);
            }  
          chmod(&path[0],0666); 	
        }

   
      ReadNewData(conn,conn2);
      RmPID("/var/run/samsf.pid");
    }
  free(users);
  free(local);
  free(RUCm);
  freeconf();

  return(0);
}
