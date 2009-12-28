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
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["site"])) $site=$_GET["site"];
  if(isset($_GET["username"])) $username=$_GET["username"];

 
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  if($SAMSConf->access==0&&$SAMSConf->domainusername !=$username)
	exit(0);
  PageTop("usergroup_48.jpg","$userbuttom_4_site_SiteUserList_1 <BR>$site $username");
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
  $result2=mysql_query("SELECT * FROM redirect");
  while($row2=mysql_fetch_array($result2))
      {
       print("<OPTION VALUE=$row2[filename]> $row2[name]");
      }
  print("</SELECT>\n");
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
  $result=mysql_query("SELECT date,user,size,url FROM cache WHERE date>=\"$sdate\"&&date<=\"$edate\"&&user=\"$username\"&&url like \"%$site%\" ORDER BY size desc limit 250");
  $counter=0;
  while($row=mysql_fetch_array($result))
       {
         print("<TR>");
	 LTableCell("$row[date]",10);
         LTableCell("$row[user]",20);
         $aaa=FormattedString("$row[size]");
         RTableCell($aaa,20);
	 if($SAMSConf->realtraffic=="real")
           PrintFormattedSize($row['size']-$row['hit']);
	 else
	   PrintFormattedSize($row['size']);
	 print("<TD ALIGN=\"LEFT\" bgcolor=blanchedalmond onclick=EditURL(\"$row[url]\")> &nbsp;$row[3] \n");
       }
   print("</TABLE>");
}


function UserSitesPeriod()
{
  global $SAMSConf;
  global $DATE;
  
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

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  PageTop("user.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$username</FONT><BR>$userbuttom_4_site_UserSitesPeriod_2");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"username\" id=UserName value=\"$username\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userdomain\" id=UserDomain value=\"$userdomain\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usersitesperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_4_site.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userid\" id=userid value=\"$userid\">\n");
  NewDateSelect(0,"");
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  print("<TABLE CLASS=samstable>");
  print("<TH>No");
  if($SAMSConf->access==2)
    {
      print("<TH>");
      print("<TH>$userbuttom_4_site_UserSitesPeriod_4");
   }   
  print("<TH>$userbuttom_4_site_UserSitesPeriod_5");
  print("<TH>URL");

  $count=1;
  $query="select trim(leading \"http://\" from substring_index(url,'/',3)) as norm_url,sum(size) as url_size,sum(hit) as hit_size from cache where user=\"$username\"&&domain=\"$userdomain\"&&date>=\"$sdate\"&&date<=\"$edate\" group by norm_url order by url_size desc limit 25000";

  $result=mysql_query("$query");
  $cache=0; 
  $counter=0;
  while($row=mysql_fetch_array($result))
       {
         print("<TR>");
         LTableCell($count,8);
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
	   
         if($SAMSConf->access==2)
           TableCell("<A TARGET=\"BLANK\" HREF=\"main.php?show=exe&function=siteuserlist&filename=userbuttom_4_site.php&site=$row[norm_url]&SDay=$sday&EDay=$eday&SMon=$smon&EMon=$emon&SYea=$syea&EYea=$eyea&username=$username\" ><FONT COLOR=\"BLACK\">$row[norm_url]</FONT></A>");
         if($SAMSConf->access==1)
           TableCell("<A HREF=\"http://$row[norm_url]\" TARGET=\"BLANK\">$row[norm_url]</A>\n");
         if($SAMSConf->access==0&&$SAMSConf->URLACCESS=="Y")
           TableCell("<A HREF=\"http://$row[norm_url]\" TARGET=\"BLANK\">$row[norm_url]</A>\n");

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
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["userid"])) $userid=$_GET["userid"];

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);

  PageTop("user.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$row[1]</FONT><BR>$userbuttom_4_site_UserSitesForm_1");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"username\" id=UserName value=\"$row[1]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userdomain\" id=UserDomain value=\"$row[6]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"usergroup\" id=UserGroup value=\"$row[group]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usersitesperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_4_site.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userid\" id=userid value=\"$userid\">\n");
  NewDateSelect(0,"");
  print("</FORM>\n");

}



function userbuttom_4_site($userid)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);

   if($SAMSConf->access>0||($SAMSConf->USERACCESS=="Y"&&$SAMSConf->domainusername=="$row[nick]")||$SAMSConf->groupauditor==$row[group])
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=usersitesform&filename=userbuttom_4_site.php&userid=$userid","basefrm","straffic_32.jpg","straffic_48.jpg","$userbuttom_4_site_userbuttom_4_site_1");
	}

}




?>
