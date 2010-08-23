<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function DeleteTRange()
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
        $DB->samsdb_query("DELETE FROM timerange WHERE s_trange_id='$id' ");
    }

  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&filename=trangetray.php&function=addtrangeform\";\n");
  print("</SCRIPT> \n");
}


function trangebuttom_9_delete()
{
  global $SAMSConf;
  global $TRANGEConf;
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
       print("  value=window.confirm(\"$trangebuttom_9_delete_trangebuttom_9_delete_2 $TRANGEConf->s_name? \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=deletetrange&filename=trangebuttom_9_delete.php&id=$TRANGEConf->s_trange_id\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD CLASS=\"samstraytd\">\n");
       print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/trash_32.jpg\" \n ");
       print("TITLE=\"$trangebuttom_9_delete_trangebuttom_9_delete_1 '$TRANGEConf->s_name'\"  border=0 ");
       print("onclick=DeleteUser() \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/trash_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/trash_32.jpg'\" >\n");
    }

}




?>
