<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function DeleteUser()
{

  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")!=1)
	exit(0);
  
  if(isset($_GET["id"])) $userid=$_GET["id"];

  $query="DELETE FROM squiduser WHERE s_user_id='$userid'";
  $num_rows=$DB->samsdb_query_value($query);
  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php?show=exe&filename=userstray.php&function=userstray\";\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");
}


function userbuttom_9_delete()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

  if($USERConf->ToWebInterfaceAccess("UC")==1)
    {
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function ReloadBaseFrame()\n");
       print("{\n");
       print("   window.location.reload();\n");
       print("}\n");
       print("function DeleteUser(userid)\n");
       print("{\n");
       print("  value=window.confirm(\"$userbuttom_1_delete_userbuttom_9_delete $SquidUSERConf->s_nick? \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=deleteuser&filename=userbuttom_9_delete.php&&id=$SquidUSERConf->s_user_id\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD CLASS=\"samstraytd\" >\n");
       print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/trash_32.jpg\" \n ");
       print("TITLE=\"$user_usertray8\"  border=0 ");
       print("onclick=DeleteUser(\"$SquidUSERConf->s_user_id\") \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/trash_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/trash_32.jpg'\" >\n");
    }

}

?>
