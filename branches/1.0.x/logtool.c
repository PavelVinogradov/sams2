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

#include <signal.h>
#include <string.h>
#include <stdio.h>
#include <stdlib.h>
#include <netdb.h>
#include <time.h>
#include <ctype.h>
#include <mysql.h>
#include <math.h>
//#include <tgmath.h>
#include <fcntl.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <sys/time.h>
#include <sys/un.h>
#include <syslog.h>
#include <unistd.h>
#include "config.h"
#include "define.h"
#include "tools.h"
#include "pcre.h"

struct url_replace {
  char user[25];
  char domain[25];
  char url[URL_LEN];
  char newurl[URL_LEN];
  int  checkip;
};

struct samsusers 
{
  char user[25];
  char domain[25];
  int  ip[6];
  int  mask[6];
  int  enabled;
  int  disabled;
  double size;
  double hit;
  double traffic;
  double quote;
  char id[25];
  char date[15];
  int updated;
  int ipauth;
  int ntlmauth;
  int ncsaauth;
  int adldauth;
};
/*
struct local_url {
  char url[URL_LEN];
  int  ip[6];
  int  mask[6];
  int ipflag;
};
*/
struct dns_cache {
  char url[URL_LEN];
  int  ip[6];
  int  mask[6];
  int len;
  int local;
};

struct url_replace *RUCm;
struct samsusers *users;
struct local_url *local;
struct dns_cache *dns;

int DNSDB;
int DNSDBMAX;
int NODNSSERVER;
int LCOUNT;
int RUC;
int UDSCRIPT;
char *UDSCRIPTFILE;
int samsuserscount;
int IP,NTLM,NCSA,NTLMDOMAIN,CHECKDNS,LOCALURL,REALTRAF;
static char strim[256];
char path[1024];



int TestInputString(char *str)
{
  int i,space,scount;
  if(str == NULL)
    {      
      if(DEBUG>0)
        {
          printf("\n*** ERROR: Input string is NULL \n");
	  return(-1);
	}  
    }  
  if(strlen(str)<60)
    {
      if(DEBUG>0)
        {
          printf("\n*** ERROR: Input string  has length < 60 chars \n");
	  return(-1);
	}  
    }  
//  if((int)str[10]!=0x2E && (int)str[14]!=0x20 && (int)str[21]!=0x20)
  if((int)str[10]!=0x2E || (int)str[14]!=0x20 || (int)str[21]!=0x20)
    {
      if(DEBUG>0)
        {
          printf("\n*** ERROR: Input string has wrong format \n");
	  return(-1);
	}  
    }  
  for(i=0,space=0,scount=1;i<strlen(str);i++)
    {
      if( (int)str[i] == 0x20 )
        space = 1;
      if( (int)str[i] != 0x20 && (int)str[i] != 0x0A && space == 1 )
        {
	  space=0;
	  scount++;
	}	
        
    }
 if(scount<10)
    { 
      if(DEBUG>0)
        printf("\n*** ERROR: Input string has too few fields (%d)\n",scount);
      return(-1);
    }

  if(scount>10)
    { 
      if(DEBUG>0)
        printf("\n*** WARNING: Input string has more fields (%d) than standart one. ",scount);
    }

  
  return(0);
}


int exec_script(char *filename, char *username)
{
 int length;
 FILE *finp;
 char buf[4096];
 char *exe=NULL;

 sprintf(&buf[0],"SAMSUSERNAME=%s\nSAMSADMINADDR=%s\n",username,&conf.adminaddr[0]);
 length=strlen(&buf[0]);

 if ((finp = fopen (filename, "r")))
   {
     fseek(finp,0,SEEK_END);
     length+=ftell(finp);
     fseek(finp,0,SEEK_SET);
     if((exe=(char *)malloc((sizeof(char))*(length+10)))==NULL)
       {
         printf("Not enought memory to allocate buffer\n");
         exit(1);
       }
     sprintf(exe,"SAMSUSERNAME=%s \nSAMSADMINADDR=%s \n",username,&conf.adminaddr[0]);
     while(feof(finp)==0)
       {
         fgets( &buf[0], 4096, finp );
         sprintf(exe,"%s%s",exe, &buf[0]);
         strcpy(&buf[0],"\0");
       }
     fclose(finp);

     system(exe);
     free(exe);
   } 
 else
   {
      if(DEBUG>0) 
        printf("Script %s not found\n",filename);
   }   
 return(0);
}

int GetIPbyHostName(struct dns_cache *dns)
{
  struct hostent *hostinfo;
  int i;
  hostinfo=gethostbyname(dns->url);
  if(hostinfo==NULL)
    {
      //NODNSSERVER=1;
      printf("No connect to DNS server. NODNSSERVER=%d\n",NODNSSERVER);
      return(1);
    }  
  dns->len=hostinfo->h_length;
  for(i=0;i<dns->len;i++)
    dns->ip[i]=255&hostinfo->h_addr[i];
  return(0);
}

int SearchDNSBase(struct dns_cache *DNS)
{
  int i,found;
  struct local_url host;

  for(i=0,found=0;i<DNSDB;i++)
    {
      if(strcmp(DNS->url,dns[i].url)==0)
        {
          return(i+1);
	}  
    }
  if(NODNSSERVER==0)
    {
      GetIPbyHostName(DNS);
    }
  if(NODNSSERVER==1)
    {
       if((host.ipflag=LocalIPAddr(DNS->url,&host.ip[0],&host.mask[0]))==1)
         {
           for(i=0;i<6;i++)
             {
	       DNS->ip[i]=host.ip[i];
	     }
           if(DEBUG==1)
             printf("%s =>  %d.%d.%d.%d\n",DNS->url, DNS->ip[0], DNS->ip[1], DNS->ip[2], DNS->ip[3]);  
	 }
       else
         {
           for(i=0;i<6;i++)
             {
	       DNS->ip[i]=255;
	     }
           if(DEBUG==1)
             printf("%s =>  %d.%d.%d.%d\n",DNS->url, DNS->ip[0], DNS->ip[1], DNS->ip[2], DNS->ip[3]);  
	 }
    }  
  for(i=0;i<DNS->len;i++)
      dns[DNSDB].ip[i]=DNS->ip[i];
  strncpy(dns[DNSDB].url,DNS->url,50);
  DNSDB++;
  if(DNSDB==DNSDBMAX)
    {
       realloc(dns,DNSDBMAX+50);
       DNSDBMAX+=50;
    }
  return(0);
}



int TestLocalURL(char *url)
{
  struct dns_cache DNS;
  int i=0,count=0,slashe=0,ipflag=0;
  int found=0,localfound=0;
  
  pcre *re;
  const char *re_error;
  int re_erroffset;
  int re_ovector[30];

  re = pcre_compile(
	"(.*):(\\d+)",
	0,
	&re_error,
	&re_erroffset,
	NULL);

  /* Get domain name for URL like http://<domain name>/ */
  if(strstr(url,"://") !=NULL)
  {
        for(i=0,count=0;i<strlen(url);i++)
        {
       	        if(i>=249 || count>=URL_LEN)
                        i=strlen(url);
                
       		if(slashe>=2)
		{
			if(slashe<3&&url[i]!='/')
			{
				DNS.url[count]=url[i];
				count++;
			}
		}
		
		if(url[i]=='/')
		{
			slashe++;
		}	 
	}
  } else {
	/* If user ask https page, we get url like: domain.name:443 */
	if ( pcre_exec(re, NULL, url, strlen(url), 0, 0, re_ovector, 30) >= 0 ) {
  		
		for(i=0,count=0;i<strlen(url);i++)
		{
       			if(i>=249 || count>=URL_LEN)
          			i=strlen(url);

			if (url[i] == ':')
			{ /* We found :<port>. Stop parse */
				break;
			} else {
				DNS.url[count]=url[i];
				count++;
			}
		}

	} else {
		if (DEBUG > 0) 
		{
			printf ("We don't know how parse URL like: %s", url);
		}

		return(0);
	}
  }
  
  /**  ищем в списке локальных хостов **/
  /* получаем доменное имя*/
  strcpy(&DNS.url[count],"\0");

  // это IP адрес? 
  ipflag=LocalIPAddr(&DNS.url[0],&DNS.ip[0],&DNS.mask[0]);
        
  // нет не IP, и включено преобразование DNS адресов
  if(ipflag==0&&NODNSSERVER==0)
    {
      GetIPbyHostName(&DNS);
      ipflag=1;
    }

  if(ipflag==1)
    {
      for(i=0;i<LCOUNT;i++)
        {
          //printf("%d=%d ",local[i].ip[0],(local[i].mask[0]&DNS.ip[0]));
	  //printf("%d=%d ",local[i].ip[1],(local[i].mask[1]&DNS.ip[1]));
	  //printf("%d=%d ",local[i].ip[2],(local[i].mask[2]&DNS.ip[2]));
	  //printf("%d=%d \n",local[i].ip[3],(local[i].mask[3]&DNS.ip[3]));
	       
	  if(local[i].ipflag!=0&&local[i].ip[0]==(local[i].mask[0]&DNS.ip[0])
	                       &&local[i].ip[1]==(local[i].mask[1]&DNS.ip[1])
			       &&local[i].ip[2]==(local[i].mask[2]&DNS.ip[2])
			       &&local[i].ip[3]==(local[i].mask[3]&DNS.ip[3]))
            {
              if(DEBUG>0)
	  	{
                  printf(" found IP\n");
		}  
	      return(1);
            }
	 }
    }
  else
    {
      for(i=0;i<LCOUNT;i++)
        {
          if(strstr(url,local[i].url)!=0&&strlen(local[i].url)>0)
            {
              if(DEBUG>0)
                printf(" found   %s = %s\n", local[i].url, url);
	      return(1);
            }
	}
    }
    
  if(NODNSSERVER==0)
    {
       /**  в списке ненашли, получаем ip адрес  **/
      found=0;
      localfound=0;
      found=SearchDNSBase(&DNS);
    }
  //exit(0);
  return(0);
}




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
  
  // ****  Маска  **
  t1=i;
  ocount=0;
  if(length-i<=2&&length-i>0)
    {
       // если маска задана количеством битов
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
*/
void trim(char *string)
{
  int i;
  strncpy(&strim[0],"\0",255);
  for(i=0;i<strlen(string);i++)
     {
        if(iscntrl(string[i])==0)
           sprintf(&strim[0],"%s%c",&strim[0],string[i]);
     }
  strncpy(string,&strim[0],255);
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


long txt2digit(char *str)
{
  int len,i,j,base=0;
  double value,pow;
  value=0;
  len=strlen(str);
  for(i=0;i<len;i++)
     {
        if(isdigit(str[i])!=0)
          {
            switch(str[i])
              {
                case '1':               base=1; break;
                case '2':               base=2; break;
                case '3':               base=3; break;
                case '4':               base=4; break;
                case '5':               base=5; break;
                case '6':               base=6; break;
                case '7':               base=7; break;
                case '8':               base=8; break;
                case '9':               base=9; break;
                case '0':               base=0; break;
              }
	    pow=1;	
            for(j=0;j<len-i-1;j++)
	      {
	        pow=pow*10; 
	      }
	    value+=base*pow;
//	    value+=base*pow(10,len-i-1);
         }
     }
  return(value);
}


void TestURL(char *url)
{
 int i;
 for(i=0;i<strlen(url);i++)
   {
     if(url[i]=='\\')
       url[i]='/';
   }
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

void ReplaceURL(char *url, char *user, char *domain)
{
  char *p;
  int i;
  strncpy(&path[0],url,255);
  for(i=0;i<RUC;i++)
     {
        if(strstr(url,RUCm[i].url)!=0&&strstr(user,RUCm[i].user)!=0&&strstr(domain,RUCm[i].domain)!=0)
          {
            p=strtok(&path[0],"/");
            p=strtok(NULL,"/");
            p=strtok(NULL,"/");
            sprintf(&path[0],"%s/%s",RUCm[i].newurl,p);
          }
     }
}

int ReturnSAMSUser(char *username, char *domain, char *ipaddr, int type)
{
  int i;
  struct local_url host;
  
  if(type==0)
    {
      LocalIPAddr(ipaddr,&host.ip[0],&host.mask[0]);
    }  

  if(type>0)
    {
      if(strlen(username)>0)
        url_decode(username);
    }  

  for(i=0;i<samsuserscount;i++)
    {
      if(type==0)
        {

               if(users[i].ip[0]==host.ip[0]&&users[i].ip[1]==host.ip[1]&&users[i].ip[2]==host.ip[2]&&users[i].ip[3]==host.ip[3])
                  {
//printf("ipauth>0 %d=%d %d=%d %d=%d %d=%d\n",users[i].ip[0],host.ip[0],users[i].ip[1],host.ip[1],users[i].ip[2],host.ip[2],users[i].ip[3],host.ip[3]);
                    return(i+1);
                  }
        }
      if(type==2)
        {
          if((users[i].ncsaauth>0)||users[i].ntlmauth>0||users[i].adldauth>0)
            {
               if(strcmp(username,users[i].user)==0)
                 {
//printf("    ncsa auth>0   %s = %s\n", username, users[i].user);
                   return(i+1);
                 }
	    }
        }
      if(type==1)
        {
          if((users[i].ntlmauth>0&&NTLMDOMAIN!=0)||(users[i].adldauth>0&&NTLMDOMAIN!=0))
            {
               if(strcmp(username,users[i].user)==0&&strcmp(domain,users[i].domain)==0)
                 {
//printf("    ntlm auth>0   %s = %s\n", username, users[i].user);
                   return(i+1);
                 }
	    }
        }
    }
 return(0);
}



