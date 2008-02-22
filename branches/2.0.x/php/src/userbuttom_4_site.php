<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function SiteUserList()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  require("reportsclass.php");
  $DB=new SAMSDB("$SAMSConf->DB_ENGINE", "0", $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB);
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["site"])) $site=$_GET["site"];
 
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  if($SAMSConf->access==0&&$SAMSConf->domainusername !=$username)
	exit(0);
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
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"urllistfunction.php\">\n");
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
  $num_rows=$DB->samsdb_query_value("SELECT * FROM redirect");
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

  mysql_select_db($SAMSConf->LOGDB);
  $num_rows=$DB->samsdb_query_value("SELECT s_date,s_user,s_size,s_url FROM squidcache WHERE s_date>='$sdate'&&s_date<='$edate'&&s_user='$USERConf->s_nick'&&s_url like '%$site%' ORDER BY s_size desc limit 250");
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


function UserSitesPeriod()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  require("reportsclass.php");
  $DB=new SAMSDB("$SAMSConf->DB_ENGINE", "0", $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB);
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["userdomain"])) $userdomain=$_GET["userdomain"];
  if(isset($_GET["userid"])) $userid=$_GET["userid"];
  if(isset($_GET["usergroup"])) $usergroup=$_GET["usergroup"];

  if($SAMSConf->access==0 && $SAMSConf->domainusername!=$username && $SAMSConf->groupauditor!=$usergroup && strlen($SAMSConf->adminname)==0)
    exit(0);

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

  PageTop("user.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$USERConf->s_nick</FONT><BR>$userbuttom_4_site_UserSitesPeriod_2");
  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usersitesperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_4_site.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=id value=\"$USERConf->s_user_id\">\n");
	$dateselect->SetPeriod();
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  print("<TABLE CLASS=samstable>");
  print("<TH>No");
  print("<TH>Domain");
  print("<TH>URL");
if($SAMSConf->access==2)
    {
      print("<TH>");
      print("<TH>$userbuttom_4_site_UserSitesPeriod_4");
   }   
  print("<TH>$userbuttom_4_site_UserSitesPeriod_5");

  $count=1;
  //$query="select trim(leading \"http://\" from substring_index(url,'/',3)) as norm_url,sum(size) as url_size,sum(hit) as hit_size from cache where user=\"$SAMSConf->s_nick\"&&domain=\"$SAMSConf->s_domain\"&&date>=\"$sdate\"&&date<=\"$edate\" group by norm_url order by url_size desc limit 25000";
  $query="select trim(leading \"http://\" from substring_index(s_url,'/',3)) as norm_url,sum(s_size) as url_size,sum(s_hit) as hit_size, substring_index(trim(leading \"http://\" from substring_index(s_url,'/',3)),'.',-2) as url_domain from squidcache where s_user='$USERConf->s_nick'&&s_domain='$USERConf->s_domain'&&s_date>='$sdate'&&s_date<='$edate' group by norm_url order by url_domain,s_url desc limit 25000";

  $num_rows=$DB->samsdb_query_value("$query");
  $cache=0; 
  $counter=0;
  $url_domain="";
  while($row=$DB->samsdb_fetch_array())
       {
         print("<TR>");

	if($url_domain!=$row['url_domain'])
	{
		print("<TD>\n");
		print("<TD  colspan=5><A HREF=\"http://$row[url_domain]\" TARGET=\"BLANK\"><B>$row[url_domain]</B></A>\n");
		$url_domain=$row['url_domain'];
		print("<TR>\n");
	}
//	else/
//	{
		LTableCell($count,8);
		print("<TD>\n");
//	}
	   
         if($SAMSConf->access==2)
           TableCell("<A TARGET=\"BLANK\" HREF=\"main.php?show=exe&function=siteuserlist&filename=userbuttom_4_site.php&site=$row[norm_url]&SDay=$sday&EDay=$eday&SMon=$smon&EMon=$emon&SYea=$syea&EYea=$eyea&id=$USERConf->s_user_id\" ><FONT COLOR=\"BLACK\">$row[norm_url]</FONT></A>");
         if($SAMSConf->access==1)
           TableCell("<A HREF=\"http://$row[norm_url]\" TARGET=\"BLANK\">$row[norm_url]</A>\n");
         if($SAMSConf->access==0&&$SAMSConf->URLACCESS=="Y")
           TableCell("<A HREF=\"http://$row[norm_url]\" TARGET=\"BLANK\">$row[norm_url]</A>\n");

         if($SAMSConf->access==2)
           {
             $aaa=FormattedString("$row[url_size]\n");
             RTableCell("$aaa",15);
             $aaa=FormattedString("$row[hit_size]\n");
             RTableCell("$aaa",15);
           }
	 if($SAMSConf->realtraffic=="real")
	   PrintFormattedSize($row['url_size']-$row['hit_size']);
	 else
	   PrintFormattedSize($row['url_size']);

         $count=$count+1;
         $counter=$counter+$row['url_size'];
         $cache=$cache+$row['hit_size'];
       }
 print("<TR><TD>");
 if($SAMSConf->access==2)
    {
      $aaa=FormattedString($counter);
      RBTableCell($aaa,15);
      $aaa=FormattedString($cache);
      RBTableCell($aaa,15);
   }      
 if($SAMSConf->realtraffic=="real")
   PrintFormattedSize($counter-$cache);
 else
   PrintFormattedSize($counter);
   
 print("<TD>");
 print("</TABLE>");


}


/****************************************************************/
function UserSitesForm()
{
  global $SAMSConf;
  global $USERConf;
  require("reportsclass.php");
  $dateselect=new DATESELECT();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("user.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$USERConf->s_nick</FONT><BR>$userbuttom_4_site_UserSitesForm_1");

	print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usersitesperiod\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_4_site.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=id value=\"$USERConf->s_user_id\">\n");
	$dateselect->SetPeriod();
	print("</FORM>\n");

}



function userbuttom_4_site($userid)
{
  global $SAMSConf;
  global $USERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($SAMSConf->access>0 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "AUC")==1)
	{
		print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
		GraphButton("main.php?show=exe&function=usersitesform&filename=userbuttom_4_site.php&id=$USERConf->s_user_id","basefrm","straffic_32.jpg","straffic_48.jpg","$userbuttom_4_site_userbuttom_4_site_1");
	}

}

?>
