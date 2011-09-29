/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/
#include <sys/stat.h>
#include <sys/types.h>
#include <dirent.h>
#include <unistd.h>
#include <fnmatch.h>
#include <errno.h>
#include <string>
#include <string.h>
#include <cstdlib>

#include "tools.h"
#include "debug.h"

#define SPACECHARS " \t\r\n"

using namespace std;

string StripComments (const string & str)
{
//  int hash_pos;
  string res;

  if (str.empty ())
    return "";

  res = TrimSpaces(str);
  if (res[0] == '#' || res[0] == ';')
    res = "";
/*
  res = str;
  hash_pos = res.find_first_of ("#;");
  if (hash_pos >= 0)
    {
      res = res.substr (0, hash_pos);
    }
*/
  return res;
}


string StripCharacters (const string & str, const string & needless)
{
  string res;
  uint i;
  int charpos;

  if (str.empty ())
    return "";

  res = "";
  for (i = 0; i < str.size (); i++)
    {
      charpos = needless.find_first_of (str[i]);
      if (charpos < 0)
        {
          res += str[i];
        }
    }
  return res;
}


string TrimCharactersLeft (const string & str, const string & needless)
{
  int pos;
  string res = "";

  if (str.empty ())
    return res;

  pos = str.find_first_not_of (needless);
  if (pos >= 0)
    res = str.substr (pos, str.size () - pos);
  else
    res = str;

  return res;
}


string TrimCharactersRight (const string & str, const string & needless)
{
  int pos;
  string res = "";

  if (str.empty ())
    return res;

  pos = str.find_last_not_of (needless);
  if (pos >= 0)
    res = str.substr (0, pos + 1);
  else
    res = str;

  return res;
}

string TrimCharacters (const string & str, const string & needless)
{
  string s_tmp = TrimCharactersRight (str, needless);
  s_tmp = TrimCharactersLeft (s_tmp, needless);
  return s_tmp;
}

string TrimSpacesLeft (const string & str)
{
  return TrimCharactersLeft (str, SPACECHARS);
}

string TrimSpacesRight (const string & str)
{
  return TrimCharactersRight (str, SPACECHARS);
}

string TrimSpaces (const string & str)
{
  return TrimCharacters (str, SPACECHARS);
}

string ToLower (const string & str)
{
  string res;
  for (unsigned int i=0; i<str.size (); i++)
    {
      res += tolower (str[i]);
    }
  return res;
}

string ToUpper (const string & str)
{
  string res;
  for (unsigned int i=0; i<str.size (); i++)
    {
      res += toupper (str[i]);
    }
  return res;
}

bool endsWith(const string & str, const string & substr)
{
  if (substr.empty ())
    return true;

  if (str.empty ())
    return false;

  if (substr.size () > str.size ())
    return false;

  string part = str.substr (str.size () - substr.size (), substr.size ());

  if (part == substr)
    return true;

  return false;
}

void Split (const string & s, const string & delim, vector < string > &tbl, bool removeEmpty)
{
  uint prev = 0;
  int next = 0;

  //DEBUG (DEBUG9, s << ", '" << delim << "', " << removeEmpty);

  tbl.clear();

  if (s.empty ())
    return;

  next = s.find_first_of (delim);

  if (next == -1)
    {
      tbl.push_back (s);
      return;
    }
  string token;
  while ((next = s.find_first_of (delim, prev)) != -1)
    {
      token = s.substr (prev, next - prev);

      if (token.size () > 0)
        {
          //DEBUG (DEBUG9, "token: " << token);
          tbl.push_back (token);
        }
      else if (!removeEmpty)
        {
          //DEBUG (DEBUG9, "token: " << token);
          tbl.push_back (token);
        }
      prev += next - prev + 1;
    }
  if (prev < s.size ())
    {
      token = s.substr (prev, s.size () - prev);
      if (token.size () > 0)
        {
          //DEBUG (DEBUG9, "token: " << token);
          tbl.push_back (token);
        }
      else if (!removeEmpty)
        {
          //DEBUG (DEBUG9, "token: " << token);
          tbl.push_back (token);
        }
    }
}


bool fileDelete (const string & path, const string & filemask)
{
  DIR *dir;
  register struct dirent *dirbuf;
  string fullname;
  bool res;

  if (path.empty ())
    {
      WARNING ("Empty path");
      return false;
    }
  if (filemask.empty ())
    {
      WARNING ("Empty filemask");
      return false;
    }

  dir = opendir (path.c_str ());
  if (dir == NULL)
    {
      ERROR (path << ": " << strerror (errno));
      return false;
    }
  res = true;
  while ((dirbuf = readdir (dir)) != NULL)
    {
      if (fnmatch (filemask.c_str (), dirbuf->d_name, FNM_FILE_NAME) == 0)
        {
          fullname = path + "/" + dirbuf->d_name;
          if (unlink (fullname.c_str ()) != 0)
            {
              res = false;
              ERROR (fullname << ": " << strerror (errno));
            }
          else
            {
              DEBUG (DEBUG4, fullname << " deleted.");
            }
        }
    }
  closedir (dir);
  return res;
}

bool fileDelete (const string & path)
{
  bool res;

  if (path.empty ())
    {
      WARNING ("Empty path");
      return false;
    }

  if (unlink (path.c_str ()) != 0)
    {
      res = false;
      WARNING (path << ": " << strerror (errno));
    }
  else
    {
      DEBUG (DEBUG4, path << " deleted.");
    }

  return res;
}

bool fileCopy (const string & name, const string & newname)
{
  if (name.empty ())
    {
      WARNING ("Empty source name");
      return false;
    }

  if (newname.empty ())
    {
      WARNING ("Empty destination name");
      return false;
    }

  ifstream in;

  in.open (name.c_str (), ios_base::in);
  if (!in.is_open ())
    {
      ERROR ("Failed to open file " << name);
      return false;
    }

  ofstream out;
  out.open (newname.c_str (), ios_base::out);
  if (!out.is_open ())
    {
      ERROR ("Failed to open file " << newname);
      in.close ();
      return false;
    }

  streamsize readed;
  char buf[10240];
  while (in.good ())
    {
      in.read (buf, sizeof (buf));
      readed = in.gcount ();
      out.write(buf, readed);
    }

  in.close ();
  out.close ();

  return true;
}

bool fileExist (const string & path)
{
  return (access (path.c_str (), F_OK) == 0);
}

vector<string> fileList (const string & path, const string & filemask)
{
  DIR *dir;
  register struct dirent *dirbuf;
  string fullname;
  vector<string> res;

  if (path.empty ())
    {
      WARNING ("Empty path");
      return res;
    }
  if (filemask.empty ())
    {
      WARNING ("Empty filemask");
      return res;
    }

  dir = opendir (path.c_str ());
  if (dir == NULL)
    {
      WARNING (path << ": " << strerror (errno));
      return res;
    }
  while ((dirbuf = readdir (dir)) != NULL)
    {
      if (fnmatch (filemask.c_str (), dirbuf->d_name, FNM_FILE_NAME) == 0)
        {
          fullname = path + "/" + dirbuf->d_name;
          res.push_back (fullname);
        }
    }
  closedir (dir);

  return res;
}

void timeSubstractDays(struct tm & stime, int days)
{
  int seconds = days * 86400; // 86400 - количество секунд в сутках
  time_t t = mktime (&stime);
  t -= seconds;
  struct tm *tmp = localtime(&t);
  memcpy (&stime, tmp, sizeof(struct tm));
}

/* Задумывалось как замена вызова htpasswd, но т.к. пароли в БД хранятся уже шифрованными
   то и необходимость отпала, а удалять жалко, вдруг пригодится :)

const char *salt_table = "aBcD0eFgH1iJkL2mNoP3qRsT4uVwX5yZAb6CdEf7GhIj8KlMn9OpQs0TuVw.XyZz/";
string CryptPassword (const string &pass)
{
  long int idx1, idx2;
  double k1, k2;
  char *crypted;
  char salt[2];

  srandom (getpid());

  idx1 = random ();
  k1 = ((double)idx1)/RAND_MAX;
  idx1 = (long int) (k1*strlen(salt_table));
  salt[0] = salt_table[idx1];

  idx2 = random ();
  k2 = ((double)idx2)/RAND_MAX;
  idx2 = (long int) (k2*strlen(salt_table));
  salt[1] = salt_table[idx2];

  crypted = crypt (pass.c_str(), salt);
  if (!crypted)
    {
      ERROR ("Failed to crypt user password");
      return "";
    }
  else
    return crypted;
}
*/

/* Converts a hex character to its integer value */
char from_hex(char ch)
{
  return isdigit(ch) ? ch - '0' : tolower(ch) - 'a' + 10;
}

/*
char to_hex(char code) {
  static char hex[] = "0123456789abcdef";
  return hex[code & 15];
}
*/

/*
char *url_encode(const char *str) {
  const char *pstr = str;
  char *buf = (char*) malloc(strlen(str) * 3 + 1), *pbuf = buf;

  while (*pstr) {
    if (isalnum(*pstr) || *pstr == '-' || *pstr == '_' || *pstr == '.' || *pstr == '~')
      *pbuf++ = *pstr;
    else if (*pstr == ' ')
      *pbuf++ = '+';
    else
      *pbuf++ = '%', *pbuf++ = to_hex(*pstr >> 4), *pbuf++ = to_hex(*pstr & 15);
    pstr++;
  }
  *pbuf = '\0';
  return buf;
}
*/

string url_decode(const string & str)
{
  const char *pstr = str.c_str();

  string res;

  while (*pstr)
    {
      if (*pstr == '%')
        {
          if (pstr[1] && pstr[2])
            {
              res += (from_hex(pstr[1]) << 4 | from_hex(pstr[2]));
              pstr += 2;
            }
        }
      else if (*pstr == '+')
        {
          res += ' ';
        }
      else
        {
          res += *pstr;
        }
      pstr++;
    }

  return res;
}

