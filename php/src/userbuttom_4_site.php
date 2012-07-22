<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ShowLoadingFilesFromDomain()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  global $SquidUSERConf;

  if(isset($_GET["id"])) $id=$_GET["id"];
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);

  require("reportsclass.php");
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["url"])) $url=$_GET["url"];
  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["userdomain"])) $userdomain=$_GET["userdomain"];
  if(isset($_GET["userid"])) $userid=$_GET["userid"];
  if(isset($_GET["usergroup"])) $usergroup=$_GET["usergroup"];

	if($USERConf->ToWebInterfaceAccess("GSC")!=1 && ($USERConf->s_user_id != $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")!=1 ) )
	{
		exit(0);
	}

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();
  $sday=$DATE->sday;
  $smon=$DATE->smon;
  $syea=$DATE->syea;
  $shou=$DATE->shou;
  $eday=$DATE->eday;
  $emon=$DATE->emon;
  $eyea=$DATE->eyea;
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());

  PageTop("straffic_48.jpg","$userbuttom_4_site_SiteUserList_1 $url <BR>$userbuttom_4_site_SiteUserList_6 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT>");
  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  $QUERY="select * from squidcache where lower(s_user)=lower('".$SquidUSERConf->s_nick."') AND s_url like '%$url%' AND s_date>='$sdate' AND s_date<='$edate' ORDER BY s_url";
  $num_rows=$DB->samsdb_query_value($QUERY);
  print("<TABLE CLASS=samstable>");
  print("<TH WIDTH=10%>Date");
  print("<TH WIDTH=10%>$usersbuttom_2_traffic_UsersTrafficPeriod_4");
  print("<TH WIDTH=60%>URL");
  print("<TH WIDTH=10%>$usersbuttom_2_traffic_UsersTrafficPeriod_7");
  print("<TH WIDTH=10%>$usersbuttom_2_traffic_UsersTrafficPeriod_5");
	while($row=$DB->samsdb_fetch_array())
	{
		echo "<TR>\n";
		RTableCell($row['s_date'],10);
		RTableCell($row['s_user'],10);
		LTableCell(" ".$row['s_url'],60);
		RTableCell(FormattedString($row['s_size']),10);
		RTableCell(FormattedString($row['s_hit']),10);

       }

}


function UserSitesPeriod()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  global $SquidUSERConf;

  if(isset($_GET["id"])) $id=$_GET["id"];
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);

  require("reportsclass.php");
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["userdomain"])) $userdomain=$_GET["userdomain"];
  if(isset($_GET["userid"])) $userid=$_GET["userid"];
  if(isset($_GET["usergroup"])) $usergroup=$_GET["usergroup"];

	if($USERConf->ToWebInterfaceAccess("GSC")!=1 && ($USERConf->s_user_id != $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")!=1 ) )
	{
		exit(0);
	}

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();
  $sday=$DATE->sday;
  $smon=$DATE->smon;
  $syea=$DATE->syea;
  $shou=$DATE->shou;
  $eday=$DATE->eday;
  $emon=$DATE->emon;
  $eyea=$DATE->eyea;
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());

  PageTop("straffic_48.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT><BR>$userbuttom_4_site_UserSitesPeriod_2");
  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userssitesperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_3_site.php\">\n");
  $dateselect->SetPeriod();
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");
 
  print("<CENTER>\n");
  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

	$URL=array("url_domain"=>array(),
		"norm_url"=>array(),
		"url_size"=>array(),
		"hit_size"=>array(),
		"sum_size"=>array());
	$count=0;
	$cache=0; 
	$counter=0;
	$url_domain="";
	$url_size_sum=0;
	$hit_size_sum=0;
	$query="SELECT substring( s_url from position('//' in s_url)+2 for position('/' in substring(s_url from position('/' in s_url)+2 )) ) as url_domain,sum(s_size) as url_size,sum(s_hit) as hit_size  FROM squidcache WHERE lower(s_user)=lower('". $SquidUSERConf->s_nick ."') AND s_date>='$sdate'AND s_date<='$edate' AND s_method!='CONNECT' GROUP BY url_domain ORDER BY url_size desc limit 5000";
	$num_rows=$DB->samsdb_query_value($query);
	while($row=$DB->samsdb_fetch_array())
	{
		if(strlen($row['url_domain'])>0)
		{
			$url_domain=explode( ".",str_replace("/","",$row['url_domain']));
			$ucount=count($url_domain);
			$URL["url_domain"][$count]=$url_domain[$ucount-2].".".$url_domain[$ucount-1];
			$URL["norm_url"][$count]=str_replace("/","",$row['url_domain']);
			$URL["url_size"][$count]=$row['url_size'];
			$URL["hit_size"][$count]=$row['hit_size'];
			$url_size_sum+=$row['url_size'];
			$hit_size_sum+=$row['hit_size'];
			if($SAMSConf->realtraffic=="real")
				$URL["sum_size"][$count]=$row['url_size']-$row['hit_size'];
			else
				$URL["sum_size"][$count]=$row['url_size'];
			$count++;
		}
       }

	$query="SELECT substring( s_url from 0 for position(':' in s_url) ) as url_domain,sum(s_size) as url_size,sum(s_hit) as hit_size  FROM squidcache WHERE lower(s_user)=lower('". $SquidUSERConf->s_nick ."') AND s_date>='$sdate'AND s_date<='$edate' AND s_method='CONNECT' GROUP BY url_domain ORDER BY url_domain desc limit 5000";
	$num_rows=$DB->samsdb_query_value($query);
	while($row=$DB->samsdb_fetch_array())
	{
		if(strlen($row['url_domain'])>0)
		{
			$url_domain=explode( ".",str_replace("/","",$row['url_domain']));
			$ucount=count($url_domain);
			$URL["url_domain"][$count]=$url_domain[$ucount-2].".".$url_domain[$ucount-1];
			$URL["norm_url"][$count]=str_replace("/","",$row['url_domain']);
			$URL["url_size"][$count]=$row['url_size'];
			$URL["hit_size"][$count]=$row['hit_size'];
			$url_size_sum+=$row['url_size'];
			$hit_size_sum+=$row['hit_size'];
			if($SAMSConf->realtraffic=="real")
				$URL["sum_size"][$count]=$row['url_size']-$row['hit_size'];
			else
				$URL["sum_size"][$count]=$row['url_size'];
			$count++;
		}
       }
	asort($URL["url_size"]);
	reset($URL["url_size"]);

  print("<script type=\"text/javascript\" src=\"lib/jquery-1.2.6.js\"></script>\n");
  print("<script type=\"text/javascript\" src=\"lib/jquery.dataTables.js\"></script>\n");
  print("<script type=\"text/javascript\">\n");
  print("$(document).ready(function(){\n");
  print("  $(\"#urltraffic\").dataTable({\n");
  print("	\"bInfo\": 0,\n");
  print("	\"iDisplayLength\": 1000,\n");
  print("	\"iDisplayStart\": 0,\n");
  print("	\"iDisplayEnd\": 1000,\n");
  print("	\"oLanguage\": {\n");	
  print("		\"sSearch\": \"search\", \n");
  print("		\"sLengthMenu\": \"Show _MENU_ entries\"\n");
  print("		},\n");
  print("	\"aoColumns\": [ \n");
  print("		{ \"sType\": \"numeric\", \"sWidth\": \"8%\" },\n");
  print("		{ \"sType\": \"html\", \"sWidth\": \"16%\"},\n");
  print("		{ \"sType\": \"formatted-num\", \"sWidth\": \"15%\" },\n");
  print("		{ \"sType\": \"formatted-num\", \"sWidth\": \"15%\" },\n");
  print("		{ \"sType\": \"formatted-num\", \"sWidth\": \"30%\" }\n");
  print("    ]\n");
  print("  });\n");
  print("});\n");
  print("</script>\n");
  print("</CENTER>\n");

echo "<style type=\"text/css\"> \n";
echo ".sorting_asc { background: url('$SAMSConf->ICONSET/sort_asc.png') no-repeat center right; } \n";
echo ".sorting_desc {	background: url('$SAMSConf->ICONSET/sort_desc.png') no-repeat center right; } \n";
echo ".sorting { background: url('$SAMSConf->ICONSET/sort_both.png') no-repeat center right; } \n";
echo ".sorting_asc_disabled { background: url('$SAMSConf->ICONSET/sort_asc_disabled.png') no-repeat center right; } \n";
echo ".sorting_desc_disabled { background: url('$SAMSConf->ICONSET/sort_desc_disabled.png') no-repeat center right; } \n";
echo "</style> \n";


  print("<TABLE CLASS=\"samstable\" id=\"urltraffic\">");
  print("<THEAD><TR>\n");
  print("<TH>No\n");
  print("<TH>URL\n");
  if($USERConf->ToWebInterfaceAccess("C")==1)
  {
      print("<TH>$userbuttom_2_traffic_UserTrafficPeriod_3\n");
      print("<TH>$userbuttom_2_traffic_UserTrafficPeriod_4\n");
   }   
  print("<TH>$userbuttom_2_traffic_UserTrafficPeriod_5\n");
  print("</TR></THEAD>\n");
  print("<TBODY>\n");
  $cc=1;
	while (list($key, $val) = each($URL["url_domain"])) 
	{
		print("<TR>\n");
		print("<TD>$cc</TD>\n");
		RTableCell($URL['norm_url'][$key],40);
//		RTableCell("<A HREF=\"main.php?show=exe&filename=usersbuttom_3_site.php&function=siteuserslist&SDay=$sday&SMon=$smon&SYea=$syea&EDay=$eday&EMon=$emon&EYea=$eyea&site=".$URL["norm_url"][$key]."\" TARGET=\"BLANK\">" .$URL["norm_url"][$key]."</A>\n",15);
		RTableCell(FormattedString($URL['url_size'][$key]),15);
		RTableCell(FormattedString($URL['hit_size'][$key]),15);
		RTableCell(FormattedString($URL['sum_size'][$key]),15);
		print("</TR>\n");
		$cc++;
	}
	print("</TBODY>\n");
	print("<TFOOT><TR>\n");
	print("<TD colspan=2> $vsego");
	PrintFormattedSize($url_size_sum);
	PrintFormattedSize($hit_size_sum);
	PrintFormattedSize($url_size_sum-$hit_size_sum);
	print("</TFOOT></TABLE>\n");

}
function UserSitesPeriod_new()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  global $SquidUSERConf;

  if(isset($_GET["id"])) $id=$_GET["id"];
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);

  require("reportsclass.php");
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["userdomain"])) $userdomain=$_GET["userdomain"];
  if(isset($_GET["userid"])) $userid=$_GET["userid"];
  if(isset($_GET["usergroup"])) $usergroup=$_GET["usergroup"];

	if($USERConf->ToWebInterfaceAccess("GSC")!=1 && ($USERConf->s_user_id != $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")!=1 ) )
	{
		exit(0);
	}

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();
  $sday=$DATE->sday;
  $smon=$DATE->smon;
  $syea=$DATE->syea;
  $shou=$DATE->shou;
  $eday=$DATE->eday;
  $emon=$DATE->emon;
  $eyea=$DATE->eyea;
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());

  PageTop("straffic_48.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT><BR>$userbuttom_4_site_UserSitesPeriod_2");
  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usersitesperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_4_site.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=id value=\"$SquidUSERConf->s_user_id\">\n");
	$dateselect->SetPeriod();
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  print("<TABLE CLASS=samstable>");
  print("<TH WIDTH=6%>No");
  print("<TH WIDTH=20%>Domain");
  print("<TH WIDTH=20%>URL");
  if($USERConf->ToWebInterfaceAccess("C")==1)
  {
      print("<TH WIDTH=15%>$userbuttom_4_site_UserSitesPeriod_3");
      print("<TH WIDTH=15%>$userbuttom_4_site_UserSitesPeriod_4");
   }   
  print("<TH WIDTH=15%>$userbuttom_4_site_UserSitesPeriod_5");

  $URL=array("url_domain"=>array(),
		"norm_url"=>array(),
		"url_size"=>array(),
		"hit_size"=>array(),
		"sum_size"=>array());

  $query="SELECT substring( s_url from position('//' in s_url)+2 for position('/' in substring(s_url from position('/' in s_url)+2 )) ) as url_domain,sum(s_size) as url_size,sum(s_hit) as hit_size  FROM squidcache WHERE lower(s_user)=lower('". $SquidUSERConf->s_nick ."') AND  s_date>='$sdate'AND s_date<='$edate' AND s_method!='CONNECT' GROUP BY url_domain ORDER BY url_domain desc limit 25000";
  $num_rows=$DB->samsdb_query_value($query);
  $count=0;
  $cache=0; 
  $counter=0;
  $url_domain="";

	while($row=$DB->samsdb_fetch_array())
	{
		if(strlen($row['url_domain'])>0)
		{
			$url_domain=explode( ".",str_replace("/","",$row['url_domain']));
			$ucount=count($url_domain);
			$URL["url_domain"][$count]=$url_domain[$ucount-2].".".$url_domain[$ucount-1];
			$URL["norm_url"][$count]=str_replace("/","",$row['url_domain']);
			$URL["url_size"][$count]=$row['url_size'];
			$URL["hit_size"][$count]=$row['hit_size'];
//			if($SAMSConf->realtraffic=="real")
				$URL["sum_size"][$count]=$row['url_size']-$row['hit_size'];
//			else
//				$URL["sum_size"][$count]=$row['url_size'];
			$count++;
		}
       }

  $query="SELECT substring( s_url from 0 for position(':' in s_url) ) as url_domain,sum(s_size) as url_size,sum(s_hit) as hit_size  FROM squidcache WHERE lower(s_user)=lower('". $SquidUSERConf->s_nick ."') AND  s_date>='$sdate'AND s_date<='$edate' AND s_method='CONNECT' GROUP BY url_domain ORDER BY url_domain desc limit 25000;";
  $num_rows=$DB->samsdb_query_value($query);
	while($row=$DB->samsdb_fetch_array())
	{
		if(strlen($row['url_domain'])>0)
		{
			$url_domain=explode( ".",str_replace("/","",$row['url_domain']));
			$ucount=count($url_domain);
			$URL["url_domain"][$count]=$url_domain[$ucount-2].".".$url_domain[$ucount-1];
			$URL["norm_url"][$count]=str_replace("/","",$row['url_domain']);
			$URL["url_size"][$count]=$row['url_size'];
			$URL["hit_size"][$count]=$row['hit_size'];
//			if($SAMSConf->realtraffic=="real")
				$URL["sum_size"][$count]=$row['url_size']-$row['hit_size'];
//			else
//				$URL["sum_size"][$count]=$row['url_size'];
			$count++;
		}
       }

	asort($URL["url_domain"]);
	reset($URL["url_domain"]);
	$count=0;
	$url_size_value=0;
	$hit_size_value=0;
	$sum_size_value=0;
	$url_domain_size_value=0;
	$hit_domain_size_value=0;
	$sum_domain_size_value=0;
	while (list($key, $val) = each($URL["url_domain"])) 
	{
		print("<TR>");
		if($url_domain!=$val)
		{
			if ($count!=0)
			{
				echo "<TD colspan=3> ";
				RBTableCell(FormattedString($url_domain_size_value),15);
				RBTableCell(FormattedString($hit_domain_size_value),15);
				RBTableCell(FormattedString($sum_domain_size_value),15);
				$url_domain_size_value=0;
				$hit_domain_size_value=0;
				$sum_domain_size_value=0;
				echo "<TR>";
			}
			$q=$count+1;
			echo "<TD>$q\n";
			if (ctype_alpha($val[strlen($val)-1])==TRUE)
				print("<TD  colspan=5><A HREF=\"http://$val\" TARGET=\"BLANK\"><B>$val</B></A>\n");
			else
				print("<TD  colspan=5><A HREF=\"http://".$URL["norm_url"][$key]."\" TARGET=\"BLANK\"><B>".$URL["norm_url"][$key]."</B></A>\n");
			$url_domain=$val;
			$count++;
			print("<TR>");
			print("<TD colspan=2>\n");

			RTableCell("<A HREF=\"main.php?show=exe&filename=userbuttom_4_site.php&function=showloadingfilesfromdomain&id=$id&SDay=$sday&SMon=$smon&SYea=$syea&EDay=$eday&EMon=$emon&EYea=$eyea&url=".$URL["norm_url"][$key]."\" TARGET=\"BLANK\" >" .$URL["norm_url"][$key]."</A>\n",15);

			RTableCell(FormattedString($URL["url_size"][$key]),15);
			RTableCell(FormattedString($URL["hit_size"][$key]),15);
			RTableCell(FormattedString($URL["sum_size"][$key]),15);
			$url_size_value+=$URL["url_size"][$key];
			$hit_size_value+=$URL["hit_size"][$key];
			$sum_size_value+=$URL["sum_size"][$key];
			$url_domain_size_value+=$URL["url_size"][$key];
			$hit_domain_size_value+=$URL["hit_size"][$key];
			$sum_domain_size_value+=$URL["sum_size"][$key];

		}
		else
		{
			print("<TD colspan=2>\n");
			RTableCell("<A HREF=\"main.php?show=exe&filename=userbuttom_4_site.php&function=showloadingfilesfromdomain&id=$id&SDay=$sday&SMon=$smon&SYea=$syea&EDay=$eday&EMon=$emon&EYea=$eyea&url=".$URL["norm_url"][$key]."\" TARGET=\"BLANK\" >" .$URL["norm_url"][$key]."</A>\n",15);
			RTableCell(FormattedString($URL["url_size"][$key]),15);
			RTableCell(FormattedString($URL["hit_size"][$key]),15);
			RTableCell(FormattedString($URL["sum_size"][$key]),15);
			$url_size_value+=$URL["url_size"][$key];
			$hit_size_value+=$URL["hit_size"][$key];
			$sum_size_value+=$URL["sum_size"][$key];
			$url_domain_size_value+=$URL["url_size"][$key];
			$hit_domain_size_value+=$URL["hit_size"][$key];
			$sum_domain_size_value+=$URL["sum_size"][$key];
		}
	}
	print("<TR>");
	echo "<TD colspan=3> ";
	RBTableCell(FormattedString($url_domain_size_value),15);
	RBTableCell(FormattedString($hit_domain_size_value),15);
	RBTableCell(FormattedString($sum_domain_size_value),15);
	print("<TR>");
	print("<TD colspan=3>\n");
//	RBTableCell(FormattedString($url_size_value),15);
	PrintFormattedSize($url_size_value);
//	RBTableCell(FormattedString($hit_size_value),15);
	PrintFormattedSize($hit_size_value);
//	RBTableCell(FormattedString($sum_size_value),15);
//	if($SAMSConf->realtraffic=="real")
		PrintFormattedSize($url_size_value - $hit_size_value);
//	else
//		PrintFormattedSize($url_size_value);
	print("</TABLE>");


}


/****************************************************************/
function UserSitesForm()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;

  if(isset($_GET["id"])) $id=$_GET["id"];
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);


  require("reportsclass.php");
  $dateselect=new DATESELECT("","");
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("GSC")==1 || ($USERConf->s_user_id == $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")==1 ) )
	{

		PageTop("straffic_48.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT><BR>$userbuttom_4_site_UserSitesForm_1");

		print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usersitesperiod\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_4_site.php\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=id value=\"$SquidUSERConf->s_user_id\">\n");
		$dateselect->SetPeriod();
		print("</FORM>\n");
	}
}



function userbuttom_4_site()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("WAUCS")==1 || $USERConf->ToGroupStatAccess("G", $SquidUSERConf->s_group_id))
	{
		GraphButton("main.php?show=exe&function=usersitesform&filename=userbuttom_4_site.php&id=$SquidUSERConf->s_user_id","basefrm","straffic_32.jpg","straffic_48.jpg","$userbuttom_4_site_userbuttom_4_site_1");
	}

}

?>
