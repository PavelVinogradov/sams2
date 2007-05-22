<?
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

  require('../../mysqltools.php');

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
  $LANG = $SAMSCong->LANG;

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
//printf("$agent<BR>");
/*   ************************************     */    

print("  <HTML><HEAD>");

print("  </HEAD>");
print("  <BODY>");
print("  <TABLE BORDER=0 WIDTH=\"95%\" >");
  if($action=="sgdisable"||$action=="rejikdisable")
    {
       print("  <TR><TD><IMG SRC=\"$path$ICONSET/denied.gif\">");
       print("  <TD>");
       if(strlen($user)>0)
          print("<H2>Пользователь <FONT COLOR=\"BLUE\">$user</H2></FONT> ");
       print("<P><FONT COLOR=\"RED\"><B><H2>Доступ к прокси-серверу запрещен</H2></B></FONT> ");
       exit(0);
    }
  if($action=="rejikdenied"||$action=="sgdenied")
    {
       print("  <TR><TD><IMG SRC=\"$path$ICONSET/denied.gif\">");
       print("  <TD> ");
       print("<P><FONT COLOR=\"RED\"><B><H2>Доступ к URL <BR></FONT>");
       print("$url<BR> <FONT COLOR=\"RED\">запрещен</H2></B></FONT> ");
       
       exit(0);
    }
  
/*   ************************************       */  
$agent=getenv("HTTP_USER_AGENT");
if(strlen($agent)>2&&(strstr($agent,'windows')||strstr($agent,'Windows')))
  echo "<h1>OS Windows, charset cp 1251</h1>"; 
else   
  echo "<h1>OS not Windows, charset koi8-r</h1>"; 

printf("$agent<BR>");
/*   ************************************     */    
    
  print("  <TR><TD><IMG SRC=\"$path$ICONSET/denied.gif\">");
  $result=mysql_query("SELECT squidusers.*,shablons.shour,shablons.smin,shablons.ehour,shablons.emin FROM squidusers LEFT JOIN shablons ON (squidusers.shablon=shablons.name) WHERE id=\"$id\" ");
  $row=mysql_fetch_array($result);

  if($action=="userdisabled")
    {
       print("  <TD>");
       if(strlen($row[nick])>0)
          print("<H2>Пользователь <FONT COLOR=\"BLUE\">$row[nick]</H2></FONT> ");
       print("<P><FONT COLOR=\"RED\"><B><H2>Доступ к прокси-серверу запрещен</H2></B></FONT> ");
       if($row[enabled]<0)
         {
	   print("  <P><FONT COLOR=\"RED\"> Вы отключены</FONT>");
	 }
       if($row[size]/($KBSIZE*$KBSIZE)>$row[quotes]&&$row[quotes]>0&&$row[enabled]>=0)
         {
           print("  <P>Превышено ограничение объема полученной информации ");
           print("  <BR><B>лимит:</B> $row[quotes] Мб");
           $msize=floor($row['size']/($KBSIZE*$KBSIZE));
           $ostatok=$row['size']%($KBSIZE*$KBSIZE);
           $ksize=floor($ostatok/$KBSIZE);
	   print("  <BR><B>получено:</B> <FONT COLOR=\"RED\"><B>$msize Мб $ksize Kb</B></FONT>" );
	 }
    }
  if($action=="urldenied")
    {
       if(strlen($row[nick])>0)
         print("  <TD><H2>Пользователь <FONT COLOR=\"BLUE\">$row[nick]</H2></FONT> ");
       print("  <P><FONT COLOR=\"RED\"><B><H3>Доступ к данному URL запрещен</H3></FONT> ");
    }
  if($action=="timedenied")
    {
       if(strlen($row[nick])>0)
         print("  <TD><H2>Пользователь <FONT COLOR=\"BLUE\">$row[nick]</H2></FONT> ");
         print("  <P><FONT COLOR=\"RED\"><B><H3>Вы не можете получить доступ к прокси серверу в этот день недели"); 
         print("<BR>или истекло время доступа к прокси-серверу</H3></FONT> ");
         print("  <P>$row[shour]:$row[smin] - $row[ehour]:$row[emin] ");
    }



  print("  <TR> ");
  print("  <TD> ");
  print("  <TD><P><H3>Access denied</H3> ");

  print("  </TABLE></BODY></HTML>");

?>

