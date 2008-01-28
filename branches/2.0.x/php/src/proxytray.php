<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */


function JSProxyInfo()
{
  global $SAMSConf;
  global $PROXYConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($PROXYConf->s_realsize=="real")
       $traffic="$adminbuttom_1_prop_SamsReConfigForm_47";
  else
       $traffic="$adminbuttom_1_prop_SamsReConfigForm_48";
  if($PROXYConf->s_auth=="ip")
	$auth="IP";
  if($PROXYConf->s_auth=="ncsa")
	$auth="NCSA";
  if($PROXYConf->s_auth=="ntlm")
	$auth="NTLM";
  if($PROXYConf->s_auth=="adld")
	$auth="ADLD";
  if($PROXYConf->s_parser==0)
	$parser="No";
  if($PROXYConf->s_parser==2)
	$parser="$adminbuttom_1_prop_SamsReConfigForm_33";
  if($PROXYConf->s_parser==1)
	$parser="$adminbuttom_1_prop_SamsReConfigForm_34";

  if($PROXYConf->s_redirector=="none")
            $redirector="$adminbuttom_1_prop_SamsReConfigForm_43";
  if($PROXYConf->s_redirector=="sams")
            $redirector="$adminbuttom_1_prop_SamsReConfigForm_7";
  if($PROXYConf->s_redirector=="rejik")
            $redirector="Rejik";
  if($PROXYConf->s_redirector=="squidguard")
            $redirector="SquidGuard";
  if($PROXYConf->s_redirector=="squid")
            $redirector="$adminbuttom_1_prop_SamsReConfigForm_8";

  $htmlcode="<HTML><BODY><CENTER>
  <TABLE WIDTH=\"95%\" border=0><TR><TD WIDTH=\"10%\"  valign=\"middle\">
  <img src=\"$SAMSConf->ICONSET/user.jpg\" align=\"RIGHT\" valign=\"middle\" >
  <TD  valign=\"middle\"><h2  align=\"CENTER\">Proxy server <BR><FONT COLOR=\"BLUE\">$PROXYConf->s_description</FONT></h2>
  </TABLE>
  <TABLE>
  <TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_2<TD>$auth
  <TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_38<TD>$parser
  <TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_6<TD>$redirector
  <TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_46<TD>$traffic
  </TABLE>";

  $htmlcode=$htmlcode."</CENTER></BODY></HTML>";
  $htmlcode=str_replace("\"","\\\"",$htmlcode);
  $htmlcode=str_replace("\n","",$htmlcode);
  print(" parent.basefrm.document.write(\"$htmlcode\");\n");
  print(" parent.basefrm.document.close();\n");

}



function ProxyTray()
{
  global $SAMSConf;
  global $PROXYConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "AULC")==1)
  {
	print("<SCRIPT>\n");
	JSProxyInfo();
	print("</SCRIPT> \n");

	print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
	print("<TR>\n");
	print("<TD VALIGN=\"TOP\" WIDTH=\"30%\"\">");
	print("<B>Proxy<BR><FONT COLOR=\"BLUE\">$PROXYConf->s_description</FONT></B>\n");

	ExecuteFunctions("./src", "proxybuttom","1");
  
	print("<TD>\n");
	print("</TABLE>\n");
  }

}

?>
