<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function lframe_1webconf()
{
  global $SAMSConf;
  global $USERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1)
  {
	$item=array("classname"=> "webconf",
			"icon" => "webinterface.gif",
			"target"=> "tray",
			"url"=> "tray.php?show=exe&function=webconfigtray&filename=webconfigtray.php",
			"text"=> "$lframe_1webconf_lframe_1webconf_1");
	treeItem($item);
  }
}

?>
