<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

/****************************************************************/
function NotEmptyGroupWarning($groupnick)
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
   
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("warning.jpg","$groupbuttom_9_delete_NotEmptyGroupWarning_1");
  print("<B>$groupbuttom_9_delete_NotEmptyGroupWarning_2</B>");
}


function DeleteGroup()
{
  global $SAMSConf;
  $DB=new SAMSDB("$SAMSConf->DBNAME", "0", $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

   if($SAMSConf->access!=2)     {       exit;     }
  $num_rows=$DB->samsdb_query_value("SELECT count(*) FROM squiduser WHERE s_group_id='$id' ");
  $row=$DB->samsdb_fetch_array();
  $count=$row[0];
  $DB->free_samsdb_query();
  if($count>0)
	{
        NotEmptyGroupWarning($id);
	}
  else
     {
        $num_rows=$DB->samsdb_query("DELETE FROM sgroup WHERE s_group_id='$id' ");
//	if($result!=FALSE)
//                   UpdateLog("$SAMSConf->adminname","Deleted group  $row[nick] ","02");

        print("<SCRIPT>\n");
        print("  parent.tray.location.href=\"tray.php?show=exe&function=userstray\";\n");
        print("  parent.lframe.location.href=\"lframe.php\"; \n");
        print("</SCRIPT> \n");

     }
}



function groupbuttom_9_delete()
{
  global $SAMSConf;
  $DB=new SAMSDB("$SAMSConf->DBNAME", "0", $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
 if(isset($_GET["id"])) $id=$_GET["id"];

  
  $num_rows=$DB->samsdb_query_value("SELECT * FROM sgroup WHERE s_group_id='$id' ");
  $row=$DB->samsdb_fetch_array();

      print("<SCRIPT language=JAVASCRIPT>\n");
      print("function DeleteUser(username,userid)\n");
      print("{\n");
      print("  value=window.confirm(\"$groupbuttom_9_delete_groupbuttom_9_delete_1 $row[s_name]? \" );\n");
      print("  if(value==true) \n");
      print("     {\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=deletegroup&filename=groupbuttom_9_delete.php&id=$id\";\n");
      print("     }\n");
      print("}\n");
      print("</SCRIPT> \n");
  if($SAMSConf->access==2)
    {
      print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
      print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/trash_32.jpg\" \n ");
      print("TITLE=\"$groupbuttom_9_delete_groupbuttom_9_delete_2\"  border=0 ");
      print("onclick=DeleteUser(\"nick\",\"id\") \n");
      print("onmouseover=\"this.src='$SAMSConf->ICONSET/trash_48.jpg'\" \n");
      print("onmouseout= \"this.src='$SAMSConf->ICONSET/trash_32.jpg'\" >\n");
    }

}




?>
