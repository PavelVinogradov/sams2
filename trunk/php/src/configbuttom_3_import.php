<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
// SHOW TABLE STATUS WHERE name='squidusers';
//SHOW VARIABLES where variable_name='character_set_database';
//show server_encoding;
class IMPORTUSERS
{
  var $sams1charset;
  var $sams2charset;
  var $pgcharset;
  var $encode;
  var $groupname = array();
  var $trangename = array();
  var $trange = array();
  var $trangecount;
  
  var $urllistname=array();
  var $urllistid2=array();
  var $urllistcount;
  var $groupid=array();
  var $groupid2=array();
  var $groupcount;
  var $groupcount2;
  var $shablonname=array();
  var $shablonid=array();
  var $shablonid2=array();
  var $shabloncount;
  var $shabloncount2;
  var $DB;
  var $oldDB;
  var $DBcharset;
  var $oldDBcharset;

function importurllists()
{
  global $SAMSConf;
  global $USERConf;


  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$DBNAME="";
	if($SAMSConf->DB_ENGINE=="MySQL")
		$DBNAME="samsdb.";

	$shabloncount=0;
	echo "<H2>$configbuttom_3_import_importurllists_1</H2>";
	echo "<TABLE CLASS=samstable>\n";
	echo "<TH>$redir_1\n";
	echo "<TH>\n";
	$QUERY="SELECT * FROM squidctrl.redirect ";

	$this->oldDB->samsdb_query_value($QUERY);
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		$row_name=iconv($this->sams1charset,$this->sams2charset,$row['name']);
		echo "<TR><TD><B>$row_name</B><TD> added<BR>";
		$this->DB->samsdb_query("INSERT INTO redirect (s_name,s_type) VALUES( '$row_name', '$row[type]')");
	}
	$this->oldDB->free_samsdb_query();
	$i=0;
        $QUERY="SELECT * FROM redirect ";
	$this->urllistcount=$this->DB->samsdb_query_value($QUERY);
	while($row=$this->DB->samsdb_fetch_array())
	{
		$this->urllistname[$i]=$row['s_name'];
		$this->urllistid2[$i]=$row['s_redirect_id'];
		$i++;
	}
	$this->DB->free_samsdb_query();

        $QUERY="SELECT urls.*,redirect.name as rname FROM squidctrl.urls LEFT JOIN squidctrl.redirect ON urls.type=redirect.filename ";
	$this->oldDB->samsdb_query_value($QUERY);
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		$group_nick=iconv($this->sams1charset,$this->sams2charset,$row['rname']);
		$index=array_search($group_nick, $this->urllistname);
			$this->DB->samsdb_query("INSERT INTO url ( s_redirect_id , s_url ) VALUES ( '".$this->urllistid2[$index]."', '$row[url]' )");
	}
	$this->oldDB->free_samsdb_query();
	echo "</TABLE>\n";

}


function importgroups()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$DBNAME="$SAMSConf->SAMSDB.";

	$this->groupcount=0;
	$this->oldDB->samsdb_query_value("SELECT * FROM sams ");
	$row=$this->oldDB->samsdb_fetch_array();
	
	$this->oldDB->samsdb_query_value("SELECT * FROM groups ");
	echo "<H2>$configbuttom_3_import_importgroups_1</H2>";
	echo "<TABLE CLASS=samstable>\n";
	echo "<TH>$grouptray_NewGroupForm_2\n";
	echo "<TH>\n";
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		$row_nick=iconv($this->sams1charset,$this->sams2charset,$row['nick']);
		$this->groupname[] =$row_nick;
		$this->groupid[]=$row['name'];

		if($row['nick']!="Administrators"&&$row['nick']!="Users")
		{

			$GROUPNAME=$this->groupname[$this->groupcount];
			echo "<TR><TD><B>$GROUPNAME</B>";

			$QUERY="INSERT INTO sgroup ( s_name ) VALUES ('".$GROUPNAME."') ";
			$this->DB->samsdb_query($QUERY);
			echo "<TD>added";
		}
		$this->groupcount++;
	}
  $this->oldDB->free_samsdb_query();
  echo "</TABLE>\n";
}

function importtimerange()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$DBNAME="";
	if($SAMSConf->DB_ENGINE=="MySQL")
		$DBNAME="samsdb.";

	$this->oldDB->samsdb_query_value("select days,shour,smin,ehour,emin from shablons group by days,shour,smin,ehour,emin");
	echo "<H2>$configbuttom_3_import_importtimerange_1</H2>";
	echo "<TABLE CLASS=samstable>\n";
	echo "<TH>$shablonbuttom_1_prop_UpdateShablonForm_14\n";
	echo "<TH>$shablonbuttom_1_prop_UpdateShablonForm_13\n";
	echo "<TH>\n";
	$this->trangecount=0;
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		$ehour=$row['ehour'];
		$emin=$row['emin'];
		if( $ehour=="24" && $emin=="00" )
		{
			$ehour=23;
			$emin=59;
		}

		echo "<TR><TD>".$row['days'];
		echo "<TD>".$row['shour'].".".$row['smin'].":".$ehour.".".$emin;

		$QUERY="INSERT INTO timerange( s_name, s_days, s_timestart, s_timeend ) VALUES ( 'import_".$this->trangecount."', '".$row['days']."', '".$row['shour'].":".$row['smin'].":00','".$ehour.":".$emin.":00' )";
		$this->DB->samsdb_query($QUERY);
		echo "<TD>added";
		$this->trangecount++;
	}
  $this->oldDB->free_samsdb_query();
  echo "</TABLE>\n";
}

function importshablons()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$DBNAME="$SAMSConf->SAMSDB.";

	$shabloncount=0;
	$this->oldDB->samsdb_query_value("SELECT * FROM shablons ");
	echo "<H2>$configbuttom_3_import_importshablons_1</H2>";
	echo "<TABLE CLASS=samstable>\n";
	echo "<TH>$shablonnew_NewShablonForm_2\n";
	echo "<TH>\n";
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		$row_nick=iconv($this->sams1charset,$this->sams2charset,$row['nick']);
		$this->shablonname[] =$row_nick;
		$this->shablonid[]="$row[name]";
		if($row['clrdate']=="0000-00-00")
			$clrdate="1980-01-01";
		else
			$clrdate=$row['clrdate'];

		echo "<TR><TD><B>$row_nick</B>";
		$QUERY="INSERT INTO shablon ( s_name, s_auth, s_quote, s_period, s_clrdate, s_alldenied) VALUES ('$row_nick', '$row[auth]', '$row[traffic]', '$row[period]', '$clrdate', '$row[alldenied]' ) ";
		$this->DB->samsdb_query($QUERY);

		$QUERY="SELECT s_shablon_id FROM shablon WHERE s_name='$row_nick'";
		$this->DB->samsdb_query_value($QUERY);
	        while($row2=$this->DB->samsdb_fetch_array())
                     $new_shablon_id=$row2['s_shablon_id'];

		$this->DB->samsdb_query("INSERT INTO delaypool ( s_name, s_class, s_agg1, s_agg2, s_ind1, s_ind2) VALUES ('$row_nick', '2', '$row[shablonpool]', '$row[shablonpool]',  '$row[userpool]', '$row[userpool]') ");

		$this->DB->samsdb_query_value("SELECT s_pool_id FROM delaypool WHERE s_name='$row_nick'");
	        while($row2=$this->DB->samsdb_fetch_array())
                     $new_pool_id=$row2['s_pool_id'];

		$this->DB->samsdb_query("INSERT INTO d_link_s ( s_pool_id, s_shablon_id, s_negative) VALUES ('$new_pool_id', '$new_shablon_id', '0') ");

		$this->DB->samsdb_query_value("SELECT s_trange_id FROM timerange WHERE s_days='".$row['days']."' AND s_timestart='".$row['shour'].":".$row['smin'].":00' AND s_timeend='".$row['ehour'].":".$row['emin'].":00'");
	        while($row2=$this->DB->samsdb_fetch_array())
                     $new_trange_id=$row2['s_trange_id'];

		$this->DB->samsdb_query("INSERT INTO sconfig_time ( s_shablon_id, s_trange_id) VALUES ('$new_shablon_id', '$new_trange_id') ");

		echo "<TD>added";
		$this->shabloncount++;
	}
  $this->oldDB->free_samsdb_query();
  echo "</TABLE>";

}

function seturllists()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$DBNAME="$SAMSConf->SAMSDB.";

	$shabloncount=0;
	$this->oldDB->samsdb_query_value("select sconfig.sname, sconfig.set, shablons.name, shablons.nick, redirect.name as gname from sconfig left join shablons on shablons.name=sconfig.sname left join redirect on redirect.filename=sconfig.set ");
	echo "<H2>$configbuttom_3_import_importshablons_1</H2>";
	echo "<TABLE CLASS=samstable>\n";
	echo "<TH>$shablonnew_NewShablonForm_2\n";
	echo "<TH>$redir_addredirectform2\n";
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		$row_nick=iconv($this->sams1charset,$this->sams2charset,$row['nick']);
		$row_gname=iconv($this->sams1charset,$this->sams2charset,$row['gname']);
		if($row['sname'] == $row['name'])
		{
			echo "<TR>\n";
			echo "<TD>$row_nick\n";
			echo "<TD>$row_gname\n";

			$QUERY="SELECT s_shablon_id, s_name FROM shablon WHERE s_name='$row_nick'";
			$num_rows_shablon=$this->DB->samsdb_query_value($QUERY);
			while($row2=$this->DB->samsdb_fetch_array())
			{
				$shablonid=$row2['s_shablon_id'];
			}
			$QUERY="SELECT s_redirect_id, s_name FROM redirect WHERE s_name='$row_gname'";
			$num_rows_redirect=$this->DB->samsdb_query_value($QUERY);
			while($row2=$this->DB->samsdb_fetch_array())
			{
				$redirectid=$row2['s_redirect_id'];
			}
			if($num_rows_shablon>0 && $num_rows_redirect>0)
			{
				$QUERY="INSERT INTO sconfig ( s_shablon_id, s_redirect_id ) VALUES ('$shablonid', '$redirectid') ";
				$num_rows=$this->DB->samsdb_query($QUERY);
			}
		}
	}
	echo "</table>\n";
}

function importsamsusers()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["nametransform"])) $nametransform=$_GET["nametransform"];

	$DBNAME="$SAMSConf->SAMSDB.";

	$groupcount2=0;
	for($i=0;$i<$this->groupcount;$i++)
	{
		$this->DB->samsdb_query_value("SELECT s_group_id FROM sgroup WHERE s_name='".$this->groupname[$i]."' ");
		while($row=$this->DB->samsdb_fetch_array())
		{
			$this->groupid2[$i]=$row['s_group_id'];
			$this->groupcount2++;
		}
  	$this->DB->free_samsdb_query();
	}

	$this->shabloncount2=0;
	for($i=0;$i<$this->shabloncount;$i++)
	{
	$this->DB->samsdb_query_value("SELECT s_shablon_id FROM shablon WHERE s_name='".$this->shablonname[$i]."' ");
	while($row=$this->DB->samsdb_fetch_array())
		{
			$this->shablonid2[$i]=$row['s_shablon_id'];
			$this->shabloncount2++;
		}
  	$this->DB->free_samsdb_query();
	}
	$this->oldDB->samsdb_query_value("SELECT * FROM squidusers ORDER BY nick");
	echo "<H2>$configbuttom_3_import_importsamsusers_1</H2>";
	echo "<TABLE CLASS=samstable>\n";
	echo "<TH>$grouptray_NewGroupForm_4\n";
	echo "<TH>$grouptray_NewGroupForm_8\n";
	echo "<TH>\n";
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		$row_nick=iconv($this->sams1charset,$this->sams2charset,$row['nick']);
		if($nametransform=="tolower")
			$row_nick=strtolower($row_nick);
		if($nametransform=="toupper")
			$row_nick==strtoupper($row_nick);
		$row_family=iconv($this->sams1charset,$this->sams2charset,$row['family']);
		$row_name=iconv($this->sams1charset,$this->sams2charset,$row['name']);
		$row_soname=iconv($this->sams1charset,$this->sams2charset,$row['soname']);

		$sindex=array_search($row['shablon'], $this->shablonid);
		$gindex=array_search($row['group'], $this->groupid);
		if($row['family']!="") 
			$s_family = $row_family;
		else
			$s_family = ".";
			
		if($row['name']!="") 
			$s_name = $row_name;
		else
			$s_name = ".";
		if($row['soname']!="") 
		{
			$s_soname = $row_soname;
		}
		else
		{
			$s_soname = ".";
		}
		if($row['ip']!="") 
			$s_ip = $row['ip'];
		else
			$s_ip = "....";
		if($row['passwd']!="none")
			$passwd=crypt($row['passwd'], substr($row['passwd'], 0, 2));
		else
			$passwd="";
		echo "<TR><TD><B>$row_nick</B><TD> $s_family $s_name <BR>";
		$str="(  s_group_id, s_shablon_id, s_nick, s_family, s_name, s_soname, s_domain, s_quote, s_size, s_hit, s_enabled, s_ip, s_passwd, s_autherrorc, s_autherrort )";
		$values="( '".$this->groupid2[$gindex]."', '".$this->shablonid2[$sindex]."', '$row_nick', '$s_family', '$s_name', '$s_soname', '$row[domain]', '$row[quotes]', '$row[size]', '$row[hit]', '$row[enabled]', '$s_ip', '$passwd',  '$row[autherrorc]', '$row[autherrort]' )";
		$this->DB->samsdb_query("INSERT INTO squiduser $str VALUES $values ");
		echo "<TD>added\n";
	}
  $this->oldDB->free_samsdb_query();
  echo "</TABLE>\n";

}

function ImportProxySettings()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$DBNAME="$SAMSConf->SAMSDB.";

	$shabloncount=0;
	$this->oldDB->samsdb_query_value("select * from squidctrl.sams");
	echo "<H2>$configbuttom_3_import_ImportProxySettings_1</H2>";
	$row=$this->oldDB->samsdb_fetch_array();

$s_description='Imported from sams 1.x'; 
$s_endvalue=$row['endvalue'];
if($s_envalue="")
	$s_endvalue=0;

$s_redirect_to=$row['redirect_to'];
$s_denied_to=$row['denied_to'];
$s_redirector=$row['redirector'];

if($s_redirector!="sams")
	$s_redirector="none";

$s_delaypool=$row['delaypool'];

if($s_delaypool=="Y")
	$s_delaypool=1;
else
	$s_delaypool=0;

$s_auth=$row['auth'];
$s_wbinfopath=$row['wbinfopath'];
$s_separator=quotemeta($row['separator']);
$s_usedomain=$row['ntlmdomain'];

if($s_usedomain=="Y")
	$s_usedomain=1;
else
	$s_usedomain=0;

$s_bigd=$row['bigd'];

if($s_bigd=="S")
	$s_bigd=1;
else if($s_bigd=="Y")
	$s_bigd=0;
else
	$s_bigd=2;

$s_bigu=$row['bigu'];

if($s_bigu=="S")
	$s_bigu=1;
else if($s_bigu=="Y")
	$s_bigu=0;
else
	$s_bigu=2;

$s_sleep=$row['sleep'];
$s_parser=$row['parser_on'];

if($s_parser=="Y")
	$s_parser=1;
else
	$s_parser=0;

$s_parser_time=$row['parser_time'];

$s_count_clean=$row['count_clean'];

if($s_count_clean=="Y")
	$s_count_clean=1;
else
	$s_count_clean=0;

$s_nameencode=$row['nameencode'];

if($s_nameencode=="Y")
	$s_nameencode=1;
else
	$s_nameencode=0;

$s_realsize=$row['realsize'];

$s_checkdns=$row['checkdns'];
if($s_checkdns=="Y")
	$s_checkdns=1;
else
	$s_checkdns=0;

$s_debuglevel=$row['loglevel'];

$s_defaultdomain=$row['defaultdomain'];
$s_squidbase=$row['squidbase'];
$s_udscript=$row['udscript'];
$s_adminaddr=$row['adminaddr'];


$QUERY = "INSERT INTO proxy
 (s_description, s_auth, s_redirector, s_defaultdomain, s_usedomain,
 s_separator, s_bigd, s_bigu, s_nameencode,
 s_redirect_to, s_denied_to, s_checkdns, s_realsize,
 s_sleep, s_parser, s_parser_time, s_count_clean,
 s_wbinfopath, s_delaypool, s_debuglevel, s_udscript,
 s_adminaddr, s_squidbase) 
VALUES ('$s_description', '$s_auth', '$s_redirector', '$s_defaultdomain', '$s_usedomain',
 '$s_separator', '$s_bigd', '$s_bigu', '$s_nameencode',
 '$s_redirect_to', '$s_denied_to', '$s_checkdns', '$s_realsize',
 '$s_sleep', '$s_parser', '$s_parser_time', '$s_count_clean',
 '$s_wbinfopath', '$s_delaypool', '$s_debuglevel', '$s_udscript',
 '$s_adminaddr', '$s_squidbase')";
  $this->DB->samsdb_query($QUERY);

  if($s_auth=="ntlm" && GetAuthParameter("ntlm","enabled")==0)
  {
	$num_rows=$this->DB->samsdb_query("UPDATE auth_param SET s_value='1' WHERE s_auth='ntlm' AND s_param='enabled' ");
  }
  if($s_auth=="ntlm" && GetAuthParameter("ntlm","ntlmdomain")!=$s_defaultdomain)
  {
	$num_rows=$this->DB->samsdb_query("UPDATE auth_param SET s_value='$s_defaultdomain' WHERE s_auth='ntlm' AND s_param='ntlmdomain' ");
  }
  if($s_auth=="ncsa" && GetAuthParameter("ncsa","enabled")==0)
  {
	$num_rows=$this->DB->samsdb_query("UPDATE auth_param SET s_value='1' WHERE s_auth='ncsa' AND s_param='enabled' ");

  }
  if($s_auth=="ip" && GetAuthParameter("ip","enabled")==0)
  {
	$num_rows=$this->DB->samsdb_query("UPDATE auth_param SET s_value='1' WHERE s_auth='ip' AND s_param='enabled' ");

  }

}


function IMPORTUSERS($hostname, $username, $pass)
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

 $this->DB=new SAMSDB();
 $this->oldDB=new CREATESAMSDB("MySQL", "0", $hostname, $username, $pass, "squidctrl", "0");



 $this->sams1charset="koi8-r";
 $QUERY="SELECT * FROM globalsettings";
 $num_rows=$this->oldDB->samsdb_query_value($QUERY);
 $row=$this->oldDB->samsdb_fetch_array();
 $this->sams1charset=$row['lang'];

 $this->oldDB->free_samsdb_query();

 if($this->sams1charset=="UTF8")
	$this->sams1charset="utf-8";
 if($this->sams1charset=="WIN1251")
	$this->sams1charset="windows-1251";
 if($this->sams1charset=="EN")
	$this->sams1charset="utf-8";

 $this->sams2charset="koi8-r";
 $QUERY="SELECT * FROM websettings";
 $num_rows=$this->DB->samsdb_query_value($QUERY);
 $row2=$this->DB->samsdb_fetch_array();
 $this->sams2charset=$row2['s_lang'];

 $this->DB->free_samsdb_query();

 if($this->sams2charset=="UTF8")
	$this->sams2charset="utf-8";
 if($this->sams2charset=="WIN1251")
	$this->sams2charset="windows-1251";
 if($this->sams2charset=="EN")
	$this->sams2charset="utf-8";

}

}



function importdata()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	{       exit;     }

 if(isset($_GET["importusers"])) $importusers=$_GET["importusers"];
 if(isset($_GET["importgroups"])) $importgroups=$_GET["importgroups"];
 if(isset($_GET["importurllists"])) $importurllists=$_GET["importurllists"];
 if(isset($_GET["importproxy"])) $importproxy=$_GET["importproxy"];
 if(isset($_GET["hostname"])) $hostname=$_GET["hostname"];
 if(isset($_GET["username"])) $username=$_GET["username"];
 if(isset($_GET["pass"])) $pass=$_GET["pass"];

   $IMP=new IMPORTUSERS($hostname, $username, $pass);

  if($importusers=="on")
	{
		$IMP->importgroups();
		echo "<BR>";
		$IMP->importtimerange();
		echo "<BR>";
		$IMP->importshablons();
		echo "<BR>";
		$IMP->importsamsusers();
		echo "<BR>";
	}
  if($importurllists=="on")
	{
		$IMP->importurllists();
	}

  if($importusers=="on" && $importurllists=="on")
	{
		$IMP->seturllists();
	}

  if($importproxy=="on")
	{
		$IMP->importproxysettings();
	}

  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");
}

function importdataform()
{
  global $SAMSConf;
  global $USERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);
  PageTop("importdb_48.jpg","$configbuttom_3_import_importdataform_1 ");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/importfromsams1.html\">$documentation</A>");
  print("<P>\n");

  print("<SCRIPT LANGUAGE=JAVASCRIPT>\n");
  print("function EnableUserNameTransform(formname) \n");
  print("{\n");
  print("  var transformenabled=formname.importusers.checked; \n");
  print("  if(transformenabled==true) \n");
  print("    {\n");
  print("  	formname.nametransform.disabled=false; \n");
  print("    }\n");
  print("  if(transformenabled==false) \n");
  print("    {\n");
  print("  	formname.nametransform.disabled=true; \n");
  print("    }\n");
  print("}\n");
  print("</SCRIPT>\n");

			print("<FORM NAME=\"createdatabase\" ACTION=\"main.php\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"importdata\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"configbuttom_3_import.php\">\n");
			print("<TABLE WIDTH=\"90%\">\n");
			print("<TR><TD ALIGN=RIGHT>DB Hostname: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"hostname\" value=\"localhost\">\n");
			print("<TR><TD ALIGN=RIGHT>DB login: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"username\">\n");
			print("<TR><TD ALIGN=RIGHT>DB password: <TD ALIGN=LEFT><INPUT TYPE=\"PASSWORD\" NAME=\"pass\">\n");
			print("<TR><TD ALIGN=RIGHT>$configbuttom_3_import_importdataform_2: <TD ALIGN=LEFT><INPUT TYPE=\"CHECKBOX\" NAME=\"importusers\" CHECKED  onchange=EnableUserNameTransform(createdatabase)>\n");
//			print("<TR><TD ALIGN=RIGHT>$configbuttom_3_import_importdataform_7\n");
			print("<TR><TD ALIGN=RIGHT>$configbuttom_3_import_importdataform_7\n");
			print("<SELECT NAME=\"nametransform\">\n");
			print("<OPTION VALUE=\"nochange\" SELECTED> $configbuttom_3_import_importdataform_8");
			print("<OPTION VALUE=\"tolower\"> $configbuttom_3_import_importdataform_9");
			print("<OPTION VALUE=\"toupper\"> $configbuttom_3_import_importdataform_10");
			print("</SELECT>\n");

			print("<TR><TD ALIGN=RIGHT>$configbuttom_3_import_importdataform_3: <TD ALIGN=LEFT><INPUT TYPE=\"CHECKBOX\" NAME=\"importurllists\" CHECKED>\n");
			print("<TR><TD ALIGN=RIGHT>$configbuttom_3_import_importdataform_4: <TD ALIGN=LEFT><INPUT TYPE=\"CHECKBOX\" NAME=\"importproxy\" CHECKED>\n");
			print("</TABLE>\n");

			printf("<BR><CENTER>");
			print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$configbuttom_3_import_importdataform_5\">\n");
			print("</FORM>\n");
}



function configbuttom_3_import()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       GraphButton("main.php?show=exe&function=importdataform&filename=configbuttom_3_import.php",
	               "basefrm","importdb_32.jpg","importdb_48.jpg","$configbuttom_3_import_importdataform_6");
    }
}

?>
