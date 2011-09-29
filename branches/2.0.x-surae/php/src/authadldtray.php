<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */


function ADLDtest()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

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
        print("<TH >$adldtest_1_authldaptray $basedn");
	for($i=0,$j=1;$i<$gcount;$i++,$j++)
	{
		$groupname = UTF8ToSAMSLang($groups[$i]);
		echo "<TR><TD>$j:<TD>$groupname <BR>";
	}
	echo "</TABLE><P>";

	$users=$ldap->all_users($include_desc = false, $search = "*", $sorted = true);
	$count=count($users);
        print("<TABLE CLASS=samstable>");
        print("<TH width=5%>No");
        print("<TH >$adldtest_2_authldaptray $basedn");
        print("<TH > ");
	$i=0;
	foreach ($users as $user) 
	{
		$i++;
		$samaccountname = UTF8ToSAMSLang($user["samaccountname"]);
		$displayname = UTF8ToSAMSLang($user["displayname"]);
        	echo "<TR><TD>$i: <TD> $samaccountname <TD> $displayname";
    	}
	echo "</TABLE>";

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
  PageTop("config_48.jpg","$RemoveSyncGroup_authadldtray_1");
  $i=0;
  while(strlen($rmsyncgroupname[$i])>0)
  {
	$QUERY="DELETE FROM auth_param WHERE s_auth='adld' AND s_param='adldgroup' AND s_value='$rmsyncgroupname[$i]'";
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

  PageTop("config_48.jpg","$AddSyncGroup_authadldtray_1");
  $i=0;
  while(strlen($addsyncgroupname[$i])>0)
  {
	$result=$DB->samsdb_query("INSERT INTO auth_param (s_auth, s_param, s_value) VALUES('adld', 'adldgroup', '$addsyncgroupname[$i]') ");

	echo "<B>$addsyncgroupname[$i]</B><BR>";
	$i++;
  }

}


function AuthADLDValues()
{
  global $SAMSConf;
  global $USERConf;
  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  PageTop("config_48.jpg","$AuthADLDValues_authadldtray_1 ");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/syncwithad.html\">$documentation</A>");
  print("<P>\n");
  print("<P>\n");

  $DB=new SAMSDB();
  $DB2=new SAMSDB();
  $result=$DB->samsdb_query_value("SELECT s_value FROM auth_param WHERE s_auth='adld' AND s_param='adldgroup'");
  if($result>0)
  {
	echo "<H3>$AuthADLDValues_authadldtray_2:</H3>";
	while($row=$DB->samsdb_fetch_array())
	{
		echo "<B>".$row['s_value']."</B><BR>\n";
	}
	echo "<P>";
  }

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");


  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ad_server</B>\n");
  $value=GetAuthParameter("adld","adldserver");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ad_domain</B>\n");
  $value=GetAuthParameter("adld","basedn");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ad_admin</B>\n");
  $value=GetAuthParameter("adld","adadmin");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ad_passwd</B>\n");
  $value=GetAuthParameter("adld","adadminpasswd");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ad_group</B>\n");
  $value=GetAuthParameter("adld","usergroup");
  print("<TD>$value \n");

  print("</TABLE>\n");

  print("<FORM NAME=\"adldreconfigform\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"adldtest\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authadldtray.php\">\n");

  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$AuthADLDValues_authadldtray_AD_connections_test\">\n");
  print("</FORM>\n");

  $num_rows=$DB->samsdb_query_value("select s_value from auth_param where s_auth='adld' AND  s_param='adldgroup';");
  if($num_rows>0)
  {
	print("<FORM NAME=\"rmsyncgroupform\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"removesyncgroup\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authadldtray.php\">\n");

	print("<SELECT NAME=\"rmsyncgroupname[]\" SIZE=3 TABINDEX=30 MULTIPLE>\n");
	while($row=$DB->samsdb_fetch_array())
	{
		print("<OPTION VALUE=\"".$row['s_value']."\"> ".$row['s_value']."");
	}
	print("</SELECT>\n");

	print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$AuthADLDValues_authadldtray_3 \">\n");
	print("</FORM>\n");
  }

  $num_rows=$DB->samsdb_query_value("SELECT sgroup.s_name FROM sgroup ");
  if($num_rows>0)
  {
	print("<FORM NAME=\"addsyncgroupform\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addsyncgroup\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authadldtray.php\">\n");

	print("<SELECT NAME=\"addsyncgroupname[]\" SIZE=3 TABINDEX=30 MULTIPLE>\n");
	while($row=$DB->samsdb_fetch_array())
	{
		$QUERY="SELECT * FROM auth_param WHERE s_param='adldgroup' AND s_value='".$row['s_name']."'";

		$num_rows=$DB2->samsdb_query_value($QUERY);
		if($num_rows==0)
			print("<OPTION VALUE=\"".$row['s_name']."\"> ".$row['s_name']."");
	}
	print("</SELECT>\n");

	print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$AuthADLDValues_authadldtray_4 \">\n");
	print("</FORM>\n");
  }

}


function AuthADLDTray()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=authadldvalues&filename=authadldtray.php\";\n");
      print("</SCRIPT> \n");

  if($USERConf->ToWebInterfaceAccess("UC")==1 )
    {
	print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=30%>");
	print("<B>$authtype_AuthTray<BR><FONT SIZE=\"+1\" COLOR=\"BLUE\">ADLD</FONT></B>\n");

	ExecuteFunctions("./src", "authadldbuttom","1");
	print("<TD> </TD>\n");
	print("</TABLE>\n");

     }

/*
	print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=\"30%\"\">");
	print("<B>Proxy<BR><FONT COLOR=\"BLUE\">$PROXYConf->s_description</FONT></B>\n");

	ExecuteFunctions("./src", "proxybuttom","1");
  
	print("<TD>\n");
	print("</TABLE>\n");

*/


}

?>
