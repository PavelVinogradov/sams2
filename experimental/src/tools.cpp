
#include "tools.h"
#include "debug.h"

#define SPACECHARS " \t\r\n"

string StripComments (const string str)
{
  int hash_pos;
  string res;

  if (str.empty ())
    return "";

  res = str;
  hash_pos = res.find_first_of ('#');
  if (hash_pos >= 0)
    {
      res = res.substr (0, hash_pos);
    }
  return res;
}


string StripCharacters (const string str, const string needless)
{
  string res;
  int i;
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


string TrimCharactersLeft (const string str, const string needless)
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


string TrimCharactersRight (const string str, const string needless)
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

string TrimCharacters (const string str, const string needless)
{
  return TrimCharactersLeft (TrimCharactersRight (str, needless), needless);
}

string TrimSpacesLeft (const string str)
{
  return TrimCharactersLeft (str, SPACECHARS);
}

string TrimSpacesRight (const string str)
{
  return TrimCharactersRight (str, SPACECHARS);
}

string TrimSpaces (const string str)
{
  return TrimCharacters (str, SPACECHARS);
}

int StringToIP (const string url, int *ip, int *mask)
{
  int slash_pos;
  int num;
  int bit, bit2;
  int i;
  int size_ip;
  int size_mask;
  string str_ip;
  string str_mask;
  std::vector < string > tbl_ip;
  std::vector < string > tbl_mask;

  DEBUG (DEBUG9, url);
  if (ip == NULL || mask == NULL)
    return 0;

  // Устанавливаем первоначальные значения
  for (i = 0; i < 6; i++)
    {
      ip[i] = 0;
      mask[i] = 255;
    }

  // Разбиваем на две части - адрес и маску
  slash_pos = url.find_first_of ('/');
  if (slash_pos >= 0)
    {
      str_ip = url.substr (0, slash_pos);
      str_mask = url.substr (slash_pos + 1, url.size () - slash_pos);
    }
  else
    {
      str_ip = url;
      str_mask = "";
    }

  // Анализ адреса
  Split (str_ip, ".", tbl_ip);
  size_ip = tbl_ip.size ();
  if ((size_ip != 0) && (size_ip != 4) && (size_ip != 6))
    {
      WARNING ("Incorrect IP: " << url);
      return 0;
    }
  for (i = 0; i < size_ip; i++)
    {
      num = atoi (tbl_ip[i].c_str ());
      if (num > 255)
        {
          WARNING ("Incorrect IP: " << url);
          return 0;
        }
      ip[i] = num;
    }

  // Анализ маски
  Split (str_mask, ".", tbl_mask);
  size_mask = tbl_mask.size ();
  if ((size_mask != 0) && (size_mask != 1) && (size_mask != 4) && (size_mask != 6))
    {
      WARNING ("Incorrect IP mask: " << url);
      return 0;
    }
  if (size_mask == 1)           // Маска представлена в виде количества бит
    {
      num = atoi (tbl_mask[0].c_str ());
      if (num > 48)
        {
          WARNING ("Incorrect IP mask: " << url);
          return 0;
        }
      bit = num;

      bit2 = bit / 8;
      for (i = 0; i < bit2; i++)
        {
          mask[i] = 255;
        }
      mask[i] = 255 & ((char) 255 << (8 - (bit - bit2 * 8)));
    }
  else                          // Полное указание маски
    {
      for (i = 0; i < size_mask; i++)
        {
          num = atoi (tbl_mask[i].c_str ());
          if (num > 255)
            {
              WARNING ("Incorrect IP mask: " << url);
              return 0;
            }
          mask[i] = num;
        }
    }
  return size_ip;
}

void Split (const string s, const string delim, std::vector < string > &tbl)
{
  int prev = 0;
  int next = 0;

  next = s.find_first_of (".");

  string token;
  while ((next = s.find_first_of (delim, prev)) != -1)
    {
      token = s.substr (prev, next - prev);
      if (token.size () > 0)
        {
          tbl.push_back (token);
        }
      prev += next - prev + 1;
    }
  if (prev < s.size ())
    {
      token = s.substr (prev, s.size () - prev);
      if (token.size () > 0)
        {
          tbl.push_back (token);
        }
    }
}

string IPToString (int ip[6], int mask[6], int octets)
{
  char s[DEBUG_LINE_SIZE];

//  DEBUG(DEBUG4,  "[" << this << "] ");

  if (octets == 0)
    return "";

  if (octets == 4)
    {
      sprintf (&s[0], "%03d.%03d.%03d.%03d/%03d.%03d.%03d.%03d", ip[0], ip[1], ip[2], ip[3], mask[0], mask[1], mask[2], mask[3]);
    }
  if (octets == 6)
    {
      sprintf (&s[0], "%03d.%03d.%03d.%03d.%03d.%03d/%03d.%03d.%03d.%03d.%03d.%03d", ip[0], ip[1], ip[2], ip[3], ip[4], ip[5], mask[0], mask[1], mask[2], mask[3], mask[4], mask[5]);
    }
  return s;
}
