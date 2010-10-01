<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function NTLMtest()
{
  global $SAMSConf;
  global $USERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  print("<H1>Test NTLM connection</H1>");

  $ntlmserver=GetAuthParameter("ntlm","ntlmserver");
  $ntlmdomain=GetAuthParameter("ntlm","ntlmdomain");
  $ntlmadmin=GetAuthParameter("ntlm","ntlmadmin");
  $ntlmpasswd=GetAuthParameter("ntlm","ntlmadminpasswd");

  $users=ExecuteShellScript("getntlmgroups","$LANG");
  $a=explode("|",$users);
  $acount=count($a);

  print("<TABLE CLASS=samstable>");
  print("<TH width=5%>No");
  print("<TH width=95%>NTLM groups");
  for($i=0;$i<$acount;$i++)
	if(strlen($a[$i])>0)
		echo("<TR><TD>$i<TD>$a[$i]<BR>\n");
  echo "</TABLE><P>";

  $users=ExecuteShellScript("getntlmusers","$LANG");
  $a=explode("|",$users);
  $acount=count($a);

  print("<TABLE CLASS=samstable>");
  print("<TH width=5%>No");
  print("<TH width=95%>NTLM users");
  for($i=0;$i<$acount;$i++)
	if(strlen($a[$i])>0)
		echo("<TR><TD>$i<TD>$a[$i]<BR>\n");
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
  PageTop("config_48.jpg","$RemoveSyncGroup_authntlmtray_1");
  $i=0;
  while(strlen($rmsyncgroupname[$i])>0)
  {
	$QUERY="DELETE FROM auth_param WHERE s_auth='ntlm' AND s_param='ntlmgroup' AND s_value='$rmsyncgroupname[$i]'";
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

  PageTop("config_48.jpg","$AddSyncGroup_authntlmtray_1");
  $i=0;
  while(strlen($addsyncgroupname[$i])>0)
  {
	$result=$DB->samsdb_query("INSERT INTO auth_param (s_auth, s_param, s_value) VALUES('ntlm', 'ntlmgroup', '$addsyncgroupname[$i]') ");

	echo "<B>$addsyncgroupname[$i]</B><BR>";
	$i++;
  }

}


function AuthNTLMValues()
{
  global $SAMSConf;
  global $USERConf;
  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $DB=new SAMSDB();
  $DB2=new SAMSDB();

  PageTop("config_48.jpg",$lframe_sams_Auth_Title_NTLM_Config);
//  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
//  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/syncwithntlm.html\">Documentation</A>");
//  print("<P>\n");
  print("<P>\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ntlm_server</B>\n");
  $value=GetAuthParameter("ntlm","ntlmserver");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ntlm_domain</B>\n");
  $value=GetAuthParameter("ntlm","ntlmdomain");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ntlm_admin</B>\n");
  $value=GetAuthParameter("ntlm","ntlmadmin");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ntlm_passwd</B>\n");
  $value=GetAuthParameter("ntlm","ntlmadminpasswd");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ntlm_group</B>\n");
  $value=GetAuthParameter("ntlm","ntlmusergroup");
  print("<TD>$value \n");


  print("</TABLE>\n");

  print("<FORM NAME=\"adldreconfigform\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"ntlmtest\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authntlmtray.php\">\n");

  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$lframe_sams_Auth_NTLM_config_test\">\n");
  print("</FORM>\n");

  $num_rows=$DB->samsdb_query_value("select s_value from auth_param where s_auth='ntlm' AND  s_param='ntlmgroup'");
  if($num_rows>0)
  {
	print("<FORM NAME=\"rmsyncgroupform\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"removesyncgroup\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authntlmtray.php\">\n");

	print("<SELECT NAME=\"rmsyncgroupname[]\" SIZE=3 TABINDEX=30 MULTIPLE>\n");
	while($row=$DB->samsdb_fetch_array())
	{
		print("<OPTION VALUE=\"".$row['s_value']."\"> ".$row['s_value']."");
	}
	print("</SELECT>\n");

	print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$AuthNTLMValues_authntlmtray_1 \">\n");
	print("</FORM>\n");
  }

  $num_rows=$DB->samsdb_query_value("SELECT sgroup.s_name FROM sgroup ");
  if($num_rows>0)
  {
	print("<FORM NAME=\"addsyncgroupform\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addsyncgroup\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authntlmtray.php\">\n");

	print("<SELECT NAME=\"addsyncgroupname[]\" SIZE=3 TABINDEX=30 MULTIPLE>\n");
	while($row=$DB->samsdb_fetch_array())
	{
		$QUERY="SELECT * FROM auth_param WHERE s_param='ntlmgroup' AND s_value='".$row['s_name']."'";

		$num_rows=$DB2->samsdb_query_value($QUERY);
		if($num_rows==0)
			print("<OPTION VALUE=\"".$row['s_name']."\"> ".$row['s_name']."");
	}
	print("</SELECT>\n");

	print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$AuthNTLMValues_authntlmtray_2 \">\n");
	print("</FORM>\n");
  }

}




function AuthNTLMTray()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=authntlmvalues&filename=authntlmtray.php\";\n");
      print("</SCRIPT> \n");

  if($USERConf->ToWebInterfaceAccess("UC")==1 )
    {
	print("<TABLE border=0 WIDTH=95%>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=25%>");
	print("<B>$authtype_AuthTray<BR><FONT SIZE=\"+1\" COLOR=\"BLUE\">NTLM</FONT></B>\n");

	ExecuteFunctions("./src", "authntlmbuttom","1");

     }
  print("<TD>\n");
  print("</TABLE>\n");



}

?>
