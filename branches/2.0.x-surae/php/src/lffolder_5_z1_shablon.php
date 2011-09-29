<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_5_z1_shablon()
 {
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

    if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
	$item=array("classname"=> "shablon",
		"icon" => "paddressbook.gif",
		"target"=> "basefrm",
		"url"=> "main.php?show=exe&function=newshablonform&filename=shablonnew.php",
		"text"=> "$lframe_sams_UserShablonFolder_1");
	treeFolder($item);

	$DB->samsdb_query("SELECT * FROM shablon ORDER BY s_name");
	while($row=$DB->samsdb_fetch_array())
	{
		$item=array("classname"=> "shablon",
			"target"=> "tray",
			"url"=> "tray.php?show=exe&function=shablontray&filename=shablontray.php&id=$row[s_shablon_id]",
			"text"=> "$row[s_name]");
		treeFolderItem($item);
	}
	treeFolderClose();
    }	 

 }
 
 

 ?>