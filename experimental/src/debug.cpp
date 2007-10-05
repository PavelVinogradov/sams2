#include "debug.h"

uint debug_level = DEBUG0;
bool verbose = false;

void dbgSetLevel(uint level)
{
  debug_level = level;
}

void dbgSetVerbose(bool v)
{
  verbose = v;
}

