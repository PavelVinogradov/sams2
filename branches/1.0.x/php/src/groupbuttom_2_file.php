<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function GroupFileSizePeriod()
{
  global $SAMSConf;
  global $DATE;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["groupname"])) $groupname=$_GET["groupname"];
  if(isset($_GET["size"])) $filesize=$_GET["size"];

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  $aaa=ReturnGroupNick($groupname);
  PageTop("usergroup_48.jpg","$grptraffic_1 <FONT COLOR=\"BLUE\">$aaa</FONT><BR>$groupbuttom_2_file_GroupFileSizePeriod_1 $filesize kb");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" id=UserName value=\"$groupname\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"groupfilesizeperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"groupbuttom_2_file.php\">\n");
  NewDateSelect(1,"$groupbuttom_2_file_GroupFileSizePeriod_2");
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");


  $filesize=($filesize*$SAMSConf->KBSIZE)-1;
  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  print("<TABLE CLASS=samstable>");
  print("<TH>No");
  print("<TH>User");
  print("<TH>$grptraffic_2");
  print("<TH>$groupbuttom_2_file_GroupFileSizePeriod_3");
  print("<TH>URL");
  $count=0;

  $query="SELECT cache.user,cache.date,cache.size,cache.url, tu.name, tu.family, tu.nick, cache.domain FROM cache , $SAMSConf->SAMSDB.squidusers AS tu WHERE tu.nick=cache.user&&tu.domain=cache.domain&&tu.group=\"$groupname\"&&date>=\"$sdate\"&&date<=\"$edate\"&&cache.size>=\"$filesize\" order by cache.size desc limit 250";
  $result=mysql_query($query);
//  while($row=mysql_fetch_array($result))
//     {
    for($i=0;$i<mysql_num_rows($result);$i++)
       {
         $row=mysql_fetch_array($result);
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
//	 LTableCell($row[0],12);
	 
	 $aaa=ReturnDate($row[1],12);
         LTableCell($aaa,12);
         $aaa=FormattedString($row[2]);
         RTableCell($aaa,15);
         LTableCell($row[3],37);
         $count=$count+1;
     }
  mysql_free_result($result);
  print("</TABLE>");

}



/****************************************************************/
function GroupFileSizeForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["groupname"])) $groupname=$_GET["groupname"];

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM groups WHERE name=\"$groupname\" ");
  $row=mysql_fetch_array($result);

  PageTop("usergroup_48.jpg","$grptraffic_1 <FONT COLOR=\"BLUE\"> $row[nick] </FONT><BR> $groupbuttom_2_file_GroupFileSizeForm_1");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" id=UserName value=\"$row[name]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"groupfilesizeperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"groupbuttom_2_file.php\">\n");
  NewDateSelect(1,"$groupbuttom_2_file_GroupFileSizeForm_2");
  print("</FORM>\n");

}




function groupbuttom_2_file($groupname)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $result=mysql_query("SELECT * FROM groups WHERE name=\"$groupname\" ");
  $row=mysql_fetch_array($result);

  if($SAMSConf->access>0||$SAMSConf->groupauditor==$row[name])
    {
      print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
      GraphButton("main.php?show=exe&function=groupfilesizeform&filename=groupbuttom_2_file.php&groupname=$groupname","basefrm","ftraffic_32.jpg","ftraffic_48.jpg","$groupbuttom_2_file_groupbuttom_2_file_1");
    }
}

?>
