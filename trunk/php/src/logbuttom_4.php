<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ShowAdminLog()
{
  global $SAMSConf;
  global $DATE;
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
 PageTop("log_48.jpg","$log_1");
       print("<FORM NAME=\"SHOWLOG\" ACTION=\"main.php\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"showadminlog\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"logbuttom_4.php\">\n");
       NewDateSelect(0,"");
       print("</FORM>\n");
  
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  $result=mysql_query("SELECT * FROM log WHERE code=\"04\"&&date>=\"$sdate\"&&date<=\"$edate\" ");

  print("<TABLE width=\"95%\">");
  print("<TR>");
  print("<TD bgcolor=blanchedalmond width=15%><font size=-1><b>$log_4</b></TD>");
  print("<TD width=12% bgcolor=beige align=left><font size=-1><b>$log_5</b></TD>");
  print("<TD width=15% bgcolor=beige align=left><font size=-1><b>$log_6</b></TD>");
  print("<TD width=58% bgcolor=beige align=center><font size=-1><b>$log_3</b></TD>");

  while($row=mysql_fetch_array($result))
    {
         print("<TR>");
	 print("<TD bgcolor=blanchedalmond <font size=-1>");
	 print("$row[date]</TD>");

	 print("<TD bgcolor=blanchedalmond ><font size=-1>");
	 print("$row[time]</TD>");

	 print("<TD bgcolor=blanchedalmond ><font size=-1>");
	 print("$row[user]</TD>");

	 print("<TD bgcolor=blanchedalmond ><font size=-1>");
	 print("$row[value]</TD>");

    }

}

function ShowAdminLogForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("log_48.jpg","$log_1");


  print(" <CENTER>\n");

       print("<FORM NAME=\"SHOWLOG\" ACTION=\"main.php\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"showadminlog\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"logbuttom_4.php\">\n");
       NewDateSelect(0,"");
       print("</FORM>\n");

}



function logbuttom_4()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
      print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
      GraphButton("main.php?show=exe&function=showadminlogform&filename=logbuttom_4.php","basefrm","logs_squid_32.gif","logs_squid.gif","$logbuttom_4_logbuttom_4_1");
    }

}

?>
