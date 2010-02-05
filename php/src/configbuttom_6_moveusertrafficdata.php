<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function MoveUserTrafficData()
{
  global $SAMSConf;
  global $USERConf;
  global $DATE;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1)
	{      exit;     }

  $DB=new SAMSDB();
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();

	if(isset($_GET["userfrom"])) $userfrom=$_GET["userfrom"];
	if(isset($_GET["userto"])) $userto=$_GET["userto"];

	$thisdate=strftime("%Y-%m-%d");
	$smdate=strftime("%Y")."-".strftime("%m")."-01";
	$stime="0:00:00";
	$etime=time();

	$QUERY="UPDATE squidcache SET s_user='$userto' WHERE s_user='$userfrom' AND  s_date>='$sdate' AND s_date<='$edate' ";
	$num_rows=$DB->samsdb_query($QUERY);

	PageTop("backup_48.jpg","Data about the traffic of user $userfrom is transferred to user $userto");

}


function MoveUserTrafficDataForm()
{
	global $SAMSConf;
	global $USERConf;
	require("reportsclass.php");
	$dateselect=new DATESELECT("","");
	
	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);
	$DB=new SAMSDB();

	if($USERConf->ToWebInterfaceAccess("C")!=1 )
		exit;
  
	PageTop("switchuser_48.jpg","$MoveUserTrafficDataForm_1");
	print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
	print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/samsbackup.html\">$documentation</A>");
	print("<P>\n");

	print("<FORM NAME=\"BACKUP\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"moveusertrafficdata\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"configbuttom_6_moveusertrafficdata.php\">\n");

	$dateselect->SetPeriod();

	print("<TABLE WIDTH=70%>\n");
	print("<TR><TD WIDTH=20%>$MoveUserTrafficDataForm_2\n");
	print("<TD WIDTH=35%><SELECT NAME=\"userfrom\">\n");
	$QUERY="SELECT s_user FROM squidcache GROUP BY s_user ORDER BY s_user";
	$num_rows=$DB->samsdb_query_value($QUERY);
	while($row=$DB->samsdb_fetch_array())
	{
		print("<OPTION VALUE=\"".$row['s_user']."\"> ".$row['s_user']."\n");

	}
	print("</SELECT>\n");

	print("<TD WIDTH=10%> $MoveUserTrafficDataForm_3\n");
	print("<TD WIDTH=35%><SELECT NAME=\"userto\">\n");
	$QUERY="SELECT * FROM squiduser ORDER BY s_nick";
	$num_rows=$DB->samsdb_query_value($QUERY);
	while($row=$DB->samsdb_fetch_array())
	{
		print("<OPTION VALUE=\"".$row['s_nick']."\"> ".$row['s_nick']."\n");

	}
	print("</SELECT>\n");
	print("</TABLE>\n");

	print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$MoveUserTrafficDataForm_4\">\n");
	print("</FORM>\n");

}

//To move the data about traffic of the user
function configbuttom_6_moveusertrafficdata()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("C")==1 )
	{
		GraphButton("main.php?show=exe&function=moveusertrafficdataform&filename=configbuttom_6_moveusertrafficdata.php","basefrm","switchuser_32.jpg","switchuser_48.jpg","To move the data about traffic of the user");
	}

}




?>
