<?
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
global $SAMSConf;

//global $KBSIZE;
//global $SAMSDB;
//global $LOGDB;
//global $MYSQLHOSTNAME;
//global $MYSQLUSER;
//global $MYSQLPASSWORD;
//global $LANG;
//global $ICONSET;

if(isset($_GET["id"])) $id=$_GET["id"];
if(isset($_GET["action"])) $action=$_GET["action"];
if(isset($_GET["ip"])) $ip=$_GET["ip"];
if(isset($_GET["user"])) $user=$_GET["user"];
if(isset($_GET["url"])) $url=$_GET["url"];

  require('../../mysqltools.php');
  $SAMSConf=new SAMSCONFIG();
  
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT denied_to FROM sams ");
  $row=mysql_fetch_array($result);
  $start=strpos($row['denied_to'],"messages");
  $path=substr($row['denied_to'],0,$start);

/*   ************************************       */  
  $agent=getenv("HTTP_USER_AGENT");
  if(strlen($agent)>2&&(strstr($agent,'windows')||strstr($agent,'Windows')))
    {
      //echo "<h1>OS Windows, charset cp 1251</h1>"; 
      $LANG="WIN1251";
    }  
  else
    {   
      //echo "<h1>OS not Windows, charset koi8-r</h1>"; 
      $LANG="KOI8-R";
    }
  $lang="../../lang/lang.$LANG";
  require($lang);
/*   ************************************     */    
  
print("  <HTML><HEAD>");

print("  </HEAD>");
print("  <BODY>");
print("  <TABLE BORDER=0 WIDTH=\"95%\" >");
  if($action=="sgdisable"||$action=="rejikdisable")
    {
       print("  <TR><TD><IMG SRC=\"$path$SAMSConf->ICONSET/denied.gif\">");
       print("  <TD>");
       if(strlen($user)>0)
          print("<H2>$blocked_php_1 <FONT COLOR=\"BLUE\">$user</H2></FONT> ");
       print("<P><FONT COLOR=\"RED\"><B><H2>$blocked_php_2</H2></B></FONT> ");
       exit(0);
    }
  if($action=="rejikdenied"||$action=="sgdenied")
    {
       print("  <TR><TD><IMG SRC=\"$path$SAMSConf->ICONSET/denied.gif\">");
       print("  <TD> ");
       print("<P><FONT COLOR=\"RED\"><B><H2>$blocked_php_3 <BR></FONT>");
       print("$url<BR> <FONT COLOR=\"RED\">$blocked_php_4</H2></B></FONT> ");
       
       exit(0);
    }
  print("  <TR><TD><IMG SRC=\"$path$SAMSConf->ICONSET/denied.gif\">");
  $result=mysql_query("SELECT squidusers.*,shablons.shour,shablons.smin,shablons.ehour,shablons.emin FROM squidusers LEFT JOIN shablons ON (squidusers.shablon=shablons.name) WHERE id=\"$id\" ");
  $row=mysql_fetch_array($result);

  if($action=="userdisabled")
    {
       print("  <TD>");
       if(strlen($row['nick'])>0)
          print("<H2>$blocked_php_1 <FONT COLOR=\"BLUE\">$row[nick]</H2></FONT> ");
       print("<P><FONT COLOR=\"RED\"><B><H2>$blocked_php_2</H2></B></FONT> ");
       if($row['enabled']<0)
         {
	   print("  <P><FONT COLOR=\"RED\"> $blocked_php_5</FONT>");
	 }
       if($row['size']/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE)>$row['quotes']&&$row['quotes']>0&&$row['enabled']>=0)
         {
           print("  <P>$blocked_php_6 ");
           print("  <BR><B>Ã…Õ…‘:</B> $row[quotes] Ì¬");
           $msize=floor($row['size']/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
           $ostatok=$row['size']%($SAMSConf->KBSIZE*$SAMSConf->KBSIZE);
           $ksize=floor($ostatok/$SAMSConf->KBSIZE);
	   print("  <BR><B>$blocked_php_7</B> <FONT COLOR=\"RED\"><B>$msize Ì¬ $ksize Kb</B></FONT>" );
	 }
    }
  if($action=="urldenied")
    {
       if(strlen($row['nick'])>0)
         print("  <TD><H2>$blocked_php_1 <FONT COLOR=\"BLUE\">$row[nick]</H2></FONT> ");
       print("  <P><FONT COLOR=\"RED\"><B><H3>$blocked_php_8</H3></FONT> ");
    }
  if($action=="timedenied")
    {
       if(strlen($row['nick'])>0)
         print("  <TD><H2>$blocked_php_1 <FONT COLOR=\"BLUE\">$row[nick]</H2></FONT> ");
         print("  <P><FONT COLOR=\"RED\"><B><H3>$blocked_php_9"); 
         print("<BR>$blocked_php_10</H3></FONT> ");
         print("  <P>$row[shour]:$row[smin] - $row[ehour]:$row[emin] ");
    }



  print("  <TR> ");
  print("  <TD> ");
  print("  <TD><P><H3>Access denied</H3> ");

  print("  </TABLE></BODY></HTML>");

?>

