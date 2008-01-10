<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
class IMPORTUSERS
{
  var $groupname = array();
  
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

function importurllists()
{
  global $SAMSConf;

	if($SAMSConf->access!=2)     {       exit;     }
	$shabloncount=0;
	$this->oldDB->samsdb_query_value("SELECT * FROM squidctrl.redirect ");
	while($row=$this->oldDB->samsdb_fetch_array())
		{
		echo "INSERT INTO redirect (s_name,s_type) VALUES( '$row[name]', '$row[type]')<BR>";
		$this->DB->samsdb_query("INSERT INTO samsdb.redirect (s_name,s_type) VALUES( '$row[name]', '$row[type]')");
		}
	$this->oldDB->free_samsdb_query();
	$i=0;
	$this->urllistcount=$this->DB->samsdb_query_value("SELECT * FROM samsdb.redirect ");
	while($row=$this->DB->samsdb_fetch_array())
		{
			print("$i:  ".$row['s_name']."<BR>");
			$this->urllistname[$i]=$row['s_name'];
			$this->urllistid2[$i]=$row['s_redirect_id'];
			//print("$row[nick]: $clrdate <BR>");
			$i++;
		}
	$this->DB->free_samsdb_query();
//	for($i=0; $i<$this->urllistcount; $i++)
//	{
		$this->oldDB->samsdb_query_value("SELECT urls.*,redirect.name as rname FROM squidctrl.urls LEFT JOIN squidctrl.redirect ON urls.type=redirect.filename ");
		while($row=$this->oldDB->samsdb_fetch_array())
			{
			$index=array_search($row['rname'], $this->urllistname);
//			echo "INSERT INTO url (  s_url_id , s_redirect_id , s_url  ";
			echo "$i from $this->urllistcount: INSERT INTO url ( s_redirect_id , s_url ) VALUES ( '".$this->urllistid2[$index]."', '$row[url]' ) <BR>";
			$this->DB->samsdb_query("INSERT INTO samsdb.url ( s_redirect_id , s_url ) VALUES ( '".$this->urllistid2[$index]."', '$row[url]' )");
			}
		$this->oldDB->free_samsdb_query();

//	}

}


function importgroups()
{
  global $SAMSConf;

	if($SAMSConf->access!=2)     {       exit;     }
	$this->groupcount=0;
	$this->oldDB->samsdb_query_value("SELECT * FROM groups ");
	while($row=$this->oldDB->samsdb_fetch_array())
	{
		$this->groupname[] ="$row[nick]";
		$this->groupid[]="$row[name]";
		print($this->groupcount.":  ".$this->groupname[$this->groupcount]." ".$this->groupid[$this->groupcount]."<BR>");
		if($row['nick']!="Administrators"&&$row['nick']!="Users")
		{
			$this->DB->samsdb_query("INSERT INTO samsdb.sgroup ( s_name ) VALUES ('".$this->groupname[$this->groupcount]."') ");
		}
		$this->groupcount++;
	}
  $this->oldDB->free_samsdb_query();
}

function importshablons()
{
  global $SAMSConf;

	if($SAMSConf->access!=2)     {       exit;     }
	$shabloncount=0;
	$this->oldDB->samsdb_query_value("SELECT * FROM shablons ");
	while($row=$this->oldDB->samsdb_fetch_array())
		{
		$this->shablonname[] ="$row[nick]";
		$this->shablonid[]="$row[name]";
		print("$this->shabloncount:  ".$this->shablonname[$this->shabloncount]." ".$this->shablonid[$this->shabloncount]."<BR>");
		if($row['clrdate']=="0000-00-00")
			$clrdate="1980-01-01";
		else
			$clrdate=$row['clrdate'];
		//print("$row[nick]: $clrdate <BR>");
		if($row['name']!="default")
			{
			$this->DB->samsdb_query("INSERT INTO samsdb.shablon ( s_name, s_shablonpool, s_userpool, s_auth, s_quote, s_period, s_clrdate, s_alldenied) VALUES ('$row[nick]', '$row[shablonpool]', '$row[userpool]', '$row[auth]', '$row[traffic]', '$row[period]', '$clrdate', '$row[alldenied]' ) ");
			}
		$this->shabloncount++;
		}
  $this->oldDB->free_samsdb_query();

}

function importsamsusers()
{
  global $SAMSConf;

	if($SAMSConf->access!=2)     {       exit;     }
	$groupcount2=0;
	for($i=0;$i<$this->groupcount;$i++)
	{
	$this->DB->samsdb_query_value("SELECT s_group_id FROM samsdb.sgroup WHERE s_name='".$this->groupname[$i]."' ");
	while($row=$this->DB->samsdb_fetch_array())
		{
			print("$i:  ".$row['s_group_id']." ".$this->groupname[$i]."<BR>");
			$this->groupid2[$i]=$row['s_group_id'];
			//print("$row[nick]: $clrdate <BR>");
			$this->groupcount2++;
		}
  	$this->DB->free_samsdb_query();
	}

echo "===== count=".$this->shabloncount."<BR>";
	$this->shabloncount2=0;
	for($i=0;$i<$this->shabloncount;$i++)
	{
	$this->DB->samsdb_query_value("SELECT s_shablon_id FROM samsdb.shablon WHERE s_name='".$this->shablonname[$i]."' ");
	while($row=$this->DB->samsdb_fetch_array())
		{
			print("$i:  ".$row['s_shablon_id']." ".$this->shablonname[$i]."<BR>");
			$this->shablonid2[$i]=$row['s_shablon_id'];
			//print("$row[nick]: $clrdate <BR>");
			$this->shabloncount2++;
		}
  	$this->DB->free_samsdb_query();
	}
echo "===== <BR>";

	$this->oldDB->samsdb_query_value("SELECT * FROM squidusers ORDER BY nick");
	while($row=$this->oldDB->samsdb_fetch_array())
		{
			$sindex=array_search($row['shablon'], $this->shablonid);
			$gindex=array_search($row['group'], $this->groupid);
echo "$row[group] ,$gindex<BR>";
//			print("$count:  ".$row['nick']." ".$this->shablonname[$sindex]."=".$this->shablonid2[$sindex]." ".$this->groupname[$gindex]."=".$this->groupid2[$gindex]."<BR>");
			//print("$row[nick]: $clrdate <BR>");
			if($row['family']!="") 
				$s_family = $row['family'];
			else
				$s_family = ".";
				
			if($row['name']!="") 
				$s_name = $row['name'];
			else
				$s_name = ".";
			if($row['soname']!="") 
				$s_soname = $row['soname'];
			else
				$s_soname = ".";
			if($row['ip']!="") 
				$s_ip = $row['ip'];
			else
				$s_ip = "....";
			$str="(  s_group_id, s_shablon_id, s_nick, s_family, s_name, s_soname, s_domain, s_quote, s_size, s_hit, s_enabled, s_ip, s_passwd, s_gauditor, s_autherrorc, s_autherrort )";
			$values="( '".$this->groupid2[$gindex]."', '".$this->shablonid2[$sindex]."', '$row[nick]', '$s_family', '$s_name', '$s_soname', '$row[domain]', '$row[quotes]', '$row[size]', '$row[hit]', '$row[enabled]', '$s_ip', '$row[passwd]', '$row[gauditor]',  '$row[autherrorc]', '$row[autherrort]' )";
echo "INSERT INTO squiduser $str VALUES $values<BR>";
			$this->DB->samsdb_query("INSERT INTO samsdb.squiduser $str VALUES $values ");
			$count++;
		}
  $this->oldDB->free_samsdb_query();

}


function IMPORTUSERS($hostname, $username, $pass)
{
  global $SAMSConf;
 if($SAMSConf->access!=2)     {       exit;     }
 $this->DB=new SAMSDB($SAMSConf->DBNAME, $SAMSConf->ODBC, $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);
 $this->oldDB=new SAMSDB("MySQL", $SAMSConf->ODBC, $hostname, $username, $pass, "squidctrl");
// $this->importgroups();
//echo "<BR>";
// $this->importshablons();
//echo "<BR>";
// $this->importsamsusers();
//echo "<BR>";
}

}

function importdata()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
 if(isset($_GET["importusers"])) $importusers=$_GET["importusers"];
 if(isset($_GET["importgroups"])) $importgroups=$_GET["importgroups"];
 if(isset($_GET["importurllists"])) $importurllists=$_GET["importurllists"];
 if(isset($_GET["hostname"])) $hostname=$_GET["hostname"];
 if(isset($_GET["username"])) $username=$_GET["username"];
 if(isset($_GET["pass"])) $pass=$_GET["pass"];
  
// if($SAMSConf->access!=2)     {       exit;     }
//  $DB=new SAMSDB("MySQL", $SAMSConf->ODBC, $hostname, $username, $pass, "squidctrl");
//  if($importgroups=="on")
//	importgroups($DB);
//  if($importshablons=="on")
//	importshablons($DB);
   $IMP=new IMPORTUSERS($hostname, $username, $pass);
  if($importusers=="on")
	{
	$IMP->importgroups();
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
  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");
}

function importdataform()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("shablon.jpg","Import database from SAMS 1 ");

			print("<H2 ALIGN=\"CENTER\">Import  data</H2>");
			print("<FORM NAME=\"createdatabase\" ACTION=\"main.php\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"importdata\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"configbuttom_3_import.php\">\n");
			print("<TABLE WIDTH=\"90%\">\n");
			print("<TR><TD ALIGN=RIGHT>DB Hostname: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"hostname\" value=\"localhost\">\n");
			print("<TR><TD ALIGN=RIGHT>DB login: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"username\" value=\"$dbadmin\">\n");
			print("<TR><TD ALIGN=RIGHT>DB password: <TD ALIGN=LEFT><INPUT TYPE=\"PASSWORD\" NAME=\"pass\">\n");
//			print("<TR><TD ALIGN=RIGHT>SAMS 1 group : <TD ALIGN=LEFT><INPUT TYPE=\"CHECKBOX\" NAME=\"importgroups\">\n");
//			print("<TR><TD ALIGN=RIGHT>SAMS 1 shablon : <TD ALIGN=LEFT><INPUT TYPE=\"CHECKBOX\" NAME=\"importshablons\">\n");
			print("<TR><TD ALIGN=RIGHT>SAMS 1 templates, groups, users : <TD ALIGN=LEFT><INPUT TYPE=\"CHECKBOX\" NAME=\"importusers\">\n");
			print("<TR><TD ALIGN=RIGHT>SAMS 1 url access lists : <TD ALIGN=LEFT><INPUT TYPE=\"CHECKBOX\" NAME=\"importurllists\">\n");
			print("</TABLE>\n");

			printf("<BR><CENTER>");
			print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Import data\">\n");
			print("</FORM>\n");
}



function configbuttom_3_import()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=importdataform&filename=configbuttom_3_import.php",
	               "basefrm","config_32.jpg","config_48.jpg","  import data from sams ver.1 database  ");
    }
}

?>
