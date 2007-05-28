<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function GroupTrafficPeriodGB()
{
  require('lib/chart.php');
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["groupname"])) $groupname=$_GET["groupname"];

  global $DATE;
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  $aaa=ReturnGroupNick($groupname);
  
  $result=mysql_query("SELECT user,cachesum.domain,sum(cachesum.hit),sum(cachesum.size) as sizeall FROM $SAMSConf->LOGDB.cachesum , $SAMSConf->SAMSDB.squidusers as tu WHERE tu.nick=cachesum.user && tu.domain=cachesum.domain &&  tu.group=\"$groupname\" && date>=\"$sdate\"&&date<=\"$edate\" group by user order by sizeall desc");
  $count=0;
  while ($row=mysql_fetch_array($result))
       {
 	 $SIZE[$count]=floor($row[3]/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
	 $HIT[$count]=floor($row[2]/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
	 $USERS[$count]=$row_2["nick"];
        //print("<BR>$row[3]-$row[2]");
	$count++;
       }
$showbar=new BAR(500, 200, 30, 20, $SIZE, $HIT, $count, $USERS);
$showbar->CreateBars();

}
 
 
function GroupTrafficPeriod()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["groupname"])) $groupname=$_GET["groupname"];

  global $DATE;
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  $aaa=ReturnGroupNick($groupname);
  PageTop("usergroup_48.jpg"," $grptraffic_1 <FONT COLOR=\"BLUE\">$aaa</FONT><BR>$groupbuttom_1_traffic_GroupTrafficPeriod_1");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" id=UserName value=\"$groupname\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"grouptrafficperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"groupbuttom_1_traffic.php\">\n");
  NewDateSelect(0,"");
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  if($SAMSConf->SHOWGRAPH=="Y")
    printf("<P><IMG SRC=\"main.php?show=exe&groupname=$groupname&function=grouptrafficperiodgb&filename=groupbuttom_1_traffic.php&gb=1&sdate=$sdate&edate=$edate \"><P>");
  
  $count=1;
  $size2=0;
  $hitsize=0;
  print("<TABLE CLASS=samstable>");
  print("<TH>No");
  print("<TH>User");
  if(($SAMSConf->AUTH=="ntlm"||$SAMSConf->AUTH=="adld")&&$SAMSConf->NTLMDOMAIN=="Y")
    print("<TH>Domain");
  if($SAMSConf->access==2)
    {
      print("<TH>$groupbuttom_1_traffic_GroupTrafficPeriod_3");
      print("<TH>$groupbuttom_1_traffic_GroupTrafficPeriod_2");
    }   
  print("<TH>$groupbuttom_1_traffic_GroupTrafficPeriod_4");

 $result=mysql_query("SELECT cachesum.user,cachesum.domain,sum(cachesum.hit),sum(cachesum.size) as sizeall, tu.name, tu.family, tu.nick, cachesum.domain FROM $SAMSConf->LOGDB.cachesum, $SAMSConf->SAMSDB.squidusers as tu WHERE tu.nick=cachesum.user && tu.domain=cachesum.domain &&  tu.group=\"$groupname\" && date>=\"$sdate\"&&date<=\"$edate\" group by user order by sizeall desc");
  while ($row=mysql_fetch_array($result))
       {
         print("<TR>");
         LTableCell($count,8);
         
	 if($SAMSConf->SHOWNAME=="fam")
           $name="$row[family]";
         else if($SAMSConf->SHOWNAME=="famn")
           $name="$row[family] $row[name]";
         else if($SAMSConf->SHOWNAME=="nickd")
           $name="$row[nick] / $row[domain]";
         else 
           $name="$row[nick]";
         LTableCell($name,16);
	 
	 //LTableCell($row[0],16);
         if(($SAMSConf->AUTH=="ntlm"||$SAMSConf->AUTH=="adld")&&$SAMSConf->NTLMDOMAIN=="Y")
            LTableCell($row[1],16);
         if($SAMSConf->access==2)
           {
             $aaa=FormattedString("$row[3]");
             RTableCell($aaa,20);
             $aaa=FormattedString("$row[2]");
             RTableCell($aaa,20);
	   }
	 if($SAMSConf->realtraffic=="real")
  	   PrintFormattedSize($row[3]-$row[2]);
	 else
	   PrintFormattedSize($row[3]);
         
	 print("</TR>");
         $count=$count+1;
	 $size2=$size2+$row[3];
	 $hitsize=$hitsize+$row[2];
       }
  print("<TR>");
  print("<TD>");
  RBTableCell("$vsego",16);
  if($SAMSConf->AUTH="ntlm"&&$SAMSConf->NTLMDOMAIN=="Y")
    print("<TD>");
  if($SAMSConf->access==2)
    {
      $aaa=FormattedString("$size2");
      RBTableCell($aaa,16);
      $aaa=FormattedString("$hitsize");
      RBTableCell($aaa,20);
    }  
  if($SAMSConf->realtraffic=="real")
    PrintFormattedSize($size2-$hitsize);
  else
    PrintFormattedSize($size2);
  print("</TABLE>");

}


/****************************************************************/
function GroupTrafficForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["groupname"])) $groupname=$_GET["groupname"];

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM groups WHERE name=\"$groupname\" ");
  $row=mysql_fetch_array($result);

  $aaa=ReturnGroupNick($groupname);
  PageTop("usergroup_48.jpg"," $grptraffic_1 <FONT COLOR=\"BLUE\"> $aaa </FONT><BR> $groupbuttom_1_traffic_GroupTrafficForm_1");
  print("<BR>\n");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" id=UserName value=\"$row[name]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"grouptrafficperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"groupbuttom_1_traffic.php\">\n");
  NewDateSelect(0,"");
  print("</FORM>\n");

}


function groupbuttom_1_traffic($groupname)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $result=mysql_query("SELECT * FROM groups WHERE name=\"$groupname\" ");
  $row=mysql_fetch_array($result);

  if($SAMSConf->access>0||$SAMSConf->groupauditor==$row[name])
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=grouptrafficform&filename=groupbuttom_1_traffic.php&groupname=$groupname","basefrm","traffic_32.jpg","traffic_48.jpg","$groupbuttom_1_traffic_groupbuttom_1_traffic_1");
	}

}




?>
