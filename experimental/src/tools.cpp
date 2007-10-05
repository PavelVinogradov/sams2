
#include "tools.h"
#include "debug.h"

#define SPACECHARS " \t\r\n"

string
StripComments(const string str)
{
  int hash_pos;
  string res;

  if (str.empty())
    return "";

  res = str;
  hash_pos = res.find_first_of('#');
  if (hash_pos >= 0)
    {
      res = res.substr(0, hash_pos);
    }
  return res;
}


string
StripCharacters(const string str, const string needless)
{
  string res;
  int i;
  int charpos;

  if (str.empty())
    return "";

  res = "";
  for (i=0; i<str.size(); i++)
    {
      charpos = needless.find_first_of(str[i]);
      if (charpos < 0)
        {
          res += str[i];
        }
    }
  return res;
}


string
TrimCharactersLeft(const string str, const string needless)
{
  int pos;
  string res;

  if (str.empty())
    return "";

  pos = str.find_first_not_of(needless);
  if (pos >= 0)
    res = str.substr(pos, str.size()-pos);
  else
    res = str;

  return res;
}


string
TrimCharactersRight(const string str, const string needless)
{
  int pos;
  string res;

  if (str.empty())
    return "";

  pos = str.find_last_not_of(needless);
  if (pos >= 0)
    res = str.substr(0, pos+1);
  else
    res = str;

  return res;
}

string
TrimCharacters(const string str, const string needless)
{
  return TrimCharactersLeft(TrimCharactersRight(str, needless), needless);
}





string
TrimSpacesLeft(const string str)
{
  return TrimCharactersLeft(str, SPACECHARS);
}

string
TrimSpacesRight(const string str)
{
  return TrimCharactersRight(str, SPACECHARS);
}

string
TrimSpaces(const string str)
{
  return TrimCharacters(str, SPACECHARS);
}

