<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 





function AuthNTLMTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php\";\n");
      print("</SCRIPT> \n");

  if($SAMSConf->access==2)
    {
	print("<TABLE border=0 WIDTH=95%>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=25%>");
	print("<B><FONT SIZE=\"+1\">ADLD</FONT></B>\n");

	ExecuteFunctions("./src", "authadldbuttom","1");

     }
  print("<TD>\n");
  print("</TABLE>\n");



}

?>
