<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function SiteGroupList()
{

  global $DATE;
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["site"])) $site=$_GET["site"];

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  //print("site=$site $sdate $edate $bdate $eddate");

  PageTop("usergroup_48.jpg","$groupbuttom_3_site_SiteGroupList_1 <BR>$site");
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
  print("<B>$groupbuttom_3_site_SiteGroupList_2:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"addurl\" SIZE=30> \n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$groupbuttom_3_site_SiteGroupList_3:\n");
  print("<TD>\n");
  print("<SELECT NAME=\"type\" >\n");
  $result2=mysql_query("SELECT * FROM redirect");
  while($row2=mysql_fetch_array($result2))
      {
       print("<OPTION VALUE=$row2[filename]> $row2[name]");
      }
  print("</SELECT>\n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$groupbuttom_3_site_SiteGroupList_4\" onsubmit=CloseWindow()>\n");

  print("</FORM>\n");

  print("<P><TABLE CLASS=samstable>");
  print("<TH>$grptraffic_2");
  print("<TH>$userbuttom_4_site_SiteUserList_6");
  print("<TH>$groupbuttom_3_site_SiteGroupList_7");
  print("<TH>URL");

  mysql_select_db($SAMSConf->LOGDB);
  $result=mysql_query("SELECT date,user,size,url FROM cache WHERE date>=\"$sdate\"&&date<=\"$edate\"&&url like \"%$site%\" ORDER BY size desc limit 250");

  while($row=mysql_fetch_array($result))
       {
         print("<TR>");
	 LTableCell("$row[date]",10);
         LTableCell("$row[user]",20);
         $aaa=FormattedString("$row[size]");
         RTableCell($aaa,20);
         print("<TD ALIGN=\"RIGHT\" onclick=EditURL(\"$row[url]\")>$row[url]\n");
       }
  print("</TABLE>");
}

function GroupSitesPeriod()
{
  global $SAMSConf;
  global $DATE;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["groupname"])) $groupname=$_GET["groupname"];

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


  $SAMSConf->access=UserAccess();
  $aaa=ReturnGroupNick($groupname);
  PageTop("usergroup_48.jpg","$grptraffic_1 $aaa<BR>$groupbuttom_3_site_GroupSitesPeriod_1");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" id=UserName value=\"$groupname\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"groupsitesperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"groupbuttom_3_site.php\">\n");
  NewDateSelect(0,"");
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  print("<TABLE CLASS=samstable>");
  print("<TR>");
  print("<TH>No");
  print("<TH>$groupbuttom_3_site_GroupSitesPeriod_2");
  print("<TH>URL");

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM squidusers WHERE squidusers.group=\"$groupname\"");

  $count=0;
  while($row=mysql_fetch_array($result))
     {
         $username[$count]=$row['nick'];
         $domain[$count]=$row['domain'];
         $count++;
     }
  mysql_free_result($result);

  $filesize=($filesize*$SAMSConf->KBSIZE)-1;

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);
  $result=mysql_query("CREATE TEMPORARY TABLE cache_ SELECT tc.* FROM ".$SAMSConf->LOGDB.".cache AS tc, ".$SAMSConf->SAMSDB.".squidusers AS tu WHERE tc.user = tu.nick AND tc.domain = tu.domain AND tu.group=\"$groupname\" AND tc.date>=\"$sdate\" AND tc.date<=\"$edate\"");
  $result=mysql_query("UPDATE cache_ SET url=SUBSTRING_INDEX(url,'/',3) ");

  $result2=mysql_query("SELECT url, SUM(size) AS sum_size FROM cache_ GROUP BY url ORDER BY sum_size DESC");
  $count = 1;
  while($row=mysql_fetch_array($result2))
       {
         print("<TR>");
         LTableCell($count,8);
         $aaa=FormattedString("$row[sum_size]");
         RTableCell($aaa,20);

         if($SAMSConf->access==2)
           TableCell("<A TARGET=BLANK HREF=\"main.php?show=exe&function=sitegrouplist&filename=groupbuttom_3_site.php&site=$row[url]&SDay=$sday&EDay=$eday&SMon=$smon&EMon=$emon&SYea=$syea&EYea=$eyea \">$row[url]</A>\n");
         if($SAMSConf->access!=2)
           TableCell("<A HREF=\"$row[url]\">$row[url]</A>\n");
         $count++;
	 $size=$size+$row['sum_size'];
       }
  print("<TR>");
  RBTableCell("$vsego:",8);
  $aaa=FormattedString("$size");
  RBTableCell($aaa,20);
  print("<TD>");
  print("</TABLE>");


}


/****************************************************************/
function GroupSitesForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["groupname"])) $groupname=$_GET["groupname"];
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM groups WHERE name=\"$groupname\" ");
  $row=mysql_fetch_array($result);


  PageTop("usergroup_48.jpg","$grptraffic_1 <FONT COLOR=\"BLUE\">$row[nick]</FONT><BR>$groupbuttom_3_site_GroupSitesForm_1");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" id=UserName value=\"$row[name]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"groupsitesperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"groupbuttom_3_site.php\">\n");
  NewDateSelect(0,"");
  print("</FORM>\n");

}






function groupbuttom_3_site($groupname)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $result=mysql_query("SELECT * FROM groups WHERE name=\"$groupname\" ");
  $row=mysql_fetch_array($result);

  if($SAMSConf->access>0||$SAMSConf->groupauditor==$row[name])
    {
      print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
      GraphButton("main.php?show=exe&function=groupsitesform&filename=groupbuttom_3_site.php&groupname=$groupname","basefrm","straffic_32.jpg","straffic_48.jpg","$groupbuttom_3_site_groupbuttom_3_site_1");
    }
}

?>
