<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
 


function AuthADLDReConfig()
{
  UpdateAuthParameter("adld","adldserver");
  UpdateAuthParameter("adld","basedn");
  UpdateAuthParameter("adld","adadmin");
  UpdateAuthParameter("adld","adadminpasswd");
  UpdateAuthParameter("adld","usergroup");

}

function AuthADLDReConfigForm()
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
  

  PageTop("config_48.jpg","ADLD Configuration ");
  print("<P>\n");


  print("<FORM NAME=\"adldreconfigform\" ACTION=\"main.php\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"authadldreconfig\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"authadldbuttom_1_prop.php\">\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");


  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>Active Directory server</B>\n");
  $value=GetAuthParameter("adld","adldserver");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adldserver\" VALUE=\"$value\" >\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>AD domain</B>\n");
  $value=GetAuthParameter("adld","basedn");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"basedn\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>AD administrator</B>\n");
  $value=GetAuthParameter("adld","adadmin");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adadmin\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>AD administrator password</B>\n");
  $value=GetAuthParameter("adld","adadminpasswd");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adadminpasswd\" VALUE=\"$value\">\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>AD user group</B>\n");
  $value=GetAuthParameter("adld","usergroup");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"usergroup\" VALUE=\"$value\">\n");

  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("</FORM>\n");
}



function authadldbuttom_1_prop()
{
  global $SAMSConf;
 if(isset($_GET["id"])) $id=$_GET["id"];
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "C")==1)
    {
       GraphButton("main.php?show=exe&function=authadldreconfigform&filename=authadldbuttom_1_prop.php",
	               "basefrm","config_32.jpg","config_48.jpg","ADLD authorisation configure");
    }

}







?>
