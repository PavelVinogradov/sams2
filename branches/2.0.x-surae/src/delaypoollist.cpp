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
#include <limits.h>
#include <string.h>
#include <algorithm>

#include "config.h"

#include "dbconn.h"
#include "dbquery.h"
#include "delaypoollist.h"
#include "delaypool.h"
#include "debug.h"
#include "samsconfig.h"

bool DelayPoolList::_loaded = false;
map<long, DelayPool*> DelayPoolList::_list;
DBConn *DelayPoolList::_conn;                ///< Соединение с БД
bool DelayPoolList::_connection_owner;

bool DelayPoolList::load ()
{
  if (_loaded)
    return true;

  return reload();
}

bool DelayPoolList::reload ()
{
  DEBUG (DEBUG_TPL, "[" << __FUNCTION__ << "] ");

  if (!_conn)
    {
      _conn = SamsConfig::newConnection ();
      if (!_conn)
        {
          ERROR ("Unable to create connection.");
          return false;
        }

      if (!_conn->connect ())
        {
          delete _conn;
          return false;
        }
      _connection_owner = true;
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Using new connection " << _conn);
    }
    else
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Using old connection " << _conn);
    }


  DBQuery *query = NULL;
  DBQuery *query2 = NULL;
  DBQuery *query3 = NULL;
  DBQuery *query4 = NULL;


  _conn->newQuery (query);
  if (!query)
    {
      ERROR("Unable to create query.");
      return false;
    }

  _conn->newQuery (query2);
  if (!query2)
    {
      ERROR("Unable to create query.");
      delete query;
      return false;
    }

  _conn->newQuery (query3);
  if (!query3)
    {
      ERROR("Unable to create query.");
      delete query;
      delete query2;
      return false;
    }

  _conn->newQuery (query4);
  if (!query4)
    {
      ERROR("Unable to create query.");
      delete query;
      delete query2;
      delete query3;
      return false;
    }

  long s_pool_id;
  long s_class;
  long s_agg1, s_agg2;
  long s_net1, s_net2;
  long s_ind1, s_ind2;
  long s_tpl_id;
  long s_trange_id;
  long s_redirect_id;
  long s_negative;

  if (!query->bindCol (1, DBQuery::T_LONG,  &s_pool_id, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }
  if (!query->bindCol (2, DBQuery::T_LONG,  &s_class, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }
  if (!query->bindCol (3, DBQuery::T_LONG,  &s_agg1, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }
  if (!query->bindCol (4, DBQuery::T_LONG,  &s_agg2, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }
  if (!query->bindCol (5, DBQuery::T_LONG,  &s_net1, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }
  if (!query->bindCol (6, DBQuery::T_LONG,  &s_net2, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }
  if (!query->bindCol (7, DBQuery::T_LONG,  &s_ind1, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }
  if (!query->bindCol (8, DBQuery::T_LONG,  &s_ind2, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }



  if (!query2->bindCol (1, DBQuery::T_LONG,  &s_tpl_id, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }
  if (!query2->bindCol (2, DBQuery::T_LONG,  &s_negative, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }



  if (!query3->bindCol (1, DBQuery::T_LONG,  &s_trange_id, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }
  if (!query3->bindCol (2, DBQuery::T_LONG,  &s_negative, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }


  if (!query4->bindCol (1, DBQuery::T_LONG,  &s_redirect_id, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }
  if (!query4->bindCol (2, DBQuery::T_LONG,  &s_negative, 0))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }


  if (!query->sendQueryDirect ("select s_pool_id, s_class, s_agg1, s_agg2, s_net1, s_net2, s_ind1, s_ind2 from delaypool order by s_pool_id"))
    {
      delete query;
      delete query2;
      delete query3;
      delete query4;
      return false;
    }

  basic_stringstream < char >sqlcmd;

  DelayPool *dc = NULL;
  _list.clear();
  while (query->fetch())
    {
      dc = new DelayPool (s_pool_id);
      dc->setClass (s_class);
      dc->setAggregateParams (s_agg1, s_agg2);
      dc->setNetworkParams (s_net1, s_net2);
      dc->setIndividualParams (s_ind1, s_ind2);

      _list[s_pool_id] = dc;

      DEBUG (DEBUG9, "[" << __FUNCTION__ << "] Found DelayPool: " <<
        "id=" << s_pool_id
        );

      // Установим связи с шаблонами
      sqlcmd.str("");
      sqlcmd << "select shablon.s_shablon_id, d_link_s.s_negative from delaypool, shablon, d_link_s";
      sqlcmd << " where delaypool.s_pool_id=" << s_pool_id;
      sqlcmd << " and delaypool.s_pool_id=d_link_s.s_pool_id";
      sqlcmd << " and shablon.s_shablon_id=d_link_s.s_shablon_id";
      if (query2->sendQueryDirect (sqlcmd.str ()))
        {
          while (query2->fetch ())
            {
              dc->addTemplate (s_tpl_id, ((s_negative==1)?true:false));
              DEBUG (DEBUG9, "[" << __FUNCTION__ << "] DelayPool " <<
                s_pool_id << " has template " << s_tpl_id
                );
            }
        }

      // Установим связи с временными границами
      sqlcmd.str("");
      sqlcmd << "select timerange.s_trange_id, d_link_t.s_negative from delaypool, timerange, d_link_t";
      sqlcmd << " where delaypool.s_pool_id=" << s_pool_id;
      sqlcmd << " and delaypool.s_pool_id=d_link_t.s_pool_id";
      sqlcmd << " and timerange.s_trange_id=d_link_t.s_trange_id";
      if (query3->sendQueryDirect (sqlcmd.str ()))
        {
          while (query3->fetch ())
            {
              dc->addTimeRange (s_trange_id, ((s_negative==1)?true:false));
              DEBUG (DEBUG9, "[" << __FUNCTION__ << "] DelayPool " <<
                s_pool_id << " has time range " << s_trange_id
                );
            }
        }


      // Установим связи с ресурсами
      sqlcmd.str("");
      sqlcmd << "select redirect.s_redirect_id, d_link_r.s_negative from delaypool, redirect, d_link_r";
      sqlcmd << " where delaypool.s_pool_id=" << s_pool_id;
      sqlcmd << " and delaypool.s_pool_id=d_link_r.s_pool_id";
      sqlcmd << " and redirect.s_redirect_id=d_link_r.s_redirect_id";
      if (query4->sendQueryDirect (sqlcmd.str ()))
        {
          while (query4->fetch ())
            {
              dc->addUrlGroup (s_redirect_id, ((s_negative==1)?true:false));
              DEBUG (DEBUG9, "[" << __FUNCTION__ << "] DelayPool " <<
                s_pool_id << " has url group " << s_redirect_id
                );
            }
        }

    }
  delete query;
  delete query2;
  delete query3;
  delete query4;
  _loaded = true;

  return true;
}

void DelayPoolList::useConnection (DBConn * conn)
{
  if (_conn)
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Already using " << _conn);
      return;
    }
  if (conn)
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Using external connection " << conn);
      _conn = conn;
      _connection_owner = false;
    }
}

void DelayPoolList::destroy ()
{
  if (_connection_owner && _conn)
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Destroy connection " << _conn);
      delete _conn;
      _conn = NULL;
    }
  else if (_conn)
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Not owner for connection " << _conn);
    }
  else
    {
      DEBUG (DEBUG6, "[" << __FUNCTION__ << "] Not connected");
    }
  map < long, DelayPool* >::iterator it;
  for (it = _list.begin (); it != _list.end (); it++)
    {
      delete (*it).second;
    }
  _list.clear ();
}

DelayPool * DelayPoolList::getDelayPool (long id)
{
  load();

  map < long, DelayPool* >::iterator it = _list.find (id);
  if (it == _list.end ())
    {
      DEBUG (DEBUG_TPL, "[" << __FUNCTION__ << "] " << id << " not found");
      return NULL;
    }
  return (*it).second;
}

vector<long> DelayPoolList::getIds ()
{
  load();

  vector<long> lst;
  map <long, DelayPool*>::iterator it;
  for (it = _list.begin (); it != _list.end (); it++)
    {
      lst.push_back((*it).first);
    }

  return lst;
}

vector<DelayPool*> DelayPoolList::getList ()
{
  load();

  vector<DelayPool*> lst;
  map <long, DelayPool*>::iterator it;
  for (it = _list.begin (); it != _list.end (); it++)
    {
      lst.push_back((*it).second);
    }
  return lst;
}

long DelayPoolList::count ()
{
  load();

  return (long)_list.size ();
}
