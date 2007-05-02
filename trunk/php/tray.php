<?
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
  global $SAMSConf;

//global $ICONSET;
//global $USERACCESS;
//global $RFRAME;
//global $adminname;
//global $domainusername;
//global  $access;
//global $groupauditor;
require('./mysqltools.php');
require('./src/auth.php');
require('./src/user.php');
require('./src/userstray.php');
require('./src/usertray.php');
require('./src/grouptray.php');
require('./src/locallisttray.php');
require('./src/deniedlisttray.php');
require('./src/allowlisttray.php');
require('./src/redirlisttray.php');
require('./src/contextlisttray.php');
require('./src/squidtray.php');
require('./src/backuptray.php');
require('./src/admintray.php');
require('./src/shablontray.php');
require('./src/monitortray.php');
require('./src/logtray.php');
require('./src/dbtray.php');
require('./src/filelisttray.php');

  $SAMSConf=new SAMSCONFIG();

/******************************/
$sday=$_GET["SDay"];
$eday=$_GET["EDay"];
$smon=$_GET["SMon"];
$emon=$_GET["EMon"];
$syea=$_GET["SYea"];
$eyea=$_GET["EYea"];
$shou=$_GET["SHou"];
$ehou=$_GET["EHou"];

if(isset($_GET["function"]))
   $function=$_GET["function"];
//   $size=$_GET["function"];

if(isset($_GET["filename"]))
   $filename=$_GET["filename"];

if(isset($_GET["delete"]))
   $delete=$_GET["delete"];
if(isset($_GET["addurl"]))
   $addurl=$_GET["addurl"];
if(isset($_GET["show"]))
   $user=$_GET["show"];
if(isset($_GET["showgroup"]))
   $showgroup=$_GET["showgroup"];
if(isset($_GET["usernick"]))
   $usernick=$_GET["usernick"];
if(isset($_GET["username"]))
   $username=$_GET["username"];
if(isset($_GET["userdomain"]))
   $userdomain=$_GET["userdomain"];
if(isset($_GET["usersoname"]))
   $usersoname=$_GET["usersoname"];
if(isset($_GET["userfamily"]))
   $userfamily=$_GET["userfamily"];
if(isset($_GET["usergroup"]))
   $usergroup=$_GET["usergroup"];
if(isset($_GET["userid"]))
   $userid=$_GET["userid"];
if(isset($_GET["userquote"]))
   $userquote=$_GET["userquote"];
if(isset($_GET["newusernick"]))
   $newusernick=$_GET["newusernick"];
if(isset($_GET["newusername"]))
   $newusername=$_GET["newusername"];
if(isset($_GET["newusersoname"]))
   $newusersoname=$_GET["newusersoname"];
if(isset($_GET["newuserfamily"]))
   $newuserfamily=$_GET["newuserfamily"];
if(isset($_GET["newusergroup"]))
   $newusergroup=$_GET["newusergroup"];
if(isset($_GET["newuserquote"]))
   $newuserquote=$_GET["newuserquote"];
if(isset($_GET["groupnick"]))
   $groupnick=$_GET["groupnick"];
if(isset($_GET["groupname"]))
   $groupname=$_GET["groupname"];
if(isset($_GET["rframe"]))
   $RFRAME=$_GET["rframe"];
if(isset($_GET["showusers"]))
   $showuser=$_GET["showusers"];
if(isset($_GET["showdenied"]))
   $showdenied=$_GET["showdenied"];
if(isset($_GET["showredir"]))
   $showredir=$_GET["showredir"];
if(isset($_GET["opengroup"]))
   $opengroup=$_GET["opengroup"];
if(isset($_GET["users"]))
   $users=$_GET["users"];
if(isset($_GET["size"]))
   $size=$_GET["size"];
$reloadleftframe=0;

//LoadConfig();
// db_connect("$SAMSDB") or exit();

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

$SAMSConf->access=UserAccess();

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
print("<html><head>\n");
print("<META  content=\"text/html; charset=$CHARSET\" http-equiv='Content-Type'>");
print("<META HTTP-EQUIV=\"expires\" CONTENT=\"THU, 01 Jan 1970 00:00:01 GMT\">");
print("<META HTTP-EQUIV=\"pragma\" CONTENT=\"no-cache\">");
print("<link rel=\"STYLESHEET\" type=\"text/css\" href=\"$SAMSConf->ICONSET/tree.css\">\n");

print("</head>\n");
print("<body LINK=\"#ffffff\" VLINK=\"#ffffff\" >\n");

if(stristr($filename,".php" )==FALSE) 
  {
    $filename="";
  }

if($user=="exe")
  {
	$req="src/$filename";
    if(strlen($req)>4)
      require($req);
    $function();
  }

/***************/
if($user=="deniedlisttray")
  {
    DeniedListTray();
  }
if($user=="localtraftray")
  {
    LocalTrafTray();
  }
if($user=="usertray")
  {
    UserTray($userid,$usergroup);
  }
if($user=="usergrouptray")
  {
    GroupTray($groupname,$groupnick);
  }
if($user=="logtray")
  {
    LogTray();
  }
if($user=="allusertray")
  {
    AllUserTray();
  }
if($user=="usershablontray")
  {
    ShablonTray($groupname);
  }
if($user=="monitortray")
  {
    MonitorTray($groupname);
  }
if($user=="admintray")
  {
    AdminTray();
  }
if($user=="backuptray")
  {
    BackUpTray();
  }
if($user=="redirecttray")
  {
    RedirTray($groupname,$username);
  }
/***************/

print("</body></html>\n");

?>
