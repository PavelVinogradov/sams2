<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

class SAMSDB
{
  var $link;
  var $result;            # результат выполнения запроса
  var $db_odbc;      # признак того, что используется ODBC
  var $odbc_source;
  var $db_name;     # используемая база данных
  var $db_pdo;      # признак того, что в веб интерфейсе используется PDO
  var $pdo_link;
  var $pdo_stmt;
  var $dberror;
  var $dberrortext;

/*
  samsdb_query_value - Функция посылает запрос базе данных  возвращает количество строк в ответе
  mysqldb_query_value - функция для работы с MySQL
  pgsqldb_query_value - функция для работы с PgSQL
  переменные:
  $query - SQL pапрос  
  $this->result - результат выполнения запроса
  $num_rows - Возвращаемое значение - количество возвращаемых строк
*/

  function mysqldb_query_value($query)
  {
        $this->result = mysql_query($query) or die("Invalid query: " . mysql_error());
	$num_rows = mysql_num_rows($this->result);
	return($num_rows);
  }
  function pgsqldb_query_value($query)
  {
	$this->result = pg_query($query) or die('Query failed: ' . pg_last_error());
	$num_rows = pg_num_rows($this->result);
	return($num_rows);
  }
  function pdodb_query_value($query)
  {
	$this->pdo_stmt = $this->pdo_link->prepare($query);
	$this->pdo_stmt->execute();
	$num_rows = $this->pdo_stmt->rowCount();
	return($num_rows);


	$this->pdo_stmt = $this->pdo_link->prepare("SELECT * FROM passwd ");
	if ($this->pdo_stmt->execute()) 
	{
  		while ($row = $this->pdo_stmt->fetch()) 
		{
    			print_r($row);
  		}
	}
  }
  function odbcdb_query_value($query)
  {
	$this->result = odbc_exec($this->link,$query);
	if($this->result!=FALSE)
	{
		$num_rows = odbc_num_rows($this->result);
	}
	return($num_rows);
  }
  function samsdb_query_value($query)
  {
   if($this->db_name=="MySQL" && $this->db_odbc==0)
      {
         $num_rows = $this->mysqldb_query_value($query);
         return($num_rows);
      }
    if($this->db_name=="PostgreSQL" && $this->db_odbc==0)
      {
         $num_rows = $this->pgsqldb_query_value($query);
         return($num_rows);
      }
    if($this->db_pdo==1)
      {
         $num_rows = $this->pdodb_query_value($query);
         return($num_rows);
      }
    if($this->db_odbc==1 && $this->db_pdo==0)
      {
         $num_rows = $this->odbcdb_query_value($query);
         return($num_rows);
      }
    return(FALSE);
  }
/*
  samsdb_fetch_array - Функция обрабатывает результат запроса, возвращая ассоциативный массив
  mysqldb_fetch_array - функция для работы с MySQL
  pgsqldb_fetch_array - функция для работы с PgSQL
  переменные:
  $this->result - результат выполнения запроса
  $row - Возвращаемое значение - количество возвращаемых строк
*/
  function odbcdb_fetch_array()
  {
	$row=odbc_fetch_array($this->result,0);
         return($row);
  }
  function pdodb_fetch_array()
  {
	$row = $this->pdo_stmt->fetch(); 
//	print_r($row);
         return($row);
  }
  function mysqldb_fetch_array()
  {
         $row=mysql_fetch_array($this->result);
         return($row);
  }
  function pgsqldb_fetch_array()
  {
         $row=pg_fetch_array($this->result);
         return($row);
  }

  function samsdb_fetch_array()
  {
    if($this->db_name=="MySQL" && $this->db_odbc==0)
      {
         $row=$this->mysqldb_fetch_array();
         return($row);
      }
    if($this->db_name=="PostgreSQL" && $this->db_odbc==0)
      {
         $row=$this->pgsqldb_fetch_array();
         return($row);
      }
    if($this->db_pdo==1)
      {
         $row = $this->pdodb_fetch_array();
         return($row);
      }
    if($this->db_odbc==1 && $this->db_pdo==0)
      {
         $row = $this->odbcdb_fetch_array();
         return($row);
      }
    return(FALSE);
  }

/*
  samsdb_query - Функция посылает запрос базе данных
  mysqldb_query - функция для работы с MySQL
  pgsqldb_query - функция для работы с PgSQL
  переменные:
  $query - SQL pапрос  
  $this->result - результат выполнения запроса
*/
  function mysqldb_query($query)
  {
        $this->result = mysql_query($query) or die("Invalid query: " . mysql_error());
  }
  function pgsqldb_query($query)
  {
//echo "QUERY = $query\n";
	$this->result = pg_query($query) or die('Query failed: ' . pg_last_error());
  }
  function pdodb_query($query)
  {
	$this->pdo_stmt = $this->pdo_link->prepare("$query");
	$this->pdo_stmt->execute();
  }
  function odbcdb_query($query)
  {
	$this->result = odbc_exec($this->link, $query) or die('Query failed: ' . odbc_error($this->link));
  }

  function samsdb_query($query)
  {
   //if($this->dberror==0)
   //{
	if($this->db_name=="MySQL" && $this->db_odbc==0)
	{
	    $this->mysqldb_query($query);
	    return($this->result);
	    }
	if($this->db_name=="PostgreSQL" && $this->db_odbc==0)
	{
	     $num_rows = $this->pgsqldb_query($query);
	    return($this->result);
	}
	if($this->db_pdo==1)
	{
	    $num_rows = $this->pdodb_query($query);
	    return($this->result);
	}
	if($this->db_odbc==1 && $this->db_pdo==0)
	{
	    $num_rows = $this->odbcdb_query($query);
	    return($this->result);
	}
    //}
    return(FALSE);
  }


/*
  free_samsdb_query - Функция высвобождает всю память, занимаемую результатом
  free_mysqldb_query - функция для работы с MySQL
  free_pgsqldb_query - функция для работы с PgSQL
  переменные:
  $this->result - результат выполнения запроса
*/
  function free_mysqldb_query()
  {
    mysql_free_result($this->result);
  }
  function free_pgsqldb_query()
  {
    pg_free_result($this->result);
  }
  function free_odbcdb_query()
  {
    odbc_free_result($this->result);
  }
  function free_samsdb_query()
  {
    if($this->db_name=="MySQL" && $this->db_odbc==0)
      {
         $this->free_mysqldb_query();
      }
    if($this->db_name=="PostgreSQL" && $this->db_odbc==0)
      {
         $this->free_pgsqldb_query();
      }
    if($this->db_odbc==1 && $this->db_odbc==0)
      {
         $this->free_odbcdb_query();
      }
  }

  function samsdb_set_encoding($charset)
  {
    if($this->db_name=="PostgreSQL" && $charset=="KOI8-R")
      {
	 pg_set_client_encoding($this->link,"KOI8");
      }
  }


/*
  mysqldb_connect - функция открывает соединение с MySQL
  pgsqldb_connect - функция открывает соединение с PgSQL
  переменные:
  $host - host
  $user - пользователь БД
  $passwd - пароль пользователя БД
  $dbname - название базы данных
*/
  function mysqldb_connect($host,$user,$passwd,$dbname)
  {
//	$link=@mysql_connect($host,$user,$passwd) || die (mysql_error());
//	$link=@mysql_connect($host,$user,$passwd) || die (mysql_error());
	if(($link=@mysql_connect($host,$user,$passwd))==FALSE)
	{
		$this->dberrortext=mysql_error();
	}

	if($link && mysql_select_db($dbname)==FALSE)
	  {
		$this->dberrortext="Error connection to database $dbname@$host<BR>";
		$this->dberror=1;
	  }
	return(0);
  }
  function pgsqldb_connect($host,$user,$passwd,$dbname)
  {
	$this->dberror=0;
	$link = pg_connect("host=$host dbname=$dbname user=$user password=$passwd") or die('Could not connect: ' . pg_last_error());
	return($link);
  }


  function odbcdb_connect($host,$user,$passwd,$dbname)
  {
//	$ConnectString="sams_mysql";
	$ConnectString="$this->odbc_source";
//echo "ConnectString=$this->odbc_source";
	$this->link = odbc_connect($ConnectString, $user, $passwd) or die('Could not connect: ' . odbc_error($this->link));	    
  }

  function pdodb_connect($host,$user,$passwd,$dbname)
  {

    $phpver=explode(".",phpversion());
    if($phpver[0]<5)
    {
	print "<FONT COLOR=\"RED\">Error!: php version = ". phpversion() .", php_pdo not supported!<br/></FONT>";
	exit(0);	
    }
    echo "PHP version:". $phpver[0]."<BR>";    

    if($this->db_pdo != "0")
    {
	//$connection="odbc:sams_mysql";

	//$this->pdo_link = new PDO($connection, $user, "$passwd");

	$connection="odbc:sams_mysql";
	$this->pdo_link = new PDO($connection, $user, "$passwd");
	if($this->pdo_link == NULL)
	{
	    print "<FONT COLOR=\"RED\">Error!: database $dbname not created!<br/> Create database $dbname manually</FONT>";
	}
exit(0);
/*
	try{
		$connection="odbc:sams_mysql";
		$this->pdo_link = new PDO($connection, $user, "$passwd");
	} 
	catch(PDOException $e){
		$this->dberror=1;
		print "<FONT COLOR=\"RED\">Error!: database $dbname not created!<br/> Create database $dbname manually</FONT>";
		die();
	}
*/
    }

}
/*
  mysqldb_connect - функция открывает соединение с MySQL
  pgsqldb_connect - функция открывает соединение с PgSQL
  переменные:
  $db - база данных, к которой происходит подключение
  $odbc - признак использования ODBC 
        1 - используется
        0 - не используется
  $host - host
  $user - пользователь БД
  $passwd - пароль пользователя БД
  $dbname - название базы данных
*/

//  function SAMSDB($db, $odbc, $host, $user ,$passwd, $dbname, $odbc_source)
//$DB=new SAMSDB($SAMSConf->DB_ENGINE, $SAMSConf->ODBC, $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB, $SAMSConf->ODBCSOURCE);

  function SAMSDB($samsconf)
  {

$db=$samsconf->DB_ENGINE;
$odbc=$samsconf->ODBC;
$host=$samsconf->DB_SERVER;
$user=$samsconf->DB_USER;
$passwd=$samsconf->DB_PASSWORD;
$dbname=$samsconf->SAMSDB;
$odbc_source=$samsconf->ODBCSOURCE;

//echo "$dbname: $user@$host <BR>";
    $phpver=explode(".",phpversion());
    if( $odbc==1 )
    {
        $this->db_odbc=1;
        $this->odbc_source=$odbc_source;
	if(function_exists('odbc_connect'))
	{
	    $this->db_odbc=1;
	}
	else
	{
	    $this->db_pdo=1;
	}
    }
    $this->db_name=$db;

    if($this->db_name=="MySQL" && $this->db_odbc==0 && $this->db_pdo==0)
      {
	$link=$this->mysqldb_connect($host,$user,$passwd,$dbname);
      }
    if($this->db_name=="PostgreSQL" && $this->db_odbc==0 && $this->db_pdo==0)
      {
	$this->link=$this->pgsqldb_connect($host,$user,$passwd,$dbname);
      }
    if($this->db_odbc==1 && $this->db_pdo==0)
      {
	$this->odbcdb_connect($host,$user,$passwd,$dbname);
      }
    if($this->db_odbc==1 && $this->db_pdo==1)
      {
	$this->pdodb_connect($host,$user,$passwd,$dbname);
      }
  }
/*
  function SAMSDB($db, $odbc, $host, $user ,$passwd, $dbname, $odbc_source)
  {

    $phpver=explode(".",phpversion());
    if( $odbc==1 )
    {
        $this->db_odbc=1;
        $this->odbc_source=$odbc_source;
	if(function_exists('odbc_connect'))
	{
	    $this->db_odbc=1;
	}
	else
	{
	    $this->db_pdo=1;
	}
    }
    $this->db_name=$db;

    if($this->db_name=="MySQL" && $this->db_odbc==0 && $this->db_pdo==0)
      {
	$link=$this->mysqldb_connect($host,$user,$passwd,$dbname);
      }
    if($this->db_name=="PostgreSQL" && $this->db_odbc==0 && $this->db_pdo==0)
      {
	$this->link=$this->pgsqldb_connect($host,$user,$passwd,$dbname);
      }
    if($this->db_odbc==1 && $this->db_pdo==0)
      {
	$this->odbcdb_connect($host,$user,$passwd,$dbname);
      }
    if($this->db_odbc==1 && $this->db_pdo==1)
      {
	$this->pdodb_connect($host,$user,$passwd,$dbname);
      }
  }

*/

}

class CREATESAMSDB extends SAMSDB
{
  function CREATESAMSDB($db, $odbc, $host, $user ,$passwd, $dbname, $odbc_source)
  {
    $phpver=explode(".",phpversion());
    if( $odbc==1 )
    {
        $this->db_odbc=1;
        $this->odbc_source=$odbc_source;
	if(function_exists('odbc_connect'))
	{
	    $this->db_odbc=1;
	}
	else
	{
	    $this->db_pdo=1;
	}
    }
    $this->db_name=$db;

    if($this->db_name=="MySQL" && $this->db_odbc==0 && $this->db_pdo==0)
      {
	$link=$this->mysqldb_connect($host,$user,$passwd,$dbname);
      }
    if($this->db_name=="PostgreSQL" && $this->db_odbc==0 && $this->db_pdo==0)
      {
	$this->link=$this->pgsqldb_connect($host,$user,$passwd,$dbname);
      }
    if($this->db_odbc==1 && $this->db_pdo==0)
      {
	$this->odbcdb_connect($host,$user,$passwd,$dbname);
      }
    if($this->db_odbc==1 && $this->db_pdo==1)
      {
	$this->pdodb_connect($host,$user,$passwd,$dbname);
      }
  }

}

function CreatePgSQLDB($filename)
{
$sDB=new CREATESAMSDB("PostgreSQL", "0", "localhost", "postgres", "", "samsdb");

if($dbf_handle = @fopen($filename, "r")) 
    {
	echo "File $filename \n";
	flush();
	$sql_query = fread($dbf_handle, filesize($filename));
	fclose($dbf_handle);
	$dejaLance=0;
	$li = 0;
	foreach ( explode(";", "$sql_query") as $sql_line) 
		{
//			echo "$sql_line\n";
			$sDB->samsdb_query("$sql_line;");		
		}
		echo ".";

		flush();
    }

}



//$db, $odbc, $host, $user ,$passwd, $dbname
function CreateSAMSdb($db, $odbc, $host, $user ,$passwd, $dbname, $create, $muser, $mpass, $odbcsource)
{
 $pgdb=array();
$pgdb[0] = "CREATE TABLE websettings (	s_lang varchar(15) NOT NULL default 'EN', s_iconset varchar(25) NOT NULL default 'classic', s_useraccess smallint NOT NULL default '1', s_urlaccess smallint NOT NULL default '1', s_showutree smallint NOT NULL default '1' , s_showname varchar(5) NOT NULL default 'nick', s_showgraph smallint NOT NULL default '0', 	s_createpdf varchar(5) NOT NULL default 'NONE',	s_version char(5) NOT NULL default '1.0')"; 
$pgdb[1] = "INSERT INTO websettings VALUES('EN','classic','1','1','1','nick','0','NONE','2.0.0')";
$pgdb[2] = "CREATE TABLE proxy (  s_proxy_id SERIAL PRIMARY KEY, s_description varchar(100) default 'Proxy server', 
s_endvalue bigint NOT NULL default '0', s_redirect_to varchar(100) default 'http://your.ip.address/sams2/icon/classic/blank.gif', s_denied_to varchar(100) default 'http://your.ip.address/sams2/messages', s_redirector varchar(25) default 'NONE', s_delaypool smallint default '0', s_auth varchar(4) default 'ip', s_wbinfopath varchar(100) default '/usr/bin', s_separator varchar(15) default '+', s_usedomain smallint default '0', s_bigd smallint default '0', s_bigu smallint default '0', s_sleep int default '1', s_parser smallint default '0', s_parser_time int default '1', s_count_clean smallint default '0', s_nameencode smallint default '0', s_realsize varchar(4) default 'real', s_checkdns smallint default '0', s_debuglevel int NOT NULL default '0', s_defaultdomain varchar(25) NOT NULL default 'workgroup', s_squidbase int NOT NULL default '0', 
s_udscript varchar(100) NOT NULL default 'NONE', 
s_adminaddr varchar(60) NOT NULL default 'root@localhost', 
s_kbsize varchar(15) NOT NULL default '1024', 
s_mbsize varchar(15) NOT NULL default '1048576', 
s_ldapserver varchar(30) NOT NULL DEFAULT '0.0.0.0', 
s_ldapbasedn varchar(50) NOT NULL DEFAULT 'workgroup', 
s_ldapuser varchar(50) NOT NULL DEFAULT 'Administrator',
s_ldappasswd varchar(50) NOT NULL DEFAULT '0', 
s_ldapusergroup varchar(50) NOT NULL DEFAULT 'Users',
s_autouser  int NOT NULL DEFAULT '0',
s_autotpl int DEFAULT '0',
s_autogrp int DEFAULT '0')";

//'0', 'Proxy server','0','http://your.ip.address/sams/icon/classic/blank.gif', 'http://your.ip.address/sams/messages', 'NONE', '0', 'ip', '/usr/bin','+', '0', '0', '0', '1', '1', '1','0', '0', 'real', '0', '0', 'workgroup', '0', 'NONE', '', '1024', '1048576')
//$pgdb[3] = "INSERT INTO proxy SET s_description='main proxy server' ";
$pgdb[3] = "INSERT INTO proxy VALUES ('0', 'Proxy server','0','http://your.ip.address/sams/icon/classic/blank.gif', 'http://your.ip.address/sams/messages', 'NONE', '0', 'ip', '/usr/bin','+', '0', '0', '0', '1', '1', '1','0', '0', 'real', '0', '0', 'workgroup', '0', 'NONE', '', '1024'
, '1048576', '0.0.0.0', 'workgroup', 'Administrator', '0', 'Users', '0', '0', '0' ) ";
$pgdb[4] = "CREATE TABLE passwd ( s_user varchar(25) PRIMARY KEY, s_pass varchar(60), s_access int default '0', s_autherrorc smallint default '0', s_autherrort varchar(16) default '0' )";
$pgdb[5] = "INSERT INTO passwd VALUES('Admin','00YfpO1MXDzqQ','2','0','' )";
$pgdb[6] = "INSERT INTO passwd VALUES('Auditor','00MTbxknCTtNs','1','0','' )";
$pgdb[7] = "CREATE TABLE shablon ( s_shablon_id SERIAL PRIMARY KEY, s_name varchar(25),
s_shablonpool bigint default '0', s_userpool bigint default '0', s_auth varchar(4) default 'ip', 
s_quote int default '100',s_period varchar(3) NOT NULL default 'M', s_clrdate date NOT NULL default '1980-01-01',
s_alldenied smallint NOT NULL default '0', s_shablon_id2 int NULL)";
$pgdb[8] = "INSERT INTO shablon VALUES('0','Default','64000','64000','ip','100','M','1980-01-01','0', '-1')";
$pgdb[9] = "CREATE TABLE timerange ( s_trange_id SERIAL PRIMARY KEY, s_name varchar(25), 
s_days varchar(14), 
s_timestart time default '00:00:00', 
s_timeend time default '23:59:59')";
//s_shour int default '0', s_smin int default '0', s_ehour int default '23', s_emin int default '59')";
$pgdb[10] = "INSERT INTO timerange VALUES('0','Full day','MTWHFAS','00:00:00','00:00:00')";
$pgdb[11] = "CREATE TABLE sconfig_time ( s_shablon_id int, s_trange_id int )";
$pgdb[12] = "CREATE TABLE sconfig ( s_shablon_id int, s_redirect_id int )";
$pgdb[13] = "CREATE TABLE redirect ( s_redirect_id SERIAL PRIMARY KEY, s_name varchar(25), s_type varchar(25), s_dest varchar(128) NULL )";
$pgdb[14] = "CREATE TABLE samslog ( s_log_id SERIAL PRIMARY KEY, s_issuer varchar(50) NOT NULL , s_date date NOT NULL, s_time time NOT NULL, s_value varchar(60) NOT NULL, s_code char(2) )";
$pgdb[15] = "CREATE TABLE sgroup ( s_group_id SERIAL PRIMARY KEY, s_name varchar(50) )";
$pgdb[16] = "INSERT INTO sgroup ( s_name ) VALUES( 'Administrators' )";
$pgdb[17] = "INSERT INTO sgroup ( s_name ) VALUES( 'Users' )";
$pgdb[18] = "CREATE TABLE reconfig ( s_proxy_id int, s_service varchar(15), s_action varchar(10) )";
$pgdb[19] = "CREATE TABLE squiduser ( s_user_id SERIAL PRIMARY KEY, s_group_id int, s_shablon_id int, s_nick varchar(50), s_family varchar(50), s_name varchar(50), s_soname varchar(50), s_domain varchar(50), s_quote int NOT NULL default '0', s_size bigint NOT NULL default '0', s_hit bigint NOT NULL default '0', s_enabled smallint, s_ip char (15), s_passwd varchar(20), s_gauditor smallint, s_autherrorc smallint default '0', s_autherrort varchar(16) default '0', s_webaccess varchar(16) default 'W')";
$pgdb[20] = "CREATE TABLE url (  s_url_id SERIAL PRIMARY KEY, s_redirect_id int, s_url varchar(132) )";
$pgdb[21] = "CREATE TABLE squidcache (  s_cache_id SERIAL PRIMARY KEY, s_proxy_id int, s_date  date NOT NULL default '1980-01-01', s_time time NOT NULL default '00:00:00', s_user varchar(50), s_domain varchar(50), s_size int NOT NULL default '0', s_hit int NOT NULL default '0', s_ipaddr varchar(15), s_period int NOT NULL default '0', s_method varchar(15), s_url varchar(1024) )";
$pgdb[22] = "CREATE TABLE cachesum (  s_proxy_id int NOT NULL, s_date date NOT NULL default '1980-01-01', s_user varchar(50) NOT NULL, s_domain varchar(50), 
s_size bigint NOT NULL default '0', 
s_hit bigint NOT NULL default '0') ";
$pgdb[23] = "CREATE INDEX idx_squidcache on squidcache ( s_user, s_proxy_id )";
$pgdb[24] = "CREATE UNIQUE INDEX idx_cachesum on cachesum ( s_proxy_id, s_date, s_user, s_domain )";
$pgdb[25] = "CREATE INDEX idx_squiduser on squiduser ( s_nick, s_name, s_shablon_id, s_group_id )";
$pgdb[26] = "CREATE INDEX idx_samslog on samslog ( s_code, s_issuer )";
$pgdb[27] = "CREATE INDEX idx_url on url ( s_redirect_id, s_url )";
$pgdb[28] = "CREATE TABLE sysinfo ( s_proxy_id INT NOT NULL , s_name VARCHAR( 50 ) NOT NULL , s_version VARCHAR( 10 ) NOT NULL ,
s_author VARCHAR( 30 ) NULL DEFAULT 'anonymous', s_info VARCHAR( 1024 ) NOT NULL DEFAULT 'not available', s_date DATETIME NOT NULL ,
s_status INT NOT NULL)";

$pgdb[29] = "create table auth_param (s_auth varchar(4) default '', s_param varchar(50) default '', s_value varchar(50) default '')";
$pgdb[30] = "INSERT INTO auth_param VALUES('ncsa', 'enabled', '0')";
$pgdb[31] = "INSERT INTO auth_param VALUES('ldap', 'enabled', '0')";
$pgdb[32] = "INSERT INTO auth_param VALUES('adld', 'enabled', '0')";
$pgdb[33] = "INSERT INTO auth_param VALUES('ncsa', 'enabled', '0')";
$pgdb[34] = "INSERT INTO auth_param VALUES('ip', 'enabled', '1')";



    $crpasswd=crypt("qwerty","00");
    if($db=="unixODBC")
      {
	$sDB=new CREATESAMSDB($db, $odbc, $host, $user, $passwd, $dbname, $odbcsource);
	
	echo "<TABLE WIDTH=95%>";
	for( $i=0; $i<count($pgdb); $i++)
	  {
		echo "<TR><TD VALIGN=TOP WIDTH=5%>$i: <TD><FONT SIZE=-1>$pgdb[$i]</FONT>\n";
		$result=$sDB->samsdb_query("$pgdb[$i];");	
		if($result>0)
			echo "<TD VALIGN=TOP><B>Ok</B>\n";
		else
			echo "<TD VALIGN=TOP><FONT COLOR=RED>ERROR</FONT>\n";
	  }
	echo "</TABLE>";
      }

    if($db=="MySQL" && ($odbc==0 || $odbc == "No"))
      {
	$link=@mysql_connect($host,$user,$passwd) || die (mysql_error());
	if($link && mysql_select_db($dbname)==FALSE)
	  {
		echo "Create database $dbname<BR>";
		$result = mysql_query("CREATE DATABASE $dbname") or die("Invalid query: " . mysql_error());
		echo "Database $dbname Created<BR>";
	  }
	else
	  {
		echo "Database $dbname is created<BR>\n";
	   }
	$sDB=new CREATESAMSDB($db, $odbc, $host, $user, $passwd, $dbname, $odbcsource);
	$sDB->mysqldb_connect($host,$user,$passwd,$dbname);
	echo "<TABLE WIDTH=95%>";
	for( $i=0; $i<count($pgdb); $i++)
	   {
		echo "<TR><TD VALIGN=TOP WIDTH=5%>$i: <TD><FONT SIZE=-1>$pgdb[$i]</FONT>\n";
		$result=$sDB->samsdb_query("$pgdb[$i];");		
		if($result>0)
			echo "<TD VALIGN=TOP><B>Ok</B>\n";
		else
			echo "<TD VALIGN=TOP><FONT COLOR=RED>ERROR</FONT>\n";
	   }
	echo "</TABLE>";
	$sDB->samsdb_query("UPDATE passwd SET s_pass='$crpasswd' WHERE s_user='Admin' ");		
        if($create=="on")
		$sDB->samsdb_query("GRANT ALL ON $dbname.* TO $muser IDENTIFIED BY '$mpass' ");		
     }

    if($db=="PostgreSQL" && $odbc==0)
      {
	$sDB=new CREATESAMSDB("PostgreSQL", "0", $host, $user, $passwd, $dbname, $odbcsource);
	$sDB->pgsqldb_connect($host,$user,$passwd,$dbname);

	echo "<TABLE WIDTH=95%>";
	 for( $i=0; $i<count($pgdb); $i++)
	  {
		echo "<TR><TD VALIGN=TOP WIDTH=5%>$i: <TD><FONT SIZE=-1>$pgdb[$i]</FONT>\n";
		$result=$sDB->samsdb_query("$pgdb[$i];");		
		if($result>0)
			echo "<TD VALIGN=TOP><B>Ok</B>\n";
		else
			echo "<TD VALIGN=TOP><FONT COLOR=RED>ERROR</FONT>\n";
	  }
	echo "</TABLE>";
      }

      print("<FORM NAME=\"startsams\" ACTION=\"index.html\" TARGET=_parent>\n");
      printf("<BR><CENTER>");
      print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Start SAMS webinterface\">\n");
      print("</FORM>\n");
exit(0);
}

function CreateSAMSdbPgSQL($host, $user, $passwd, $dbname)
{
CreateSAMSdb("PostgreSQL", "0", $host, $user ,$passwd, $dbname, "", "", "");

}


?>
