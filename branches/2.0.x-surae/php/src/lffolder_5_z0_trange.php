<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_5_z0_trange()
 {
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

    if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
	$item=array("classname"=> "timerange",
		"icon" => "clock.gif",
		"target"=> "basefrm",
		"url"=> "main.php?show=exe&filename=trangetray.php&function=addtrangeform",
		"text"=> "$lffolder_5_z0_trange_lfforder_5_z0_trange_1");
	treeFolder($item);

	$num_rows=$DB->samsdb_query_value("SELECT * FROM timerange");
	while($row=$DB->samsdb_fetch_array())
	{
		$item=array("classname"=> "timerange",
			"target"=> "tray",
			"url"=> "tray.php?show=exe&filename=trangetray.php&function=trangetray&id=$row[s_trange_id]",
			"text"=> "$row[s_name]");
		treeFolderItem($item);
	}
	treeFolderClose();
    }	 

 }
 
 

 ?>