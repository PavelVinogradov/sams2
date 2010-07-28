<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UsersTrafficPeriodPDF()
{
  require('chart.php');
  
  global $SAMSConf;
  global $DATE;
  $DB=new SAMSDB();
  
  if($SAMSConf->LANG=="KOI8-R")
    $lang="./lang/lang.WIN1251";
  else
    $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();


  $pdfFile=pdf_new();
  PDF_open_file($pdfFile, "");
//  pdf_set_info($pdfFile, "Author", "");
  pdf_set_info($pdfFile, "Creator", "Created by SAMS");
  pdf_set_info($pdfFile, "Title", "$usersbuttom_2_traffic_UsersTrafficPeriod_1 $usersbuttom_2_traffic_UsersTrafficPeriod_2");
//  pdf_set_info($pdfFile, "Subject", "");
  
  pdf_begin_page($pdfFile, 595, 842);
  pdf_add_bookmark($pdfFile, "Page 1", 0, 0);
  
  $font = pdf_load_font($pdfFile, "Helvetica", "cp1251", "");
  PDF_setfont($pdfFile, $font, 16);
  
  $imagefile = "$SAMSConf->ICONSET/usergroup_48.jpg";
  $image = PDF_load_image($pdfFile, "auto", $imagefile, "");
  if (!$image)
    {
      die( "Error: " . PDF_get_errmsg($pdfFile) );
    }
//  PDF_fit_image($pdfFile, $image, 350, 780, "adjustpage" );
  PDF_fit_image($pdfFile, $image, 50, 760, "" );
  PDF_close_image($pdfFile, $image);
  
  pdf_show_xy($pdfFile, "$usersbuttom_2_traffic_UsersTrafficPeriod_1", 170, 780);  
  pdf_show_xy($pdfFile, "$usersbuttom_2_traffic_UsersTrafficPeriod_2", 120, 760);  
  
        
  PDF_setfont($pdfFile, $font, 10);
  pdf_show_xy($pdfFile, "$traffic_2 $bdate $traffic_3 $eddate", 220, 740);  
    
  PDF_setfont($pdfFile, $font, 11);
  
  $ycount=700;
  $result=mysql_query("SELECT sum(size) as all_sum,sum(hit),user,domain FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\" group by user,domain order by all_sum desc");
  while($row=mysql_fetch_array($result))
       {
         $result_2=mysql_query("SELECT * FROM ".$SAMSConf->SAMSDB.".squidusers WHERE ".$SAMSConf->SAMSDB.".squidusers.nick=\"$row['user']\"&&".$SAMSConf->SAMSDB.".squidusers.domain=\"$row['domain']\"");
         $row_2=mysql_fetch_array($result_2);
         pdf_show_xy($pdfFile, $count+1, 50, $ycount);  
         pdf_show_xy($pdfFile, "$row[user]", 80, $ycount);  
         pdf_show_xy($pdfFile, "$row[family]", 150, $ycount);  
         pdf_show_xy($pdfFile, "$row[0]", 250, $ycount);  
         pdf_show_xy($pdfFile, "$row[1]", 350, $ycount);  
         pdf_show_xy($pdfFile, $row[0]-$row[1], 450, $ycount);  
         
         
         $count=$count+1;
         $size2=$size2+$row[0];
         $hitsize=$hitsize+$row[1];
         $traf=$traf+$row[0]-$row[1];
         $ycount-=20;
       }
  
  
  
  
  
  pdf_end_page($pdfFile);
  pdf_close($pdfFile);
  $pdf = pdf_get_buffer($pdfFile);
  $pdflen = strlen($pdf);
  
  header("Content-type: application/pdf");
  header("Content-Length: $pdflen");
  header("Content-Disposition: inline; filename=sams_traffic.pdf");
  
  print("$pdf");
  pdf_delete($pdfFile);
  
  //$fout = fopen("data/test.pdf", "w");
  //fwrite($fout, "$fout");
  //fclose($fout);     
} 
 
function UsersTrafficPeriodGB()
{
  require('lib/chart.php');
  
  global $SAMSConf;
  global $DATE;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if(isset($_GET["sort"])) $sort=$_GET["sort"];
   if(isset($_GET["desc"])) $desc=$_GET["desc"];
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

	$QUERY="SELECT sum(cachesum.s_size),sum(cachesum.s_hit),cachesum.s_user,cachesum.s_domain, squiduser.s_nick, squiduser.s_family, squiduser.s_name, squiduser.s_user_id FROM cachesum LEFT JOIN squiduser ON cachesum.s_user=squiduser.s_nick WHERE cachesum.s_date>='$sdate'AND cachesum.s_date<='$edate' GROUP BY cachesum.s_user,cachesum.s_domain,squiduser.s_nick,squiduser.s_family, squiduser.s_name, squiduser.s_user_id  order by sum(cachesum.s_size) desc";
	$num_rows=$DB->samsdb_query_value($QUERY);

//  $num_rows=$DB->samsdb_query_value("SELECT sum(s_size) as all_sum,sum(s_hit),s_user,s_domain FROM cachesum WHERE s_date>='$sdate'&&s_date<='$edate' group by s_user,s_domain order by all_sum desc");
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
   if(isset($_GET["sort"])) $sort=$_GET["sort"];
   if(isset($_GET["desc"])) $desc=$_GET["desc"];

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

    printf("<P><IMG SRC=\"main.php?show=exe&function=userstrafficperiodgb&filename=usersbuttom_2_traffic.php&gb=1&sdate=$sdate&edate=$edate&sort=$sort&desc=$desc \"><P>");
  
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
//  print("<TABLE class=\"sortable\" id=\"userstraffic\">\n");

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
			$name="$row[s_nick]";
		$str="<A HREF=\"tray.php?show=exe&filename=usertray.php&function=usertray&id=$row[s_user_id]\" TARGET=\"tray\">$name</A>\n";
		LTableCell($str,16);
//		LTableCell($name,16);
//		print("<TD>$str</TD>");
	 
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
//			echo "<TD> ".$row[0]." </TD>";
			$aaa=FormattedString("$row[1]");
			LTableCell("$aaa",15);
//			echo "<TD> ".$row[1]." </TD>";
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
  if((($SAMSConf->AUTH="ntlm"||$SAMSConf->AUTH="adld")&&$SAMSConf->NTLMDOMAIN=="Y")||$_GET["size"]=="On")
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
