<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_2_squid()
 {
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($USERConf->ToWebInterfaceAccess("C")==1 )
  {
	$item=array("classname"=> "squid",
		"icon" => "pobject.gif",
		"target"=> "tray",
		"url"=> "tray.php?show=exe&filename=squidtray.php&function=squidtray",
		"text"=> "SQUID");
	treeFolder($item);

	$DB->samsdb_query_value("SELECT * FROM proxy");
	while($row=$DB->samsdb_fetch_array())
	{
		$item=array("classname"=> "squid",
			"target"=> "tray",
			"url"=> "tray.php?show=exe&function=proxytray&filename=proxytray.php&id=$row[s_proxy_id]",
			"text"=> "$row[s_description]");
		treeFolderItem($item);
         }
 
	treeFolderClose();
  }
 }
 
 
 ?>