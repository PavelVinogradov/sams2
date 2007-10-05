#include <map>

#include "samsconfig.h"
#include "samsfile.h"
#include "tools.h"
#include "debug.h"

typedef std::map<string, string> strMap;
//typedef std::map<string, int>    intMap;
//typedef std::map<string, double> dblMap;
//typedef std::map<string, bool>   boolMap;

strMap strs;
//intMap ints;
//dblMap dbls;
//boolMap bools;

#define ATTR_NOT_FOUND    1
#define ATTR_NOT_PARSED   2

#define BUFFER_SIZE 1024
bool
cfgRead (const string filename)
{
  FILE *cfgfile;
  static char buf[BUFFER_SIZE];
  string line;
  string name;
  string value;
  int signpos;
  int err;

  cfgfile = fileOpen (filename, "r");
  if (cfgfile == NULL)
    {
      return false;
    }

  while (fgets( buf, BUFFER_SIZE-1, cfgfile ) != NULL)
    {
      line = buf;
      line = StripComments(line);

      if (line.empty())
        continue;

      signpos = line.find_first_of('=');
      if (signpos < 0)
        continue;

      name = line.substr(0, signpos);
      value = line.substr(signpos+1, line.size()-signpos);

      name = TrimSpaces(name);
      value = TrimSpaces(value);
      cfgSetString (name, value);
      DEBUG(DEBUG9, name << "=" << value);
    }

  fileClose(cfgfile);
}

string
cfgGetString (const string attrname, int &err)
{
  strMap::iterator it  = strs.find(attrname);
  if (it == strs.end())
    {
      err = ATTR_NOT_FOUND;
      DEBUG(DEBUG9, attrname << " not found");
      return "";
    }
  DEBUG(DEBUG9, attrname << "=" << (*it).second);
  return (*it).second;
}

int
cfgGetInt (const string attrname, int &err)
{
  int res;
  string val;

  val = cfgGetString(attrname, err);
  if (err == ATTR_NOT_FOUND)
    return 0;
  if (sscanf(val.c_str(), "%d", &res) != 1)
    {
      err = ATTR_NOT_PARSED;
      DEBUG(DEBUG9, attrname << " not parsed");
      return 0;
    }
  return res;
}

double
cfgGetDouble (const string attrname, int &err)
{
  double res;
  string val;

  val = cfgGetString(attrname, err);
  if (err == ATTR_NOT_FOUND)
    return 0;
  if (sscanf(val.c_str(), "%lf", &res) != 1)
    {
      err = ATTR_NOT_PARSED;
      DEBUG(DEBUG9, attrname << " not parsed");
      return 0;
    }
  return res;
}

bool
cfgGetBool (const string attrname, int &err)
{
  int res;
  string val;

  val = cfgGetString(attrname, err);
  if (err == ATTR_NOT_FOUND)
    return 0;
  if (sscanf(val.c_str(), "%d", &res) != 1)
    {
      err = ATTR_NOT_PARSED;
      DEBUG(DEBUG9, attrname << " not parsed");
      return false;
    }
  if (res == 0)
    return false;
  return true;
}

void
cfgSetString (const string attrname, const string value)
{
  DEBUG(DEBUG9, attrname << "=" << value);
  strs[attrname] = value;
}

void
cfgSetInt (const string attrname, const int value)
{
  char buf[64];
  sprintf(&buf[0], "%d", value);
  DEBUG(DEBUG9, attrname << "=" << buf);
  strs[attrname] = buf;
}

void
cfgSetDouble (const string attrname, const double value)
{
  char buf[64];
  sprintf(&buf[0], "%lf", value);
  DEBUG(DEBUG9, attrname << "=" << buf);
  strs[attrname] = buf;
}

void
cfgSetBool (const string attrname, const bool value)
{
  char buf[64];
  sprintf(&buf[0], "%d", (value==true)?1:0);
  DEBUG(DEBUG9, attrname << "=" << buf);
  strs[attrname] = buf;
}
