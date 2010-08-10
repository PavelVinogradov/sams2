<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_1_config()
 {
  global $SAMSConf;
  global $USERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($USERConf->ToWebInterfaceAccess("C")==1 )
  {
	$item=array("classname"=> "samsconfig",
		"icon" => "config_20.jpg",
		"target"=> "tray",
		"url"=> "tray.php?show=exe&function=configtray&filename=configtray.php",
		"text"=> "$lframe_sams_lframe_sams_2");
	treeItem($item);

  }
 }
 
 
 ?>