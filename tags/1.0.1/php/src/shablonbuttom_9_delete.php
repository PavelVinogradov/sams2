<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function DeleteShablon()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  if($sname!="default")
    {
        $result=mysql_query("SELECT * FROM shablons WHERE name=\"$id\" ");
        $row=mysql_fetch_array($result);
        $result=mysql_query("DELETE FROM shablons WHERE name=\"$id\" ");
        $result=mysql_query("DELETE FROM sconfig WHERE sname=\"$id\" ");
        UpdateLog("$SAMSConf->adminname","$shablonbuttom_9_delete_DeleteShablon_1 $row[nick]","01");
    }
  $result=mysql_query("UPDATE squidusers SET shablon=\"default\" WHERE shablon=\"$id\" ");
  print("OK<BR>");

  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=newshablonform&filename=shablonnew.php\";\n");
  print("</SCRIPT> \n");
}


function shablonbuttom_9_delete()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];
  $result=mysql_query("SELECT * FROM shablons WHERE name=\"$id\" ");
  $row=mysql_fetch_array($result);

  if($SAMSConf->access==2)
    {
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function ReloadBaseFrame()\n");
       print("{\n");
       print("   window.location.reload();\n");
       print("}\n");
       print("function DeleteUser()\n");
       print("{\n");
       print("  value=window.confirm(\"$shablonbuttom_9_delete_shablonbuttom_9_delete_1 $row[nick]? \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=deleteshablon&filename=shablonbuttom_9_delete.php&id=$id\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/trash_32.jpg\" \n ");
       print("TITLE=\"$shablonbuttom_9_delete_shablonbuttom_9_delete_1 '$row[nick]'\"  border=0 ");
       print("onclick=DeleteUser() \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/trash_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/trash_32.jpg'\" >\n");
    }

}




?>
