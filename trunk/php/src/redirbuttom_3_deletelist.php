<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function redirbuttom_3_deletelist()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  $num_rows=$DB->samsdb_query_value("SELECT * FROM redirect WHERE s_redirect_id='$id' ");
  $row=$DB->samsdb_fetch_array();

  if($USERConf->ToWebInterfaceAccess("LC")==1 )
    {
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function DeleteList(id)\n");
       print("{\n");
       print("  value=window.confirm(\"$redirbuttom_3_deletelist_redirbuttom_3_deletelist_1 $row[s_name] \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=deletelist&filename=redirlisttray.php&id=$id\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD CLASS=\"samstraytd\">\n");
       print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/trash_32.jpg\" \n ");
       print("TITLE=\"$redirbuttom_3_deletelist_redirbuttom_3_deletelist_1\"  border=0 ");
       print("onclick=DeleteList(\"$id\") \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/trash_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/trash_32.jpg'\" >\n");
    }

}




?>
