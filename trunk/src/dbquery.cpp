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
#include "dbquery.h"
#include "dbconn.h"

#include "debug.h"

string DBQuery::toString (VarType t)
{
  string res;
  switch (t)
    {
    case T_LONG:
      res = "long";
      break;
    case T_CHAR:
      res = "char";
      break;
/*
    case T_DATE:
      res = "date";
      break;
    case T_TIME:
      res = "time";
      break;
    case T_DATETIME:
      res = "datetime";
      break;
    case T_TIMESTAMP:
      res = "timestamp";
      break;
*/
    default:
      res = "unknown";
      break;
    }
  return res;
}

DBQuery::DBQuery ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
}


DBQuery::~DBQuery ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");
}


bool DBQuery::sendQueryDirect (const string & query)
{
  WARNING ("[DBQuery::" << __FUNCTION__ << "] " << " must be overriden.");
  return false;
}

bool DBQuery::bindCol (uint colNum, VarType dstType, void *buf, int bufLen)
{
  WARNING ("[DBQuery::" << __FUNCTION__ << "] " << " must be overriden.");
  return false;
}

bool DBQuery::bindParam (uint num, VarType dstType, void *buf, int bufLen)
{
  WARNING ("[DBQuery::" << __FUNCTION__ << "] " << " must be overriden.");
  return false;
}

bool DBQuery::prepareQuery (const string & query)
{
  WARNING ("[DBQuery::" << __FUNCTION__ << "] " << " must be overriden.");
  return false;
}

bool DBQuery::sendQuery ()
{
  WARNING ("[DBQuery::" << __FUNCTION__ << "] " << " must be overriden.");
  return false;
}

bool DBQuery::fetch ()
{
  WARNING ("[DBQuery::" << __FUNCTION__ << "] " << " must be overriden.");
  return false;
}

long DBQuery::affectedRows ()
{
  WARNING ("[DBQuery::" << __FUNCTION__ << "] " << " must be overriden.");
  return 0;
}

void DBQuery::destroy ()
{
  WARNING ("[DBQuery::" << __FUNCTION__ << "] " << " must be overriden.");
}
