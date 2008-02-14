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
#include <sstream>

#include "squidconf.h"

#include "debug.h"
#include "samsconfig.h"
#include "samsusers.h"
#include "samsuser.h"
#include "proxy.h"
#include "templates.h"
#include "template.h"
#include "timeranges.h"
#include "timerange.h"
#include "tools.h"

string SquidConf::sams2_marker = " # sams2 marker";


SquidConf::SquidConf()
{
}


SquidConf::~SquidConf()
{
}

bool SquidConf::defineAccessRules()
{
  return defineACL ();
}

bool SquidConf::defineACL ()
{
  int err;

  string squidconfdir = SamsConfig::getString (defSQUIDCONFDIR, err);

  if (squidconfdir.empty ())
    {
      ERROR (defSQUIDCONFDIR << " not defined. Check config file.");
      return false;
    }

  string squidconffile = squidconfdir + "/squid.conf";
  string squidbakfile = squidconfdir + "/squid.conf.bak";

  if (!fileCopy (squidconffile, squidbakfile))
    return false;

  ofstream fout;
  fout.open (squidconffile.c_str (), ios::out);
  if (!fout.is_open ())
    {
      ERROR ("Unable to create file " << squidconffile);
      return false;
    }

  ifstream fin;
  fin.open (squidbakfile.c_str (), ios::in);
  if (!fin.is_open ())
    {
      ERROR ("Unable to open file " << squidbakfile);
      fout.close();
      return false;
    }

  string line;
  string nextline;
  string current_tag = "unknown";
  vector <string> v;

  vector<long> tpls = Templates::getIds();
  vector<long> time_ids;
  Template * tpl;
  uint i, j;

  while (fin.good ())
    {
      getline (fin, line);
      if (line.empty ())
        {
          if (!fin.eof ())
            fout << endl;
          continue;
        }

      if (line.find (sams2_marker) != string::npos)
        continue;

      if (line[0] == '#' && line.find ("TAG:") != string::npos)
        {
          fout << line << endl;
          nextline = skipComments (fin, fout);

          Split(line, " \t\n", v);
          current_tag = "unknown";
          if (v[2] == "acl")
            {
              current_tag = "acl";
              DEBUG (DEBUG8, "Found TAG: acl");
            }
          else if (v[2] == "http_access")
            {
              current_tag = "http_access";
              DEBUG (DEBUG8, "Found TAG: http_access");
            }

          if (current_tag == "acl")
            {
              // Создаем списки пользователей
              vector<SAMSUser *> users;
              for (i = 0; i < tpls.size (); i++)
                {
                  SAMSUsers::getUsersByTemplate (tpls[i], users);
                  if (users.empty ())
                    continue;

                  DEBUG(DEBUG_DAEMON, "Processing "<<users.size()<<" user[s] in template " << tpls[i]);

                  string method;
                  tpl = Templates::getTemplate(tpls[i]);
                  if (tpl->getAuth () == Proxy::AUTH_IP)
                    method = "src";
                  else
                    method = "proxy_auth";
                  vector<SAMSUser *>::iterator it;

                  for (it = users.begin(); it != users.end(); it++)
                    {
                      fout << "acl tpl" <<tpls[i] << " " << method << " " << *(*it) << sams2_marker << endl;
                    }
                }

              // Создаем списки временных границ
              time_ids = TimeRanges::getIds();
              for (i = 0; i < time_ids.size (); i++)
                {
                  TimeRange * tr = TimeRanges::getTimeRange(time_ids[i]);
                  if (tr->hasMidnight ())
                    {
                    fout << "acl time" <<time_ids[i] << " time " << tr->getDays () << " " << tr->getStartTimeStr () << "-23:59" << sams2_marker << endl;
                    fout << "acl time" <<time_ids[i] << " time " << tr->getDays () << " " << "00:00-" << tr->getEndTimeStr () << sams2_marker << endl;
                    }
                  else
                    fout << "acl time" <<time_ids[i] << " time " << tr->getDays () << " " << tr->getStartTimeStr () << "-" << tr->getEndTimeStr () << sams2_marker << endl;
                }
            }

          if (current_tag == "http_access")
            {
              fout << "# Setup HTTP Access here" << sams2_marker << endl;
              vector <long> times;
              basic_stringstream < char >restrict;
              for (i = 0; i < tpls.size (); i++)
                {
                  tpl = Templates::getTemplate(tpls[i]);
                  if ((tpl->getAuth() != Proxy::AUTH_NCSA) && (tpl->getAuth() != Proxy::AUTH_IP))
                    {
                      DEBUG(DEBUG_DAEMON, "Template " << tpls[i] << " has external auth scheme, skipping.");
                      continue;
                    }

                  //Определяем временные границы для текущего шаблона
                  restrict.str("");
                  time_ids = tpl->getTimeRangeIds ();
                  for (j = 0; j < time_ids.size(); j++)
                    restrict << " time" << time_ids[j];

                  //Определяем запретные адреса для текущего шаблона
                  //Определяем запретные типы файлов для текущего шаблона
                  //Определяем запретные регулярные выражения для текущего шаблона

                  restrict.str("");

                  fout << "http_access allow tpl" << tpls[i] << restrict.str() << sams2_marker << endl;
                }
            }
          fout << nextline << endl;
        }
      else
        {
          fout << line << endl;
        }
    }

  fin.close ();
  fout.close ();

  return true;
}

string SquidConf::skipComments (ifstream & in, ofstream & out)
{
  string line;
  getline (in, line);
  while (in.good () && line[0] == '#')
    {
      if (line.find (sams2_marker) == string::npos)
        out << line << endl;
      getline (in, line);
      if (line.find (sams2_marker) != string::npos)
        line[0] = '#';
    }
  return line;
}

