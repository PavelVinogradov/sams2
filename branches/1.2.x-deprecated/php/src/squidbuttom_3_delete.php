<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ClearSquidLog()
{
  global $SAMSConf;
  global $DATE;

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB)
       or print("Error\n");
  $result=mysql_query("DELETE FROM cache WHERE date>=\"$sdate\"&&date<=\"$edate\"");
  $result=mysql_query("DELETE FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\"");
  print("<H3>$squidbuttom_3_delete_ClearSquidLog_1</H3> $traffic_2 $bdate $traffic_3 $eddate ");
  $result=mysql_query("OPTIMIZE TABLE cache");
  $result=mysql_query("OPTIMIZE TABLE cachesum");
  if($result!=FALSE)
      UpdateLog("$SAMSConf->adminname","SQUID log data have been deleted $sdate - $edate ","04");
}

function ClearSquidLogForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("erase_48.jpg","$squidbuttom_3_delete_ClearSquidLogForm_1");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"clearsquidlog\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"squidbuttom_3_delete.php\">\n");
  NewDateSelect(0,"");
  print("</FORM>\n");


}


function squidbuttom_3_delete()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=clearsquidlogform&filename=squidbuttom_3_delete.php","basefrm","trash_32.jpg","trash_48.jpg","$squidbuttom_3_delete_squidbuttom_3_delete_1");
	}

}




?>
