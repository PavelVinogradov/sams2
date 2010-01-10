<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UpdateGroup()
{
  global $SAMSConf;
  $SAMSConf->access=UserAccess();
  if($SAMSConf->access!=2)     {       exit;     }

  if(isset($_GET["id"])) $sname=$_GET["id"];
  if(isset($_GET["nick"])) $nick=$_GET["nick"];

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("DELETE FROM sconfig WHERE sname=\"$sname\" ");

  $result=mysql_query("UPDATE groups SET nick=\"$nick\"  WHERE name=\"$sname\" ");
  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");

}



function UpdateGroupForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  $result2=mysql_query("SELECT * FROM groups WHERE groups.name=\"$id\" ");
  $row2=mysql_fetch_array($result2);
  PageTop("shablon.jpg","$groupbuttom_0_prop_UpdateGroupForm_1 <FONT COLOR=\"BLUE\">$row2[nick]</FONT>");

  print("<FORM NAME=\"UPDATEGROUP\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"updategroup\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"groupbuttom_0_prop.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$id\">\n");
  
  print("<TABLE  BORDER=0>\n");
  print("<TR>\n");
  print("<TD><B>$groupbuttom_0_prop_UpdateGroupForm_2: </B>\n" );
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"nick\" SIZE=30 VALUE=$row2[nick]> \n" );
  print("</TABLE>\n");
/* calendar */  
  
  
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$shablonbuttom_1_prop_UpdateShablonForm_7\">\n");
  print("</FORM>\n");


}


function groupbuttom_0_prop($id)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=updategroupform&filename=groupbuttom_0_prop.php&id=$id",
	               "basefrm","config_32.jpg","config_48.jpg","$groupbuttom_0_prop_groupbuttom_0_prop_1");
    }
}

?>
