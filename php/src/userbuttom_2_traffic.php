<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UserTrafficPeriodGB()
{
  require('lib/chart.php');
  
  global $SAMSConf;
  global $DATE;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();
  
  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["userdomain"])) $userdomain=$_GET["userdomain"];

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  $stime=gmmktime (0, 0, 1, $DATE->smon, $DATE->sday, $DATE->syea);
  $etime=gmmktime (23, 59, 59, $DATE->emon, $DATE->eday, $DATE->eyea);
  $days=ceil(($etime-$stime)/(60*60*24));
  
  for($i=0;$i<$days+1;$i++)
    {
      $data1[$i]=0;
      $data2[$i]=0;
    }
  $result=mysql_query("SELECT SUM(size), SUM(hit), MONTH(date), DAYOFMONTH(date), YEAR(date) FROM cachesum WHERE user=\"$username\" &&date>=\"$sdate\" &&date<=\"$edate\" &&domain=\"$userdomain\" GROUP BY date");
  while($row=mysql_fetch_array($result))
       {
         $time=gmmktime (23, 59, 59, $row[2], $row[3], $row[4]);
	 $day=ceil(($time-$stime)/(60*60*24));
         if($SAMSConf->realtraffic=="real")
	     $data1[$day]=$row['0']-$row['1'];
         else
	     $data1[$day]=$row['0'];
//         $data1[$day]=$row[0];
//         $data2[$day]=$row[1];
       }
  
$chart = new chart(400, 200, "");
//$chart->plot($data1);
$chart->plot($data1, false, "MidnightBlue", "lines");

$chart->set_background_color("white", "white");
$chart->set_title("Traffic of user $username");
$chart->set_labels("", "Mb");
$chart->stroke();  

}





function UserTrafficPeriod()
{
  global $SAMSConf;
  global $DATE;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["userdomain"])) $userdomain=$_GET["userdomain"];
  if(isset($_GET["usergroup"])) $usergroup=$_GET["usergroup"];

  if($SAMSConf->access==0 && $SAMSConf->domainusername!=$username && $SAMSConf->groupauditor!=$usergroup && strlen($SAMSConf->adminname)==0)
    exit(0);
  
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);
  PageTop("user.jpg","$traffic_1 <FONT COLOR=\"BLUE\"> $username</FONT><BR>$userbuttom_2_traffic_UserTrafficPeriod_2");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"username\" id=UserName value=\"$username\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userdomain\" id=UserDomain value=\"$userdomain\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usertrafficperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_2_traffic.php\">\n");

  NewDateSelect(0,"");
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  if($SAMSConf->SHOWGRAPH=="Y")
    printf("<P><IMG SRC=\"main.php?username=$username&userdomain=$userdomain&show=exe&function=usertrafficperiodgb&filename=userbuttom_2_traffic.php&gb=1&sdate=$sdate&edate=$edate \"><P>");
  
  $count=1;
  $cache=0;
  print("<TABLE CLASS=samstable>");
  print("<TH>No");
  print("<TH>$traffic_data");
  if($SAMSConf->access==2)
    {
      print("<TH>$userbuttom_2_traffic_UserTrafficPeriod_3");
      print("<TH>$userbuttom_2_traffic_UserTrafficPeriod_4");
    }   
  print("<TH>$userbuttom_2_traffic_UserTrafficPeriod_5");
  $size=0;
  $result=mysql_query("SELECT sum(size),date,user,domain,sum(hit) FROM cachesum WHERE user=\"$username\"&&date>=\"$sdate\"&&date<=\"$edate\"&&domain=\"$userdomain\" GROUP BY date");
  while($row=mysql_fetch_array($result))
       {
         print("<TR>");
         LTableCell($count,10);
         $aaa=ReturnDate($row['date']);
         LTableCell($aaa,15);
         if($SAMSConf->access==2)
           {
             $aaa=FormattedString("$row[0]");
             RTableCell($aaa,25);
             $aaa=FormattedString("$row[4]");
	     RTableCell($aaa,25);
	   }   
         if($SAMSConf->realtraffic=="real")
	   PrintFormattedSize($row[0]-$row[4]);
	 else
	   PrintFormattedSize($row[0]);
	 
         print("</TR>");
         $count=$count+1;
         $size=$size+$row[0];
	 $cache=$cache+$row[4];
       }
  print("<TR>");
  print("<TD>");
  RBTableCell("$vsego",25);
  if($SAMSConf->access==2)
    {
      $aaa=FormattedString("$size");
      RBTableCell("$aaa",25);
      $aaa=FormattedString("$cache");
      RBTableCell("$aaa",25);
   }
  if($SAMSConf->realtraffic=="real")
    PrintFormattedSize($size-$cache);
  else
    PrintFormattedSize($size);
  
  print("</TABLE>");
}






/****************************************************************/
function UserTrafficForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["userid"])) $userid=$_GET["userid"];

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);

  PageTop("user.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$row[1]</FONT><BR>$userbuttom_2_traffic_UserTrafficForm_1");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"username\" id=UserName value=\"$row[1]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userdomain\" id=UserDomain value=\"$row[6]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"usergroup\" id=UserGroup value=\"$row[group]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usertrafficperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_2_traffic.php\">\n");
  NewDateSelect(0,"");
  print("</FORM>\n");


}


function userbuttom_2_traffic($userid)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);
   
   if($SAMSConf->access>0||($SAMSConf->USERACCESS=="Y"&&$SAMSConf->domainusername=="$row[nick]")||$SAMSConf->groupauditor==$row['group'])
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=usertrafficform&filename=userbuttom_2_traffic.php&userid=$userid","basefrm","traffic_32.jpg","traffic_48.jpg","$userbuttom_2_traffic_userbuttom_2_traffic_1");
	}

}




?>
