<?php


  require('./dbclass.php');


  if(isset($_GET["hostname"])) $hostname=$_GET["hostname"];
  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["pass"])) $pass=$_GET["pass"];
  if(isset($_GET["dbname"])) $dbname=$_GET["dbname"];
  if(isset($_GET["samsdb"])) $samsdb=$_GET["samsdb"];
  if(isset($_GET["odbc"])) $odbc=$_GET["odbc"];
  if(isset($_GET["pdo"])) $pdo=$_GET["pdo"];
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
  //print("$dbname, $odbc, $hostname, $username ,$pass, $samsdb");
  CreateSAMSdb($dbname, $odbc, $hostname, $username ,$pass, $samsdb, $create, $muser, $mpass, "sams_mysql");
  print("</body>");
?>
