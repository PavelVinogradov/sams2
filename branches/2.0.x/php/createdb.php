<?php

  require('./samsclass.php');
  require('./dbclass.php');

  $SAMSConf = new SAMSCONFIG();

  $dbtype   = (isset($_GET["dbname"]))		? $_GET["dbname"]	: $SAMSConf->DB_ENGINE;
  $hostname = (isset($_GET["hostname"]))	? $_GET["hostname"]	: $SAMSConf->DB_SERVER;
  $username = (isset($_GET["username"]))	? $_GET["username"]	: $SAMSConf->DB_USER;
  $pass     = (isset($_GET["pass"]))		? $_GET["pass"]		: $SAMSConf->DB_PASSWORD;
  $samsdb   = (isset($_GET["samsdb"])) 		? $_GET["samsdb"]	: $SAMSConf->SAMSDB;
  $odbc     = (isset($_GET["odbc"]))		? $_GET["odbc"]		: $SAMSConf->ODBC;
  $pdo	    = (isset($_GET["pdo"])) 		? $_GET["pdo"]		: $SAMSConf->PDO;

//  if(isset($_GET["action"])) 		$action=$_GET["action"];
  if(isset($_GET["muser"])) 		$muser=$_GET["muser"];
  if(isset($_GET["mpass"])) 		$mpass=$_GET["mpass"];
  if(isset($_GET["create"])) 		$create=$_GET["create"];

  print("<head>");
  print("<TITLE>SAMS (Squid Account Management System)</TITLE>");
  print("</head><body>");

  print("<CENTER>");
  print("<TABLE BORDER=0 CELLPADDING='5' WIDTH='90%' ><TR><TD ALIGN='CENTER' BGCOLOR='BEIGE'> SAMS DB installations</TABLE><?P>");

	if(!function_exists('mysql_connect'))
	{
		echo "<br><center><font color=red><b>ERROR: MySql for PHP is not properly installed.<br>Try installing mysql for php package </b></font></center>";
		die();
	}
	if(!function_exists('gzopen'))
	{
		echo "<br><center><font color=red><b>ERROR: Zlib for PHP is not properly installed.<br>Try installing Zlib for php package </b></font></center>";
		die();
	}
	if(!function_exists('imagecreatetruecolor'))
	{
		echo "<br><center><font color=red><b>ERROR: GD for PHP is not properly installed.<br>Try installing GD for php package </b></font></center>";
		die();
	}
	if (function_exists('ini_get'))
	{
		$safe_switch = @ini_get("safe_mode") ? 1 : 0;
	}
	if($safe_switch==0)
	{
		echo "<br><center><font color=red><b>ERROR:safe_mode = off</b></font><BR> Switch php into safe_mode = on</center><BR><BR> ";
	}
  //print("dbtype = $dbtype, odbc = $odbc, hostname = $hostname, username = $username, pass = $pass, samsdb = $samsdb");
  CreateSAMSdb($dbtype, $odbc, $hostname, $username ,$pass, $samsdb, $create, $muser, $mpass, $pdo);
  print("</body>");
?>
