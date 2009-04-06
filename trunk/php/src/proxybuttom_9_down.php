<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function DownSquid()
{
  global $SAMSConf;
  global $USERConf;
 
 if(isset($_GET["cache"])) $cache=$_GET["cache"];
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
       PageTop("reconfig_48.jpg","Shutdown proxy server");
       
       if($SAMSConf->PROXYCOUNT>1)
         {
            for($i=0;$i<$SAMSConf->PROXYCOUNT;$i++)
	       {
	           if($cache[$i]=="on")
	              {
	                 //echo "remove cache $row[id] $row[description]<BR>";
                         $result=mysql_query("INSERT INTO reconfig SET number=\"$i\",service=\"squid\",action=\"shutdown\" ");
                      }
                }
          
         }
      }
}

function shutdown_proxy()
{
  global $SAMSConf;
  global $USERConf;
  if(isset($_GET["id"])) $id=$_GET["id"];
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1 )
  {
      $DB=new SAMSDB(&$SAMSConf);
      PageTop("reconfig_48.jpg","Send command '$SAMSConf->SHUTDOWN' to proxy server");
      $num_rows=$DB->samsdb_query("INSERT INTO reconfig SET s_proxy_id='$id',s_service='proxy',s_action='shutdown' ");
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
       print("function DeleteUser(userid)\n");
       print("{\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=shutdown_proxy&filename=proxybuttom_9_down.php&id=$id\";\n");
       print("}\n");
       print("</SCRIPT> \n");
       
       print("<TD CLASS=\"samstraytd\">\n");
       print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/shutdown_32.jpg\" \n ");
       print("TITLE=\"Shutdown proxy server\"  border=0 ");
       print("onclick=DeleteUser(\"userid\") \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/shutdown_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/shutdown_32.jpg'\" >\n");
    }

}




?>
