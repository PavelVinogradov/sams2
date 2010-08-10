<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
 


function AuthNTLMReConfig()
{
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);
  UpdateAuthParameter("ntlm","ntlmserver");
  UpdateAuthParameter("ntlm","ntlmdomain");
  UpdateAuthParameter("ntlm","ntlmadmin");
  UpdateAuthParameter("ntlm","ntlmadminpasswd");
  UpdateAuthParameter("ntlm","ntlmusergroup");
  print("<SCRIPT>\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=authntlmvalues&filename=authntlmtray.php\";\n");
  print("</SCRIPT> \n");

}

function AuthNTLMReConfigForm()
{
  global $SAMSConf;
  global $PROXYConf;
  global $USERConf;
  $DB=new SAMSDB();

  $files=array();
  if(isset($_GET["id"])) $proxy_id=$_GET["id"];
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;
  

  PageTop("config_48.jpg","$authntlmbuttom_1_prop_AuthNTLMReConfigForm_1 ");
  print("<P>\n");


  print("<FORM NAME=\"adldreconfigform\" ACTION=\"main.php\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"authntlmreconfig\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authntlmbuttom_1_prop.php\">\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");


  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ntlm_server</B>\n");
  $value=GetAuthParameter("ntlm","ntlmserver");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"ntlmserver\" VALUE=\"$value\" >\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ntlm_domain</B>\n");
  $value=GetAuthParameter("ntlm","ntlmdomain");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"ntlmdomain\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ntlm_admin</B>\n");
  $value=GetAuthParameter("ntlm","ntlmadmin");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"ntlmadmin\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ntlm_passwd</B>\n");
  $value=GetAuthParameter("ntlm","ntlmadminpasswd");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"ntlmadminpasswd\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ntlm_group</B>\n");
  $value=GetAuthParameter("ntlm","ntlmusergroup");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"ntlmusergroup\" VALUE=\"$value\">\n");

  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("</FORM>\n");
}



function authntlmbuttom_1_prop()
{
  global $SAMSConf;
  global $USERConf;

 if(isset($_GET["id"])) $id=$_GET["id"];
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       GraphButton("main.php?show=exe&function=authntlmreconfigform&filename=authntlmbuttom_1_prop.php",
	               "basefrm","config_32.jpg","config_48.jpg","$authntlmbuttom_1_prop_AuthNTLMReConfigForm_1");
    }

}







?>
