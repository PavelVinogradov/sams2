<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddAdmin()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["username"])) $newuser=$_GET["username"];
  if(isset($_GET["passw1"])) $newpasswd=$_GET["passw1"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  $passwd=crypt($newpasswd,"00");
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  $result=mysql_query("INSERT INTO ".$SAMSConf->SAMSDB.".passwd VALUES('$newuser','$passwd','1') ");
  UpdateLog("$SAMSConf->adminname","$adminbuttom_3_addadm_AddAdmin_1 $newuser","04");
  PageTop("puser_open.gif","$adminbuttom_3_addadm_AddAdmin_2 $newuser");

}

function AddAdminForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  if(strstr("Admin",$SAMSConf->adminname))
    {
      PageTop("user_48.jpg","$adminbuttom_3_addadm_AddAdminForm_1");
	  print("<P>\n");

       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function TestUserData(formname)\n");
       print("{\n");
       print("  var username=formname.username.value; \n");
       print("  var passw1=formname.passw1.value; \n");
       print("  var passw2=formname.passw2.value; \n");
	   print("  var res=0;\n");
	   print("  if(username.length==0) \n");
       print("    {\n");
	   print("       window.confirm(\"$adminbuttom_3_addadm_AddAdminForm_4\");\n");
	   print("       res=1;\n");
	   print("    }\n");
	   print("  if(passw1.length!=passw2.length) \n");
       print("    {\n");
	   print("       window.confirm(\"$adminbuttom_3_addadm_AddAdminForm_5\");\n");
	   print("       res=1;\n");
       print("    }\n");
       print("  else\n");
       print("    {\n");
	   print("       for(var i=0; i < passw1.length; i +=1 ) \n");
       print("          {\n");
	   print("             if(passw1.charAt(i)!=passw2.charAt(i)) \n");
       print("               {\n");
       print("                 window.confirm(\"$adminbuttom_3_addadm_AddAdminForm_6\");\n");
	   print("                 res=1;\n");
	   print("                 break;\n");
       print("               }\n");
       print("          }\n");
       print("    }\n");
	   print("  if(res==0) \n");
       print("    this.document.forms[\"form1\"].submit();\n");
       print("}\n");
       print("</SCRIPT> \n");

	  print("<FORM NAME=\"form1\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addadmin\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"adminbuttom_3_addadm.php\">\n");
      print("<TABLE WIDTH=\"90%\">");
      print("<TR><TD><B>login:</B><TD>");
      print("<INPUT TYPE=\"TEXT\" NAME=\"username\" SIZE=30> \n");
      print("<TR><TD><B>Password:</B><TD>");
      print("<BR><INPUT TYPE=\"PASSWORD\" NAME=\"passw1\" SIZE=30> \n");
      print("<TR><TD><B>Retype:</B><TD>");
      print("<BR><INPUT TYPE=\"PASSWORD\" NAME=\"passw2\" SIZE=30> \n");
      print("<BR><INPUT TYPE=\"BUTTON\" value=\"$adminbuttom_3_addadm_AddAdminForm_2\" onclick=TestUserData(form1)>\n");
      print("</FORM>\n");
    }
  else
    {
       PageTop("warning.jpg","$adminbuttom_3_addadm_AddAdminForm_3");
    }

}


function adminbuttom_3_addadm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=addadminform&filename=adminbuttom_3_addadm.php",
	               "basefrm","user_32.jpg","user_48.jpg","$adminbuttom_3_addadm_adminbuttom_3_addadm_1");
    }

}

?>
