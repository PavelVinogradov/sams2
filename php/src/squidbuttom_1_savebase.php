<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function SaveSquidLog()
{
  global $DATE;
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB)
       or print("Error\n");

  $tablecount=0;
  $filename=strftime("squidlogdump-%d%b%Y-%H-%M-%S.sql.gz");

  $fout=gzopen("data/$filename","w9");
  gzwrite($fout,"# SQUIDLOG DUMP FOR MYSQL DATABASE\n\n");
  gzwrite($fout," USE ".$SAMSConf->LOGDB."; \n");
  
  //записываем squidlog.cache
  $result2=mysql_query("SHOW COLUMNS FROM cache"); //берем количество
  $count=0;
  while($row2=mysql_fetch_array($result2))           //столбцов
    {
       $count++;
    }
  $result3=mysql_query("SELECT * FROM cache WHERE date>=\"$sdate\"&&date<=\"$edate\""); //
  while($row3=mysql_fetch_array($result3))           //
    {
       gzwrite($fout," INSERT INTO cache SET date='$row3[date]',time='$row3[time]', user='$row3[user]',domain='$row3[domain]',size='$row3[size]',ipaddr='$row3[ipaddr]', period='$row3[period]',url='$row3[url]',hit='$row3[hit]',method='$row3[method]'; \n");
    }
    
    //записываем squidlog.cachesum
  $result2=mysql_query("SHOW COLUMNS FROM cachesum"); //берем количество
  $count=0;
  while($row2=mysql_fetch_array($result2))           //столбцов
    {
       $count++;
    }
  $result3=mysql_query("SELECT * FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\""); //
  while($row3=mysql_fetch_array($result3))           //
    {
       gzwrite($fout," INSERT INTO cachesum SET date='$row3[date]', user='$row3[user]',domain='$row3[domain]',size='$row3[size]',hit='$row3[hit]'; \n");
    }

  gzclose($fout);
  print("<A HREF=\"data/$filename\">\n");
  print("<BR>$squidbuttom_1_savebase_SaveSquidLog_1\n");
  print("</A>\n");
  return("$filename");
}


function SaveSquidLogForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("ark_48.jpg","$squidbuttom_1_savebase_SaveSquidLogForm_1");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"savesquidlog\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"squidbuttom_1_savebase.php\">\n");
  NewDateSelect(0,"");
  print("</FORM>\n");


}


function squidbuttom_1_savebase()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=savesquidlogform&filename=squidbuttom_1_savebase.php","basefrm","savebase_32.jpg","savebase_48.jpg","$squidbuttom_1_savebase_squidbuttom_1_savebase_1");
	}

}




?>
