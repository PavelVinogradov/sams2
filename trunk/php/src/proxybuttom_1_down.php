<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function DownSquid()
{
  global $SAMSConf;
 
 if(isset($_GET["cache"])) $cache=$_GET["cache"];
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
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
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
  PageTop("reconfig_48.jpg","Send command '$SAMSConf->SHUTDOWN' to proxy server");
  
   if($SAMSConf->PROXYCOUNT>1)
    {
      print("<FORM NAME=\"adddenied\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"downsquid\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"proxybuttom_1_down.php\">\n");
      print("<P>\n");
      print("<TABLE CLASS=samstable WIDTH=\"80%\">");
      print("<TH width=60%>$shutdown_proxy_proxybuttom_1_down_1");
      print("<TH width=20%>$shutdown_proxy_proxybuttom_1_down_2");
      $result=mysql_query("SELECT id,description FROM ".$SAMSConf->SAMSDB.".proxyes ORDER BY id");
       while($row=mysql_fetch_array($result))
           {
             print("<TR><TD> $row[description]");
              print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=cache[$row[id]]> \n");
           }
      print("</TABLE>");
      print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$shutdown_proxy_proxybuttom_1_down_3\">\n");
      print("</FORM>\n");
    
    }
  else
    {  
      $result=mysql_query("INSERT INTO reconfig SET number=\"0\",service=\"proxy\",action=\"shutdown\" ");
      if($result!=FALSE)
        UpdateLog("$SAMSConf->adminname","Send shutdown command to proxy server ","03");
    }	
}

function proxybuttom_1_down()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);


   if($SAMSConf->access==2)
    {
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function ReloadBaseFrame()\n");
       print("{\n");
       print("   window.location.reload();\n");
       print("}\n");
       print("function DeleteUser(userid)\n");
       print("{\n");
       if($SAMSConf->PROXYCOUNT==1)
         {
           print("  value=window.confirm(\"$proxybuttom_1_down_proxybuttom_1_down_1 \" );\n");
           print("  if(value==true) \n");
           print("     {\n");
	 }   
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=shutdown_proxy&filename=proxybuttom_1_down.php\";\n");
       if($SAMSConf->PROXYCOUNT==1)
         {
           print("     }\n");
         }
       print("}\n");
       print("</SCRIPT> \n");
       
      // print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
      // GraphButton("main.php?show=exe&function=shutdown_proxy&filename=proxybuttom_1_down.php","basefrm","loadbase_32.jpg","loadbase_48.jpg","Shutdown proxy server");
       
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/shutdown_32.jpg\" \n ");
       print("TITLE=\"Shutdown proxy server\"  border=0 ");
       print("onclick=DeleteUser(\"userid\") \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/shutdown_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/shutdown_32.jpg'\" >\n");
    }

}




?>
