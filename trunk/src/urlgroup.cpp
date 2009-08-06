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

using namespace std;

#include "urlgroup.h"
#include "url.h"
#include "proxy.h"
#include "tools.h"

#include "debug.h"

string UrlGroup::toString (accessType t)
{
  string res;
  switch (t)
    {
    case ACC_DENY:
      res = "deny";
      break;
    case ACC_ALLOW:
      res = "allow";
      break;
    case ACC_REGEXP:
      res = "regex";
      break;
    case ACC_REDIR:
      res = "redir";
      break;
    case ACC_REPLACE:
      res = "replace";
      break;
    case ACC_FILEEXT:
      res = "files";
      break;
    default:
      res = "unknown";
      break;
    }
  return res;
}

UrlGroup::UrlGroup (const long &id, const UrlGroup::accessType &access)
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "(" << id << ")]");

  _id = id;
  _type = access;
}

UrlGroup::~UrlGroup ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
  _list.clear ();

#ifdef USE_PCRECPP
  vector<pcrecpp::RE*>::iterator it;
  for (it = _patterns.begin (); it != _patterns.end (); it++)
    {
      delete (*it);
    }
  _patterns.clear ();
#endif

#ifdef USE_PCRE
  _patterns.clear ();
#endif
}

long UrlGroup::getId ()
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] = " << _id);
  return _id;
}

UrlGroup::accessType UrlGroup::getAccessType ()
{
  return _type;
}

void UrlGroup::addUrl (const string & url)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << url << ")]");

/*
  if (_type == UrlGroup::ACC_REGEXP || _type == UrlGroup::ACC_REPLACE || _type == UrlGroup::ACC_REDIR)
    {
*/
#ifdef USE_PCRECPP
      pcrecpp::RE *re;

      re = new pcrecpp::RE(url);
      if (!re->error().empty ())
        {
          WARNING ("Mailformed regexp pattern " << url << " " << re->error ());
          delete re;
        }
      else
        {
          _patterns.push_back (re);
          /// TODO Убрать использование переменной _list если _type=ACC_REGEXP, но для этого подправить asString()
          _list.push_back (url);
        }
#endif
#ifdef USE_PCRE
      int erroroffset;
      const char *error;
      pcre *re;

      re = pcre_compile(url.c_str (), 0, &error, &erroroffset, NULL);
      if (re == NULL)
        {
          WARNING ("Mailformed regexp pattern " << url);
        }
      else
        {
          _patterns.push_back (re);
          _list.push_back (url);
        }
#endif
/*
    }
  else
    _list.push_back (url);
*/
}

bool UrlGroup::hasUrl (const string & url) const
{
  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "(" << url << ")] type=" << toString (_type));

  Url u;
  u.setUrl (url);

  string str;

  if (_type == UrlGroup::ACC_DENY || _type == UrlGroup::ACC_ALLOW || _type == UrlGroup::ACC_REPLACE)
    {
      str = ((u.getProto ().empty ())?(""):(u.getProto () + "://")) +
            u.getAddress () +
            ((u.getPort ().empty ())?(""):(":"+u.getPort ()));
    }
  if (_type == UrlGroup::ACC_REGEXP || _type == UrlGroup::ACC_REDIR)
    {
      str = url;
    }
  else if (_type == UrlGroup::ACC_FILEEXT)
    {
      str = u.getPath ();
    }

  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] using " << str);

#ifndef WITHOUT_PCRE

      uint idx;
#ifdef USE_PCRE
      int ovector[300];
#endif

      for (idx = 0; idx < _patterns.size (); idx++)
        {
          DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] Checking rule " << _list[idx]);
#ifdef USE_PCRECPP
          if (_patterns[idx]->PartialMatch (str))
            {
              DEBUG (DEBUG4, "[" << this << "] Found rule " << _patterns[idx]->pattern () << " for " << str);
              return true;
            }
#endif
#ifdef USE_PCRE
          if (pcre_exec (_patterns[idx], NULL, str.c_str (), str.size (), 0, 0, ovector, 300) >= 0)
            {
              DEBUG (DEBUG4, "[" << this << "] Found rule " << _list[idx] << " for " << str);
              return true;
            }
#endif
        }
#endif // #ifndef WITHOUT_PCRE
      return false;
}

void UrlGroup::setReplacement (const string & dest)
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << dest << ")]");
  _destination = dest;
}

string UrlGroup::modifyUrl (const string & url) const
{
  DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "(" << url << ")]");

  string res = "";
  if (hasUrl (url))
    {
      switch (_type)
        {
          case ACC_DENY:
          case ACC_ALLOW:
          case ACC_REGEXP:
          case ACC_FILEEXT:
            break;
          case ACC_REDIR:
            res = Proxy::getRedirectAddr ();
            break;
          case ACC_REPLACE:
            res = "301:" + _destination;
            break;
        }
    }
  return res;
}

string UrlGroup::asString () const
{
  string res = "";
  vector<string>::const_iterator it;

  for (it = _list.begin (); it != _list.end (); it++)
    {
      if ( ! res.empty () )
        res += " ";
      res += (*it);
    }

  return res;
}

ostream & operator<< (ostream & out, const UrlGroup & grp)
{
  out << grp.asString();
  return out;
}
