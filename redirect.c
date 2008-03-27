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
#include <mysql.h>
#include <math.h>
#include <ctype.h>
#include <signal.h>
#include <sys/time.h>
#include <sys/types.h>

#include "pcre.h"
#include "config.h"
#include "define.h"
#include "tools.h"

struct samsusers {
  char user[25];
  char domain[25];
  int ip[6];
  int ipmask[6];
  char shablon[25];
  int enabled;
  char id[16];
  int stime;
  int etime;
  int days[7];
  int ipauth;
  int ntlmauth;
  int ncsaauth;
  int adldauth;
  int alldenied;
};

struct samsshablons 
{
  char name[25];
  char list[25];
  long sm;
  long len;
};

struct samsurls 
{
  char url[90];
  char list[25];
  char type[25];
  int  regex;
  double count;
  pcre *cexpr;
};


sig_atomic_t child_exit_status;

struct samsusers *users;
struct samsurls *urls;
struct samsshablons *shablons;
int samsshablonscount,samsurlscount,samslistcount, samsuserscount,smcount,rflag;
char samsuser[256];
char samsdomain[256];
int DEBUG,NCSA,IP,NTLM,NCSA,NTLMDOMAIN,USERNUMBER;
int REDIRECT,DENIED,USERDENIED,ALLOW,URLALLOW,TIMEDENIED;
char userid[16];
char LANG[16];
  MYSQL *conn;
  MYSQL_RES *res, *res2;
  MYSQL_ROW row, row2;


/*
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
  
  // ****  Маска  
  t1=i;
  ocount=0;
  if(length-i<=2&&length-i>0)
    {
       // * если маска задана количеством битов
       strncpy(&octet[0],url+i,length-i);
       strcpy(&octet[length-i],"\0");
       if(atof(&octet[0])>48)
	 return(0);
       else  
	 bit=atoi(&octet[0]);
       bit2=abs((32-bit)/8);
       for(j=0;j<bit2;j++)
         {
           om[j]=255;
	 }
	om[j]=255&((char)255<<(8-(32-bit2*8-bit)));
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
*/

void clean_up_child_process(int signal_number)
{
  int status;
  wait(&status);
  child_exit_status = status;
}


void convertplus(char* p)
{
 char* buffer;
 int strCount = 0;
 int sourceCount = 0;

 while(1)
   {
     buffer = strstr(p,"%5c");
     if(buffer)
       {
         sourceCount = strlen(p);
         strCount = strlen(buffer);
         p[sourceCount - strCount] = '+';
         strcpy(&p[sourceCount - strCount+1],buffer+3);
         if(DEBUG==1)
	   printf("   Found NTLN separator: %s \n","%5c");
       }
     else
         break;
   }
} 

void ReturnFullDomainName(char *domainname,char *url)
{
  int i=0,count=0,slashe=0,t1=0,t2=0,j=0;
  for(i=0,count=0,t2=0;i<strlen(url);i++)
     {
        if(url[i]=='.')
           { 
              t1=t2;  t2=i;
           }

        if(url[i]=='/')
           { 
             count++;
             if(count>=3)
                {
                   count=i-1;
                   break;
                }
             slashe=i;
           }
     }
  if(t1<slashe)
     t1=slashe;
  count=i;
  for(i=slashe+1,j=0;i<count;i++,j++)
     {
        if(url[i]=='-')
           domainname[j]='%';
        else
           domainname[j]=url[i];
     }
  domainname[j]='\0';
}



void ReturnDomainName(char *domainname,char *url)
{
  int i=0,count=0,slashe=0,t1=0,t2=0,j=0;
  for(i=0,count=0,t2=0;i<strlen(url);i++)
     {
        if(url[i]=='.')
           { 
              t1=t2;  t2=i;
           }

        if(url[i]=='/')
           { 
             count++;
             if(count>=3)
                {
                   count=i-1;
                   break;
                }
             slashe=i;
           }
     }
  if(t1<slashe)
     t1=slashe;
  domainname[0]='%';
  count=i;
  for(i=t1+1,j=1;i<count;i++,j++)
     {
        if(url[i]=='-')
           domainname[j]='%';
        else
           domainname[j]=url[i];
     }

  domainname[j]='%';
  domainname[j+1]='\0';
}

void trim(char *string)
{
  char strim[BUFFER_SIZE];
  int i;
  strncpy(&strim[0],"\0",BUFFER_SIZE-1);
  for(i=0;i<strlen(string);i++)
     {
        if(iscntrl(string[i])==0)
           sprintf(&strim[0],"%s%c",&strim[0],string[i]);
     }
  strncpy(string,&strim[0],254);
} 


char *str2lower(char *string)
{
 char *original=string;
 while(*string)
     {
        *string=tolower(*string);
        string++;
     }
 return(original);
}


int UserAccess(char *str0, char *str1, char *str2)
{ 
  int i;
  char str3[100];
  int ip[6],ipmask[6];

  strcpy(&userid[0],"\0");

  LocalIPAddr(str0,&ip[0],&ipmask[0]);

  for(i=0;i<samsuserscount;i++)
    {
      if(users[i].ipauth!=0)
        {
          if(users[i].ip[0]==ip[0]&&users[i].ip[1]==ip[1]&&users[i].ip[2]==ip[2]&&users[i].ip[3]==ip[3])
            {
              strcpy(&userid[0],users[i].id);
	      if(users[i].enabled>0)
		 return(i+1);
	    }
        }
      if((users[i].ntlmauth!=0||users[i].adldauth!=0)&&NTLMDOMAIN!=0)
        {
             strncpy(&samsuser[0],users[i].user,255);
             strncpy(&samsdomain[0],users[i].domain,255);
             strcat(&samsuser[0],"\0");
	     strcat(&samsdomain[0],"\0");
             if(strstr(str1,"+")!=0)
               {
                 sprintf(&str3[0],"%s+%s",users[i].domain,users[i].user);
               }
             else if(strstr(str1,"\\")!=0)
               {
                 sprintf(&str3[0],"%s\\%s",users[i].domain,users[i].user);
	       } 
             else if(strstr(str1," ")!=0)
               {
                 sprintf(&str3[0],"%s %s",users[i].domain,users[i].user);
	       } 
             else
	       {
                 sprintf(&str3[0],"%s%s",users[i].domain,users[i].user);
	       }

	     if(strcmp(str2lower(str1),&str3[0])==0)
               {
                 strcpy(&userid[0],users[i].id);
	       }

	     if(strcmp(str2lower(str1),&str3[0])==0&&users[i].enabled>0)
               {
                 return(i+1);
	       }
             strcpy(&samsuser[0],"\0");
	     strcpy(&samsdomain[0],"\0");
        }
      if(users[i].ncsaauth!=0||(users[i].ntlmauth!=0&&NTLMDOMAIN==0)||(users[i].adldauth!=0&&NTLMDOMAIN==0))  
        {
             if(strcmp(str2lower(str1),users[i].user)==0)
               {
                 strcpy(&userid[0],users[i].id);
	       }
             if(strcmp(str2lower(str1),users[i].user)==0&&users[i].enabled>0)
               {
                return(i+1);
	       }
        }
    }   


  return(0);
}








int main (int argc, char *argv[])
{
  int i=0,j=0,k=0,flag=0;
  static char domainname[BUFFER_SIZE];
  static char str[BUFFER_SIZE];
  static char str1[BUFFER_SIZE];
  static char str1_[BUFFER_SIZE];
  static char str2[BUFFER_SIZE];
  static char str2_[BUFFER_SIZE];
  static char str3[BUFFER_SIZE];
  static char str4[BUFFER_SIZE];
  static char redir_to[120];
  static char denied_to[120];
  char *user=NULL,*domain=NULL;
  char separator;
  unsigned int pid;
  struct sigaction sigchld_action;
  time_t tt;
  struct tm *t;
  char week[8];
  int erroffset;
  const char *error;
  int ovector[30];

  sprintf(&week[0],"SMTWHFA");
  pid = getpid();

  NCSA=0;
  REQUEST=0;
  for(i=0;i<argc;i++)
     {
       if(strstr(argv[i],"--help")!=0||strstr(argv[i],"-h")!=0)
          {
            printf("Usage: sams [options]\n");
            printf(" -h, --help       show this message.\n");
            printf(" -d, --debug      print debug message.\n");
            printf(" -V, --version    Print version.\n");
            //printf(" -r, --request      print debug message.\n");
            exit(0);
          }
       if(strstr(argv[i],"--version")!=0||strstr(argv[i],"-V")!=0)
          {
            printf("Version %s\n", VERSION);
	    exit(0);
          }
       if(strstr(argv[i],"--debug")!=0||strstr(argv[i],"-d")!=0)
          {
            DEBUG=1;
          }
       if(strstr(argv[i],"--request")!=0||strstr(argv[i],"-r")!=0)
          {
            REQUEST=1;
          }
     }

  readconf();
  conn = do_connect(conf.host, conf.user, conf.passwd, conf.samsdb, def_port_num, def_socket_name, 0);
  if (conn == NULL)
                    exit(1);

//  fflush(stdout);
  sprintf(&str[0],"SELECT redirect_to,denied_to,auth,ntlmdomain FROM %s.sams",conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  row=mysql_fetch_row(res);

  strncpy(&redir_to[0],row[0],119);
  strncpy(&denied_to[0],row[1],119);
  if(strcmp(row[2],"ip")==0)
    {
       IP=1;
       if(DEBUG==1)
         {
            printf("authentification: IP addr\n");
         }
    }
  if(strcmp(row[2],"ntlm")==0)
    {
       NTLM=1;
       if(DEBUG==1)
         {
            printf("authentification: NTLM\n");
         }
    }
  if(strcmp(row[2],"adld")==0)
    {
       NTLM=1;
       if(DEBUG==1)
         {
            printf("authentification: Active Directory LDAP\n");
         }
    }
  if(strcmp(row[2],"ncsa")==0)
    {
	  NCSA=1;
          if(DEBUG==1)
            printf("authentification: NCSA\n");
    }

  if(strcmp(row[3],"Y")==0)
    {
      NTLMDOMAIN=1;
    }

  mysql_free_result(res);


  sprintf(&str[0],"SELECT lang FROM %s.globalsettings",conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  row=mysql_fetch_row(res);
  strncpy(&LANG[0],row[0],15);
  mysql_free_result(res);





  /* Получаем количество пользователей SAMS */
  samsuserscount=0;
  sprintf(&str[0],"SELECT count(nick) FROM %s.squidusers",conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  row=mysql_fetch_row(res);
  samsuserscount=atoi(row[0]);

  if(DEBUG==1)
    {
       printf("Get SAMS users: %d users found\n",samsuserscount);
    }
  /* Выделяем память под пользователей SAMS */
  if((users=(struct samsusers *)malloc(sizeof(struct samsusers)*samsuserscount))==NULL)
     {
       printf("Not enought memory to allocate buffer\n");
       exit(1);
     }
  /* Загружаем пользователей SAMS в массив */
  sprintf(&str[0],"SELECT squidusers.nick,squidusers.domain,squidusers.ip,squidusers.ipmask,squidusers.enabled,squidusers.shablon,squidusers.id,shablons.shour,shablons.smin,shablons.ehour,shablons.emin,shablons.days,shablons.auth,shablons.alldenied FROM %s.squidusers LEFT JOIN %s.shablons ON (squidusers.shablon=shablons.name) ORDER BY nick",conf.samsdb,conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  //printf("!!!!!!!!!!!!!! flag=%d !!!!!!!!!!!!!\n",flag);
  res=mysql_store_result(conn);

  for(i=0;i<samsuserscount;i++)
     { 
        row=mysql_fetch_row(res);
        strncpy(users[i].user,str2lower(row[0]),25);
        strncpy(users[i].domain,str2lower(row[1]),25);
        //strncpy(users[i].ip,row[2],15);
        //strncpy(users[i].ipmask,row[3],15);
        LocalIPAddr(row[2],users[i].ip,users[i].ipmask);
        users[i].enabled=atoi(row[4]);
        strncpy(users[i].shablon,row[5],15);
        strncpy(users[i].id,row[6],15);
        users[i].stime=atoi(row[7])*60+atoi(row[8]);
        users[i].etime=atoi(row[9])*60+atoi(row[10]);

        for(j=0;j<7;j++)
	  {
            if(memchr(row[11],week[j], 7)!=NULL)
	      {
	        users[i].days[j]=1;
              }
	    else
	      {
	        users[i].days[j]=0;
              }
	  }
         users[i].ipauth=0;
         users[i].ntlmauth=0;
         users[i].ncsaauth=0;
         users[i].adldauth=0;
         users[i].alldenied=0;


        if(strcmp(row[12],"ip")==0)
	                            users[i].ipauth=1;
        if(strcmp(row[12],"ntlm")==0)
	                            users[i].ntlmauth=1;
        if(strcmp(row[12],"ncsa")==0)
	                            users[i].ncsaauth=1;
        if(strcmp(row[12],"adld")==0)
	                            users[i].adldauth=1;
        if(atoi(row[13])>0)
	                            users[i].alldenied=1;
         
        if(DEBUG==1)
          {
             printf("%3d: ",i);
             printf("%15s ",users[i].user);
             printf("%15s ",users[i].domain);
             printf("%3d.%3d.%3d.%3d ",users[i].ip[0],users[i].ip[1],users[i].ip[2],users[i].ip[3]);
             printf("%2d ",users[i].enabled);
             printf("%16s ",users[i].shablon);
             printf("%d%d%d%d%d%d%d ",users[i].days[0],users[i].days[1],users[i].days[2],users[i].days[3],users[i].days[4],users[i].days[5],users[i].days[6]);
             printf("%d - %d ",users[i].stime,users[i].etime);
             printf("%d %d %d %d\n",users[i].ipauth,users[i].ntlmauth,users[i].ncsaauth,users[i].adldauth);
          }
     }


  /* Получаем количество Списков  */
  samsshablonscount=0;
  sprintf(&str[0],"SELECT count(sname) FROM %s.sconfig",conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  row=mysql_fetch_row(res);
  samsshablonscount=atoi(row[0]);
  if(DEBUG==1)
    {
       printf("\nSearch URL lists in the users templates: found %d URL lists\n",samsshablonscount);
    }
  /* Выделяем память под список шаблонов */
  if((shablons=(struct samsshablons *)malloc(sizeof(struct samsshablons)*samsshablonscount))==NULL)
     {
       printf("Not enought memory to allocate buffer\n");
       exit(1);
     }
  /* Загружаем списки и шаблоны в массив */
  sprintf(&str[0],"SELECT * FROM %s.sconfig",conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  for(i=0;i<samsshablonscount;i++)
     {
        row=mysql_fetch_row(res);
        strncpy(shablons[i].name,row[0],25);
        strncpy(shablons[i].list,row[1],25);
        shablons[i].sm=0;
        shablons[i].len=0;
        //if
	//shablons[i].alldenied=0; 
        if(DEBUG==1)
          {
             printf("%3d: ",i);
             printf("%25s ",shablons[i].name);
             printf("%25s \n",shablons[i].list);
          }
     }


  /* Получаем количество URL  */
  samslistcount=0;
  sprintf(&str[0],"SELECT count(filename) FROM %s.redirect",conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  row=mysql_fetch_row(res);
  samslistcount=atoi(row[0]);
  if(DEBUG==1)
    {
       printf("\nSorting URL lists in the users templates: found %d URL lists\n",samslistcount);
    }
  sprintf(&str[0],"SELECT filename,type FROM %s.redirect",conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  smcount=1;
  for(i=0;i<samslistcount;i++)
     {
       row=mysql_fetch_row(res);
       //if(strcmp("local",row[1])!=0)
       // {
           sprintf(&str[0],"SELECT count(url) FROM %s.urls WHERE type='%s'",conf.samsdb,row[0]);
           //printf("%s\n",&str[0]); 
           flag=send_mysql_query(conn,&str[0]);
           res2=mysql_store_result(conn);
           row2=mysql_fetch_row(res2);
           for(j=0;j<samsshablonscount;j++)
              {
	        if(strcmp(shablons[j].list,row[0])==0)
	          {
                     shablons[j].sm=smcount;
                     shablons[j].len=atoi(row2[0]);
                     if(DEBUG==1)
                       { 
                        printf("%3d ",j);
                        printf("%25s ",shablons[j].name);
                        printf("%25s ",shablons[j].list);
                        printf("line: %6ld ",shablons[j].sm);
                        printf("len: %ld \n",shablons[j].len);
		       } 
	          }
	      }
            smcount+=atoi(row2[0]);
         //}
     }

  samsurlscount=smcount;
  if(DEBUG==1)
    {
       printf("Found %d URL in the SAMS URL lists\n",samsurlscount);
    }
  /* Выделяем память под URL */
  if((urls=(struct samsurls *)malloc(sizeof(struct samsurls)*(samsurlscount+10)))==NULL)
     {
       printf("Not enought memory to allocate buffer\n");
       exit(1);
     }

  k=1;
  sprintf(&str[0],"SELECT filename,type FROM %s.redirect",conf.samsdb);
  flag=send_mysql_query(conn,&str[0]);
  res=mysql_store_result(conn);
  for(i=0;i<samslistcount;i++)
     {
       row=mysql_fetch_row(res);
       //if(strcmp("local",row[1])!=0)
         //{
            sprintf(&str[0],"SELECT urls.url,urls.type FROM %s.urls WHERE type='%s'",conf.samsdb,row[0]);
            flag=send_mysql_query(conn,&str[0]);
            res2=mysql_store_result(conn);
            while((row2=mysql_fetch_row(res2))!=NULL)
              {
                strncpy(urls[k].url,row2[0],90);
                strncpy(urls[k].list,row2[1],25);
                strncpy(urls[k].type,row[1],25);
	        urls[k].count=0;
                if(strcmp(row[1],"regex")==0||strcmp(row[1],"redir")==0)
                  {
	            urls[k].regex=1;
                    urls[k].cexpr= pcre_compile(row2[0],0,&error,&erroffset,NULL);
	          }
                else
                  {
	            urls[k].regex=0;
	          }
                if(DEBUG==1)
                  { 
                    printf("%3d ",k);
                    printf("%25s ",urls[k].list);
                    printf("%25s ",urls[k].url);
                    printf("%15s\n",urls[k].type);
	          } 
                k++;
              }
	 //}
     }

  if(REQUEST!=1)
    do_disconnect(conn);

  memset(&sigchld_action,0,sizeof(sigchld_action));
  sigchld_action.sa_handler=&clean_up_child_process;
  sigaction (SIGCHLD, &sigchld_action, NULL);

  tt=time(NULL);
  t=localtime(&tt);


  rflag=0;
  while(fgets(&str[0], BUFFER_SIZE, stdin)!=NULL)
      {
        if(DEBUG==1)
           printf("STDIN:  %s",&str[0]);

        TIMEDENIED=0;
        USERNUMBER=0;
        REDIRECT=0;
	DENIED=0;
	USERDENIED=0;
        URLALLOW=0;
        ALLOW=0;
        trim(&str[0]);

        if(strlen(&str[0])>0)
           {
             strncpy(&str1[0],strtok(&str[0]," "),BUFFER_SIZE-1);
             sprintf(&str1_[0],"%s",&str1[0]);
             strncpy(&str2_[0],strtok(NULL," "),254);
             strncpy(&str3[0],strtok(NULL," "),254);

             if(NTLM!=0||NCSA!=0)
               { 
                 url_decode(&str3[0]);

	       }
	     convertplus(&str3[0]);
             strncpy(&str4[0],strtok(NULL," "),254);
             strncpy(&str2[0],strtok(&str2_[0],"/"),254);
             ReturnFullDomainName(&str[0],&str1[0]);

             if(DEBUG==1)
                   printf("   decode data: URL=%s IP=%s USER=%s TYPE=%s\n",&str1[0],&str2[0],&str3[0],&str4[0]);


                 if(DEBUG==1&&NCSA!=0)  printf("   authentication: ncsa \n");
                 if(DEBUG==1&&NTLM!=0&&NTLMDOMAIN!=0)  printf("   authentication: ntlm (domain+username) \n");
                 if(DEBUG==1&&NTLM!=0&&NTLMDOMAIN==0)  printf("   authentication: ntlm (username) \n");
                 if(DEBUG==1&&IP!=0)		       printf("   authentication: ip \n");

                 if(DEBUG==1)
                    printf("   Search SAMS user... ");
                 if(NTLM!=0||NCSA!=0)
		     USERNUMBER=UserAccess(&str2[0],&str3[0],&str4[0]);
                 if(IP!=0)
                    USERNUMBER=UserAccess(&str2[0],&str3[0],&str4[0]);
                 if(USERNUMBER==0)
		   {
                      if(DEBUG==1)
                         printf("   NOT FOUND\n");
                      USERDENIED=1;
                   }  
                 else
		   {
                      if(DEBUG==1&&(NCSA!=0||(NTLM!=0&&NTLMDOMAIN==0)))
                        printf("    %s template: %s\n",users[USERNUMBER-1].user,users[USERNUMBER-1].shablon);
                      if(DEBUG==1&&NTLM!=0&&NTLMDOMAIN!=0)
                        printf("    %s+%s template: %s\n",users[USERNUMBER-1].domain,users[USERNUMBER-1].user,users[USERNUMBER-1].shablon);
                      if(DEBUG==1&&IP!=0)
                        printf("   %s, ip: %d.%d.%d.%d, template: %s\n",users[USERNUMBER-1].user,users[USERNUMBER-1].ip[0],users[USERNUMBER-1].ip[1],users[USERNUMBER-1].ip[2],users[USERNUMBER-1].ip[3],users[USERNUMBER-1].shablon);

                      tt=time(NULL);
                      t=localtime(&tt);

                      TIMEDENIED=1;

                      if(users[USERNUMBER-1].days[t->tm_wday]==1)
		        {
		          if( (users[USERNUMBER-1].stime>users[USERNUMBER-1].etime))
		            { 
		              if(users[USERNUMBER-1].stime <= (t->tm_hour*60+t->tm_min) || (t->tm_hour*60+t->tm_min) <= users[USERNUMBER-1].etime)
			       {
                                 TIMEDENIED=0;
			       }
			    }   
                          if(users[USERNUMBER-1].stime<users[USERNUMBER-1].etime)
                            {
		              if(users[USERNUMBER-1].stime < (t->tm_hour*60+t->tm_min)&& (t->tm_hour*60+t->tm_min) < users[USERNUMBER-1].etime )
			       {
                                 TIMEDENIED=0;
			       }
                            }
			}    

		      if( TIMEDENIED==0 )
			{
                          strcpy(&domainname[0],"\0"); 
                          ReturnDomainName(&domainname[0],&str1[0]);

//                          if(NTLM!=0)
                          if(users[USERNUMBER-1].ntlmauth!=0)
                            {
                              if(strcmp(&str3[0],"-")==0)
                                {
                                   strncpy(&str3[0],"\0",254);
                                   domain=&str3[0];
	                           user=&str3[0];
                                   separator='-';
                                }   
                              else
                                {
                                  domain=&samsdomain[0];
                                  user=&samsuser[0];
                                  if(strstr(&str3[0],"+")!=0)
                                                                separator='+';
                                  else if(strstr(&str3[0],"\\")!=0)
                                                                strncpy(&separator,"\\",1);
				  else
                                                                strncpy(&separator,"\0",1);
                                }
			    }
                          if(NCSA!=0)
			    {
	                        user=&str3[0];
				domain="";
			    } 

                          for(i=0;i<samsshablonscount;i++)
			    {
			        if(strcmp(users[USERNUMBER-1].shablon,shablons[i].name)==0)
                                  {
                                     if(DEBUG==1)
				       {
				          printf("   Searching to the template: ");
				          printf("%15s ",users[USERNUMBER-1].shablon);
				          printf("URL list: %15s ",shablons[i].list);
				          printf("line: %5ld ",shablons[i].sm);
				          printf(" %5ld\n",shablons[i].len);
				       }
				     for(j=0;j<shablons[i].len;j++)
				        {
					  k=shablons[i].sm+j;
                                          //Если доступ запрещен ко всем, кроме разрешенных
					  if(users[USERNUMBER-1].alldenied==1)
					    {
						   URLALLOW=1;
					    }
                                          if(strcmp(urls[k].type,"regex")==0)
					    {
                                              if(urls[k].cexpr!=NULL)
                                                if(pcre_exec(urls[k].cexpr,NULL,&str1[0],strlen(&str1[0]),0,0,ovector,30)>=0)
                                                  {
                                                    DENIED=1;
				                    if(DEBUG==1) 
						    printf("        REGEX:    found rules: %s\n",urls[k].url);
                                                  }
					    }
                                          if(strcmp(urls[k].type,"redir")==0)
					    {
                                              if(urls[k].cexpr!=NULL)
                                                if(pcre_exec(urls[k].cexpr,NULL,&str1[0],strlen(&str1[0]),0,0,ovector,30)>=0)
                                                  {
                                                     REDIRECT=1;
						     urls[k].count+=1;
						     rflag=1;
						     if(DEBUG==1)
				                        printf("        REDIR:    found URL %s\n",urls[k].url);
                                                  }
					    }
				          //if(DEBUG==1) 
					  //   printf("        %d  %s  %d URL: %s=%s \n",k,urls[k].type,j,&str1[0],urls[k].url);
					  if(strstr(&str1[0],urls[k].url)!=0)
					    {
                                              if(strcmp(urls[k].type,"allow")==0)
					        {
                                                   ALLOW=1;
						   if(DEBUG==1)
				                      printf("        ALLOW:    found URL %s\n",urls[k].url);
						}
                                              if(strcmp(urls[k].type,"denied")==0)
					        {
                                                   DENIED=1;
						   if(DEBUG==1)
				                      printf("        DENIED:   found URL %s\n",urls[k].url);
						}
					    }
					}
				  }
			    }
			}
		      else
		        {
			  //TIMEDENIED=1;
			  if(DEBUG==1)
			     printf("Data or time limit error %c=%d %d:%d \n",week[t->tm_wday],users[USERNUMBER-1].days[t->tm_wday],t->tm_hour,t->tm_min);
			} 		  
                   }

                 if(DEBUG==1)
                    printf("URLALLOW=%d ALLOW=%d USERDENIED=%d DENIED=%d REDIRECT=%d TIMEDENIED=%d\n",URLALLOW, ALLOW, USERDENIED, DENIED, REDIRECT, TIMEDENIED);
                 
		 //запрещено все и непопали в разрещенный список
		 if(URLALLOW!=0&&ALLOW==0)
                    {
                      sprintf(&str1[0],"%s/blocked.php?action=urldenied&id=%s",&denied_to[0],&userid[0]);
		    }  
                 if(USERDENIED!=0)
                    {
                      sprintf(&str1[0],"%s/blocked.php?action=userdisabled&id=%s",&denied_to[0],&userid[0]);
		    }  
                 if(DENIED!=0&&ALLOW==0)
                    {
                      sprintf(&str1[0],"%s/blocked.php?action=urldenied&id=%s",&denied_to[0],&userid[0]);
		    }  
                 if(REDIRECT!=0)
                    {
                      strncpy(&str1[0],&redir_to[0],254);
		    }  
                 if(TIMEDENIED!=0)
                    {
                      sprintf(&str1[0],"%s/blocked.php?action=timedenied&id=%s",&denied_to[0],&userid[0]);
		    }  
                 sprintf(&str[0],"%s %s/- %s %s",&str1[0],&str2[0],&str3[0],&str4[0]);
                 fprintf(stdout,"%s\n",&str[0]);
                     


             fflush(stdout);
             if(DEBUG==1)
               printf("\n");

             if(REQUEST==1)
	       {
                 sprintf(&str[0],"INSERT INTO %s.redirect_test SET redirect_test.inp='%s',redirect_test.ip='%s',redirect_test.out='%s',redirect_test.user='%s',redirect_test.pid='%d'",conf.logdb,&str1_[0],&str2[0],&str1[0],&str3[0],pid);
                 printf("%s\n",&str[0]);
                 flag=send_mysql_query(conn,&str[0]);
	         strcpy(&str[0],"\0");
                 printf("flag=%d\n",flag);
	       }
           }
      }

 if(REQUEST==1)
   do_disconnect(conn);

 return(0);
}






