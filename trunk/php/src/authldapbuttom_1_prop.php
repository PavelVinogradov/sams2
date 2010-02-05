<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
 


function AuthLDAPReConfig()
{
  global $USERConf;
  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);

  UpdateAuthParameter("ldap","ldapserver");
  UpdateAuthParameter("ldap","basedn");
  UpdateAuthParameter("ldap","adadmin");
  UpdateAuthParameter("ldap","adadminpasswd");
  UpdateAuthParameter("ldap","usersrdn");
  UpdateAuthParameter("ldap","usersfilter");
  UpdateAuthParameter("ldap","usernameattr");
  UpdateAuthParameter("ldap","groupsrdn");
  UpdateAuthParameter("ldap","groupsfilter");

  print("<SCRIPT>\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=authldapvalues&filename=authldaptray.php\";\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");
}

function AuthLDAPReConfigForm()
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
  

  PageTop("config_48.jpg","$authadldbuttom_1_prop_AuthLDAPReConfigForm_1 ");
  print("<P>\n");


  print("<FORM NAME=\"ldapreconfigform\" ACTION=\"main.php\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"authldapreconfig\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authldapbuttom_1_prop.php\">\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");


  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Server</B>\n");
  $value=GetAuthParameter("ldap","ldapserver");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"ldapserver\" VALUE=\"$value\" >\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Base DN</B>\n");
  $value=GetAuthParameter("ldap","basedn");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"basedn\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Bind DN</B>\n");
  $value=GetAuthParameter("ldap","adadmin");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adadmin\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Bind password</B>\n");
  $value=GetAuthParameter("ldap","adadminpasswd");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adadminpasswd\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Users RDN</B>\n");
  $value=GetAuthParameter("ldap","usersrdn");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"usersrdn\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Users filter</B>\n");
  $value=GetAuthParameter("ldap","usersfilter");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"usersfilter\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>User name attribute</B>\n");
  $value=GetAuthParameter("ldap","usernameattr");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"usernameattr\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Groups RDN</B>\n");
  $value=GetAuthParameter("ldap","groupsrdn");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"groupsrdn\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Groups filter</B>\n");
  $value=GetAuthParameter("ldap","groupsfilter");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"groupsfilter\" VALUE=\"$value\">\n");

  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("</FORM>\n");
}



function authldapbuttom_1_prop()
{
  global $SAMSConf;
  global $USERConf;
 if(isset($_GET["id"])) $id=$_GET["id"];
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       GraphButton("main.php?show=exe&function=authldapreconfigform&filename=authldapbuttom_1_prop.php",
	               "basefrm","config_32.jpg","config_48.jpg","$authadldbuttom_1_prop_AuthLDAPReConfigForm_1");
    }

}







?>
