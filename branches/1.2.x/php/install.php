<?php

function CreateDatabase($filename)
{

if(isset($_GET["hostname"])) $hostname=$_GET["hostname"];
if(isset($_GET["username"])) $username=$_GET["username"];
if(isset($_GET["pass"])) $pass=$_GET["pass"];

  $link=@mysql_connect($hostname,$username,$pass) || die (mysql_error());
$db_file = "data/$filename";
if($dbf_handle = @fopen($db_file, "r")) 
    {
	echo "<br><center><font color=blue><b>File $filename opened</b></font></center>";
	echo "<br><center><font color=black><b>Please wait, database createst may take up to 30 minutes...";
	flush();
	$sql_query = fread($dbf_handle, filesize($db_file));
	fclose($dbf_handle);
	$dejaLance=0;
	$li = 0;
	foreach ( explode(";", "$sql_query") as $sql_line) {
		$li++;
		if(!mysql_query($sql_line)) {
			if(  mysql_errno()==1062 || mysql_errno()==1061 || mysql_errno()==1044 || mysql_errno()==1065 || mysql_errno()==1060 || mysql_errno()==1054 || mysql_errno()==1091 || mysql_errno()==1061) 
				continue;		

			if(  mysql_errno()==1071 ) {
				echo "<br><center><font color=red><b>ERROR: line $li: query:[$sql_line] failed, KEY was too long<br>You need to redo this query later or you will experience severe performance issues.</b><br>";
				continue;
			}
			
			if(mysql_errno()==1007 || mysql_errno()==1050) {
				$dejaLance = 1;
				continue;
			}
			
			echo "<br><center><font color=red><b>ERROR: line $li: query:[$sql_line] failed</b><br>";
			echo "<b>mysql error: ".mysql_error()." (err:".mysql_errno().")</b></font></center>";
			$nberr++;
		}
		echo ".";
		flush();
	}
	echo "</b></font></center>";
	if(!$nberr&&!$dejaLance)
		echo "<br><center><font color=green><b>Database successfully generated</b></font></center>";
    }
else 
    {
	echo "<br><center><font color=red><b>ERROR: $db_file needed</b></font></center>";
	die();
    }

}



  if(isset($_GET["hostname"])) $hostname=$_GET["hostname"];
  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["pass"])) $pass=$_GET["pass"];
  if(isset($_GET["action"])) 		$action=$_GET["action"];
  if(isset($_GET["muser"])) 		$muser=$_GET["muser"];
  if(isset($_GET["mpass"])) 		$mpass=$_GET["mpass"];
  if(isset($_GET["create"])) 		$create=$_GET["create"];

  print("<head>");
  print("<TITLE>SAMS (Squid Account Management System)</TITLE>");
  print("</head><body>");

  print("<CENTER>");
  print("<TABLE BORDER=0 CELLPADDING='5' WIDTH='90%' ><TR><TD ALIGN='CENTER' BGCOLOR='BEIGE'> SAMS installations</TABLE><?P>");

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
		echo "<br><center><font color=red><b>ERROR:safe_mode = off</b></font> Switch php into safe_mode = on</center><BR><BR> ";
	}
  if($action=="createdatabase")
    {
	$link=@mysql_connect($hostname,$username,$pass) || die (mysql_error());
	$result=mysql_select_db("squidctrl");
	if($result==TRUE)
	   {
		echo "<br><center><font color=red><b>DB squidctrl connected. Installation script stopped.</b></font><BR>Remove base squidctrl and start a script again";
		exit(0);
	   }
	$result=mysql_select_db("squidlog");
	if($result==TRUE)
	   {
		echo "<br><center><font color=red><b>DB squidlog connected. Installation script stopped.</b></font><BR>Remove base squidlog and start a script again";
		exit(0);
	   }

	CreateDatabase("squid_db.sql");
	CreateDatabase("sams_db.sql");
	echo "<CENTER><H1>SAMS databases created</H1>";

     if($create=="on")
	{
		$link=@mysql_connect($hostname,$username,$pass) || die (mysql_error());
		echo "<br><center><font color=black><b>Please wait, create SAMS MySQL user...";
		if(!mysql_query("GRANT ALL ON squidctrl.* TO $muser IDENTIFIED BY '$mpass';")) 
		  {
			if(  mysql_errno()==1062 || mysql_errno()==1061 || mysql_errno()==1044 || mysql_errno()==1065 || mysql_errno()==1060 || mysql_errno()==1054 || mysql_errno()==1091 || mysql_errno()==1061) 
				continue;		

			if(  mysql_errno()==1071 ) {
				echo "<br><center><font color=red><b>ERROR: line $li: query:[$sql_line] failed, KEY was too long<br>You need to redo this query later or you will experience severe performance issues.</b><br>";
				continue;
			}
		  }
		if(!mysql_query("GRANT ALL ON squidlog.* TO $muser IDENTIFIED BY '$mpass';")) 
		  {
			if(  mysql_errno()==1062 || mysql_errno()==1061 || mysql_errno()==1044 || mysql_errno()==1065 || mysql_errno()==1060 || mysql_errno()==1054 || mysql_errno()==1091 || mysql_errno()==1061) 
				continue;		

			if(  mysql_errno()==1071 ) {
				echo "<br><center><font color=red><b>ERROR: line $li: query:[$sql_line] failed, KEY was too long<br>You need to redo this query later or you will experience severe performance issues.</b><br>";
				continue;
			}
		  }
		echo "<br><center><font color=green><b>SAMS MySQL user created</b></font></center>";
	}

      print("<FORM NAME=\"startsams\" ACTION=\"index.html\" TARGET=_parent>\n");
      printf("<BR><CENTER>");
      print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Starting SAMS webinterface\">\n");
      print("</FORM>\n");

      exit(0);
     }
  else
     {
           print("<SCRIPT LANGUAGE=JAVASCRIPT>");
           print("function SetChange()");
           print("{");
           print("if(document.forms[\"createdatabase\"].elements[\"create\"].checked==true)\n");
           print("  {\n");
           print("    document.forms[\"createdatabase\"].elements[\"muser\"].disabled=false\n");
           print("    document.forms[\"createdatabase\"].elements[\"mpass\"].disabled=false\n");
           print("  }\n");
           print("if(document.forms[\"createdatabase\"].elements[\"create\"].checked==false)\n");
           print("  {\n");
           print("    document.forms[\"createdatabase\"].elements[\"muser\"].disabled=true\n");
           print("    document.forms[\"createdatabase\"].elements[\"mpass\"].disabled=true\n");
           print("  }\n");
           print("}\n");
           print("</SCRIPT>");

  	print("<FORM NAME=\"createdatabase\" ACTION=\"install.php\">\n");
  	print("<INPUT TYPE=\"HIDDEN\" NAME=\"action\" value=\"createdatabase\">\n");
  	print("<TABLE WIDTH=\"90%\">\n");
  	print("<TR><TD ALIGN=RIGHT>MySQL Hostname: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"hostname\" value=\"localhost\">\n");
  	print("<TR><TD ALIGN=RIGHT>MySQL login: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"username\" value=\"root\">\n");
  	print("<TR><TD ALIGN=RIGHT>MySQL password: <TD ALIGN=LEFT><INPUT TYPE=\"PASSWORD\" NAME=\"pass\">\n");
  	print("<TR><TD ALIGN=RIGHT><P>Create SAMS MySQL user <INPUT TYPE=\"CHECKBOX\" NAME=\"create\" CHECKED  onclick=SetChange()><TD>\n");
  	print("<TR><TD ALIGN=RIGHT><P>SAMS MySQL user: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"muser\" value=\"sams@localhost\">\n");
  	print("<TR><TD ALIGN=RIGHT>SAMS MySQL user password: <TD ALIGN=LEFT><INPUT TYPE=\"PASSWORD\" NAME=\"mpass\">\n");
  	print("</TABLE>\n");

  	printf("<BR><CENTER>");
  	print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Create Database\">\n");
  	print("</FORM>\n");
   	print("<P><B>SAMS documentation</B><BR>\n");
  	print("<A HREF=\"doc/EN/index.html\">english<BR>\n");
  	print("<A HREF=\"doc/RU/index.html\">russian<BR>\n");
   }
  print("</body>");
?>
