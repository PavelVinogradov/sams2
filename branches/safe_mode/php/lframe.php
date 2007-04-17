<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */



function loadjsfiles()
{
//global $ICONSET;

$finp=fopen("menu/ua.js","r");
while(feof($finp)==0)
   {
       $string=fgets($finp, 10000);
       print("$string");
   }
fclose($finp);
$finp=fopen("menu/ftiens4.js","r");
while(feof($finp)==0)
   {
       $string=fgets($finp, 10000);
       print("$string");
   }
fclose($finp);
}
 

  require('./src/auth.php');
  require('./mysqltools.php');

  global $SAMSConf;

  $SAMSConf=new SAMSCONFIG();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

 if($SAMSConf->PHPVER<5)
   {
//echo "<h1>12345 phpver=$SAMSConf->PHPVER</h1>";
//echo "<BR>user=$_COOKIE[user]=$HTTP_COOKIE_VARS[user]";
//echo "<BR>passwd=$_COOKIE[passwd]=$HTTP_COOKIE_VARS[passwd]";
//echo "<BR>domainuser=$_COOKIE[domainuser]=$HTTP_COOKIE_VARS[domainuser]";
//echo "<BR>gauditor=$_COOKIE[gauditor]=$HTTP_COOKIE_VARS[gauditor]";
     $SAMSConf->adminname=UserAuthenticate($HTTP_COOKIE_VARS['user'],$HTTP_COOKIE_VARS['passwd']);
     $SAMSConf->domainusername=$HTTP_COOKIE_VARS['domainuser'];
     $SAMSConf->groupauditor=$HTTP_COOKIE_VARS['gauditor'];
   }  
 else
   {
     $SAMSConf->adminname=UserAuthenticate($_COOKIE['user'],$_COOKIE['passwd']);
     $SAMSConf->domainusername=$_COOKIE['domainuser'];
     $SAMSConf->groupauditor=$_COOKIE['gauditor'];
   }  

print("<html><head>\n");
print("<META  content=\"text/html; charset=$CHARSET\" http-equiv='Content-Type'>");
print("<link rel=\"STYLESHEET\" type=\"text/css\" href=\"$SAMSConf->ICONSET/tree.css\">\n");
print("</head>\n");
print("<body topmargin=16 marginheight=16 >\n");
//$SAMSConf->PrintSAMSSettings();

print("<IMG SRC=\"$SAMSConf->ICONSET/sams.gif\">");
print("<script language=\"javascript\">\n");
loadjsfiles();


print("PERSERVESTATE = 1\n");
print("USETEXTLINKS = 1\n");
print("STARTALLOPEN = 0\n");
print("ICONPATH = '$SAMSConf->ICONSET/'\n\n");

$HOSTNAME=getenv('HOSTNAME');
print("foldersTree = gFld(\"$HOSTNAME $retval\", \"main.php\", \"earth.gif\")\n");

      ExecuteFunctions("./", "lframe_");

print("\n</script>\n");

print("<a href=http://www.treeview.net/treemenu/userhelp ></a>\n");
print("<script>initializeDocument()</script>\n");
print("<noscript>\n");
print("��� ������������ �� ������������ Javascript. ���������� <a href=\"software/mozilla/\">Mozilla</a>\n");
print("</noscript>\n");


print("</html>\n");


?>
