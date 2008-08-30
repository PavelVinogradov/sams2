<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function DeleteTRange()
{
  global $SAMSConf;
  $DB=new SAMSDB($SAMSConf->DB_ENGINE, $SAMSConf->ODBC, $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB, $SAMSConf->PDO);
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  if($SAMSConf->access!=2 && $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "C")!=1)
	{       exit;     }
  
  if($sname!="default")
    {
        //$DB->samsdb_query_value("SELECT * FROM shablon WHERE name=\"$id\" ");
        //$row=$DB->samsdb_fetch_array();
        $DB->samsdb_query("DELETE FROM timerange WHERE s_trange_id='$id' ");
        //$result=mysql_query("DELETE FROM sconfig WHERE sname=\"$id\" ");
        //UpdateLog("$SAMSConf->adminname","$shablonbuttom_9_delete_DeleteShablon_1 $row[nick]","01");
    }
  //$result=mysql_query("UPDATE squidusers SET shablon=\"default\" WHERE shablon=\"$id\" ");
  print("OK<BR>");

  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("        parent.tray.location.href=\"tray.php\";\n");
  print("</SCRIPT> \n");
}


function trangebuttom_9_delete()
{
  global $SAMSConf;
  global $TRANGEConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);


  if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "C")==1)
    {
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function ReloadBaseFrame()\n");
       print("{\n");
       print("   window.location.reload();\n");
       print("}\n");
       print("function DeleteUser()\n");
       print("{\n");
       print("  value=window.confirm(\"Delete Time Range $TRANGEConf->s_name? \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=deletetrange&filename=trangebuttom_9_delete.php&id=$TRANGEConf->s_trange_id\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/trash_32.jpg\" \n ");
       print("TITLE=\"Delete Time Range '$TRANGEConf->s_name'\"  border=0 ");
       print("onclick=DeleteUser() \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/trash_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/trash_32.jpg'\" >\n");
    }

}




?>
