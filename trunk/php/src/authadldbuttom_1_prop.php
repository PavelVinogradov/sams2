<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
 


function AuthADLDReConfig()
{
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);
  UpdateAuthParameter("adld","adldserver");
  UpdateAuthParameter("adld","basedn");
  UpdateAuthParameter("adld","adadmin");
  UpdateAuthParameter("adld","adadminpasswd");
  UpdateAuthParameter("adld","usergroup");

  print("<SCRIPT>\n");
  print("  parent.tray.location.href=\"tray.php?show=exe&function=authadldtray&filename=authadldtray.php\";\n");
  print("</SCRIPT> \n");

}

function AuthADLDReConfigForm()
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
  

  PageTop("config_48.jpg","$authadldbuttom_1_AuthADLDReConfigForm_1");
  print("<P>\n");


  print("<FORM NAME=\"adldreconfigform\" ACTION=\"main.php\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"authadldreconfig\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authadldbuttom_1_prop.php\">\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");


  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ad_server</B>\n");
  $value=GetAuthParameter("adld","adldserver");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adldserver\" VALUE=\"$value\" >\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ad_domain</B>\n");
  $value=GetAuthParameter("adld","basedn");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"basedn\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ad_admin</B>\n");
  $value=GetAuthParameter("adld","adadmin");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adadmin\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ad_passwd</B>\n");
  $value=GetAuthParameter("adld","adadminpasswd");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adadminpasswd\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$ad_group</B>\n");
  $value=GetAuthParameter("adld","usergroup");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"usergroup\" VALUE=\"$value\">\n");

  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("</FORM>\n");
}



function authadldbuttom_1_prop()
{
  global $SAMSConf;
  global $USERConf;

 if(isset($_GET["id"])) $id=$_GET["id"];
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       GraphButton("main.php?show=exe&function=authadldreconfigform&filename=authadldbuttom_1_prop.php",
	               "basefrm","config_32.jpg","config_48.jpg","$authadldbuttom_1_AuthADLDReConfigForm_1");
    }

}







?>
