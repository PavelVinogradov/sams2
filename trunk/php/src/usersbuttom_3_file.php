<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UsersFileSizePeriod()
{
  global $SAMSConf;
  global $DATE;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  if(isset($_GET["size"])) $filesize=$_GET["size"];

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access!=2)     {      exit;    }
  
  PageTop("usergroup_48.jpg","$usersbuttom_3_file_UsersFileSizePeriod_1<BR>$usersbuttom_3_file_UsersFileSizePeriod_2 $filesize Kb");
  print("<BR>\n");

//  SizeDateSelect();
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usersfilesizeperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_3_file.php\">\n");
  NewDateSelect(3,"$usersbuttom_3_file_UsersFileSizePeriod_3");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  if($_GET["check"]==1)
    {
      $filesize=($filesize*$SAMSConf->KBSIZE)-1;
      print("<TABLE CLASS=samstable>\n");
      //print("<TR>");
      print("<THEAD>");
      print("<TH>No");
      print("<TH>$usersbuttom_3_file_UsersFileSizePeriod_4");
      print("<TH>$usersbuttom_3_file_UsersFileSizePeriod_5");
      print("<TH>$usersbuttom_3_file_UsersFileSizePeriod_6");
      print("<TH>URL");
      print("</THEAD>");
      $count=0;
//      $result=mysql_query("SELECT user,date,size,url FROM cache WHERE cache.date>=\"$sdate\"&&cache.date<=\"$edate\"&&size>\"$filesize\" ORDER BY size DESC limit 250");
      $result=mysql_query("SELECT cache.user,cache.date,cache.size,cache.url,squidusers.name,squidusers.family,squidusers.nick,cache.domain FROM ".$SAMSConf->LOGDB.".cache LEFT JOIN ".$SAMSConf->SAMSDB.".squidusers ON cache.user=squidusers.nick WHERE cache.date>=\"$sdate\"&&cache.date<=\"$edate\"&&cache.size>\"$filesize\" ORDER BY size DESC limit 250");
      while($row=mysql_fetch_array($result))
         {
             print("\n<TR>");
             LTableCell($count,8);
             
	     if($SAMSConf->SHOWNAME=="fam")
               $name="$row[family]";
             else if($SAMSConf->SHOWNAME=="famn")
               $name="$row[family] $row[name]";
             else if($SAMSConf->SHOWNAME=="nickd")
               $name="$row[nick] / $row[domain]";
             else 
               $name="$row[nick]";
             LTableCell("$name ",15);
	     
	     //LTableCell($row['user'],15);
             
	     $aaa=ReturnDate($row['date']);
             LTableCell($row['date'],15);
             $aaa=FormattedString("$row[size]");
             RTableCell($aaa,20);
             RTableCell($row['url'],42);
             $count=$count+1;
         }
      mysql_free_result($result);

      print("</TABLE>");
    }
  if($_GET["check"]==2)
    {
      print("<TABLE CLASS=samstable>");
//      print("\n<TR>");
      print("<TH>No");
      print("<TH>$usersbuttom_3_file_UsersFileSizePeriod_4");
      print("<TH>$usersbuttom_3_file_UsersFileSizePeriod_5");
      print("<TH>$usersbuttom_3_file_UsersFileSizePeriod_7");
      print("<TH>URL");
      $count=0;
      $result=mysql_query("SELECT * FROM cache WHERE cache.date>=\"$sdate\"&&cache.date<=\"$edate\"&&url like \"%$filesize%\" ORDER BY user ");
      while($row=mysql_fetch_array($result))
         {
             print("\n<TR>");
             LTableCell($count,8);
             LTableCell($row['user'],15);
             $aaa=ReturnDate($row['date']);
             LTableCell($row['date'],15);
             $aaa=FormattedString("$row[time]");
             RTableCell($aaa,20);
             RTableCell($row['url'],42);

             $count=$count+1;
         }
      mysql_free_result($result);
      print("<TR>");
      print("<TD>");
      print("<TD>$usersbuttom_3_file_UsersFileSizePeriod_7");
      print("<TD>");
      print("<TD>");
//      $aaa=FormattedString("$size");
      print("<TD width=42% align=right><font size=-1>$aaa</TD>");
      print("</TABLE>");
    }
}


function  UsersFileSizeForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access!=2)     {      exit;    }
  
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);

  PageTop("usergroup_48.jpg","$usersbuttom_3_file_usersbuttom_3_file_1");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usersfilesizeperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_3_file.php\">\n");
  NewDateSelect(3,"$usersbuttom_3_file_usersbuttom_3_file_2");
  print("</FORM>\n");

}



function usersbuttom_3_file()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=usersfilesizeform&filename=usersbuttom_3_file.php","basefrm","ftraffic_32.jpg","ftraffic_48.jpg","$usersbuttom_3_file_usersbuttom_3_file_1");
	}

}




?>
