<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UserFileSizePeriod()
{
  global $SAMSConf;
  global $DATE;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  $usergroup="";
  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["userdomain"])) $userdomain=$_GET["userdomain"];
  if(isset($_GET["usergroup"])) $usergroup=$_GET["usergroup"];
  if(isset($_GET["size"])) $filesize=$_GET["size"];

  if($SAMSConf->domainusername!=$username&&$SAMSConf->groupauditor!=$usergroup&&strlen($SAMSConf->adminname)==0)
    exit(0);
  
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  if($SAMSConf->access==0&&$SAMSConf->domainusername !=$username)
	exit(0);
  PageTop("user.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$username</FONT><BR>$userbuttom_3_file_UserFileSizePeriod_1 $filesize kb");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"username\" id=UserName value=\"$username\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userdomain\" id=UserDomain value=\"$userdomain\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userfilesizeperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_3_file.php\">\n");
  NewDateSelect(1,"$userbuttom_3_file_UserFileSizePeriod_2");
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  print("<TABLE CLASS=samstable>");
  print("<TH>No");
  print("<TH>$traffic_data");
  print("<TH>$userbuttom_3_file_UserFileSizePeriod_4");
  print("<TH>URL");

  $count=1;
  $filesize=$filesize*$SAMSConf->KBSIZE;
  $result=mysql_query("SELECT date,size,url FROM cache WHERE date>=\"$sdate\"&&date<=\"$edate\"&&user=\"$username\"&&domain=\"$userdomain\"&&size>\"$filesize\" order by size desc limit 250");
  while($row=mysql_fetch_array($result))
       {
         print("<TR>");
         LTableCell($count,8);
         $aaa=ReturnDate($row['date']);
         LTableCell($aaa,15);
         $aaa=FormattedString("$row[size]");
         RTableCell($aaa,20);
         LTableCell($row['url'],57);
         $count=$count+1;
       }
  print("</TABLE>");


}


/****************************************************************/
function UserFileSizeForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["userid"])) $userid=$_GET["userid"];

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);

  PageTop("user.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$row[1]</FONT><BR>$userbuttom_3_file_UserFileSizeForm_1");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"username\" id=UserName value=\"$row[1]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userdomain\" id=UserDomain value=\"$row[6]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"usergroup\" id=UserGroup value=\"$row[group]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userfilesizeperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_3_file.php\">\n");
  NewDateSelect(1,"$userbuttom_3_file_UserFileSizeForm_2");
  print("</FORM>\n");

}



function userbuttom_3_file($userid)
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
//echo "access=$SAMSConf->access";

  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);
   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=userfilesizeform&filename=userbuttom_3_file.php&userid=$userid","basefrm","ftraffic_32.jpg","ftraffic_48.jpg","$userbuttom_3_file_userbuttom_3_file_3");
	}

}




?>
