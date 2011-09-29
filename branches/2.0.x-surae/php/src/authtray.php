<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
function AuthEnabled()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

  if(isset($_GET["adld"])) $adld=$_GET["adld"];
  if(isset($_GET["ntlm"])) $ntlm=$_GET["ntlm"];
  if(isset($_GET["ldap"])) $ldap=$_GET["ldap"];
  if(isset($_GET["ncsa"])) $ncsa=$_GET["ncsa"];
  if(isset($_GET["ip"])) $ip=$_GET["ip"];

  if($ip=="on") $ip=1; else $ip=0;
  if($adld=="on") $adld=1; else $adld=0;
  if($ntlm=="on") $ntlm=1; else $ntlm=0;
  if($ldap=="on") $ldap=1; else $ldap=0;
  if($ncsa=="on") $ncsa=1; else $ncsa=0;


	$DB=new SAMSDB();
	$num_rows=$DB->samsdb_query("UPDATE auth_param SET s_value='$ip' WHERE s_auth='ip' AND s_param='enabled' ");
	$num_rows=$DB->samsdb_query("UPDATE auth_param SET s_value='$adld' WHERE s_auth='adld' AND s_param='enabled' ");
	$num_rows=$DB->samsdb_query("UPDATE auth_param SET s_value='$ntlm' WHERE s_auth='ntlm' AND s_param='enabled' ");
	$num_rows=$DB->samsdb_query("UPDATE auth_param SET s_value='$ldap' WHERE s_auth='ldap' AND s_param='enabled' ");
	$num_rows=$DB->samsdb_query("UPDATE auth_param SET s_value='$ncsa' WHERE s_auth='ncsa' AND s_param='enabled' ");

	print("<SCRIPT>\n");
	print("        parent.basefrm.location.href=\"tray.php?show=exe&function=authtray&filename=authtray.php\";\n");
	print("        parent.lframe.location.href=\"lframe.php\";\n");
	print("</SCRIPT> \n");

}

function AuthEnabledForm()
{
  global $SAMSConf;
  global $USERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

  $ADLDCHECKED="";
  $NTLMCHECKED="";
  $LDAPCHECKED="";
  $NCSACHECKED="";
  PageTop("config_48.jpg","$authtray_AuthEnabledForm_1");
  print("<P>\n");

  print("<FORM NAME=\"authenabledform\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"authenabled\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authtray.php\">\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TH bgcolor=blanchedalmond>$authtray_AuthEnabledForm_2\n");
  print("<TH bgcolor=blanchedalmond>$authtray_AuthEnabledForm_3\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>IP </B>\n");
  if(GetAuthParameter("ip","enabled")>0)
	$IPCHECKED="CHECKED";
  print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"ip\" $IPCHECKED> \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Active Directory </B>\n");
  if(GetAuthParameter("adld","enabled")>0)
	$ADLDCHECKED="CHECKED";
  print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"adld\" $ADLDCHECKED> \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>NTLM</B>\n");
  if(GetAuthParameter("ntlm","enabled")>0)
	$NTLMCHECKED="CHECKED";
  print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"ntlm\" $NTLMCHECKED> \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>LDAP</B>\n");
  if(GetAuthParameter("ldap","enabled")>0)
	$LDAPCHECKED="CHECKED";
  print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"ldap\" $LDAPCHECKED> \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>NCSA</B>\n");
  if(GetAuthParameter("ncsa","enabled")>0)
	$NCSACHECKED="CHECKED";
  print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"ncsa\" $NCSACHECKED> \n");

  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$lframe_sams_Auth_ConfigureButton\">\n");
  print("</FORM>\n");
  print("<P><IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/authorization.html\">$documentation</A>");

} 





function AuthTray()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=authenabledform&filename=authtray.php\";\n");
      print("</SCRIPT> \n");
/*
  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
	print("<TABLE border=0 WIDTH=95%>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=25%>");
	print("<B><FONT SIZE=\"+1\">Authorisation</FONT></B>\n");

	ExecuteFunctions("./src", "authbuttom","1");

	print("<TD>\n");
	print("</TABLE>\n");
     }
*/


}

?>
