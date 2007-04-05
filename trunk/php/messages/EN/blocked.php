<?
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

global $KBSIZE;
global $MYSQLDATABASE;
global $SQUIDCTRLDATABASE;
global $MYSQLHOSTNAME;
global $MYSQLUSER;
global $MYSQLPASSWORD;
global $LANG;
global $ICONSET;


if(isset($_GET["id"])) $id=$_GET["id"];
if(isset($_GET["action"])) $action=$_GET["action"];
if(isset($_GET["ip"])) $ip=$_GET["ip"];
if(isset($_GET["user"])) $user=$_GET["user"];
if(isset($_GET["url"])) $url=$_GET["url"];

  require('../../mysqltools.php');
  LoadConfig();
  db_connect($SAMSConf->MYSQLDATABASE) or exit();
  mysql_select_db($SAMSConf->MYSQLDATABASE);
  $result=mysql_query("SELECT denied_to FROM sams ");
  $row=mysql_fetch_array($result);
  $start=strpos($row['denied_to'],"messages");
  $path=substr($row['denied_to'],0,$start);

print("  <HTML><HEAD>");

print("  </HEAD>");
print("  <BODY>");
print("  <TABLE BORDER=0 WIDTH=\"95%\" >");

  if($action=="sgdisable"||$action=="rejikdisable")
    {
       print("  <TR><TD><IMG SRC=\"$path$ICONSET/denied.gif\">");
       print("  <TD>");
       if(strlen($user)>0)
          print("<H2>User <FONT COLOR=\"BLUE\">$user</H2></FONT> ");
       print("<P><FONT COLOR=\"RED\"><B><H2>Access to proxy server is denied</H2></B></FONT> ");
       exit(0);
    }
  if($action=="rejikdenied"||$action=="sgdenied")
    {
       print("  <TR><TD><IMG SRC=\"$path$ICONSET/denied.gif\">");
       print("  <TD> ");
       print("<P><FONT COLOR=\"RED\"><B><H2>Access to URL <BR></FONT>");
       print("$url<BR> <FONT COLOR=\"RED\">forbidden</H2></B></FONT> ");
       
       exit(0);
    }
  
      
  print("  <TR><TD><IMG SRC=\"$path$ICONSET/denied.gif\">");
  $result=mysql_query("SELECT squidusers.*,shablons.shour,shablons.smin,shablons.ehour,shablons.emin FROM squidusers LEFT JOIN shablons ON (squidusers.shablon=shablons.name) WHERE id=\"$id\" ");
  $row=mysql_fetch_array($result);

  if($action=="userdisabled")
    {
       print("  <TD>");
       if(strlen($row[nick])>0)
          print("<H2>User <FONT COLOR=\"BLUE\">$row[nick]</H2></FONT> ");
       print("<P><FONT COLOR=\"RED\"><B><H2>Access to proxy server is denied</H2></B></FONT> ");
       if($row[enabled]<0)
         {
	   print("  <P><FONT COLOR=\"RED\"> Your account is SWITCH OFF</FONT>");
	 }
       if($row[size]/($KBSIZE*$KBSIZE)>$row[quotes]&&$row[quotes]>0&&$row[enabled]>=0)
         {
           print("  <P>Your traffic is OFF ");
           print("  <BR><B>your quota:</B> $row[quotes] Mb");
           $msize=floor($row['size']/($KBSIZE*$KBSIZE));
           $ostatok=$row['size']%($KBSIZE*$KBSIZE);
           $ksize=floor($ostatok/$KBSIZE);
           print("  <BR><B>your traffic:</B> <FONT COLOR=\"RED\"><B>$msize Mb $ksize Kb</B></FONT>" );
	 }
    }
  if($action=="urldenied")
    {
       if(strlen($row[nick])>0)
         print("  <TD><H2>User <FONT COLOR=\"BLUE\">$row[nick]</H2></FONT> ");
       print("  <P><FONT COLOR=\"RED\"><B><H3>Access to this URL is denied</H3></FONT> ");
    }
  if($action=="timedenied")
    {
       if(strlen($row[nick])>0)
         print("  <TD><H2>User <FONT COLOR=\"BLUE\">$row[nick]</H2></FONT> ");
         print("  <P><FONT COLOR=\"RED\"><B><H3>time access error</H3></FONT> ");
         print("  <P>$row[shour]:$row[smin] - $row[ehour]:$row[emin] ");
    }



   print("  <TR> ");
   print("  <TD> ");
   print("  <TD><P><H3>Access denied</H3> ");

   print("  </TABLE></BODY></HTML>");

?>

