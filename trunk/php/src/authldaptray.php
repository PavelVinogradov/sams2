<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function LDAPtest()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

  $info=array();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

       //create the LDAP connection

  	$adldserver=GetAuthParameter("ldap","ldapserver");
	$basedn=GetAuthParameter("ldap","basedn");
	$adadmin=GetAuthParameter("ldap","adadmin");
	$adadminpasswd=GetAuthParameter("ldap","adadminpasswd");
	$usersrdn=GetAuthParameter("ldap","usersrdn");
	$usersfilter=GetAuthParameter("ldap","usersfilter");
	$usernameattr=GetAuthParameter("ldap","usernameattr");
	$groupsrdn=GetAuthParameter("ldap","groupsrdn");
	$groupsfilter=GetAuthParameter("ldap","groupsfilter");

	print("<H1>Test LDAP connection</H1>");
	include('ldap.php');
	$samsldap = new sams_ldap($adldserver, $basedn, $usersrdn, $usersfilter, $usernameattr, $groupsrdn, $groupsfilter, $adadmin, $adadminpasswd);
	if($samsldap != NULL)
	{

		$groupdata=$samsldap->GetGroupsData();
		print("<H2>LDAP groups</H2>");
	        print("<TABLE CLASS=samstable>");
        	print("<TH width=5%>No");
        	print("<TH >Name");
        	print("<TH >gid");
        	print("<TH >Description");
		for($j=0;$j<$groupdata['groupscount'];$j++)
		{
			echo "<TR><TD>$j<TD> ".$groupdata['cn'][$j];
			echo "<TD> ".$groupdata['gidNumber'][$j];
			echo "<TD> ".$groupdata['description'][$j];
		}
		echo "</TABLE>";

		$userdata=$samsldap->GetUsersData();
		print("<H2>LDAP users</H2>");
	        print("<TABLE CLASS=samstable>");
        	print("<TH width=5%>No");
        	print("<TH >Name");
        	print("<TH >Display name");
		for($j=0;$j<$userdata['userscount'];$j++)
		{
			echo "<TR><TD>$j<TD> ".$userdata['uid'][$j];
			echo "<TD> ".$userdata['name'][$j];
		}
		echo "</TABLE>";

	}    
  

}   





 
function AuthLDAPValues()
{
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

  PageTop("config_48.jpg","LDAP configuration ");
  print("<P>\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Server</B>\n");
  $value=GetAuthParameter("ldap","ldapserver");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Base DN</B>\n");
  $value=GetAuthParameter("ldap","basedn");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Bind DN</B>\n");
  $value=GetAuthParameter("ldap","adadmin");
  print("<TD>$value\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Bind password</B>\n");
  $value=GetAuthParameter("ldap","adadminpasswd");
  print("<TD>$value\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Users RDN</B>\n");
  $value=GetAuthParameter("ldap","usersrdn");
  print("<TD>$value\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Users filter</B>\n");
  $value=GetAuthParameter("ldap","usersfilter");
  print("<TD>$value\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>User name attribute</B>\n");
  $value=GetAuthParameter("ldap","usernameattr");
  print("<TD>$value\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Groups RDN</B>\n");
  $value=GetAuthParameter("ldap","groupsrdn");
  print("<TD>$value\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Groups filter</B>\n");
  $value=GetAuthParameter("ldap","groupsfilter");
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
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=authldapvalues&filename=authldaptray.php\";\n");
      print("</SCRIPT> \n");

  if($USERConf->ToWebInterfaceAccess("C")==1 )
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
