<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function SaveRedirList()
{
  global $SAMSConf;
  $DB=new SAMSDB("$SAMSConf->DBNAME", "0", $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  $num_rows=$DB->samsdb_query_value("SELECT * FROM redirect WHERE  s_redirect_id='$id' ");
  $row=$DB->samsdb_fetch_array();
  PageTop("export_48.jpg","$redir_exporturllist1 <FONT COLOR=\"BLUE\">$row[s_name]</FONT>");
  $DB->free_samsdb_query();
  $filename=strftime("urllist-%d%b%Y-%H-%M-%S.txt");
  $fout=fopen("data/$filename","w");
  if($fout==FALSE)
    {
      echo "can't open sams config file data/$filename<BR>";
      exit(0);
    }
  $num_rows=$DB->samsdb_query_value("SELECT * FROM url WHERE  s_redirect_id='$id' ");
  while($row=$DB->samsdb_fetch_array())
    {
       fwrite($fout,"$row[s_url]\n");
    }
  fclose($fout);
  print("<A HREF=\"data/$filename\">\n");
  print("<BR>$redir_exporturllist2 \n");
  print("</A>\n");

}

function redirbuttom_2_savelist()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=saveredirlist&filename=redirbuttom_2_savelist.php&id=$id","basefrm","export_32.jpg","export_48.jpg","$redir_redirtray3");
	}

}




?>
