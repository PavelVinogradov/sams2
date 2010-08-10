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
#include "samsuserlist.h"
#include "samsuser.h"
#include "urlgrouplist.h"
#include "urlgroup.h"
#include "url.h"
#include "localnetworks.h"
#include "net.h"
#include "proxy.h"
#include "templatelist.h"
#include "template.h"
#include "timerangelist.h"
#include "timerange.h"
#include "delaypoollist.h"
#include "delaypool.h"
#include "tools.h"

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

  vector <Template *> tpls = TemplateList::getList ();
  vector <Template *>::iterator tpls_it;
  vector <TimeRange*> times = TimeRangeList::getList ();
  vector <TimeRange*>::const_iterator time_it;
  vector <long> time_ids;
  vector <long> group_ids;
  Template * tpl;
  TimeRange * trange;
  uint j;
  bool haveBlockedUsers = false;
  bool haveLocalName = false;
  bool haveLocalAddr = false;
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

      // Не меняем некоторые параметры, даже если есть подстрока 'Sams2'
      if (line.find ("deny_info") == 0)
        {
          fout << line << endl;
          continue;
        }

      // Строка от нашей старой конфигурации - игнорируем ее
      if (line.find ("Sams2") != string::npos)
        continue;

      // Ограничение скорости настраивается демоном, поэтому стираем текущие настройки
      if (line.find ("delay_") == 0 && Proxy::useDelayPools())
        continue;

      // Используется встроенный редиректор, и какой-то редиректор уже определен
      if ( (line.find ("url_rewrite_program") == 0 || line.find ("redirect_program") == 0) &&
          Proxy::getRedirectType() == Proxy::REDIR_INTERNAL)
        {
          if (line.find("/samsredir") != string::npos) // определен встроенный редиректор - не меняем ничего
            fout << line << endl;
          continue; // определен сторонний редиректор, хотя нужно встроенный - стираем настройки
        }

      if (line[0] == '#' && line.find ("TAG:") != string::npos)
        {
          fout << line << endl;
          nextline = skipComments (fin, fout);

          Split(line, " \t\n", v);
          current_tag = "unknown";
          if (  (v[2] == "acl") || (v[2] == "http_access") ||
                (v[2] == "url_rewrite_access") || (v[2] == "redirector_access") ||
                (v[2] == "url_rewrite_program") || (v[2] == "redirector_program") ||
                (v[2] == "url_rewrite_children") || (v[2] == "redirector_children") ||
                (v[2] == "delay_pools") || (v[2] == "delay_class") ||
                (v[2] == "delay_access") || (v[2] == "delay_parameters")
             )
            {
              current_tag = v[2];
              DEBUG (DEBUG2, "Found TAG: " << current_tag);
            }

          if (current_tag == "acl")
            {
              // Создаем списки временных границ
              for (time_it = times.begin (); time_it != times.end (); time_it++)
                {
                      if ((*time_it)->isFullDay ())
                        continue;

                      fout << "acl Sams2Time" << (*time_it)->getId () << " time " << (*time_it)->getDays () << " ";
                      if ((*time_it)->hasMidnight ())
                        fout << (*time_it)->getEndTimeStr () << "-" << (*time_it)->getStartTimeStr () << endl;
                      else
                        fout << (*time_it)->getStartTimeStr () << "-" << (*time_it)->getEndTimeStr () << endl;
                }

              // Создаем списки пользователей
              vector<SAMSUser *> users;
              for (tpls_it = tpls.begin (); tpls_it != tpls.end (); tpls_it++)
                {
                  tpl = *tpls_it;
                  SAMSUserList::getUsersByTemplate (tpl->getId (), users);
                  if (users.empty ())
                    continue;

                  DEBUG(DEBUG2, "Processing "<<users.size()<<" user[s] in template " << tpl->getId ());

                  string method;
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

                  /// TODO Пользователи могут быть заблокированы из разных шаблонов с разными типами авторизации
                  vector<SAMSUser *>::iterator it;
                  for (it = users.begin(); it != users.end(); it++)
                    {
                      if ( (redir_type == Proxy::REDIR_NONE) &&
                           ((*it)->getEnabled() != SAMSUser::STAT_ACTIVE) &&
                           ((*it)->getEnabled() != SAMSUser::STAT_LIMITED))
                        {
                          // Блокируем пользователей в squid.conf только если никакой редиректор не используется.
                          haveBlockedUsers = true;
                          fout << "acl Sams2BlockedUsers " << method << " " << *(*it) << endl;
                        }
                      else
                        {
                          if ((authType == Proxy::AUTH_NCSA) && (fncsa.is_open ()))
			  {
                            fncsa << *(*it) << ":" << (*it)->getPassword () << endl;
			  }

                          fout << "acl Sams2Template" << tpl->getId () << " " << method << " " << *(*it) << endl;
                        }
                    }

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

              if (redir_type == Proxy::REDIR_NONE)
                {
                  vector<UrlGroup *>::iterator grp_it;
                  vector<UrlGroup *> grps = UrlGroupList::getAllGroups ();
                  for (grp_it = grps.begin (); grp_it != grps.end (); grp_it++)
                    {
                      long grp_id = (*grp_it)->getId ();
                      switch ( (*grp_it)->getAccessType () )
                        {
                          case UrlGroup::ACC_DENY:
                            fout << "acl Sams2Deny" << grp_id << " dstdom_regex " << *(*grp_it) << endl;
                            break;
                          case UrlGroup::ACC_ALLOW:
                            fout << "acl Sams2Allow" << grp_id << " dstdom_regex " << *(*grp_it) << endl;
                            break;
                          case UrlGroup::ACC_REGEXP:
                            fout << "acl Sams2Regexp" << grp_id << " url_regex " << *(*grp_it) << endl;
                            break;
                          case UrlGroup::ACC_REDIR:
                            break;
                          case UrlGroup::ACC_REPLACE:
                            break;
                          case UrlGroup::ACC_FILEEXT:
                            fout << "acl Sams2Fileext" << grp_id << " urlpath_regex " << *(*grp_it) << endl;
                            break;
                        }
                    }
                  vector<Net *>::iterator net_it;
                  vector<Net *> nets = LocalNetworks::getAllNetworks ();
                  for (net_it = nets.begin (); net_it != nets.end (); net_it++)
                    {
                      fout << "acl Sams2Local";// << net_it->getId ();
                      if ((*net_it)->isDomain())
                        {
                          haveLocalName = true;
                          fout << "Name dstdomain ";
                        }
                      else
                        {
                          haveLocalAddr = true;
                          fout << "Addr dst ";
                        }
                      fout << (*net_it)->asString () << endl;
                    }
                }

            } // if (current_tag == "acl")
          else if (current_tag == "http_access")
            {
              fout << "# Setup Sams2 HTTP Access here" << endl;
              vector <long> times;
              basic_stringstream < char >restriction;
              vector <string> restriction_time;
              vector <string> restriction_allow;
              vector <string> restriction_deny;

              if (haveLocalName)
                fout << "http_access allow Sams2LocalName" << endl;

              if (haveLocalAddr)
                fout << "http_access allow Sams2LocalAddr" << endl;

              if (haveBlockedUsers)
                fout << "http_access deny Sams2BlockedUsers" << endl;

              vector<SAMSUser *> users;
              for (tpls_it = tpls.begin (); tpls_it != tpls.end (); tpls_it++)
                {
                  tpl = *tpls_it;
                  SAMSUserList::getUsersByTemplate (tpl->getId (), users);
                  if (users.empty ())
                    continue;

                  DEBUG(DEBUG_DAEMON, "Processing template " << tpl->getId ());

                  restriction_time.clear();
                  restriction_allow.clear();
                  restriction_deny.clear();

                  if (redir_type == Proxy::REDIR_NONE)
                    {
                      time_ids = tpl->getTimeRangeIds ();
                      for (j = 0; j < time_ids.size(); j++)
                        {
                          trange = TimeRangeList::getTimeRange(time_ids[j]);
                          if (!trange)
                            continue;
                          if (trange->isFullDay ())
                            continue;
                          restriction.str("");
                          if (trange->hasMidnight ())
                            restriction << "!Sams2Time" << time_ids[j];
                          else
                            restriction << "Sams2Time" << time_ids[j];
                          restriction_time.push_back(restriction.str());
                        }

                      //Определяем разрешающие и запрещающие правила для текущего шаблона
                      group_ids = tpl->getUrlGroupIds ();
                      for (j = 0; j < group_ids.size(); j++)
                        {
                          UrlGroup * grp = UrlGroupList::getUrlGroup(group_ids[j]);
                          if (!grp)
                            continue;
                          restriction.str("");
// fout << "# Setup Sams2 http_acess: ACC_DENY: " << grp->getAccessType () << endl;
                          switch (grp->getAccessType ())
                            {
                              case UrlGroup::ACC_DENY:
                                restriction << "Sams2Deny" << group_ids[j];
                                restriction_deny.push_back(restriction.str());
                                break;
                              case UrlGroup::ACC_ALLOW:
                                restriction << "Sams2Allow" << group_ids[j];
                                restriction_allow.push_back(restriction.str());
                                break;
                              case UrlGroup::ACC_REGEXP:
                                restriction << "Sams2Regexp" << group_ids[j];
                                restriction_deny.push_back(restriction.str());
                                break;
                              case UrlGroup::ACC_REDIR:
                                break;
                              case UrlGroup::ACC_REPLACE:
                                break;
                              case UrlGroup::ACC_FILEEXT:
                                restriction << "Sams2Fileext" << group_ids[j];
                                restriction_deny.push_back(restriction.str());
                                break;
                            }
                        }
                    }

                  // Если используется внешний редиректор, то он проверяет ограничения, а squid пускает всех
                  if (redir_type != Proxy::REDIR_NONE)
                    fout << "http_access allow Sams2Template" << tpl->getId () << endl;
                  else if (SAMSUserList::activeUsersInTemplate (tpl->getId ()) > 0)
                    {
                      // Если нет ограничений у шаблона, то просто разрешаем доступ
                      if ((restriction_deny.size()==0) && (restriction_allow.size()==0) && (restriction_time.size()==0))
                        {
                          fout << "http_access allow Sams2Template" << tpl->getId () << endl;
                        }
                      else // Накладываем различные ограничения
                        {
                          uint idx;
                          for (idx=0; idx<restriction_deny.size(); idx++)
                              fout << "http_access allow Sams2Template" << tpl->getId () << " !" << restriction_deny[idx] << endl;

                          //Если запрещен доступ ко всем ресурсам
		          if ( tpl->getAllDeny () != 0 )
		          {
                            for (idx=0; idx<restriction_allow.size(); idx++)
                                fout << "http_access deny Sams2Template" << tpl->getId () << " !" << restriction_allow[idx] << endl;
                          }
                          else
		          {
                            for (idx=0; idx<restriction_allow.size(); idx++)
                                fout << "http_access allow Sams2Template" << tpl->getId () << " " << restriction_allow[idx] << endl;
                          }

                          for (idx=0; idx<restriction_time.size(); idx++)
                              fout << "http_access allow Sams2Template" << tpl->getId () << " " << restriction_time[idx] << endl;
                        }
                    }
                }
            } //if (current_tag == "http_access")
          else if (current_tag == "url_rewrite_access" || current_tag == "redirector_access")
            {
              Url proxy_url;
              proxy_url.setUrl (Proxy::getDenyAddr ());
              string proxy_addr = proxy_url.getAddress ();
              if (!proxy_addr.empty ())
                {
                  fout << "acl Sams2Proxy dst " << proxy_addr << endl;
                  fout << current_tag << " deny Sams2Proxy" << endl;
                }
              else
                {
                  WARNING ("Unable to identify proxy address");
                }
            }
          else if ( (current_tag == "url_rewrite_program" || current_tag == "redirector_program") &&
                Proxy::getRedirectType() == Proxy::REDIR_INTERNAL)
            {
              // в следующей строке определен нужный редиректор, не меняем настройки
              if (nextline.find (current_tag) == 0 && nextline.find("/samsredir") != string::npos)
                {
                  fout << nextline << endl;
                  continue;
                }
              else
                {
                  string samspath = SamsConfig::getString (defSAMSHOME, err);

                  if (samspath.empty ())
                    {
                      ERROR (defSAMSHOME << " not defined. Check config file.");
                      continue;
                    }
                  if (!fileExist(samspath+"/bin/samsredir"))
                    {
                      ERROR (samspath << "/bin/samsredir" << " not found. (Wrong " << defSAMSHOME << " value in config file?).");
                      continue;
                    }
                  fout << current_tag << " " << samspath << "/bin/samsredir" << endl;
                }
            }
          else if ( (current_tag == "url_rewrite_children" || current_tag == "redirector_children") &&
                Proxy::getRedirectType() != Proxy::REDIR_NONE)
            {
              // в следующей строке уже определено количество редиректоров, не меняем настройки
              if (nextline.find (current_tag) == 0)
                {
                  fout << nextline << endl;
                  continue;
                }
              else
                {
                  fout << current_tag  << " 5" << endl;
                }
            }
          else if (current_tag == "delay_pools" && Proxy::useDelayPools())
            {
              fout << "delay_pools " << DelayPoolList::count () << endl;
            }
          else if (current_tag == "delay_class" && Proxy::useDelayPools())
            {
              vector<DelayPool*> pools = DelayPoolList::getList ();
              for (unsigned int i=0; i < pools.size (); i++)
                {
                  fout << "delay_class " << i+1 << " " << pools[i]->getClass () << endl;
                }
            }
          else if (current_tag == "delay_access" && Proxy::useDelayPools())
            {
              vector<DelayPool*> pools = DelayPoolList::getList ();
              map <long, bool> link;
              map <long, bool>::const_iterator it;
              for (unsigned int i=0; i < pools.size (); i++)
                {
                  link = pools[i]->getTemplates ();
                  for (it = link.begin (); it != link.end (); it++)
                    {
                      if (SAMSUserList::activeUsersInTemplate (it->first) > 0)
                        fout << "delay_access " << i+1 << " " << ((it->second==true)?"deny":"allow") << " Sams2Template" << it->first << endl;
                    }

                  link = pools[i]->getTimeRanges ();
                  for (it = link.begin (); it != link.end (); it++)
                    {
                      trange = TimeRangeList::getTimeRange(it->first);
                      if (!trange)
                        continue;
                      if (trange->isFullDay ())
                        continue;

                      fout << "delay_access " << i+1 << " " << ((it->second==true)?"deny":"allow");
                      if (trange->hasMidnight ())
                        fout << " !Sams2Time" << it->first;
                      else
                        fout << " Sams2Time" << it->first;
                      fout << endl;
                    }

                  fout << "delay_access " << i+1 << " deny all" << endl;
                }
            }
          else if (current_tag == "delay_parameters" && Proxy::useDelayPools())
            {
              vector<DelayPool*> pools = DelayPoolList::getList ();
              long agg1, agg2;
              long net1, net2;
              long ind1, ind2;
              for (unsigned int i=0; i < pools.size (); i++)
                {
                  pools[i]->getAggregateParams (agg1, agg2);
                  pools[i]->getNetworkParams (net1, net2);
                  pools[i]->getIndividualParams (ind1, ind2);

                  fout << "delay_parameters " << i+1 << " ";
                  switch (pools[i]->getClass ())
                    {
                      case 1:
                        fout << agg1 << "/" << agg2 << endl;
                        break;
                      case 2:
                        fout << agg1 << "/" << agg2 << " " << ind1 << "/" << ind2 << endl;
                        break;
                      case 3:
                        fout << agg1 << "/" << agg2 << " " << net1 << "/" << net2 << " " << ind1 << "/" << ind2 << endl;
                        break;
                      default:
                        break;
                    }
                }
            }

          // Ограничение скорости настраивается демоном, поэтому стираем текущие настройки
          if (nextline.find ("delay_") == 0 && Proxy::useDelayPools())
            continue;

          // Используется встроенный редиректор, и какой-то редиректор уже определен
          if (nextline.find ("url_rewrite_program") == 0 && Proxy::getRedirectType() == Proxy::REDIR_INTERNAL)
            {
              if (nextline.find("/samsredir") != string::npos) // определен встроенный редиректор - не меняем ничего
                fout << nextline << endl;
              continue; // определен сторонний редиректор, стираем настройки
            }

          if ( Proxy::getRedirectType() == Proxy::REDIR_NONE &&
                (nextline.find ("url_rewrite_program") == 0 || nextline.find ("redirector_program") == 0 ||
                nextline.find ("url_rewrite_children") == 0 || nextline.find ("redirector_children") == 0)
              )
            continue;

          if (nextline.find ("Sams2") == string::npos)
	    {
               fout << nextline << endl;
              continue;
            }

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

