<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function SaveDeniedList()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  $result=mysql_query("SELECT * FROM redirect WHERE  filename=\"$id\" ");
  $row=mysql_fetch_array($result);
  PageTop("export_48.jpg","$redir_exporturllist1 <FONT COLOR=\"BLUE\">$row[name]</FONT>");
  $filename=strftime("urllist-%d%b%Y-%H-%M-%S.txt");
  $fout=fopen("data/$filename","w");
  if($fout==FALSE)
    {
      echo "can't open sams config file data/$filename<BR>";
      exit(0);
    }
  $result=mysql_query("SELECT * FROM urls WHERE  type=\"$id\" ");
  while($row=mysql_fetch_array($result))
    {
       fwrite($fout,"$row[url]\n");
    }
  fclose($fout);
  print("<A HREF=\"data/$filename\">\n");
  print("<BR>$redir_exporturllist2 \n");
  print("</A>\n");

}

function deniedbuttom_2_savelist()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=savedeniedlist&filename=deniedbuttom_2_savelist.php&id=$id","basefrm","export_32.jpg","export_48.jpg","$redir_redirtray3");
	}

}




?>
