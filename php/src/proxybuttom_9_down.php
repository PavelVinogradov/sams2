<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function shutdown_proxy()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  if($USERConf->ToWebInterfaceAccess("C")==1 )
  {
	$DB=new SAMSDB();
	PageTop("reconfig_48.jpg","Send command '$SAMSConf->SHUTDOWN' to proxy server");

	$QUERY="INSERT INTO reconfig (s_proxy_id, s_service, s_action)  VALUES('$id', 'squid', 'shutdown'); ";
	$result=$DB->samsdb_query($QUERY);

  } 
}

function proxybuttom_9_down()
{
  global $SAMSConf;
  global $USERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];


  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function ReloadBaseFrame()\n");
       print("{\n");
       print("   window.location.reload();\n");
       print("}\n");
       print("function StopProxy()\n");
       print("{\n");

       print("  value=window.confirm(\" $proxybuttom_1_down_proxybuttom_1_down_1 \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=shutdown_proxy&filename=proxybuttom_9_down.php&id=$id\";\n");
       print("     }\n");
				   
       print("}\n");
       print("</SCRIPT> \n");
       
       print("<TD CLASS=\"samstraytd\">\n");
       print("<IMAGE id=Shutdown name=\"Shutdown\" src=\"$SAMSConf->ICONSET/shutdown_32.jpg\" \n ");
       print("TITLE=\"$proxybuttom_1_down_proxybuttom_1_down_2\"  border=0 ");
       print("onclick=StopProxy() \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/shutdown_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/shutdown_32.jpg'\" >\n");
    }

}




?>
