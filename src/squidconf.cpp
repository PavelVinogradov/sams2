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
#include "urlgrouplist.h"
#include "urlgroup.h"
#include "proxy.h"
#include "templates.h"
#include "template.h"
#include "timeranges.h"
#include "timerange.h"
#include "tools.h"

//string SquidConf::sams_marker = " # sams marker";


SquidConf::SquidConf()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
}


SquidConf::~SquidConf()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
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
  vector<long> group_ids;
  Template * tpl;
  uint i, j;
  bool haveBlockedUsers = false;
  Proxy::RedirType redir_type = Proxy::getRedirectType ();

  ofstream fncsa;
  string ncsafile = squidconfdir + "/sams2.ncsa";
  Proxy::usrAuthType authType;

  while (fin.good ())
    {
      getline (fin, line);
      if (line.empty ())
        {
          if (!fin.eof ())
            fout << endl;
          continue;
        }

      // Строка от нашей старой конфигурации - игнорируем ее
      if (line.find ("Sams2") != string::npos)
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
              DEBUG (DEBUG2, "Found TAG: acl");
            }
          else if (v[2] == "http_access")
            {
              current_tag = "http_access";
              DEBUG (DEBUG2, "Found TAG: http_access");
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

                  DEBUG(DEBUG2, "Processing "<<users.size()<<" user[s] in template " << tpls[i]);

                  string method;
                  tpl = Templates::getTemplate(tpls[i]);
                  authType = tpl->getAuth ();
                  if (authType == Proxy::AUTH_IP)
                    method = "src";
                  else
                    method = "proxy_auth";

                  if ((authType == Proxy::AUTH_NCSA) && (!fncsa.is_open ()))
                    {
                      fncsa.open (ncsafile.c_str (), ios::out | ios::trunc);
                      if (!fncsa.is_open ())
                        {
                          ERROR ("Unable to open file " << ncsafile);
                        }
                    }

                  //TODO Пользователи могут быть заблокированы из разных шаблонов с разными типами авторизации
                  vector<SAMSUser *>::iterator it;
                  for (it = users.begin(); it != users.end(); it++)
                    {
                      if ((*it)->getEnabled() != SAMSUser::STAT_ACTIVE)
                        {
                          haveBlockedUsers = true;
                          fout << "acl Sams2BlockedUsers " << method << " " << *(*it) << endl;
                        }
                      else
                        {
                          if ((authType == Proxy::AUTH_NCSA) && (fncsa.is_open ()))
                            fncsa << *(*it) << ":" << (*it)->getPassword () << endl;
                          fout << "acl Sams2Template" << tpls[i] << " " << method << " " << *(*it) << endl;
                        }
                    }

                  //Определяем временные границы для текущего шаблона
                  time_ids = tpl->getTimeRangeIds ();
                  for (j = 0; j < time_ids.size(); j++)
                    {
                      TimeRange * tr = TimeRanges::getTimeRange(time_ids[j]);
                      if (!tr)
                        continue;
                      if (tr->isFullDay())
                        continue;
                      if (tr->hasMidnight())
                        {
                          fout << "acl Sams2Template" << tpls[i] << "time time " << tr->getDays () << " ";
                          fout << tr->getStartTimeStr () << "-23:59" << endl;
                          fout << "acl Sams2Template" << tpls[i] << "time time " << tr->getDays () << " ";
                          fout << "00:00-" << tr->getEndTimeStr () << endl;
                        }
                      else
                        {
                          fout << "acl Sams2Template" << tpls[i] << "time time " << tr->getDays () << " ";
                          fout << tr->getStartTimeStr () << "-" << tr->getEndTimeStr () << endl;
                        }
                    }
                }

              // Создаем списки запретных адресов
              group_ids = UrlGroupList::getAllowGroupIds();
              for (i = 0; i < group_ids.size (); i++)
                {
                  UrlGroup * grp = UrlGroupList::getUrlGroup(group_ids[i]);
                  if (!grp)
                    continue;
                  else
                    fout << "acl Sams2Allow" << group_ids[i] << " dstdom_regex " << *grp << endl;
                }

              // Создаем списки разрешенных адресов
              group_ids = UrlGroupList::getDenyGroupIds();
              for (i = 0; i < group_ids.size (); i++)
                {
                  UrlGroup * grp = UrlGroupList::getUrlGroup(group_ids[i]);
                  if (!grp)
                    continue;
                  else
                    fout << "acl Sams2Deny" << group_ids[i] << " dstdom_regex " << *grp << endl;
                }

              if (fncsa.is_open ())
                {
                  fncsa.close ();
/* Смена владельца не актуальна, т.к. демоны авторизации работают под root
                  string chown_cmd = "chown squid " + ncsafile;
                  if (system (chown_cmd.c_str ()))
                    {
                      WARNING ("Unable to change owner of " << ncsafile);
                    }
*/
                }

            } // if (current_tag == "acl")

          // Если используется редиректор, то не вносим новые правила для http_access
          if (current_tag == "http_access")
            {
              fout << "# Setup Sams2 HTTP Access here" << endl;
              vector <long> times;
              basic_stringstream < char >restriction;

              if (haveBlockedUsers)
                fout << "http_access deny Sams2BlockedUsers" << endl;

              vector<SAMSUser *> users;
              for (i = 0; i < tpls.size (); i++)
                {
                  SAMSUsers::getUsersByTemplate (tpls[i], users);
                  if (users.empty ())
                    continue;

                  DEBUG(DEBUG_DAEMON, "Processing template " << tpls[i]);

                  tpl = Templates::getTemplate(tpls[i]);

                  restriction.str("");

                  if (redir_type != Proxy::REDIR_INTERNAL)
                    {
                      time_ids = tpl->getTimeRangeIds ();
                      for (j = 0; j < time_ids.size(); j++)
                        {
                          TimeRange * tr = TimeRanges::getTimeRange(time_ids[j]);
                          if (!tr)
                            continue;
                          if (tr->isFullDay())
                            continue;
                          restriction << " Sams2Template" <<tpls[i] << "time";
                          break;
                        }

                      //Определяем разрешенные и запретные адреса для текущего шаблона
                      group_ids = tpl->getUrlGroupIds ();
                      for (j = 0; j < group_ids.size(); j++)
                        {
                          UrlGroup * grp = UrlGroupList::getUrlGroup(group_ids[j]);
                          if (!grp)
                            continue;
                          if (grp->getAccessType () == UrlGroup::ACC_ALLOW)
                            restriction << " Sams2Allow" << group_ids[j];
                          else if (grp->getAccessType () == UrlGroup::ACC_DENY)
                            restriction << " !Sams2Deny" << group_ids[j];
                        }

                      //Определяем запретные типы файлов для текущего шаблона
                      //Определяем запретные регулярные выражения для текущего шаблона
                    }
                  if (SAMSUsers::activeUsersInTemplate ( tpls[i]) > 0)
                    fout << "http_access allow Sams2Template" << tpls[i] << restriction.str() << endl;
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
      if (line.find ("Sams2") == string::npos)
        out << line << endl;
      getline (in, line);
      if (line.find ("Sams2") != string::npos)
        line[0] = '#';
    }
  return line;
}

