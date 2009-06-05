using namespace std;

#include <string>
#include <stdlib.h>

extern char * get_fsusage ();

string information()
{
  string res;
  char *str_res = get_fsusage ();

  if (str_res)
    {
      res = str_res;
      free (str_res);
    }

  return res;
}

string name()
{
  return "File System Usage";
}

string version()
{
  return "1.0.0-a";
}

string author()
{
  return "Andrey Ovcharov";
}

