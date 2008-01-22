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

#ifdef USE_MYSQL

#include "mysqlconn.h"
#include "debug.h"

MYSQLQuery::MYSQLQuery(MYSQLConn *conn):DBQuery ()
{
  _conn = conn;
  _bind = NULL;
  _param_real_len = NULL;
  _statement = NULL;
  _binded = false;
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

  if (!_conn)
  {
    ERROR("[" << this << "->" << __FUNCTION__ << "] " << "NULL connection.");
    return false;
  }

  if (mysql_query(_conn->_mysql, query.c_str()))
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << mysql_error(_conn->_mysql));
      return false;
    }

  _res = mysql_store_result (_conn->_mysql);

  return true;
}

bool MYSQLQuery::bindCol (uint colNum, DBQuery::VarType dstType, void *buf, int bufLen)
{
  enum_field_types dType;
  switch (dstType)
    {
      case DBQuery::T_LONG:
        dType = MYSQL_TYPE_LONG;
        break;
      case DBQuery::T_LONGLONG:
        dType = MYSQL_TYPE_LONGLONG;
        break;
      case DBQuery::T_CHAR:
        dType = MYSQL_TYPE_STRING;
        break;
      default:
        return false;
        break;
    }

  return bindCol (colNum, dType, buf, bufLen);
}

bool MYSQLQuery::bindCol (uint colNum, enum_field_types dstType, void *buf, int bufLen)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "num:" << colNum << ", type:" << dstType << ", len:" << bufLen );

  if (colNum == 1 && _columns.size()>0)
    {
      _columns.clear();
    }

  if (_columns.size()+1 != colNum)
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Unexpected column number.");
      return false;
    }

  struct Column col;
  col.t = dstType;
  col.dst = buf;
  col.len = bufLen-1;
  _columns.push_back(col);

  return true;
}

bool MYSQLQuery::bindParam (uint num, DBQuery::VarType dstType, void *buf, int bufLen)
{
  enum_field_types dType;
  switch (dstType)
    {
      case DBQuery::T_LONG:
        dType = MYSQL_TYPE_LONG;
        break;
      case DBQuery::T_LONGLONG:
        dType = MYSQL_TYPE_LONGLONG;
        break;
      case DBQuery::T_CHAR:
        dType = MYSQL_TYPE_STRING;
        break;
      default:
        return false;
        break;
    }

  return bindParam (num, dType, buf, bufLen);
}

bool MYSQLQuery::bindParam (uint num, enum_field_types dstType, void *buf, int bufLen)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "num:" << num << ", type:" << dstType << ", len:" << bufLen );

  if (num == 1 && _params.size()>0)
    {
      _params.clear();
      if (_bind)
        free (_bind);
      if (_param_real_len)
        free (_param_real_len);
      _bind = NULL;
      _param_real_len = NULL;
      _binded = false;
    }

  if (_params.size()+1 != num)
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Unexpected marker number.");
      return false;
    }

  struct Param par;
  par.t = dstType;
  par.dst = buf;
  par.len = bufLen;
  _params.push_back(par);

  return true;
}

bool MYSQLQuery::prepareQuery (const string & query)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << query);

  if (!createStatement ())
    {
      return false;
    }

  if (mysql_stmt_prepare(_statement, query.c_str(), query.size()))
  {
    ERROR("[" << this << "->" << __FUNCTION__ << "] " << mysql_stmt_error(_statement));
    return false;
  }

  return true;
}

/**
 * @todo При работе со строковыми параметрами необходимо заключать значение в одинарные кавычки
 */
bool MYSQLQuery::sendQuery ()
{
  if (!_statement)
  {
    ERROR("[" << this << "->" << __FUNCTION__ << "] " << "NULL statement.");
    return false;
  }

  uint i;
  //Маркеры определены, массив для привязки не определен
  bool ok = true;
  if (!_bind && !_params.empty())
    {
      _bind = (MYSQL_BIND*) malloc (sizeof (MYSQL_BIND) * _params.size());
      _param_real_len = (ulong*) malloc (sizeof (ulong) * _params.size());
      memset (_bind, 0, sizeof (MYSQL_BIND) * _params.size());
      memset (_param_real_len, 0, sizeof (ulong) * _params.size());
      for (i=0; i<_params.size(); i++)
        {
          DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] " << "Fill marker " << i);
          switch (_params[i].t)
            {
              case MYSQL_TYPE_STRING:
                _bind[i].buffer_type = _params[i].t;
                _bind[i].buffer = (char *)_params[i].dst;
                _bind[i].buffer_length = _params[i].len;
                _bind[i].is_null = 0;
                _bind[i].length = &_param_real_len[i];
                break;
              case MYSQL_TYPE_LONG:
              case MYSQL_TYPE_LONGLONG:
                _bind[i].buffer_type = _params[i].t;
                _bind[i].buffer = (char *)_params[i].dst;
                _bind[i].is_null = 0;
                _bind[i].length = 0;
                break;
              default:
                ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Unsupported marker type (" << _params[i].t << ")");
                ok = false;
                break;
            }
          if (!ok)
            break;
        }
      DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] " << "Fill bind structure: ok");
    }


  if (!ok)
    return false;

  // массив для привяки определен, но не привязан
  if (_bind && !_binded)
    {
      if (mysql_stmt_bind_param(_statement, _bind))
        {
          ERROR("[" << this << "->" << __FUNCTION__ << "] " << mysql_stmt_error(_statement));
          return false;
        }
      DEBUG (DEBUG8, "[" << this << "->" << __FUNCTION__ << "] " << "Bind markers: ok");
      _binded = true;
    }

  // обновляем привязанный массив в соответствии с текущими значениями буферов маркеров
  if (!_params.empty())
    {
      for (i=0; i<_params.size(); i++)
        {
          switch (_params[i].t)
            {
              case MYSQL_TYPE_STRING:
                _param_real_len[i] = strlen((char *)_params[i].dst);
//                if (_param_real_len[i] == 0)
//                  sprintf((char *)_params[i].dst, "''");
//                else
//                  sprintf((char *)_params[i].dst, "'%s'", (char *)_params[i].dst);
                break;
              default:
                break;
            }
        }
    }

  if (mysql_stmt_execute(_statement))
  {
    ERROR("[" << this << "->" << __FUNCTION__ << "] " << mysql_stmt_error(_statement));
    return false;
  }

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ok");
  return true;
}

bool MYSQLQuery::fetch ()
{
  MYSQL_ROW row = NULL;

  if (!_res)
    {
      DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "failed: No results");
      return false;
    }

  row = mysql_fetch_row (_res);
  if (!row)
    {
      DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "failed: No rows");
      return false;
    }

  bool ok = true;
  int res_len;
  int use_len;
  for (uint i=0; i<_columns.size(); i++)
    {
      switch (_columns[i].t)
        {
          case MYSQL_TYPE_STRING:
            res_len = strlen(row[i])+1;
            use_len = (res_len<_columns[i].len)?(res_len):(_columns[i].len);
            strncpy((char*)_columns[i].dst, row[i], use_len);
            ((char*)_columns[i].dst)[use_len] = 0;
            //sprintf((char*)_columns[i].dst, "%s", row[i]);
            break;
          case MYSQL_TYPE_LONG:
            if (sscanf(row[i], "%ld", (long*)_columns[i].dst) != 1)
              ok = false;
            break;
          case MYSQL_TYPE_LONGLONG:
            if (sscanf(row[i], "%Ld", (long long*)_columns[i].dst) != 1)
              ok = false;
            break;
          default:
            ok = false;
            break;
        }
      if (!ok)
        break;
    }

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << ((ok) ? ("ok") : ("failed")));

  return ok;
}

bool MYSQLQuery::createStatement ()
{
  if (!_conn)
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << "NULL connection.");
      return false;
    }
  if (!(_conn->_connected))
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Not connected to the DB.");
      return false;
    }

  destroy ();

  _statement = mysql_stmt_init(_conn->_mysql);
  if (!_statement)
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Cannot initialize statement.");
      return false;
    }

  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ok");

  return true;
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

#endif // #ifdef USE_MYSQL
