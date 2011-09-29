<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function DeletePool()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	{       exit;     }
  
  if($sname!="default")
    {
        $DB->samsdb_query("DELETE FROM delaypool WHERE s_pool_id='$id' ");
        $DB->samsdb_query("DELETE FROM d_link_s WHERE s_pool_id='$id' ");
        $DB->samsdb_query("DELETE FROM d_link_t WHERE s_pool_id='$id' ");
        $DB->samsdb_query("DELETE FROM d_link_r WHERE s_pool_id='$id' ");
    }

  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&filename=pooltray.php&function=addpoolform\";\n");
  print("</SCRIPT> \n");
}


function poolbuttom_9_delete()
{
  global $SAMSConf;
  global $POOLConf;
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
       print("function DeletePool()\n");
       print("{\n");
       print("  value=window.confirm(\"Delete delay pool $POOLConf->s_name? \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=deletepool&filename=poolbuttom_9_delete.php&id=$POOLConf->s_pool_id\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD CLASS=\"samstraytd\">\n");
       print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/trash_32.jpg\" \n ");
       print("TITLE=\"$lframe_sams_DelayPools_DeleteButton '$POOLConf->s_name'\"  border=0 ");
       print("onclick=DeletePool() \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/trash_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/trash_32.jpg'\" >\n");
    }

}




?>
