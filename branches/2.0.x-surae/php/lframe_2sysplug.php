<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Andrey Ovcharov mclight77@permlug.org
 * (see the file 'main.php' for license details)
 */

function lframe_2sysplug()
{
  global $SAMSConf;
  global $USERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1)
  {
	$item=array("classname"=> "sysplug",
			"icon" => "sysplug_24.png",
			"target"=> "tray",
			"url"=> "tray.php?show=exe&function=sysplugconfigtray&filename=sysplugconfigtray.php",
			"text"=> "$lframe_2sysplug_lframe_1webconf_1");
	treeItem($item);
  }
}

?>
