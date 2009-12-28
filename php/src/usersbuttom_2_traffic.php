<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UsersTrafficPeriodPDF()
{
  //require('chart.php');
  
  global $SAMSConf;
  global $DATE;
  
  if($SAMSConf->LANG=="KOI8-R")
    $lang="./lang/lang.WIN1251";
  else
    $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

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
         $result_2=mysql_query("SELECT * FROM ".$SAMSConf->SAMSDB.".squidusers WHERE ".$SAMSConf->SAMSDB.".squidusers.nick=\"$row[user]\"&&".$SAMSConf->SAMSDB.".squidusers.domain=\"$row[domain]\"");
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
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  $result=mysql_query("SELECT sum(size) as all_sum,sum(hit),user,domain FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\" group by user,domain order by all_sum desc");
//  $result=mysql_query("SELECT sum(size) as all_sum,sum(hit),user,domain FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\" group by user,domain");

  $count=0;
  while($row=mysql_fetch_array($result))
       {
         $result_2=mysql_query("SELECT * FROM ".$SAMSConf->SAMSDB.".squidusers WHERE ".$SAMSConf->SAMSDB.".squidusers.nick=\"$row[user]\"&&".$SAMSConf->SAMSDB.".squidusers.domain=\"$row[domain]\"");
         $row_2=mysql_fetch_array($result_2);
         
	 $SIZE[$count]=floor($row[0]/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
	 $HIT[$count]=floor($row[1]/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
	 $USERS[$count]=$row_2["nick"];
	 $count++;
       }
$showbar=new BAR(500, 200, 30, 20, $SIZE, $HIT, $count, $USERS);
$showbar->CreateBars();
       
}

 
 
function UsersTrafficPeriod()
{
  global $SAMSConf;
  global $DATE;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $size="";
   if(isset($_GET["size"])) $size=$_GET["size"];

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  PageTop("usergroup_48.jpg","$usersbuttom_2_traffic_UsersTrafficPeriod_1<BR>$usersbuttom_2_traffic_UsersTrafficPeriod_2");
  print("<BR>\n");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userstrafficperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_2_traffic.php\">\n");
  NewDateSelect(0,"");
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  if($SAMSConf->SHOWGRAPH=="Y")
    printf("<P><IMG SRC=\"main.php?show=exe&function=userstrafficperiodgb&filename=usersbuttom_2_traffic.php&gb=1&sdate=$sdate&edate=$edate \"><P>");
  
  $count=1;
  $size2=0;
  $hitsize=0;
  $traf=0;
  print("<TABLE CLASS=samstable>");
  print("<TH width=8%>No");
  print("<TH width=16%>$usersbuttom_2_traffic_UsersTrafficPeriod_4");
  if($size=="On")
    {
      print("<TH width=16%>$usersbuttom_2_traffic_UsersTrafficPeriod_8");
    }
  else
    {  
      if(($SAMSConf->AUTH="ntlm"||$SAMSConf->AUTH="adld")&&$SAMSConf->NTLMDOMAIN=="Y")
        print("<TH width=16%>Domain");
    }  
  if($SAMSConf->access==2)
    {
      print("<TH width=15%>$usersbuttom_2_traffic_UsersTrafficPeriod_6");
      print("<TH width=15%>$usersbuttom_2_traffic_UsersTrafficPeriod_5");
    }
  print("<TH width=30%>$usersbuttom_2_traffic_UsersTrafficPeriod_7");

  $result=mysql_query("SELECT sum(size) as all_sum,sum(hit),user,domain FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\" group by user,domain order by all_sum desc");

  while($row=mysql_fetch_array($result))
       {
         print("<TR>");
         $result_2=mysql_query("SELECT * FROM ".$SAMSConf->SAMSDB.".squidusers WHERE ".$SAMSConf->SAMSDB.".squidusers.nick=\"$row[user]\"&&".$SAMSConf->SAMSDB.".squidusers.domain=\"$row[domain]\"");
         $row_2=mysql_fetch_array($result_2);
         LTableCell($count,8);
                 
	if($SAMSConf->SHOWNAME=="fam")
           $name="$row_2[family]";
        else if($SAMSConf->SHOWNAME=="famn")
           $name="$row_2[family] $row_2[name]";
        else if($SAMSConf->SHOWNAME=="nickd")
           $name="$row_2[nick] / $row_2[domain]";
        else 
           $name="$row_2[nick]";
         $str="<A HREF=\"tray.php?show=usertray&userid=$row_2[id]&usergroup=$row_2[group]\" TARGET=\"tray\">$name</A>\n";
	 LTableCell($str,16);
	 
	 //LTableCell("<A HREF=\"tray.php?show=usertray&userid=$row_2[id]&usergroup=$row_2[group]\" TARGET=\"tray\">$row[user]</A>\n",16);
         if($size=="On")
           {
              LTableCell($row_2['family'],16);
           }
         else
           {
              if(($SAMSConf->AUTH="ntlm"||$SAMSConf->AUTH="adld")&&$SAMSConf->NTLMDOMAIN=="Y")
  	        TableCell($row['domain'],16);
           }
         if($SAMSConf->access==2)
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
         
	 print("</TR>");
         $count=$count+1;
         $size2=$size2+$row[0];
         $hitsize=$hitsize+$row[1];
       }
  print("<TR>");
  print("<TD>");
  RBTableCell("$vsego",16);
  if((($SAMSConf->AUTH="ntlm"||$SAMSConf->AUTH="adld")&&$SAMSConf->NTLMDOMAIN=="Y")||$_GET["size"]=="On")
    print("<TD>");
  if($SAMSConf->access==2)
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
  
  print("</TABLE>");


}



/****************************************************************/
function UsersTrafficForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["userid"])) $userid=$_GET["userid"];

  PageTop("usergroup_48.jpg","$alltraffic_1<BR>$usersbuttom_2_traffic_UsersTrafficForm_1");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userstrafficperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_2_traffic.php\">\n");
  NewDateSelect(0,"");
  print("</FORM>\n");


}


function usersbuttom_2_traffic()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->access>0||($SAMSConf->USERACCESS=="Y"&&$SAMSConf->domainusername=="$row[domain]+$row[nick]"))
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=userstrafficform&filename=usersbuttom_2_traffic.php","basefrm","traffic_32.jpg","traffic_48.jpg","$usersbuttom_2_traffic_usersbuttom_2_traffic_1");
	}

}




?>
