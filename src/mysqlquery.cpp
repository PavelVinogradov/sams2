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
#include <string.h>
#include <stdlib.h>
#include <limits.h>

#include "mysqlquery.h"

#ifdef USE_MYSQL

#include "mysqlconn.h"
#include "debug.h"

MYSQLQuery::MYSQLQuery(MYSQLConn *conn):DBQuery ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  _conn = conn;
  _bind_param = NULL;
  _bind_column = NULL;
  _param_real_len = NULL;
  _columns_real_len = NULL;
  _statement = NULL;
  _param_binded = false;
  _col_binded = false;
  _res = NULL;
  _prepeared_statement = false;

  if (conn)
    {
      conn->registerQuery (this);
      _conn = conn;
    }
}

MYSQLQuery::~MYSQLQuery()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  destroy ();

  if (_conn)
    _conn->unregisterQuery (this);
}

bool MYSQLQuery::sendQueryDirect (const string & query)
{
  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] " << query);

  if (!_conn)
  {
    ERROR("[" << this << "->" << __FUNCTION__ << "] " << "NULL connection.");
    return false;
  }

  if (_res)
    {
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] mysql_free_result(" << _res << ")");
      mysql_free_result (_res);
    }

  if (mysql_query(_conn->_mysql, query.c_str()))
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << mysql_error(_conn->_mysql));
      return false;
    }

  _res = mysql_store_result (_conn->_mysql);

  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] mysql_store_result(" << _conn->_mysql << ")=" << _res);

  return true;
}

bool MYSQLQuery::bindCol (uint colNum, DBQuery::VarType dstType, void *buf, int bufLen)
{
  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] " << "num:" << colNum << ", type:" << dstType << ", len:" << bufLen );

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

  if (colNum == 1 && _columns.size()>0)
    {
      _columns.clear();
      if (_bind_column)
        free (_bind_column);
      if (_columns_real_len)
        free (_columns_real_len);
      _bind_column = NULL;
      _col_binded = false;
      _columns_real_len = NULL;
    }

  if (_columns.size()+1 != colNum)
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Unexpected column number.");
      return false;
    }

  struct Column col;
  col.t = dType;
  col.dst = buf;
  col.len = bufLen-1;
  _columns.push_back(col);

  return true;
}

bool MYSQLQuery::bindParam (uint num, DBQuery::VarType dstType, void *buf, int bufLen)
{
  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] " << "num:" << num << ", type:" << dstType << ", len:" << bufLen );

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

  if (num == 1 && _params.size()>0)
    {
      _params.clear();
      if (_bind_param)
        free (_bind_param);
      if (_param_real_len)
        free (_param_real_len);
      _bind_param = NULL;
      _param_binded = false;
      _param_real_len = NULL;
    }

  if (_params.size()+1 != num)
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Unexpected marker number.");
      return false;
    }

  struct Param par;
  par.t = dType;
  par.dst = buf;
  par.len = bufLen;
  _params.push_back(par);

  return true;
}

bool MYSQLQuery::prepareQuery (const string & query)
{
  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] " << query);

  if (!createStatement ())
    {
      return false;
    }

  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] mysql_stmt_prepare(" << _statement << ", " << query << ")");

  if (mysql_stmt_prepare(_statement, query.c_str(), query.size()))
  {
    ERROR("[" << this << "->" << __FUNCTION__ << "] " << mysql_stmt_error(_statement));
    return false;
  }

  _prepeared_statement = true;

  return true;
}

bool MYSQLQuery::sendQuery ()
{
  if (!_statement)
  {
    ERROR("[" << this << "->" << __FUNCTION__ << "] " << "NULL statement.");
    return false;
  }

  uint i;
  bool ok = true;

  // Маркеры определены, массив для привязки не определен
  if (!_bind_param && !_params.empty())
    {
      if (_bind_param)
        free (_bind_param);
      if (_param_real_len)
        free (_param_real_len);
      _bind_param = (MYSQL_BIND*) malloc (sizeof (MYSQL_BIND) * _params.size());
      _param_real_len = (unsigned long*) malloc (sizeof (unsigned long) * _params.size());
      memset (_bind_param, 0, sizeof (MYSQL_BIND) * _params.size());
      memset (_param_real_len, 0, sizeof (unsigned long) * _params.size());
      for (i=0; i<_params.size(); i++)
        {
          DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Fill marker " << i);
          switch (_params[i].t)
            {
              case MYSQL_TYPE_STRING:
                _bind_param[i].buffer_type = _params[i].t;
                _bind_param[i].buffer = (char *)_params[i].dst;
                _bind_param[i].buffer_length = _params[i].len;
                _bind_param[i].is_null = 0;
                _bind_param[i].length = &_param_real_len[i];
                break;
              case MYSQL_TYPE_LONG:
              case MYSQL_TYPE_LONGLONG:
                _bind_param[i].buffer_type = _params[i].t;
                _bind_param[i].buffer = (char *)_params[i].dst;
                _bind_param[i].is_null = 0;
                _bind_param[i].length = 0;
                break;
              default:
                ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Unsupported marker type (" << _params[i].t << ")");
                ok = false;
                break;
            }
          if (!ok)
            break;
        }
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Fill bind structure: ok");
    }

  if (!ok)
    return false;

  // Столбцы определены, массив для привязки не определен
  if (!_bind_column && !_columns.empty())
    {
      _bind_column = (MYSQL_BIND*) malloc (sizeof (MYSQL_BIND) * _columns.size());
      _columns_real_len = (unsigned long*) malloc (sizeof (unsigned long) * _columns.size());
      memset (_bind_column, 0, sizeof (MYSQL_BIND) * _columns.size());
      memset (_columns_real_len, 0, sizeof (unsigned long) * _columns.size());
      for (i=0; i<_columns.size(); i++)
        {
          DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Fill column " << i);
          switch (_columns[i].t)
            {
              case MYSQL_TYPE_STRING:
                _bind_column[i].buffer_type = _columns[i].t;
                _bind_column[i].buffer = (char *)_columns[i].dst;
                _bind_column[i].buffer_length = _columns[i].len;
                _bind_column[i].is_null = 0;
                _bind_column[i].length = &_columns_real_len[i];
                break;
              case MYSQL_TYPE_LONG:
              case MYSQL_TYPE_LONGLONG:
                _bind_column[i].buffer_type = _columns[i].t;
                _bind_column[i].buffer = (char *)_columns[i].dst;
                _bind_column[i].is_null = 0;
                _bind_column[i].length = 0;
                break;
              default:
                ERROR ("[" << this << "->" << __FUNCTION__ << "] " << "Unsupported column type (" << _columns[i].t << ")");
                ok = false;
                break;
            }
          if (!ok)
            break;
        }
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Fill bind structure: ok");
    }

  if (!ok)
    return false;

  // массив параметров определен, но не привязан
  if (_bind_param && !_param_binded)
    {
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] mysql_stmt_bind_param(" << _statement << ", " << _bind_param << ")");
      if (mysql_stmt_bind_param(_statement, _bind_param))
        {
          ERROR("[" << this << "->" << __FUNCTION__ << "] " << mysql_stmt_error(_statement));
          return false;
        }
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Bind markers: ok");
      _param_binded = true;
    }

  // массив столбцов определен, но не привязан
  if (_bind_column && !_col_binded)
    {
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] mysql_stmt_bind_result(" << _statement << ", " << _bind_column << ")");
      if (mysql_stmt_bind_result(_statement, _bind_column))
        {
          ERROR("[" << this << "->" << __FUNCTION__ << "] " << mysql_stmt_error(_statement));
          return false;
        }
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] " << "Bind columns: ok");
      _col_binded = true;
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
                break;
              default:
                break;
            }
        }
    }

  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] mysql_stmt_execute(" << _statement << ")");
  if (mysql_stmt_execute(_statement))
  {
    ERROR ("[" << this << "->" << __FUNCTION__ << "] " << mysql_stmt_error(_statement));
    _res = NULL; // Might be wrong
    return false;
  }

  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] ok");
  return true;
}

bool MYSQLQuery::fetch ()
{
  MYSQL_ROW row = NULL;
  bool ok = true;

  if (_prepeared_statement)
    {
      int ret;
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] mysql_stmt_fetch(" << _statement << ")");
      ret = mysql_stmt_fetch (_statement);
      if (ret == 0)
        ok = true;
      else if (ret == 1)
        {
          ERROR("[" << this << "->" << __FUNCTION__ << "] " << mysql_stmt_error(_statement));
          return false;
        }
      else if (ret == MYSQL_NO_DATA)
        {
          DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] = No rows");
          return false;
        }
    }
  else
    {
      if (!_res)
        {
          DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] = No results");
          return false;
        }

      row = mysql_fetch_row (_res);
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] mysql_fetch_row(" << _res << ")=" << row);
      if (!row)
        {
          DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] = No rows");
          return false;
        }
      int res_len;
      int use_len;
      for (uint i=0; i<_columns.size(); i++)
        {
          switch (_columns[i].t)
            {
              case MYSQL_TYPE_STRING:
                if (row[i])
                  res_len = strlen(row[i])+1;
                else
                  res_len = 0;
                use_len = (res_len<_columns[i].len)?(res_len):(_columns[i].len);
                if (use_len > 0)
                  strncpy((char*)_columns[i].dst, row[i], use_len);
                ((char*)_columns[i].dst)[use_len] = 0;
                break;
              case MYSQL_TYPE_LONG:
                if (row[i])
                  {
                    if (sscanf(row[i], "%ld", (long*)_columns[i].dst) != 1)
                      ok = false;
                  }
                else
                  *((long*)_columns[i].dst) = LONG_MAX;
                break;
              case MYSQL_TYPE_LONGLONG:
                if (row[i])
                  {
                    if (sscanf(row[i], "%lld", (long long*)_columns[i].dst) != 1)
                      ok = false;
                  }
                else
                  *(long long*)_columns[i].dst = LLONG_MAX;
                break;
              default:
                ok = false;
                break;
            }
          if (!ok)
            break;
        }
    }

  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] " << ((ok) ? ("ok") : ("failed")));

  return ok;
}

long MYSQLQuery::affectedRows ()
{
  if (!_statement)
  {
    ERROR("[" << this << "->" << __FUNCTION__ << "] " << "NULL statement.");
    return 0;
  }

  long res = (long) mysql_stmt_affected_rows (_statement);
  if (res < 0)
    {
      WARNING("Unable to get affected rows.");
      res = 0;
    }
  return res;
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
  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] mysql_stmt_init(" << _conn->_mysql << ")=" << _statement);
  if (!_statement)
    {
      ERROR ("[" << this << "->" << __FUNCTION__ << "] " << "Cannot initialize statement.");
      return false;
    }

  DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] ok");

  return true;
}

void MYSQLQuery::destroy ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "] ");

  if (_res)
    {
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] mysql_free_result(" << _res << ")");
      mysql_free_result (_res);
    }
  if (_statement)
    {
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] mysql_stmt_close(" << _statement << ")");
      mysql_stmt_close (_statement);
    }
  if (_bind_param)
    free (_bind_param);
  if (_bind_column)
    free (_bind_column);
  if (_param_real_len)
    free (_param_real_len);
  if (_columns_real_len)
    free (_columns_real_len);

  _columns.clear ();
  _params.clear ();

  _res = NULL;
  _statement = NULL;
  _bind_param = NULL;
  _param_real_len = NULL;
  _bind_column = NULL;
  _columns_real_len = NULL;
}

#endif // #ifdef USE_MYSQL
