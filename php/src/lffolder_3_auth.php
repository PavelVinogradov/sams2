<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_3_auth()
 {
  global $SAMSConf;
  global $USERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
//	if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "AUC")==1)
	if($USERConf->ToWebInterfaceAccess("C")==1 )
	{
		$item=array("classname"=> "samsauth",
			"icon" => "auth.gif",
			"target"=> "tray",
			"url"=> "tray.php?show=exe&filename=authtray.php&function=authtray",
			"text"=> "Authorisation");
		treeFolder($item);

		if(GetAuthParameter("adld","enabled")>0)
		{
			$item=array("classname"=> "samsauth",
				"target"=> "tray",
				"url"=> "tray.php?show=exe&function=authadldtray&filename=authadldtray.php",
				"text"=> "ActiveDirectory");
			treeFolderItem($item);
		}
		if(GetAuthParameter("ntlm","enabled")>0)
		{
			$item=array("classname"=> "samsauth",
				"target"=> "tray",
				"url"=> "tray.php?show=exe&function=authntlmtray&filename=authntlmtray.php",
				"text"=> "NTLM");
			treeFolderItem($item);
		}
		if(GetAuthParameter("ldap","enabled")>0)
		{
			$item=array("classname"=> "samsauth",
				"target"=> "tray",
				"url"=> "tray.php?show=exe&function=authldaptray&filename=authldaptray.php",
				"text"=> "LDAP");
			treeFolderItem($item);
		}
		treeFolderClose();
	}
  }
 ?>