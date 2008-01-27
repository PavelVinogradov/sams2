<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ShowAllLog()
{
  global $SAMSConf;
  global $DATE;
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["groupname"])) $groupname=$_GET["groupname"];
  if(isset($_GET["size"])) $size=$_GET["size"];
  if(isset($_GET["userid"])) $userid=$_GET["userid"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("log_48.jpg","$log_1");
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  if($username=="on")
                 $v1="01";
  if($groupname=="on")
                 $v2="02";
  if($size=="on")
                 $v3="03";
  if($userid=="on")
                 $v4="04";
  $result=mysql_query("SELECT * FROM log WHERE (code=\"$v1\"||code=\"$v2\"||code=\"$v3\"||code=\"$v4\")&&date>=\"$sdate\"&&date<=\"$edate\" ");

  print("<TABLE width=\"95%\">");
  print("<TR>");
  print("<TD bgcolor=blanchedalmond width=15%><font size=-1><b>$log_4</b></TD>");
  print("<TD width=12% bgcolor=beige align=left><font size=-1><b>$log_5</b></TD>");
  print("<TD width=15% bgcolor=beige align=left><font size=-1><b>$log_6</b></TD>");
  print("<TD width=58% bgcolor=beige align=center><font size=-1><b>$log_3</b></TD>");

  while($row=mysql_fetch_array($result))
    {
         print("<TR>");
	 print("<TD bgcolor=blanchedalmond <font size=-1>");
	 print("$row[date]</TD>");

	 print("<TD bgcolor=blanchedalmond ><font size=-1>");
	 print("$row[time]</TD>");

	 print("<TD bgcolor=blanchedalmond ><font size=-1>");
	 print("$row[user]</TD>");

	 print("<TD bgcolor=blanchedalmond ><font size=-1>");
	 print("$row[value]</TD>");

    }

}

function ShowAllLogForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("log_48.jpg","$log_1");


  print(" <CENTER>\n");

       print("<FORM NAME=\"SHOWLOG\" ACTION=\"main.php\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"showalllog\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"logbuttom_1.php\">\n");
       NewDateSelect(0,"");
       print("<TABLE>\n");
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$log_2:\n");
       print("<TD>\n");
       print("<INPUT TYPE=\"CHECKBOX\" NAME=\"username\">\n");

       print("<TR>\n");
       print("<TD>\n");
       print("<B>$logbuttom_1_ShowAllLogForm_2:\n");
       print("<TD>\n");
       print("<INPUT TYPE=\"CHECKBOX\" NAME=\"groupname\">\n");

       print("<TR>\n");
       print("<TD>\n");
       print("<B>$logbuttom_1_ShowAllLogForm_3:\n");
       print("<TD>\n");
       print("<INPUT TYPE=\"CHECKBOX\" NAME=\"size\">\n");

       print("<TR>\n");
       print("<TD>\n");
       print("<B>$logbuttom_1_ShowAllLogForm_4:\n");
       print("<TD>\n");
       print("<INPUT TYPE=\"CHECKBOX\" NAME=\"userid\">\n");

       print("</TABLE>\n");
       print("</FORM>\n");
}

function logbuttom_1()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
      print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
      GraphButton("main.php?show=exe&function=showalllogform&filename=logbuttom_1.php","basefrm","logs-32.gif","logs.gif","$logbuttom_1_logbuttom_1_1");
    }

}

?>
