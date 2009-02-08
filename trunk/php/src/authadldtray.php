<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */


function ADLDtest()
{
  global $SAMSConf;
  $info=array();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	print("<H1>Test AD connection</H1>");
	require_once("src/adldap.php");
       //create the LDAP connection

  	$adldserver=GetAuthParameter("adld","adldserver");
	$basedn=GetAuthParameter("adld","basedn");
	$adadmin=GetAuthParameter("adld","adadmin");
	$adadminpasswd=GetAuthParameter("adld","adadminpasswd");
	$usergroup=GetAuthParameter("adld","usergroup");

	$LDAPBASEDN2=strtok($basedn,".");
	$LDAPBASEDN="DC=$LDAPBASEDN2";
	while(strlen($LDAPBASEDN2)>0)
	{
		$LDAPBASEDN2=strtok(".");
		if(strlen($LDAPBASEDN2)>0)
			$LDAPBASEDN="$LDAPBASEDN,DC=$LDAPBASEDN2";
	}

 	$pdc=array("$adldserver");
	$options=array(account_suffix=>"@$basedn", base_dn=>"$LDAPBASEDN",domain_controllers=>$pdc, 
	ad_username=>"$adadmin",ad_password=>"$adadminpasswd","","","");

	$ldap=new adLDAP($options);

	$charset=explode(",",$_SERVER['HTTP_ACCEPT_CHARSET']);

	$groups=$ldap->all_groups($include_desc = false, $search = "*", $sorted = true);
	$gcount=count($groups);
        print("<TABLE CLASS=samstable>");
        print("<TH width=5%>No");
        print("<TH >AD domain $basedn groups");
	for($i=0;$i<$gcount;$i++)
	{
		$groupname = UTF8ToSAMSLang($groups[$i]);
		echo "<TR><TD>$i:<TD>$groupname<BR>";
	}
	echo "</TABLE><P>";

	$users=$ldap->all_users($include_desc = false, $search = "*", $sorted = true);
	$count=count($users);
        print("<TABLE CLASS=samstable>");
        print("<TH width=5%>No");
        print("<TH >AD domain $basedn users");
        print("<TH > ");
	for($i=0;$i<$count;$i++)
   	{
		$username = UTF8ToSAMSLang($users[$i]);
        	echo "<TR><TD>$i:<TD> $username ";

		$userinfo=$ldap->user_info( $users[$i], $fields=NULL);
		$displayname = UTF8ToSAMSLang($userinfo[0]["displayname"][0]);
		echo "<TD>$displayname";
    	}
	echo "</TABLE>";

}   




function AuthADLDValues()
{

  PageTop("config_48.jpg","Active Directory configuration ");
  print("<P>\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");


  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Active Directory server</B>\n");
  $value=GetAuthParameter("adld","adldserver");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Base DN</B>\n");
  $value=GetAuthParameter("adld","basedn");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>AD administrator</B>\n");
  $value=GetAuthParameter("adld","adadmin");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>AD administrator password</B>\n");
  $value=GetAuthParameter("adld","adadminpasswd");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>AD user group</B>\n");
  $value=GetAuthParameter("adld","usergroup");
  print("<TD>$value \n");

  print("</TABLE>\n");

  print("<FORM NAME=\"adldreconfigform\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"adldtest\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authadldtray.php\">\n");

  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"test adld configurations\">\n");
  print("</FORM>\n");

}


function AuthADLDTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=authadldvalues&filename=authadldtray.php\";\n");
      print("</SCRIPT> \n");

  if($SAMSConf->access==2)
    {
	print("<TABLE border=0 WIDTH=95%>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=25%>");
	print("<B><FONT SIZE=\"+1\">ADLD</FONT></B>\n");

	ExecuteFunctions("./src", "authadldbuttom","1");

     }
  print("<TD>\n");
  print("</TABLE>\n");



}

?>
