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

#include "tools.h"
#include "debug.h"

#define SPACECHARS " \t\r\n"

string StripComments (const string & str)
{
  int hash_pos;
  string res;

  if (str.empty ())
    return "";

  res = str;
  hash_pos = res.find_first_of ("#;");
  if (hash_pos >= 0)
    {
      res = res.substr (0, hash_pos);
    }
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
  string res;

  if (str.empty ())
    return "";

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
  string res;

  if (str.empty ())
    return "";

  pos = str.find_last_not_of (needless);
  if (pos >= 0)
    res = str.substr (0, pos + 1);
  else
    res = str;

  return res;
}

string TrimCharacters (const string & str, const string & needless)
{
  return TrimCharactersLeft (TrimCharactersRight (str, needless), needless);
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

void Split (const string & s, const string & delim, vector < string > &tbl, bool removeEmpty)
{
  uint prev = 0;
  int next = 0;

  DEBUG (DEBUG9, s << ", '" << delim << "', " << removeEmpty);

  next = s.find_first_of (delim);

  string token;
  while ((next = s.find_first_of (delim, prev)) != -1)
    {
      token = s.substr (prev, next - prev);

      if (token.size () > 0)
        {
          DEBUG (DEBUG9, "token: " << token);
          tbl.push_back (token);
        }
      else if (!removeEmpty)
        {
          DEBUG (DEBUG9, "token: " << token);
          tbl.push_back (token);
        }
      prev += next - prev + 1;
    }
  if (prev < s.size ())
    {
      token = s.substr (prev, s.size () - prev);
      if (token.size () > 0)
        {
          DEBUG (DEBUG9, "token: " << token);
          tbl.push_back (token);
        }
      else if (!removeEmpty)
        {
          DEBUG (DEBUG9, "token: " << token);
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
              DEBUG (DEBUG_FILE, fullname << " deleted.");
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
      ERROR (path << ": " << strerror (errno));
    }
  else
    {
      DEBUG (DEBUG_FILE, path << " deleted.");
    }

  return res;
}

bool fileExist (const string & path)
{
  if (path.empty ())
    {
      WARNING ("Empty path");
      return false;
    }

  struct stat buffer;
  int status;

  status = stat (path.c_str (), &buffer);
  if (status == 0)
    {
      DEBUG (DEBUG_FILE, path << ": file exists.");
      return true;
    }

  DEBUG (DEBUG_FILE, path << ": " << strerror (errno));

  return false;
}
