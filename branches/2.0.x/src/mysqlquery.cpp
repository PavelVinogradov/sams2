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
#include "mysqlquery.h"
#include "mysqlconn.h"
#include "debug.h"

MYSQLQuery::MYSQLQuery(MYSQLConn *conn):DBQuery ()
{
  _conn = conn;
  _bind = NULL;
  _statement = NULL;
/*
  if (conn)
    {
      conn->registerQuery (this);
      _conn = conn;
    }

  _statement = NULL;
  _bind = NULL;
*/
}

MYSQLQuery::~MYSQLQuery()
{
  destroy ();

//  if (_conn)
//    ((MYSQLConn *) _conn)->unregisterQuery (this);
}

bool MYSQLQuery::sendQueryDirect (const string & query)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << query);

  if (mysql_query(_conn->_mysql, query.c_str()))
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << mysql_error(_conn->_mysql));
      return false;
    }

  _res = mysql_store_result (_conn->_mysql);

  return true;
}

bool MYSQLQuery::bindCol (uint colNum, enum_field_types dstType, void *buf, int bufLen)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "num:" << colNum << ", type:" << dstType << ", len:" << bufLen );

  if (colNum == 1 && _columns.size()>0)
    _columns.clear();

  if (_columns.size()+1 != colNum)
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Unexpected column number.");
      return false;
    }

  struct Column col;
  col.t = dstType;
  col.dst = buf;
  col.len = bufLen;
  _columns.push_back(col);

  return true;
}

bool MYSQLQuery::bindParam (uint num, VarType dstType, void *buf, int bufLen)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << num);
  ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Not implemented.");
  return false;
}

bool MYSQLQuery::prepareQuery (const string & query)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << query);
  ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Not implemented.");
  return false;
}

bool MYSQLQuery::sendQuery ()
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");
  ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Not implemented.");
  return false;
}

bool MYSQLQuery::fetch ()
{
  MYSQL_ROW row = NULL;

//  ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Not implemented.");

  if (!_res)
    {
      DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "failed");
      return false;
    }

  row = mysql_fetch_row (_res);
  if (!row)
    {
      DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "failed");
      return false;
    }

  bool ok = true;
  for (uint i=0; i<_columns.size(); i++)
    {
      switch (_columns[i].t)
        {
          case MYSQL_TYPE_STRING:
            sprintf((char*)_columns[i].dst, "%s", row[i]);
            break;
          default:
            ok = false;
            break;
        }
    }

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << ((ok) ? ("ok") : ("failed")));

  return ok;
}

void MYSQLQuery::destroy ()
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");

  if (_statement)
    mysql_stmt_close (_statement);
  if (_bind)
    free (_bind);

  _statement = NULL;
  _bind = NULL;
}
