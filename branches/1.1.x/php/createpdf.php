<?php

class DATE
{
  var $sday,$smon,$syea,$shou,$eday,$emon,$eyea,$ehou;
  function DATE($mas)
    {
       list($this->sday,$this->smon,$this->syea,$this->shou,$this->eday,$this->emon,$this->eyea,$this->ehou)=$mas;
    }
  function BeginDate()
    {
       return("$this->sday.$this->smon.$this->syea"); 
    }
  function EndDate()
    {
       return("$this->eday.$this->emon.$this->eyea");
    }
  function sdate()
    {
       return("$this->syea-$this->smon-$this->sday");
    }
  function edate()
    {
       return("$this->eyea-$this->emon-$this->eday");
    }
}


function UsersTrafficPeriodPDFHeader($page) {

	global $pdfFile;
	global $DATE;
	global $SAMSConf;

  	if($SAMSConf->LANGCODE=="RU")
		$lang="./lang/lang.WIN1251";
	else
		$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	$sdate=$DATE->sdate();
	$edate=$DATE->edate();

  	$pdfFile->AddPage();
	$imagefile = "$SAMSConf->ICONSET/usergroup_48.jpg";
	$pdfFile->Image($imagefile,20,10,20,20);

	$pdfFile->SetFont('Nimbus','',15);
  	//$pdfFile->SetFont('SUSESerif-Roman','',16);
  	$pdfFile->SetXY(50, 15);
  	$pdfFile->Write(0, " $usersbuttom_2_traffic_UsersTrafficPeriod_1 $sdate - $edate ($lang_pdfReport_page $page)");
  	$pdfFile->SetXY(50, 25);
  	$pdfFile->Write(0, " $usersbuttom_2_traffic_UsersTrafficPeriod_2 ");


  	$pdfFile->SetFont('Nimbus','',11);
	//$pdfFile->SetFont('SUSESerif-Roman','',11);
	$ycount=40;
	$pdfFile->SetXY(30, $ycount);
	$pdfFile->Write(0, "N");
	$pdfFile->SetXY(40, $ycount);
	$pdfFile->Write(0, "$usersbuttom_2_traffic_UsersTrafficPeriod_4");
	$pdfFile->SetXY(130, $ycount);
	$pdfFile->Write(0, "$usersbuttom_2_traffic_UsersTrafficPeriod_7");
  
 
	$pdfFile->SetFont('Nimbus','',11);
	//$pdfFile->SetFont('SUSESerif-Roman','',11);
  }


function UsersTrafficPeriodPDF()
{
  global $SAMSConf;
  global $DATE;
  global $pdfFile; 

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $currentPage = 1;

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  UsersTrafficPeriodPDFHeader($currentPage);

  $ycount=50;
  $count=0;

  if($SAMSConf->realtraffic=="real")
    $result=mysql_query("SELECT sum(size), sum(hit), user,domain, sum(size) - sum(hit) as traff  FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\" group by user,domain order by traff desc");
  else
    $result=mysql_query("SELECT sum(size), sum(hit), user,domain, sum(size) as traff  FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\" group by user,domain order by traff desc");
  while($row=mysql_fetch_array($result))
       {
         $result_2=mysql_query("SELECT * FROM ".$SAMSConf->SAMSDB.".squidusers WHERE ".$SAMSConf->SAMSDB.".squidusers.nick=\"$row[user]\"&&".$SAMSConf->SAMSDB.".squidusers.domain=\"$row[domain]\"");
         $row_2=mysql_fetch_array($result_2);
         
	 $pdfFile->SetXY(30, $ycount);
         $pdfFile->Write(0, $count+1);
         $pdfFile->SetXY(40, $ycount);
         $aaa=convert_cyr_string($row['user'],"k","w");
	 $pdfFile->Write(0, $aaa);
         $pdfFile->SetXY(80, $ycount);
         $aaa=convert_cyr_string("$row_2[family] $row_2[name]","k","w");
	 $pdfFile->Write(0, $aaa);
	 $pdfFile->SetXY(130, $ycount);
  
         $aaa=ReturnTrafficFormattedSize($row[4]);
	 $pdfFile->Write(0, $aaa);
	 
	 $count=$count+1;
         $size2=$size2+$row[0];
         $hitsize=$hitsize+$row[1];
         $traf=$traf+$row[4];
	 $ycount+=7;

	 if ($count % 30 == 0) {
		$currentPage = $currentPage + 1;
		UsersTrafficPeriodPDFHeader($currentPage);
		$ycount = 50;
	 }
       }
  mysql_free_result($result);  
 
} 


function AllUsersTrafficPDF()
{
  //define('FPDF_FONTPATH','lib/font/');
  //require('lib/fpdf.php'); 
  
  global $SAMSConf;
  global $DATE;
  global $pdfFile;
  
  if($SAMSConf->LANGCODE=="RU")
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

  $result=mysql_query("SELECT * FROM ".$SAMSConf->SAMSDB.".squidusers ");
  while($row=mysql_fetch_array($result))
       {
          if($row['size']>0)
	    {
	      $pdfFile->AddPage();
              $imagefile = "$SAMSConf->ICONSET/usergroup_48.jpg";
              $pdfFile->Image($imagefile,20,10,20,20);

              $pdfFile->SetFont('Nimbus','',14);
              //$pdfFile->SetFont('SUSESerif-Roman','',16);
              $pdfFile->SetXY(50, 15);
              $pdfFile->Write(0, " $lang_pdfReport_user $row[nick] ($sdate - $edate)");
 
	      $pdfFile->SetXY(30, 40);
              $pdfFile->Write(0, $lang_pdfReport_date);
               
	      $pdfFile->SetXY(60, 40);
              $pdfFile->Write(0, $lang_pdfReport_size);
              $pdfFile->SetXY(90, 40);
              $pdfFile->Write(0, $lang_pdfReport_cache);
              $pdfFile->SetXY(130, 40);
	      $pdfFile->Write(0, $lang_pdfReport_traffic);
	      
	      $ycount=50;
              $pdfFile->SetFont('Nimbus','',11);
              //$pdfFile->SetFont('SUSESerif-Roman','',11);
              $result2=mysql_query("SELECT sum(cachesum.size),cachesum.date,cachesum.user,cachesum.domain,sum(cachesum.hit) FROM cachesum WHERE cachesum.user=\"$row[nick]\" &&cachesum.date>=\"$sdate\" &&cachesum.date<=\"$edate\" &&cachesum.domain=\"$row[domain]\" GROUP BY date");
	      while($row2=mysql_fetch_array($result2))
                {
	       
	           $pdfFile->SetXY(30, $ycount);
	           $aaa=ReturnDate($row2['date']);
                   $pdfFile->Write(0, $aaa);
               
	           $pdfFile->SetXY(60, $ycount);
                   $pdfFile->Write(0, $row2[0]);
                   $pdfFile->SetXY(90, $ycount);
                   $pdfFile->Write(0, $row2[4]);
                   $pdfFile->SetXY(130, $ycount);
	           if($SAMSConf->realtraffic=="real")
                     $aaa=ReturnTrafficFormattedSize($row2[0]-$row2[4]);
		   else
                     $aaa=ReturnTrafficFormattedSize($row2[0]);
	           $pdfFile->Write(0, " $aaa");
               
                   $count=$count+1;
                   $size=$size+$row[0];
	           $cache=$cache+$row[4];
                   $ycount+=7;
                }
	      mysql_free_result($result2);  

              $ycount+=20;
          
	      $pdfFile->SetXY(30, $ycount-10);
              $pdfFile->Write(0, $lang_pdfReport_site);
	      $pdfFile->SetXY(130, $ycount-10);
              $pdfFile->Write(0, $lang_pdfReport_size);
              $pdfFile->SetXY(160, $ycount-10);
	      $pdfFile->Write(0, $lang_pdfReport_cache);

	      $query="select trim(leading \"http://\" from substring_index(url,'/',3)) as norm_url,sum(size) as url_size,sum(hit) as hit_size from cache where user=\"$row[nick]\"&&domain=\"$row[domain]\"&&date>=\"$sdate\"&&date<=\"$edate\" group by norm_url order by url_size desc limit 50";
              $result3=mysql_query($query);
              while($row3=mysql_fetch_array($result3))
                {	      
	           $pdfFile->SetXY(30, $ycount);
                   $pdfFile->Write(0, $row3['norm_url']);
	           $pdfFile->SetXY(130, $ycount);
                   $pdfFile->Write(0, $row3['url_size']);
                   $pdfFile->SetXY(160, $ycount);
                   $pdfFile->Write(0, $row3['hit_size']);
	       
                   $ycount+=7;
	           if($ycount>=273)
	             {
                       $pdfFile->AddPage();
	               $ycount=50; 
		     }  
                }
	      mysql_free_result($result3);  
	    }   
       }
  mysql_free_result($result);  

} 


function UsersTrafficPeriodPDFlib($pdfFile)
{
  global $SAMSConf;
  global $DATE;
  global $PAGE;
      
  if($SAMSConf->LANGCODE=="RU")
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
  
  $imagefile = "$SAMSConf->ICONSET/usergroup_48.jpg";
  $image = PDF_load_image($pdfFile, "auto", $imagefile, "");
  if (!$image)
    {
      die( "Error: " . PDF_get_errmsg($pdfFile) );
    }
  $fontdir = "lib/font/";
  pdf_set_parameter($pdfFile, "FontOutline", "Nimbus=$fontdir/Nimbus.ttf");
  
  $ycount=700;
  if($SAMSConf->realtraffic=="real")
    $result=mysql_query("SELECT sum(size), sum(hit), user,domain, sum(size) - sum(hit) as traff  FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\" group by user,domain order by traff desc");
  else
    $result=mysql_query("SELECT sum(size), sum(hit), user,domain, sum(size) as traff  FROM cachesum WHERE date>=\"$sdate\"&&date<=\"$edate\" group by user,domain order by traff desc");
  while($row=mysql_fetch_array($result))
       {
         if($ycount==700)
	   {
              pdf_begin_page($pdfFile, 595, 842);
              if($SAMSConf->LANGCODE=="RU")
                {
                  $font = PDF_findfont($pdfFile, "Nimbus", "cp1251",1);
                  PDF_setfont($pdfFile, $font, 16);
                }

	     PDF_fit_image($pdfFile, $image, 50, 760, "" );
             pdf_show_xy($pdfFile, "$usersbuttom_2_traffic_UsersTrafficPeriod_1", 170, 780);  
             pdf_show_xy($pdfFile, "$usersbuttom_2_traffic_UsersTrafficPeriod_2", 120, 760);  
             PDF_setfont($pdfFile, $font, 10);
             pdf_show_xy($pdfFile, "$traffic_2 $bdate $traffic_3 $eddate", 220, 740);  
	   
             pdf_moveto($pdfFile, 20, 720);
             pdf_lineto($pdfFile, 575, 720);
             pdf_stroke($pdfFile);		       
             
             PDF_setfont($pdfFile, $font, 12);
	     pdf_show_xy($pdfFile, "$usersbuttom_2_traffic_UsersTrafficPeriod_4", 160, $ycount);
             pdf_show_xy($pdfFile, "$usersbuttom_2_traffic_UsersTrafficPeriod_7", 410, $ycount);  
             
	     PDF_setfont($pdfFile, $font, 11);
             $ycount-=40;
  
	   }
	 $result_2=mysql_query("SELECT * FROM ".$SAMSConf->SAMSDB.".squidusers WHERE ".$SAMSConf->SAMSDB.".squidusers.nick=\"$row[user]\"&&".$SAMSConf->SAMSDB.".squidusers.domain=\"$row[domain]\"");
         $row_2=mysql_fetch_array($result_2);
         pdf_show_xy($pdfFile, $count+1, 50, $ycount);  
         pdf_show_xy($pdfFile, "$row[user]", 80, $ycount);  
         $aaa=convert_cyr_string("$row_2[family] $row_2[name]","k","w");
         pdf_show_xy($pdfFile, $aaa, 160, $ycount);  
 	 $aaa=ReturnTrafficFormattedSize($row[4]);
	 pdf_show_xy($pdfFile, $aaa, 400, $ycount);  
         //$aaa=ReturnTrafficFormattedSize($row[1]);
         //pdf_show_xy($pdfFile, $aaa, 375, $ycount);
         
         $count=$count+1;
         $size2=$size2+$row[0];
         $hitsize=$hitsize+$row[1];
         $traf=$traf+$row[4];
         $ycount-=20;
	 if($ycount==40)
	   {
             PDF_setfont($pdfFile, $font, 9);
             pdf_show_xy($pdfFile, "Created by SAMS (C) 2003-2010", 250, 20);  
             pdf_show_xy($pdfFile, "page $PAGE", 500, 10);  
	     pdf_end_page($pdfFile);
	     $ycount=700;
	     $PAGE+=1;

	   }  
       }
  PDF_close_image($pdfFile, $image);
  PDF_setfont($pdfFile, $font, 9);
  pdf_show_xy($pdfFile, "Created by SAMS (C) 2003-2010", 250, 20);  
  pdf_show_xy($pdfFile, "page $PAGE", 500, 10);  
  pdf_end_page($pdfFile);
  $PAGE+=1;
  
  
 
} 

function AllUsersTrafficPDFlib()
{
  global $SAMSConf;
  global $DATE;
  global $pdfFile;
  global $PAGE;
  
  if($SAMSConf->LANGCODE=="RU")
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

  $imagefile = "$SAMSConf->ICONSET/user.jpg";
  $image = PDF_load_image($pdfFile, "auto", $imagefile, "");
  if(!$image)
    {
        die( "Error: " . PDF_get_errmsg($pdfFile) );
    }
  $fontdir = "lib/font/";
  pdf_set_parameter($pdfFile, "FontOutline", "Nimbus=$fontdir/Nimbus.ttf");
		 
  $result=mysql_query("SELECT * FROM ".$SAMSConf->SAMSDB.".squidusers WHERE squidusers.size>\"0\" ");
  while($row=mysql_fetch_array($result))
       {
          $ycount=700;
          if($row['size']>0)
	    {
              pdf_begin_page($pdfFile, 595, 842);
              
	      $result2=mysql_query("SELECT sum(cachesum.size),cachesum.date,cachesum.user,cachesum.domain,sum(cachesum.hit) FROM cachesum WHERE cachesum.user=\"$row[nick]\" &&cachesum.date>=\"$sdate\" &&cachesum.date<=\"$edate\" &&cachesum.domain=\"$row[domain]\" GROUP BY date");
	      while($row2=mysql_fetch_array($result2))
                {
                   if($ycount>=700)
	             {
                       if($SAMSConf->LANGCODE=="RU")
                         {
                           $font = PDF_findfont($pdfFile, "Nimbus", "cp1251",1);
                           PDF_setfont($pdfFile, $font, 16);
                         }

                       PDF_fit_image($pdfFile, $image, 50, 760, "" );
                       pdf_show_xy($pdfFile, "$traffic_1 $row[nick]", 170, 780);  
                       pdf_show_xy($pdfFile, "$userbuttom_2_traffic_UserTrafficPeriod_2", 120, 760);  
                       PDF_setfont($pdfFile, $font, 10);
                       pdf_show_xy($pdfFile, "$traffic_2 $bdate $traffic_3 $eddate", 220, 740);  
                       
                       pdf_moveto($pdfFile, 20, 720);
                       pdf_lineto($pdfFile, 575, 720);
                       pdf_stroke($pdfFile);		       
		       
                       PDF_setfont($pdfFile, $font, 12);
		       pdf_show_xy($pdfFile, "$traffic_data", 110, $ycount);  
		       pdf_show_xy($pdfFile, "$userbuttom_2_traffic_UserTrafficPeriod_5", 290, $ycount);  
                       $ycount-=30;
                       PDF_setfont($pdfFile, $font, 11);
	             }
	       
	           $aaa=ReturnDate($row2['date']);
                   pdf_show_xy($pdfFile, $aaa, 100, $ycount);  
                   //pdf_show_xy($pdfFile, $row2[4], 230, $ycount);  
                   if($SAMSConf->realtraffic=="real")
	             {
                       $aaa=ReturnTrafficFormattedSize($row2[0]-$row2[4]);  
                     }
		   else
		     {
                       $aaa=ReturnTrafficFormattedSize($row2[0]); 
		     }  
		     pdf_show_xy($pdfFile, $aaa, 290, $ycount);  
                   
		   $size=$size+$row[0];
	           $cache=$cache+$row[4];
                   $ycount-=20;
	 
	           if($ycount==40)
	             {
                       PDF_setfont($pdfFile, $font, 9);
                       pdf_show_xy($pdfFile, "Created by SAMS (C) 2003-2010", 250, 20);  
                       pdf_show_xy($pdfFile, "page $PAGE", 500, 10);  
	               pdf_end_page($pdfFile);
                       pdf_begin_page($pdfFile, 595, 842);
	               $ycount=700;
                       $PAGE+=1;
	             }  
                }
	      mysql_free_result($result2);  
	                    
	     //if($ycount!=500)
              PDF_setfont($pdfFile, $font, 9);
              pdf_show_xy($pdfFile, "Created by SAMS (C) 2003-2010", 250, 20);  
              pdf_show_xy($pdfFile, "page $PAGE", 500, 10);  
              pdf_end_page($pdfFile);
              $PAGE+=1;
	    }   
          
       }
  mysql_free_result($result);  
  PDF_close_image($pdfFile, $image);

} 

  
  global $pdfFile;
  global $SAMSConf;
  global $DATE;
  global $PAGE;
  
  //echo "$argv[0] $argv[1] $argv[2]\n";
  if($argv[1]==1)
    {
      $path="$argv[2]/mysqltools.php";
      require($path);
    }
  else
    require('./mysqltools.php');

  $year=strftime("%Y");
  $mon=strftime("%m");
  $day=strftime("%d");

  $DATE=new DATE(Array( 1, $mon, $year, 0, 31, $mon, $year, 23), $sdate, $edate);
  $SAMSConf=new SAMSCONFIG();
  if($SAMSConf->LANGCODE=="RU")
    $lang="./lang/lang.WIN1251";
  else
    $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
  $PAGE=1;
  if($SAMSConf->PDFLIB=="pdflib")
    {
      $pdfFile=pdf_new();
      PDF_open_file($pdfFile, "");
      pdf_set_info($pdfFile, "Creator", "Created by SAMS");
      pdf_set_info($pdfFile, "Title", "$usersbuttom_2_traffic_UsersTrafficPeriod_1 $usersbuttom_2_traffic_UsersTrafficPeriod_2");
      
      
      UsersTrafficPeriodPDFlib($pdfFile);
      AllUsersTrafficPDFlib();
      
      pdf_close($pdfFile);
      $pdf = pdf_get_buffer($pdfFile);
      $pdflen = strlen($pdf);
      print("$pdf");
      pdf_delete($pdfFile);
      
    }
  if($SAMSConf->PDFLIB=="fpdf")
    {
      define('FPDF_FONTPATH','lib/font/');
      require('lib/fpdf.php');
      $pdfFile = new FPDF();
      $pdfFile->Open();
  
      $pdfFile-> AddFont('Nimbus','','Nimbus.php');
      $pdfFile->SetAuthor("SQUID Account Management System");  
      $pdfFile->SetCreator("Created by SAMS.");
      $pdfFile->SetTitle("SAMS users statistic");
      UsersTrafficPeriodPDF();
      AllUsersTrafficPDF();
      $pdfFile->Output();
    }


?>
