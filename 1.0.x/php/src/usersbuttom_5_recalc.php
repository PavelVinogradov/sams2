<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function RecalcUsersTraffic()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access!=2)     {      exit;     }
    
  $syea=strftime("%Y");
  $smon=strftime("%m");
  $eday=strftime("%d");

  $sdate="$syea-$smon-1";
  $edate="$syea-$smon-$eday";
  $stime="0:00:00";
  $etime="0:00:00";

  PageTop("usergroup_48.jpg","$usersbuttom_5_recalc_RecalcUsersTraffic_1 1.$smon.$syea по $eday.$smon.$syea $usersbuttom_5_recalc_RecalcUsersTraffic_2");

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);
  
  $result=mysql_query("CREATE TEMPORARY TABLE cache_ SELECT sum(size),user,domain,sum(hit) FROM cache WHERE date>=\"$sdate\"&&date<=\"$edate\" GROUP BY user,domain");
  $result=mysql_query("SELECT * FROM cache_ ");
  while($row=mysql_fetch_array($result))
       {
	 $tsize=0;
	 $thit=0;
	 if($row[0]>0)
	    $tsize=$row[0];
	 if($row[3]>0)
	    $thit=$row[3];
	 $result2=mysql_query("UPDATE ".$SAMSConf->SAMSDB.".squidusers SET size=\"$tsize\",hit=\"$thit\" WHERE nick=\"$row[user]\"&&domain=\"$row[domain]\" ");
       }
  UpdateLog("$SAMSConf->adminname","$usersbuttom_5_recalc_RecalcUsersTraffic_3","01");

}



function usersbuttom_5_recalc()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access==2)
    {
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function RecalcCounter(username,userid)\n");
       print("{\n");
       print("  value=window.confirm(\"$usersbuttom_5_recalc_usersbuttom_5_recalc_1 \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=recalcuserstraffic&filename=usersbuttom_5_recalc.php\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       print("<IMAGE id=Trash name=\"Clear\" src=\"$SAMSConf->ICONSET/recalc_32.jpg\" \n ");
       print("TITLE=\"$usersbuttom_5_recalc_usersbuttom_5_recalc_2\"  border=0 ");
       print("onclick=RecalcCounter(\"nick\",\"id\") \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/recalc_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/recalc_32.jpg'\" >\n");
    }

}

?>
