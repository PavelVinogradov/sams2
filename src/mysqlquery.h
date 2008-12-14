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
#ifndef MYSQLQUERY_H
#define MYSQLQUERY_H

#include "config.h"

#ifdef USE_MYSQL

using namespace std;

#include <vector>
#include <string>

#include <mysql.h>

#include "dbquery.h"

class MYSQLConn;

class MYSQLQuery : public DBQuery
{
public:
  MYSQLQuery(MYSQLConn *conn);

  ~MYSQLQuery();

  bool sendQueryDirect (const string & query);
  bool bindCol (uint colNum, DBQuery::VarType dstType, void *buf, int bufLen);
  bool bindCol (uint colNum, enum_field_types dstType, void *buf, int bufLen);

  bool prepareQuery (const string & query);
//  bool bindResult (uint num, DBQuery::VarType dstType, void *buf, int bufLen);
  bool bindParam (uint num, DBQuery::VarType dstType, void *buf, int bufLen);
  bool bindParam (uint num, enum_field_types dstType, void *buf, int bufLen);
  bool sendQuery ();

  bool fetch ();

private:
  struct Column
  {
    enum_field_types t;
    void *dst;
    int len;
  };
  struct Param
  {
    enum_field_types t;
    void *dst;
    //void *use_dst;
    int len;
  };
  bool _prepeared_statement;
  MYSQL_STMT *_statement;
  vector<struct Column> _columns;
  vector<struct Param> _params;
  MYSQL_BIND *_bind_param;
  MYSQL_BIND *_bind_column;
  unsigned long *_param_real_len;
  unsigned long *_columns_real_len;
  bool _param_binded;
  bool _col_binded;
  MYSQL_RES *_res;
  MYSQLConn *_conn;

  /**
   * @brief Выделяет память для запроса
   *
   * @return true если память выделена успешно и false в противном случае
   */
  bool createStatement ();

protected:
  void destroy ();

};

#endif // #ifdef USE_MYSQL

#endif
