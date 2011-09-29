<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_5_z20_delaypool()
 {
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

    if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
	$item=array("classname"=> "delaypool",
		"icon" => "delaypool.png",
		"target"=> "basefrm",
		"url"=> "main.php?show=exe&filename=pooltray.php&function=addpoolform",
		"text"=> "$lframe_sams_DelayPools");
	treeFolder($item);

	$num_rows=$DB->samsdb_query_value("SELECT * FROM delaypool");
	while($row=$DB->samsdb_fetch_array())
	{
		$item=array("classname"=> "delaypool",
			"target"=> "tray",
			"url"=> "tray.php?show=exe&filename=pooltray.php&function=pooltray&id=$row[s_pool_id]",
			"text"=> "$row[s_name]");
		treeFolderItem($item);
	}
	treeFolderClose();
    }	 

 }
 
 

 ?>
