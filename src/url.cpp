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
#include "url.h"
#include "debug.h"
#include <stdlib.h>

Url::Url ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
}

Url::~Url ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
}

void Url::setUrl (const string & url)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << url << ")]");
  _url = url;
  _proto = "";
  _user = "";
  _pass = "";
  _addr = "";
  _port = "";
  _path = "";
  parse ();
}

string Url::getProto ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _proto);
  return _proto;
}

string Url::getUser ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _user);
  return _user;
}

string Url::getPass ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = *hidden*");
  return _pass;
}

string Url::getAddress ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _addr);
  return _addr;
}

string Url::getPort ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _port);
  return _port;
}

string Url::getPath ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _path);
  return _path;
}

string Url::asString () const
{
  return _url;
}

Url *Url::fromString (const string & str)
{
  Url *obj = new Url ();
  obj->setUrl (str);
  return obj;
}

void Url::parse ()
{
  int idx_double_slash;
  int idx_at;
  int idx_colon;
  int idx_path;
  int idx_query;
  string line;

  line = _url;

  // На входе строка имеет такой вид:
  // [protocol://][login[@password]:]<canonical.name.dom|ip.address>[:port][/path | ?query]

  // Если нашли строку :// то до нее - это протокол.
  // Вытащим его из строки
  idx_double_slash = line.find ("://");
  if (idx_double_slash != -1)
    {
      _proto = line.substr (0, idx_double_slash);
      DEBUG (DEBUG9, "Protocol: " << _proto);
      line.erase (0, idx_double_slash + 3);
    }

  // Из оставшегося все что идет после символа '/' или '?' это путь до ресурса.
  // Вытащим его из строки
  idx_query = 0;
  while (idx_query != -1)
    {
      idx_query = line.find ('?', idx_query + 1);

      if (idx_query != -1)
        {
          if (line[idx_query - 1] == '\\')      //Если знак вопроса экранирован, значит это не начало запроса
            continue;
          else
            break;
        }
    }

  idx_path = line.find ('/');

  if (idx_path != -1 && idx_query != -1)
    {
      if (idx_query < idx_path)
        idx_path = idx_query;
    }
  else if (idx_path == -1 && idx_query != -1)
    {
      idx_path = idx_query;
    }

  if (idx_path != -1)
    {
      _path = line.substr (idx_path, line.size () - idx_path);
      DEBUG (DEBUG9, "Path (or query): " << _path);
      line.erase (idx_path, line.size () - idx_path);
    }

  // Оставшаяся строка может иметь такой вид:
  // [login[@password]:]<canonical.name.dom|ip.address>[:port]

  // Определим, указан ли порт
  idx_colon = line.rfind (':');
  if (idx_colon != -1)
    {
      // это может быть указан порт,
      // а может и разделитель пароля и адреса
      _port = line.substr (idx_colon + 1, line.size () - idx_colon - 1);

      // Порт не может быть 0, поэтому если функция atoi вернула 0, то произошла ошибка
      if (atoi (_port.c_str ()) != 0)
        // Все верно, это указан порт
        {
          line.erase (idx_colon, line.size () - idx_colon);
        }
      else
        // не подошло, значит порт здесь не указан
        {
          _port = "";
        }
      DEBUG (DEBUG9, "Port: " << _port);
    }


  // Оставшаяся строка может иметь такой вид:
  // [login[@password]:]<canonical.name.dom|ip.address>

  // Если нашли символ '@' то до него - это логин.
  // Вытащим его из строки
  idx_at = line.find ('@');
  if (idx_at != -1)
    {
      _user = line.substr (0, idx_at);
      DEBUG (DEBUG9, "User: " << _user);
      line.erase (0, idx_at + 1);
    }

  // Если нашли символ ':' то до него - это пароль или логин.
  idx_colon = line.find (':');

  // обнаружен то-ли пароль, то-ли логин
  // определим что именно и вытащим его из строки
  if (idx_colon != -1)
    {
      if (idx_at == -1)         // логин ранее не встречался, значит он и есть
        {
          _user = line.substr (0, idx_colon);
          DEBUG (DEBUG9, "User: " << _user);
        }
      else                      // логин уже был, значит это пароль
        {
          _pass = line.substr (0, idx_colon);
          DEBUG (DEBUG9, "Password: " << _pass);
        }
      line.erase (0, idx_colon + 1);
    }

  // Все что осталось и есть адрес
  _addr = line;

  DEBUG (DEBUG9, "Address: " << _addr);
}

ostream & operator<< (ostream & out, const Url & url)
{
  out << url.asString ();
  return out;
}
