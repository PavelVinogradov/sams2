<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

/* PavelVinogradov@03.11.08: 
 * This is deprecated function. 
 * Don't produce any results.
 * */
function URLTimePeriodGB()
{
  require('lib/chart.php');
  
  global $SAMSConf;
  global $DATE;
  //$DataArray = array();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["userdomain"])) $userdomain=$_GET["userdomain"];

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
  $ehou=$DATE->ehou;

  if(isset($_GET["userid"])) $userid=$_GET["userid"];
  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);
  
  $SAMSConf->access=UserAccess();
  if($SAMSConf->access==0&&$SAMSConf->groupauditor!=$row[group])     {       exit;    }
   
  
  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);
  
  $count=0;
  for($h=0;$h<24;$h++)
    {
      for($m=0;$m<60;$m++)
        {
          $time=$h*100+$m;
	  $t="$h:$m";
	  $DataArray[$t]=0;
	  $data1[$count]=0;
          $data2[$time]=$count++;
        }
    }
  
  $acount=0;
  $query="SELECT HOUR(time) as hour,MINUTE(time) as minute,SUM(size) as size,SUM(hit) as hit FROM ".$SAMSConf->LOGDB.".cache WHERE cache.user=\"$username\"&&cache.domain=\"$userdomain\"&&cache.date=\"$sdate\"&&HOUR(time)>=$shou&&HOUR(time)<$ehou GROUP BY hour,minute";
  $result=mysql_query("$query");
  while($row=mysql_fetch_array($result))
       {
         //$time=$row['hour']*100+$row['minute']; 
	 //$acount=$data2[$time];
         //if($SAMSConf->realtraffic=="real")
	 //    $data1[$acount]=$row['size']-$row['hit'];
         //else
	 //    $data1[$acount]=$row['size'];
	 $t="$row[hour]:$row[minute]";
	 $DataArray[$t]=$row['size'];
	 //echo "$acount: $DataArray[$acount]=$row[size]<BR>";
	 $acount++;    
         //echo "$row[hour]:$row[minute] $time $data1[$time] $row[size] $row[hit]<BR>";
       }
    //for($acount=0;$acount<$count;$acount++)
    //  {
    //        echo "$acount:  $DataArray[$acount] $row[size]<BR>";
    //  }

//$chart = new chart(400, 200, "");
//$chart->plot($data1, false, "MidnightBlue", "lines");
//$chart->set_background_color("white", "white");
//$chart->set_title("Traffic of user $username");
//$chart->set_labels("", "Mb");
//$chart->stroke();  

}

 

function URLTimePeriod()
{
  global $SAMSConf;
  global $DATE;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["userdomain"])) $userdomain=$_GET["userdomain"];
  if(isset($_GET["usergroup"])) $usergroup=$_GET["usergroup"];

  if($SAMSConf->domainusername!=$username&&$SAMSConf->groupauditor!=$usergroup&&strlen($SAMSConf->adminname)==0)
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
  $ehou=$DATE->ehou;

  if(isset($_GET["userid"])) $userid=$_GET["userid"];
  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);
  
  $SAMSConf->access=UserAccess();
  if($SAMSConf->access==0&&$SAMSConf->groupauditor!=$row[group])     {       exit;    }
   
  
  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);
  
  PageTop("user.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$username</FONT><BR>$URLTimeForm_userbuttom_4_time_1");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"username\" id=UserName value=\"$username\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userdomain\" id=UserDomain value=\"$userdomain\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"urltimeperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_4_time.php\">\n");
  DateTimeSelect(0,"");
  print("</FORM>\n");

  printf("<BR><B>$traffic_2 $bdate $shou to $ehou</B> ");

//  User graph not implemented yet.  
//  if($SAMSConf->SHOWGRAPH=="Y")
//    printf("<P><IMG SRC=\"main.php?username=$username&userdomain=$userdomain&show=exe&function=urltimeperiodgb&filename=userbuttom_4_time.php&gb=1&sdate=$sdate&edate=$edate&SHou=$shou&EHou=$ehou \"><P>");

 // printf("<P>main.php?username=$username&userdomain=$userdomain&show=exe&function=urltimeperiodgb&filename=userbuttom_4_time.php&gb=1&sdate=$sdate&edate=$edate&SHou=$shou&EHou=$ehou<P>");  
      
  print("<TABLE CLASS=samstable>");
  print("<TH>No");
//  print("<TH>$userbuttom_4_site_UserSitesPeriod_3");
  print("<TH>Hour");
  print("<TH>Minute");
  print("<TH>URL");

  $count=1;
  $query="DROP TABLE IF EXIST \"cache_\" ";
  $result=mysql_query("$query");
  
  $query="CREATE TEMPORARY TABLE $SAMSConf->LOGDB.cache_ SELECT date,time,HOUR(time) as hour,MINUTE(time) as minute,user,domain,trim(leading \"http://\" from substring_index(url,'/',3)) as norm_url FROM $SAMSConf->LOGDB.cache WHERE cache.user=\"$username\"&&cache.domain=\"$userdomain\"&&cache.date=\"$sdate\"&&HOUR(time)>=$shou&&HOUR(time)<$ehou ORDER BY hour,minute,url";
  $result=mysql_query("$query");
//  print("query=$query<BR>result = $result");
  
  $query=" SELECT * FROM $SAMSConf->LOGDB.cache_  GROUP BY hour,minute,norm_url";
  $result=mysql_query("$query");
  //print("result = $result");
  $cache=0; 
  $counter=0;  
  while($row=mysql_fetch_array($result))
       {
  	 print("<TR>");
         LTableCell($count,8);
         LTableCell($row['hour'],10);
         LTableCell($row['minute'],10);
         if($SAMSConf->access==2)
           TableCell("<A TARGET=BLANK  HREF=\"main.php?show=exe&function=siteuserlist&filename=userbuttom_4_site.php&site=$row[norm_url]&SDay=$sday&EDay=$sday&SMon=$smon&EMon=$smon&SYea=$syea&EYea=$syea&username=$username\" target=\"blank\"><FONT COLOR=\"BLACK\">$row[norm_url]</FONT></A>");
         if($SAMSConf->access==1)
           TableCell("<A HREF=\"http://$row[norm_url]\" target=\"blank\">$row[norm_url]</A>\n");
         if($SAMSConf->access==0&&$SAMSConf->URLACCESS=="Y")
           TableCell("<A HREF=\"http://$row[norm_url]\" target=\"blank\">$row[norm_url]</A>\n");

         $count=$count+1;
       }
  print("</TABLE>");


}


/****************************************************************/
function URLTimeForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["userid"])) $userid=$_GET["userid"];

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access==0&&$SAMSConf->groupauditor!=$row[group])     {       exit;    }
   
  PageTop("user.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$row[1]</FONT><BR>$URLTimeForm_userbuttom_4_time_1");

  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"username\" id=UserName value=\"$row[1]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userdomain\" id=UserDomain value=\"$row[6]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"usergroup\" id=UserGroup value=\"$row[group]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"urltimeperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_4_time.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userid\" id=userid value=\"$userid\">\n");
//  NewDateSelect(0,"");
//  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_4_time.php\">\n");
  DateTimeSelect(0,"");
  print("</FORM>\n");

}



function userbuttom_4_time($userid)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);

   if($SAMSConf->access>0||$SAMSConf->groupauditor==$row[group])
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=urltimeform&filename=userbuttom_4_time.php&userid=$userid","basefrm","ttraffic_32.jpg","ttraffic_48.jpg","$userbuttom_4_site_userbuttom_4_site_1");
	}

}




?>
