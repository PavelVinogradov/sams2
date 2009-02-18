<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

/*
function UserDoc()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);


  print("<H2>$admintray_UserDoc_2 </H2>\n");
  
  if($SAMSConf->SHOWUTREE=="Y")
    {
      PageTop("user.jpg","$admintray_UserDoc_1");
      print("</CENTER>");
      print("<IMG SRC=\" $SAMSConf->ICONSET/lframe.jpg \" ALIGN=LEFT>\n");
      print("$admintray_UserDoc_3");
      print("$admintray_UserDoc_4");
    }
  else
    {
      print("<P><B>$AdminTray_UserDoc_5</B>\n");
      print("<P>");
      print("<FORM NAME=\"NUSERPASSWORD\" ACTION=\"main.php\" METHOD=\"POST\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"nuserauth\">\n");
      print("<TABLE WIDTH=\"90%\">\n");
      print("<TR>\n");
      print("<TD><B>login:</B>\n");
      print("<TD><INPUT TYPE=\"TEXT\" NAME=\"user\" SIZE=30> \n");
      print("<TR>\n");
      print("<TD><B>password:</B>\n");
      print("<TD><INPUT TYPE=\"PASSWORD\" NAME=\"userid\" SIZE=30> \n");
      print("</TABLE>\n");
      print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Ok\">\n");
      print("</FORM>\n");

    }  
}
*/

function AdminTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  print("<SCRIPT>\n");
 if($SAMSConf->access!=2)
    {       print("parent.basefrm.location.href=\"main.php?show=exe&filename=admintray.php&function=userdoc\";\n");    }
  print("</SCRIPT> \n");

  print("<TABLE WIDTH=\"90%\" BORDER=0 ALIGN=CENTER>\n");
  print("<TR>\n");
  print("<TD WIDTH=34 HEIGH=34><IMAGE src=\"$SAMSConf->ICONSET/config_32.jpg\" BORDER=0 ALT=\"SAMS management interface\"\n ");
  print("<TD><A HREF=\"main.php?show=exe&function=changeuser&filename=adminbuttom_2_chuser.php\" TARGET=basefrm> SAMS management interface</A>");

}

?>
