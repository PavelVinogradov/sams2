<?php

  global $SAMSConf;

  require('./samsclass.php');
  require('./dbclass.php');
  require('./tools.php');

  $accepted_charset=$_SERVER['HTTP_ACCEPT_CHARSET'];
  $mas1 = explode("=", $accepted_charset);
  $mas2 = explode(",", $mas1[0]);
  $charset=$mas2[0];

  if (empty($charset)) $charset='utf-8';

  $SAMSConf=new SAMSCONFIG();
  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["action"])) $action=$_GET["action"];
  if(isset($_GET["ip"])) $ip=$_GET["ip"];
  if(isset($_GET["user"])) $user=$_GET["user"];
  if(isset($_GET["url"])) $url=$_GET["url"];

  switch($charset)
    {
      case "windows-1251":
        $LANG="WIN1251";
        break;
      case "koi8-r":
        $LANG="KOI8-R";
        break;
      case "utf-8":
        $LANG="UTF8";
        break;
      case "iso-8859-1":
        $LANG="EN";
        break;
      default:
        $LANG="KOI8-R";
        break;
    }

  $langfile="lang/lang.$LANG";
  require($langfile);

  $DB=new SAMSDB();
  $QUERY="SELECT s_nick, s_family, s_name, s_shablon_id FROM squiduser WHERE s_ip='$id' OR s_nick='$id'";

  $result=$DB->samsdb_query_value($QUERY);
  $row=$DB->samsdb_fetch_array();
  $s_nick=$row['s_nick'];
  $s_family=$row['s_family'];
  $s_name=$row['s_name'];

  header("Content-type: text/html; charset=$charset");
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: THU, 01 Jan 1970 00:00:01 GMT"); // Date in the past

  print("<HTML><HEAD>");
  print("<link rel=\"STYLESHEET\" type=\"text/css\" href=\"$SAMSConf->ICONSET/tree.css\">\n");
  print("</head>\n");
  print("<body LINK=\"#ffffff\" VLINK=\"#ffffff\">\n");
  print("<center>\n");


  $img=$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."$SAMSConf->ICONSET/stop_128.jpg";
  $img=str_replace("blocked.php","",$img);

  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD WIDTH=120><IMG SRC=\"http://$img\">\n");
  print("<TD><H1>Access denied</H1>\n");


  if($action=="urldenied")
    {
       print("  <P><FONT COLOR=\"RED\"><B><H3>$blocked_php_3 $blocked_php_4</H3></FONT> ");
    }
  if($action=="timedenied")
    {
        print("  <P><FONT COLOR=\"RED\"><B><H3>$blocked_php_9");
        print("  <BR>$blocked_php_10</H3></FONT> ");
        print("  <P>$blocked_php_14");

        $DB2=new SAMSDB();
        $QUERY2="SELECT sconfig_time.s_shablon_id,timerange.s_timestart,timerange.s_timeend FROM sconfig_time LEFT JOIN timerange
        ON sconfig_time.s_trange_id=timerange.s_trange_id WHERE sconfig_time.s_shablon_id='$row[s_shablon_id]'";
        $result2=$DB2->samsdb_query_value($QUERY2);
        while($row2=$DB2->samsdb_fetch_array())
        {
            print("  <P>$row2[s_timestart] - $row2[s_timeend] ");

        }
    }
  if($action=="usernotfound")
    {
       print("  <P><FONT COLOR=\"RED\"><B><H3>$blocked_php_2</H3></FONT> ");
    }
  if($action=="userdisabled")
    {
       print("  <TR><TD colspan=\"2\" align=\"center\"><H2>$blocked_php_1 <FONT COLOR=\"BLUE\">$s_nick</FONT><BR>");
       print("  <FONT COLOR=\"RED\">$blocked_php_5</FONT></H2> ");
    }
  if($action=="templatenotfound")
    {
       print("  <P><FONT COLOR=\"RED\"><B><H3>$blocked_php_2</H3></FONT> ");
    }

  print("</TABLE>\n");




  print("</center>\n");
  print("</body></html>\n");

?>