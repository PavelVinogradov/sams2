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
  global $USERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
   
  if($USERConf->ToWebInterfaceAccess("C")!=1)
	exit;
  
  PageTop("warning.jpg","$groupbuttom_9_delete_NotEmptyGroupWarning_1");
  print("<B>$groupbuttom_9_delete_NotEmptyGroupWarning_2</B>");
}


function DeleteGroup()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  if($USERConf->ToWebInterfaceAccess("C")!=1)
	exit;

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
	$num_rows=$DB->samsdb_query_value("SELECT s_name FROM sgroup WHERE s_group_id='$id' ");
	$row=$DB->samsdb_fetch_array();
	$gname=$row['s_name'];
	$DB->free_samsdb_query();

	$QUERY="DELETE FROM sgroup WHERE s_group_id='$id' ";
        $num_rows=$DB->samsdb_query($QUERY);

	$QUERY="delete from auth_param where (s_param='adldgroup' OR s_param='ntlmgroup' OR s_param='ldapgroup') AND s_value='$gname'";
        $num_rows=$DB->samsdb_query($QUERY);

        print("<SCRIPT>\n");
        print("  parent.tray.location.href=\"tray.php?show=exe&function=userstray\";\n");
        print("  parent.lframe.location.href=\"lframe.php\"; \n");
        print("</SCRIPT> \n");

  }
}



function groupbuttom_9_delete()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
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
  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
      print("<TD CLASS=\"samstraytd\">\n");
      print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/trash_32.jpg\" \n ");
      print("TITLE=\"$groupbuttom_9_delete_groupbuttom_9_delete_2\"  border=0 ");
      print("onclick=DeleteUser(\"nick\",\"id\") \n");
      print("onmouseover=\"this.src='$SAMSConf->ICONSET/trash_48.jpg'\" \n");
      print("onmouseout= \"this.src='$SAMSConf->ICONSET/trash_32.jpg'\" >\n");
    }

}




?>
