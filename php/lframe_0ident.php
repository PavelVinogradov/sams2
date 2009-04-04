<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function lframe_0ident()
{
global $SAMSConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(strlen($SAMSConf->domainusername)>0||strlen($SAMSConf->adminname)>0 || $SAMSConf->USERPASSWD==1)
    {

	$item=array("classname"=> "logoff",
		"icon" => "logoff_20.gif",
		"target"=> "basefrm",
		"url"=> "main.php?function=logoff",
		"text"=> "logoff");
	treeItem($item);
    }  
  
$item=array("classname"=> "userauth",
	"icon" => "ident.gif",
	"target"=> "tray",
	"url"=> "tray.php?show=exe&filename=admintray.php&function=admintray",
	"text"=> "$lframe_0ident_lframe_0ident_1");
treeItem($item);
 

}

?>
