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
  global $DATE;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if(isset($_GET["sort"])) $sort=$_GET["sort"];
   if(isset($_GET["desc"])) $desc=$_GET["desc"];
	if(isset($_GET["id"])) $id=$_GET["id"];
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

	$QUERY="SELECT sum(c.s_size) as all_sum, sum(c.s_hit) as hit_sum, c.s_user, c.s_domain, s.s_nick, s.s_family, s.s_name, s.s_user_id FROM cachesum c, squiduser s WHERE c.s_user=s.s_nick AND c.s_date>='$sdate' AND c.s_date<='$edate' AND s.s_group_id='$id' GROUP BY c.s_user, c.s_domain, s.s_nick, s.s_family, s.s_name, s.s_user_id ORDER BY all_sum DESC";

	$num_rows=$DB->samsdb_query_value($QUERY);

  $count=0;
  while($row=$DB->samsdb_fetch_array())
       {
	 $SIZE[$count]=floor($row[0]/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
	 $HIT[$count]=floor($row[1]/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
	 $USERS[$count]=$row["s_user"];
	 $count++;
	}
$showbar=new BAR(500, 200, 30, 20, $SIZE, $HIT, $count, $USERS);
$showbar->CreateBars();
       
}

 
 
function GroupTrafficPeriod()
{
  global $SAMSConf;
  global $USERConf;
  global $DATE;

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
   if(isset($_GET["id"])) $id=$_GET["id"];

  require("reportsclass.php");
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	$a = array(array($usersbuttom_2_traffic_UsersTrafficForm_4,'all_sum','desc','CHECKED' ), array($usersbuttom_2_traffic_UsersTrafficForm_5,'s_nick','',''));

  $num_rows=$DB->samsdb_query_value("SELECT * FROM sgroup WHERE s_group_id='$id' ");
  $row=$DB->samsdb_fetch_array();
  PageTop("usergroup_48.jpg","$grptraffic_1 <FONT COLOR=\"BLUE\"> $row[s_name] </FONT><BR> $groupbuttom_1_traffic_GroupTrafficPeriod_1");
  print("<BR>\n");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"grouptrafficperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"groupbuttom_2_traffic.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$id\">\n");
  $dateselect->SetPeriod2("$usersbuttom_2_traffic_UsersTrafficForm_3", $a);
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

    printf("<P><IMG SRC=\"main.php?show=exe&function=grouptrafficperiodgb&filename=groupbuttom_2_traffic.php&gb=1&sdate=$sdate&edate=$edate&sort=$sort&desc=$desc&id=$id \"><P>");
  
  $count=1;
  $size2=0;
  $hitsize=0;
  $traf=0;

  print("<script type=\"text/javascript\" src=\"lib/jquery-1.2.6.js\"></script>");
  print("<script type=\"text/javascript\" src=\"lib/jquery.dataTables.js\"></script>\n");
  print("<script type=\"text/javascript\">\n");

  print("$(document).ready(function(){\n");
  print("  $(\"#userstraffic\").dataTable({\n");
  print("	\"bInfo\": 0,\n");
  print("	\"iDisplayLength\": 100,\n");
  print("	\"iDisplayStart\": 0,\n");
  print("	\"iDisplayEnd\": 100,\n");
  print("	\"oLanguage\": {\n");	
  print("		\"sSearch\": \"search\", \n");
  print("		\"sLengthMenu\": \"Show _MENU_ entries\"\n");
  print("		},\n");
  print("	\"aoColumns\": [ \n");
  print("		{ \"sType\": \"numeric\" },\n");
  print("		{ \"sType\": \"html\" },\n");
  print("		{ \"sType\": \"formatted-num\" },\n");
  print("		{ \"sType\": \"formatted-num\" },\n");
  print("		{ \"sType\": \"formatted-num\" }\n");
  print("    ]\n");
  print("  });\n");
  print("});\n");
  print("</script>\n");

  print("<TABLE CLASS=samstable id=\"userstraffic\">\n");

	$item=array("head"=> "squid",
		"access" => "pobject.gif",
		"target"=> "tray",
		"url"=> "tray.php?show=exe&filename=squidtray.php&function=squidtray",
		"text"=> "SQUID");

  print("<THEAD>\n");
  print("<TH width=8%>No\n");
  print("<TH width=16%>$usersbuttom_2_traffic_UsersTrafficPeriod_4\n");
  if($size=="On")
    {
      print("<TH width=16%>$usersbuttom_2_traffic_UsersTrafficPeriod_8\n");
    }
  else
    {  
      if(($SAMSConf->AUTH="ntlm"||$SAMSConf->AUTH="adld")&&$SAMSConf->NTLMDOMAIN=="Y")
        print("<TH width=16%>Domain\n");
    }  
  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
      print("<TH width=15%>$usersbuttom_2_traffic_UsersTrafficPeriod_6\n");
      print("<TH width=15%>$usersbuttom_2_traffic_UsersTrafficPeriod_5\n");
    }
  print("<TH width=30%>$usersbuttom_2_traffic_UsersTrafficPeriod_7\n");
  print("</THEAD>\n");
  print("<TBODY>\n");

	$QUERY="SELECT sum(c.s_size) as all_sum, sum(c.s_hit) as hit_sum, c.s_user, c.s_domain, s.s_nick, s.s_family, s.s_name, s.s_user_id FROM cachesum c, squiduser s WHERE c.s_user=s.s_nick AND c.s_date>='$sdate' AND c.s_date<='$edate' AND s.s_group_id='$id' GROUP BY c.s_user, c.s_domain, s.s_nick, s.s_family, s.s_name, s.s_user_id ORDER BY all_sum DESC";

	$num_rows=$DB->samsdb_query_value($QUERY);
	while($row=$DB->samsdb_fetch_array())
	{
		print("<TR>\n");
		LTableCell($count,8);
		if($SAMSConf->SHOWNAME=="fam")
			$name="$row[s_family]";
		else if($SAMSConf->SHOWNAME=="famn")
			$name="$row[s_family] $row[s_name]";
		else if($SAMSConf->SHOWNAME=="nickd")
			$name="$row[s_nick] / $row[s_domain]";
		else 
			$name="$row[s_nick]";
		$str="<A HREF=\"tray.php?show=exe&filename=usertray.php&function=usertray&id=$row[s_user_id]\" TARGET=\"tray\">$name</A>\n";
		LTableCell($str,16);
	 
		if($size=="On")
		{
			LTableCell($row['s_family'],16);
		}
		else
		{
			if(($SAMSConf->AUTH="ntlm"||$SAMSConf->AUTH="adld")&&$SAMSConf->NTLMDOMAIN=="Y")
				TableCell($row['s_domain'],16);
		}
		if($USERConf->ToWebInterfaceAccess("C")==1)
		{
			$aaa=FormattedString("$row[0]");
			RTableCell($aaa,15);
			$aaa=FormattedString("$row[1]");
			RTableCell($aaa,15);
		}   
		if($SAMSConf->realtraffic=="real")
			PrintFormattedSize($row[0]-$row[1]);
		else
			PrintFormattedSize($row[0]);
         
		print("</TR>\n");
		$count=$count+1;
		$size2=$size2+$row[0];
		$hitsize=$hitsize+$row[1];
	}
  print("</TBODY>\n");
  print("<TFOOT>\n");
  print("<TR>\n");
  print("<TD>");
  RBTableCell("$vsego",16);
  if((($SAMSConf->AUTH="ntlm"||$SAMSConf->AUTH="adld")&&$SAMSConf->NTLMDOMAIN=="Y")||$size=="On")
    print("<TD>");
  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
      $aaa=FormattedString("$size2");
      RBTableCell($aaa,15);
      $aaa=FormattedString("$hitsize");
      RBTableCell($aaa,15);
    }   
  if($SAMSConf->realtraffic=="real")
    PrintFormattedSize($size2 - $hitsize);
  else
    PrintFormattedSize($size2);
  
  print("</TFOOT>\n");
  print("</TABLE>\n");


}



/****************************************************************/
function GroupTrafficForm()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("CS")!=1)
	exit(0);

  require("reportsclass.php");
  $dateselect=new DATESELECT("","");
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
	$DB=new SAMSDB();

	if(isset($_GET["id"])) $id=$_GET["id"];

	$a = array(array($usersbuttom_2_traffic_UsersTrafficForm_4,'all_sum','desc','CHECKED' ), array($usersbuttom_2_traffic_UsersTrafficForm_5,'s_nick','',''));

	$num_rows=$DB->samsdb_query_value("SELECT * FROM sgroup WHERE s_group_id='$id' ");
	$row=$DB->samsdb_fetch_array();
	PageTop("traffic_48.jpg","$grptraffic_1 <FONT COLOR=\"BLUE\"> ".$row['s_name']." </FONT><BR> $groupbuttom_1_traffic_GroupTrafficForm_1");

	print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"grouptrafficperiod\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"groupbuttom_2_traffic.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$id\">\n");
	$dateselect->SetPeriod2("$usersbuttom_2_traffic_UsersTrafficForm_3", $a);
	print("</FORM>\n");


}


function groupbuttom_2_traffic()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("CSG")==1)
  {

	if(isset($_GET["id"])) $id=$_GET["id"];
	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

       GraphButton("main.php?show=exe&function=grouptrafficform&filename=groupbuttom_2_traffic.php&id=$id","basefrm","traffic_32.jpg","traffic_48.jpg","$groupbuttom_1_traffic_groupbuttom_1_traffic_1");
  }

}




?>
