#include <sys/types.h>
#include <dirent.h>
#include <unistd.h>
#include <fnmatch.h>

#include "samsfile.h"
#include "debug.h"

FILE *
fileOpen (const string fname, const string fmode)
{
  FILE *f;
  f = fopen (fname.c_str (), fmode.c_str ());
  if (f == NULL)
    WARNING ("Cannot open file " << fname << " mode " << fmode);
  return f;
}

void
fileClose (FILE * f)
{
  if (f != NULL)
    fclose (f);
  else
    WARNING ("Null pointer f");
}

bool
fileDelete (const string path, const string filemask)
{
  DIR *dir;
  register struct dirent *dirbuf;
  string fullname;
  bool res;

  if (path.empty())
    {
      WARNING ("Empty path");
      return false;
    }
  if (filemask.empty())
    {
      WARNING ("Empty filemask");
      return false;
    }

  dir = opendir (path.c_str());
  if (dir == NULL)
    {
      ERROR (path << ": " << strerror(errno) );
      return false;
    }
  res = true;
  while ((dirbuf = readdir (dir)) != NULL )
    {
       if (fnmatch(filemask.c_str(), dirbuf->d_name, FNM_FILE_NAME) == 0)
         {
           fullname = path + "/" + dirbuf->d_name;
           if (unlink(fullname.c_str()) != 0)
             {
               res = false;
               ERROR(fullname << ": " << strerror(errno));
             }
           else
             {
               INFO("File " << fullname << " deleted.");
             }
         }
    }
  closedir (dir);
  return res;
}
