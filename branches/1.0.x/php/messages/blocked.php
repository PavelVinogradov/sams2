<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

if(isset($_GET["id"])) $id=$_GET["id"];
if(isset($_GET["action"])) $action=$_GET["action"];
if(isset($_GET["ip"])) $ip=$_GET["ip"];
if(isset($_GET["user"])) $user=$_GET["user"];
if(isset($_GET["url"])) $url=$_GET["url"];

  require('../mysqltools.php');

  $SAMSConf = new SAMSCONFIG();
    
  $SAMSConf->LoadConfig();

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT denied_to FROM sams ");
  $row=mysql_fetch_array($result);
  $start=strpos($row['denied_to'],"messages");
  $path=substr($row['denied_to'],0,$start);

  $ICONSET = $SAMSConf->ICONSET;
  $KBSIZE = $SAMSConf->KBSIZE;
  $MBSIZE = $SAMSConf->KBSIZE * $SAMSConf->KBSIZE;
  $LANG = $SAMSConf->LANG;
  $LANGCODE = "ru";

  switch($LANG)
    {
      case "KOI8-R":
	$CHAR_SET="koi8-r";
	break;
      case "WIN1251":
      case "CP1251":
	$CHAR_SET="windows-1251";
	break;
      case "UTF8":
      case "UTF-8":
	$CHAR_SET="utf-8";
	break;
      case "EN":
	$CHAR_SET="iso-8859-1";
	$LANGCODE="en";
      default:
	$CHAR_SET="koi8-r";
	break;
    }  

  $lang="../lang/lang.$LANG";
  require($lang);
  
print("  <HTML lang=\"$LANGCODE\"><HEAD>\n");
print("  </HEAD>\n");
print("  <TITLE>SAMS (SQUID Account Management System).</TITLE>\n");
print("  <META  content=\"text/html; charset=$CHAR_SET\" http-equiv=\"Content-Type\">\n");
print("  <BODY>\n");
print("  <TABLE BORDER=0 WIDTH=\"95%\" >");

  if($action=="sgdisable"||$action=="rejikdisable")
    {
       $result=mysql_query("SELECT squidusers.*,shablons.shour,shablons.smin,shablons.ehour,shablons.emin FROM squidusers LEFT JOIN shablons ON (squidusers.shablon=shablons.name) WHERE squidusers.nick=\"$user\" ");
       $row=mysql_fetch_array($result);

       print("  <TR><TD><IMG SRC=\"$path$ICONSET/denied.gif\">");
       print("  <TD>");
       if(strlen($row['nick'])>0)
          print("<H2>$blocked_php_1 <FONT COLOR=\"BLUE\">$row[family] $row[name] $row[soname]($row[nick])</H2></FONT> ");
       print("<P><FONT COLOR=\"RED\"><B><H2>$blocked_php_3 <FONT COLOR=\"BLACK\">$url</FONT> $blocked_php_4!!!</H2></B></FONT> ");
       if($row['enabled']<0)
         {
	   print("  <P><FONT COLOR=\"RED\"> $blocked_php_5</FONT>");
	 }
       if($row['size']/$MBSIZE>$row['quotes']&&$row['quotes']>0&&$row['enabled']>=0)
         {
           print("  <P>$blocked_php_6 ");
           print("  <BR><B>$blocked_php_11</B> $row[quotes] $blocked_php_12");
           $msize=floor($row['size']/$MBSIZE);
           $ostatok=$row['size']%$MBSIZE;
           $ksize=floor($ostatok/$KBSIZE);
	   print("  <BR><B>$blocked_php_7</B> <FONT COLOR=\"RED\"><B>$msize $blocked_php_12 $ksize $blocked_php_13</B></FONT>" );
	 }

       if(strlen($user)>0)
          print("<H2>$blocked_php_1 <FONT COLOR=\"BLUE\">$user</H2></FONT> ");
       print("<P><FONT COLOR=\"RED\"><B><H2>$blocked_php_2</H2></B></FONT> ");
       exit(0);
    }
  if($action=="rejikdenied"||$action=="sgdenied")
    {
       print("  <TR><TD><IMG SRC=\"$path$ICONSET/denied.gif\">");
       print("  <TD> ");
       print("<P><FONT COLOR=\"RED\"><B><H2>$blocked_php_3 <BR></FONT>");
       print("$url<BR> <FONT COLOR=\"RED\">$blocked_php_4</H2></B></FONT> ");
       
       exit(0);
    }

  print("  <TR><TD><IMG SRC=\"$path$ICONSET/denied.gif\">");

  $result=mysql_query("SELECT sams.realsize,squidusers.*,shablons.shour,shablons.smin,shablons.ehour,shablons.emin FROM sams,squidusers LEFT JOIN shablons ON (squidusers.shablon=shablons.name) WHERE id=\"$id\" ");
  $row=mysql_fetch_array($result);

  print("  <TD>");
  if(strlen($row['nick'])>0)
    print("<H2>$blocked_php_1 <FONT COLOR=\"BLUE\">$row[nick] : $row[family] $row[name]</H2></FONT> ");
  if($action=="userdisabled")
    {
       print("<P><FONT COLOR=\"RED\"><B><H2>$blocked_php_2</H2></B></FONT> ");
       if($row['enabled']<0)
         {
	   print("  <P><FONT COLOR=\"RED\"> $blocked_php_5</FONT>");
	 }
       if($row['size']/$MBSIZE>$row['quotes']&&$row['quotes']>0&&$row['enabled']>=0)
         {
           print("  <P>$blocked_php_6 ");
           print("  <BR><B>$blocked_php_11</B> $row[quotes] $blocked_php_12");
           if ($row['realsize'] == 'real')
    	     $total = $row['size']-$row['hit'];
           else
             $total = $row['size'];
           $msize=floor($total/$MBSIZE);
           $ostatok=$total%$MBSIZE;
           $ksize=floor($ostatok/$KBSIZE);
	   print("  <BR><B>$blocked_php_7</B> <FONT COLOR=\"RED\"><B>$msize $blocked_php_12 $ksize $blocked_php_13</B></FONT>" );
	 }
    }
  if($action=="urldenied")
    {
       print("  <P><FONT COLOR=\"RED\"><B><H3>$blocked_php_8</H3></FONT> ");
    }
  if($action=="timedenied")
    {
       print("  <P><FONT COLOR=\"RED\"><B><H3>$blocked_php_9"); 
       print("  <BR>$blocked_php_10</H3></FONT> ");
       print("  <P>$row[shour]:$row[smin] - $row[ehour]:$row[emin] ");
    }

  print("  <TR> ");
  print("  <TD> ");
  print("  <TD><P><H3>Access denied</H3> ");
  print("  </TABLE></BODY></HTML>");

?>
