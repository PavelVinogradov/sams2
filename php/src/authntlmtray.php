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

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  print("<H1>Test NTLM connection</H1>");

  $ntlmserver=GetAuthParameter("ntlm","ntlmserver");
  $ntlmdomain=GetAuthParameter("ntlm","ntlmdomain");
  $ntlmadmin=GetAuthParameter("ntlm","ntlmadmin");
  $ntlmpasswd=GetAuthParameter("ntlm","ntlmadminpasswd");

  $users=ExecuteShellScript("getntlmgroups","$ntlmserver $ntlmadmin $ntlmpasswd");
  $a=explode("|",$users);
  $acount=count($a);

  print("<TABLE CLASS=samstable>");
  print("<TH width=5%>No");
  print("<TH width=95%>NTLM groups");
  for($i=0;$i<$acount;$i++)
	echo("<TR><TD>$i<TD>$a[$i]<BR>\n");
  echo "</TABLE><P>";

  $users=ExecuteShellScript("getntlmusers","$ntlmserver $ntlmadmin $ntlmpasswd");
  $a=explode("|",$users);
  $acount=count($a);

  print("<TABLE CLASS=samstable>");
  print("<TH width=5%>No");
  print("<TH width=95%>NTLM users");
  for($i=0;$i<$acount;$i++)
	echo("<TR><TD>$i<TD>$a[$i]<BR>\n");
  echo "</TABLE>";


}   
 

function AuthNTLMValues()
{
  global $USERConf;
  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  PageTop("config_48.jpg","NTLM configuration ");
  print("<P>\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Windows domain server</B>\n");
  $value=GetAuthParameter("ntlm","ntlmserver");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Windows domain</B>\n");
  $value=GetAuthParameter("ntlm","ntlmdomain");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Windows domain administrator</B>\n");
  $value=GetAuthParameter("ntlm","ntlmadmin");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Windows domain administrator password</B>\n");
  $value=GetAuthParameter("ntlm","ntlmadminpasswd");
  print("<TD>$value \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Windows domain user group</B>\n");
  $value=GetAuthParameter("ntlm","ntlmusergroup");
  print("<TD>$value \n");


  print("</TABLE>\n");

  print("<FORM NAME=\"adldreconfigform\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"ntlmtest\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authntlmtray.php\">\n");

  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"test ntlm configurations\">\n");
  print("</FORM>\n");

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

  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
	print("<TABLE border=0 WIDTH=95%>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=25%>");
	print("<B><FONT SIZE=\"+1\">NTLM</FONT></B>\n");

	ExecuteFunctions("./src", "authntlmbuttom","1");

     }
  print("<TD>\n");
  print("</TABLE>\n");



}

?>
