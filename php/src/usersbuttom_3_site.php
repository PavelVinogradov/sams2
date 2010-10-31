<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
function AddUrltoList()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  require("reportsclass.php");
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["addurl"])) $addurl=$_GET["addurl"];
  if(isset($_GET["type"])) $type=$_GET["type"];

  $QUERY="INSERT INTO url (s_redirect_id, s_url) VALUES ('$type', '$addurl')";
  $num_rows=$DB->samsdb_query_value($QUERY);

  echo "$redir_addurl1 $redir_addurl2 $addurl";
}

function SiteUsersList()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  require("reportsclass.php");
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["site"])) $site=$_GET["site"];
  if(isset($_GET["id"])) $id=$_GET["id"];
 
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

	if($USERConf->ToWebInterfaceAccess("GSC")!=1 && ($USERConf->s_user_id != $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")!=1 ) )
	{
		exit(0);
	}
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());

  PageTop("usergroup_48.jpg","$userbuttom_4_site_SiteUserList_1 <BR>$site $USERConf->s_nick");
  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  print("<script language=JAVASCRIPT>\n");
  print("function EditURL(URL)\n");
  print("{\n");
  print("document.forms[\"REDIRECT\"].elements[\"addurl\"].value=URL;\n");
  print("}\n");
  print("function CloseWindow()\n");
  print("{\n");
  print("this.document.forms[\"REDIRECT\"].submit();\n");
  print("window.close;\n");
  print("}\n");
  print("</script>\n");

  print("<FORM NAME=\"REDIRECT\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addurltolist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"usersbuttom_3_site.php\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_4_site_SiteUserList_2:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"addurl\" SIZE=30> \n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_4_site_SiteUserList_3:\n");
  print("<TD>\n");
  print("<SELECT NAME=\"type\" >\n");
  $num_rows=$DB->samsdb_query_value("SELECT s_redirect_id,s_name FROM redirect");
  while($row2=$DB->samsdb_fetch_array())
      {
       print("<OPTION VALUE=$row2[s_redirect_id]> $row2[s_name]");
      }
  print("</SELECT>\n");
  $DB->free_samsdb_query();
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$userbuttom_4_site_SiteUserList_4\" onsubmit=CloseWindow()>\n");

  print("</FORM>\n");

  print("<P><TABLE CLASS=samstable>");
  print("<TR>");
  print("<TH>$grptraffic_2</b></TD>");
  print("<TH>$userbuttom_4_site_SiteUserList_6");
  print("<TH>$userbuttom_4_site_SiteUserList_7");
  print("<TH>$userbuttom_4_site_SiteUserList_7");
  print("<TH>URL");

	$QUERY="SELECT s_date,s_user,s_size,s_url FROM squidcache WHERE s_date>='$sdate' AND  s_date<='$edate' AND s_url like '%$site%' ORDER BY s_size desc limit 250";

  $num_rows=$DB->samsdb_query_value($QUERY);
  $counter=0;
  while($row=$DB->samsdb_fetch_array())
       {
         print("<TR>");
	 LTableCell("$row[s_date]",10);
         LTableCell("$row[s_user]",20);
         $aaa=FormattedString("$row[s_size]");
         RTableCell($aaa,20);
	 if($SAMSConf->realtraffic=="real")
           PrintFormattedSize($row['s_size']-$row['s_hit']);
	 else
	   PrintFormattedSize($row['s_size']);
	 print("<TD ALIGN=\"LEFT\" bgcolor=blanchedalmond onclick=EditURL(\"$row[s_url]\")> &nbsp;$row[3] \n");
       }
   print("</TABLE>");
}


function UsersSitesPeriod()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  global $SquidUSERConf;

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

  PageTop("straffic_48.jpg","$alltraffic_1 <BR>$groupbuttom_3_site_GroupSitesPeriod_1");
  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userssitesperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_3_site.php\">\n");
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

  $query="SELECT substring( s_url from position('//' in s_url)+2 for position('/' in substring(s_url from position('/' in s_url)+2 )) ) as url_domain,sum(s_size) as url_size,sum(s_hit) as hit_size  FROM squidcache WHERE s_date>='$sdate'AND s_date<='$edate' AND s_method!='CONNECT' GROUP BY url_domain ORDER BY url_domain desc limit 25000";

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
			if($SAMSConf->realtraffic=="real")
				$URL["sum_size"][$count]=$row['url_size']-$row['hit_size'];
			else
				$URL["sum_size"][$count]=$row['url_size'];
			$count++;
		}
       }

  $query="SELECT substring( s_url from 0 for position(':' in s_url) ) as url_domain,sum(s_size) as url_size,sum(s_hit) as hit_size  FROM squidcache WHERE s_date>='$sdate'AND s_date<='$edate' AND s_method='CONNECT' GROUP BY url_domain ORDER BY url_domain desc limit 25000";

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
			if($SAMSConf->realtraffic=="real")
				$URL["sum_size"][$count]=$row['url_size']-$row['hit_size'];
			else
				$URL["sum_size"][$count]=$row['url_size'];
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
				echo "<TD colspan=3>";
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
			print("<TD colspan=2> \n");
			RTableCell("<A HREF=\"main.php?show=exe&filename=usersbuttom_3_site.php&function=siteuserslist&SDay=$sday&SMon=$smon&SYea=$syea&EDay=$eday&EMon=$emon&EYea=$eyea&site=".$URL["norm_url"][$key]."\" TARGET=\"BLANK\">" .$URL["norm_url"][$key]."</A>\n",15);
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
			RTableCell("<A HREF=\"main.php?show=exe&filename=usersbuttom_3_site.php&function=siteuserslist&SDay=$sday&SMon=$smon&SYea=$syea&EDay=$eday&EMon=$emon&EYea=$eyea&site=".$URL["norm_url"][$key]."\" TARGET=\"BLANK\">" .$URL["norm_url"][$key]."</A>\n",15);
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
	RBTableCell(FormattedString($url_size_value),15);
	RBTableCell(FormattedString($hit_size_value),15);
	if($SAMSConf->realtraffic=="real")
		PrintFormattedSize($url_size_value - $hit_size_value);
	else
		PrintFormattedSize($url_size_value);
	print("</TABLE>");


}


/****************************************************************/
function UsersSitesForm()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;

  require("reportsclass.php");
  $dateselect=new DATESELECT("","");
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("GSC")==1 || ($USERConf->s_user_id == $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")==1 ) )
	{

		PageTop("straffic_48.jpg","$alltraffic_1 <BR>$groupbuttom_3_site_GroupSitesPeriod_1");

		print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userssitesperiod\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_3_site.php\">\n");
		$dateselect->SetPeriod();
		print("</FORM>\n");
	}
}



function usersbuttom_3_site()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("WAUCS")==1 || $USERConf->ToGroupStatAccess("G", $SquidUSERConf->s_group_id))
	{
		GraphButton("main.php?show=exe&function=userssitesform&filename=usersbuttom_3_site.php","basefrm","straffic_32.jpg","straffic_48.jpg","$userbuttom_4_site_userbuttom_4_site_1");
	}

}

?>
