<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_4_z4_local()
 {
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

    if($USERConf->ToWebInterfaceAccess("CL")==1 )
    {
	$item=array("classname"=> "local",
		"icon" => "pfile.gif",
		"target"=> "basefrm",
		"url"=> "main.php?show=exe&filename=redirlisttray.php&function=addurllistform&type=local",
		"text"=> "$lframe_sams_lframe_sams_1");
	treeFolder($item);
	$num_rows=$DB->samsdb_query_value("SELECT * FROM redirect WHERE s_type='local' ");
	while($row=$DB->samsdb_fetch_array())
	{
		$item=array("classname"=> "local",
			"target"=> "tray",
			"url"=> "tray.php?show=exe&filename=redirlisttray.php&function=redirlisttray&id=$row[s_redirect_id]",
			"text"=> "$row[s_name]");
		treeFolderItem($item);
	}
	treeFolderClose();
    }	 

 }
 

 ?>