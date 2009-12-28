<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ClearLog()
{
  global $SAMSConf;
  global $DATE;

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");
  $result=mysql_query("DELETE FROM ".$SAMSConf->SAMSDB.".log WHERE date>=\"$sdate\"&&date<=\"$edate\"");
  print("<H3>$logbuttom_9_delete_ClearLog_1:</H3> $traffic_2 $bdate $traffic_3 $eddate ");
//  UpdateLog("$SAMSConf->adminname","$squidbuttom_3_delete_ClearSquidLog_1 $traffic_2 $sday.$smon.$syea $traffic_3 $eday.$emon.$eyea","03");
}


function ClearLogForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("erase_48.jpg","$logbuttom_9_delete_ClearLogForm_1");;

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"clearlog\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"logbuttom_9_delete.php\">\n");
  NewDateSelect(0,"");
  print("</FORM>\n");


}


function logbuttom_9_delete()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=clearlogform&filename=logbuttom_9_delete.php","basefrm","trash_32.jpg","trash_48.jpg","$squidbuttom_3_delete_squidbuttom_3_delete_1");
	}

}




?>
