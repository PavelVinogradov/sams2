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
#include <ctype.h>
#include <dirent.h>
#include <fcntl.h>
#include <math.h>
#include <mysql.h>
#include <signal.h>
#include <string.h>
#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <sys/wait.h>
#include <sys/param.h>
#include <sys/un.h>
#include <syslog.h>
#include <time.h>
#include <unistd.h>

#include "config.h"
#include "define.h"
#include "tools.h"


int ANALOG, DISKRET, COUNTCLEAN,RECODE,DPOOLS,SQUIDBASE, ADLD;
int sams_step;
int local_ip, local_url;
int disabled_ip, disabled_id;

sig_atomic_t child_exit_status;
static char str[BUFFER_SIZE];
static char squiduser[128];
//int week;



/*************************************************************
               Если редиректор - SquidGuard       
**************************************************************/

int chSquidGuardConf(MYSQL *conn)
{
  int i, j, flag, deny;
  char redirect_to[BUFFER_SIZE];
  //char str[BUFFER_SIZE];
  char shablonname[256];
  MYSQL_ROW row,row2;
  MYSQL_RES *res,*res2;
  FILE *fout,*finp;
  
  deny=0;
  /* создаем SquidGuard squidguard.conf      */
  if(RGUARD==1)
    {

      sprintf(&shablonname[0],"%s/squidGuard.bak",conf.sgdbpath);
      sprintf(&redirect_to[0],"cp %s/squidGuard.conf %s", conf.sgdbpath, &shablonname[0]);
      system(&redirect_to[0]);
      if((finp=fopen(&shablonname[0], "rt" ))==NULL)
        {
          //printf("Don't open file %s\n",&filefrom[0]);
          return(0);
        }

  
      sprintf(&shablonname[0],"%s/squidGuard.conf",conf.sgdbpath);
      if((fout=fopen(&shablonname[0], "wt" ))==NULL)
        {
          //printf("Don't open file %s\n",&shablonname[0]);
          return(0);
        }
      while((i=feof(finp))==0)
        {
           fgets( &str[0], BUFFER_SIZE-1, finp ); 
//           fprintf(fout,"READ EOF: %d",i); 
           if(strstr( &str[0], "sams" )==NULL)
             {
               fprintf(fout,"%s",&str[0]);
             }
           strcpy(&str[0],"\0");
        }
      fclose(finp);
      sprintf(&str[0],"SELECT shablons.name,shablons.days,shablons.shour,shablons.smin,shablons.ehour,shablons.emin FROM %s.shablons LEFT JOIN %s.squidusers ON shablons.name=squidusers.shablon WHERE squidusers.nick!='NULL' GROUP BY shablons.name",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      for(i=0;i<mysql_num_rows(res);i++)
         {
            row=mysql_fetch_row(res);

            sprintf(&str[0],"SELECT squidusers.ip,squidusers.nick,shablons.auth FROM %s.squidusers LEFT JOIN %s.shablons ON squidusers.shablon=shablons.name WHERE squidusers.shablon='%s'&&squidusers.enabled='1'",conf.samsdb,conf.samsdb,row[0]);
            flag=send_mysql_query(conn,&str[0]);
            res2=mysql_store_result(conn);
            if(mysql_num_rows(res2)>0)
	      {
                if(atoi(row[4])>=atoi(row[2]))
	          {
                    fprintf(fout,"time sams_%s_time { %30s#sams\n",row[0]," ");
                    fprintf(fout,"weekly %s ",row[1]);
                    if(strlen(row[2])<2)
	              fprintf(fout,"0");
	            fprintf(fout,"%s:",row[2]);
                    if(strlen(row[3])<2)
	              fprintf(fout,"0");
	            fprintf(fout,"%s-",row[3]);
                    if(strlen(row[4])<2)
	              fprintf(fout,"0");
	            fprintf(fout,"%s:",row[4]);
                    if(strlen(row[5])<2)
	              fprintf(fout,"0");
	            fprintf(fout,"%s ",row[5]);
                    fprintf(fout,"  %30s#sams\n"," ");
                    fprintf(fout,"} %50s#sams\n"," ");
                  }
	        else
	          {
                    fprintf(fout,"time sams_%s_time_1 { %30s#sams\n",row[0]," ");
                    fprintf(fout,"weekly %s ",row[1]);
                    if(strlen(row[2])<2)
	              fprintf(fout,"0");
	            fprintf(fout,"%s:",row[2]);
                    if(strlen(row[3])<2)
	              fprintf(fout,"0");
	            fprintf(fout,"%s-24:00",row[3]);
                    fprintf(fout,"  %30s#sams\n"," ");
                    fprintf(fout,"} %50s#sams\n"," ");

                    fprintf(fout,"time sams_%s_time_2 { %30s#sams\n",row[0]," ");
                    fprintf(fout,"weekly %s ",row[1]);
	              fprintf(fout,"00:00-");
                    if(strlen(row[4])<2)
	              fprintf(fout,"0");
	            fprintf(fout,"%s:",row[4]);
                    if(strlen(row[5])<2)
	              fprintf(fout,"0");
	            fprintf(fout,"%s ",row[5]);
                    fprintf(fout,"  %30s#sams\n"," ");
                    fprintf(fout,"} %50s#sams\n"," ");
                  }
                fprintf(fout,"src sams_%s { %30s#sams\n",row[0]," ");
	        for(j=0;j<mysql_num_rows(res2);j++)
                  {
                    row2=mysql_fetch_row(res2);
		    if(strcmp(row2[2],"ip")==0)
                      fprintf(fout,"         ip %s %30s#sams\n",row2[0]," ");
                    if(strcmp(row2[2],"ncsa")==0||strcmp(row2[2],"ntlm")==0||strcmp(row2[2],"adld")==0)
                      fprintf(fout,"         user %s %30s#sams\n",row2[1]," ");
                  }
                fprintf(fout,"} %50s#sams\n"," ");
	      }	
	    mysql_free_result(res2);

         }
       mysql_free_result(res);


            fprintf(fout,"src sams_disabled { #sams\n");
            sprintf(&str[0],"SELECT squidusers.ip,squidusers.nick,shablons.auth FROM %s.squidusers LEFT JOIN %s.shablons ON squidusers.shablon=shablons.name WHERE squidusers.enabled!='1' ",conf.samsdb,conf.samsdb);
            flag=send_mysql_query(conn,&str[0]);
            res2=mysql_store_result(conn);
            for(j=0;j<mysql_num_rows(res2);j++)
              {
                row2=mysql_fetch_row(res2);
                if(strcmp(row2[2],"ip")==0)
                  fprintf(fout,"         ip %s %30s#sams\n",row2[0]," ");
                if(strcmp(row2[2],"ncsa")==0||strcmp(row2[2],"ntlm")==0||strcmp(row2[2],"adld")==0)
                  fprintf(fout,"         user %s %30s#sams\n",row2[1]," ");
              }
            mysql_free_result(res2);
            fprintf(fout,"} %50s#sams\n"," ");

      sprintf(&str[0],"SELECT * FROM %s.redirect LEFT JOIN %s.sconfig ON redirect.filename=sconfig.set WHERE (type='redir'||type='denied'||type='regex'||type='allow')&&redirect.filename=sconfig.set GROUP BY redirect.filename",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      //count=mysql_num_rows(res);
      for(i=0;i<mysql_num_rows(res);i++)
         {
           row=mysql_fetch_row(res);
           fprintf(fout,"dest sams_%s { %30s#sams\n",row[1]," ");
           fprintf(fout,"urllist %s/_sams_banlists/%s/urls #sams\n", conf.sgdbpath, row[1]);

           if(strstr(row[2],"redir")==0)
             fprintf(fout,"redirect %s/blocked.php?action=sgdenied&user=%s&url=\%s  #sams\n", conf.deniedpath, "%n","%u");
           else 	     
             fprintf(fout,"redirect %s  #_sams_\n", conf.redirpath);
	   fprintf(fout,"} #sams\n");
         }  
       mysql_free_result(res);

      fprintf(fout,"acl {  %30s#sams\n"," ");
      sprintf(&str[0],"SELECT shablons.name,shablons.shour,shablons.ehour FROM %s.shablons LEFT JOIN %s.squidusers ON shablons.name=squidusers.shablon WHERE squidusers.nick!='NULL'&&squidusers.enabled>'0' GROUP BY shablons.name",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      for(i=0;i<mysql_num_rows(res);i++)
         {
            row=mysql_fetch_row(res);
	    if(atoi(row[2])>atoi(row[1]))
	      {
                fprintf(fout,"sams_%s  within sams_%s_time {  %30s#sams\n pass ", row[0], row[0]," ");
                sprintf(&str[0],"SELECT redirect.filename,shablons.alldenied,redirect.type FROM %s.sconfig LEFT JOIN %s.redirect ON sconfig.set = redirect.filename LEFT JOIN %s.shablons ON sconfig.sname=shablons.name WHERE sname = '%s'\n",conf.samsdb,conf.samsdb,conf.samsdb,row[0]);
                flag=send_mysql_query(conn,&str[0]);
                res2=mysql_store_result(conn);
		deny=0;
                for(j=0;j<mysql_num_rows(res2);j++)
                  {
                    row2=mysql_fetch_row(res2);

                    if(atoi(row2[1])==0 && strstr( row2[2], "allow" )!=0)
		      {
		        fprintf(fout,"sams_%s ",row2[0]);
		      }
                    if(atoi(row2[1])==0 && strstr( row2[2], "allow" )==0)
		      {
		        fprintf(fout,"!sams_%s ",row2[0]);
		      } 	
                    if(atoi(row2[1])==1 && strstr( row2[2], "allow" )!=0)
		      {
		        fprintf(fout,"sams_%s ",row2[0]);
                        deny=1;
		      }
	          }
		if(deny==1)  
		  fprintf(fout,"none  %20s#sams\n"," ");
                else
                  fprintf(fout,"all  %20s#sams\n"," ");
                fprintf(fout,"redirect %s/blocked.php?action=sgdenied&user=%s&url=\%s  #sams\n", conf.deniedpath, "%n","%u");
                fprintf(fout,"}  %30s#sams\n"," ");
                mysql_free_result(res2);
	      }
	    else //если период времени задан с вечера до утра
	      {
                fprintf(fout,"sams_%s  within sams_%s_time_1 {  %30s#sams\n pass ", row[0], row[0]," ");
                sprintf(&str[0],"SELECT redirect.filename,shablons.alldenied,redirect.type FROM %s.sconfig LEFT JOIN %s.redirect ON sconfig.set = redirect.filename LEFT JOIN %s.shablons ON sconfig.sname=shablons.name WHERE sname = '%s'\n",conf.samsdb,conf.samsdb,conf.samsdb,row[0]);
                flag=send_mysql_query(conn,&str[0]);
                res2=mysql_store_result(conn);
                for(j=0;j<mysql_num_rows(res2);j++)
                  {
                    row2=mysql_fetch_row(res2);

                    if(atoi(row2[1])==0 && strstr( row2[2], "allow" )!=0)
		      {
		        fprintf(fout,"sams_%s ",row2[0]);
		      }
                    if(atoi(row2[1])==0 && strstr( row2[2], "allow" )==0)
		      {
		        fprintf(fout,"!sams_%s ",row2[0]);
		      } 	
                    if(atoi(row2[1])==1 && strstr( row2[2], "allow" )!=0)
		      {
		        fprintf(fout,"sams_%s ",row2[0]);
                        deny=1;
		      }
	          }
		if(deny==1)  
		  fprintf(fout,"none  %20s#sams\n"," ");
                else
                  fprintf(fout,"all  %20s#sams\n"," ");
                fprintf(fout,"redirect %s/blocked.php?action=sgdenied&user=%s&url=\%s  #sams\n", conf.deniedpath, "%n","%u");
                fprintf(fout,"}  %30s#sams\n"," ");
                mysql_free_result(res2);

                fprintf(fout,"sams_%s  within sams_%s_time_2 {  %30s#sams\n pass ", row[0], row[0]," ");
                sprintf(&str[0],"SELECT redirect.filename,shablons.alldenied,redirect.type FROM %s.sconfig LEFT JOIN %s.redirect ON sconfig.set = redirect.filename LEFT JOIN %s.shablons ON sconfig.sname=shablons.name WHERE sname = '%s'\n",conf.samsdb,conf.samsdb,conf.samsdb,row[0]);
                flag=send_mysql_query(conn,&str[0]);
                res2=mysql_store_result(conn);
                for(j=0;j<mysql_num_rows(res2);j++)
                  {
                    row2=mysql_fetch_row(res2);

                    if(atoi(row2[1])==0 && strstr( row2[2], "allow" )!=0)
		      {
		        fprintf(fout,"sams_%s ",row2[0]);
		      }
                    if(atoi(row2[1])==0 && strstr( row2[2], "allow" )==0)
		      {
		        fprintf(fout,"!sams_%s ",row2[0]);
		      } 	
                    if(atoi(row2[1])==1 && strstr( row2[2], "allow" )!=0)
		      {
		        fprintf(fout,"sams_%s ",row2[0]);
                        deny=1;
		      }
	          }
		if(deny==1)  
		  fprintf(fout,"none  %20s#sams\n"," ");
                else
                  fprintf(fout,"all  %20s#sams\n"," ");
                fprintf(fout,"redirect %s/blocked.php?action=sgdenied&user=%s&url=\%s  #sams\n", conf.deniedpath, "%n","%u");
                fprintf(fout,"}  %30s#sams\n"," ");
                mysql_free_result(res2);
	      }
         }
       mysql_free_result(res);

      fprintf(fout,"sams_disabled {  %30s#sams\n pass  %30s#sams\n"," "," ");
      fprintf(fout,"pass none%30s#sams\n"," ");
      fprintf(fout,"redirect %s/blocked.php?action=sgdisable&user=%s&url=%s  #sams\n", conf.deniedpath, "%i","%u");
      fprintf(fout,"}%30s#sams\n"," ");
      
      fprintf(fout,"default {  %30s#sams\n pass  %30s#sams\n"," "," ");
      fprintf(fout,"pass none%30s#sams\n"," ");
      fprintf(fout,"redirect %s/blocked.php?action=sgdisable&user=%s&url=%s  #sams\n", conf.deniedpath, "%i","%u");
      fprintf(fout,"}%30s#sams\n"," ");
      fprintf(fout,"}  %30s#sams\n"," ");

      fclose(fout);
    }
  /* END    создаем SquidGuard squidguard.conf      */
 return(0);
}
/*************************************************************
    END           Если редиректор - SquidGuard       
**************************************************************/


/*************************************************************
               Если редиректор - REJIK       
**************************************************************/

int chRejikConf(MYSQL *conn)
{
  int i,flag;
//  count,i,j, flag, ucount;
  char redirect_to[BUFFER_SIZE];
  char str[BUFFER_SIZE];
  char shablonname[256];
  MYSQL_ROW row=NULL;
//  ,row2=NULL;
  MYSQL_RES *res=NULL,*res2=NULL;
  FILE *fout=NULL,*finp=NULL;

  if(RREJIK==1)
    {
 /* создаем REJIK redirector.conf      */
 
      sprintf(&shablonname[0],"%s/redirector.bak",conf.rejikpath);
      sprintf(&redirect_to[0],"cp %s/redirector.conf %s", conf.rejikpath, &shablonname[0]);
      system(&redirect_to[0]);
      if((finp=fopen(&shablonname[0], "rt" ))==NULL)
        {
          //printf("Don't open file %s\n",&filefrom[0]);
          return(0);
        }

  
      sprintf(&shablonname[0],"%s/redirector.conf",conf.rejikpath);
      if((fout=fopen(&shablonname[0], "wt" ))==NULL)
        {
          //printf("Don't open file %s\n",&shablonname[0]);
          return(0);
        }
      while((i=feof(finp))==0)
        {
           fgets( &str[0], BUFFER_SIZE-1, finp ); 
           if(strstr( &str[0], "sams" )==NULL)
             {
               fprintf(fout,"%s",&str[0]);
             }
           strcpy(&str[0],"\0");
        }
      fclose(finp);

      sprintf(&str[0],"SELECT shablons.name,shablons.nick,shablons.auth,shablons.alldenied FROM %s.shablons LEFT JOIN %s.squidusers ON shablons.name=squidusers.shablon WHERE squidusers.enabled='1' GROUP BY shablons.name",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      for(i=0;i<mysql_num_rows(res);i++)
         {
            row=mysql_fetch_row(res);

            sprintf(&str[0],"SELECT urls.url,redirect.type,sconfig.sname FROM %s.sconfig LEFT JOIN %s.urls ON sconfig.set=urls.type LEFT JOIN %s.redirect ON urls.type=redirect.filename WHERE sname='%s'&&redirect.type='redir'",conf.samsdb,conf.samsdb,conf.samsdb, row[0]);
            flag=send_mysql_query(conn,&str[0]);
            res2=mysql_store_result(conn);
	    if(mysql_num_rows(res2)>0)
	      {
                fprintf(fout,"<_sams_%s_redir>\n",row[0]);
	        if(strcmp(row[2],"ip")==0)
                  fprintf(fout,"work_ip f:%s/%s.sams\n", conf.rejikpath, row[0]);
                if(strcmp(row[2],"ncsa")==0||strcmp(row[2],"ntlm")==0||strcmp(row[2],"adld")==0)
                  fprintf(fout,"work_id f:%s/%s.sams\n", conf.rejikpath, row[0]);
	        fprintf(fout,"ban_dir %s/_sams_banlists/%s_redir\n", conf.rejikpath, row[0]);
		fprintf(fout,"url %s  #_sams_\n", conf.redirpath); 
	      }
            mysql_free_result(res2);

            if(atoi(row[3])==0)
	      {
	        sprintf(&str[0],"SELECT urls.url,redirect.type,sconfig.sname FROM %s.sconfig LEFT JOIN %s.urls ON sconfig.set=urls.type LEFT JOIN %s.redirect ON urls.type=redirect.filename WHERE sname='%s'&&redirect.type='denied'",conf.samsdb,conf.samsdb,conf.samsdb, row[0]);
                flag=send_mysql_query(conn,&str[0]);
                res2=mysql_store_result(conn);
	        if(mysql_num_rows(res2)>0)
	          {
                    fprintf(fout,"<_sams_%s_denied>\n",row[0]);
	            if(strcmp(row[2],"ip")==0)
                      fprintf(fout,"work_ip f:%s/%s.sams\n", conf.rejikpath, row[0]);
                    if(strcmp(row[2],"ncsa")==0||strcmp(row[2],"ntlm")==0||strcmp(row[2],"adld")==0)
                      fprintf(fout,"work_id f:%s/%s.sams\n", conf.rejikpath, row[0]);
	            fprintf(fout,"ban_dir %s/_sams_banlists/%s_denied\n", conf.rejikpath, row[0]);
                    fprintf(fout,"url %s/blocked.php?action=rejikdenied&url=#URL#  #_sams_\n", conf.deniedpath);
	          }
                mysql_free_result(res2);
	        sprintf(&str[0],"SELECT urls.url,redirect.type,sconfig.sname FROM %s.sconfig LEFT JOIN %s.urls ON sconfig.set=urls.type LEFT JOIN %s.redirect ON urls.type=redirect.filename WHERE sname='%s'&&redirect.type='regex'",conf.samsdb,conf.samsdb,conf.samsdb, row[0]);
                flag=send_mysql_query(conn,&str[0]);
                res2=mysql_store_result(conn);
	        if(mysql_num_rows(res2)>0)
	          {
                    fprintf(fout,"<_sams_%s_regex>\n",row[0]);
	            if(strcmp(row[2],"ip")==0)
                      fprintf(fout,"work_ip f:%s/%s.sams\n", conf.rejikpath, row[0]);
                    if(strcmp(row[2],"ncsa")==0||strcmp(row[2],"ntlm")==0||strcmp(row[2],"adld")==0)
                      fprintf(fout,"work_id f:%s/%s.sams\n", conf.rejikpath, row[0]);
	            fprintf(fout,"ban_dir %s/_sams_banlists/%s_regex\n", conf.rejikpath, row[0]);
                    fprintf(fout,"url %s/blocked.php?action=rejikdenied&url=#URL#  #_sams_\n", conf.deniedpath);
	          }
                mysql_free_result(res2);
	      }
	    else  
	      {
	        sprintf(&str[0],"SELECT urls.url,redirect.type,sconfig.sname FROM %s.sconfig LEFT JOIN %s.urls ON sconfig.set=urls.type LEFT JOIN %s.redirect ON urls.type=redirect.filename WHERE sname='%s'&&redirect.type='allow'",conf.samsdb,conf.samsdb,conf.samsdb, row[0]);
		flag=send_mysql_query(conn,&str[0]);
                res2=mysql_store_result(conn);
	        if(mysql_num_rows(res2)>0)
	          {
                    fprintf(fout,"<_sams_%s_allow>\n",row[0]);
	            if(strcmp(row[2],"ip")==0)
                      fprintf(fout,"work_ip f:%s/%s.sams\n", conf.rejikpath, row[0]);
                    if(strcmp(row[2],"ncsa")==0||strcmp(row[2],"ntlm")==0||strcmp(row[2],"adld")==0)
                      fprintf(fout,"work_id f:%s/%s.sams\n", conf.rejikpath, row[0]);
	            fprintf(fout,"ban_dir %s/_sams_banlists/%s_allow\n", conf.rejikpath, row[0]);
                    fprintf(fout,"url %s/blocked.php?action=rejikdenied&url=#URL#  #_sams_\n", conf.deniedpath);
                    fprintf(fout,"reverse  #_sams_\n");
	          }
                mysql_free_result(res2);
	      }
         }
      mysql_free_result(res);
      if(disabled_ip>0)
        {
                    fprintf(fout,"<_sams_disabled_ip>\n");
                    fprintf(fout,"work_ip f:%s/disabled_ip.sams\n", conf.rejikpath);
	            fprintf(fout,"ban_dir %s/_sams_banlists/localhosts\n", conf.rejikpath);
                    fprintf(fout,"url %s/blocked.php?action=rejikdisable&url=#URL#&user=#IDENT#&ip=#IP#  #_sams_\n", conf.deniedpath);
                    fprintf(fout,"reverse  #_sams_\n");
	
	}
      if(disabled_id>0)
        {
                    fprintf(fout,"<_sams_disabled_id>\n");
                    fprintf(fout,"work_id f:%s/disabled_id.sams\n", conf.rejikpath);
	            fprintf(fout,"ban_dir %s/_sams_banlists/localhosts\n", conf.rejikpath);
                    fprintf(fout,"url %s/blocked.php?action=rejikdisable&url=#URL#&user=#IDENT#&ip=#IP#  #_sams_\n", conf.deniedpath);
                    fprintf(fout,"reverse  #_sams_\n");
	
	}


    }
 fclose(fout);
 return(0);
}

/*************************************************************
    END           Если редиректор - REJIK       
**************************************************************/


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

int SaveBackUp(int sm, MYSQL *conn)
{
  time_t tt;
  struct tm *t;
  int month, year;
  char *filename;
  FILE *fout;
  MYSQL_RES *res;
  MYSQL_ROW row;
  pid_t childpid;
  
  childpid=fork();
  if(childpid==0)
    {
       if(DEBUG>0)
         printf("Starting child process \n");

       tt=time(NULL);
       t=localtime(&tt);
       year=t->tm_year+1900;
       month=t->tm_mon;
       if((month-sm)<0)
         {
           year-=1;
           month=month-sm+12;
         } 
       else
           month-=sm;
       if((filename=(char *)malloc((sizeof(char))*(strlen(WEBINTERFACEPATH)+strlen("/backup/")+50)))==NULL)
         {
           printf("Not enought memory to allocate buffer\n");
           exit(1);
         }
       sprintf(filename,"%s/backup/squidlogbase_to_%d-%d-31",WEBINTERFACEPATH,year,month);
       if(DEBUG>0)
         printf("Created file %s\n",filename);

       if((fout=fopen( filename, "wt" ))==NULL)
         {
              printf("Don't create file %s\n",filename);
              return(1);
         }
       fprintf(fout,"USE %s;\n\n",conf.logdb);
       fprintf(fout,"\n### CACHESUM table\n");
       sprintf(&str[0],"SELECT * FROM %s.cachesum WHERE date<='%d-%d-31' ORDER BY date",conf.logdb,year,month);
       send_mysql_query(conn,&str[0]);
       res=mysql_store_result(conn);
       while((row=mysql_fetch_row(res)))
         {
           fprintf(fout,"INSERT INTO %s.cachesum SET date='%s',user='%s',domain='%s',size='%s',hit='%s';\n",conf.logdb,row[0],row[1],row[2],row[3],row[4]);
         }
       mysql_free_result(res);

       fprintf(fout,"\n### CACHE table\n");
	
       sprintf(&str[0],"SELECT * FROM %s.cache WHERE date<='%d-%d-31' ORDER BY date",conf.logdb,year,month);
       send_mysql_query(conn,&str[0]);
       res=mysql_store_result(conn);
       while((row=mysql_fetch_row(res)))
         {
           fprintf(fout,"INSERT INTO %s.cache SET date='%s',time='%s',user='%s',domain='%s',size='%s',ipaddr='%s',period='%s',url='%s',hit='%s',method='%s';\n",conf.logdb,row[0],row[1],row[2],row[3],row[4],row[5],row[6],row[7],row[8],row[9]);
         }
       mysql_free_result(res);
       if(DEBUG>0)
         printf("Empty %s base to %d-%d-31 \n",conf.logdb,year,month);
       sprintf(&str[0],"DELETE FROM %s.cachesum WHERE cachesum.date<='%d-%d-31'",conf.logdb,year,month);
       send_mysql_query(conn,&str[0]);
       sprintf(&str[0],"DELETE FROM %s.cache WHERE cache.date<='%d-%d-31'",conf.logdb,year,month);
       send_mysql_query(conn,&str[0]);
       sprintf(&str[0],"OPTIMIZE TABLE %s.cache, %s.cachesum",conf.logdb, conf.logdb);
       send_mysql_query(conn,&str[0]);

       fclose(fout);
       free(filename);  
       exit(0);
    }

  return(0);
}


int RecodeFile(char *string,char *finpname,char *foutname)
{
  int finp, fout,i,j,len;
  char s2[BUFFER_SIZE];  
  finp=0;
  fout=0;

  len=strlen(conf.recode);
  if( strstr( conf.recode, "%finp")!=0 && strstr( conf.recode, "#" )==NULL )
     {
       finp=1;
     }
  if( strstr( conf.recode, "%fout" )!=0 && strstr( conf.recode, "#" )==NULL )
     {
       fout=10;
     }
  strncpy(string,"\0",1);
  for(j=0,i=0;i<len;i++)
     {
       
       if(conf.recode[i]==' ')
         {
	   s2[j++]='\0';
	   j=0;
	   if(!strcasecmp( &s2[0], "%finp" ))
	     {
	        strcpy(&s2[0],finpname);
	     }
	   if(!strcasecmp( &s2[0], "%fout" ))
	     {
	        strcpy(&s2[0],foutname);
	     }
           strcat(string,&s2[0]);
           strcat(string," ");
	 }
       else
         {
           s2[j++]=conf.recode[i];
	 }    
     }
  s2[j++]='\0';
  j=0;
  if(!strcasecmp( &s2[0], "%finp" ))
    {
       strcpy(&s2[0],finpname);
    }
  if(!strcasecmp( &s2[0], "%fout" ))
    {
       strcpy(&s2[0],foutname);
    }
  strcat(string,&s2[0]);
  if(DEBUG==1)
     printf("recode command = %s\n",string);
  
  return(finp+fout);

}

void clean_up_child_process(int signal_number)
{
  int status;
  wait(&status);
  child_exit_status = status;
}

int ChangeSQUIDconf(MYSQL *conn)
{
  char outstr[BUFFER_SIZE];
  char filefrom[256];
  char fileto[256];
  char method[128];
  FILE *finp,*fout;
  time_t tt;
  struct tm *t;
  MYSQL_ROW row,row2;
  MYSQL_RES *res,*res2;
  int flag,count,acount;
  struct stat s;
  int mode,i;
  char *suser, *suser2;

  sprintf(&filefrom[0],"%s/squid.conf",conf.squidrootdir);
  sprintf(&fileto[0],"%s/squid.conf.bak",conf.squidrootdir);

  if(DEBUG==1)
     printf("squid configure file: %s/squid.conf\n",conf.squidrootdir);
  lstat(&filefrom[0],&s);
  if(!s.st_mode&S_IRUSR) printf("1");
//  if(S_IRUSR(s.st_mode)) printf("1");
   
  mode=0;
  if(s.st_mode&S_IRUSR) mode+=256;
  if(s.st_mode&S_IWUSR) mode+=128;
  if(s.st_mode&S_IXUSR) mode+=64;
  if(s.st_mode&S_IRGRP) mode+=32;
  if(s.st_mode&S_IWGRP) mode+=16;
  if(s.st_mode&S_IXGRP) mode+=8;
  if(s.st_mode&S_IROTH) mode+=4;
  if(s.st_mode&S_IWOTH) mode+=2;
  if(s.st_mode&S_IXOTH) mode+=1;

  if((finp=fopen(&filefrom[0], "rt" ))==NULL)
    {
      //printf("Don't open file %s\n",&filefrom[0]);
      return(0);
    }
  if((fout=fopen(&fileto[0], "wt" ))==NULL)
    {
      //printf("Don't open file %s\n",&fileto[0]);
      return(0);
    }
  while((i=feof(finp))==0)
    {
       fgets( &buf[0], BUFFER_SIZE-1, finp ); 
       if((strstr( &buf[0], "_sams_" )==NULL&&strstr( &buf[0], "delay_pools" )==NULL&&strstr( &buf[0], "delay_class" )==NULL&&strstr(&buf[0], "delay_access" )==NULL&&strstr( &buf[0], "delay_parameters" )==NULL)||strstr( &buf[0], "#" )!=NULL)
         {
           if(strstr( &buf[0], "created by SAMS" )==NULL)
	     {
	       fprintf(fout,"%s",&buf[0]);
	     }  
	   
         }
       strcpy(&buf[0],"\0");
    }
  fclose(fout);
  fclose(finp);
  unlink(&filefrom[0]);


  if((finp=fopen(&fileto[0], "rt" ))==NULL)
    {
      //printf("Don't open file %s\n",&fileto[0]);
      return(0);
    }
  if((fout=fopen(&filefrom[0], "wt" ))==NULL)
    {
      //printf("Don't open file %s\n",&filefrom[0]);
      return(0);
    }
  tt=time(NULL);
  t=localtime(&tt);

  fprintf(fout,"# created by SAMS _sams_ %d-%d-%d %d:%d:%d\n",t->tm_year+1900,t->tm_mon+1,t->tm_mday,t->tm_hour,t->tm_min,t->tm_sec);
  while(feof(finp)==0)
    {
       fgets( &buf[0], BUFFER_SIZE-1, finp ); 
       fprintf(fout,"%s",&buf[0]);

       if( strstr( &buf[0], "cache_effective_user" )!=0 && strstr( &buf[0], "#" )==0 )
         {
	   stripws(&buf[0]);
	   suser=strstr( &buf[0], "cache_effective_user" ) + strlen("cache_effective_user");
           suser2=strtok(suser," ");
           strncpy(&squiduser[0],suser2,32);
	 }
       //############## TAG ACL ###################################################################
       if(strstr( &buf[0], "TAG:" )!=0&&strstr( &buf[0], "acl" )!=0&&strstr( &buf[0], "_" )==0)
         {
           if(DEBUG==1)
             printf("TAG: acl found... START\n");
           
//#####
	   sprintf(&str[0],"SELECT * FROM %s.shablons WHERE auth='ip'",conf.samsdb);

	   flag=send_mysql_query(conn,&str[0]);
           res=mysql_store_result(conn);
           while((row=mysql_fetch_row(res)))
	     {
               count=0;
               if(RSAMS==1||RGUARD==1||RREJIK==1)
                  sprintf(&str[0],"SELECT count(*) FROM %s.squidusers WHERE shablon='%s'",conf.samsdb,row[0]);
               else   
		  sprintf(&str[0],"SELECT count(*) FROM %s.squidusers WHERE shablon='%s'&&enabled>'0'",conf.samsdb,row[0]);
               flag=send_mysql_query(conn,&str[0]);
               res2=mysql_store_result(conn);
               row2=mysql_fetch_row(res2);
               count=atoi(row2[0]);
               mysql_free_result(res2);
               if(DEBUG==1)
                 printf("%d users found in template %s (%s), create ACL\n",count,row[0],row[1]);
               if(count>0)
	         {
		   if(strcmp(row[5],"ntlm")==0||strcmp(row[5],"ncsa")==0||strcmp(row[5],"adld")==0)
                     sprintf(&method[0],"proxy_auth");
		   else  
                     sprintf(&method[0],"src");
		   fprintf(fout,"acl _sams_%s %s \"%s/%s.sams\" \n",row[0],&method[0],conf.squidrootdir,row[0]);
    
		   if(RSAMS==0&&RGUARD==0)    
		    {
                       if(atoi(row[8])<atoi(row[10]))
		         {
		           fprintf(fout,"acl _sams_%s_time time %s %s:%s-%s:%s\n",row[0],row[7],row[8],row[9],row[10],row[11]);
			 }
		       else
		         {
		           fprintf(fout,"acl _sams_%s_time_1 time %s %s:%s-24:0\n",row[0],row[7],row[8],row[9]);
		           fprintf(fout,"acl _sams_%s_time_2 time %s 0:0-%s:%s\n",row[0],row[7],row[10],row[11]);
			 }	   
		     }  

		 }
	     }
           mysql_free_result(res);

	   if(ADLD==1)
	     sprintf(&str[0],"SELECT * FROM %s.shablons WHERE auth!='ip' ORDER BY auth DESC",conf.samsdb);
           else
	     sprintf(&str[0],"SELECT * FROM %s.shablons WHERE auth!='ip' ORDER BY auth",conf.samsdb);

//#####
//	   if(ADLD==1)
//	     sprintf(&str[0],"SELECT * FROM %s.shablons ORDER BY auth DESC",conf.samsdb);
//           else
//	     sprintf(&str[0],"SELECT * FROM %s.shablons ORDER BY auth",conf.samsdb);

	   flag=send_mysql_query(conn,&str[0]);
           res=mysql_store_result(conn);
           while((row=mysql_fetch_row(res)))
	     {
               count=0;
               if(RSAMS==1||RGUARD==1||RREJIK==1)
                  sprintf(&str[0],"SELECT count(*) FROM %s.squidusers WHERE shablon='%s'",conf.samsdb,row[0]);
               else   
		  sprintf(&str[0],"SELECT count(*) FROM %s.squidusers WHERE shablon='%s'&&enabled>'0'",conf.samsdb,row[0]);
               flag=send_mysql_query(conn,&str[0]);
               res2=mysql_store_result(conn);
               row2=mysql_fetch_row(res2);
               count=atoi(row2[0]);
               mysql_free_result(res2);
               if(DEBUG==1)
                 printf("%d users found in template %s (%s), create ACL\n",count,row[0],row[1]);
               if(count>0)
	         {
		   if(strcmp(row[5],"ntlm")==0||strcmp(row[5],"ncsa")==0||strcmp(row[5],"adld")==0)
                     sprintf(&method[0],"proxy_auth");
		   else  
                     sprintf(&method[0],"src");
		   fprintf(fout,"acl _sams_%s %s \"%s/%s.sams\" \n",row[0],&method[0],conf.squidrootdir,row[0]);
    
		   if(RSAMS==0&&RGUARD==0)    
		    {
                       if(atoi(row[8])<atoi(row[10]))
		         {
		           fprintf(fout,"acl _sams_%s_time time %s %s:%s-%s:%s\n",row[0],row[7],row[8],row[9],row[10],row[11]);
			 }
		       else
		         {
		           fprintf(fout,"acl _sams_%s_time_1 time %s %s:%s-24:0\n",row[0],row[7],row[8],row[9]);
		           fprintf(fout,"acl _sams_%s_time_2 time %s 0:0-%s:%s\n",row[0],row[7],row[10],row[11]);
			 }	   
		     }  

		 }
	     }
           mysql_free_result(res);

           // списки расширений файлов 
           sprintf(&str[0],"SELECT * FROM %s.redirect LEFT JOIN %s.sconfig ON redirect.filename=sconfig.set WHERE redirect.type='files'&&redirect.filename=sconfig.set GROUP BY filename",conf.samsdb,conf.samsdb);
           flag=send_mysql_query(conn,&str[0]);
           res=mysql_store_result(conn);
           while((row=mysql_fetch_row(res)))
	     {
               count=0;
               sprintf(&str[0],"SELECT count(urls.url) FROM %s.urls LEFT JOIN %s.redirect ON urls.type=redirect.filename WHERE redirect.type='files'&&redirect.filename='%s'",conf.samsdb,conf.samsdb,row[1]);
               flag=send_mysql_query(conn,&str[0]);
               res2=mysql_store_result(conn);
               row2=mysql_fetch_row(res2);
               count=atoi(row2[0]);
               mysql_free_result(res2);
               if(count>0)
	         {
                   fprintf(fout,"acl _sams_%s urlpath_regex -i \"%s/%s.sams\"\n",row[1],conf.squidrootdir,row[1]);
                   //if(DEBUG==1)
                   //  printf("acl _sams_%s urlpath_regex -i \"%s/%s.sams\"\n",row[1],conf.squidrootdir,row[1]);
		 }
	     }
           mysql_free_result(res);

           // списки запрета доступа 
           sprintf(&str[0],"SELECT redirect.* FROM %s.redirect LEFT JOIN %s.sconfig ON redirect.filename=sconfig.set WHERE (redirect.type='denied'||redirect.type='regex')&&redirect.filename=sconfig.set GROUP BY filename",conf.samsdb,conf.samsdb);
           flag=send_mysql_query(conn,&str[0]);
           res=mysql_store_result(conn);
           while((row=mysql_fetch_row(res)))
	     {
               count=0;
               sprintf(&str[0],"SELECT count(urls.url) FROM %s.urls LEFT JOIN %s.redirect ON urls.type=redirect.filename WHERE redirect.type='denied'&&redirect.filename='%s'",conf.samsdb,conf.samsdb,row[1]);
               flag=send_mysql_query(conn,&str[0]);
               res2=mysql_store_result(conn);
               row2=mysql_fetch_row(res2);
               count=atoi(row2[0]);
               mysql_free_result(res2);
               if(count>0&&(RSQUID!=0||RNONE!=0))
	         {
                   fprintf(fout,"acl _sams_%s url_regex \"%s/%s.sams\"\n",row[1],conf.squidrootdir,row[1]);
                   if(DEBUG==1)
                     printf("acl _sams_%s url_regex \"%s/%s.sams\"\n",row[1],conf.squidrootdir,row[1]);
		 }
	     }
           mysql_free_result(res);

           sprintf(&str[0],"SELECT * FROM %s.redirect LEFT JOIN %s.sconfig ON redirect.filename=sconfig.set WHERE type='regex'&&redirect.filename=sconfig.set GROUP BY redirect.filename ",conf.samsdb,conf.samsdb);
           //printf("str=%s\n",&str[0]);
           flag=send_mysql_query(conn,&str[0]);
           res=mysql_store_result(conn);
           while((row=mysql_fetch_row(res)))
	     {
               count=0;
               sprintf(&str[0],"SELECT count(urls.url) FROM %s.urls left join %s.redirect ON urls.type=redirect.filename WHERE redirect.type = 'regex'&&redirect.filename='%s'",conf.samsdb,conf.samsdb,row[1]);
               flag=send_mysql_query(conn,&str[0]);
               res2=mysql_store_result(conn);
               row2=mysql_fetch_row(res2);
               count=atoi(row2[0]);
               mysql_free_result(res2);
               if(count>0)
	         {
                   if(RSAMS==0&&RREJIK==0&&RGUARD==0)
		     {
                       fprintf(fout,"acl _sams_%s urlpath_regex \"%s/%s.sams\"\n",row[1],conf.squidrootdir,row[1]);
                       if(DEBUG==1)
                         printf("acl _sams_%s urlpath_regex \"%s/%s.sams\"\n",row[1],conf.squidrootdir,row[1]);
		     } 
		 }
	     }
           mysql_free_result(res);

           // списки разрешения доступа
           sprintf(&str[0],"SELECT * FROM %s.redirect WHERE type='allow'",conf.samsdb);
           flag=send_mysql_query(conn,&str[0]);
           res=mysql_store_result(conn);
           while((row=mysql_fetch_row(res)))
	     {
               count=0;
//               sprintf(&str[0],"SELECT count(urls.url) FROM %s.urls left join %s.redirect ON urls.type=redirect.filename WHERE redirect.type = 'allow'&&redirect.filename='%s'",conf.samsdb,conf.samsdb,row[1]);
               sprintf(&str[0],"SELECT count(urls.url) FROM %s.urls LEFT JOIN %s.sconfig ON urls.type=sconfig.set WHERE sconfig.set='%s'",conf.samsdb,conf.samsdb,row[1]);
               flag=send_mysql_query(conn,&str[0]);
               res2=mysql_store_result(conn);
               row2=mysql_fetch_row(res2);
               count=atoi(row2[0]);
               mysql_free_result(res2);
               if(count>0&&(RSQUID!=0||RNONE!=0))
	         {
                   fprintf(fout,"acl _sams_%s url_regex \"%s/%s.sams\"\n",row[1],conf.squidrootdir,row[1]);
                   if(DEBUG==1)
                     printf("acl _sams_%s url_regex \"%s/%s.sams\"\n",row[1],conf.squidrootdir,row[1]);
		 }
	     }
           mysql_free_result(res);
           
           // списки отключенных пользователей IP
	   if(disabled_ip>0&&(RSQUID!=0||RNONE!=0))
             fprintf(fout,"acl _sams_disabled_ip src \"%s/disabled_ip.sams\"\n",conf.squidrootdir);

           // списки локальных доменов IP
	   if(local_ip>0&&(RSQUID!=0||RNONE!=0))
             fprintf(fout,"acl _sams_local_ip dst \"%s/local_ip.sams\"\n",conf.squidrootdir);

           // списки отключенных пользователей NTLM, NCSA
           if(disabled_id>0&&(RSQUID!=0||RNONE!=0))
             fprintf(fout,"acl _sams_disabled_id proxy_auth \"%s/disabled_id.sams\"\n",conf.squidrootdir);

           // списки локальных доменов NTLM, NCSA
           if(local_url>0&&(RSQUID!=0||RNONE!=0))
             fprintf(fout,"acl _sams_local_url dstdomain \"%s/local_url.sams\"\n",conf.squidrootdir);

           if(DEBUG==1)
             printf("TAG: acl END\n\n");
         }

       //############## TAG http_access ###################################################################

//       if(strstr( &buf[0], "#  TAG: http_access" )!=0&&strstr( &buf[0], "#  TAG: http_access2" )==0)
       if(strstr( &buf[0], "TAG:" )!=0&& strstr( &buf[0], "http_access" )!=0&& strstr( &buf[0], "2" )==0)
         {
           if(DEBUG==1)
             printf("TAG: http_access found...  START\n");
//#######	   
	     sprintf(&str[0],"SELECT * FROM %s.shablons WHERE auth='ip'",conf.samsdb);
           flag=send_mysql_query(conn,&str[0]);
           res=mysql_store_result(conn);
           while((row=mysql_fetch_row(res)))
	     {
               count=0;
               if(RSAMS==1||RGUARD==1||RREJIK==1)   
		  sprintf(&str[0],"SELECT count(*) FROM %s.squidusers WHERE shablon='%s'",conf.samsdb,row[0]);
	       else
	          sprintf(&str[0],"SELECT count(*) FROM %s.squidusers WHERE shablon='%s'&&enabled>'0'",conf.samsdb,row[0]);
               flag=send_mysql_query(conn,&str[0]);
               res2=mysql_store_result(conn);
               row2=mysql_fetch_row(res2);
               count=atoi(row2[0]); 
               mysql_free_result(res2);
               if(DEBUG==1)
                 printf("%d users found in the template %s (%s), create access rights\n",count,row[0],row[1]);

               if(count>0)
                 {

                   acount=atoi(row[14]);
                   if(acount>0)
		     {
                       if(RSQUID==1||RNONE==1)
		         {
			   sprintf(&outstr[0],"http_access deny _sams_%s ",row[0]);
		         }  
                       if(RSAMS==1||RGUARD==1)
		         {
			   sprintf(&outstr[0],"http_access allow _sams_%s ",row[0]);
			 }  
		     }
                   else
		     {
                       if(RSAMS==1||RGUARD==1)
		         {
                           sprintf(&outstr[0],"http_access allow _sams_%s ",row[0]);
			 }  
		       else
		         {   
                           sprintf(&outstr[0],"http_access allow _sams_%s ",row[0]);
			 }  
		     }  
		     
                           sprintf(&str[0],"SELECT sconfig.sname, sconfig.set, urls.url FROM %s.sconfig LEFT JOIN %s.urls ON sconfig.set=urls.type LEFT JOIN %s.redirect ON redirect.filename=sconfig.set WHERE sconfig.sname='%s'&&urls.url!='NULL'&&redirect.type='files' GROUP BY sconfig.set",conf.samsdb,conf.samsdb,conf.samsdb,row[0]);
                           flag=send_mysql_query(conn,&str[0]);
                           res2=mysql_store_result(conn);
                           while((row2=mysql_fetch_row(res2)))
	                     {
                               sprintf(&outstr[0],"%s !_sams_%s", &outstr[0], row2[1]);
                             }
                           mysql_free_result(res2);
		      
                   if(RSQUID==1||RNONE==1)
		     {
                           sprintf(&str[0],"SELECT sconfig.sname, sconfig.set, redirect.filename, redirect.type FROM %s.sconfig left join %s.redirect ON sconfig.set = redirect.filename WHERE sname = '%s'&&redirect.type='allow'\n",conf.samsdb,conf.samsdb,row[0]);
                           flag=send_mysql_query(conn,&str[0]);
                           res2=mysql_store_result(conn);
                           while((row2=mysql_fetch_row(res2)))
	                     {
                               if(acount>0)
			         sprintf(&outstr[0],"%s !_sams_%s", &outstr[0], row2[1]);
			       else
			         sprintf(&outstr[0],"%s _sams_%s", &outstr[0], row2[1]);
                             }
                           mysql_free_result(res2);
                       if(acount==0)
		         {
                           sprintf(&str[0],"SELECT sconfig.sname, sconfig.set, urls.url FROM %s.sconfig LEFT JOIN %s.urls ON sconfig.set=urls.type LEFT JOIN %s.redirect ON redirect.filename=sconfig.set WHERE sconfig.sname='%s'&&urls.url!='NULL'&&(redirect.type='denied'||redirect.type='regex') GROUP BY sconfig.set",conf.samsdb,conf.samsdb,conf.samsdb,row[0]);
                           flag=send_mysql_query(conn,&str[0]);
                           res2=mysql_store_result(conn);
                           while((row2=mysql_fetch_row(res2)))
	                     {
                               sprintf(&outstr[0],"%s !_sams_%s", &outstr[0], row2[1]);
                             }
                           mysql_free_result(res2);
			 }  
                     }

                   if(RSQUID==1||RNONE==1||RREJIK==1)
		     {
                       if(atoi(row[8])<atoi(row[10]))
		         sprintf(&outstr[0],"%s _sams_%s_time ", &outstr[0], row[0]);
		       else 
		         {	 
		           sprintf(&outstr[0],"%s _sams_%s_time_1 ", &outstr[0], row[0]);
		           sprintf(&outstr[0],"%s _sams_%s_time_2 ", &outstr[0], row[0]);
			 }  
		     } 

		   fprintf(fout,"%s \n", &outstr[0]);
		 }
	     }
           mysql_free_result(res);

	   if(disabled_ip>0)
	     {
                if(RNONE==1||RSQUID==1)
		  { 
		    fprintf(fout,"http_access deny _sams_disabled_ip ");
                    if(local_ip>0)
		      fprintf(fout,"!_sams_local_ip ");
                    if(local_url>0)
		      fprintf(fout,"!_sams_local_url ");
		    fprintf(fout,"\n");
		  }
	     }

	   if(ADLD==1)
	     sprintf(&str[0],"SELECT * FROM %s.shablons WHERE auth!='ip' ORDER BY auth DESC",conf.samsdb);
           else
	     sprintf(&str[0],"SELECT * FROM %s.shablons WHERE auth!='ip' ORDER BY auth",conf.samsdb);

           flag=send_mysql_query(conn,&str[0]);
           res=mysql_store_result(conn);
           while((row=mysql_fetch_row(res)))
	     {
               count=0;
               if(RSAMS==1||RGUARD==1||RREJIK==1)   
		  sprintf(&str[0],"SELECT count(*) FROM %s.squidusers WHERE shablon='%s'",conf.samsdb,row[0]);
	       else
	          sprintf(&str[0],"SELECT count(*) FROM %s.squidusers WHERE shablon='%s'&&enabled>'0'",conf.samsdb,row[0]);
               flag=send_mysql_query(conn,&str[0]);
               res2=mysql_store_result(conn);
               row2=mysql_fetch_row(res2);
               count=atoi(row2[0]); 
               mysql_free_result(res2);
               if(DEBUG==1)
                 printf("%d users found in the template %s (%s), create access rights\n",count,row[0],row[1]);

               if(count>0)
                 {

                   acount=atoi(row[14]);
                   if(acount>0)
		     {
                       if(RSQUID==1||RNONE==1)
		         {
			   sprintf(&outstr[0],"http_access deny _sams_%s ",row[0]);
		         }  
                       if(RSAMS==1||RGUARD==1||RREJIK==1)
		         {
			   sprintf(&outstr[0],"http_access allow _sams_%s ",row[0]);
			 }  
		     }
                   else
		     {
                       if(RSAMS==1||RGUARD==1)
		         {
                           //fprintf(fout,"http_access allow _sams_%s \n",row[0]);
                           sprintf(&outstr[0],"http_access allow _sams_%s ",row[0]);
			 }  
		       else
		         {   
                           sprintf(&outstr[0],"http_access allow _sams_%s ",row[0]);
			 }  
		     }  
		     
                           sprintf(&str[0],"SELECT sconfig.sname, sconfig.set, urls.url FROM %s.sconfig LEFT JOIN %s.urls ON sconfig.set=urls.type LEFT JOIN %s.redirect ON redirect.filename=sconfig.set WHERE sconfig.sname='%s'&&urls.url!='NULL'&&redirect.type='files' GROUP BY sconfig.set",conf.samsdb,conf.samsdb,conf.samsdb,row[0]);
                           flag=send_mysql_query(conn,&str[0]);
                           res2=mysql_store_result(conn);
                           while((row2=mysql_fetch_row(res2)))
	                     {
                               sprintf(&outstr[0],"%s !_sams_%s", &outstr[0], row2[1]);
                             }
                           mysql_free_result(res2);
		      
                   if(RSQUID==1||RNONE==1)
		     {
                           sprintf(&str[0],"SELECT sconfig.sname, sconfig.set, redirect.filename, redirect.type FROM %s.sconfig left join %s.redirect ON sconfig.set = redirect.filename WHERE sname = '%s'&&redirect.type='allow'\n",conf.samsdb,conf.samsdb,row[0]);
                           flag=send_mysql_query(conn,&str[0]);
                           res2=mysql_store_result(conn);
                           while((row2=mysql_fetch_row(res2)))
	                     {
                               if(acount>0)
			         sprintf(&outstr[0],"%s !_sams_%s", &outstr[0], row2[1]);
			       else
			         sprintf(&outstr[0],"%s _sams_%s", &outstr[0], row2[1]);
                             }
                           mysql_free_result(res2);
                       if(acount==0)
		         {
                           sprintf(&str[0],"SELECT sconfig.sname, sconfig.set, urls.url FROM %s.sconfig LEFT JOIN %s.urls ON sconfig.set=urls.type LEFT JOIN %s.redirect ON redirect.filename=sconfig.set WHERE sconfig.sname='%s'&&urls.url!='NULL'&&(redirect.type='denied'||redirect.type='regex') GROUP BY sconfig.set",conf.samsdb,conf.samsdb,conf.samsdb,row[0]);
                           flag=send_mysql_query(conn,&str[0]);
                           res2=mysql_store_result(conn);
                           while((row2=mysql_fetch_row(res2)))
	                     {
                               sprintf(&outstr[0],"%s !_sams_%s", &outstr[0], row2[1]);
                             }
                           mysql_free_result(res2);
			 }  
                     }

                   if(RSQUID==1||RNONE==1||RREJIK==1)
		     {
                       if(atoi(row[8])<atoi(row[10]))
		         {	 
		         sprintf(&outstr[0],"%s _sams_%s_time ", &outstr[0], row[0]);
			 }  
		       else 
		         {	 
		           sprintf(&outstr[0],"%s _sams_%s_time_1 ", &outstr[0], row[0]);
		           sprintf(&outstr[0],"%s _sams_%s_time_2 ", &outstr[0], row[0]);
			 }  
		     } 

		   fprintf(fout,"%s \n", &outstr[0]);
		 }
	     }
           mysql_free_result(res);

	   if(disabled_id>0)
	     {
                if(RNONE==1||RSQUID==1)
		  { 
                    fprintf(fout,"http_access deny _sams_disabled_id ");
                    if(local_ip>0)
		      fprintf(fout,"!_sams_local_ip ");
                    if(local_url>0)
		      fprintf(fout,"!_sams_local_url ");
		    fprintf(fout,"\n");
		  }  
	     }

           if(DEBUG==1)
             printf("TAG: http_access END\n");
         }


       if(strstr( &buf[0], "#  TAG: delay_class" )!=0&&DPOOLS==1)
         {
           if(DEBUG==1)
             printf("TAG: delay_class found\n");

           count=0;
           sprintf(&str[0],"SELECT squidusers.nick,squidusers.shablon,shablons.name,shablons.shablonpool,shablons.userpool FROM %s.squidusers LEFT JOIN %s.shablons ON squidusers.shablon=shablons.name WHERE squidusers.enabled>0 GROUP BY squidusers.shablon",conf.samsdb,conf.samsdb);
           flag=send_mysql_query(conn,&str[0]);
           res=mysql_store_result(conn);
           while((row=mysql_fetch_row(res)))
	     {
               count=count+1;
	     }
           fprintf(fout,"delay_pools %d\n",count);
           //if(DEBUG==1)
           //  printf("delay_pools %d\n",count);
           mysql_free_result(res);
           sprintf(&str[0],"SELECT squidusers.nick,squidusers.shablon,shablons.name,shablons.shablonpool,shablons.userpool FROM %s.squidusers LEFT JOIN %s.shablons ON squidusers.shablon=shablons.name WHERE squidusers.enabled>0 GROUP BY squidusers.shablon",conf.samsdb,conf.samsdb);
           flag=send_mysql_query(conn,&str[0]);
           res=mysql_store_result(conn);
           count=1;
           while((row=mysql_fetch_row(res)))
	     {
               fprintf(fout,"delay_class %d 2\n",count);
               //if(DEBUG==1)
               //  printf("delay_class %d 2\n",count);
	       count=count+1;
	     }
           mysql_free_result(res);

           sprintf(&str[0],"SELECT squidusers.nick,squidusers.shablon,shablons.name,shablons.shablonpool,shablons.userpool FROM %s.squidusers LEFT JOIN %s.shablons ON squidusers.shablon=shablons.name WHERE squidusers.enabled>0 GROUP BY squidusers.shablon",conf.samsdb,conf.samsdb);
           flag=send_mysql_query(conn,&str[0]);
           res=mysql_store_result(conn);
           count=1;
           while((row=mysql_fetch_row(res)))
	     {
               fprintf(fout,"delay_access %d allow _sams_%s\n",count,row[1]);
               fprintf(fout,"delay_access %d deny all\n",count);
               fprintf(fout,"delay_parameters %d %s/%s %s/%s\n",count,row[3],row[3],row[4],row[4]);
               if(DEBUG==1)
	         {
                   fprintf(fout,"delay_access %d allow _sams_%s\n",count,row[1]);
                   fprintf(fout,"delay_access %d deny all\n",count);
                   fprintf(fout,"delay_parameters %d %s/%s %s/%s\n",count,row[3],row[3],row[4],row[4]);
		 }
	       count=count+1;
	     }
           mysql_free_result(res);
         }
      strcpy(&buf[0],"\0");
/*       
*/
    }
 fclose(fout);
 fclose(finp);

 chmod(&filefrom[0],mode);
 chown(&filefrom[0],s.st_uid,s.st_gid);

 return(0);
}



int MakeACLFiles(MYSQL *conn)
{
  int count,i,j,k,ucount,ncsacount;
  char redirect_to[BUFFER_SIZE];
  char str[BUFFER_SIZE];
  char shablonname[256];
  MYSQL_ROW row,row2;
  MYSQL_RES *res,*res2;
  FILE *fout=NULL, *fout2=NULL;
  struct local_url host;
  struct stat s;

  int flag;

  BIGU=0;
  BIGD=0;
  NTLMDOMAIN=0;
  
  sprintf(&str[0],"SELECT redirect_to,bigd,bigu,ntlmdomain FROM %s.sams",conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  row=mysql_fetch_row(res);
  if(strcmp(row[1],"Y")==0)
                             {      BIGD=1;    }
  if(strcmp(row[1],"S")==0)
                             {      BIGD=-1;   }
  if(strcmp(row[1],"N")==0)
                             {      BIGD=0;    }
  if(strcmp(row[1],"A")==0)
                             {      BIGD=100;  }
  if(strcmp(row[2],"Y")==0)
                             {      BIGU=1;    }
  if(strcmp(row[2],"S")==0)
                             {      BIGU=-1;   }
  if(strcmp(row[2],"N")==0)
                             {      BIGU=0;    }
  if(strcmp(row[3],"Y")==0)
                             {      NTLMDOMAIN=1;    }
  strcpy(&redirect_to[0],row[0]);
  mysql_free_result(res);

  /* Если редиректор - SQUID  */
  if(RSQUID==1)
    {
      sprintf(&shablonname[0],"%s/redirector.sams",conf.squidrootdir);
      if((fout=fopen(&shablonname[0], "wt" ))==NULL)
        {
          //printf("Don't open file %s\n",&shablonname[0]);
          return(0);
        }
      fprintf(fout,"#!/usr/bin/perl\n");
      fprintf(fout,"$0 = 'redirect' ;\n");
      fprintf(fout,"$| = 1;\n\n");
      fprintf(fout,"@banners    = (");

      count=0;
      sprintf(&str[0],"SELECT count(urls.url) FROM %s.urls left join %s.redirect ON urls.type = redirect.filename WHERE redirect.type = 'redir'",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      row=mysql_fetch_row(res);
      count=atoi(row[0]);
//?
      mysql_free_result(res);
      
      sprintf(&str[0],"SELECT urls.url, urls.type, redirect.filename, redirect.type FROM %s.urls left join %s.redirect ON urls.type = redirect.filename WHERE redirect.type = 'redir'",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      for(i=0;i<count;i++)
         {
           row=mysql_fetch_row(res);
           fprintf(fout,"'%s',\n",row[0]);
	   //printf("redirect url: %s %s %s %s\n",row[0],row[1],row[2],row[3]);
           
         }
      fprintf(fout,"         'web\\.icq\\.com/client');\n");
      fprintf(fout,"while (<>) {\n");
      fprintf(fout,"    ($url, $who, $ident, $method) = /^(\\S+) (\\S+) (\\S+) (\\S+)$/ ;\n");
      fprintf(fout,"    $url = '%s'\n",&redirect_to[0]);
      fprintf(fout,"      if grep ($url=~/$_/i, @banners) ;\n");
      fprintf(fout,"    print \"$url $who $ident $method \\n\" ;\n");
      fprintf(fout,"}\n");
      mysql_free_result(res);
      fclose(fout);
      chmod(&shablonname[0],0755);
    
      sprintf(&str[0],"Create %s file",&shablonname[0]);
      AddLog(conn,8,"samsdaemon",&str[0]);


    }
  /*END   Если редиректор - SQUID        */




 if(RGUARD==1)
   {
     sprintf(&shablonname[0],"rm -R -f %s/_sams_*",conf.sgdbpath);
     system(&shablonname[0]);
     sprintf(&shablonname[0],"%s/_sams_banlists",conf.sgdbpath);
     flag = mkdir(&shablonname[0], 0755);
     sprintf(&shablonname[0],"chown -R %s %s/_sams_banlists", &squiduser[0], conf.sgdbpath);
     system(&shablonname[0]);
   }


 if(RREJIK==1)
   {
     sprintf(&shablonname[0],"%s/squid.conf",conf.squidrootdir);
     lstat(&shablonname[0],&s);

     sprintf(&shablonname[0],"rm -R -f %s/_sams_*",conf.rejikpath);
     system(&shablonname[0]);
     sprintf(&shablonname[0],"%s/_sams_banlists",conf.rejikpath);
     flag = mkdir(&shablonname[0], 0755);
     chown(&shablonname[0],s.st_uid,s.st_gid);

  /* создаем список локальных доменов  для REJIK      */
     sprintf(&shablonname[0],"%s/_sams_banlists/localhosts", conf.rejikpath);
     flag = mkdir(&shablonname[0], 0755);
     chown(&shablonname[0],s.st_uid,s.st_gid);
     sprintf(&shablonname[0],"%s/urls", &shablonname[0]);
     if((fout=fopen(&shablonname[0], "wt" ))==NULL)
       {
          return(0);
       }
     sprintf(&str[0],"SELECT * FROM %s.urls where type='local'",conf.samsdb);
     flag=send_mysql_query(conn,&str[0]);
     res2=mysql_store_result(conn);
     for(j=0;j<mysql_num_rows(res2);j++)
        {
          row2=mysql_fetch_row(res2);
          fprintf(fout,"%s\n",row2[0]);
        }
      mysql_free_result(res2);
      fclose(fout);
      chown(&shablonname[0],s.st_uid,s.st_gid);

      sprintf(&str[0],"Create %s file",&shablonname[0]);
      AddLog(conn,8,"samsdaemon",&str[0]);
   }

  /***************************************************************************************/
  /*                   Создаем списки расширений файлов                                  */
  /***************************************************************************************/
      sprintf(&str[0],"SELECT * FROM %s.redirect LEFT JOIN %s.sconfig ON redirect.filename=sconfig.set WHERE redirect.type='files'&&redirect.filename=sconfig.set",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      for(i=0;i<mysql_num_rows(res);i++)
         {
           row=mysql_fetch_row(res);
             sprintf(&shablonname[0],"%s/%s.sams",conf.squidrootdir,row[1]);

           sprintf(&str[0],"SELECT count(*) FROM %s.urls where type='%s'",conf.samsdb,row[1]);
           flag=send_mysql_query(conn,&str[0]);
           res2=mysql_store_result(conn);
           row2=mysql_fetch_row(res2);
           ucount=atoi(row2[0]); 
           mysql_free_result(res2);
           if(ucount>0)
             {
               if((fout=fopen(&shablonname[0], "wt" ))==NULL)
                  {
                    //printf("Don't open file %s\n",&shablonname[0]);
                    return(0);
                  }
               sprintf(&str[0],"SELECT * FROM %s.urls where type='%s'",conf.samsdb,row[1]);
               flag=send_mysql_query(conn,&str[0]);
               res2=mysql_store_result(conn);
               for(j=0;j<ucount;j++)
	          {
                    row2=mysql_fetch_row(res2);
                    fprintf(fout,"%s\n",row2[0]);
	          }
               fclose(fout);

               sprintf(&str[0],"Create %s file",&shablonname[0]);
               AddLog(conn,8,"samsdaemon",&str[0]);
               mysql_free_result(res2);
	     }
           //mysql_free_result(res2);
         }  


  /***************************************************************************************/
  /*                   Создаем списки перенаправления запросов                           */
  /***************************************************************************************/
  if(RREJIK==1)
    {
      sprintf(&str[0],"SELECT shablons.name,shablons.nick FROM %s.shablons LEFT JOIN %s.squidusers ON shablons.name=squidusers.shablon WHERE squidusers.enabled='1' GROUP BY shablons.name",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      for(i=0;i<mysql_num_rows(res);i++)
         {
            row=mysql_fetch_row(res);

            sprintf(&str[0],"SELECT urls.url,redirect.type,sconfig.sname FROM %s.sconfig LEFT JOIN %s.urls ON sconfig.set=urls.type LEFT JOIN %s.redirect ON urls.type=redirect.filename WHERE sname='%s'&&redirect.type='redir'",conf.samsdb,conf.samsdb,conf.samsdb, row[0]);
            flag=send_mysql_query(conn,&str[0]);
            res2=mysql_store_result(conn);
	    
	    if(mysql_num_rows(res2)>0)
	      {
	        sprintf(&shablonname[0],"%s/_sams_banlists/%s_redir", conf.rejikpath, row[0]);
                mkdir(&shablonname[0], 0755);
	        sprintf(&shablonname[0],"%s/pcre", &shablonname[0]);
                if((fout=fopen(&shablonname[0], "wt" ))==NULL)
                  {
                    //printf("Don't open file %s\n",&shablonname[0]);
                    return(0);
                  }
                sprintf(&str[0],"Create %s file",&shablonname[0]);
                AddLog(conn,8,"samsdaemon",&str[0]);
      
                for(j=0;j<mysql_num_rows(res2);j++)
                   {
                     row2=mysql_fetch_row(res2);
                     fprintf(fout,"%s\n",row2[0]);
                   }  
                fclose(fout);
	      }
            mysql_free_result(res2);
         }
      mysql_free_result(res);
    }
  else 
    {
      sprintf(&str[0],"SELECT * FROM %s.redirect LEFT JOIN %s.sconfig ON redirect.filename=sconfig.set WHERE redirect.type='redir'&&redirect.filename=sconfig.set",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      for(i=0;i<mysql_num_rows(res);i++)
         {
           row=mysql_fetch_row(res);
           if(RREJIK==1)
             {
	       sprintf(&shablonname[0],"%s/_sams_banlists/%s", conf.rejikpath, row[1]);
               mkdir(&shablonname[0], 0755);
	       sprintf(&shablonname[0],"%s/pcre", &shablonname[0]);
	     }  
           else if(RGUARD==1)
             {
	       sprintf(&shablonname[0],"%s/_sams_banlists/%s", conf.sgdbpath, row[1]);
               mkdir(&shablonname[0], 0755);
	       sprintf(&shablonname[0],"%s/urls", &shablonname[0]);
	     }  
           else
             sprintf(&shablonname[0],"%s/%s.sams",conf.squidrootdir,row[1]);
           sprintf(&str[0],"SELECT count(*) FROM %s.urls where type='%s'",conf.samsdb,row[1]);
           flag=send_mysql_query(conn,&str[0]);
           res2=mysql_store_result(conn);
           row2=mysql_fetch_row(res2);
           ucount=atoi(row2[0]); 
           mysql_free_result(res2);
           if(ucount>0)
             {
               if((fout=fopen(&shablonname[0], "wt" ))==NULL)
                  {
                    //printf("Don't open file %s\n",&shablonname[0]);
                    return(0);
                  }
               sprintf(&str[0],"SELECT * FROM %s.urls where type='%s'",conf.samsdb,row[1]);
               flag=send_mysql_query(conn,&str[0]);
               res2=mysql_store_result(conn);
               for(j=0;j<ucount;j++)
	          {
                    row2=mysql_fetch_row(res2);
                    fprintf(fout,"%s\n",row2[0]);
	          }
               fclose(fout);

               sprintf(&str[0],"Create %s file",&shablonname[0]);
               AddLog(conn,8,"samsdaemon",&str[0]);
               mysql_free_result(res2);
	     }
           //mysql_free_result(res2);
         }  

    }
  /***************************************************************************************/
  /*                           Создаем списки запрета доступа                            */
  /***************************************************************************************/

  if(RREJIK==1)
    {
      sprintf(&str[0],"SELECT shablons.name,shablons.nick FROM %s.shablons LEFT JOIN %s.squidusers ON shablons.name=squidusers.shablon WHERE squidusers.enabled='1' GROUP BY shablons.name",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      for(i=0;i<mysql_num_rows(res);i++)
         {
            row=mysql_fetch_row(res);

            sprintf(&str[0],"SELECT urls.url,redirect.type,sconfig.sname FROM %s.sconfig LEFT JOIN %s.urls ON sconfig.set=urls.type LEFT JOIN %s.redirect ON urls.type=redirect.filename WHERE sname='%s'&&redirect.type='denied'",conf.samsdb,conf.samsdb,conf.samsdb, row[0]);
            flag=send_mysql_query(conn,&str[0]);
            res2=mysql_store_result(conn);
	    if(mysql_num_rows(res2)>0)
	      {
	        sprintf(&shablonname[0],"%s/_sams_banlists/%s_denied", conf.rejikpath, row[0]);
                mkdir(&shablonname[0], 0755);
	        sprintf(&shablonname[0],"%s/urls", &shablonname[0]);
                if((fout=fopen(&shablonname[0], "wt" ))==NULL)
                  {
                    //printf("Don't open file %s\n",&shablonname[0]);
                    return(0);
                  }
                sprintf(&str[0],"Create %s file",&shablonname[0]);
                AddLog(conn,8,"samsdaemon",&str[0]);
      
                for(j=0;j<mysql_num_rows(res2);j++)
                   {
                     row2=mysql_fetch_row(res2);
                     fprintf(fout,"%s\n",row2[0]);
                   }  
                fclose(fout);
	      }
            mysql_free_result(res2);

            sprintf(&str[0],"SELECT urls.url,redirect.type,sconfig.sname FROM %s.sconfig LEFT JOIN %s.urls ON sconfig.set=urls.type LEFT JOIN %s.redirect ON urls.type=redirect.filename WHERE sname='%s'&&redirect.type='regex'",conf.samsdb,conf.samsdb,conf.samsdb, row[0]);
            flag=send_mysql_query(conn,&str[0]);
            res2=mysql_store_result(conn);
	    if(mysql_num_rows(res2)>0)
	      {
	        sprintf(&shablonname[0],"%s/_sams_banlists/%s_regex", conf.rejikpath, row[0]);
                mkdir(&shablonname[0], 0755);
	        sprintf(&shablonname[0],"%s/pcre", &shablonname[0]);
                if((fout=fopen(&shablonname[0], "wt" ))==NULL)
                  {
                    //printf("Don't open file %s\n",&shablonname[0]);
                    return(0);
                  }
                sprintf(&str[0],"Create %s file",&shablonname[0]);
                AddLog(conn,8,"samsdaemon",&str[0]);
      
                for(j=0;j<mysql_num_rows(res2);j++)
                   {
                     row2=mysql_fetch_row(res2);
                     fprintf(fout,"%s\n",row2[0]);
                   }  
                fclose(fout);
	      }
            mysql_free_result(res2);
         }
      mysql_free_result(res);
    }
  else 
    {
      sprintf(&str[0],"SELECT * FROM %s.redirect LEFT JOIN %s.sconfig ON redirect.filename=sconfig.set WHERE (redirect.type='denied'||redirect.type='regex')&&redirect.filename=sconfig.set",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      for(i=0;i<mysql_num_rows(res);i++)
         {
           row=mysql_fetch_row(res);
           if(RREJIK==1)
             {
	       sprintf(&shablonname[0],"%s/_sams_banlists/%s", conf.rejikpath, row[1]);
               mkdir(&shablonname[0], 0755);
	       sprintf(&shablonname[0],"%s/pcre", &shablonname[0]);
	     }  
           else if(RGUARD==1)
             {
	       sprintf(&shablonname[0],"%s/_sams_banlists/%s", conf.sgdbpath, row[1]);
               mkdir(&shablonname[0], 0755);
	       sprintf(&shablonname[0],"%s/urls", &shablonname[0]);
	     }  
           else
             sprintf(&shablonname[0],"%s/%s.sams",conf.squidrootdir,row[1]);
           sprintf(&str[0],"SELECT count(*) FROM %s.urls where type='%s'",conf.samsdb,row[1]);
           flag=send_mysql_query(conn,&str[0]);
           res2=mysql_store_result(conn);
           row2=mysql_fetch_row(res2);
           ucount=atoi(row2[0]); 
           mysql_free_result(res2);
           if(ucount>0)
             {
               if((fout=fopen(&shablonname[0], "wt" ))==NULL)
                  {
                    //printf("Don't open file %s\n",&shablonname[0]);
                    return(0);
                  }
               sprintf(&str[0],"SELECT * FROM %s.urls where type='%s'",conf.samsdb,row[1]);
               flag=send_mysql_query(conn,&str[0]);
               res2=mysql_store_result(conn);
               for(j=0;j<ucount;j++)
	          {
                    row2=mysql_fetch_row(res2);
                    fprintf(fout,"%s\n",row2[0]);
                    //printf("url found: %15s %15s \n",row2[1],row2[0]);
	          }
               mysql_free_result(res2);
               fclose(fout);

               sprintf(&str[0],"Create %s file",&shablonname[0]);
               AddLog(conn,8,"samsdaemon",&str[0]);
	     }
         }  
      mysql_free_result(res);
    } 
 
  /* создаем списки доступ разрешен      */

  if(RREJIK==1)
    {
      sprintf(&str[0],"SELECT shablons.name,shablons.nick FROM %s.shablons LEFT JOIN %s.squidusers ON shablons.name=squidusers.shablon WHERE squidusers.enabled='1' GROUP BY shablons.name",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      for(i=0;i<mysql_num_rows(res);i++)
         {
            row=mysql_fetch_row(res);

            sprintf(&str[0],"SELECT urls.url,redirect.type,sconfig.sname FROM %s.sconfig LEFT JOIN %s.urls ON sconfig.set=urls.type LEFT JOIN %s.redirect ON urls.type=redirect.filename WHERE sname='%s'&&redirect.type='allow'",conf.samsdb,conf.samsdb,conf.samsdb, row[0]);
            flag=send_mysql_query(conn,&str[0]);
            res2=mysql_store_result(conn);
	    
	    if(mysql_num_rows(res2)>0)
	      {
	        sprintf(&shablonname[0],"%s/_sams_banlists/%s_allow", conf.rejikpath, row[0]);
                mkdir(&shablonname[0], 0755);
	        sprintf(&shablonname[0],"%s/urls", &shablonname[0]);
                if((fout=fopen(&shablonname[0], "wt" ))==NULL)
                  {
                    //printf("Don't open file %s\n",&shablonname[0]);
                    return(0);
                  }
                sprintf(&str[0],"Create %s file",&shablonname[0]);
                AddLog(conn,8,"samsdaemon",&str[0]);
      
                for(j=0;j<mysql_num_rows(res2);j++)
                   {
                     row2=mysql_fetch_row(res2);
                     fprintf(fout,"%s\n",row2[0]);
                   }  
                fclose(fout);
	      }
            mysql_free_result(res2);
         }
      mysql_free_result(res);
    }
  else 
    {

      sprintf(&str[0],"SELECT * FROM %s.redirect LEFT JOIN %s.sconfig ON redirect.filename=sconfig.set WHERE redirect.type='allow'&&redirect.filename=sconfig.set",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      for(i=0;i<mysql_num_rows(res);i++)
         {
           row=mysql_fetch_row(res);
           if(RREJIK==1)
             {
	       sprintf(&shablonname[0],"%s/_sams_banlists/%s", conf.rejikpath, row[1]);
               mkdir(&shablonname[0], 0755);
	       sprintf(&shablonname[0],"%s/pcre", &shablonname[0]);
	     }  
           else if(RGUARD==1)
             {
	       sprintf(&shablonname[0],"%s/_sams_banlists/%s", conf.sgdbpath, row[1]);
               mkdir(&shablonname[0], 0755);
	       sprintf(&shablonname[0],"%s/urls", &shablonname[0]);
	     }  
           else
             sprintf(&shablonname[0],"%s/%s.sams",conf.squidrootdir,row[1]);
           //printf("found denied list: %s %s %s\n",row[0],row[1],&shablonname[0]);
           sprintf(&str[0],"SELECT count(*) FROM %s.urls where type='%s'",conf.samsdb,row[1]);
           flag=send_mysql_query(conn,&str[0]);
           res2=mysql_store_result(conn);
           row2=mysql_fetch_row(res2);
           ucount=atoi(row2[0]); 
           //printf("denied list included %d url\n",ucount);
           mysql_free_result(res2);
           if(ucount>0)
             {
               if((fout=fopen(&shablonname[0], "wt" ))==NULL)
                  {
                    //printf("Don't open file %s\n",&shablonname[0]);
                    return(0);
                  }
               sprintf(&str[0],"SELECT * FROM %s.urls where type='%s'",conf.samsdb,row[1]);
               flag=send_mysql_query(conn,&str[0]);
               res2=mysql_store_result(conn);
               for(j=0;j<ucount;j++)
	          {
                    row2=mysql_fetch_row(res2);
                    fprintf(fout,"%s\n",row2[0]);
                    //printf("url found: %15s %15s \n",row2[1],row2[0]);
	          }
               mysql_free_result(res2);
               fclose(fout);

               sprintf(&str[0],"Create %s file",&shablonname[0]);
               AddLog(conn,8,"samsdaemon",&str[0]);
	     }

         }  
      mysql_free_result(res);
    }


/*список локальных хостов*/
 if(RNONE==1||RSQUID==1)
   {
      local_ip=0;
      local_url=0;
      sprintf(&str[0],"SELECT * FROM %s.urls WHERE type='local'",conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      for(j=0;j<mysql_num_rows(res);j++)
        {
          row=mysql_fetch_row(res);
          if(strlen(row[0])>0)
	    {
              host.ipflag=LocalIPAddr(row[0],host.ip,host.mask);
	      if(host.ipflag>0)
	        {
		  if(local_ip==0)
		    {
                      if(RREJIK==1)
	                sprintf(&shablonname[0],"%s/_sams_banlists/local_ip.sams", conf.rejikpath);
                      //else if(RGUARD==1)
	              //  sprintf(&shablonname[0],"%s/_sams_banlists/local_ip.sams", conf.sgdbpath);
		      else
                        sprintf(&shablonname[0],"%s/local_ip.sams",conf.squidrootdir);
		      if((fout=fopen(&shablonname[0], "wt" ))==NULL)
                        {
                          //printf("Don't open file %s\n",&shablonname[0]);
                          return(0);
                        }
                      //fprintf(fout,"%s\n",row[0]);
                      fprintf(fout,"%d.%d.%d.%d/%d.%d.%d.%d\n", host.ip[0],host.ip[1],host.ip[2],host.ip[3], host.mask[0],host.mask[1],host.mask[2],host.mask[3]);
		    }
		  else
		    {
                      fprintf(fout,"%d.%d.%d.%d/%d.%d.%d.%d\n", host.ip[0],host.ip[1],host.ip[2],host.ip[3], host.mask[0],host.mask[1],host.mask[2],host.mask[3]);
		    }  
		  local_ip++;  
		}
	      else  	
	        {
		  if(local_url==0)
		    {
                      if(RREJIK==1)
	                sprintf(&shablonname[0],"%s/_sams_banlists/local_url.sams", conf.rejikpath);
                      //else if(RGUARD==1)
	              //  sprintf(&shablonname[0],"%s/_sams_banlists/local_url.sams", conf.sgdbpath);
		      else 
                        sprintf(&shablonname[0],"%s/local_url.sams",conf.squidrootdir);
                      if((fout2=fopen(&shablonname[0], "wt" ))==NULL)
                        {
                          //printf("Don't open file %s\n",&shablonname[0]);
                          return(0);
                        }
                      fprintf(fout2,"%s\n",row[0]);
		    }
		  else
		    {
                      fprintf(fout2,"%s\n",row[0]);
		    }  
		  local_url++;  
		}
	    }
	
	}
      if(local_ip>0)
        fclose(fout);
      if(local_url>0)
        fclose(fout2);
		    
      mysql_free_result(res);
   }
/*список локальных хостов*/



  /* создаем списки пользователей      */
  ncsacount=0;
  ucount=0; 
//  sprintf(&str[0],"SELECT * FROM %s.shablons",conf.samsdb);
  sprintf(&str[0],"SELECT name,auth FROM %s.shablons",conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  for(i=0;i<mysql_num_rows(res);i++)
     {
       row=mysql_fetch_row(res);
       
       if(RSAMS==1||RREJIK==1||RGUARD==1)
         sprintf(&str[0],"SELECT count(*) FROM %s.squidusers where shablon='%s'",conf.samsdb,row[0]);
       else
         sprintf(&str[0],"SELECT count(*) FROM %s.squidusers where shablon='%s'&&enabled>'0'",conf.samsdb,row[0]);
       flag=send_mysql_query(conn,&str[0]);
       res2=mysql_store_result(conn);
       row2=mysql_fetch_row(res2);
       ucount=atoi(row2[0]); 
       mysql_free_result(res2);
       if(ucount>0)
         {

           if(RREJIK==1)
             {
	       sprintf(&shablonname[0],"%s/%s.sams",conf.rejikpath, row[0]);
               if((fout2=fopen(&shablonname[0], "wt" ))==NULL)
                 {
                    printf("Don't open file %s\n",&shablonname[0]);
                    return(0);
                 }
	     }  
           
	   sprintf(&shablonname[0],"%s/%s.sams",conf.squidrootdir,row[0]);

           if(DEBUG==1)
             printf("Creating SAMS users list %s\n",&shablonname[0]);
           
	   if((fout=fopen(&shablonname[0], "wt" ))==NULL)
              {
                printf("Don't create file %s\n",&shablonname[0]);
                return(0);
              }

           if(RSAMS==1||RREJIK==1||RGUARD==1)
             sprintf(&str[0],"SELECT * FROM %s.squidusers WHERE shablon='%s'",conf.samsdb,row[0]);
           else
             sprintf(&str[0],"SELECT * FROM %s.squidusers WHERE shablon='%s'&&enabled>'0'",conf.samsdb,row[0]);
           flag=send_mysql_query(conn,&str[0]);
           res2=mysql_store_result(conn);
           for(j=0;j<ucount;j++)
	     {
                row2=mysql_fetch_row(res2);
                //if(NTLM==1) 
		if(strcmp(row[1],"ntlm")==0||strcmp(row[1],"adld")==0)
		  {
                    if(BIGU==1)
                      {
                        str2upper(row2[1]);
		      }
                    if(BIGU==-1)
                      {
                        Str2Lower(row2[1]);
		      }
                    if(NTLMDOMAIN==1)
                      {
			for(k=1;k<strlen(SEPARATOR);k++)
			  {
                            if(BIGD==1)
		              {
                                str2upper((char *)row2[6]);
			        fprintf(fout,"%s%c%s\n",row2[6],SEPARATOR[k],row2[1]);
                                if(RREJIK==1&&atoi(row2[10])>0)
                                  fprintf(fout2,"%s%c%s\n",row2[6],SEPARATOR[k],row2[1]);
		              }
                            else if(BIGD==-1)
		              {
                                Str2Lower(row2[6]);
			        fprintf(fout,"%s%c%s\n",row2[6],SEPARATOR[k],row2[1]);
                                if(RREJIK==1&&atoi(row2[10])>0)
                                  fprintf(fout2,"%s%c%s\n",row2[6],SEPARATOR[k],row2[1]);
		              }
			    else  
		              {
			        fprintf(fout,"%s%c%s\n",row2[6],SEPARATOR[k],row2[1]);
                                if(RREJIK==1&&atoi(row2[10])>0)
                                  fprintf(fout2,"%s%c%s\n",row2[6],SEPARATOR[k],row2[1]);
		              }
			  }
			
		      }
                    fprintf(fout,"%s\n",row2[1]);
                    if(RREJIK==1&&atoi(row2[10])>0)
                      fprintf(fout2,"%s\n",row2[1]);

		  }      
                //if(NCSA==1) 
		if(strcmp(row[1],"ncsa")==0)
		  {
                     fprintf(fout,"%s\n",row2[1]);
                     if(RREJIK==1&&atoi(row2[10])>0)
                       fprintf(fout2,"%s\n",row2[1]);

                     if(ncsacount==0)
		       {
                          
			  if(access("htpasswd",F_OK)==0)
			    {
			      if(DEBUG==1)
			        printf(" htpasswd not found \n");
			    }
			    
			  if(DEBUG==1)
                            printf("Creating %s/ncsa.sams user: %s\n",conf.squidrootdir,row2[1]);
                          sprintf(&str[0],"htpasswd -cb %s/ncsa.sams %s %s",conf.squidrootdir,row2[1],row2[13]);
                          flag=system(&str[0]);
                          if(flag==0)
                            sprintf(&str[0],"Added user %s into ncsa.sams... Ok",row2[1]);
                          else
                            sprintf(&str[0],"Added user %s into ncsa.sams... Error",row2[1]);
                          AddLog(conn,9,"samsdaemon",&str[0]);
		       } 
                     else
		       {
                          if(DEBUG==1)
                            printf("Creating %s/ncsa.sams user: %s\n",conf.squidrootdir,row2[1]);
                          sprintf(&str[0],"htpasswd -b %s/ncsa.sams %s %s",conf.squidrootdir,row2[1],row2[13]);
                          flag=system(&str[0]);
                          if(flag==0)
                            sprintf(&str[0],"Added user %s into ncsa.sams... Ok",row2[1]);
                          else
                            sprintf(&str[0],"Added user %s into ncsa.sams... Error",row2[1]);
                          AddLog(conn,9,"samsdaemon",&str[0]);
		       } 
                     ncsacount++;

		  }      
                if((strcmp(row[1],"ip")==0||strlen(row[1])==0)&&RREJIK==0&&strlen(row2[11])>4&&strlen(row2[12])>4) 
                    fprintf(fout,"%s/%s\n",row2[11],row2[12]);
                if((strcmp(row[1],"ip")==0||strlen(row[1])==0)&&RREJIK==1) 
		  {
		    if(strlen(row2[11]) > 4 && strlen(row2[12]) > 4) {
                      fprintf(fout,"%s/%s\n",row2[11],row2[12]);

                      if(RREJIK==1&&atoi(row2[10])>0) {
                        fprintf(fout2,"%s\n",row2[11]);
		      }
		    } else {
		      if (DEBUG>0) {
			printf("Bad user: %s/%s", row2[11], row2[12]);
		      }
		    }
		  }  
		  
//                fprintf(fout,"\n");
//                if(RREJIK==1&&atoi(row2[10])>0)
//                  fprintf(fout2,"\n");
	     }
	   mysql_free_result(res2);

           fclose(fout);
           if(RREJIK==1)
             fclose(fout2);

           sprintf(&str[0],"Create %s file",&shablonname[0]);
           AddLog(conn,8,"samsdaemon",&str[0]);
           if(NTLM==1&&RECODE==1)
	     {
                sprintf(&buf[0],"%s.out",&shablonname[0]);
	        if(RecodeFile(&str[0],&shablonname[0],&buf[0])<10)
		   {
		      //printf("system(%s)\n",&str[0]);
		      system(&str[0]);
		   }
		else   
		   {
		      //printf("system(%s)\n",&str[0]);
		      system(&str[0]);
                      sprintf(&str[0],"mv %s.out %s",&shablonname[0],&shablonname[0]);
		      system(&str[0]);
		   }

	     }      
     
	 }
         //If we use ncsa auth but don't have any enabled users - we must have
	 //ncsa.sams for our auth_helper.
         if(strcmp(row[1],"ncsa")==0) 
	   {
             sprintf(&str[0],"%s/ncsa.sams",conf.squidrootdir);
	     if(access(&str[0],F_OK)!=0) {
  	       
	       FILE *ncsa=NULL;

	       if (DEBUG==1)
	         printf("Used ncsa auth, but nsca.sams don't exist! Create empty one.\n");
		   if((ncsa = fopen(&str[0], "wt")) == NULL) {		 
		     printf("Can't create file %s\n", &str[0]);
		   }
		   fclose(ncsa);
	     }
             chmod(&str[0],0644);
           }              
     }  
  mysql_free_result(res);
  /* END    создаем списки пользователей      */

  if(RSQUID==1||RNONE==1||RREJIK==1)
    {
      /* создаем списки отключенных пользователей IP     */
      ncsacount=0;
      disabled_ip=0; 
      sprintf(&str[0],"SELECT squidusers.*,shablons.auth FROM %s.squidusers LEFT JOIN %s.shablons ON squidusers.shablon=shablons.name WHERE squidusers.enabled<'1'&&shablons.auth='ip' ",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      disabled_ip=mysql_num_rows(res);
      if(disabled_ip>0)
        {
	   if(RREJIK==1)
	     sprintf(&shablonname[0],"%s/disabled_ip.sams",conf.rejikpath);
           else
	     sprintf(&shablonname[0],"%s/disabled_ip.sams",conf.squidrootdir);
           if((fout=fopen(&shablonname[0], "wt" ))==NULL)
             {
               return(0);
             }
           if(DEBUG==1)
             printf("Creating SAMS disabled users list: %s\n",&shablonname[0]);
           for(i=0;i<disabled_ip;i++)
             {
                row=mysql_fetch_row(res);
                if(RREJIK==0) 
                    fprintf(fout,"%s/%s",row[11],row[12]);
                if(RREJIK==1) 
                    fprintf(fout,"%s",row[11]);
                fprintf(fout,"\n");
	     }
          fclose(fout);
        }      
      mysql_free_result(res);
      /* END    создаем списки отключенных пользователей IP     */

      /* создаем списки отключенных пользователей NCSA, NTLM     */
      ncsacount=0;
      disabled_id=0; 
      sprintf(&str[0],"SELECT squidusers.*,shablons.auth FROM %s.squidusers LEFT JOIN %s.shablons ON squidusers.shablon=shablons.name WHERE squidusers.enabled<'1'&&shablons.auth!='ip' ",conf.samsdb,conf.samsdb);
      flag=send_mysql_query(conn,&str[0]);
      res=mysql_store_result(conn);
      disabled_id=mysql_num_rows(res);
      if(disabled_id>0)
        {
	   if(RREJIK==1)
	     sprintf(&shablonname[0],"%s/disabled_id.sams",conf.rejikpath);
           else
             sprintf(&shablonname[0],"%s/disabled_id.sams",conf.squidrootdir);
           if((fout=fopen(&shablonname[0], "wt" ))==NULL)
             {
               return(0);
             }
           if(DEBUG==1)
             printf("Creating SAMS disabled users list: %s\n",&shablonname[0]);
           for(i=0;i<disabled_id;i++)
             {
               row=mysql_fetch_row(res);
//printf("NTLM = %d  ADLD = %d   user = %s\n" ,NTLM,ADLD,row[6]);	       
               if(NTLM==1||ADLD==1) 
	         {
                   if(BIGD==1)
                       str2upper((char *)row[6]);
                   if(BIGD==-1)
                       Str2Lower(row[6]);
                   if(BIGU==1)
                       str2upper(row[1]);
                   if(BIGU==-1)
                       Str2Lower(row[1]);
                   if(NTLMDOMAIN==1)
                     {
		       for(k=1;k<strlen(SEPARATOR);k++)
		         {
                           fprintf(fout,"%s%c%s\n",row[6],SEPARATOR[k],row[1]);
		         }
		      }
                    fprintf(fout,"%s",row[1]);
	          }      
                if(NCSA==1) 
	          {
                    fprintf(fout,"%s",row[1]);
	          }      
                fprintf(fout,"\n");
	     }
          fclose(fout);
        }      
      mysql_free_result(res);
      /* END    создаем списки отключенных пользователей NCSA, NTLM     */
    }

/*
  if(NTLM==1&&RECODE==1)
    {
       sprintf(&buf[0],"disable.out");
       if(RecodeFile(&str[0],&shablonname[0],&buf[0])<10)
         system(&str[0]);
       else   
         {
	   system(&str[0]);
           sprintf(&str[0],"mv %s.out %s",&shablonname[0],&shablonname[0]);
           system(&str[0]);
	 }

    }      
*/
 return(0);
}


void ReadSAMSFlags(MYSQL *conn2)
{
  MYSQL_RES *res;
  MYSQL_ROW row;
  int flag;
  char temp[32];
  
  sprintf(&str[0],"SELECT endvalue,auth,ntlmdomain,sleep,redirector,parser_time,parser,count_clean,nameencode,delaypool,sams.separator,loglevel,squidbase,redirect_to,denied_to FROM %s.sams",conf.samsdb);
  flag=send_mysql_query(conn2,&str[0]);
  res=mysql_store_result(conn2);
  row=mysql_fetch_row(res);

  IP=0;
  NCSA=0;
  NTLM=0;
  RSAMS=0;
  RSQUID=0;
  RGUARD=0;
  RNONE=0; 
  DPOOLS=0;
  LOGLEVEL=0;
  if(DEBUG==1)
    printf("    Cache... %d\n", conf.cachenum);

  if(DEBUG==1)
    printf("    User autentification... ");
  sprintf(&temp[0],"%s",row[1]);
  if(strcmp(&temp[0],"ncsa")==0)
    {
      NCSA=1;
      if(DEBUG==1)
         printf("NCSA\n");
    }
  if(strcmp(&temp[0],"ntlm")==0)
    {
      NTLM=1;
      if(DEBUG==1)
         printf("NTLM\n");
    }
  if(strcmp(&temp[0],"adld")==0)
    {
      NTLM=1;
      ADLD=1;
      if(DEBUG==1)
         printf("Active Directory (NTLM)\n");
    }
  if(strcmp(&temp[0],"ip")==0)
    {
      IP=1;
      if(DEBUG==1)
         printf("IP\n");
    }
  sprintf(&temp[0],"%s",row[2]);
  if(strcmp(&temp[0],"Y")==0)
    {
      NTLMDOMAIN=1;
      if(DEBUG==1)
         printf("    Windows domain used\n");
    }

  if(DEBUG==1)
    {
       printf("    Sleep time of samsdaemon... ");
    }
  SLEEP=atoi(row[3]);
  if(DEBUG==1)
    {
       printf(" %d second\n",SLEEP);
    }

  if(DEBUG==1)
    printf("    Redirector... ");
  sprintf(&temp[0],"%s",row[4]);
  if(strcmp(&temp[0],"none")==0)
    {
      RNONE=1;
      if(DEBUG==1)
         printf("NONE\n");
    }
  if(strcmp(&temp[0],"sams")==0)
    {
      RSAMS=1;
      if(DEBUG==1)
         printf("SAMS\n");
    }
  if(strcmp(&temp[0],"rejik")==0)
    {
      RREJIK=1;
      if(DEBUG==1)
         printf("REJIK\n");
    }
  if(strcmp(&temp[0],"squidguard")==0)
    {
      RGUARD=1;
      if(DEBUG==1)
         printf("SQUIDGUARD\n");
    }
  if(strcmp(&temp[0],"squid")==0)
    {
      RSQUID=1;
      if(DEBUG==1)
         printf("SQUID\n");
    }


  if(DEBUG==1)
    printf("    SQUID log parser... ");
  sams_step=1;
  sprintf(&temp[0],"%s",row[6]);
  if(strcmp(row[6],"analog")==0)
    {
      ANALOG=1;
      if(DEBUG==1)
         printf("permanent\n");
    }
  sprintf(&temp[0],"%s",row[6]);
  if(strcmp(&temp[0],"diskret")==0)
    {
      sams_step=atoi(row[5]);
      DISKRET=1;
      if(DEBUG==1)
         printf("diskret\n");
    }


  if(DEBUG==1)
    printf("    User traffic cleaner... ");
  sprintf(&temp[0],"%s",row[7]);
  if(strcmp(&temp[0],"Y")==0)
    {
      COUNTCLEAN=1;
      if(DEBUG==1)
         printf("YES\n");
    }
  else
    {
      if(DEBUG==1)
         printf("NO\n");
    }
  
  if(DEBUG==1)
    printf("    Squidlog cache save... ");
  sprintf(&temp[0],"%s",row[12]);
  SQUIDBASE=atoi(row[12]);
  if(SQUIDBASE==0)
    {
      if(DEBUG==1)
         printf("ALL\n");
    }
  else
    {
      if(DEBUG==1)
         printf("%d month\n", SQUIDBASE);
    }
   
  if(DEBUG==1)
    printf("    User name recode... ");
  sprintf(&temp[0],"%s",row[8]);
  if(strcmp(&temp[0],"Y")==0)
    {
      RECODE=1;
      if(DEBUG==1)
         printf("YES\n");
    }
  else
    {
      if(DEBUG==1)
         printf("NO\n");
    }

  if(DEBUG==1)
    printf("    Delay pools... ");
  sprintf(&temp[0],"%s",row[9]);
  if(strcmp(&temp[0],"Y")==0)
    {
      DPOOLS=1;
      if(DEBUG==1)
         printf("ON\n");
    }
  else
    {
      if(DEBUG==1)
         printf("OFF\n");
    }
  SEPARATOR=MallocMemory(row[10]);  
  sprintf(SEPARATOR,"%s",row[10]);
  if(DEBUG==1)
    printf("    Domain separators... '%s'\n",SEPARATOR);
  LOGLEVEL=atoi(row[11]);
  if(DEBUG==1)
    printf("    Log level... '%d'\n",LOGLEVEL);
  //printf("SQUID LOG %d %d",SQUIDBASE,atoi(row[12]));
  conf.redirpath=MallocMemory(row[13]);
  conf.deniedpath=MallocMemory(row[14]);
  mysql_free_result(res);

  sprintf(&str[0],"SELECT lang,createpdf FROM %s.globalsettings",conf.samsdb);
  flag=send_mysql_query(conn2,&str[0]);
  res=mysql_store_result(conn2);
  row=mysql_fetch_row(res);
  conf.lang=MallocMemory(row[0]);
  if(DEBUG==1)
    printf("    Create PDF file... ");
  if(strcmp(row[1],"none")!=0)
    {
      conf.createpdf=99;
      if(DEBUG==1)
         printf("YES\n");
    }
  else
    {
      conf.createpdf=0;
      if(DEBUG==1)
         printf("NO\n");
    }
  mysql_free_result(res);

}

/* PV: Seems like deprecated function */
void CodeSlashe(char *strin, char *strout)
{
  int i;
  strcpy(strout,"\0");
  printf("input=%s\n",strin);
  for(i=0;i<strlen(strin);i++)
    {
printf("%c",strin[i]);
/*
      if(strin[i]=="\\")
        {
	  strcat(strout,"%2f");
	}
      else
        {
	  strcat(strout,strin[i]);
	}	
  */    
    } 
printf("\n");
  if(DEBUG>0)
    printf("ekrane slashe: %s\n",strout);
}


int ReplaceCHR(char *str)
{
  char *pos;
  if(str[0]=='\0')
     return(1);
  pos=strstr(str,"'");
  while(pos>0)
    {
        strcpy(pos,pos+1);
        pos=strstr(str,"'");
    }
 return(0);
}

int listdir(char *dirname, int MAXSIZE, MYSQL *conn)
{
    register struct dirent *dirbuf;
    DIR *fddir;
    FILE *finp;
    struct   stat st;
    ino_t dot_ino = 0, dotdot_ino = 0;
    char filename[1024];
    char filebuf[1024];
    char url[1024];
    char urlcode[1024];
    char letter;
    int flag;

    if((fddir = opendir (dirname)) == NULL)
      {
        fprintf(stderr, "Can't read %s\n", dirname);
        return 1;
      }
      
    while ((dirbuf = readdir (fddir)) != NULL ) 
      {
        if (dirbuf->d_ino == 0) 
	  continue;
        if (strcmp (dirbuf->d_name, "." ) == 0)
	  {
            dot_ino = dirbuf->d_ino;
	    continue;
	  } 
	else 
	  {
	     if(strcmp (dirbuf->d_name, "..") == 0)
	       {
                dotdot_ino = dirbuf->d_ino;
                continue;
               } 
	     else 
	       { 
		 strncpy(&filename[0],dirname,512);
		 sprintf(&filename[0],"%s/%s", &filename[0], dirbuf->d_name);
	         //lstat (dirbuf->d_name, &st);
	         lstat (&filename[0], &st);
                 if(S_ISREG(st.st_mode))
	           {     
		     if(st.st_size > MAXSIZE)
		       {
			 if((finp=fopen(&filename[0], "rt" ))==NULL)
			    {
    				printf("Don't open file %s\n",&filename[0]);
    				return(0);
			    }
			 while((letter=fgetc(finp))!=(char)0x0A)
			    { 
                              //fprintf(fout,"%c",letter);
				if(letter!=(char)0x00)
                                  {
				    sprintf(&filebuf[0],"%s%c",&filebuf[0],letter);
                                  }
				else
				  {

				    strcpy(&url[0],&filebuf[0]);
				    strcpy(&filebuf[0],"\0");

				  }
                            }
			 //CodeSlashe(&url[0],&urlcode[0]);   
			 ReplaceCHR(&url[0]);
	                 if(DEBUG>0)
			   printf("%s %s %d\n", &filename[0], &url[0], st.st_size);
                         sprintf(&str[0],"INSERT INTO %s.files SET id='%d',filepath='%s',url='%s',size='%d'",conf.logdb,conf.cachenum,&filename[0],&url[0], st.st_size);
                         flag=send_mysql_query(conn,&str[0]);
			 fclose(finp);
		       }
	           }
                 if(S_ISDIR(st.st_mode))
	           {     
	             //printf("        Directory %s\n", &filename[0]);
		     listdir(&filename[0],MAXSIZE,conn);
	           }
		 strcpy(&filename[0],"\0");
	       }
          }      
	

      }
    closedir (fddir);
																				
    if(dot_ino    == 0)
      if(DEBUG>0)
        printf(" :   \".\"\n");
    if(dotdot_ino == 0) 
      if(DEBUG>0)
        printf(" :   \"..\"\n");
    if(dot_ino && dot_ino == dotdot_ino)  
      if(DEBUG>0)
        printf("   \n");
			
    return 0;
}




int main (int argc, char *argv[])
{
  int i,save;
  MYSQL *conn,*conn2;
  MYSQL_RES *res;
  MYSQL_ROW row;
  int flag;
  pid_t pid,childpid,parentpid;
  time_t tt,tt2;
  struct tm *t,*t2;
  struct stat st, s;
  int sams_sec;
  int sams_clr_month;
  int sams_clr_day;
  int SD=0;
  int clearflag;
  int sleepcounter;
    char buf[1024];
    char url[1024];
    char urlcode[1024];
    char letter;
  FILE *finp,*fout;
  unsigned char symbol[3];
  
  struct sigaction sigchld_action;

  sleepcounter=0;
  ADLD=0;
  SDELAY=0;
  sams_sec=0;
  clearflag=0;

  for(i=0;i<argc;i++)
     {
       if(strstr(argv[i],"--help")!=0||strstr(argv[i],"-h")!=0)
          {
            printf("Usage: samsdaemon [options]\n");
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
       if(strstr(argv[i],"--sleep")!=0||strstr(argv[i],"-s")!=0)
          {
            strtok(argv[i],"=");
            SDELAY=atoi(strtok(NULL,"="));
          }
       if(strstr(argv[i],"--startdebug")!=0)
          {
            SD=1;;
          }
     }

//  printf("Starting samsdaemon\n");
  pid = getpid();
  if(DEBUG==1||SD==1)
    {
      printf("Starting process: pid = %d\n",pid);
    }  
  if(access("/var/run/samsdaemon.pid",F_OK)==0)
    {
      if(TestPID("/var/run/samsdaemon.pid")!=0)
         exit(0);
    }

  openlog("samsdaemon",LOG_PID | LOG_CONS , LOG_DAEMON);
  syslog(LOG_LOCAL0|LOG_INFO,"Starting\n");

  if(DEBUG==1||SD==1)
     printf("Read SAMS configuration... ");
  readconf();
  if(DEBUG==1||SD==1)
     printf("Ok \n");
  conn = do_connect(conf.host, conf.user, conf.passwd, conf.samsdb, def_port_num, def_socket_name, 0);
  if (conn == NULL)
                    exit(1);
  conn2 = do_connect(conf.host, conf.user, conf.passwd, conf.logdb, def_port_num, def_socket_name, 0);
  if (conn2 == NULL)
                    exit(1);
  if(DEBUG==1||SD==1)
     printf("Read SAMS properties... \n");
  ReadSAMSFlags(conn2);
  if(DEBUG==1||SD==1)
     printf("Ok \n");

  
  strcpy(&squiduser[0],"squid");
  i=0;
  tt=time(NULL);
  t=localtime(&tt);

  AddLog(conn2,0,"samsdaemon","Starting");

  if(DEBUG==1||SD==1)
    printf("SQUID log parser time=%d min\n",sams_step);
  //printf("start time: %d-%d-%d %d:%d:%d\n",t->tm_year+1900,t->tm_mon+1,t->tm_mday,t->tm_hour,t->tm_min,t->tm_sec);


  if(ANALOG==1)  
    {
       if(DEBUG==1)
         {
           printf("starting FIFO log parser/ parser time=%d %d\n",sams_step,sams_sec);
           printf("start time: %d-%d-%d %d:%d:%d\n",t->tm_year+1900,t->tm_mon+1,t->tm_mday,t->tm_hour,t->tm_min,t->tm_sec);
	 }  
       sprintf(&str[0],"%s/bin/samsf ", conf.samspath);
       system(&str[0]);
    }
  sams_clr_month=t->tm_mon+1;
  sams_clr_day=t->tm_mday;
  sams_sec=(60*sams_step)-t->tm_sec;

/**/
  childpid=0;
  if(DEBUG==0)
    {
      parentpid=getppid();
      childpid=fork();
    }
  if(childpid==0)
    {
       if(DEBUG==0)
         {
            pid = getpid();
            if(SavePID(pid,"/var/run/samsdaemon.pid")!=0)
              exit(0);
            AddLog(conn2,0,"samsdaemon","Starting as daemon");
	 }    
       else
         {
            AddLog(conn2,0,"samsdaemon","Starting in debug mode");
	 
	 }
/**/
       memset(&sigchld_action,0,sizeof(sigchld_action));
       sigchld_action.sa_handler=&clean_up_child_process;
       sigaction (SIGCHLD, &sigchld_action, NULL);
  
       while(SLEEP>0)
         {
           sams_sec-=SLEEP;
           if(DEBUG==1)
              printf("countdown: %d\n",sams_sec);
           if(sleepcounter>=3600)
	     {
	       sleepcounter=0;
	       do_disconnect(conn);
	       do_disconnect(conn2);
               conn = do_connect(conf.host, conf.user, conf.passwd, conf.samsdb, def_port_num, def_socket_name, 0);
               if (conn == NULL)
                    exit(1);
               conn2 = do_connect(conf.host, conf.user, conf.passwd, conf.logdb, def_port_num, def_socket_name, 0);
               if (conn2 == NULL)
                    exit(1);
	     }  
	   else
	       sleepcounter+=SLEEP;
	   
	   if(sams_sec<0)
             {
               tt=time(NULL);
               t=localtime(&tt);
               sams_sec=(60*sams_step);

               //if(DEBUG==1)
  	       //   printf("t->tm_hour==%d t->tm_min=%d conf.createpdf=%d %d!=%d\n", t->tm_hour, t->tm_min, conf.createpdf, t->tm_mday, conf.createpdf);
		// Создаем PDF
               if(t->tm_hour==23&&t->tm_min==59&&conf.createpdf>0&&t->tm_mday!=conf.createpdf)
	         {
		   conf.createpdf=t->tm_mday;
                   if(DEBUG==1)
		     { 
                       printf("Creating PDF \n");
                       //printf("sh -c \"cd %s/share/sams; php %s/share/sams/createpdf.php > %s/share/sams/0.pdf \" \n", conf.samspath, conf.samspath, conf.samspath);
		     }  
                   childpid=fork();
                   printf("childpid = %d\n", childpid);
                   if(childpid==0)
                     {
                       sprintf(&str[0],"sh -c \"cd %s/share/sams; php %s/share/sams/createpdf.php > %s/share/sams/0.pdf \" ", conf.samspath, conf.samspath, conf.samspath);
                       system(&str[0]);
		       exit(0);
                     }
		 
		 }

               clearflag=0;
	       //Если настал новый месяц то проверяем необходимость ротации БД
	       if((sams_clr_month!=(t->tm_mon+1) && SQUIDBASE>0) && conf.cachenum<2)
		 {
                   if(DEBUG==1)
  	              printf("New month. We need purge our database.\n");

                   sams_clr_month=t->tm_mon+1;
                  
		   if(DEBUG==1)
                     printf("Save SQUID base\n");
                   if(DEBUG==0)
		     syslog(LOG_LOCAL0|LOG_INFO,"SAMS: SQUID base saved to disk\n");
                   
                   sprintf(&str[0],"SQUID base saved to disk");
		   AddLog(conn2,0,"samsdaemon",&str[0]);
		   flag=SaveBackUp(SQUIDBASE,conn);
                   if(flag!=0)
                     {
                       printf("error: can't clear user traffic counter\n");
                     }
                   else
                     {
                       if(DEBUG==1)
                         printf("SAMS: SQUID base saved to disk\n");
                     }
                 }

               //если настал новый день, смотрим не пора ли сбросить счетчики трафика
               if((sams_clr_day!=(t->tm_mday))&&COUNTCLEAN==1 && conf.cachenum<2)  
                 {
                   if(DEBUG==1)
  	              printf("New day\n");
		   
                   if(sams_clr_day!=(t->tm_mday))
		     sams_clr_day=t->tm_mday;
                   
                   
		   if(t->tm_mday==1)
		     {
		       if(DEBUG>0)
		         printf("Perod: Month. Traffic cleaned\n");
		       sprintf(&str[0],"SELECT period,name,nick FROM %s.shablons WHERE period='M' ",conf.samsdb);
                       flag=send_mysql_query(conn2,&str[0]);
                       res=mysql_store_result(conn2);
		       for(i=0;i<mysql_num_rows(res);i++)
                         {
                           row=mysql_fetch_row(res);
//                           sprintf(&str[0],"UPDATE %s.squidusers SET size='0',hit='0',enabled='1' WHERE enabled>='0'&&shablon='%s' ",conf.samsdb,row[1]);
                           sprintf(&str[0],"UPDATE %s.squidusers SET size='0',hit='0',enabled='1' WHERE shablon='%s' ",conf.samsdb,row[1]);
                           flag=send_mysql_query(conn2,&str[0]);
                           sprintf(&str[0],"Traffic clean. Template %s, period %s",row[2],row[0]);
			   AddLog(conn2,0,"samsdaemon",&str[0]);
		           clearflag=1;
			}
                       mysql_free_result(res);
		     
		     }
		   if(t->tm_wday==1)
		     {
		       if(DEBUG>0)
		         printf("Perod: Week. Traffic cleaned\n");
		       sprintf(&str[0],"SELECT period,name,nick FROM %s.shablons WHERE period='W' ",conf.samsdb);
                       flag=send_mysql_query(conn2,&str[0]);
                       res=mysql_store_result(conn2);
		       for(i=0;i<mysql_num_rows(res);i++)
                         {
                           row=mysql_fetch_row(res);
//                           sprintf(&str[0],"UPDATE %s.squidusers SET size='0',hit='0',enabled='1' WHERE enabled>='0'&&shablon='%s' ",conf.samsdb,row[1]);
                           sprintf(&str[0],"UPDATE %s.squidusers SET size='0',hit='0',enabled='1' WHERE shablon='%s' ",conf.samsdb,row[1]);
                           flag=send_mysql_query(conn2,&str[0]);
                           sprintf(&str[0],"Traffic clean. Template %s, period %s",row[2],row[0]);
			   AddLog(conn2,0,"samsdaemon",&str[0]);
			   clearflag=1;
		        }
                       mysql_free_result(res);
		     
		     }

		   sprintf(&str[0],"SELECT period,name,nick FROM %s.shablons WHERE clrdate<='%d-%d-%d'&&clrdate>'0000-00-00'&&period!='M'&&period!='W'",conf.samsdb,t->tm_year+1900,t->tm_mon+1,t->tm_mday);
                   flag=send_mysql_query(conn2,&str[0]);
                   res=mysql_store_result(conn2);
		   for(i=0;i<mysql_num_rows(res);i++)
                     {
                        row=mysql_fetch_row(res);
		        if(DEBUG>0)
		          printf("Perod %d: %d days. Traffic cleaned\n", i, atoi(row[0]));
//                        sprintf(&str[0],"UPDATE %s.squidusers SET size='0',hit='0',enabled='1' WHERE enabled>='0'&&shablon='%s' ",conf.samsdb,row[1]);
                        sprintf(&str[0],"UPDATE %s.squidusers SET size='0',hit='0',enabled='1' WHERE shablon='%s' ",conf.samsdb,row[1]);
                        flag=send_mysql_query(conn2,&str[0]);

			    tt2=tt+60*60*24*atoi(row[0]);
                            t2=localtime(&tt2);

		        sprintf(&str[0],"UPDATE %s.shablons SET clrdate='%d-%d-%d' WHERE name='%s'",conf.samsdb,t2->tm_year+1900,t2->tm_mon+1,t2->tm_mday,row[1]);
			flag=send_mysql_query(conn2,&str[0]);
                           sprintf(&str[0],"Traffic clean. Template %s, period %s",row[2],row[0]);
			AddLog(conn2,0,"samsdaemon",&str[0]);
			clearflag=1;
		     }
                   mysql_free_result(res);
		   
		 }
               if(DISKRET==1)  
                 {
                   if(DEBUG==1)
		     { 
                       printf("starting log parser/ parser time=%d %d\n",sams_step,sams_sec);
                       printf("start time: %d-%d-%d %d:%d:%d\n",t->tm_year+1900,t->tm_mon+1,t->tm_mday,t->tm_hour,t->tm_min,t->tm_sec);
		     }  
                   childpid=fork();
                   if(childpid==0)
                     {
                       sprintf(&str[0],"%s/bin/sams",conf.samspath);
		       if(DEBUG>0)
		         printf("starting sams %s\n",&str[0]);
                       system(&str[0]);
		       exit(0);
                     }
                 }
             }

           sprintf(&str[0],"SELECT action,service,value FROM %s.reconfig WHERE service='squid'&&action='loadfile'&&number='%d'",conf.samsdb,conf.cachenum);
	   flag=send_mysql_query(conn2,&str[0]);
           res=mysql_store_result(conn2);
           //Если сигнал на чтение логов
           
	   if(mysql_num_rows(res)>0)
	     {
               row=mysql_fetch_row(res);
               sprintf(&str[0],"DELETE FROM %s.reconfig WHERE service='squid'&&action='loadfile'&&number='%d'",conf.samsdb,conf.cachenum);
               flag=send_mysql_query(conn,&str[0]);
//###########################
	   	if((finp=fopen(row[2], "rt" ))==NULL)
	     	  {
    			printf("Don't open file %s\n", row[2]);
    			return(0);
	     	  }
	   	while((letter=fgetc(finp))!=(char)0x0A)
	          { 
		    if(letter!=(char)0x00&&letter!=(char)0x2F)
                      {
		        sprintf(&buf[0],"%s%c",&buf[0],letter);
                      }
	            else
		      {
		        strcpy(&url[0],&buf[0]);
		        strcpy(&buf[0],"\0");
		      }
                  }
  	   	fclose(finp);
           	if(DEBUG>0)
	      	  printf("Read command to copy file %s to %s/share/sams/data/%s\n",row[2],conf.samspath,&url[0]);
	   	if((finp=fopen(row[2], "rb" ))==NULL)
  	     	  {
               		printf("Don't open file %s\n",row[2]);
               		return(0);
             	  }
	   	sprintf(&buf[0],"%s/share/sams/data/%s",conf.samspath,&url[0]);
  	   	if((fout=fopen(&buf[0], "wb" ))==NULL)
    	     	  {
      			return(0);
    	      	  }
  	   	save=0;
  	   	lstat (row[2], &st);
  	   	while((i=feof(finp))==0)
    	     	  {
       			symbol[0]=fgetc( finp ); 
       			if(save==1)
	 	  	  fprintf(fout,"%c",symbol[0]); 
       			if(symbol[0]==0x0A&&symbol[1]==0x0D&&symbol[2]==0x0A)
         	  	  save=1;
       			symbol[2]=symbol[1];
       			symbol[1]=symbol[0];
   	     	  }
  	   	fclose(finp);
  	   	fclose(fout);

//###########################
	     }  
           mysql_free_result(res);

           sprintf(&str[0],"SELECT action,service,value FROM %s.reconfig WHERE service='squid'&&action='files'&&number='%d'",conf.samsdb,conf.cachenum);
	   flag=send_mysql_query(conn2,&str[0]);
           res=mysql_store_result(conn2);
           //Если сигнал на чтение логов
           
	   if(mysql_num_rows(res)>0)
	     {
               row=mysql_fetch_row(res);
               sprintf(&str[0],"DELETE FROM %s.reconfig WHERE service='squid'&&action='files'&&number='%d'",conf.samsdb,conf.cachenum);
               flag=send_mysql_query(conn,&str[0]);
                    if(DEBUG>0)
		      printf("\n\n\nRead command to scan squid cache files\n\n\n");

               sprintf(&str[0],"INSERT INTO %s.reconfig SET service='squid',action='cachescan',number='%d'",conf.samsdb,conf.cachenum);
               flag=send_mysql_query(conn,&str[0]);
               listdir(conf.cachedir,atoi(row[2])*1024, conn2);
               sprintf(&str[0],"DELETE FROM %s.reconfig WHERE service='squid'&&action='cachescan'&&number='%d'",conf.samsdb,conf.cachenum);
               flag=send_mysql_query(conn,&str[0]);
               sprintf(&str[0],"INSERT INTO %s.reconfig SET service='squid',action='scanoff',number='%d'",conf.samsdb,conf.cachenum);
               flag=send_mysql_query(conn,&str[0]);
	       //printf("\nflag = %d\n\n",flag);

	     }  
           mysql_free_result(res);

           sprintf(&str[0],"SELECT action,service FROM %s.reconfig WHERE service='proxy'&&action='shutdown'&&number='%d'",conf.samsdb,conf.cachenum);
	   flag=send_mysql_query(conn2,&str[0]);
           res=mysql_store_result(conn2);
           //Если сигнал на shutdown
           
	   if(mysql_num_rows(res)>0)
	     {
               sprintf(&str[0],"DELETE FROM %s.reconfig WHERE service='proxy'&&action='shutdown'&&number='%d'",conf.samsdb,conf.cachenum);
               flag=send_mysql_query(conn,&str[0]);
//               if(flag!=0)
//                 {
                    AddLog(conn2,0,"samsdaemon","Shutdown proxy server");
                    sprintf(&str[0],"%s",conf.shutdown);
                    if(DEBUG>0)
		      printf("Send command to shutdown of proxy server: %s \n", &str[0]);
                    flag=system(&str[0]);
                    exit(1);
//                 }
	     }  
           mysql_free_result(res);

	   sprintf(&str[0],"SELECT action,service FROM %s.reconfig WHERE service='squid'&&action='reconfig'&&number='%d'",conf.samsdb,conf.cachenum);
           flag=send_mysql_query(conn2,&str[0]);
           res=mysql_store_result(conn2);
           //Если сигнал на рекофигурирование SQUID
           if(mysql_num_rows(res)>0||clearflag>0)  
             {
	       clearflag=0;

               sprintf(&str[0],"DELETE FROM %s.reconfig WHERE service='squid'&&action='reconfig'&&number='%d'",conf.samsdb,conf.cachenum);
               flag=send_mysql_query(conn,&str[0]);
               if(flag!=0)
                 {
                    exit(1);
                 }

	       AddLog(conn2,0,"samsdaemon","Reading request to reconfigure SQUID");
               freeconf();
	       readconf();
               ReadSAMSFlags(conn2);

               UnlinkFiles(conf.squidrootdir,".sams");

               if(RGUARD==1)
                 UnlinkFiles(conf.squidrootdir,".squidguard");

               MakeACLFiles(conn2);
	       sleep(1);

               ChangeSQUIDconf(conn2);
               if(RREJIK==1)
	         {
		   chRejikConf(conn2);
		 }
               if(RGUARD==1)
	         {
		   chSquidGuardConf(conn2);
		 }

	       sleep(1);
               if(RGUARD==1)
	         {
                    sprintf(&str[0],"squidGuard -C all -c %s/squidGuard.conf",conf.sgdbpath);
                    system(&str[0]);
                    sprintf(&str[0],"chown nobody:nobody %s/_sams_*",conf.sgdbpath);
                    system(&str[0]);
                    sprintf(&str[0],"chown -R %s %s/_sams_*", &squiduser[0], conf.sgdbpath);
		    system(&str[0]);
	         }
               /* Задаем права доступа на каталог с Режиком */
               if(RREJIK==1)
                 {
                    sprintf(&str[0],"chown -R %s %s/_sams_*", &squiduser[0], conf.rejikpath);
                    system(&str[0]);
                 }

               childpid=fork();
               if(childpid==0)
                 {
	            tt=time(NULL);
                    t=localtime(&tt);

                    if(ANALOG==1)  
                      {
                         flag=system("killall samsf");
                         if(DEBUG>0)
			   printf("killing samsf = %d\n",flag);
                         sprintf(&str[0],"%s/bin/samsf ", conf.samspath);
                         flag=system(&str[0]);
                         if(DEBUG>0)
			   printf("restarting samsf = %d\n",flag);
			 AddLog(conn2,0,"samsdaemon","Restarting samsf");
                      }
/* swap.state*/
//  sprintf(&str[0],"%s/swap.state",conf.cachedir);
//  lstat(&str[0],&s);

/* */
                    sprintf(&str[0],"%s/squid -f %s/squid.conf -k reconfigure",conf.squidpath,conf.squidrootdir);
                    flag=system(&str[0]);
                    if(flag==0)
                      AddLog(conn2,0,"samsdaemon","Reconfigure & restart SQUID... Ok");
                    else
                      AddLog(conn2,0,"samsdaemon","Reconfigure & restart SQUID... Error");
  	            exit(0);
                 }
	     }
           mysql_free_result(res);

           sleep(SLEEP);
         }
       if(DEBUG==0)
          RmPID("/var/run/samsdaemon.pid");
/**/
    }

/**/  
tt=time(NULL);
t=localtime(&tt);
freeconf();
free(SEPARATOR);
closelog();
return(0);
}
