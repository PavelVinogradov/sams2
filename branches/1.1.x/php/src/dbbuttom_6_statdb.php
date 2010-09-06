<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function DBStat()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("stat_48.jpg","$dbbuttom_6_statdb_DBStat_1");
  print("<TABLE width=\"90%\">");
  print("<TR>");
  print("<TD ALIGN=\"CENTER\" bgcolor=beige width=80%><font size=-1><b>$dbbuttom_6_statdb_DBStat_2</b></TD>");
  print("<TD width=20% bgcolor=beige align=right><font size=-1><b>$dbbuttom_6_statdb_DBStat_3</b></TD>");

  $result=mysql_query("SELECT count(size) FROM $SAMSConf->SAMSDB.squidusers ");
  $row=mysql_fetch_array($result);
  print("<TR>");
  TableCell("<B>$dbbuttom_6_statdb_DBStat_4</B>");
  TableCell("$row[0]");
//$SAMSConf->SAMSDB
  $result=mysql_query("select * FROM $SAMSConf->SAMSDB.groups ");
  while($row=mysql_fetch_array($result))
       {
         $result2=mysql_query("SELECT count(size) FROM $SAMSConf->SAMSDB.squidusers WHERE squidusers.group=\"$row[name]\" ");
         $row2=mysql_fetch_array($result2);
         print("<TR>");
         TableCell("$dbbuttom_6_statdb_DBStat_5 $row[nick]:");
         TableCell("$row2[0]");
       }

  $result=mysql_query("SELECT count(url) FROM $SAMSConf->SAMSDB.urls ");
  $row=mysql_fetch_array($result);
  print("<TR>");
  TableCell("<B>$dbbuttom_6_statdb_DBStat_6</B>");
  TableCell("$row[0]");

  $result=mysql_query("select * FROM $SAMSConf->SAMSDB.redirect ");
  while($row=mysql_fetch_array($result))
       {
         $result2=mysql_query("select count(url) FROM $SAMSConf->SAMSDB.urls WHERE type=\"$row[filename]\"");
         $row2=mysql_fetch_array($result2);
         print("<TR>");
         TableCell("$dbbuttom_6_statdb_DBStat_7 $row[name]:");
         TableCell("$row2[0]");
       }

  $result=mysql_query("SELECT count(size) FROM $SAMSConf->LOGDB.cache ");
  $row=mysql_fetch_array($result);
  print("<TR>");
  TableCell("<B>$dbbuttom_6_statdb_DBStat_8</B>");
  TableCell("$row[0]");

  $result=mysql_query("select * FROM $SAMSConf->SAMSDB.squidusers ORDER BY nick");
  while($row=mysql_fetch_array($result))
       {
         $result2=mysql_query("SELECT count(size) FROM $SAMSConf->LOGDB.cache WHERE cache.user=\"$row[nick]\"&&cache.domain=\"$row[domain]\" ");
         $row2=mysql_fetch_array($result2);
         print("<TR>");
         TableCell("$dbbuttom_6_statdb_DBStat_9 <B>$row[nick]</B>:");
         TableCell("$row2[0]");
       }

  print("</TABLE>");
  print("<A href=\"main.php?show=exe&function=showreplaceurltable&filename=dbbuttom_6_statdb.php\"> .</A>");

}


function DeleteReplaceURL()
{
 if(isset($_GET["username"])) $username=$_GET["username"];
 $url=trim(strtok($username,"+"));
 $user=trim(strtok("+"));
 $domain=trim(strtok("+"));
// print("$ip $user $domain");
 $result=mysql_query("DELETE FROM urlreplace WHERE url=\"$url\"&&user=\"$user\"&&domain=\"$domain\"");
  ShowReplaceURLTable();
}

function AddReplaceURL()
{
 if(isset($_GET["username"])) $username=$_GET["username"];
 if(isset($_GET["userid"])) $userid=$_GET["userid"];
 if(isset($_GET["userip"])) $userip=$_GET["userip"];
 $user=trim(strtok($username,"+"));
 $domain=trim(strtok("+"));
  $result=mysql_query("INSERT INTO urlreplace SET user=\"$user\",domain=\"$domain\",url=\"$userid\",newurl=\"$userip\"");
  ShowReplaceURLTable();
}


function ShowReplaceURLTable()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->adminname=="Admin")
     {
       PageTop("stat_48.jpg","$dbbuttom_6_statdb_ShowReplaceURLTable_1");

       print("<FORM NAME=\"ADDUSERIP\" ACTION=\"main.php\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addreplaceurl\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"dbbuttom_6_statdb.php\">\n");
       print("<TABLE width=\"90%\">");
       print("<TR>");
       print("<TD  width=25%><font size=-1><b>$dbbuttom_6_statdb_ShowReplaceURLTable_2<BR>$dbbuttom_6_statdb_ShowReplaceURLTable_3");
       print("<TD> \n");
       print("<SELECT NAME=\"username\" SIZE=1 TABINDEX=30 >\n");
       db_connect($SAMSConf->SAMSDB) or exit();
       mysql_select_db($SAMSConf->SAMSDB)
            or print("Error\n");
       $result=mysql_query("SELECT nick,domain FROM squidusers");
       while($row=mysql_fetch_array($result))
           {
             print("<OPTION VALUE=$row[nick]+$row[domain]> $row[nick]/$row[domain] ");
           }
       print("</SELECT>\n");
       print("<TR>");
       print("<TD  width=25%><font size=-1><b>URL");
       print("<TD  width=50%><INPUT TYPE=\"TEXT\" NAME=\"userid\" SIZE=25> \n");
       print("<TR>");
       print("<TD  width=25%><font size=-1><b>Новый URL");
       print("<TD  width=50%><INPUT TYPE=\"TEXT\" NAME=\"userip\" SIZE=25> \n");
       print("<TD  width=25%><INPUT TYPE=\"SUBMIT\" value=\"$dbbuttom_6_statdb_ShowReplaceURLTable_4\">\n");
       print("</TABLE>");
       print("</FORM>");

       print("<P><FORM NAME=\"DELETEUSERIP\" ACTION=\"main.php\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"deletereplaceurl\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"dbbuttom_6_statdb.php\">\n");
       print("<TABLE width=\"90%\">");
       print("<TR>");
       print("<TD  width=20%><font size=-1><b>$dbbuttom_6_statdb_ShowReplaceURLTable_5");
       print("<TD> \n");
       print("<SELECT NAME=\"username\" SIZE=1 TABINDEX=30 >\n");
       db_connect($SAMSConf->SAMSDB) or exit();
       mysql_select_db($SAMSConf->SAMSDB)
            or print("Error\n");
       $result=mysql_query("SELECT * FROM urlreplace");
       while($row=mysql_fetch_array($result))
           {
            print("<OPTION VALUE=$row[url]+$row[user]+$row[domain]> $row[user]/$row[domain] $row[url]");
           }
       print("</SELECT>\n");
       print("<TD><INPUT TYPE=\"SUBMIT\" value=\"$dbbuttom_6_statdb_ShowReplaceURLTable_1\">\n");
       print("</TABLE>");
       print("</FORM>");

       print("<TABLE width=\"90%\">");
       print("<TR>");
       print("<TD ALIGN=\"CENTER\" bgcolor=beige width=15%><font size=-1><b>$dbbuttom_6_statdb_ShowReplaceURLTable_6</b></TD>");
       print("<TD ALIGN=\"CENTER\" bgcolor=beige width=15%><font size=-1><b>$dbbuttom_6_statdb_ShowReplaceURLTable_7</b></TD>");
       print("<TD ALIGN=\"CENTER\" bgcolor=beige width=35%><font size=-1><b>URL</b></TD>");
       print("<TD ALIGN=\"CENTER\" bgcolor=beige width=35%><font size=-1><b>$dbbuttom_6_statdb_ShowReplaceURLTable_8 URL</b></TD>");
       $result=mysql_query("select * FROM $SAMSConf->SAMSDB.urlreplace ");
       while($row=mysql_fetch_array($result))
            {
              print("<TR>");
              TableCell("$row[user]");
              TableCell("$row[domain]");
              TableCell("$row[url]");
              TableCell("$row[newurl]");
            }
       print("</TABLE>");
     }
}



function dbbuttom_6_statdb()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=dbstat&filename=dbbuttom_6_statdb.php",
	               "basefrm","stat_32.jpg","stat_48.jpg","$dbbuttom_6_statdb_dbbuttom_6_statdb_1");
    }

}







?>
