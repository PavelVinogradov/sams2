<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_4_z1_urldenied()
 {
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB(&$SAMSConf);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

// if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "LC")==1)
    if($USERConf->ToWebInterfaceAccess("CL")==1 )
    {
	$item=array("classname"=> "denied",
		"icon" => "stop.gif",
		"target"=> "basefrm",
		"url"=> "main.php?show=exe&&filename=redirlisttray.php&function=addurllistform&type=denied",
		"text"=> "$lframe_sams_FolderDenied_1");
	treeFolder($item);

	$num_rows=$DB->samsdb_query_value("SELECT * FROM redirect WHERE s_type='denied' ");
	while($row=$DB->samsdb_fetch_array())
	{
		$item=array("classname"=> "denied",
			"target"=> "tray",
			"url"=> "tray.php?show=exe&filename=redirlisttray.php&function=redirlisttray&id=$row[s_redirect_id]",
			"text"=> "$row[s_name]");
		treeFolderItem($item);
	}
	treeFolderClose();
    }	 

 }
 
 

 ?>