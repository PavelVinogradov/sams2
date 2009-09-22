<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
 
 

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
$circlesize=15;
$circlesize=100;
//   $circle=new CIRCLE3D(500, $count*$circlesize, $SIZE, $count, $USERS);
   $circle=new CIRCLE3D(500, 250, $SIZE, $count, $USERS);
   $circle->ShowCircle();
}

function UsersChartGB()
{
  require('lib/chart.php');
  
  global $SAMSConf;
  global $DATE;
  $DB=new SAMSDB(&$SAMSConf);
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();
   $size="";
   if(isset($_GET["size"])) $size=$_GET["size"];
   if(isset($_GET["sort"])) $sort=$_GET["sort"];
   if(isset($_GET["desc"])) $desc=$_GET["desc"];


	$QUERY="SELECT s_user,s_domain,sum(s_size) as user_size,sum(s_hit) as hit_size FROM cachesum WHERE s_date>='$sdate' AND s_date<='$edate' group by s_user,s_domain,s_size,s_hit";
	$num_rows=$DB->samsdb_query_value($QUERY);
	$count=0;
	$sum_size=0;
	$sum_hit=0;
	$sum_pc=0;
	while($row=$DB->samsdb_fetch_array())
	{
		$user[$count]=$row['s_user'];
		$size[$count]=$row['user_size'];
		$sum_size+=$size[$count];
		$hit[$count]=$row['hit_size'];
		$sum_hit+=$hit[$count];
		$count++;
	}


  $circle=new CIRCLE3D(500, $count*15, $size, $count, $user);
  $circle->ShowCircle();
}


function UsersChart()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("CS")!=1)
	exit(0);

  $DB=new SAMSDB(&$SAMSConf);
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();
   $size="";
   if(isset($_GET["size"])) $size=$_GET["size"];
   if(isset($_GET["sort"])) $sort=$_GET["sort"];
   if(isset($_GET["desc"])) $desc=$_GET["desc"];

  require("reportsclass.php");
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $a = array(array('users','all_sum','desc','CHECKED' ), array('groups','s_nick','',''));

  
  PageTop("persent_48.jpg","$usersbuttom_4_percent_UsersPercentTraffic_1<BR>$usersbuttom_4_percent_UsersPercentTraffic_2");


  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userschart\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_4_percent.php\">\n");
  $dateselect->SetPeriod2("select sort mode", $a);
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

	printf("<P><IMG SRC=\"main.php?show=exe&function=userschartgb&filename=usersbuttom_4_percent.php&gb=1&sdate=$sdate&edate=$edate \"><P>");
  

	$QUERY="SELECT s_user,s_domain,sum(s_size) as user_size,sum(s_hit) as hit_size FROM cachesum WHERE s_date>='$sdate' AND s_date<='$edate' group by s_user,s_domain,s_size,s_hit";
	$num_rows=$DB->samsdb_query_value($QUERY);
	$count=0;
	$sum_size=0;
	$sum_hit=0;
	$sum_pc=0;
	while($row=$DB->samsdb_fetch_array())
	{
		$user[$count]=$row['s_user'];
		$size[$count]=$row['user_size'];
		$sum_size+=$size[$count];
		$hit[$count]=$row['hit_size'];
		$sum_hit+=$hit[$count];
		$count++;
	}

	print("<TABLE CLASS=samstable>");
	print("<TH width=8%>No");
	print("<TH width=16%>$usersbuttom_2_traffic_UsersTrafficPeriod_4");

	for($i=0;$i<$count;$i++)
	{
		print("<TR>");
		LTableCell($i,8);
		LTableCell($user[$i],16);
		LTableCell($size[$i],16);
		LTableCell($hit[$i],16);
		$pc[$i]=round($size[$i]/($sum_size/100),2);
		$sum_pc+=$pc[$i];
		LTableCell($pc[$i],16);

	}	
	print("<TR><TD><TD>");
	LTableCell($sum_size,16);
	LTableCell($sum_hit,16);
	LTableCell($sum_pc,16);
	print("</TABLE>");

}


function UsersPercentTrafficForm()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("CS")!=1)
	exit(0);

  require("reportsclass.php");
  $dateselect=new DATESELECT("","");
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $a = array(array('users','all_sum','desc','CHECKED' ), array('groups','s_nick','',''));

  PageTop("persent_48.jpg","$usersbuttom_4_percent_UsersPercentTrafficForm_1<BR>$usersbuttom_4_percent_UsersPercentTrafficForm_2");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userschart\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_4_percent.php\">\n");
  $dateselect->SetPeriod2("select sort mode", $a);
  print("</FORM>\n");

}


function usersbuttom_4_percent()
{
  global $SAMSConf;
  global $USERConf;
  
  if($USERConf->ToWebInterfaceAccess("CS")==1)
  {
  
	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	GraphButton("main.php?show=exe&function=userspercenttrafficform&filename=usersbuttom_4_percent.php","basefrm","persent_32.jpg","persent_48.jpg","$usersbuttom_4_percent_usersbuttom_4_percent_1");
   }
}

?>
