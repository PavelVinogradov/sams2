<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function DeleteShablon()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;
  
  if($sname!="default")
    {
        $num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE s_shablon_id='$id' ");
	$DB->free_samsdb_query();
	if($num_rows==0)
		{
        		$DB->samsdb_query("DELETE FROM shablon WHERE s_shablon_id='$id' ");
		}
	else
		{
			PageTop("denied.gif","<FONT SIZE=+3 COLOR=\"RED\"> $shablonbuttom_9_delete_DeleteShablon_3 </FONT>");
			echo "<FONT  SIZE=+1>$shablonbuttom_9_delete_DeleteShablon_1 $num_rows $shablonbuttom_9_delete_DeleteShablon_2</FONT>";
			exit(0);
		}
    }
  print("OK<BR>");

  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=newshablonform&filename=shablonnew.php\";\n");
  print("</SCRIPT> \n");
}


function shablonbuttom_9_delete()
{
  global $SAMSConf;
  global $SHABLONConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function ReloadBaseFrame()\n");
       print("{\n");
       print("   window.location.reload();\n");
       print("}\n");
       print("function DeleteUser()\n");
       print("{\n");
       print("  value=window.confirm(\"$shablonbuttom_9_delete_shablonbuttom_9_delete_1 $SHABLONConf->s_name? \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=deleteshablon&filename=shablonbuttom_9_delete.php&id=$SHABLONConf->s_shablon_id\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD CLASS=\"samstraytd\">\n");
       print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/trash_32.jpg\" \n ");
       print("TITLE=\"$shablonbuttom_9_delete_shablonbuttom_9_delete_1 '$SHABLONConf->s_name'\"  border=0 ");
       print("onclick=DeleteUser() \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/trash_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/trash_32.jpg'\" >\n");
    }

}




?>
