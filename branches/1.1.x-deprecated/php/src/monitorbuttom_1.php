<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function Monitor_1()
{
  global $SAMSConf;

  $timeout = 10; 
  if(isset($_GET["timeout"]) and ctype_digit(strval($_GET["timeout"])))
    $timeout = $_GET["timeout"] >= 10 ? $_GET["timeout"] : 10;

  printf("<SCRIPT LANGUAGE=\"javascript\">\n");
  printf("function Refr() \n");
  printf("{\n");
  printf("document.location='main.php?show=exe&function=monitor_1&filename=monitorbuttom_1.php&timeout=$timeout'};\n");
  printf("setTimeout('Refr();',$timeout*1000);\n");
  printf("</SCRIPT>\n");
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  print("<FORM NAME=\"timeoutform\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"monitor_1\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"monitorbuttom_1.php\">\n");
  print("<B>Timeout:</B> <INPUT TYPE=\"TEXT\" NAME=\"timeout\" SIZE=\"3\" value=\"$timeout\"> sec\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" VALUE=\"Set new timeout\">\n");
  print("</FORM>\n");
 
  $result=mysql_query("SELECT * FROM squidusers ORDER BY nick");
  $count=0;
  print("<TABLE WIDTH=\"95%\" BORDER=0>");
  while($row=mysql_fetch_array($result))
     {
        if($count==0)
           {
              print("<TR>\n");
           }
        print("<TD WIDTH=\"33%\">");
        if($row['enabled']>0)
           print("<IMAGE align=left src=\"$SAMSConf->ICONSET/personal.gif\" ");
        else
           print("<IMAGE align=left src=\"$SAMSConf->ICONSET/dpersonal.gif\" ");
        print("<B>$row[nick] <BR>");

        if($SAMSConf->realtraffic=="real")
          {
	      $traffic=ReturnTrafficFormattedSize($row['size']-$row['hit']);
              $trafsize=$row['size']-$row['hit'];
          }
        else
          {
	     $traffic=ReturnTrafficFormattedSize($row['size']);
             $trafsize=$row['size'];
          }
	$quote=ReturnTrafficFormattedSize($row['quotes']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE);

        if($trafsize>$row['quotes']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE && $row['quotes']>0)
              print("<FONT COLOR=\"RED\">$traffic</FONT></B>\n");
        else
	      print("<FONT COLOR=\"BLUE\">$traffic</FONT></B>\n");
	
        $count=$count+1;
        if($count>2)
          {
             $count=0;
          }
     }
  print("</TABLE>");
  mysql_free_result($result);
}


function monitorbuttom_1()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
      print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
      GraphButton("main.php?show=exe&function=monitor_1&filename=monitorbuttom_1.php","basefrm","usermon-32.jpg","usermon-48.jpg","$monitorbuttom_1_monitorbuttom_1_1");
    }

}

?>
