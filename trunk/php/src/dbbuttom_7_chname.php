<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ChUser()
{
  global $SAMSConf;
  global $DATE;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["fromuser"])) $fromuser=$_GET["fromuser"];
  if(isset($_GET["touser"])) $touser=$_GET["touser"];
  
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {      exit;    }
  
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();

    
  $olduser=strtok($fromuser,"+");
  $olddomain=strtok("+");
  $newuser=strtok($touser,"+");
  $newdomain=strtok("+");

  PageTop("user.jpg"," $dbbuttom_7_chname_ChUser_1 <BR><FONT COLOR=\"BLUE\">$olddomain/$olduser -> $newdomain/$newuser</FONT><BR> $sdate - $edate");
  //print("<h3>  </h3>");
  db_connect($SAMSConf->LOGDB) or exit();
    mysql_select_db($SAMSConf->LOGDB);
  $result=mysql_query("UPDATE cache SET user=\"$newuser\",domain=\"$newdomain\" WHERE user=\"$olduser\"&&domain=\"$olddomain\"&&date>=\"$sdate\"&&date<=\"$edate\" ");
  $result=mysql_query("UPDATE cachesum SET user=\"$newuser\",domain=\"$newdomain\" WHERE user=\"$olduser\"&&domain=\"$olddomain\"&&date>=\"$sdate\"&&date<=\"$edate\" ");

}


/****************************************************************/
function ChUserForm()
{

  global $SAMSConf;
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {      exit;    }
  
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  PageTop("user.jpg","$dbbuttom_7_chname_ChUserForm_1");

  print("<FORM NAME=\"CHUSER\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"chuser\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"dbbuttom_7_chname.php\">\n");
  
  NewDateSelect(0,"");
  print("</CENTER>\n");
  
  print("<TABLE border=0 width=\"100%\">\n");
  print("<TR>\n");
  print("<TD WIDTH=\"30%\">\n");
  print("<B>$dbbuttom_7_chname_ChUserForm_2\n");
  print("<TD WIDTH=\"70%\">\n");
  print("<SELECT NAME=\"fromuser\" ID=\"fromuser\" SIZE=1 TABINDEX=30 >\n");

  db_connect($SAMSConf->SAMSDB) or exit();
    mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT user,domain FROM $SAMSConf->LOGDB.cachesum GROUP BY user,domain ORDER BY user");
  while($row=mysql_fetch_array($result))
      {
           print("<OPTION VALUE=$row[user]+$row[domain] SELECTED> $row[user]+$row[domain]");
      }
  print("</SELECT>\n");
  
  print("<TR>\n");
  print("<TD WIDTH=\"30%\">\n");
  print("<B>$dbbuttom_7_chname_ChUserForm_3\n");
  print("<TD WIDTH=\"70%\">\n");
  print("<SELECT NAME=\"touser\" ID=\"touser\" SIZE=1 TABINDEX=30 >\n");

  db_connect($SAMSConf->SAMSDB) or exit();
    mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT nick,domain FROM squidusers ORDER BY nick");
  while($row=mysql_fetch_array($result))
      {
           print("<OPTION VALUE=$row[nick]+$row[domain] SELECTED> $row[nick]+$row[domain]");
      }
  print("</SELECT>\n");
  
  print("</TABLE>\n");
  //print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$userbuttom_1_prop_UpdateUserForm_13\">\n");
  print("</FORM>\n");

}



function dbbuttom_7_chname()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

 $SAMSConf->access=UserAccess();
 if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=chuserform&filename=dbbuttom_7_chname.php",
	               "basefrm","chname_32.jpg","chname_48.jpg","$dbbuttom_7_chname_dbbuttom_7_chname_1");
    }

}







?>
