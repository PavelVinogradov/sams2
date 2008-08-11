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


  //require('./src/auth.php');
  require('./dbclass.php');
  require('./samsclass.php');
  require('./tools.php');
  global $SAMSConf;

  $SAMSConf=new SAMSCONFIG();
//  $SAMSConf->access=2;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

 $cookie_user="";
 $cookie_passwd="";
 $cookie_domainuser="";
 $cookie_gauditor="";
 if(isset($HTTP_COOKIE_VARS['user'])) $cookie_user=$HTTP_COOKIE_VARS['user'];
 if(isset($HTTP_COOKIE_VARS['passwd'])) $cookie_passwd=$HTTP_COOKIE_VARS['passwd'];
 if(isset($HTTP_COOKIE_VARS['domainuser'])) $cookie_domainuser=$HTTP_COOKIE_VARS['domainuser'];
 if(isset($HTTP_COOKIE_VARS['gauditor'])) $cookie_gauditor=$HTTP_COOKIE_VARS['gauditor'];
	if(isset($HTTP_COOKIE_VARS['userid'])) $SAMSConf->USERID=$HTTP_COOKIE_VARS['userid'];
	if(isset($HTTP_COOKIE_VARS['webaccess'])) $SAMSConf->USERWEBACCESS=$HTTP_COOKIE_VARS['webaccess'];

 if($SAMSConf->PHPVER<5)
   {
     $SAMSConf->adminname=UserAuthenticate($cookie_user,$cookie_passwd);
     $SAMSConf->domainusername=$cookie_domainuser;
     $SAMSConf->groupauditor=$cookie_gauditor;
   }  
 else
   {
     $SAMSConf->adminname=UserAuthenticate($_COOKIE['user'],$_COOKIE['passwd']);
     $SAMSConf->domainusername=$_COOKIE['domainuser'];
     $SAMSConf->groupauditor=$_COOKIE['gauditor'];
	$SAMSConf->USERID=$_COOKIE['userid'];
	$SAMSConf->USERWEBACCESS=$_COOKIE['webaccess'];
   }  

 $SAMSConf->access=UserAccess();

print("<html><head>\n");
print("<META  content=\"text/html; charset=$CHARSET\" http-equiv='Content-Type'>");
//print("<META  content=\"text/html; charset=KOI8-R\" http-equiv='Content-Type'>");
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
print("foldersTree = gFld(\"$HOSTNAME \", \"main.php\", \"earth.gif\")\n");

//print("\n</script>\n");




      ExecuteFunctions("./", "lframe_","1");

print("\n</script>\n");

print("<a href=http://www.treeview.net/treemenu/userhelp ></a>\n");
print("<script>initializeDocument()</script>\n");
print("<noscript>\n");
print("��� ������������ �� ������������ Javascript. ���������� <a href=\"software/mozilla/\">Mozilla</a>\n");
print("</noscript>\n");


print("</html>\n");


?>
