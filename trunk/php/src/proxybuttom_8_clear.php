<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ClearCounter()
{

  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();

  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;
  
  if(isset($_GET["id"])) $id=$_GET["id"];

  
	$QUERY="UPDATE proxy SET s_endvalue='0' WHERE s_proxy_id='$id'";
	$DB->samsdb_query($QUERY);

}



function proxybuttom_8_clear()
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
       print("function ClearCounter()\n");
       print("{\n");
       print("  value=window.confirm(\"clear counter '$PROXYConf->s_description'? \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=clearcounter&filename=proxybuttom_8_clear.php&&id=$id\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD CLASS=\"samstraytd\">\n");
       print("<IMAGE id=Remove name=\"Remove Proxy\" src=\"$SAMSConf->ICONSET/erase_32.jpg\" \n ");
       print("TITLE=\"clear counter\"  border=0 ");
       print("onclick=ClearCounter(\"$id\") \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/erase_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/erase_32.jpg'\" >\n");
    }

}

?>
