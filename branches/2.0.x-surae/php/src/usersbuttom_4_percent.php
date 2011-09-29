<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
 

function UsersChartGB()
{
  require('lib/piegraph.class.php');
  
  global $SAMSConf;
  global $DATE;
  $DB=new SAMSDB();
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();
   $size="";
   if(isset($_GET["size"])) $size=$_GET["size"];
   if(isset($_GET["sort"])) $sort=$_GET["sort"];
   if(isset($_GET["desc"])) $desc=$_GET["desc"];

	$QUERY="SELECT sum(s_size) as user_size FROM cachesum WHERE s_date>='$sdate' AND s_date<='$edate'";
	$num_rows=$DB->samsdb_query_value($QUERY);
	$row=$DB->samsdb_fetch_array();
	$full_utraffic_size=$row['user_size'];
	$DB->free_samsdb_query();
 	 $colors=array("#FF0000","#0000FF","#FFFF00","#009000","#C3690F","#CC009C","#99CC33","#3C3CFF","#C9960C","#FF6F00","#C6C63C","#CC033C","#00FFFF","#636F66","#6FFF00","#96693F","#009C9C","#FFCC00","#9F9F6F","#CC60CC");
	$usercolors=array();

	if($sort=="users")
		$QUERY="SELECT c.s_user,sum(c.s_size) as user_size,sum(c.s_hit) as hit_size, s.s_user_id as s_id FROM cachesum c, squiduser s WHERE c.s_user=s.s_nick AND c.s_date>='$sdate' AND c.s_date<='$edate' GROUP BY c.s_user, s.s_user_id ORDER BY user_size DESC;";
	else
		$QUERY="SELECT sum.sum_name as s_user,sum(sum.sum_size) as user_size, sum(sum.sum_hit) as hit_size, sum.sum_group_id as s_id FROM ( SELECT sum(c.s_size) as sum_size, sum(c.s_hit) as sum_hit, c.s_user as sum_user, s.s_group_id as sum_group_id, g.s_name as sum_name  FROM cachesum c, squiduser s, sgroup g WHERE c.s_user=s.s_nick AND s.s_group_id=g.s_group_id AND s_date>='$sdate' AND s_date<='$edate' GROUP BY c.s_user, s.s_group_id, g.s_name ORDER BY g.s_name ) as sum GROUP BY s_user, sum.sum_group_id;";

	$num_rows=$DB->samsdb_query_value($QUERY);
	$count=0;
	$sum_size=0;
	$sum_hit=0;
	$sum_pc=0;
	$another_utraffic_size=0;
	while($row=$DB->samsdb_fetch_array())
	{
		if($row['user_size']>=($full_utraffic_size/100))
		{
			if($count<count($colors))
			{
				$usercolors[$count]=$colors[$count];
			}
			else
			{
				$r=mt_rand(10,240);
				$g=mt_rand(10,240);
				$b=mt_rand(10,240);
				$usercolors[$count]="#".sprintf("%X%X%X",mt_rand(20,200),mt_rand(20,200),mt_rand(20,200));
			}
			$user[$count]=$row['s_user'];
			$size[$count]=$row['user_size'];
			$count++;
		}
		else
		{
			$another_utraffic_size+=$row['user_size'];
		}
	}
	if($another_utraffic_size>0)
	{
		$user[$count]="another users";
		$size[$count]=$another_utraffic_size;
		$count++;
	}

	$pie = new PieGraph(300, 200, $size);
	$pie->setColors($usercolors);
	// legends for the data
	$pie->setLegends($user);
	// Display creation time of the graph
	$pie->DisplayCreationTime();
	// Height of the pie 3d effect
	$pie->set3dHeight(15);
	// Display the graph
	$pie->display();
}



function UsersChart()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("CS")!=1)
	exit(0);

  $DB=new SAMSDB();
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

  $a = array(array('users','users','desc','CHECKED' ), array('groups','groups','',''));

  PageTop("persent_48.jpg","$usersbuttom_4_percent_UsersPercentTraffic_1<BR>$usersbuttom_4_percent_UsersPercentTraffic_2");


  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userschart\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_4_percent.php\">\n");
  $dateselect->SetPeriod2("select sort mode", $a);
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

	printf("<P><IMG SRC=\"main.php?show=exe&function=userschartgb&filename=usersbuttom_4_percent.php&gb=1&sdate=$sdate&edate=$edate&sort=$sort \"><P>");
  
	if($sort=="users")
		$QUERY="SELECT c.s_user,sum(c.s_size) as user_size,sum(c.s_hit) as hit_size, s.s_user_id as s_id FROM cachesum c, squiduser s WHERE c.s_user=s.s_nick AND c.s_date>='$sdate' AND c.s_date<='$edate' GROUP BY c.s_user, s.s_user_id ORDER BY user_size DESC;";
	else
		$QUERY="SELECT sum(sum.sum_size) as user_size, sum(sum.sum_hit) as hit_size, sum.sum_name as s_user, sum.sum_group_id as s_id FROM ( SELECT sum(c.s_size) as sum_size, sum(c.s_hit) as sum_hit, c.s_user as sum_user, s.s_group_id as sum_group_id, g.s_name as sum_name  FROM cachesum c, squiduser s, sgroup g WHERE c.s_user=s.s_nick AND s.s_group_id=g.s_group_id AND s_date>='$sdate' AND s_date<='$edate' GROUP BY c.s_user, s.s_group_id, g.s_name ORDER BY g.s_name ) as sum GROUP BY s_user, sum.sum_group_id;";
		
	$num_rows=$DB->samsdb_query_value($QUERY);
	$count=0;
	$sum_size=0;
	$sum_hit=0;
	$sum_pc=0;
	while($row=$DB->samsdb_fetch_array())
	{
		$user[$count]=$row['s_user'];
		$size[$count]=$row['user_size'];
		$userid[$count]=$row['s_id'];
		$sum_size+=$size[$count];
		$hit[$count]=$row['hit_size'];
		$sum_hit+=$hit[$count];
		$count++;
	}

	print("<TABLE CLASS=samstable>");
	print("<TH width=8%>No");
	print("<TH width=16%>$usersbuttom_2_traffic_UsersTrafficPeriod_4");
	print("<TH width=16%>$usersbuttom_2_traffic_UsersTrafficPeriod_6");
	print("<TH width=16%>$usersbuttom_2_traffic_UsersTrafficPeriod_5");
	print("<TH width=16%>%");

	for($i=0;$i<$count;$i++)
	{
		print("<TR>");
		LTableCell($i,8);
		if($sort=="users")
			LTableCell("<A HREF=\"tray.php?show=exe&filename=usertray.php&function=usertray&auth=adld&id=".$userid[$i]."\" TARGET=\"tray\" >".$user[$i]."</A>",16);
		else
			LTableCell("<A HREF=\"tray.php?show=exe&filename=grouptray.php&function=grouptray&id=".$userid[$i]."\" TARGET=\"tray\" >".$user[$i]."</A>",16);

		RTableCell(FormattedString($size[$i]),16);
		RTableCell(FormattedString($hit[$i]),16);
		$pc[$i]=round($size[$i]/($sum_size/100),2);
		$sum_pc+=$pc[$i];
		RTableCell($pc[$i],16);

	}	
	print("<TR><TD><TD>");
	RBTableCell(FormattedString($sum_size),16);
	RBTableCell(FormattedString($sum_hit),16);
	print("<TD>");
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

  $a = array(array('users','users','desc','CHECKED' ), array('groups','groups','',''));

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
