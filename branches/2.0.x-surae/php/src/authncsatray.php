<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AuthNCSAValues()
{

}


function AuthNCSATray()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=authncsavalues&filename=authncsatray.php\";\n");
      print("</SCRIPT> \n");

  if($USERConf->ToWebInterfaceAccess("UC")==1 )
    {
	print("<TABLE border=0 WIDTH=95%>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=25%>");
	print("<B>$authtype_AuthTray<BR><FONT SIZE=\"+1\" COLOR=\"BLUE\">NCSA</FONT></B>\n");

	ExecuteFunctions("./src", "authncsabuttom","1");

     }
  print("<TD>\n");
  print("</TABLE>\n");



}

?>
