<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ClearUserTrafficCounter()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  if(isset($_GET["userid"])) $userid=$_GET["userid"];

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("UPDATE squidusers SET size=\"0\",hit=\"0\" WHERE id=\"$userid\" ");
  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);
  UpdateLog("$SAMSConf->adminname","$userbuttom_5_clear_ClearUserTrafficCounter_1 $row[domain]+$row[nick]","01");
}




function userbuttom_5_clear($userid)
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
       print("function ClearCounter(username,userid)\n");
       print("{\n");
       print("  value=window.confirm(\"$userbuttom_5_clear_userbuttom_5_clear_1 \"+username+\"? \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=clearusertrafficcounter&filename=userbuttom_5_clear.php&userid=$userid\";\n");
	   print("        window.setInterval(\"ReloadBaseFrame()\",500)\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       print("<IMAGE id=Trash name=\"Clear\" src=\"$SAMSConf->ICONSET/erase_32.jpg\" \n ");
       print("TITLE=\"$userbuttom_5_clear_userbuttom_5_clear_1\"  border=0 ");
       print("onclick=ClearCounter(\"$row[nick]\",\"$row[id]\") \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/erase_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/erase_32.jpg'\" >\n");
    }

}




?>
