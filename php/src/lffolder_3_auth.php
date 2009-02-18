<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_3_auth()
 {
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
	if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "AUC")==1)
	{
        print("   auth = insFld(sams, gFld2(\"Authorisation\", \"tray.php?show=exe&filename=authtray.php&function=authtray\", \"auth.gif\"))\n");

	if(GetAuthParameter("adld","enabled")>0)
		print("      insDoc(auth, gLnk(\"D\", \"ActiveDirectory\", \"tray.php?show=exe&function=authadldtray&filename=authadldtray.php\",\"auth.gif\"))\n");
	if(GetAuthParameter("ntlm","enabled")>0)
		print("      insDoc(auth, gLnk(\"D\", \"NTLM\", \"tray.php?show=exe&function=authntlmtray&filename=authntlmtray.php\",\"auth.gif\"))\n");
	if(GetAuthParameter("ldap","enabled")>0)
		print("      insDoc(auth, gLnk(\"D\", \"LDAP\", \"tray.php?show=exe&function=authldaptray&filename=authldaptray.php\",\"auth.gif\"))\n");
	}
  }
 ?>