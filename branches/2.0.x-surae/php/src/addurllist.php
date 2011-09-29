<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */



function AddURLListForm()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php\";\n");
  print("</SCRIPT> \n");

  if($USERConf->ToWebInterfaceAccess("C")!=1)
	exit;
  
  PageTop("redirect_48.jpg","$redirlisttray_AddRedirListForm_1 ");
  print("<BR>\n");
 
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function TestName(formname)\n");
       print("{\n");
       print("  var shablonname=formname.name.value; \n");
       print("  if(shablonname.length==0) \n");
       print("    {\n");
       print("       alert(\"$redirlisttray_AddRedirListForm_5\");\n");
       print("       return false");
       print("    }\n");
       print("  return true");
       print("}\n");
       print("</SCRIPT> \n");
 
  print("<FORM NAME=\"REDIRECT\" ACTION=\"main.php\" onsubmit=\"return TestName(REDIRECT)\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addnewlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=execute value=\"redirlisttray\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$redirlisttray_AddRedirListForm_2:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" id=type value=\"redir\">\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"name\" SIZE=50> \n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$redirlisttray_AddRedirListForm_3\">\n");
  print("</FORM>\n");

       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\"><A HREF=\"doc/$SAMSConf->LANGCODE/urllists.html\">");
       print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
       print("<TD>$redirlisttray_AddRedirListForm_4");

}



?>
