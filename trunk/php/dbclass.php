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
  var $samsdb_name;

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
	if(($this->result = mysql_query($query, $this->link))==FALSE)
	{
		$this->dberror=1;
		$this->dberrortext=mysql_error();
	}
	else
	{
		$num_rows = mysql_num_rows($this->result);
		return($num_rows);
	}
	return($num_rows);
  }
  function pgsqldb_query_value($query)
  {
	if(($this->result = pg_query($query))==FALSE)
	{
		$this->dberror=1;
		$this->dberrortext=pg_last_error();
	}
	else
	{
		$num_rows = pg_num_rows($this->result);
		return($num_rows);
	}
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
        $this->result = mysql_query($query,$this->link) or die("Invalid query: " . mysql_error());
  }
  function pgsqldb_query($query)
  {
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
	if(($link=@mysql_connect($host,$user,$passwd,new_link))==FALSE)
	{
		$this->dberror=1;
		$this->dberrortext=mysql_error();
	}
	if($link && mysql_select_db($dbname)==FALSE)
	  {
		$this->dberrortext="Error connection to database $dbname@$host<BR>";
		$this->dberror=1;
	  }
	return($link);

  }
  function pgsqldb_connect($host,$user,$passwd,$dbname)
  {
	$this->dberror=0;
	$link = pg_connect("host=$host dbname=$dbname user=$user password=$passwd");
	return($link);
  }


  function odbcdb_connect($host,$user,$passwd,$dbname)
  {
	$ConnectString="$this->odbc_source";
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
//    echo "PHP version:". $phpver[0]."<BR>";    

    if($this->db_pdo != "0")
    {
	$connection="odbc:sams_mysql";
	$this->pdo_link = new PDO($connection, $user, "$passwd");
	if($this->pdo_link == NULL)
	{
	    print "<FONT COLOR=\"RED\">Error!: database $dbname not created!<br/> Create database $dbname manually</FONT>";
	}
exit(0);
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

  function SAMSDB()
  {
	$SAMSConf=new MAINCONF();

	$db=$SAMSConf->DB_ENGINE;
	$odbc=$SAMSConf->ODBC;
	$host=$SAMSConf->DB_SERVER;
	$user=$SAMSConf->DB_USER;
	$passwd=$SAMSConf->DB_PASSWORD;
	$dbname=$SAMSConf->SAMSDB;
	$odbc_source=$SAMSConf->ODBCSOURCE;
	$this->samsdb_name=$dbname;

	$this->db_odbc=0;
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
		$this->link=$this->mysqldb_connect($host,$user,$passwd,$dbname);
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

class CREATESAMSDB extends SAMSDB
{
  function CREATESAMSDB($db, $odbc, $host, $user ,$passwd, $dbname, $odbc_source)
  {
    $this->samsdb_name=$dbname;
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
	$this->link=$this->mysqldb_connect($host,$user,$passwd,$dbname);
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
			$sDB->samsdb_query("$sql_line;");		
		}
		echo ".";

		flush();
    }

}

function CreateSAMSdbPgSQL($host, $user, $passwd, $dbname)
{
CreateSAMSdb("PostgreSQL", "0", $host, $user ,$passwd, $dbname, "", "", "");

}


?>
