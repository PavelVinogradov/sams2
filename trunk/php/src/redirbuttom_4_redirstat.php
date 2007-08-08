<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ShowRedirStat()
{
  global $SAMSConf;
  global $DATE;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["redirid"])) $id=$_GET["redirid"];
  
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  PageTop("user.jpg","$redirbuttom_4_redirstat_ShowRedirStat_1<BR><FONT COLOR=\"BLUE\">$row[name]</FONT>");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"showredirstat\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"redirbuttom_4_redirstat.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"redirid\" id=redirid value=\"$id\">\n");
  NewDateSelect(0,"");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  print("<TABLE CLASS=samstable>");
  print("<TH>No");
  print("<TH>URL");
  print("<TH>Количество в базе");

  $count=1;
  $filesize=$filesize*$SAMSConf->KBSIZE;
  
  $result=mysql_query("SELECT * FROM ".$SAMSConf->SAMSDB.".urls WHERE type=\"$id\" ");
  while($row=mysql_fetch_array($result))
       {
         $result2=mysql_query("SELECT count(*) FROM ".$SAMSConf->LOGDB.".cache WHERE  date>=\"$sdate\"&&date<=\"$edate\"&&url LIKE \"%$row[url]%\" ");
         $row2=mysql_fetch_array($result2);
	 print("<TR>");
         LTableCell($count,8);
         LTableCell($row[url],60);
         LTableCell($row2[0],15);
         $count=$count+1;
       }
  print("</TABLE>");


}

  
 /****************************************************************/
function ShowRedirStatForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM redirect WHERE filename=\"$id\" ");
  $row=mysql_fetch_array($result);

  PageTop("user.jpg","$redirbuttom_4_redirstat_ShowRedirStat_1 <BR><FONT COLOR=\"BLUE\">$row[name]</FONT>");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"showredirstat\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"redirbuttom_4_redirstat.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"redirid\" id=redirid value=\"$id\">\n");
  NewDateSelect(0,"");
  print("</FORM>\n");

}


 
function redirbuttom_4_redirstat()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=showredirstatform&filename=redirbuttom_4_redirstat.php&id=$id","basefrm","liststat_32.jpg","liststat_48.jpg","Статистика");
	}

}




?>
