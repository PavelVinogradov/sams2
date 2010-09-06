<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UserAccess()
{
  global $adminname;
  if(strlen($adminname)>0)
    {
      if($adminname=="auditor")
        {
          return(1);
        }
      return(2);
    }
  return(0);
}


function ShowDomainUserName()
{
  global $domainusername;
   if(strlen($domainusername)>0)
     {
       PageTop("getpassword.jpg","Введен пароль пользователя $domainusername");
     }
   else
     {
       PageTop("warning.jpg","Пароль пользователя неопознан контроллером домена");
     }

}


function UserAuthetificateForm($usernick,$userdomain)
{
  global $NTLMDOMAIN;
  global $AUTH;
  PageTop("getpassword.jpg","Введите имя пользователя и пароль");
  print("<P>\n");
  print("<FORM NAME=\"USERPASSWORD\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"userauth\">\n");
  print("<TABLE WIDTH=\"90%\">\n");
  print("<TR>\n");
  print("<TD><B>login:</B>\n");

  if($AUTH=="ntlm")
    {
//      if($NTLMDOMAIN=="Y")
//        print("<TD><INPUT TYPE=\"TEXT\" NAME=\"usernick\" VALUE=\"$userdomain+$usernick\" SIZE=30>\n");
//      else
        print("<TD><INPUT TYPE=\"TEXT\" NAME=\"usernick\" VALUE=\"$usernick\" SIZE=30>\n");

    }
  if($AUTH=="ip"||$AUTH=="ncsa")
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



function ChangeAdmin()
{
//  print("changeadmin");
  PageTop("getpassword.jpg","Смена пользователя ");
  print("<B>ведите пароль пользователя \"Администратор\"</B>");
  print("<P>\n");
  print("<FORM NAME=\"CHANGEADMIN\" ACTION=\"main.php\">\n");
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




function ShowCookieInfo($user,$passwd)
{
  global $adminname;
  $name=UserAuthenticate($user,$passwd);
  if(strlen($name)>0)
     {
       if(strstr("$name",$adminname))
         {
            UpdateLog("$adminname","В системе зарегистрировался администратор $adminname","04");
            PageTop("admin_48.jpg","Введен пароль пользователя <FONT COLOR=\"BLUE\">$name</FONT>");
         }
    }
  else
    {
      PageTop("warning.jpg","Пароль пользователя Администратор неопознан");
    }
}

function CheckUserPassword($user,$passwd)
{
  global $SAMSConf;

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  $result=mysql_query("SELECT * FROM passwd WHERE user=\"$user\"&&pass=\"$passwd\" ");
  $row=mysql_fetch_array($result);
  PageTop("getpassword.jpg","Введен пароль пользователя <FONT COLOR=\"BLUE\">$row[user]</FONT>");
}

function SetUserCookie($user,$passwd)
{
  //$newuser=crypt($user,"00");
  $newpasswd=crypt($passwd,"00");
  setcookie("user","$user");
  setcookie("passwd","$newpasswd");
}




function GetPasswordForm()
{
  PageTop("getpassword.jpg","Эта страница доступна только пользователю с правами Администратора");
  print("<P>\n");
  print("<FORM NAME=\"UPDATEUSER\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"adminpasswd\">\n");
  print("<INPUT TYPE=\"PASSWORD\" NAME=\"userid\" SIZE=30> \n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Ввести\">\n");
  print("</FORM>\n");

}



function SaveUserName($passwd,$number)
{
  global $adminname, $SAMSConf;
  $newpasswd=crypt($passwd,"00");
  print("passwd=$newpasswd=");
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  $result=mysql_query("SELECT * FROM passwd WHERE pass=\"$newpasswd\" ");
  $row=mysql_fetch_array($result);
  if($number==0)
    setcookie("user","$row[user]");
  else
    {
      PageTop("puser_open.gif","Введен пароль пользователя \"$row[user]\" ");
    }
}


function GetAdminPasswordForm()
{
  PageTop("admin_48.jpg","Введите пароль пользователя \"Администратор\"");
  print("<P>\n");
  print("<FORM NAME=\"UPDATEUSER\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"adminpasswd\">\n");
  print("<INPUT TYPE=\"PASSWORD\" NAME=\"userid\" SIZE=30> \n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Ввести\">\n");
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
  print("$username=$row[user] $newpasswd=$row[pass]<BR>");
}

?>
