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
#include "delaypool.h"

#include "debug.h"


DelayPool::DelayPool (const long & id)
{
  _id = id;
}

DelayPool::~DelayPool ()
{
  _times.clear ();
  _urlgroups.clear ();
  _templates.clear ();
}

long DelayPool::getId () const
{
  return _id;
}

void DelayPool::setClass (const long &c)
{
  _class = c;
}

long DelayPool::getClass () const
{
  return _class;
}

void DelayPool::setAggregateParams (const long & p1, const long & p2)
{
  _agg1 = p1;
  _agg2 = p2;
}

void DelayPool::getAggregateParams (long & p1, long & p2) const
{
  p1 = _agg1;
  p2 = _agg2;
}

void DelayPool::setNetworkParams (const long & p1, const long & p2)
{
  _net1 = p1;
  _net2 = p2;
}

void DelayPool::getNetworkParams (long & p1, long & p2) const
{
  p1 = _net1;
  p2 = _net2;
}

void DelayPool::setIndividualParams (const long & p1, const long & p2)
{
  _ind1 = p1;
  _ind2 = p2;
}

void DelayPool::getIndividualParams (long & p1, long & p2) const
{
  p1 = _ind1;
  p2 = _ind2;
}

void DelayPool::addTimeRange (const long & id, bool negative)
{
  _times[id] = negative;
}

map <long, bool> DelayPool::getTimeRanges () const
{
  return _times;
}

void DelayPool::addUrlGroup (const long & id, bool negative)
{
  _urlgroups[id] = negative;
}

map <long, bool> DelayPool::getUrlGroups () const
{
  return _urlgroups;
}

void DelayPool::addTemplate (const long & id, bool negative)
{
  _templates[id] = negative;
}

map <long, bool> DelayPool::getTemplates () const
{
  return _templates;
}
