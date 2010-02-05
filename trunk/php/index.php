<?php

require('./samsclass.php');
require('./dbclass.php');

$SAMSConf=new MAINCONF();
$DB=new SAMSDB();

$result=1;
if($DB->dberror==0)
{
	$QUERY="SELECT count(*) FROM auth_param";
	$result=$DB->samsdb_query_value($QUERY);	
}
if($DB->dberror==1)
{	
	header("Content-type: text/html; charset=\"koi8-r\" ");
	echo "<HTML><HEAD>";
	echo "<link rel=\"STYLESHEET\" type=\"text/css\" href=\"icon/classic/setup.css\">\n";
	echo "</head>\n";
	echo "<body LINK=\"#ffffff\" VLINK=\"#ffffff\">\n";

	echo "<CENTER>";
	echo "<H1>SAMS v.2</H1>";

	echo "<FORM NAME=\"setupform\" ACTION=\"setup.php\">\n";
	echo "<FONT COLOR=RED SIZE=+2>database <B>$SAMSConf->DB_ENGINE</B> not connected</FONT>";
	if($result==FALSE)
	{
		echo "<BR><FONT COLOR=RED SIZE=+1>database structure is not created</FONT>";
	}
	echo "<P><INPUT CLASS=\"button\" TYPE=\"SUBMIT\" value=\"Run setup program >>\">\n";
	echo "</FORM>\n";

	echo "</CENTER>";

	echo "<HTML><HEAD>";
}
else
{
	echo "<html><head>";
	echo "<TITLE>SAMS 2 (SQUID Account Management System).</TITLE>";
	echo "<META  content=\"text/html; charset=koi8-r\" http-equiv=\"Content-Type\">";
	echo "<META HTTP-EQUIV=\"expires\" CONTENT=\"THU, 01 Jan 1970 00:00:01 GMT\">";
	echo "<META HTTP-EQUIV=\"pragma\" CONTENT=\"no-cache\">";

	echo "<script>";
	echo "function op() { //This function is used with folders that do not open pages themselves. See online docs.";
	echo "}";
	echo "</script>";
	echo "</head>";

	echo "<FRAMESET frameborder=\"0\" framespacing=\"0\" cols=\"25%,*,0\">";
	echo "  <FRAME src=\"lframe.php?value=start\" name=\"lframe\" >";
	echo "  <FRAMESET frameborder=\"0\" framespacing=\"0\" rows=\"*,75\">";
	echo "    <FRAME SRC=\"main.php?show=exe&function=userdoc&value=start\" name=\"basefrm\">";
	echo "    <FRAME SRC=\"tray.php?show=exe&filename=admintray.php&function=admintray&value=start\" name=\"tray\">";
#echo "    <FRAME SRC=\"\" name=\"hidden\" >";
	echo "  </FRAMESET> ";
	echo "</FRAMESET> ";
	echo "</HTML>";
}



?>