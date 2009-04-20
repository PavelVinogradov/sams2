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
#ifndef PGQUERY_H
#define PGQUERY_H

#include "config.h"

#ifdef USE_PQ

using namespace std;

#include <vector>
#include <string>

#include <libpq-fe.h>

#include "dbquery.h"

class PgConn;

class PgQuery : public DBQuery
{
public:
  PgQuery(PgConn *conn);

  ~PgQuery();

  bool sendQueryDirect (const string & query);
  bool bindCol (uint colNum, VarType dstType, void *buf, int bufLen);
  bool prepareQuery (const string & query);
  bool bindParam (uint num, DBQuery::VarType dstType, void *buf, int bufLen);
  bool sendQuery ();

  bool fetch ();

  long affectedRows ();
private:
  string convert (const string & cmd);

  PgConn  *_conn;
  PGresult *_res;
  struct Column
  {
    VarType t;
    void *dst;
    int len;
  };
  struct Param
  {
    VarType t;
    void *dst;
    int len;
  };
  vector<struct Column> _columns;
  vector<struct Param> _params;
  bool _prepeared;
  int _current_row;
  string _prepeared_query;
  string _query_name;
  char **_param_values;
  int *_param_real_len;
  int *_param_formats;

protected:
  void destroy ();
};

#endif // #ifdef USE_PQ

#endif
