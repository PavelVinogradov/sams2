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
  $DB=new SAMSDB();

  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;
  
  if(isset($_GET["id"])) $id=$_GET["id"];

  
	$QUERY="DELETE FROM proxy WHERE s_proxy_id='$id'";
	$DB->samsdb_query($QUERY);

	print("<SCRIPT>\n");
	print("  parent.lframe.location.href=\"lframe.php\";\n");
	print("  parent.basefrm.location.href = \"main.php?show=exe&function=cacheform&filename=squidtray.php&function=squidtray\"; \n");
	print("</SCRIPT> \n");

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
       print("function DeleteProxy()\n");
       print("{\n");
       print("  value=window.confirm(\"$proxybuttom_9_delete_proxybuttom_9_delete_1 '$PROXYConf->s_description'? \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=deleteproxy&filename=proxybuttom_9_delete.php&&id=$id\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD CLASS=\"samstraytd\">\n");
       print("<IMAGE id=Remove name=\"Remove Proxy\" src=\"$SAMSConf->ICONSET/trash_32.jpg\" \n ");
       print("TITLE=\"$proxybuttom_9_delete_proxybuttom_9_delete_1\"  border=0 ");
       print("onclick=DeleteProxy(\"$id\") \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/trash_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/trash_32.jpg'\" >\n");
    }

}

?>
