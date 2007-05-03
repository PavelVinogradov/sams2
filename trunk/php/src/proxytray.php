<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */




function ProxyTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  print("<SCRIPT>\n");
  print(" parent.basefrm.location.href=\"main.php?show=exe&function=about\";\n");    
  print("</SCRIPT> \n");

  print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
  print("<TR>\n");
  print("<TD VALIGN=\"TOP\" WIDTH=\"30%\"\">");
  print("<B>Proxy</B>\n");

  ExecuteFunctions("./src", "proxybuttom","1");
  
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
