<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

  //require('./src/auth.php');
  require('./dbclass.php');
  require('./samsclass.php');
  require('./tools.php');
  include('./pluginmanager.php');
  require("lib/treeview.php");
  require('./userclass.php');
  global $SAMSConf;
  global $USERConf;

  $SAMSConf=new SAMSCONFIG();
  $USERConf=new SAMSUSER();
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
 if(isset($HTTP_COOKIE_VARS['samsadmin'])) $samsadmin=$HTTP_COOKIE_VARS['samsadmin'];
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
	$samsadmin=$_COOKIE['samsadmin'];
   }  

// $SAMSConf->access=UserAccess();
// $SAMSConf->access=2;
// $SAMSConf->USERPASSWD=1;

   if($samsadmin==1)
	{
		$USERConf->sams_admin();

	}
	else
	{
		if($SAMSConf->USERID > 0)
			$USERConf->sams_user($SAMSConf->USERID);
	}


header("Content-type: text/html; charset=$CHARSET");
print("<html><head>\n");
print("<link rel=\"STYLESHEET\" type=\"text/css\" href=\"$SAMSConf->ICONSET/tree.css\">\n");
print("</head>\n");
print("<body topmargin=16 marginheight=16 >\n");

print("<IMG SRC=\"$SAMSConf->ICONSET/sams.gif\">\n");
print("<link rel=\"STYLESHEET\" type=\"text/css\" href=\"icon/classic/treeview.css\">\n");
echo "<script type=\"text/javascript\" src=\"lib/jquery-1.2.6.js\"></script> \n
<script type=\"text/javascript\" src=\"lib/jquery.cookie.js\"></script>\n
<script type=\"text/javascript\" src=\"lib/jquery.treeview.js\"></script>\n";

$HOSTNAME=getenv('HOSTNAME');

echo "<style type=\"text/css\">\n
.filetree span.hostname { padding: 1px 0 1px 25px; display: block; }\n
.filetree span.hostname { background: url($SAMSConf->ICONSET/earth.gif) 0 0 no-repeat; }\n
</style>\n";

echo "<li><span class=\"hostname\">$HOSTNAME</span></li>";
echo "<div id=\"ex1\">";
echo "<ul id=\"browser\" class=\"filetree treeview\">";

    ExecuteFunctions("./", "lframe_","1");
echo "</ul>";
echo "</div>";


echo "<script type=\"text/javascript\">\n";
echo "$(document).ready(function(){\n";
echo "$(\"#browser\").treeview({";
echo "                animated: \"fast\",";
echo "                collapsed: true,";
echo "                persist: \"cookie\",";
echo "                prerendered: true, \n";
echo "                toggle: function() {";
echo "                        window.console && console.log(\"%o was toggled\", this);";
echo "                }";
echo " });\n";

echo "});\n";
echo "</script>\n";

//echo "samsadmin=$USERConf->s_samsadmin <BR>userid=$USERConf->s_user_id=$id=$SAMSConf->USERID <BR> $USERConf->s_webaccess";
print("</html>\n");


?>
