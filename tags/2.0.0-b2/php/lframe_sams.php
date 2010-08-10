<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function lframe_sams()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	$item=array("classname"=> "sams",
		"icon" => "proxy.gif",
		"target"=> "basefrm",
		"url"=> "main.php",
		"text"=> "SAMS");
	treeFolder($item);

  ExecuteFunctions("./src", "lffolder_","1");

  treeFolderClose();

return(0);
}

?>
