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

int EMPTY;
long ENDVALUE;
long NEWENDVALUE;

int RIPC;
int ucount,rcount;
int ucount,rcount;
char *STR[10];
char AUTH[5];
int CLEAR,TCLEAR, LOADFILE;

  char ip[15];
  int ip1[4];
  int ip2[4];

// void trim(char *string) 
static char str[BUFFER_SIZE];


int ExportDB(char *date1, char *date2, MYSQL *conn)
{
  int flag;
  FILE *fout;
  MYSQL_RES *res;
  MYSQL_ROW row=NULL;

  sprintf(&str[0],"%s_db_%s_%s.sql",conf.logdb,date1,date2);
  if(DEBUG>0)
    {
      printf("Export SQUID log database to file %s\n",&str[0]);
    }
  if((fout=fopen( &str[0], "wt" ))==NULL)
    {
         printf("Don't create file %s\n",&str[0]);
         return(1);
    }
  fprintf(fout,"USE %s;\n\n",conf.logdb);
  sprintf(&str[0],"SELECT * FROM %s.cachesum WHERE date>='%s'&&date<='%s'",conf.logdb,date1,date2);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  while((row=mysql_fetch_row(res)))
    {
      fprintf(fout,"INSERT INTO %s.cachesum SET date='%s',user='%s',domain='%s',size='%s',hit='%s';\n",conf.logdb,row[0],row[1],row[2],row[3],row[4]);
    }
  mysql_free_result(res);

  fprintf(fout,"\n");

  sprintf(&str[0],"SELECT * FROM %s.cache WHERE date>='%s'&&date<='%s'",conf.logdb,date1,date2);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  while((row=mysql_fetch_row(res)))
    {
      fprintf(fout,"INSERT INTO %s.cache SET date='%s',time='%s',user='%s',domain='%s',size='%s',ipaddr='%s',period='%s',url='%s',hit='%s',method='%s';\n",conf.logdb,row[0],row[1],row[2],row[3],row[4],row[5],row[6],row[7],row[8],row[9]);
    }
  mysql_free_result(res);
  if(EMPTY>0)
    {
      if(DEBUG>0)
        printf("Empty %s base to %s:%s",conf.logdb,date1,date2);
      sprintf(&str[0],"DELETE FROM %s.cachesum WHERE date>='%s'&&date<='%s'",conf.logdb,date1,date2);
      flag=send_mysql_query(conn,&str[0]);
      sprintf(&str[0],"DELETE FROM %s.cache WHERE date>='%s'&&date<='%s'",conf.logdb,date1,date2);
      flag=send_mysql_query(conn,&str[0]);
    
    }

  fclose(fout);
  return(0);
}


long GetNewEndValue()
{
  long value;
  FILE *finp;

  trim(&path[0]);
  sprintf(&path[0],"%s/%s",conf.logdir,conf.logfile);
  if((finp=fopen( &path[0], "rb" ))==NULL)
    {
         printf("Don't open file %s/%s\n",conf.logdir,conf.logfile);
         return(0);
    }
  fseek(finp,0,SEEK_END);
  value=ftell(finp);
  fclose(finp);
  return(value);
}




void SaveNewEndFileValue(MYSQL *conn,long count)
{
  int flag;
  if(conf.cachenum==0)
    sprintf(&str[0],"UPDATE %s.sams SET endvalue=\'%ld\'",conf.samsdb,count);
  else
    sprintf(&str[0],"UPDATE %s.proxyes SET endvalue=\'%ld\' WHERE id=\'%d\'",conf.samsdb,count, conf.cachenum);
  flag=send_mysql_query(conn,&str[0]);
  if(flag!=0)
    {
       printf("error: can't update end file counter\n");
       exit(1);
    }
  if(DEBUG!=0)
    printf("Save new access.log file size\n");
}


void ReadNewData(MYSQL *conn,MYSQL *conn2)
{
  char *user=NULL,*domain=NULL,*status=NULL;
  FILE *finp;
  int i,flag,flag0;
  long count;
  int str7len=0;
  time_t tt;
  struct tm *t;
  long hitsize;
  double size;
  MYSQL_RES *res;
  MYSQL_ROW row;
  int samsuser;
  
  int userflag=0;
  double ENDVALUE2;

  sprintf(&path[0],"%s/%s",conf.logdir,conf.logfile);
  trim(&path[0]);
  
  if((finp=fopen( &path[0], "r" )))
    {

      if(DEBUG>0)
        {
          printf("open SQUID cache file: %s\n",&path[0]);
        }
      fseek(finp,ENDVALUE,SEEK_SET);
      count=0;
      ENDVALUE2=ENDVALUE;
      while(ENDVALUE2<NEWENDVALUE)
        {
           LOCALURL=0;
           count++;
	   if (TestInputString(fgets( &buf[0], BUFFER_SIZE-1, finp ))==-1) 
	     {
               if(DEBUG>0||PRINT>0)
	         printf("input string: \n%s\n",&buf[0]);
               ENDVALUE2=ftell(finp);
	       strcpy(&buf[0],"\0");	       
	     }
	   else
	     {
               for(i=0;i<strlen(&buf[0]);i++)
                 {
                   if(iscntrl(buf[i])!=0)
                      break;
                 }
               if(DEBUG>0||PRINT>0)
                 {
                   printf("\n%ld SQUID log string:\n%s",count,&buf[0]);
                 }
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

	            if(DEBUG!=0)
                      printf("Serch SAMS user:");
		    // аутентификация NTLM domain+user?
		    if(strcmp(STR[7],"-")!=0)
		      {
                        if(NTLM>0||NCSA>0)
                          {
                            if(strstr(STR[7],"+")!=0)
                              {
			        domain=strtok(STR[7],"+");
                                user=strtok(NULL,"+");
                                samsuser=ReturnSAMSUser(user, domain, STR[2], 1);
                                if(samsuser>0)
			          {
			            if(DEBUG!=0)
                                      printf(" %s/%s user found \n",domain,user);
                                    userflag=1;
			          }
                              }
                            else if(strstr(STR[7],"\\")!=0)
                              {
                                domain=strtok(STR[7],"\\");
                                user=strtok(NULL,"\\");
                                samsuser=ReturnSAMSUser(user,domain, STR[2], 1);
                                if(samsuser>0)
			          {
                                    if(DEBUG!=0)
                                      printf(" %s/%s user found \n",domain,user);
                                    userflag=1;
			          }
                              } 
		            else
		              {
                                strcpy(str,STR[7]);
                                if((samsuser=ReturnSAMSUser(str,"", STR[2], 2))>0)
                                  {
                                     domain=users[samsuser-1].domain;
                                     user=users[samsuser-1].user;
                                     if(samsuser>0)
			               {
                                         if(DEBUG!=0)
                                           printf(" %s/%s user found \n",domain,user);
                                         userflag=1;
				       }  
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
                                printf(" %s/%s user found \n",domain,user);
                              userflag=1;
		           }	  
                      }
                    if(userflag==0&&DEBUG!=0)
                      {
                        printf(" not found \n");
                      }
                
		    tt=atol(STR[0]);
                    t=localtime(&tt);

                    status=strtok(STR[3],"/");
                    if(DEBUG!=0)
                      {
                        printf("Test local domain: ");
                      }
                    LOCALURL=0;
                    LOCALURL=TestLocalURL(STR[6]);
                    if(LOCALURL==0&&DEBUG!=0)
                      {
                        printf(" local domain not found \n");
                      }
                
		    if(strcmp( status, "TCP_DENIED" )!=0&&strcmp( status, "UDP_DENIED" )!=0&&userflag!=0&&LOCALURL==0)
                      {
		        size=atof(STR[4]);
		        users[samsuser-1].size+=size;
                        users[samsuser-1].updated=1;
		        if(strstr( status, "_HIT" )!=0&&strstr( status, "_NEGATIVE_" )==0)
                          {
		            users[samsuser-1].hit+=size;
                            hitsize=size;
                          }

                        if(users[samsuser-1].quote>0)
		          {
                            if(REALTRAF==1)
			      {
                                users[samsuser-1].traffic+=size-hitsize;
				if(DEBUG>0)
				  printf("REALTRAFfic = %15.0lf - %ld\n", size, hitsize);
			      }  
                            else
			      {
                                users[samsuser-1].traffic+=size;
			      }  
                            if(users[samsuser-1].traffic>=users[samsuser-1].quote)
		              {

                                if(users[samsuser-1].enabled==1)
			          {
			            users[samsuser-1].disabled=1;
			            users[samsuser-1].enabled=0;
                                    if(DEBUG!=0||PRINT!=0)
                                      printf("User %s/%s disabled.  traffic %f usertraffic %12.0f < %12.0f\n",users[samsuser-1].domain,users[samsuser-1].user, size, users[samsuser-1].traffic,users[samsuser-1].quote);

			            sprintf(&str[0],"UPDATE %s.squidusers SET enabled='0' WHERE id='%s'",conf.samsdb,users[samsuser-1].id);
                                    flag=send_mysql_query(conn2,&str[0]);
			            if(UDSCRIPT>0)
				      {
				        exec_script(UDSCRIPTFILE, users[samsuser-1].user);
				      }  
				    sprintf(&str[0],"INSERT INTO %s.reconfig SET number='%d',action='reconfig',service='squid'",conf.samsdb,conf.cachenum);
                                    flag=send_mysql_query(conn2,&str[0]);
				    if(flag==0)
				      {
                                        sprintf(&str[0],"Disable user %s traffic %.0f>%.0f",users[samsuser-1].user,users[samsuser-1].traffic,users[samsuser-1].quote);
                                        AddLog(conn2,0,"sams",&str[0]);
			                if(UDSCRIPT>0)
				          {
                                            sprintf(&str[0],"Send message to admin. script %s",UDSCRIPTFILE);
				            AddLog(conn2,0,"samsdaemon",&str[0]);
				          }
				      }    
			          }

		              }
                          }
                        if(DEBUG!=0)
                          printf("user %s/%s, ip=%d.%d.%d.%d  traffic: %.0f+%.0f=%.0f  limit:%.0f\n",users[samsuser-1].domain,users[samsuser-1].user,users[samsuser-1].ip[0],users[samsuser-1].ip[1],users[samsuser-1].ip[2],users[samsuser-1].ip[3],size,users[samsuser-1].traffic,size+users[samsuser-1].traffic,users[samsuser-1].quote);

                        ReplaceURL(STR[6],user,domain);
                        TestURL(&path[0]);

                        sprintf(&str[0],"INSERT INTO %s.cache SET date='%d-%d-%d',time='%d:%d:%d',size='%s',ipaddr='%s',url='%s',user='%s',domain='%s',hit='%lu'",conf.logdb,t->tm_year+1900,t->tm_mon+1,t->tm_mday,t->tm_hour,t->tm_min,t->tm_sec,STR[4],STR[2],&path[0],user,domain,hitsize);

                        flag=send_mysql_query(conn,&str[0]);
                        if(PRINT!=0||DEBUG!=0)
                          printf("update db: %d-%d-%d %d:%d:%d %s/%s %s %s\n",t->tm_year+1900,t->tm_mon+1,t->tm_mday,t->tm_hour,t->tm_min,t->tm_sec,user,domain,STR[4],&path[0]);
                        if(flag!=0)
                          {
                            printf("error: %s\n",&str[0]);
                            printf("Error %u (%s)\n",mysql_errno(conn),mysql_error(conn));
                            NEWENDVALUE=ENDVALUE2;
                            SaveNewEndFileValue(conn2, NEWENDVALUE);
                            for(i=0;i<samsuserscount;i++)
                              {
  	                        sprintf(&str[0],"UPDATE %s.squidusers SET size='%20.0f',hit='%20.0f' WHERE id='%s'",conf.samsdb,users[i].size,users[i].hit,users[i].id);
                                flag=send_mysql_query(conn2,&str[0]);
                                if(DEBUG!=0)
                                  printf("update user traffic size: %s/%s %20.0f %20.0f\n",users[i].domain,users[i].user,users[i].size,users[i].hit);
	                      }    
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
                         ENDVALUE2=ftell(finp);
                         SaveNewEndFileValue(conn2, ENDVALUE2);

                        if(DEBUG>0)
                          {
                            printf("database appended\n");
                          }
		                  
                      }
                 }

               ENDVALUE2=ftell(finp);
            }
	}
      fclose(finp);
      if(DEBUG!=0)
         printf("\n");
      for(i=0;i<samsuserscount;i++)
         {
            if(users[i].updated>0)
	      {
  	         sprintf(&str[0],"UPDATE %s.squidusers SET size='%.0f',hit='%.0f' WHERE id='%s'",conf.samsdb,users[i].size,users[i].hit,users[i].id);
                 flag=send_mysql_query(conn2,&str[0]);
                 if(PRINT!=0)
                   printf("flag=%d %s\n",flag,&str[0]);
                 if(DEBUG!=0)
                   printf("update SAMS user %s/%s  traffic: %.0f %.0f\n",users[i].domain,users[i].user,users[i].size,users[i].hit);
	      }	   
	 }    
      SaveNewEndFileValue(conn2, NEWENDVALUE);
    }
}





void LoadFile(MYSQL *conn,char *filename)
{
  char *user=NULL,*domain=NULL,*status=NULL;
  FILE *finp;
  int i,flag,flag0;
  int str7len=0;
  time_t tt;
  struct tm *t;
  long hitsize;
  long count;
  double size;
  int samsuser;
  MYSQL_RES *res;
  MYSQL_ROW row;
  
  int userflag=0;

  if((finp=fopen( filename, "r" )))
    {

      if(DEBUG>0)
        {
          printf("open SQUID cache file: %s\n",filename);
        }
      count=0;
      while(feof(finp)==0)
        {

           count++;
           fgets( &buf[0], BUFFER_SIZE-1, finp );
           for(i=0;i<strlen(&buf[0]);i++)
             {
               if(iscntrl(buf[i])!=0)
                  break;
             }
           if(DEBUG>0)
             {
               printf("\n%ld string: %s",count,&buf[0]);
             }
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
                               printf("found user: %s/%s \n",domain,user);
                             userflag=1;
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
                            printf("found user: %s/%s \n",domain,user);
                          userflag=1;
		       }	  
                  }
		
                tt=atol(STR[0]);
                t=localtime(&tt);
/**********/
                status=strtok(STR[3],"/");
                if(strstr( status, "_HIT" )!=0&&strstr( status, "_NEGATIVE_" )==0)
                  {
                    hitsize=atof(STR[4]);
                  }
                LOCALURL=TestLocalURL(STR[6]);
                if(strcmp( status, "TCP_DENIED" )!=0&&strcmp( status, "UDP_DENIED" )!=0&&userflag!=0&&LOCALURL==0)
                  {

                    size=atof(STR[4]);
                    if(DEBUG!=0)
                      printf(" %s/%s, ip=%s traffic size=%10f \n",users[samsuser-1].domain,users[samsuser-1].user,(char*)users[samsuser-1].ip,size);

                    ReplaceURL(STR[6],user,domain);
                    sprintf(&str[0],"INSERT INTO %s.cache SET date='%d-%d-%d',time='%d:%d:%d',size='%s',ipaddr='%s',url='%s',user='%s',domain='%s',hit='%lu'",conf.logdb,t->tm_year+1900,t->tm_mon+1,t->tm_mday,t->tm_hour,t->tm_min,t->tm_sec,STR[4],STR[2],&path[0],user,domain,hitsize);
                    flag=send_mysql_query(conn,&str[0]);
                    if(PRINT!=0||DEBUG!=0)
                       printf("%ld: %d-%d-%d %d:%d:%d %s/%s %s %s\n",count,t->tm_year+1900,t->tm_mon+1,t->tm_mday,t->tm_hour,t->tm_min,t->tm_sec,user,domain,STR[4],&path[0]);
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
        }
      fclose(finp);
    }
}



int main (int argc, char *argv[])
{
  int i,k,j,EXPORT,tc;
  MYSQL *conn,*conn2;
  MYSQL_RES *res, *res2;
  MYSQL_ROW row, row2;
  char *filename=NULL;
  char *date1=NULL;
  char *date2=NULL;
  int flag;
  time_t tt,tt2;
  struct tm *t, *t2;
  int pid;
  char real[6];
  //int week[12]={31,28,31,30,31,30,31,31,30,31,30,31};
  
  if(DEBUG>0)
    printf("\n");
  EXPORT=0;
  EMPTY=0;
  DNSDB=0;
  DNSDBMAX=50;
  for(i=0;i<argc;i++)
     {
       if(strstr(argv[i],"--help")!=0||strstr(argv[i],"-h")!=0)
          {
            printf("Usage: sams [options]\n");
            printf(" -h, --help         show this message.\n");
            printf(" -p, --print        print additional information.\n");
            printf(" -c, --clearall     clear counters.\n");
            printf(" -t     clear counters.\n");
            printf(" -d, --debug        print debug message.\n");
            printf(" --export=date:date export squid log database into file (not gzipped!).\n");
            printf("                    for example: --export=2005-01-01:2005-01-31.\n");
            printf(" --empty            export and empty squid log database.\n");
            printf(" -V, --version      Print version.\n");
            exit(0);
          }
       if(strstr(argv[i],"--version")!=0||strstr(argv[i],"-V")!=0)
          {
            printf("Version %s\n", VERSION);
	    exit(0);
          }
       if(strstr(argv[i],"--file")!=0||strstr(argv[i],"-f")!=0)
          {
            LOADFILE=1;
            strtok(argv[i],"=");
            filename=strtok(NULL,"=");
          }
       if(strstr(argv[i],"--export")!=0)
          {
            EXPORT=1;
	    strtok(argv[i],"=");
            filename=strtok(NULL,"=");
            date1=strtok(filename,":");
            date2=strtok(NULL,":");
	    //printf("%s %s %s\n",filename,date1,date2);
          }
       if(strstr(argv[i],"--empty")!=0)
          {
            EMPTY=1;
          }
       if(strstr(argv[i],"--print")!=0||strstr(argv[i],"-p")!=0)
          {
            PRINT=1;
          }
       if(strstr(argv[i],"--clearall")!=0||strstr(argv[i],"-c")!=0)
          {
            CLEAR=1;
          }
       if(strstr(argv[i],"-t")!=0)
          {
            TCLEAR=1;
          }
       if(strstr(argv[i],"--debug")!=0||strstr(argv[i],"-d")!=0)
          {
            DEBUG=1;
          }
#if defined linux
       if(strstr(argv[i],"--ostype")!=0)
          {
             printf("It's LINUX \n");
          }
#endif
#if defined FREEBSD
       if(strstr(argv[i],"--ostype")!=0)
          {
             printf("It's FreeBSD \n");
          }
#endif
     }

  openlog("samslogparser",LOG_PID | LOG_CONS , LOG_DAEMON);
//  openlog("samslogparser",LOG_PID | LOG_CONS );

  readconf();

  conn = do_connect(conf.host, conf.user, conf.passwd, conf.samsdb, def_port_num, def_socket_name, 0);
  if (conn == NULL)
                    exit(1);
  conn2 = do_connect(conf.host, conf.user, conf.passwd, conf.logdb, def_port_num, def_socket_name, 0);
  if (conn2 == NULL)
                    exit(1);
  if(CLEAR==1)
     {
       sprintf(&str[0],"UPDATE %s.squidusers SET size='0',hit='0',enabled='1' WHERE enabled>='0'",conf.samsdb);
       flag=send_mysql_query(conn2,&str[0]);
       if(flag!=0)
         {
           printf("error: can't clear user traffic counter\n");
         }
       else
         {
           sprintf(&str[0],"INSERT INTO %s.reconfig SET number='%d',action='reconfig',service='squid'",conf.samsdb,conf.cachenum);
           flag=send_mysql_query(conn2,&str[0]);
           printf("SAMS: users traffic counter is cleaned\n");
         }
       exit(1);
     }

  if(TCLEAR==1)
     {
       tc=0;
       tt=time(NULL);
       t=localtime(&tt);
       sprintf(&str[0],"SELECT period,name,nick FROM %s.shablons WHERE clrdate<='%d-%d-%d'&&clrdate>'0000-00-00'&&period!='M'&&period!='W'",conf.samsdb,t->tm_year+1900,t->tm_mon+1,t->tm_mday);
       flag=send_mysql_query(conn2,&str[0]);
       res=mysql_store_result(conn2);
       for(i=0;i<mysql_num_rows(res);i++)
          {
             row=mysql_fetch_row(res);
	     tc++;
	     if(DEBUG>0)
	        printf("Perod %d: %d days. Traffic cleaned\n", i, atoi(row[0]));
             sprintf(&str[0],"UPDATE %s.squidusers SET size='0',hit='0',enabled='1' WHERE enabled>='0'&&shablon='%s' ",conf.samsdb,row[1]);
             flag=send_mysql_query(conn2,&str[0]);

             tt2=tt+60*60*24*atoi(row[0]);
             t2=localtime(&tt2);

	     sprintf(&str[0],"UPDATE %s.shablons SET clrdate='%d-%d-%d' WHERE name='%s'",conf.samsdb,t2->tm_year+1900,t2->tm_mon+1,t2->tm_mday,row[1]);
	     flag=send_mysql_query(conn2,&str[0]);
             sprintf(&str[0],"Traffic clean. Template %s",row[2]);
	     AddLog(conn2,0,"samsdaemon",&str[0]);
	  }
       sprintf(&str[0],"SELECT period,name,nick FROM %s.shablons WHERE period='W'&&clrdate<='%d-%d-%d'",conf.samsdb,t->tm_year+1900,t->tm_mon+1,t->tm_mday);
       flag=send_mysql_query(conn2,&str[0]);
       res=mysql_store_result(conn2);
       for(i=0;i<mysql_num_rows(res);i++)
          {
             row=mysql_fetch_row(res);
	     tc++;
	     if(DEBUG>0)
	        printf("Perod %d: Week. Traffic cleaned\n", i);
             sprintf(&str[0],"UPDATE %s.squidusers SET size='0',hit='0',enabled='1' WHERE enabled>='0'&&shablon='%s' ",conf.samsdb,row[1]);
             flag=send_mysql_query(conn2,&str[0]);

             k=t->tm_wday;
	     if(k==0)
	       k=7;
	     tt2=tt+60*60*24*(8-k);
             t2=localtime(&tt2);

	     sprintf(&str[0],"UPDATE %s.shablons SET clrdate='%d-%d-%d' WHERE name='%s'",conf.samsdb,t2->tm_year+1900,t2->tm_mon+1,t2->tm_mday,row[1]);
	     flag=send_mysql_query(conn2,&str[0]);
             sprintf(&str[0],"Traffic clean. Template %s",row[2]);
	     AddLog(conn2,0,"samsdaemon",&str[0]);
          }

       sprintf(&str[0],"SELECT period,name,nick FROM %s.shablons WHERE period='M'&&clrdate<='%d-%d-%d'",conf.samsdb,t->tm_year+1900,t->tm_mon+1,t->tm_mday);
       flag=send_mysql_query(conn2,&str[0]);
       res=mysql_store_result(conn2);
       for(i=0;i<mysql_num_rows(res);i++)
          {
             row=mysql_fetch_row(res);
	     tc++;
	     if(DEBUG>0)
	        printf("Perod %d: Month. Traffic cleaned\n", i);
             sprintf(&str[0],"UPDATE %s.squidusers SET size='0',hit='0',enabled='1' WHERE enabled>='0'&&shablon='%s' ",conf.samsdb,row[1]);
             flag=send_mysql_query(conn2,&str[0]);

             j=0;
             if(t->tm_mon<11)
	       k=t->tm_mon+1;
	     else
	       {
	         k=1;  
		 j=1;
	       } 
	     sprintf(&str[0],"UPDATE %s.shablons SET clrdate='%d-%d-%d' WHERE name='%s'",conf.samsdb,t->tm_year+1900+j,k,1,row[1]);
	     flag=send_mysql_query(conn2,&str[0]);
             sprintf(&str[0],"Traffic clean. Template %s",row[2]);
	     AddLog(conn2,0,"samsdaemon",&str[0]);
          }

       if(tc>0)
         {
	   sprintf(&str[0],"INSERT INTO %s.reconfig SET number='%d',action='reconfig',service='squid'",conf.samsdb,conf.cachenum);
           flag=send_mysql_query(conn2,&str[0]);
	 
	 }
       exit(0); 
     }
 
  if(EXPORT==1)
     {
       ExportDB(date1,date2,conn2);
       exit(0);
     }

  pid = getpid();
  if(DEBUG==1)
    {
      printf("Starting process: pid = %d\n",pid);
    }  
  if(access("/var/run/sams.pid",F_OK)==0)
    {
      if(TestPID("/var/run/sams.pid")!=0)
         exit(0);
    }
  if(SavePID(pid,"/var/run/sams.pid")!=0)
      exit(0);

  /* Чтение конфигурации SAMS */
  if(CLEAR==0)
    {
      sprintf(&str[0],"SELECT endvalue,auth,ntlmdomain,realsize,checkdns,loglevel,udscript,adminaddr FROM %s.sams",conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      row=mysql_fetch_row(res);
      IP=0;
      NCSA=0;
      NTLM=0; 

      if(strcmp(row[2],"Y")==0)
        {
          NTLMDOMAIN=1;
        }
	
      if(conf.cachenum==0)
        {
          if(DEBUG>0)
            printf("Cache %d\n", conf.cachenum);
          ENDVALUE=txt2digit(row[0]);
        }
      else
        {
          if(DEBUG>0)
            printf("Cache %d\n", conf.cachenum);
          sprintf(&str[0],"SELECT endvalue FROM %s.proxyes WHERE id='%d'", conf.samsdb, conf.cachenum);
          flag=send_mysql_query(conn,&str[0]);
          res2=mysql_store_result(conn);
          row2=mysql_fetch_row(res2);
          ENDVALUE=txt2digit(row2[0]);
          mysql_free_result(res2);
        }


      if(LOADFILE==0)
        NEWENDVALUE=GetNewEndValue();
      if(DEBUG>0)
        {
          printf("Reading file: start=%ld length=%ld\n",ENDVALUE,NEWENDVALUE);
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
          /* Скрипт, выполняемый при отключении пользователей */
          if((UDSCRIPTFILE=(char *)malloc((sizeof(char))*(strlen(WEBINTERFACEPATH)+strlen("/src/script/")+strlen(row[6])+1)))==NULL)
            {
              printf("Not enought memory to allocate buffer\n");
              exit(1);
             }
	  
	  sprintf(UDSCRIPTFILE,"%s/src/script/%s",WEBINTERFACEPATH,row[6]);
        }
      if(DEBUG>0)
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
	}  
      mysql_free_result(res);


   }
 
  /* Чтение конфигурации SAMS - END */

  sprintf(&str[0],"Starting, pid=%d",pid);
  AddLog(conn,4,"sams",&str[0]);

  tt=time(NULL);
  t=localtime(&tt);

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
  //sprintf(&str[0],"SELECT nick,domain,ip,ipmask,enabled,size,quotes,id,hit FROM %s.squidusers",conf.samsdb);
  sprintf(&str[0],"SELECT squidusers.nick,squidusers.domain,squidusers.ip,squidusers.ipmask,squidusers.enabled,squidusers.size,squidusers.quotes,squidusers.id,squidusers.hit,squidusers.shablon,shablons.auth FROM %s.squidusers LEFT JOIN %s.shablons ON squidusers.shablon=shablons.name",conf.samsdb,conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  for(i=0;i<samsuserscount;i++)
     {
        row=mysql_fetch_row(res);
        strncpy(users[i].user,str2lower(row[0]),25);
        strncpy(users[i].domain,str2lower(row[1]),25);

	if(row[10] == NULL) 
	  {
	     printf("Error: User %s has wrong shablon! Please fix it before using SAMS!\n", users[i].user);
	     if(DEBUG==1)
	       {
		  printf("Error: Shablon %s not exist on not configured!", row[9]);
	       }
             exit(1);
	  }
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
	users[i].updated=0;

	users[i].ipauth=0;
	users[i].ntlmauth=0;
	users[i].adldauth=0;
	users[i].ncsaauth=0;
        //sprintf(&str[0],"%s",row[10]);

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
             printf(" %d %10.0f %12.0f %s %s\n",users[i].enabled,users[i].traffic,users[i].quote,users[i].id,row[10]);

          }
     }

  if((dns=(struct dns_cache *)malloc(sizeof(struct dns_cache)*(DNSDBMAX)))==NULL)
     {
       printf("Not enought memory to allocate buffer\n");
       exit(1);
     }

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
         if(strlen(row[0])>0)
	   {
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




  if(ENDVALUE>NEWENDVALUE&&CLEAR==0&&LOADFILE==0)
    {
       ENDVALUE=0;
    }
  
  
  if(DEBUG==1)
    {
       printf("end=%lu newend=%lu clear=%u loadfile=%u\n",ENDVALUE,NEWENDVALUE,CLEAR,LOADFILE);
    }   
  if(NEWENDVALUE>ENDVALUE&&CLEAR==0&&LOADFILE==0)
    {
       if(DEBUG==1)
         printf("Reading new data from %s/%s\n",conf.logdir,conf.logfile);
       ReadNewData(conn,conn2);
    }
  else
    {
      if(LOADFILE==1)
        {
          LoadFile(conn,filename);

        }
      else
        {
          if(DEBUG==1)
	    printf("No new values...\n");
	} 
    }


  sprintf(&str[0],"Stopped, pid=%d",pid);
  AddLog(conn,4,"sams",&str[0]);

  if(UDSCRIPT>0)
    free(UDSCRIPTFILE);
  free(users);
  free(local);
  free(dns);
  free(RUCm);
  freeconf();

 closelog();
 return(0);
}
