<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
  global $SAMSConf;
  global $PROXYConf;
  global $USERConf;
  global $SquidUSERConf;
  global $TRANGEConf;
  global $POOLConf;
  global $SHABLONConf;
  require('./dbclass.php');
  require('./samsclass.php');
  require('./tools.php');
  //require('./str/grouptray.php');
  include('./pluginmanager.php');  
  
  $SAMSConf=new SAMSCONFIG();

require('./userclass.php');
$USERConf=new SAMSUSER();

$DB=new SAMSDB($SAMSConf->DB_ENGINE, $SAMSConf->ODBC, $SAMSConf->DB_SERVER,   		$SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB, $SAMSConf->PDO);
 $filename="";
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
 $user="";

 if(isset($_GET["show"]))    $user=$_GET["show"];
 if(isset($_GET["module"]))    $module=$_GET["module"]; else $module = null;
 if(isset($_GET["filename"])) $filename=$_GET["filename"];
 if(isset($_GET["function"])) $function=$_GET["function"];
 if(isset($_GET["id"])) $proxy_id=$_GET["id"];
 if(isset($_POST["function"])) $function=$_POST["function"];
 if(isset($_POST["filename"])) $filename=$_POST["filename"];

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

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  print("<html><head>\n");
  print("<META  content=\"text/html; charset=$CHARSET\" http-equiv='Content-Type'>\n");
  print("<META HTTP-EQUIV=\"expires\" CONTENT=\"THU, 01 Jan 1970 00:00:01 GMT\">\n");
  print("<META HTTP-EQUIV=\"pragma\" CONTENT=\"no-cache\">\n");
  print("<LINK rel=\"stylesheet\" type=\"text/css\" href=\"$SAMSConf->ICONSET/tree.css\">\n");


  print("</head>\n");
  print("<body LINK=\"#ffffff\" VLINK=\"#ffffff\" >\n");

  if(strstr($filename,"proxy"))
	{
	require('./proxyclass.php');
	$PROXYConf=new SAMSPROXY($proxy_id);
	}
  if(strstr($filename,"trange"))
	{
	require('./trangeclass.php');
	$TRANGEConf=new SAMSTRANGE($proxy_id);
	//$PROXYConf->PrintProxyClass();
	}
  if(strstr($filename,"pool"))
	{
	require('./poolclass.php');
	$POOLConf=new SAMSPOOL($proxy_id);
	//$PROXYConf->PrintProxyClass();
	}
  if(strstr($filename,"shablon"))
	{
	 if(isset($_GET["id"])) $id=$_GET["id"];
	require('./shablonclass.php');
	$SHABLONConf=new SAMSSHABLON($id);
	//$PROXYConf->PrintProxyClass();
	}

  if(stristr($filename,".php" )==FALSE) 
    {
      $filename="";
    }
  $req="src/$filename";
  if(strlen($req)>4)
    {
      require($req);
    }
  if( isset($function) && $module == null)
  {
      $function();
  }	
  if ($module !== null) {
     $manager = new PluginManager($DB, 1, $SAMSConf);
     print ($manager->dispatch($module, $function));
  } 

print("</body></html>\n");

?>
