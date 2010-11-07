<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UsersTrafficPeriodGB()
{
  require('lib/chart.php');
  
  global $SAMSConf;
  global $DATE;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

	$QUERY="SELECT sum(cachesum.s_size),sum(cachesum.s_hit),cachesum.s_user,cachesum.s_domain, squiduser.s_nick, squiduser.s_family, squiduser.s_name, squiduser.s_user_id FROM cachesum LEFT JOIN squiduser ON cachesum.s_user=squiduser.s_nick WHERE cachesum.s_date>='$sdate'AND cachesum.s_date<='$edate' GROUP BY cachesum.s_user,cachesum.s_domain,squiduser.s_nick,squiduser.s_family, squiduser.s_name, squiduser.s_user_id  order by sum(cachesum.s_size) desc";
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

 
 
function UsersTrafficPeriod()
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

  require("reportsclass.php");
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("usergroup_48.jpg","$usersbuttom_2_traffic_UsersTrafficPeriod_1<BR>$usersbuttom_2_traffic_UsersTrafficPeriod_2");
  print("<BR>\n");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userstrafficperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_2_traffic.php\">\n");
  $dateselect->SetPeriod();
 
 print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

    printf("<P><IMG SRC=\"main.php?show=exe&function=userstrafficperiodgb&filename=usersbuttom_2_traffic.php&gb=1&sdate=$sdate&edate=$edate\"><P>");
  
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
  print("		{ \"sType\": \"numeric\", \"sWidth\": \"8%\" },\n");
  print("		{ \"sType\": \"html\", \"sWidth\": \"16%\"},\n");
  print("		{ \"sType\": \"formatted-num\", \"sWidth\": \"15%\" },\n");
  print("		{ \"sType\": \"formatted-num\", \"sWidth\": \"15%\" },\n");
  print("		{ \"sType\": \"formatted-num\", \"sWidth\": \"30%\" }\n");
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
  print("<TH>No\n");
  print("<TH>$usersbuttom_2_traffic_UsersTrafficPeriod_4\n");
  if($size=="On")
    {
      print("<TH>$usersbuttom_2_traffic_UsersTrafficPeriod_8\n");
    }
  else
    {  
      if(($SAMSConf->AUTH="ntlm"||$SAMSConf->AUTH="adld")&&$SAMSConf->NTLMDOMAIN=="Y")
        print("<TH>Domain\n");
    }  
  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
      print("<TH>$usersbuttom_2_traffic_UsersTrafficPeriod_6\n");
      print("<TH>$usersbuttom_2_traffic_UsersTrafficPeriod_5\n");
    }
  print("<TH>$usersbuttom_2_traffic_UsersTrafficPeriod_7\n");
  print("</THEAD>\n");
  print("<TBODY>\n");

	$QUERY="SELECT sum(cachesum.s_size),sum(cachesum.s_hit),cachesum.s_user,cachesum.s_domain, squiduser.s_nick, squiduser.s_family, squiduser.s_name, squiduser.s_user_id FROM cachesum LEFT JOIN squiduser ON cachesum.s_user=squiduser.s_nick WHERE cachesum.s_date>='$sdate'AND cachesum.s_date<='$edate' GROUP BY cachesum.s_user,cachesum.s_domain,squiduser.s_nick,squiduser.s_family, squiduser.s_name, squiduser.s_user_id  order by sum(cachesum.s_size) desc";

	$num_rows=$DB->samsdb_query_value($QUERY);
	while($row=$DB->samsdb_fetch_array())
	{
		print("<TR>\n");
		//LTableCell($count,8);
		print("<TD>$count");
		if($SAMSConf->SHOWNAME=="fam")
			$name="$row[s_family]";
		else if($SAMSConf->SHOWNAME=="famn")
			$name="$row[s_family] $row[s_name]";
		else if($SAMSConf->SHOWNAME=="nickd")
			$name="$row[s_nick] / $row[s_domain]";
		else 
			$name=$row['s_nick'];
		$str="<A HREF=\"tray.php?show=exe&filename=usertray.php&function=usertray&id=$row[s_user_id]\" TARGET=\"tray\">$name </A>\n";
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
			LTableCell("$aaa",15);
			$aaa=FormattedString("$row[1]");
			LTableCell("$aaa",15);
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
  print("<TFOOT><TR>\n");
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
  
  print("</TFOOT></TABLE>\n");


}



/****************************************************************/
function UsersTrafficForm()
{
  global $SAMSConf;
  global $USERConf;

	if($USERConf->ToWebInterfaceAccess("CS")!=1)
		exit(0);

	require("reportsclass.php");
	$dateselect=new DATESELECT("","");
	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);
	if(isset($_GET["userid"])) $userid=$_GET["userid"];

	PageTop("traffic_48.jpg","$alltraffic_1<BR>$usersbuttom_2_traffic_UsersTrafficForm_1");

	print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userstrafficperiod\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_2_traffic.php\">\n");
	$dateselect->SetPeriod();
	print("</FORM>\n");


}


function usersbuttom_2_traffic()
{
  global $SAMSConf;
  global $USERConf;
  
  if($USERConf->ToWebInterfaceAccess("CS")==1)
  {

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

       GraphButton("main.php?show=exe&function=userstrafficform&filename=usersbuttom_2_traffic.php","basefrm","traffic_32.jpg","traffic_48.jpg","$usersbuttom_2_traffic_usersbuttom_2_traffic_1");
  }

}




?>
