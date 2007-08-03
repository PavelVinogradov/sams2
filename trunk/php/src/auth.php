<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */



function UserAccess()
{
  global $SAMSConf;
  //echo " adminname ='$SAMSConf->adminname'";
  $len=strlen($SAMSConf->adminname);
  //echo " length=$len  adminname ='$SAMSConf->adminname'";
  $access=0;
  if(strlen($SAMSConf->adminname)>0)
    {
	if(strtolower($SAMSConf->adminname)=="auditor")
          {
            $access=1;
	  }
	else  
          $access=2;
    }
  //echo "access=$access";  
  return($access);
}



function ReturnLanguage($filename)
{
  $finp=fopen($filename,"rt");
  while(feof($finp)==0)
     {
       $string=fgets($finp, 10000);
         if(strstr($string,"#LANGUAGE:" )!=FALSE)
          {
              $language=str_replace("#LANGUAGE:","",$string);
              return($language);
	  }
     }
  fclose($finp);
}



function ShowDomainUserName()
{
  global $SAMSConf;
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if(strlen($SAMSConf->domainusername)>0)
     {
       PageTop("getpassword.jpg","$auth_ShowDomainUserName_1 $SAMSConf->domainusername");
     }
   else
     {
       PageTop("warning.jpg","$auth_ShowDomainUserName_2");
     }

}



function UserAuthetificateForm($usernick,$userdomain)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("getpassword.jpg","$auth_UserAuthetificateForm_1");
  print("<P>\n");
  print("<FORM NAME=\"USERPASSWORD\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"userauth\">\n");
  print("<TABLE WIDTH=\"90%\">\n");
  print("<TR>\n");
  print("<TD><B>login:</B>\n");

  if($SAMSConf->AUTH=="ntlm"||$SAMSConf->AUTH=="adld")
    {
       if($SAMSConf->NTLMDOMAIN=="Y")
         print("<TD><INPUT TYPE=\"TEXT\" NAME=\"usernick\" VALUE=\"$userdomain+$usernick\" SIZE=30>\n");
       else
         print("<TD><INPUT TYPE=\"TEXT\" NAME=\"usernick\" VALUE=\"$usernick\" SIZE=30>\n");	 
    }
  if($SAMSConf->AUTH=="ip"||$SAMSConf->AUTH=="ncsa")
    {
      print("<TD><INPUT TYPE=\"TEXT\" NAME=\"usernick\" VALUE=\"$usernick\" SIZE=30>\n");
    }
  print("<TR>\n");
  print("<TD><B>password:</B>\n");
  print("<TD><INPUT TYPE=\"PASSWORD\" NAME=\"userid\" SIZE=30> \n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Ok\">\n");
  print("</FORM>\n");
}



function ShowCookieInfo($user,$passwd)
{
  global $SAMSConf;
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $name=UserAuthenticate($user,$passwd);
  if(strlen($name)>0)
     {
       if(strstr("$name",$SAMSConf->adminname))
         {
            UpdateLog("$SAMSConf->adminname","$auth_ShowCookieInfo_1 $SAMSConf->adminname","04");
            PageTop("admin_48.jpg","$auth_ShowCookieInfo_2 <FONT COLOR=\"BLUE\">$name</FONT>");
         }
    }
  else
    {
      PageTop("warning.jpg","$auth_ShowCookieInfo_3");
    }
}

function CheckUserPassword($user,$passwd)
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  mysql_select_db($SAMSConf->SAMSDB);

  $result=mysql_query("SELECT * FROM passwd WHERE user=\"$user\"&&pass=\"$passwd\" ");
  $row=mysql_fetch_array($result);
  PageTop("getpassword.jpg","$auth_CheckUserPassword_1 <FONT COLOR=\"BLUE\">$row[user]</FONT>");
}

function SetUserCookie($user,$passwd)
{
  //$newuser=crypt($user,"00");
  $newpasswd=crypt($passwd,"00");
  setcookie("user","$user");
  setcookie("passwd","$newpasswd");
}


function GetUserPasswordForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("getpassword.jpg","$auth_GetUserPasswordForm_1");
  print("<BR>$auth_GetUserPasswordForm_2");
  print("<P>\n");
  print("<FORM NAME=\"GETUSERPASSWORD\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"setcookie\">\n");
  print("<TABLE WIDTH=\"90%\">\n");
  print("<TR>\n");
  print("<TD><B>login:</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"username\" SIZE=30>\n");
  print("<TR>\n");
  print("<TD><B>password:</B>\n");
  print("<TD><INPUT TYPE=\"PASSWORD\" NAME=\"userid\" SIZE=30> \n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Ok\">\n");
  print("</FORM>\n");
}


function GetPasswordForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("getpassword.jpg","$auth_GetPasswordForm_1");
  print("<P>\n");
  print("<FORM NAME=\"UPDATEUSER\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"adminpasswd\">\n");
  print("<INPUT TYPE=\"PASSWORD\" NAME=\"userid\" SIZE=30> \n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$auth_GetPasswordForm_2\">\n");
  print("</FORM>\n");

}


function UserAuthenticate($user,$passwd)
{
  global $SAMSConf;
  db_connect($SAMSConf->SAMSDB) or exit();
    mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM passwd WHERE user=\"$user\"&&pass=\"$passwd\" ");
  $row=mysql_fetch_array($result);

  return("$row[user]");
}

function SaveUserName($passwd,$number)
{
  global $SAMSConf;
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $newpasswd=crypt($passwd,"00");
  //print("passwd=$newpasswd=");
  db_connect($SAMSConf->SAMSDB) or exit();
    mysql_select_db($SAMSConf->SAMSDB);
    
  $result=mysql_query("SELECT * FROM passwd WHERE pass=\"$newpasswd\" ");
  $row=mysql_fetch_array($result);
  if($number==0)
    setcookie("user","$row[user]");
  else
    {
      PageTop("puser_open.gif","$auth_SaveUserName_1 \"$row[user]\" ");
    }
}


function GetAdminPasswordForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("admin_48.jpg","$auth_GetAdminPasswordForm_1");
  print("<P>\n");
  print("<FORM NAME=\"UPDATEUSER\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"adminpasswd\">\n");
  print("<INPUT TYPE=\"PASSWORD\" NAME=\"userid\" SIZE=30> \n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$auth_GetAdminPasswordForm_2\">\n");
  print("</FORM>\n");

}

function UserPasswordTest($username,$passwd)
{
  global $SAMSConf;
  
  $newpasswd=crypt($passwd,"00");
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  $result=mysql_query("SELECT * FROM passwd  WHERE user=\"$username\" ");
  $row=mysql_fetch_array($result);
  //print("$username=$row[user] $newpasswd=$row[pass]<BR>");
}



?>
