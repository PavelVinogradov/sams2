<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AdminTray()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	print("<SCRIPT>\n");

	if($USERConf->ToWebInterfaceAccess("C")!=1 )
	{
		print("parent.basefrm.location.href= 	\"main.php?show=exe&filename=admintray.php&function=userdoc\";\n");    
	}
	print("</SCRIPT> \n");

	print("<TABLE WIDTH=\"90%\" BORDER=0 ALIGN=CENTER>\n");
	print("<TR>\n");

	if($USERConf->ToWebInterfaceAccess("C")!=1 )
	{
		print("<TD WIDTH=34 HEIGH=34><IMAGE src=\"$SAMSConf->ICONSET/config_32.jpg\" BORDER=0 ALT=\"SAMS management interface\"\n ");
		print("<TD><A HREF=\"main.php?show=exe&function=changeuser&filename=adminbuttom_2_chuser.php\" TARGET=basefrm> SAMS management interface</A>");
	}
	else
	{
		print("<TD VALIGN=\"TOP\" WIDTH=\"30%\"\">");
		print("<B>User<BR><FONT SIZE=\"+1\" COLOR=\"blue\">$SAMSConf->adminname</FONT></B>\n");

 		ExecuteFunctions("./src", "adminbuttom","1");
		print("<TD>\n");

	}
	print("</TABLE>\n");
}

?>
