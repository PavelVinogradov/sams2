<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ChangeUser()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("getpassword.jpg","$adminbuttom_2_chuser_ChangeUser_1 ");
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function TestUserData(formname)\n");
       print("{\n");
       print("  var username=formname.username.value; \n");
       print("  var res=0;\n");
       print("  var host=window.location.hostname;\n");
       print("  var path=window.location.pathname;\n");
       print("  var hrefstr=\"https://\"+host+path;\n");
//       print("  value=window.confirm(hrefstr);\n");
//       print("  formname.form.action=hrefstr; \n");
       print("  if(username.length==0) \n");
       print("    {\n");
       print("       window.confirm(\"$adminbuttom_2_chuser_ChangeUser_3\");\n");
       print("       res=1;\n");
       print("    }\n");
       print("  if(res==0) \n");
       print("    this.document.forms[\"form1\"].submit();\n");
       print("}\n");
       print("</SCRIPT> \n");

  print("<B>$adminbuttom_2_chuser_ChangeUser_2</B>");
  print("<P>\n");
//  print("<FORM NAME=\"form1\" ACTION=\"main.php\"  onsubmit=TestUserData(form1)>\n");
  print("<FORM NAME=\"form1\" ACTION=\"main.php\"  onsubmit=TestUserData(form1) method=\"POST\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"setcookie\">\n");
  print("<TABLE WIDTH=\"90%\">\n");
  print("<TR>\n");
  print("<TD><B>login:</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"username\" SIZE=30>\n");
  print("<TR>\n");
  print("<TD><B>password:</B>\n");
  print("<TD><INPUT TYPE=\"PASSWORD\" NAME=\"userid\" SIZE=30 > \n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Ok\">\n");
  print("</FORM>\n");
}



function adminbuttom_2_chuser()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
//  if($SAMSConf->access==2)
//    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=changeuser&filename=adminbuttom_2_chuser.php",
	               "basefrm","usergroup_32.jpg","usergroup_48.jpg","$adminbuttom_2_chuser_adminbuttom_2_chuser_1");
 //   }

}







?>
