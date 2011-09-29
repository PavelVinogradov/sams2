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
#include <stdlib.h>
#include <string.h>

#include "pgquery.h"

#ifdef USE_PQ

#include "debug.h"
#include "pgconn.h"

PgQuery::PgQuery (PgConn *conn):DBQuery ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  _conn = conn;
  _res = NULL;
  _prepeared = false;
  _param_real_len = NULL;
  _param_values = NULL;
  _param_formats = NULL;

  basic_stringstream < char >s;
  s << this;
  _query_name = s.str ();

  if (_conn)
    {
      _conn->registerQuery (this);
    }
}


PgQuery::~PgQuery ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  destroy ();

  if (_conn)
    _conn->unregisterQuery (this);
}

bool PgQuery::sendQueryDirect (const string & query)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << query);

  if (!_conn)
  {
    ERROR ("[" << this << "->" << __FUNCTION__ << "] " << "NULL connection.");
    return false;
  }

  if (_res)
    {
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQclear(" << _res << ")");
      PQclear (_res);
    }

  _res = PQexec (_conn->_pgconn, query.c_str ());
  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQexec(" << _conn->_pgconn << ", " << query << ") = " << _res);

  int status = PQresultStatus (_res);
  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQresultStatus(" << _res << ") = " << PQresStatus((ExecStatusType)status));

  if ((status != PGRES_COMMAND_OK) && (status != PGRES_TUPLES_OK))
    {
      ERROR ("[" << this << "->" << __FUNCTION__ << "] " << PQerrorMessage (_conn->_pgconn));
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQclear(" << _res << ")");
      PQclear (_res);
      _res = NULL;
      return false;
    }

  _current_row = 0;

  return true;
}

bool PgQuery::bindCol (uint colNum, VarType dstType, void *buf, int bufLen)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "num:" << colNum << ", type:" << dstType << ", len:" << bufLen );

  if (colNum == 1 && _columns.size ()>0)
    {
      _columns.clear();
    }

  if (_columns.size ()+1 != colNum)
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Unexpected column number.");
      return false;
    }

  struct Column col;
  col.t = dstType;
  col.dst = buf;
  col.len = bufLen-1;
  _columns.push_back (col);

  return true;
}

bool PgQuery::prepareQuery (const string & query)
{
  _prepeared_query = convert (query);
  _prepeared = false;
  return true;
}

bool PgQuery::bindParam (uint num, DBQuery::VarType dstType, void *buf, int bufLen)
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] " << "num:" << num << ", type:" << dstType << ", len:" << bufLen );

  if (num == 1 && _params.size ()>0)
    {
      _params.clear();
    }

  if (_params.size ()+1 != num)
    {
      ERROR("[" << this << "->" << __FUNCTION__ << "] " << "Unexpected parameter number.");
      return false;
    }

  struct Param par;
  par.t = dstType;
  par.dst = buf;
  par.len = bufLen-1;
  _params.push_back (par);

  return true;
}

bool PgQuery::sendQuery ()
{
  int status;
  uint i;

  if (_res)
    {
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQclear(" << _res << ")");
      PQclear (_res);
      _res = NULL;
    }

  if (!_prepeared && (_params.size () > 0))
    {
      _res = PQprepare(_conn->_pgconn, _query_name.c_str (), _prepeared_query.c_str (), _params.size (), NULL);
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQprepare(" << _conn->_pgconn << ", " << _query_name << ", " << _prepeared_query << ", " << _params.size () << ") = " << _res);

      status = PQresultStatus (_res);
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQresultStatus(" << _res << ") = " << PQresStatus((ExecStatusType)status));
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQclear(" << _res << ")");
      PQclear (_res);
      _res = NULL;

      if ((status != PGRES_COMMAND_OK) && (status != PGRES_TUPLES_OK))
        {
          ERROR ("[" << this << "->" << __FUNCTION__ << "] " << PQerrorMessage (_conn->_pgconn));
          return false;
        }

      _param_real_len = (int*)malloc(sizeof(int)*_params.size ());
      _param_values = (char**)malloc(sizeof(char*)*_params.size ());
      _param_formats = (int*)malloc(sizeof(int)*_params.size ());
      for (i=0; i<_params.size(); i++)
        {
          switch (_params[i].t)
            {
              case DBQuery::T_CHAR:
                break;
              case DBQuery::T_LONG:
                _param_values[i] = (char*)malloc(32);
                break;
              case DBQuery::T_LONGLONG:
                _param_values[i] = (char*)malloc(64);
                break;
            }
          _param_real_len[i] = 0;
          _param_formats[i] = 0;
        }

      _prepeared = true;
    }

  // обновляем привязанный массив в соответствии с текущими значениями буферов маркеров
  if (!_params.empty())
    {
      for (i=0; i<_params.size(); i++)
        {
          switch (_params[i].t)
            {
              case DBQuery::T_CHAR:
                _param_values[i] = (char*)_params[i].dst;
                break;
              case DBQuery::T_LONG:
                sprintf(_param_values[i], "%ld", *(long*)_params[i].dst);
                break;
              case DBQuery::T_LONGLONG:
                sprintf(_param_values[i], "%Ld", *(long long*)_params[i].dst);
                break;
            }
        }
    }

  _res = PQexecPrepared(_conn->_pgconn, _query_name.c_str (), _params.size (), _param_values, _param_real_len, _param_formats, 0);
  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQexecPrepared(" << _conn->_pgconn << ", " << _query_name << ", " << _params.size () << ") = " << _res);

  status = PQresultStatus (_res);
  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQresultStatus(" << _res << ") = " << PQresStatus((ExecStatusType)status));

/*
  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQclear(" << _res << ")");
  PQclear (_res);
  _res = NULL;
*/

  if ((status != PGRES_COMMAND_OK) && (status != PGRES_TUPLES_OK))
    {
      ERROR ("[" << this << "->" << __FUNCTION__ << "] " << PQerrorMessage (_conn->_pgconn));
      return false;
    }

  return true;
}

bool PgQuery::fetch ()
{
  if (_current_row == 0)
    {
      int status = PQresultStatus (_res);
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQresultStatus(" << _res << ") = " << PQresStatus((ExecStatusType)status));

      if (status != PGRES_TUPLES_OK)
        {
          DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] No results");
          return false;
        }
    }
  int num_rows = PQntuples (_res);
  DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQntuples(" << _res << ") = " << num_rows);

  if (_current_row >= num_rows)
    {
      DEBUG (DEBUG6, "[" << this << "->" << __FUNCTION__ << "] = No rows");
      DEBUG (DEBUG9, "[" << this << "->" << __FUNCTION__ << "] PQclear(" << _res << ")");
      _current_row = 0;
      PQclear (_res);
      _res = NULL;
      return false;
    }

  bool ok = true;
  int res_len;
  int use_len;
  char *val;
  for (uint i=0; i<_columns.size (); i++)
    {
      val = PQgetvalue(_res, _current_row, i);

      switch (_columns[i].t)
        {
          case DBQuery::T_CHAR:
            res_len = PQgetlength (_res, _current_row, i);
            use_len = (res_len<_columns[i].len)?(res_len):(_columns[i].len);
            strncpy((char*)_columns[i].dst, val, use_len);
            ((char*)_columns[i].dst)[use_len] = 0;
            break;
          case DBQuery::T_LONG:
            if (sscanf(val, "%ld", (long*)_columns[i].dst) != 1)
              ok = false;
            break;
          case DBQuery::T_LONGLONG:
            if (sscanf(val, "%lld", (long long*)_columns[i].dst) != 1)
              ok = false;
            break;
          default:
            ok = false;
            break;
        }
      if (!ok)
        break;
    }

  _current_row++;

  return ok;
}

long PgQuery::affectedRows ()
{
  long res = 0;
  char *str_rows = PQcmdTuples (_res);
  if (!str_rows || !*str_rows)
    return res;
  if (sscanf (str_rows, "%ld", &res) != 1)
    return 0;
  return res;
}

string PgQuery::convert (const string & cmd)
{
  basic_stringstream < char >s;
  int par_idx=1;
  for (uint i = 0; i < cmd.size (); i++)
    {
      if (cmd[i] == '?')
          s << '$' << par_idx++;
      else
          s << cmd[i];
    }
  return s.str ();
}

void PgQuery::destroy ()
{
  DEBUG (DEBUG_DB, "[" << this << "->" << __FUNCTION__ << "] ");

  if (_res)
    PQclear (_res);

  if (_param_values)
    {
      for (uint i=0; i<_params.size(); i++)
        {
          switch (_params[i].t)
            {
              case DBQuery::T_CHAR:
                break;
              case DBQuery::T_LONG:
              case DBQuery::T_LONGLONG:
                free(_param_values[i]);
                break;
            }
          _param_values[i] = 0;
        }
      free (_param_values);
    }
    if (_param_real_len)
      free (_param_real_len);
    if (_param_formats)
      free (_param_formats);

  _res = NULL;
  _param_real_len = NULL;
  _param_values = NULL;
  _param_formats = NULL;
}

#endif // #ifdef USE_PQ
