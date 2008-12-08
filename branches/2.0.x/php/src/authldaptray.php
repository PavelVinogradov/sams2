<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function LDAPtest()
{
  global $SAMSConf;
$info=array();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

       //create the LDAP connection

  	$adldserver=GetAuthParameter("ldap","ldapserver");
	$basedn=GetAuthParameter("ldap","basedn");
	$adadmin=GetAuthParameter("ldap","adadmin");
	$adadminpasswd=GetAuthParameter("ldap","adadminpasswd");
	$usergroup=GetAuthParameter("ldap","usergroup");

/*
	$LDAPBASEDN2=strtok($basedn,".");
	$LDAPBASEDN="DC=$LDAPBASEDN2";
	while(strlen($LDAPBASEDN2)>0)
	{
		$LDAPBASEDN2=strtok(".");
		if(strlen($LDAPBASEDN2)>0)
			$LDAPBASEDN="$LDAPBASEDN,DC=$LDAPBASEDN2";
	}
*/
	print("<H1>Test LDAP connection</H1>");
	include('ldap.php');
	$samsldap = new sams_ldap($adldserver, $basedn, $usergroup, $adadmin, $adadminpasswd);
	if($samsldap != NULL)
	{

		$userdata=$samsldap->GetUsersData();
	        print("<TABLE CLASS=samstable>");
        	print("<TH width=5%>No");
        	print("<TH >LDAP users");
        	print("<TH >");
		for($j=0;$j<$userdata['userscount'];$j++)
		{
			echo "<TR><TD>$j:<TD> ".$userdata['uid'][$j];
			echo "<TD> ".$userdata['cn'][$j];
			echo "<TD>$aaa ";
        		//echo "CN=".$userdata['cn'][$j]."   userid=".$userdata['uid'][$j]."   homeDirectory=".$userdata['homeDirectory'][$j]." \n";
		}
		echo "</TABLE>";


	}    
  

}   





 
function AuthLDAPValues()
{
  PageTop("config_48.jpg","LDAP configuration ");
  print("<P>\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");


  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>LDAP server</B>\n");
  $value=GetAuthParameter("ldap","ldapserver");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Base DN</B>\n");
  $value=GetAuthParameter("ldap","basedn");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Root DN</B>\n");
  $value=GetAuthParameter("ldap","adadmin");
  print("<TD>$value\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>LDAP administrator password</B>\n");
  $value=GetAuthParameter("ldap","adadminpasswd");
  print("<TD>$value\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>LDAP user group</B>\n");
  $value=GetAuthParameter("ldap","usergroup");
  print("<TD>$value\n");

  print("</TABLE>\n");

  print("<FORM NAME=\"ldapreconfigform\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"ldaptest\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authldaptray.php\">\n");

  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"test ldap configurations\">\n");
  print("</FORM>\n");
}




function AuthLDAPTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=authldapvalues&filename=authldaptray.php\";\n");
      print("</SCRIPT> \n");

  if($SAMSConf->access==2)
    {
	print("<TABLE border=0 WIDTH=95%>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=25%>");
	print("<B><FONT SIZE=\"+1\">ADLD</FONT></B>\n");

	ExecuteFunctions("./src", "authldapbuttom","1");

     }
  print("<TD>\n");
  print("</TABLE>\n");



}

?>
