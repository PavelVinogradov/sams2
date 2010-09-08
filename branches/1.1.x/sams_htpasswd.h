/*
 * htpasswd.c: simple program for manipulating password file for NCSA httpd
 * Rob McCool
 */

#include <sys/types.h>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <time.h>
#include <unistd.h>
#include <syslog.h>

#define LF 10
#define CR 13

#define MAX_STRING_LEN 256

char *tn;

char *strd(char *s) {
    char *d;

    d=(char *)malloc(strlen(s) + 1);
    strcpy(d,s);
    return(d);
}

void message(char *msg) {
  openlog("samsdaemon",LOG_PID | LOG_CONS , LOG_DAEMON);
  syslog(LOG_LOCAL0|LOG_INFO,msg);
  closelog();
  return;
}

int file_exists(const char * filename)
{
   if (access(filename, F_OK) == 0) return 1;
   return 0;
}

void pgetword(char *word, char *line, char stop) {
    int x = 0,y;

    for(x=0;((line[x]) && (line[x] != stop));x++)
        word[x] = line[x];

    word[x] = '\0';
    if(line[x]) ++x;
    y=0;

    while(line[y++] = line[x++]);
}

int pgetline(char *s, int n, FILE *f) {
    register int i=0;

    while(1) {
        s[i] = (char)fgetc(f);

        if(s[i] == CR)
            s[i] = fgetc(f);

        if((s[i] == 0x4) || (s[i] == LF) || (i == (n-1))) {
            s[i] = '\0';
            return (feof(f) ? 1 : 0);
        }
        ++i;
    }
}

void pputline(FILE *f,char *l) {
    int x;

    for(x=0;l[x];x++) fputc(l[x],f);
    fputc('\n',f);
}


static void to64(char *s, unsigned long v, int n)
{
    static unsigned char itoa64[] =         /* 0 ... 63 => ASCII - 64 */
    "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            
    while (--n >= 0) {
       *s++ = itoa64[v&0x3f];
       v >>= 6;
    }
}

void add_password(char *user, char *pw, FILE *f) {
    char *cpw, salt[3];

    (void)srand((int)time((time_t *)NULL));
    to64(&salt[0],rand(),2);
    cpw = crypt(pw,salt);
    fprintf(f,"%s:%s\n",user,cpw);
}

void set_password(char *conf, char *user, char *passwd) {
    FILE *tfp,*f;
    char line[MAX_STRING_LEN];
    char l[MAX_STRING_LEN];
    char w[MAX_STRING_LEN];
    char command[MAX_STRING_LEN];
    char tn[256];
    int found;
    
    strncpy(&tn[0],"\0",255);
    strcpy(&tn[0],"/tmp/htpassXXXXXX");

    if(!file_exists(conf)) {
        if(!(tfp = fopen(conf,"w"))) {
              message("Cannot open password file\n");
              return;
        }
        add_password(user,passwd,tfp);
        fclose(tfp);
        return;
    }

    //tn = tmpnam(NULL);
    mkstemp(&tn[0]);
    
    if(!(tfp = fopen(tn,"w"))) {
        message("Could not open temp file.\n");
        return;
    }

    if(!(f = fopen(conf,"r"))) {
        message("Could not open passwd file for reading.\n");
        return;
    }

    found = 0;
    while(!(pgetline(line,MAX_STRING_LEN,f))) {
        if(found || (line[0] == '#') || (!line[0])) {
            pputline(tfp,line);
            continue;
        }
        strcpy(l,line);
        pgetword(w,l,':');
        if(strcmp(user,w)) {
            pputline(tfp,line);
            continue;
        }
        else {
            //Changing password for user 
            add_password(user,passwd,tfp);
            found = 1;
        }
    }
    if(!found) {
        //Adding user
        add_password(user,passwd,tfp);
    }
    
    fclose(f);
    fclose(tfp);
    sprintf(command,"cp %s %s",tn,conf);
    system(command);
    unlink(tn);
}
