#ifndef SAMS_DBMYSQL_H
#define SAMS_DBMYSQL_H

/*
 * Модуль предназначен для работы с СУБД MySQL
 *
 *
 */

#include <strings.h>
#include <mysql.h>
#include "defines.h"





MYSQL *dbMySQLConnect (const string host_name,
		       const string user_name,
		       const string password,
		       const string db_name,
		       uint port_num, const string socket_name, uint flags);

void dbMySQLDisconnect (MYSQL * conn);






#endif /* #ifndef SAMS_DBMYSQL_H */
