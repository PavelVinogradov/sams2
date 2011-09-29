<?php

function mainfrm($step, $lang)
{
	switch ($step) {
	case '1':	step_1($lang);
			break;
	case '2':	step_2($lang);
			break;
	case '3':	step_3($lang);
			break;
	case '4':	step_4($lang);
			break;
	}
}
function setcolor($thisstep,$step)
{
	if($step==$thisstep)
	{
		return("<div class=\"step-on\">");
	}
	else
	{
		return("<div class=\"step-off\">");
	}
	
}
function leftfrm($step, $lang)
{
	$langmodule="./lang/lang.$lang";
	require($langmodule);
	echo "<TABLE>\n";
	echo "<TR><TD>".$color=setcolor($step,1)."$setup_1</div>\n";
	echo "<TR><TD>".$color=setcolor($step,2)."$setup_2</div>\n";
	echo "<TR><TD>".$color=setcolor($step,3)."$setup_3</div>\n";
	echo "<TR><TD>".$color=setcolor($step,4)."$setup_4</div>\n";

	echo "</TABLE>\n";
}
function step_1($lang)
{
	require('./tools.php');
	$langmodule="./lang/lang.$lang";
	require($langmodule);

	print("<INPUT TYPE=\"HIDDEN\" NAME=\"step\" value=\"2\">\n");
	print("<H2 ALIGN=\"CENTER\">$setup_1</H2>");

	if ($handle2 = opendir("./lang"))
        {
	    echo "<SCRIPT language=JAVASCRIPT>\n";
	    echo "function ReloadPage()\n";
	    echo "{\n";
	    echo "  for (var i=0; i < setupform.lang.length; i++)\n";
	    echo "    if (setupform.lang[i].checked)\n";
	    echo "        document.location.replace('setup.php?step=1&lang='+setupform.lang[i].value);\n";
	    echo "}\n";
	    echo "</SCRIPT>\n";
	  while (false !== ($file = readdir($handle2)))
            {
 	      if(strstr($file, "lang.")!=FALSE)
                {
  			$filename2=str_replace("lang.","",$file);
			$language=ReturnLanguage("lang/$file");
			if($filename2==$lang)
			{
     				print("<INPUT TYPE=\"radio\" NAME=\"lang\" VALUE=\"$filename2\" CHECKED> $language<BR>");
			}
			else
			{
     				print("<INPUT TYPE=\"radio\" NAME=\"lang\" VALUE=\"$filename2\"> $language<BR>");
			}
                }
            }
	    print("<BR><INPUT CLASS=\"button\" TYPE=\"BUTTON\" value=\"Reload\" onclick=ReloadPage(lang)>\n");
        }

}

function get_permissions($str, $mode, $comment, $lang)
{
	$langmodule="./lang/lang.$lang";
	require($langmodule);

	echo "<TR><TD WIDTH=50%><B>$comment</B><BR>";
	if (($fout = fopen($str, $mode))!=FALSE)
	{
		fwrite( $fout, "test");
		fclose($fout);
		echo "<TD WIDTH=50%><font color=GREEN> $setup_26</font>";
		return(1);
	}
	echo "<TD WIDTH=50%><font color=red> $setup_27</font>";

  return(0);
}
function get_php_function($phpfunction, $str, $lang)
{
	$langmodule="./lang/lang.$lang";
	require($langmodule);

	echo "<TR><TD WIDTH=50%><B>$str</B>";
	if(function_exists($phpfunction)==TRUE)
	{
		echo "<TD WIDTH=50%><font color=green>$setup_25</font>";
	}
	else
	{
		echo "<TD WIDTH=50%><font color=red>$setup_28</font>";
		//exit;
	}
  return(0);
}
function step_2($lang)
{
	require('./samsclass.php');
	$langmodule="./lang/lang.$lang";
	require($langmodule);

	$SAMSConf=new MAINCONF();

	print("<INPUT TYPE=\"HIDDEN\" NAME=\"step\" value=\"3\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"lang\" value=\"$lang\">\n");
	print("<H2 ALIGN=\"CENTER\">$setup_5</H2>\n");
	echo "<INPUT CLASS=\"button\" TYPE=\"button\" onclick=\"window.location=window.location\" VALUE=\"$setup_18\">\n";	
	print("<H4>$setup_6</H4>\n");

	echo "<TABLE WIDTH=80%>\n";
	echo "<H3><FONT COLOR=BLUE>$setup_19:</FONT></H3>\n";
	if(get_permissions("data/test.txt", "w", $setup_7, $lang)==0)
	{
		echo "<BR><font color=red> $setup_8</font>";
		echo "<BR>$setup_9";
	}
	echo "</TABLE>";

	echo "<H3><FONT COLOR=BLUE>$setup_21:</FONT></H3>";
	echo "<TABLE WIDTH=80%>";
	if($SAMSConf->DB_ENGINE=="MySQL")
	{
		get_php_function("mysql_connect",$setup_24, $lang);
	}
	if($SAMSConf->DB_ENGINE=="PostgreSQL")
	{
		get_php_function("pg_connect",$setup_35, $lang);
	}
	get_php_function("gzopen",$setup_22, $lang);
	get_php_function("imagecreatetruecolor",$setup_23, $lang);
	echo "</TABLE>";


	echo "<H3><FONT COLOR=BLUE>$setup_20:</FONT></H3>";
	echo "<TABLE WIDTH=90%>";
	echo "<TR>";
	echo "<TD WIDTH=30%><B>$setup_31</B>";
	echo "<TD WIDTH=35%><B>$setup_29</B>";
	echo "<TD WIDTH=35%><B>$setup_30</B>";
	echo "<TR>";
	echo "<TD WIDTH=50%><B>safe_mode</B>";
	echo "<TD>on";
	if (function_exists('ini_get'))
	{
		$safe_switch = @ini_get("safe_mode") ? 1 : 0;
	}
	if($safe_switch==0)
	{
		echo "<TD><font color=RED>off</b></font>";
	}
	else
	{
		echo "<TD><font color=GREEN>on</b></font>";
		$safe_mode_exec_dir = @ini_get("safe_mode_exec_dir") ? 1 : 0;
		$safe_mode_exec_dir_path=@ini_get("safe_mode_exec_dir");
		$real_path = realpath(".");
		echo "<TR>";
		echo "<TD WIDTH=50%><B>safe_mode_exec_dir</B>";
		echo "<TD>$real_path/bin";
		if($safe_mode_exec_dir==0)
		{
			echo "<TD><font color=RED>$setup_28</b></font>";
		}
		else
		{
			if($safe_mode_exec_dir_path == "$real_path/bin" )
				echo "<TD><font color=GREEN>$safe_mode_exec_dir_path</b></font>";
			else
				echo "<TD><font color=RED>$safe_mode_exec_dir_path</b></font>";
		}
		echo "<TR>";
		echo "<TD><TD COLSPAN=2>$setup_40";

		$disable_functions = @ini_get("disable_functions") ? 1 : 0;
		$disable_functions_names=@ini_get("disable_functions");
		echo "<TR>";
		echo "<TD WIDTH=50%><B>disable_functions</B>";
		echo "<TD>$setup_41 <B>exec</B>";
		if(strstr($disable_functions_names,",exec"))
		{
			echo "<TD><font color=RED>disable_functions= ".str_replace ( ",", ", ", $disable_functions_names )."</b></font>";
		}
		else
		{
			echo "<TD><font color=GREEN>disable_functions= ".str_replace ( ",", ", ", $disable_functions_names )."</b></font>";
		}
//		echo "<TR>";
//		echo "<TD><TD COLSPAN=2>$setup_40";	
	}


	echo "</TABLE>\n";

}


function step_3($lang)
{
	require('./samsclass.php');
	$langmodule="./lang/lang.$lang";
	require($langmodule);

	$SAMSConf=new MAINCONF();
	$DB=new SAMSDB();

	$result=1;
	if($DB->dberror==0)
	{
		$QUERY="SELECT count(*) FROM auth_param";
		$result=$DB->samsdb_query_value($QUERY);	
		if($result==0)
			$DB->dberror=1;
	}
	if($DB->dberror==1)
	{	

		require('./tools.php');
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"step\" value=\"4\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"lang\" value=\"$lang\">\n");

		print("<SCRIPT LANGUAGE=JAVASCRIPT>\n");
		print("function SetChange()");
		print("{");
		print("if(document.forms[\"setupform\"].elements[\"create\"].checked==true)\n");
		print("  {\n");
		print("    document.forms[\"setupform\"].elements[\"muser\"].disabled=false\n");
		print("    document.forms[\"setupform\"].elements[\"mpass\"].disabled=false\n");
		print("  }\n");
		print("if(document.forms[\"setupform\"].elements[\"create\"].checked==false)\n");
		print("  {\n");
		print("    document.forms[\"setupform\"].elements[\"muser\"].disabled=true\n");
		print("    document.forms[\"setupform\"].elements[\"mpass\"].disabled=true\n");
		print("  }\n");
		print("}\n");
		print("</SCRIPT>\n");

		print("<H2 ALIGN=\"CENTER\">$setup_37</H2>");

		print("<TABLE WIDTH=\"90%\">\n");

		print("<TR><TD ALIGN=RIGHT>$setup_11: <TD ALIGN=LEFT>$SAMSConf->DB_ENGINE\n");
		print("<TR><TD ALIGN=RIGHT>$setup_15: <TD ALIGN=LEFT>$SAMSConf->SAMSDB\n");
		print("<TR><TD ALIGN=RIGHT>$setup_14: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"hostname\" value=\"localhost\">\n");
		print("<TR><TD ALIGN=RIGHT>$setup_12: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"username\" value=\"$dbadmin\">\n");
		print("<TR><TD ALIGN=RIGHT>$setup_13: <TD ALIGN=LEFT><INPUT TYPE=\"PASSWORD\" NAME=\"pass\">\n");
		if($SAMSConf->DB_ENGINE == "MySQL")
		{
			print("<TR><TD ALIGN=RIGHT><P>$setup_32 <INPUT TYPE=\"CHECKBOX\" NAME=\"create\" CHECKED  onclick=SetChange()><TD>\n");
			print("<TR><TD ALIGN=RIGHT><P>$setup_33: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"muser\" value=\"sams@localhost\">\n");
			print("<TR><TD ALIGN=RIGHT>$setup_34: <TD ALIGN=LEFT><INPUT TYPE=\"PASSWORD\" 	NAME=\"mpass\">\n");
		}
		print("</TABLE>\n");

  		print("<H3>SAMS documentation</H3>\n");
  		print("<A HREF=\"http://sams.perm.ru/sams2/doc/EN/index.html\">english</A><BR>\n");
		print("<A HREF=\"http://sams.perm.ru/sams2/doc/RU/index.html\">russian</A><BR>\n");
	}
	else
	{
		echo "<H2 ALIGN=\"CENTER\">$setup_38</H2>";
		echo "$setup_39<BR>";
		echo "<SCRIPT language=JAVASCRIPT>\n";
		echo "function StartWebInterface()\n";
		echo "{\n";
		echo "        document.location.replace('index.html');\n";
		echo "}\n";
		echo "</SCRIPT>\n";
		echo "<BR><INPUT CLASS=\"button\" TYPE=\"BUTTON\" value=\"$setup_36\" onclick=StartWebInterface()>\n";
		echo "</FORM>";
		exit();
		
	}

}

function CreateSAMSdb($lang)
{

	$pgdb=array();

	require('./samsclass.php');

	$SAMSConf=new MAINCONF();

	if(isset($_GET["hostname"]))	$host=$_GET["hostname"];
	if(isset($_GET["username"]))	$user=$_GET["username"];
	if(isset($_GET["pass"])) 	$passwd=$_GET["pass"];
	if(isset($_GET["muser"]))	$muser=$_GET["muser"];
	if(isset($_GET["mpass"]))	$mpass=$_GET["mpass"];
	if(isset($_GET["create"]))	$create=$_GET["create"];

$db=$SAMSConf->DB_ENGINE; 
$odbc=$SAMSConf->ODBC; 
$dbname=$SAMSConf->SAMSDB; 
$odbcsource=$SAMSConf->ODBCSOURCE;


$pgdb[0] = "CREATE TABLE websettings (	s_lang varchar(15) NOT NULL default 'EN', s_iconset varchar(25) NOT NULL default 'classic', s_useraccess smallint NOT NULL default '1', s_urlaccess smallint NOT NULL default '1', s_showutree smallint NOT NULL default '1' , s_showname varchar(5) NOT NULL default 'nick', s_showgraph smallint NOT NULL default '0', 	s_createpdf varchar(5) NOT NULL default 'NONE',	s_version char(5) NOT NULL default '1.0')"; 
$pgdb[1] = "INSERT INTO websettings VALUES('$lang','classic','1','1','1','nick','0','NONE','2.0.0')";
$pgdb[2] = "CREATE TABLE proxy (  s_proxy_id SERIAL PRIMARY KEY, s_description varchar(100) default 'Proxy server', 
s_endvalue bigint NOT NULL default '0', s_redirect_to varchar(100) default 'http://your.ip.address/sams2/icon/classic/blank.gif', s_denied_to varchar(100) default 'http://your.ip.address/sams2/messages', s_redirector varchar(25) default 'NONE', s_delaypool smallint default '0', s_auth varchar(4) default 'ip', s_wbinfopath varchar(100) default '/usr/bin', s_separator varchar(15) default '+', s_usedomain smallint default '0', s_bigd smallint default '2', s_bigu smallint default '2', s_sleep int default '1', s_parser smallint default '0', s_parser_time int default '1', s_count_clean smallint default '0', s_nameencode smallint default '0', s_realsize varchar(4) default 'real', s_checkdns smallint default '0', s_debuglevel int NOT NULL default '0', s_defaultdomain varchar(25) NOT NULL default 'workgroup', s_squidbase int NOT NULL default '0', 
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

$pgdb[3] = "INSERT INTO proxy VALUES ('0', 'Proxy server','0','http://your.ip.address/sams2/icon/classic/blank.gif', 'http://your.ip.address/sams2/messages', 'NONE', '0', 'ip', '/usr/bin','+', '0', '2', '2', '1', '1', '1','0', '0', 'real', '0', '0', 'workgroup', '0', 'NONE', '', '1024'
, '1048576', '0.0.0.0', 'workgroup', 'Administrator', '0', 'Users', '0', '0', '0' ) ";
$pgdb[4] = "CREATE TABLE passwd ( s_user varchar(25) PRIMARY KEY, s_pass varchar(60), s_access int default '0', s_autherrorc smallint default '0', s_autherrort varchar(16) default '0' )";
$pgdb[5] = "INSERT INTO passwd VALUES('admin','00YfpO1MXDzqQ','2','0','' )";
$pgdb[6] = "INSERT INTO passwd VALUES('auditor','00MTbxknCTtNs','1','0','' )";
$pgdb[7] = "CREATE TABLE shablon ( s_shablon_id SERIAL PRIMARY KEY, s_name varchar(100),
s_auth varchar(4) default 'ip', 
s_quote int default '100',s_period varchar(3) NOT NULL default 'M', s_clrdate date NOT NULL default '1980-01-01',
s_alldenied smallint NOT NULL default '0', s_shablon_id2 int default '-1')";
$pgdb[8] = "INSERT INTO shablon VALUES('0','Default','ip','100','M','1980-01-01','0', '-1')";
$pgdb[9] = "CREATE TABLE timerange ( s_trange_id SERIAL PRIMARY KEY, s_name varchar(100), 
s_days varchar(14), 
s_timestart time default '00:00:00', 
s_timeend time default '23:59:59')";
$pgdb[10] = "INSERT INTO timerange VALUES('0','Full day','MTWHFAS','00:00:00','23:59:59')";
$pgdb[11] = "CREATE TABLE sconfig_time ( s_shablon_id int, s_trange_id int )";
if($SAMSConf->DB_ENGINE=="MySQL")
	$pgdb[12] = "INSERT INTO sconfig_time VALUES( '1', '1' )";
else
	$pgdb[12] = "INSERT INTO sconfig_time VALUES( '0', '0' )";
$pgdb[13] = "CREATE TABLE sconfig ( s_shablon_id int, s_redirect_id int )";
$pgdb[14] = "CREATE TABLE redirect ( s_redirect_id SERIAL PRIMARY KEY, s_name varchar(100), s_type varchar(25), s_dest varchar(128) NULL )";
$pgdb[15] = "CREATE TABLE samslog ( s_log_id SERIAL PRIMARY KEY, s_issuer varchar(50) NOT NULL , s_date date NOT NULL, s_time time NOT NULL, s_value varchar(60) NOT NULL, s_code char(2) )";
$pgdb[16] = "CREATE TABLE sgroup ( s_group_id SERIAL PRIMARY KEY, s_name varchar(100) )";
$pgdb[17] = "INSERT INTO sgroup ( s_name ) VALUES( 'Administrators' )";
$pgdb[18] = "INSERT INTO sgroup ( s_name ) VALUES( 'Users' )";
$pgdb[19] = "CREATE TABLE reconfig ( s_proxy_id int, s_service varchar(15), s_action varchar(10) )";
$pgdb[20] = "CREATE TABLE squiduser ( s_user_id SERIAL PRIMARY KEY, 
s_group_id int, 
s_shablon_id int, 
s_nick varchar(50), 
s_family varchar(50), 
s_name varchar(50), 
s_soname varchar(50), 
s_domain varchar(50), 
s_quote int NOT NULL default '0', 
s_size bigint NOT NULL default '0', 
s_hit bigint NOT NULL default '0', 
s_enabled smallint, 
s_ip char (15), 
s_passwd varchar(20), 
s_gauditor smallint default '0', 
s_autherrorc smallint default '0', 
s_autherrort varchar(16) default '0', 
s_webaccess varchar(16) default 'W')";
$pgdb[21] = "CREATE TABLE url (  s_url_id SERIAL PRIMARY KEY, s_redirect_id int, s_url varchar(132) )";
$pgdb[22] = "CREATE TABLE squidcache (  s_cache_id SERIAL PRIMARY KEY, s_proxy_id int, s_date  date NOT NULL default '1980-01-01', s_time time NOT NULL default '00:00:00', s_user varchar(50), s_domain varchar(50), s_size int NOT NULL default '0', s_hit int NOT NULL default '0', s_ipaddr varchar(15), s_period int NOT NULL default '0', s_method varchar(15), s_url varchar(1024) )";
$pgdb[23] = "CREATE TABLE cachesum (  s_proxy_id int NOT NULL, s_date date NOT NULL default '1980-01-01', s_user varchar(50) NOT NULL, s_domain varchar(50), 
s_size bigint NOT NULL default '0', 
s_hit bigint NOT NULL default '0') ";
$pgdb[24] = "CREATE INDEX idx_squidcache on squidcache ( s_user, s_proxy_id )";
$pgdb[25] = "CREATE UNIQUE INDEX idx_cachesum on cachesum ( s_proxy_id, s_date, s_user, s_domain )";
$pgdb[26] = "CREATE INDEX idx_squiduser on squiduser ( s_nick, s_name, s_shablon_id, s_group_id )";
$pgdb[27] = "CREATE INDEX idx_samslog on samslog ( s_code, s_issuer )";
$pgdb[28] = "CREATE INDEX idx_url on url ( s_redirect_id, s_url )";
$pgdb[29] = "CREATE TABLE sysinfo ( s_row_id SERIAL PRIMARY KEY, s_proxy_id INT NOT NULL , s_name VARCHAR( 50 ) NOT NULL , s_version VARCHAR( 10 ) NOT NULL , s_author VARCHAR( 30 ) NULL DEFAULT 'anonymous', s_info VARCHAR( 1024 ) NOT NULL DEFAULT 'not available', s_date TIMESTAMP, s_status INT NOT NULL)";

$pgdb[30] = "create table auth_param (s_auth varchar(4) default '', s_param varchar(50) default '', s_value varchar(50) default '')";
$pgdb[31] = "INSERT INTO auth_param VALUES('ntlm', 'enabled', '0')";
$pgdb[32] = "INSERT INTO auth_param VALUES('ldap', 'enabled', '0')";
$pgdb[33] = "INSERT INTO auth_param VALUES('adld', 'enabled', '0')";
$pgdb[34] = "INSERT INTO auth_param VALUES('ncsa', 'enabled', '0')";
$pgdb[35] = "INSERT INTO auth_param VALUES('ip', 'enabled', '1')";
$pgdb[36] = "CREATE TABLE delaypool (s_pool_id SERIAL PRIMARY KEY, s_name varchar(50), s_class int NOT NULL,
s_agg1 int NOT NULL default '-1', s_agg2 int NOT NULL default '-1',
s_net1 int NOT NULL default '-1', s_net2 int NOT NULL default '-1',
s_ind1 int NOT NULL default '-1', s_ind2 int NOT NULL default '-1')";
$pgdb[37] = "CREATE TABLE d_link_s (s_pool_id int NOT NULL, s_shablon_id int NOT NULL, s_negative int)";
$pgdb[38] = "CREATE TABLE d_link_t (s_pool_id int NOT NULL, s_trange_id int NOT NULL, s_negative int)";
$pgdb[39] = "CREATE TABLE d_link_r (s_pool_id int NOT NULL, s_redirect_id int NOT NULL, s_negative int)";
$pgdb[40] = "INSERT INTO auth_param VALUES('ldap','ldapserver','127.0.0.1')";
$pgdb[41] = "INSERT INTO auth_param VALUES('ldap','basedn','dc=example,dc=com')";
$pgdb[42] = "INSERT INTO auth_param VALUES('ldap','adadmin','cn=Manager,dc=example,dc=com')";
$pgdb[43] = "INSERT INTO auth_param VALUES('ldap','adadminpasswd','secret')";
$pgdb[44] = "INSERT INTO auth_param VALUES('ldap','usersrdn','ou=People')";
$pgdb[45] = "INSERT INTO auth_param VALUES('ldap','usersfilter','(objectClass=Person)')";
$pgdb[46] = "INSERT INTO auth_param VALUES('ldap','usernameattr','gecos')";
$pgdb[47] = "INSERT INTO auth_param VALUES('ldap','groupsrdn','ou=Group')";
$pgdb[48] = "INSERT INTO auth_param VALUES('ldap','groupsfilter','(objectClass=posixGroup)')";

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
	$sDB->samsdb_query("UPDATE passwd SET s_pass='$crpasswd' WHERE s_user='admin' ");		
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

	print("<INPUT TYPE=\"HIDDEN\" NAME=\"step\" value=\"5\">\n");

      printf("<BR><CENTER>");
}

function step_4($lang)
{
	$langmodule="./lang/lang.$lang";
	require($langmodule);

	if(isset($_GET["hostname"])) $hostname=$_GET["hostname"];
	if(isset($_GET["username"])) $username=$_GET["username"];
	if(isset($_GET["pass"])) $pass=$_GET["pass"];

	if(isset($_GET["muser"]))             $muser=$_GET["muser"];
	if(isset($_GET["mpass"]))             $mpass=$_GET["mpass"];
	if(isset($_GET["create"]))            $create=$_GET["create"];

	echo "<H3>$setup_37</H3>\n";
	CreateSAMSdb($lang);
	echo "<H3>$setup_36</H3>\n";
}


	require('./dbclass.php');
	$lang="EN";
	$step=1;
	if(isset($_GET["step"])) $step=$_GET["step"];
	if(isset($_GET["lang"])) $lang=$_GET["lang"];

	$langmodule="./lang/lang.$lang";
	require($langmodule);

	header("Content-type: text/html; charset=$CHARSET");
	print("<HTML><HEAD>");
	print("<link rel=\"STYLESHEET\" type=\"text/css\" href=\"icon/classic/setup.css\">\n");
	print("</head>\n");
	print("<body LINK=\"#ffffff\" VLINK=\"#ffffff\">\n");

	echo "<CENTER>";
	echo "<H1>SAMS v.2 setup</H1>\n";
	if($step>1)
		$prevstep=$step-1;
	else
		$prevstep=1;

	echo "<SCRIPT language=JAVASCRIPT>\n";
	echo "function PrevPage()\n";
	echo "{\n";
	echo "  document.location.replace('setup.php?step=$prevstep&lang=$lang');\n";
	echo "}\n";
	echo "</SCRIPT>\n";

	print("<TABLE WIDTH=80%>");
	echo "<TR><TD VALIGN=\"TOP\">";
	leftfrm($step, $lang);
	echo "<TD>";
	if($step!=4)
		print("<FORM NAME=\"setupform\" ACTION=\"setup.php\">\n");
	else
		print("<FORM NAME=\"setupform\" ACTION=\"index.html\">\n");
	mainfrm($step, $lang);
	print(" <INPUT CLASS=\"button\" TYPE=\"BUTTON\" value=\"<< $setup_17\" onclick=PrevPage() >\n");
	print(" <INPUT CLASS=\"button\" TYPE=\"SUBMIT\" value=\"$setup_16 >>\">\n");
	print("</FORM>\n");
	print("</TABLE>\n");

	echo "</CENTER>";

	print("<HTML><HEAD>");

?>
