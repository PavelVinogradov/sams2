<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ShablonUsers()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  db_connect($SAMSConf->SQUIDCTRLDATABASE) or exit();
  mysql_select_db($SAMSConf->SQUIDCTRLDATABASE)
       or print("Error\n");
  $result=mysql_query("SELECT * FROM shablons WHERE shablons.name=\"$id\" ");
  $row=mysql_fetch_array($result);
  $nick1=$row['nick'];

  PageTop("shablon.jpg","$shablon_1<BR>$shablontray_ShablonUsers_1 <FONT COLOR=\"BLUE\">$nick1</FONT>");

  $result=mysql_query("SELECT * FROM squidusers WHERE squidusers.shablon=\"$id\" ORDER BY nick");

  print("<TABLE>\n");
  while($row=mysql_fetch_array($result))
      {
       print("<TR>\n");
       print("<TD>");
       if($row['enabled']>0)
         {
           if($SAMSConf->realtraffic=="real")
	     $traffic=$row['size']-$row['hit'];
           else
	     $traffic=$row['size'];
	   if($row['quotes']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE>=$traffic||$row['quotes']<=0)
              $gif="puser.gif";
           else
              if($row['quotes']>0)
                  $gif="quote_alarm.gif";
         }
       if($row['enabled']==0)
         {
            $gif="puserd.gif";
         }
       if($row['enabled']<0)
         {
            $gif="duserd.gif";
         }
       print("<IMG SRC=\"$SAMSConf->ICONSET/$gif\" TITLE=\"\"> ");
       print("<TD> <B>$row[nick] </B>");
       print("<TD> $row[family] ");
       print("<TD> $row[name] ");
       print("<TD> $row[soname] ");
      }
  print("</TABLE>\n");
}


function ShablonTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  if($SAMSConf->access==2)
    {
      db_connect($SAMSConf->SQUIDCTRLDATABASE) or exit();
      mysql_select_db($SAMSConf->SQUIDCTRLDATABASE);
      $result=mysql_query("SELECT * FROM shablons WHERE name=\"$id\" ");
      $row=mysql_fetch_array($result);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=shablonusers&id=$id\";\n");
      print("</SCRIPT> \n");

      print("<TABLE border=0 WIDTH=\"100%\">\n");
      print("<TR>\n");
      print("<TD VALIGN=\"TOP\" WIDTH=\"30%\">");
      print("<B>$shablontray_ShablonTray_1 <BR><FONT SIZE=\"+1\" COLOR=\"BLUE\">$row[nick]</FONT></B>\n");

      ExecuteFunctions("./src", "shablonbuttom","");
    }
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
