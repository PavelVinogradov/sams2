using namespace std;

#include <string>

extern "C" const char * get_uptime ();

string information()
{
  return get_uptime ();
  //return "System Information here";
}

string name()
{
  return "Load average";
}

string version()
{
  return "1.0.0-a";
}

string author()
{
  return "Andrey Ovcharov";
}

