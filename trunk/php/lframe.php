<?
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
     $SAMSConf->adminname=UserAuthenticate($HTTP_COOKIE_VARS[user],$HTTP_COOKIE_VARS[passwd]);
     $SAMSConf->domainusername=$HTTP_COOKIE_VARS[domainuser];
     $SAMSConf->groupauditor=$HTTP_COOKIE_VARS[gauditor];
   }  
 else
   {
     $SAMSConf->adminname=UserAuthenticate($_COOKIE[user],$_COOKIE[passwd]);
     $SAMSConf->domainusername=$_COOKIE[domainuser];
     $SAMSConf->groupauditor=$_COOKIE[gauditor];
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
print("</script>\n");


$LS=`ls lframe_*`;
$len=substr_count($LS,"lframe_");
$func[0]=strtok($LS,chr(0x0a));
for($i=1;$i<$len;$i++)
   {
      $func[$i]=strtok(chr(0x0a));
   }

print("<script language=\"javascript\">\n");
print("PERSERVESTATE = 1\n");
print("USETEXTLINKS = 1\n");
print("STARTALLOPEN = 0\n");
print("ICONPATH = '$SAMSConf->ICONSET/'\n\n");

$HOSTNAME=trim(`hostname`);
print("foldersTree = gFld(\"$HOSTNAME\", \"main.php\", \"earth.gif\")\n");

for($i=0;$i<$len;$i++)
   {
      $func[$i]=strtok($func[$i],".php");
      require("./$func[$i].php");
      call_user_func("$func[$i]");
   }


print("</script>\n");
print("<a href=http://www.treeview.net/treemenu/userhelp ></a>\n");
print("<script>initializeDocument()</script>\n");
print("<noscript>\n");
print("Ваш обозреватель не поддерживает Javascript. Установите <a href=\"software/mozilla/\">Mozilla</a>\n");
print("</noscript>\n");


print("</html>\n");


?>
