<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
 
 
function UsersPercentTrafficGB()
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

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  $cresult=mysql_query("CREATE TEMPORARY TABLE cache_ SELECT user,domain,sum(size) as user_size,sum(hit) as user_hit FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\" group by user");
  $cresult=mysql_query("SELECT sum(user_size),sum(user_hit) FROM cache_ ");
  $row=mysql_fetch_array($cresult);
  if($SAMSConf->realtraffic=="real")
	$all=$row[0] - $row[1];
  else
	$all=$row[0];
  if($all==0) $all=1;
	$percent=$all/100;

  if($SAMSConf->realtraffic=="real")
    $result=mysql_query("SELECT user,domain,user_size,round((user_size-user_hit)/$percent,2) as percent,user_hit from cache_ order by user_size desc");
  else
    $result=mysql_query("SELECT user,domain,user_size,round(user_size/$percent,2) as percent from cache_ order by user_size desc");
  $count=0;
  while($row=mysql_fetch_array($result))
    {
      if($SAMSConf->realtraffic=="real")
          $SIZE[$count]=$row['user_size']-$row['user_hit'];
      else
          $SIZE[$count]=$row['user_size'];
      $USERS[$count]=$count+1;
      $count++;
    }

  $circle=new CIRCLE3D(500, $count*15, $SIZE, $count, $USERS);
  $circle->ShowCircle();
}

function GroupsPercentTrafficGB()
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

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  $cresult=mysql_query("CREATE TEMPORARY TABLE cache_ SELECT user,domain,sum(size) as user_size,sum(hit) as user_hit FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\" group by user");
  $cresult=mysql_query("SELECT sum(user_size),sum(user_hit) FROM cache_ ");
  $row=mysql_fetch_array($cresult);
  if($SAMSConf->realtraffic=="real")
	$all=$row[0] - $row[1];
  else
	$all=$row[0];
  if($all==0) $all=1;
	$percent=$all/100;
  
  $result=mysql_query("SELECT groups.nick,sum(user_size) as grp_size,sum(user_hit) as grp_hit FROM $SAMSConf->SAMSDB.squidusers,$SAMSConf->SAMSDB.groups,cache_ where cache_.user=squidusers.nick && squidusers.group=groups.name group by groups.nick order by grp_size DESC");
  $count=0;
  while($row=mysql_fetch_array($result))
    {
	if($SAMSConf->realtraffic=="real")
          $SIZE[$count]=$row['grp_size']-$row['grp_hit'];
	else
          $SIZE[$count]=$row['grp_size'];
      $USERS[$count]=$count+1;
      $count++;
    }
   $circle=new CIRCLE3D(500, $count*15, $SIZE, $count, $USERS);
   $circle->ShowCircle();
}


function UsersPercentTraffic()
{
  global $SAMSConf;
  global $DATE;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access==0)     {      exit;    }
  
  PageTop("usergroup_48.jpg","$usersbuttom_4_percent_UsersPercentTraffic_1<BR>$usersbuttom_4_percent_UsersPercentTraffic_2");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userspercenttraffic\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_4_percent.php\">\n");
  NewDateSelect(0,"");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");
                                                   
  if($SAMSConf->SHOWGRAPH=="Y")
    printf("<P><IMG SRC=\"main.php?show=exe&function=userspercenttrafficgb&filename=usersbuttom_4_percent.php&gb=1&sdate=$sdate&edate=$edate \"><P>");
  
  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  $cresult=mysql_query("CREATE TEMPORARY TABLE cache_ SELECT user,domain,sum(size) as user_size,sum(hit) as hit_size FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\" group by user");
  $cresult=mysql_query("SELECT sum(user_size),sum(hit_size) FROM cache_ ");
  $row=mysql_fetch_array($cresult);
  
  if($SAMSConf->realtraffic=="real")
	$all=$row[0] - $row[1];
  else
	$all=$row[0];
  if($all==0) $all=1;
	$percent=$all/100;

  print("<TABLE  CLASS=samstable><TH>No");
  print("<TH>$usersbuttom_4_percent_UsersPercentTraffic_3");
  if(($SAMSConf->AUTH="ntlm"||$SAMSConf->AUTH="adld")&&$SAMSConf->NTLMDOMAIN=="Y")
    print("<TH>$usersbuttom_4_percent_UsersPercentTraffic_4");
  if($SAMSConf->access==2)
    print("<TH>$usersbuttom_4_percent_UsersPercentTraffic_5");
  print("<TH>$usersbuttom_4_percent_UsersPercentTraffic_6");
  print("<TH>%");

  if($SAMSConf->realtraffic=="real")
	$result=mysql_query("SELECT cache_.user,cache_.domain,cache_.user_size,round((cache_.user_size-cache_.hit_size)/$percent,2) as percent,squidusers.name,squidusers.family,squidusers.nick,cache_.domain,cache_.hit_size from cache_  LEFT JOIN $SAMSConf->SAMSDB.squidusers ON cache_.user=squidusers.nick order by cache_.user_size desc");
  else
	$result=mysql_query("SELECT cache_.user,cache_.domain,cache_.user_size,round(cache_.user_size/$percent,2) as percent,squidusers.name,squidusers.family,squidusers.nick,cache_.domain from cache_  LEFT JOIN $SAMSConf->SAMSDB.squidusers ON cache_.user=squidusers.nick order by cache_.user_size desc");
  $ap=0;
  $count=1;

  while ($row=mysql_fetch_array($result))
    {
      print("<TR>");
      LTableCell($count,5);
     
      if($SAMSConf->SHOWNAME=="fam")
        $name="$row[family]";
      else if($SAMSConf->SHOWNAME=="famn")
        $name="$row[family] $row[name]";
      else if($SAMSConf->SHOWNAME=="nickd")
        $name="$row[nick] / $row[domain]";
      else 
        $name="$row[nick]";
 
      LTableCell("$name ",15);
     
      if(($SAMSConf->AUTH="ntlm"||$SAMSConf->AUTH="adld")&&$SAMSConf->NTLMDOMAIN=="Y")
        LTableCell($row['domain'],20);
      if($SAMSConf->access==2)
        {
	  if($SAMSConf->realtraffic=="real")
          	$aaa=FormattedString($row['user_size']-$row['hit_size']);
	  else
          	$aaa=FormattedString($row['user_size']);
          RTableCell($aaa,20);
        }	 
     
      if($SAMSConf->realtraffic=="real")
      	RTableCell(ReturnTrafficFormattedSize($row['user_size']-$row['hit_size']),25);
      else
      	RTableCell(ReturnTrafficFormattedSize($row['user_size']),25);
      RTableCell($row['percent'],15);
      $ap=$ap+$row['percent'];
      $count++;
    }

  print("<TR><TD>");
  RBTableCell("$usersbuttom_4_percent_UsersPercentTraffic_7",20);
  if(($SAMSConf->AUTH="ntlm"||$SAMSConf->AUTH="adld")&&$SAMSConf->NTLMDOMAIN=="Y")
    print("<TD>");
  if($SAMSConf->access==2)
    {
      $aaa=FormattedString("$all"); 
      RBTableCell($aaa,20);
    }   
  RBTableCell(ReturnTrafficFormattedSize($all),25);
  RBTableCell("$ap %",15);

  print("</TABLE>\n");

  if($SAMSConf->SHOWGRAPH=="Y")
    printf("<P><IMG SRC=\"main.php?show=exe&function=groupspercenttrafficgb&filename=usersbuttom_4_percent.php&gb=1&sdate=$sdate&edate=$edate \"><P>");

  print("<P><TABLE   CLASS=samstable>");
  print("<TR><TH>No");
  if($SAMSConf->access==2)
    print("<TH>$usersbuttom_4_percent_UsersPercentTraffic_8");
  print("<TH>$usersbuttom_4_percent_UsersPercentTraffic_5");
  print("<TH>$usersbuttom_4_percent_UsersPercentTraffic_6");
  print("<TH>%");

  $result=mysql_query("SELECT groups.nick,sum(user_size) as grp_size,sum(hit_size) as grp_hit FROM $SAMSConf->SAMSDB.squidusers,$SAMSConf->SAMSDB.groups,cache_ where cache_.user=squidusers.nick && squidusers.group=groups.name group by groups.nick order by grp_size DESC");
  
  $count=1;
  while($row=mysql_fetch_array($result))
    {
      $grname=$row['nick'];
      print("<TR>");
      LTableCell($count,5);
      LTableCell($row['nick'],20);
      
      if($SAMSConf->access==2)
        {
 	   if($SAMSConf->realtraffic=="real")
              $aaa=FormattedString($row['grp_size']-$row['grp_hit']);
	   else
              $aaa=FormattedString($row['grp_size']);
          RTableCell($aaa,30);
        }	 
      if($SAMSConf->realtraffic=="real")
          RTableCell(ReturnTrafficFormattedSize($row['grp_size']-$row['grp_hit']),35);
      else
          RTableCell(ReturnTrafficFormattedSize($row['grp_size']),35);
      $aaa=round($row['grp_size']/$percent,2);
      RTableCell($aaa,15);
      $count++;
    }
}


function UsersPercentTrafficForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access==0)     {      exit;    }
  
  PageTop("usergroup_48.jpg","$usersbuttom_4_percent_UsersPercentTrafficForm_1<BR>$usersbuttom_4_percent_UsersPercentTrafficForm_2");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userspercenttraffic\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_4_percent.php\">\n");
  NewDateSelect(0,"");
  print("</FORM>\n");

}


function usersbuttom_4_percent()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access>0)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=userspercenttrafficform&filename=usersbuttom_4_percent.php","basefrm","persent_32.jpg","persent_48.jpg","$usersbuttom_4_percent_usersbuttom_4_percent_1");
    }
}

?>
