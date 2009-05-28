using namespace std;

#include <string>

extern "C" const char * get_fsusage ();

string information()
{
  return "File System Usage Information here";
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

