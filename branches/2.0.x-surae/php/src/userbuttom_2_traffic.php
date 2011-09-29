<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UserTrafficPeriodGB()
{
  require('lib/charts.class.php');
  
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  global $SquidUSERConf;
  $elemx = Array();
  $elemy = Array();

  if(isset($_GET["id"])) $id=$_GET["id"];
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);

	if($USERConf->ToWebInterfaceAccess("GSC")!=1 && ($USERConf->s_user_id != $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")!=1 ) )
	{
		exit(0);
	}

	$DB=new SAMSDB();
	$g = new chart;
	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$sdate=$DATE->sdate();
	$edate=$DATE->edate();
	$bdate=$DATE->BeginDate();
	$eddate=$DATE->EndDate();
  

	$num_rows=$DB->samsdb_query_value("SELECT sum(s_size) as s_size,s_date,s_user,s_domain,sum(s_hit) as s_hit FROM cachesum WHERE s_user='$SquidUSERConf->s_nick' AND s_date>='$sdate' AND s_date<='$edate' GROUP BY s_date,s_user,s_domain ORDER BY s_date");
	while($row=$DB->samsdb_fetch_array())
	{
		$elemx[0][]=$row['s_date'];
		$elemx[1][]=$row['s_date'];
		$elemy[0][]=$row['s_size'];
		$elemy[1][]=$row['s_hit'];
	}
	$xcount = 0;
	foreach ($elemx as $v)
		$xcount = max($xcount, count($v));

	$ymax = 0;
	foreach ($elemy as $v)
		$ymax = max($ymax, ceil(max($v)));

	$diff = array_sum($elemy[0]) - array_sum($elemy[1]);

	foreach ($elemy as $k => $v)
        	foreach ($v as $kk => $vv)
        	{
        		$g->xValue[$k][] = $elemx[$k][$kk];
        		$g->DataValue[$k][] = $vv;
        	}

	$g->Title = "";
	$g->SubTitle = " ";
	$g->Width = ($xcount*45) + 75;
	$g->Height = 300;
	$g->ShowBullets = TRUE;

	$g->LineShowCaption = FALSE; // TO BE FIXED YET
	$g->LineShowTotal = FALSE;   // DEPENDS ON LineShowCaption to be TRUE
	$g->LineCaption[0] = "Period 1";
	$g->LineCaption[1] = "Period 2";
	$g->LineCount = 2;

	$g->xCount = $xcount;
	$g->xCaption = " ";
	$g->xShowValue = TRUE;
	$g->xShowGrid = TRUE;

	$g->yCount = 10;
	$g->yCaption = "Daily traffic (bytes)";
	$g->yShowValue = TRUE;
	$g->yShowGrid = TRUE;

	$g->DataDecimalPlaces = 0;
	$g->DataMax = $ymax;
	$g->DataMin = 0;
	$g->DataShowValue = FALSE;

// #ITS DRAWING TIME################################
	$g->MakeLinePointChart();
// #################################################
}





function UserTrafficPeriod()
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
  require("reportsclass.php");
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("WAUC")!=1)
	exit(0);


  PageTop("usertraffic_48.jpg","$traffic_1 <FONT COLOR=\"BLUE\"> $SquidUSERConf->s_nick</FONT><BR>$userbuttom_2_traffic_UserTrafficPeriod_2");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=id value=\"$SquidUSERConf->s_user_id\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usertrafficperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_2_traffic.php\">\n");
	$dateselect->SetPeriod();
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

    printf("<P><IMG SRC=\"main.php?show=exe&function=usertrafficperiodgb&filename=userbuttom_2_traffic.php&id=$SquidUSERConf->s_user_id&gb=1&sdate=$sdate&edate=$edate\"><P>");
  $count=1;
  $cache=0;
  print("<TABLE CLASS=samstable>");
  print("<THEAD>\n");
  print("<TH>No");
  print("<TH>$traffic_data");
  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
      print("<TH>$userbuttom_2_traffic_UserTrafficPeriod_3");
      print("<TH>$userbuttom_2_traffic_UserTrafficPeriod_4");
    }   
  print("<TH>$userbuttom_2_traffic_UserTrafficPeriod_5");
  print("</THEAD>\n");
  print("<TBODY>\n");
  $size=0;
  $num_rows=$DB->samsdb_query_value("SELECT sum(s_size),s_date,s_user,s_domain,sum(s_hit) FROM cachesum WHERE s_user='$SquidUSERConf->s_nick' AND s_date>='$sdate' AND s_date<='$edate' GROUP BY s_date,s_user,s_domain ORDER BY s_date");


  while($row=$DB->samsdb_fetch_array())
       {
         print("<TR>");
         LTableCell($count,10);
         $aaa=ReturnDate($row['s_date']);
         LTableCell($aaa,15);
	 if($USERConf->ToWebInterfaceAccess("C")==1)
           {
             $aaa=FormattedString("$row[0]");
             RTableCell($aaa,25);
             $aaa=FormattedString("$row[4]");
	     RTableCell($aaa,25);
	   }   
//         if($SAMSConf->realtraffic=="real")
	   PrintFormattedSize($row[0]-$row[4]);
//	 else
//	   PrintFormattedSize($row[0]);
	 
         print("</TR>");
         $count=$count+1;
         $size=$size+$row[0];
	 $cache=$cache+$row[4];
       }
  print("<TR>");
  print("</TBODY>\n");
  print("<TD>");
  RBTableCell("$vsego",25);
  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
//      $aaa=FormattedString("$size");
//      RBTableCell("$aaa",25);
	PrintFormattedSize($size);
//      $aaa=FormattedString("$cache");
//      RBTableCell("$aaa",25);
	PrintFormattedSize($cache);
   }
//  if($SAMSConf->realtraffic=="real")
    PrintFormattedSize($size-$cache);
//  else
//    PrintFormattedSize($size);
//  echo "$SAMSConf->realtraffic";
  print("</TABLE>");
}






/****************************************************************/
function UserTrafficForm()
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
		PageTop("usertraffic_48.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT> <BR>$userbuttom_2_traffic_UserTrafficForm_1");

		print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=UserName value=\"$SquidUSERConf->s_user_id\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usertrafficperiod\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_2_traffic.php\">\n");
		$dateselect->SetPeriod();
		print("</FORM>\n");
	}
}


function userbuttom_2_traffic()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("WAUCS")==1 || $USERConf->ToGroupStatAccess("G", $SquidUSERConf->s_group_id))
	{
		GraphButton("main.php?show=exe&function=usertrafficform&filename=userbuttom_2_traffic.php&id=$SquidUSERConf->s_user_id","basefrm","usertraffic_32.jpg","usertraffic_48.jpg","$userbuttom_2_traffic_userbuttom_2_traffic_1");
	}

}




?>
