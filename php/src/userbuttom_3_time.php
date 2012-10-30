<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */


function UserTimeContent()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  global $SquidUSERConf;

  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["url"])) $url=$_GET["url"];
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);

	if($USERConf->ToWebInterfaceAccess("GSC")!=1 && ($USERConf->s_user_id != $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")!=1 ) )
	{
		exit(0);
	}

  $DB=new SAMSDB();
 
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();
  require("reportsclass.php");
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("WAUC")!=1)
	exit(0);


  PageTop("ttraffic_48.jpg","$traffic_1 <FONT COLOR=\"BLUE\"> $SquidUSERConf->s_nick</FONT><BR>content");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=id value=\"$SquidUSERConf->s_user_id\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usertimetraffic\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_3_time.php\">\n");
	$dateselect->SetDate();
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> \n");

  $count=1;
  $cache=0;
  print("<TABLE CLASS=samstable WIDTH=80%>");
  print("<THEAD>\n");
  print("<TH>Time");
  print("<TH>URL");
  print("<TH>SIZE");
  print("<TH>HIT");
  print("</THEAD>\n");
  print("<TBODY>\n");
  $size=0;

  $QUERY="SELECT s_time, s_url, s_size, s_hit FROM squidcache WHERE s_user='".$SquidUSERConf->s_nick."' AND s_date='$sdate' AND s_url like ('%$url%') ORDER BY s_time";
  $num_rows=$DB->samsdb_query_value($QUERY);


  while($row=$DB->samsdb_fetch_array())
       {
         print("<TR>");
         LTableCell($row['s_time'],15);
	 if($USERConf->ToWebInterfaceAccess("C")==1)
           {
		LTableCell($row['s_url'],85);
		RTableCell($row['s_size'],85);
		RTableCell($row['s_hit'],85);
	   }   
	 
         print("</TR>");
       }
  print("</TABLE>");
}


function UserTimeTraffic()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  global $SquidUSERConf;

  if(isset($_GET["id"])) $id=$_GET["id"];
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);

	if($USERConf->ToWebInterfaceAccess("GSC")!=1 && ($USERConf->s_user_id != $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")!=1 ) )
	{
		exit(0);
	}

  $DB=new SAMSDB();
 
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
  require("reportsclass.php");
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("WAUC")!=1)
	exit(0);


  PageTop("ttraffic_48.jpg","$traffic_1 <FONT COLOR=\"BLUE\"> $SquidUSERConf->s_nick</FONT><BR>$URLTimeForm_userbuttom_4_time_1<BR>$bdate");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=id value=\"$SquidUSERConf->s_user_id\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usertimetraffic\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_3_time.php\">\n");
	$dateselect->SetDate();
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  $URL=array("time"=>array(),
	"url"=>array(),
	"method"=>array());
  $count=0;
  $QUERY="SELECT * FROM (SELECT substring(s_time from 1 for 5) as time  , substring( s_url from position('//' in s_url)+2 for position('/' in substring(s_url from position('/' in s_url)+2 )) ) as url_domain, s_method FROM squidcache WHERE s_user='".$SquidUSERConf->s_nick."' AND s_date='$sdate' AND s_method!='CONNECT' ORDER BY s_time) AS _cache GROUP BY _cache.time,_cache.url_domain ORDER BY _cache.time";

  $num_rows=$DB->samsdb_query_value($QUERY);
  while($row=$DB->samsdb_fetch_array())
  {
	$URL["time"][$count]=$row['time'];
	$URL["url"][$count]=str_replace("/","",$row['url_domain']);
	$URL["method"][$count]=$row['s_method'];

	$count++;
  }

  $QUERY="SELECT * FROM (SELECT substring(s_time from 1 for 5) as time, s_url as url_domain,  s_method FROM squidcache WHERE s_user='".$SquidUSERConf->s_nick."' AND s_date='$sdate' AND s_method='CONNECT' ORDER BY s_time) AS _cache GROUP BY _cache.time,_cache.url_domain ORDER BY _cache.time";

  $num_rows=$DB->samsdb_query_value($QUERY);
  while($row=$DB->samsdb_fetch_array())
  {
	$URL["time"][$count]=$row['time'];
	$URL["url"][$count]=str_replace("/","",$row['url_domain']);
	$URL["method"][$count]=$row['s_method'];

	$count++;
  }

  print("<CENTER>\n");
  print("<script type=\"text/javascript\" src=\"lib/jquery-1.2.6.js\"></script>\n");
  print("<script type=\"text/javascript\" src=\"lib/jquery.dataTables.js\"></script>\n");
  print("<script type=\"text/javascript\">\n");
  print("$(document).ready(function(){\n");
  print("  $(\"#urltime\").dataTable({\n");
  print("	\"bInfo\": 0,\n");
  print("	\"iDisplayLength\": $count,\n");
  print("	\"iDisplayStart\": 0,\n");
  print("	\"iDisplayEnd\": $count,\n");
  print("	\"oLanguage\": {\n");	
  print("		\"sSearch\": \"search\", \n");
  print("		\"sLengthMenu\": \"Show _MENU_ entries\"\n");
  print("		},\n");
  print("	\"aoColumns\": [ \n");
  print("		{ \"sType\": \"numeric\", \"sWidth\": \"15%\" },\n");
  print("		{ \"sType\": \"html\", \"sWidth\": \"75%\"},\n");
  print("		{ \"sType\": \"html\", \"sWidth\": \"10%\"},\n");
  print("    ]\n");
  print("  });\n");
  print("});\n");
  print("</script>\n");
  print("</CENTER>\n");

  asort($URL["time"]);
  reset($URL["time"]);

  $cache=0;
  print("<TABLE CLASS=samstable id=\"urltime\" WIDTH=80%>");
  print("<THEAD>\n");
  print("<TH>Time");
  print("<TH>URL");
  print("<TH>Method");
  print("</THEAD>\n");
  print("<TBODY>\n");
  $size=0;

	asort($URL["time"]);
	reset($URL["time"]);
	while (list($key, $val) = each($URL["time"])) 
	{
		print("<TR>");
		LTableCell($URL['time'][$key],15);
		if($USERConf->ToWebInterfaceAccess("C")==1)
		{
			RTableCell($URL['url'][$key],75);
		}
		
		RTableCell($URL['method'][$key],15);
		print("</TR>\n");

	}
 print("</TBODY>\n");
 print("</TABLE>");
}


/****************************************************************/
function UserTimeTrafficForm()
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
		PageTop("ttraffic_48.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT> <BR>$URLTimeForm_userbuttom_4_time_1");

		print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=UserName value=\"$SquidUSERConf->s_user_id\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usertimetraffic\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_3_time.php\">\n");
//		$dateselect->SetDate();
		$dateselect->ThisDate();
		print("</FORM>\n");
	}
}


function userbuttom_3_time()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("WAUCS")==1 || $USERConf->ToGroupStatAccess("G", $SquidUSERConf->s_group_id))
	{
		GraphButton("main.php?show=exe&function=usertimetrafficform&filename=userbuttom_3_time.php&id=$SquidUSERConf->s_user_id","basefrm","ttraffic_32.jpg","ttraffic_48.jpg","$userbuttom_2_traffic_userbuttom_2_traffic_1");
	}

}




?>
