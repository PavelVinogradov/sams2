<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function DeleteProxy()
{

  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB("$SAMSConf->DB_ENGINE", $SAMSConf->ODBC, $SAMSConf->MYSQLHOSTNAME, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB, $SAMSConf->ODBCSOURCE);
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;
  
  if(isset($_GET["id"])) $id=$_GET["id"];

  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php?show=exe&function=userstray\";\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");
  
  $num_rows=$DB->samsdb_query_value("SELECT * FROM proxy WHERE s_proxy_id='$id' ");
  $row=$DB->samsdb_fetch_array();
  $DB->samsdb_query("DELETE FROM proxy WHERE s_proxy_id='$id' ");
  //UpdateLog("$SAMSConf->adminname","Deleted user $row[nick] ","01");

}



function proxybuttom_9_delete()
{
  global $SAMSConf;
  global $PROXYConf;
  global $USERConf;
  if(isset($_GET["id"])) $id=$_GET["id"];
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function ReloadBaseFrame()\n");
       print("{\n");
       print("   window.location.reload();\n");
       print("}\n");
       print("function DeleteUser(userid)\n");
       print("{\n");
       print("  value=window.confirm(\"Remove proxy $PROXYConf->s_description? \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=deleteproxy&filename=proxybuttom_9_delete.php&&id=$id\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD CLASS=\"samstraytd\">\n");
       print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/trash_32.jpg\" \n ");
       print("TITLE=\"remove proxy\"  border=0 ");
       print("onclick=DeleteUser(\"$id\") \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/trash_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/trash_32.jpg'\" >\n");
    }

}

?>
