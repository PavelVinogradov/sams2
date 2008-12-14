<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
 


function AuthLDAPReConfig()
{
  UpdateAuthParameter("ldap","ldapserver");
  UpdateAuthParameter("ldap","basedn");
  UpdateAuthParameter("ldap","adadmin");
  UpdateAuthParameter("ldap","adadminpasswd");
  UpdateAuthParameter("ldap","usergroup");

}

function AuthLDAPReConfigForm()
{
  global $SAMSConf;
  global $PROXYConf;
  $DB=new SAMSDB(&$SAMSConf);

  $files=array();
  if(isset($_GET["id"])) $proxy_id=$_GET["id"];
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($SAMSConf->access!=2 && $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "C")!=1)
{       exit;     }
  

  PageTop("config_48.jpg","LDAP Configuration ");
  print("<P>\n");


  print("<FORM NAME=\"ldapreconfigform\" ACTION=\"main.php\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"authldapreconfig\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authldapbuttom_1_prop.php\">\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");


  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>LDAP server</B>\n");
  $value=GetAuthParameter("ldap","ldapserver");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"ldapserver\" VALUE=\"$value\" >\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Base DN</B>\n");
  $value=GetAuthParameter("ldap","basedn");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"basedn\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>LDAP administrator</B>\n");
  $value=GetAuthParameter("ldap","adadmin");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adadmin\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>LDAP administrator password</B>\n");
  $value=GetAuthParameter("ldap","adadminpasswd");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adadminpasswd\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>LDAP user group</B>\n");
  $value=GetAuthParameter("ldap","usergroup");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"usergroup\" VALUE=\"$value\">\n");

  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("</FORM>\n");
}



function authldapbuttom_1_prop()
{
  global $SAMSConf;
 if(isset($_GET["id"])) $id=$_GET["id"];
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "C")==1)
    {
       GraphButton("main.php?show=exe&function=authldapreconfigform&filename=authldapbuttom_1_prop.php",
	               "basefrm","config_32.jpg","config_48.jpg","LDAP authorisation configure");
    }

}







?>
