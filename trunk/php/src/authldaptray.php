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



function RemoveSyncGroup()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

  if(isset($_GET["rmsyncgroupname"])) $rmsyncgroupname=$_GET["rmsyncgroupname"];
  PageTop("config_48.jpg","$RemoveSyncGroup_authldaptray_1");
  $i=0;
  while(strlen($rmsyncgroupname[$i])>0)
  {
	$QUERY="DELETE FROM auth_param WHERE s_auth='ldap' AND s_param='ldapgroup' AND s_value='$rmsyncgroupname[$i]'";
	$DB->samsdb_query($QUERY);
	
	echo "<B>$rmsyncgroupname[$i]</B><BR>";
	$i++;
  }

}

function AddSyncGroup()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

  if(isset($_GET["addsyncgroupname"])) $addsyncgroupname=$_GET["addsyncgroupname"];

  PageTop("config_48.jpg","$AddSyncGroup_authldaptray_1");
  $i=0;
  while(strlen($addsyncgroupname[$i])>0)
  {
	$result=$DB->samsdb_query("INSERT INTO auth_param (s_auth, s_param, s_value) VALUES('ldap', 'ldapgroup', '$addsyncgroupname[$i]') ");

	echo "<B>$addsyncgroupname[$i]</B><BR>";
	$i++;
  }

}

 
function AuthLDAPValues()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $DB=new SAMSDB();
  $DB2=new SAMSDB();

  PageTop("config_48.jpg",$lframe_sams_Auth_Title_LDAP_Config);
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/syncwithldap.html\">$documentation</A>");
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

  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$lframe_sams_Auth_LDAP_connections_test\">\n");
  print("</FORM>\n");

  $num_rows=$DB->samsdb_query_value("select s_value from auth_param where s_auth='ldap' AND  s_param='ldapgroup'");
  if($num_rows>0)
  {
	print("<FORM NAME=\"rmsyncgroupform\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"removesyncgroup\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authldaptray.php\">\n");

	print("<SELECT NAME=\"rmsyncgroupname[]\" SIZE=3 TABINDEX=30 MULTIPLE>\n");
	while($row=$DB->samsdb_fetch_array())
	{
		print("<OPTION VALUE=\"".$row['s_value']."\"> ".$row['s_value']."");
	}
	print("</SELECT>\n");

	print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$AuthLDAPValues_authldaptray_1 \">\n");
	print("</FORM>\n");
  }

  $num_rows=$DB->samsdb_query_value("SELECT sgroup.s_name FROM sgroup ");
  if($num_rows>0)
  {
	print("<FORM NAME=\"addsyncgroupform\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addsyncgroup\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authldaptray.php\">\n");

	print("<SELECT NAME=\"addsyncgroupname[]\" SIZE=3 TABINDEX=30 MULTIPLE>\n");
	while($row=$DB->samsdb_fetch_array())
	{
		$QUERY="SELECT * FROM auth_param WHERE s_param='ldapgroup' AND s_value='".$row['s_name']."'";

		$num_rows=$DB2->samsdb_query_value($QUERY);
		if($num_rows==0)
			print("<OPTION VALUE=\"".$row['s_name']."\"> ".$row['s_name']."");
	}
	print("</SELECT>\n");

	print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$AuthLDAPValues_authldaptray_2\">\n");
	print("</FORM>\n");
  }


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

  if($USERConf->ToWebInterfaceAccess("UC")==1 )
    {
	print("<TABLE border=0 WIDTH=95%>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=25%>");
	print("<B>$authtype_AuthTray<BR><FONT SIZE=\"+1\" COLOR=\"BLUE\">LDAP</FONT></B>\n");

	ExecuteFunctions("./src", "authldapbuttom","1");

     }
  print("<TD>\n");
  print("</TABLE>\n");



}

?>
