<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function DeleteUser()
{

  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  if(isset($_GET["userid"])) $userid=$_GET["userid"];

  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php?show=exe&function=userstray\";\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");
  
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);
  $result=mysql_query("DELETE FROM squidusers WHERE id=\"$userid\" ");
  UpdateLog("$SAMSConf->adminname","Deleted user $row[nick] ","01");

}



function userbuttom_9_delete($userid)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);

  if($SAMSConf->access==2)
    {
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function ReloadBaseFrame()\n");
       print("{\n");
       print("   window.location.reload();\n");
       print("}\n");
       print("function DeleteUser(userid)\n");
       print("{\n");
       print("  value=window.confirm(\"$userbuttom_1_delete_userbuttom_9_delete $row[nick]? \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=deleteuser&filename=userbuttom_9_delete.php&&userid=$userid\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/trash_32.jpg\" \n ");
       print("TITLE=\"$user_usertray8\"  border=0 ");
       print("onclick=DeleteUser(\"$userid\") \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/trash_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/trash_32.jpg'\" >\n");
    }

}

?>
