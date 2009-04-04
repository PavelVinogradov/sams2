<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_5_z2_groups()
 {
  global $SAMSConf;
  global $USERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
   
// if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "C")==1)
    if($USERConf->ToWebInterfaceAccess("C")==1 )
    {

	$item=array("classname"=> "newgroup",
		"icon" => "paddressbook.gif",
		"target"=> "tray",
		"url"=> "tray.php?show=exe&filename=newgrptray.php&function=newgrptray",
		"text"=> "$lframe_sams_UserGroupsFolder_1");
	treeItem($item);
    }	 

 }
 
 

 ?>