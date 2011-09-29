<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UpdateGroup()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();

  if($USERConf->ToWebInterfaceAccess("C")!=1)
	exit;

  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["nick"])) $nick=$_GET["nick"];

  $num_rows=$DB->samsdb_query("UPDATE sgroup SET s_name='$nick'  WHERE s_group_id='$id' ");

  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("        parent.tray.location.href=\"tray.php?show=exe&filename=grouptray.php&function=grouptray&id=$id\" ");
  print("</SCRIPT> \n");

}



function UpdateGroupForm()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  if($USERConf->ToWebInterfaceAccess("C")!=1)
	exit;

  $num_rows=$DB->samsdb_query_value("SELECT * FROM sgroup WHERE s_group_id='$id' ");
  $row=$DB->samsdb_fetch_array();
  PageTop("shablon.jpg","$groupbuttom_0_prop_UpdateGroupForm_1 <FONT COLOR=\"BLUE\">$row[s_name]</FONT>");

  print("<FORM NAME=\"UPDATEGROUP\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"updategroup\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"groupbuttom_0_prop.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$id\">\n");
  
  print("<TABLE  BORDER=0>\n");
  print("<TR>\n");
  print("<TD><B>$groupbuttom_0_prop_UpdateGroupForm_2: </B>\n" );
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"nick\" SIZE=30 VALUE=\"$row[s_name]\"> \n" );
  print("</TABLE>\n");
/* calendar */  
  
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$shablonbuttom_1_prop_UpdateShablonForm_7\">\n");
  print("</FORM>\n");
}


function groupbuttom_0_prop()
{
  global $SAMSConf;
  global $USERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];
  
  if($USERConf->ToWebInterfaceAccess("C")==1)
  {
       GraphButton("main.php?show=exe&function=updategroupform&filename=groupbuttom_0_prop.php&id=$id",
	               "basefrm","config_32.jpg","config_48.jpg","$groupbuttom_0_prop_groupbuttom_0_prop_1");
    }
}

?>
