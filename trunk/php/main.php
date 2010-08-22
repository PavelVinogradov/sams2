<?php
/*      SAMS 2(Squid Account Management System ver. 2 
 *      Author: Dmitry Chemerik chemerik@mail.ru
 *
 *	This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this programt, write to the Free Software
 *	Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */



  global $DATE;
  global $SAMSConf;
  global $PROXYConf;
  global $USERConf;
  global $TRANGEConf;
  global $POOLConf;
  global $SHABLONConf;

  require('./dateclass.php');
  require('./dbclass.php');
  require('./samsclass.php');
  require('./tools.php');
  include('./pluginmanager.php');  

/******************************/
$sday=0;
$smon=0;
$syea=0;
$shou=0;
$eday=0;
$emon=0;
$eyea=0;
$ehou=0;
$sdate=0; 
$edate=0;
$user=0;
$function="";
$filename=0;
$username=0;
$usergroup=0;
$usernick=0;
$userid=0;
$userid=0;
$gb=0;

if(isset($_GET["show"])) $user=$_GET["show"];
if(isset($_GET["module"])) $module=$_GET["module"]; else $module=null;
if(isset($_GET["function"])) $function=$_GET["function"];
if(isset($_GET["filename"])) $filename=$_GET["filename"];
if(isset($_GET["userid"])) $userid=$_GET["userid"];
if(isset($_GET["username"])) $username=$_GET["username"];

if(isset($_POST["show"])) $user=$_POST["show"];
if(isset($_POST["function"])) $function=$_POST["function"];
if(isset($_POST["filename"])) $filename=$_POST["filename"];
if(isset($_POST["userid"])) $userid=$_POST["userid"];
if(isset($_POST["username"])) $username=$_POST["username"];

if(isset($_GET["id"])) $userid=$_GET["id"];
if(isset($_GET["gb"])) $gb=$_GET["gb"];

if(isset($_GET["SDay"])) $sday=$_GET["SDay"];
if(isset($_GET["EDay"])) $eday=$_GET["EDay"];
if(isset($_GET["SMon"])) $smon=$_GET["SMon"];
if(isset($_GET["EMon"])) $emon=$_GET["EMon"];
if(isset($_GET["SYea"])) $syea=$_GET["SYea"];
if(isset($_GET["EYea"])) $eyea=$_GET["EYea"];
if(isset($_GET["SHou"])) $shou=$_GET["SHou"];
if(isset($_GET["EHou"])) $ehou=$_GET["EHou"];

if(isset($_GET["sdate"]))
	{
		$a=explode("-",$_GET["sdate"]);
		$syea=$a[0];
		$smon=$a[1];
		$sday=$a[2];
	}
if(isset($_GET["edate"]))
	{
		$a=explode("-",$_GET["edate"]);
		$eyea=$a[0];
		$emon=$a[1];
		$eday=$a[2];
	}
$reloadleftframe=0;
$settime=0;

if($ehou==0)
   $ehou=24;
if($shou==0)
   $shou=0;
$DATE=new DATE(Array($sday,$smon,$syea,$shou,$eday,$emon,$eyea,$ehou), $sdate, $edate);
$SAMSConf=new SAMSCONFIG();

$DB=new SAMSDB();

require('./userclass.php');
$USERConf=new SAMSUSER();

$lang="./lang/lang.$SAMSConf->LANG";
require($lang);

$WI=1;
  
if($function=="logoff")
  {
	setcookie("user","");
	setcookie("passwd","");
	setcookie("domainuser","");
	setcookie("gauditor","");
	setcookie("userid","");
	setcookie("webaccess","");
	setcookie("samsadmin","0");
	print("<SCRIPT>\n");
	print("  parent.lframe.location.href=\"lframe.php\"; \n");
	print("  parent.tray.location.href=\"tray.php?show=exe&filename=admintray.php&function=admintray\"; \n");
	print("</SCRIPT> \n");


  }   
/*
Авторизация администратора
*/
if($function=="setcookie")
  {
	$USERConf->sams_admin_authentication($username,$userid);
  }   

/*
Авторизация пользователя
*/
if($function=="userauth")
  {
	$USERConf->sams_user_id_authentication();
  }  

if($function=="nuserauth")
  {   
	$USERConf->sams_user_name_authentication();

  }  

	$cookie_user="";
	$cookie_passwd="";
	$cookie_domainuser="";
	$cookie_gauditor="";
	if(isset($HTTP_COOKIE_VARS['samsadmin'])) $samsadmin=$HTTP_COOKIE_VARS['samsadmin'];

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
		if(isset($_COOKIE['domainuser'])) $SAMSConf->domainusername = $_COOKIE['domainuser'];
		if(isset($_COOKIE['gauditor']))   $SAMSConf->groupauditor=$_COOKIE['gauditor'];
		if(isset($_COOKIE['userid']))     $SAMSConf->USERID=$_COOKIE['userid'];
		if(isset($_COOKIE['webaccess']))  $SAMSConf->USERWEBACCESS=$_COOKIE['webaccess'];
		if(isset($_COOKIE['samsadmin']))  $samsadmin=$_COOKIE['samsadmin'];
	  }  
   if($samsadmin==1)
	{
		$USERConf->sams_admin();

	}
	else
	{
		if($SAMSConf->USERID > 0)
			$USERConf->sams_user($SAMSConf->USERID);
	}



if($gb!=1)
  { 
    header("Content-type: text/html; charset=$CHARSET");
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: THU, 01 Jan 1970 00:00:01 GMT"); // Date in the past

    print("<HTML><HEAD>");
    print("<link rel=\"STYLESHEET\" type=\"text/css\" href=\"$SAMSConf->ICONSET/tree.css\">\n");
    print("</head>\n");
    print("<body LINK=\"#ffffff\" VLINK=\"#ffffff\">\n");
    print("<center>\n");
  }


  if(strstr($filename,"proxy"))
	{
	require('./proxyclass.php');
	$PROXYConf=new SAMSPROXY($_GET["id"]);
	}

  if(strstr($filename,"trange")&&$function!="addtrangeform"&&$function!="addtrange")
	{
	require('./trangeclass.php');
	 if(isset($_GET["id"])) $id=$_GET["id"];
	$TRANGEConf=new SAMSTRANGE($id);
	}

  if(strstr($filename,"pool")&&$function!="addpoolform"&&$function!="addpool")
	{
	require('./poolclass.php');
	 if(isset($_GET["id"])) $id=$_GET["id"];
	$POOLConf=new SAMSPOOL($id);
	}

  if(strstr($filename,"shablon")&&$function!="newshablonform"&&$function!="addshablon")
	{
	 if(isset($_GET["id"])) $id=$_GET["id"];
	require('./shablonclass.php');
	$SHABLONConf=new SAMSSHABLON($id);
	}

  if($user=="exe"&&$function!="setcookie"&&$function!="userauth"&&$function!="nuserauth"&&$module==null)
     {
	if(stristr($filename,".php" )==FALSE) 
  	{
    		$filename="";
  	}
  	$req="src/$filename";
  	if(strlen($req)>4)
    	{
      		require($req);
    	}
  	if(strlen($function)>0)
       		$function();

     } 

  if ($module !== null) {
	$manager = new PluginManager($DB, 1, $SAMSConf);
	print ($manager->dispatch($module, $function));
  }
if($function=="nuserauth"|| $function=="userauth")
  {
     if(isset($_POST["id"])) $id=$_POST["id"];
     print("<SCRIPT>\n");
     print("        parent.lframe.location.href=\"lframe.php\";\n");
     print("        parent.tray.location.href = \"tray.php?show=exe&filename=usertray.php&function=usertray&id=$id\";\n");
     print("</SCRIPT> \n");
  }   
if($function=="setcookie")
  {
     print("<SCRIPT>\n");
     print("        parent.lframe.location.href=\"lframe.php\";\n");
     print("        parent.tray.location.href=\"tray.php?show=exe&filename=admintray.php&function=admintray\";\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&function=sysinfo&filename=configtray.php\";\n");    
     print("</SCRIPT> \n");
  }   
if($function=="autherror")
  {
     print("<h1><FONT COLOR=\"RED\">Authentication ERROR</FONT></h1> \n");
     $time2=60 - ($time - $autherrort);
     if($autherrorc==0&&$time<$autherrort+60)
       {
          print("<h2>next logon after $time2 second</h2> \n");
       }   
  }   
print("</center>\n");
print("</body></html>\n");

?>
