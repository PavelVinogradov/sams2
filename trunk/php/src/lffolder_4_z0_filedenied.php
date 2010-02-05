<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_4_z0_filedenied()
 {
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);


    if($USERConf->ToWebInterfaceAccess("CL")==1 )
    {
	$item=array("classname"=> "filetype",
		"icon" => "pfile.gif",
		"target"=> "basefrm",
		"url"=> "main.php?show=exe&filename=redirlisttray.php&function=addurllistform&type=files",
		"text"=> "$lframe_sams_FileDenied_1");
	treeFolder($item);

	$num_rows=$DB->samsdb_query_value("SELECT * FROM redirect WHERE s_type='files' ");
	while($row=$DB->samsdb_fetch_array())
	{
		$item=array("classname"=> "filetype",
			"target"=> "tray",
			"url"=> "tray.php?show=exe&filename=redirlisttray.php&function=redirlisttray&id=$row[s_redirect_id]",
			"text"=> "$row[s_name]");
		treeFolderItem($item);

	}
	treeFolderClose();
    }	 

 }
 
 

 ?>