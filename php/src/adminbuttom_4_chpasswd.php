<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ChangeAdminPasswd()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["adminname"])) $adminname=$_GET["adminname"];
  if(isset($_GET["passw1"])) $newpasswd=$_GET["passw1"];
  if(isset($_GET["oldpasswd"])) $oldpasswd=$_GET["oldpasswd"];

    $SAMSConf->access=UserAccess();
   if($SAMSConf->access<1)     {       exit;     }
  
 $passwd=crypt($newpasswd,"00");
  if(strstr($username,$SAMSConf->adminname)||strstr("Admin",$SAMSConf->adminname))
    {
       db_connect($SAMSConf->SAMSDB) or exit();
       mysql_select_db($SAMSConf->SAMSDB);
       $oldpasswd2=crypt($oldpasswd,"00");

	if($oldpasswd2!=mysql_result(mysql_query("SELECT pass FROM passwd WHERE user='$username' "),0))
		{
		       PageTop("warning.jpg","$username password wrong");
		}
	else
		{
			$result=mysql_query("UPDATE ".$SAMSConf->SAMSDB.".passwd SET pass=\"$passwd\" WHERE user=\"$username\" ");
			if($result>0)
				PageTop("user_48.jpg","$adminbuttom_4_chpasswd_ChangeAdminPasswd_1 $username $adminbuttom_4_chpasswd_ChangeAdminPasswd_2");
		}

    }
  else
    {
       PageTop("warning.jpg","$adminbuttom_4_chpasswd_ChangeAdminPasswd_3");
    }
}

function ChangeAdminPasswdForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access<1)     {       exit;     }
  
  PageTop("userpasswd_48.jpg","$adminbuttom_4_chpasswd_ChangeAdminPasswdForm_1");
  print("<P>\n");
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function TestUserData(formname)\n");
       print("{\n");
       print("  var adminname=formname.adminname.value; \n");
       print("  var passw1=formname.passw1.value; \n");
       print("  var passw2=formname.passw2.value; \n");
	   print("  var res=0;\n");
	   print("  if(adminname.length==0) \n");
       print("    {\n");
	   print("       window.confirm(\"$adminbuttom_4_chpasswd_ChangeAdminPasswdForm_3\");\n");
	   print("       res=1;\n");
	   print("    }\n");
	   print("  if(passw1.length!=passw2.length) \n");
       print("    {\n");
	   print("       window.confirm(\"$adminbuttom_4_chpasswd_ChangeAdminPasswdForm_4\");\n");
	   print("       res=1;\n");
       print("    }\n");
       print("  else\n");
       print("    {\n");
	   print("       for(var i=0; i < passw1.length; i +=1 ) \n");
       print("          {\n");
	   print("             if(passw1.charAt(i)!=passw2.charAt(i)) \n");
       print("               {\n");
       print("                 window.confirm(\"$adminbuttom_4_chpasswd_ChangeAdminPasswdForm_5\");\n");
	   print("                 res=1;\n");
	   print("                 break;\n");
       print("               }\n");
       print("          }\n");
       print("    }\n");
       print("  if(res==0) \n");
       print("    {\n");
       print("      formname.username.value=formname.adminname.value; \n");
       print("      this.document.forms[\"form1\"].submit();\n");
       print("    }\n");
       print("}\n");
       print("</SCRIPT> \n");

      print("<FORM NAME=\"form1\" ACTION=\"main.php\" onsubmit=TestUserData(form1)>\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"changeadminpasswd\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"adminbuttom_4_chpasswd.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"username\" value=\"$SAMSConf->adminname\">\n");
      print("<TABLE WIDTH=\"90%\">");
      print("<TR><TD><B>login:</B><TD>");
      if($SAMSConf->adminname!="admin"&&$SAMSConf->adminname!="Admin")
        {
          print("<SELECT NAME=\"adminname\" >\n");
               print("<OPTION VALUE=$SAMSConf->adminname SELECTED> $SAMSConf->adminname");
          print("</SELECT>\n");
	}  
      else
        {
          print("<SELECT NAME=\"adminname\" >\n");
          $result2=mysql_query("SELECT * FROM passwd ");
          while($row=mysql_fetch_array($result2))
             {
               print("<OPTION VALUE=$row[user] SELECTED> $row[user]");
             }
          print("</SELECT>\n");
        }
      print("<TR><TD><B>$adminbuttom_4_chpasswd_ChangeAdminPasswdForm_6:</B><TD>");
      print("<BR><INPUT TYPE=\"PASSWORD\" NAME=\"oldpasswd\" SIZE=30> \n");
      print("<TR><TD><B>$adminbuttom_4_chpasswd_ChangeAdminPasswdForm_7:</B><TD>");
      print("<BR><INPUT TYPE=\"PASSWORD\" NAME=\"passw1\" SIZE=30> \n");
      print("<TR><TD><B>$adminbuttom_4_chpasswd_ChangeAdminPasswdForm_8:</B><TD>");
      print("<BR><INPUT TYPE=\"PASSWORD\" NAME=\"passw2\" SIZE=30> \n");
      print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_4_chpasswd_ChangeAdminPasswdForm_2\">\n");
      print("</FORM>\n");

}


function adminbuttom_4_chpasswd()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access>0)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=changeadminpasswdform&filename=adminbuttom_4_chpasswd.php",
	               "basefrm","userpasswd_32.jpg","userpasswd_48.jpg","$adminbuttom_4_chpasswd_adminbuttom_4_chpasswd_1");
    }

}







?>
