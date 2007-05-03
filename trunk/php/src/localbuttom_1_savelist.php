<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function SaveLocalList()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");

  $result=mysql_query("SELECT * FROM redirect WHERE  filename=\"localhosts\" ");
  $row=mysql_fetch_array($result);
  PageTop("export_48.jpg","$redir_exporturllist1 <FONT COLOR=\"BLUE\">$row[name]</FONT>");
  $filename=strftime("urllist-%d%b%Y-%H-%M-%S.txt");
  $fout=fopen("data/$filename","w");
  $result=mysql_query("SELECT * FROM urls WHERE  type=\"local\" ");
  while($row=mysql_fetch_array($result))
    {
       fwrite($fout,"$row[url]\n");
    }
  fclose($fout);
  print("<A HREF=\"data/$filename\">\n");
  print("<BR>$redir_exporturllist2 \n");
  print("</A>\n");

}

function localbuttom_1_savelist()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=savelocallist&filename=localbuttom_1_savelist.php","basefrm","export_32.jpg","export_48.jpg","$user_usertray4");
	}

}




?>
