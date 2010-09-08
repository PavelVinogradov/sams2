int sprintf_sams(char *to, char *format, char *s1, char *s2) {
  char tmp[BUFFER_SIZE];
  strncpy(&tmp[0],"\0",BUFFER_SIZE-1);
  sprintf(&tmp[0],format,s1,s2);
  strcpy(to,&tmp[0]);
  return 0;
}

int sprintf_samsl(char *to, char *format, char *s1, char s2) {
  char tmp[BUFFER_SIZE];
  strncpy(&tmp[0],"\0",BUFFER_SIZE-1);
  sprintf(&tmp[0],format,s1,s2);
  strcpy(to,&tmp[0]);
  return 0;
}

int sprintf_sams1(char *to, char *format, char *s1) {
  char tmp[BUFFER_SIZE];
  strncpy(&tmp[0],"\0",BUFFER_SIZE-1);
  sprintf(&tmp[0],format,s1);
  strcpy(to,&tmp[0]);
  return 0;
}
