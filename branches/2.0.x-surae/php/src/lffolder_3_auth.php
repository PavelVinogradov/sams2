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

	if($USERConf->ToWebInterfaceAccess("UC")==1 )
	{
		$item=array("classname"=> "samsauth",
			"icon" => "auth.gif",
			"target"=> "tray",
			"url"=> "tray.php?show=exe&filename=authtray.php&function=authtray",
			"text"=> "$lframe_sams_Authorisation");
		treeFolder($item);

		if(GetAuthParameter("adld","enabled")>0)
		{
			$item=array("classname"=> "samsauth",
				"target"=> "tray",
				"url"=> "tray.php?show=exe&function=authadldtray&filename=authadldtray.php",
				"text"=> "Active Directory");
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
		if(GetAuthParameter("ncsa","enabled")>0)
		{
			$item=array("classname"=> "samsauth",
				"target"=> "tray",
				"url"=> "tray.php?show=exe&function=authncsatray&filename=authncsatray.php",
				"text"=> "NCSA");
			treeFolderItem($item);
		}
		treeFolderClose();
	}
  }
 ?>