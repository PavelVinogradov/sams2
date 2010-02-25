<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ClearUserTrafficCounter()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")!=1)
	exit(0);
  
  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["enabled"])) $enabled=$_GET["enabled"];

  if($enabled=="on")
	$uenabled=",s_enabled='1'";
  else
	$uenabled="";

  $QUERY="UPDATE squiduser SET s_size='0',s_hit='0'".$uenabled." WHERE s_user_id='$id'";
  $num_rows=$DB->samsdb_query_value($QUERY);
  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php?show=exe&filename=usertray.php&function=usertray&auth=ip&id=$id\";\n");
  if($enabled=="on")
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");

}

function ClearUserTrafficCounterForm()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;

  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($USERConf->ToWebInterfaceAccess("UC")!=1)
	exit(0);
  
  if(isset($_GET["id"])) $id=$_GET["id"];
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);

	PageTop("usertraffic_48.jpg","$usertray_UserTray_1 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT> <BR>$userbuttom_5_clear_userbuttom_5_clear_1");

	print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=UserName value=\"$SquidUSERConf->s_user_id\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"clearusertrafficcounter\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_5_clear.php\">\n");
	print("<BR><INPUT TYPE=\"CHECKBOX\" NAME=\"enabled\" CHECKED> $activate_user\n");
	print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$userbuttom_5_clear_userbuttom_5_clear_1\">\n");
	print("</FORM>\n");

}



function userbuttom_5_clear()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

  if($USERConf->ToWebInterfaceAccess("UC")==1)
    {
	GraphButton("main.php?show=exe&function=clearusertrafficcounterform&filename=userbuttom_5_clear.php&id=$SquidUSERConf->s_user_id","basefrm","erase_32.jpg","erase_48.jpg","$userbuttom_5_clear_userbuttom_5_clear_1");
    }

}




?>
