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
	$auth="Active Directory";
  if($PROXYConf->s_auth=="ldap")
	$auth="LDAP";
  if($PROXYConf->s_parser==0)
	$parser="No";
  if($PROXYConf->s_parser==2)
	$parser="$adminbuttom_1_prop_SamsReConfigForm_33";
  if($PROXYConf->s_parser==1)
	$parser="$adminbuttom_1_prop_SamsReConfigForm_34";

  if($PROXYConf->s_redirector=="sams")
            $redirector="$adminbuttom_1_prop_SamsReConfigForm_7";
  else if($PROXYConf->s_redirector=="rejik")
            $redirector="Rejik";
  else if($PROXYConf->s_redirector=="squidguard")
            $redirector="SquidGuard";
  else if($PROXYConf->s_redirector=="squid")
            $redirector="$adminbuttom_1_prop_SamsReConfigForm_8";
  else
            $redirector="$adminbuttom_1_prop_SamsReConfigForm_43";

  $extrainfo="";
  $DB=new SAMSDB();
  $num_rows=$DB->samsdb_query("SELECT * FROM sysinfo WHERE s_status='1' AND s_proxy_id='$PROXYConf->s_proxy_id'");

  if ($num_rows>0)
    {
      $extrainfo=$extrainfo."<HR>";
      $extrainfo=$extrainfo."<TABLE CLASS=samstable>";
    }

  while($row=$DB->samsdb_fetch_array())
    {
      $extrainfo=$extrainfo."<TR><TD><FONT COLOR=blue>$row[s_name] updated at $row[s_date]</FONT></TD></TR>";
      $extrainfo=$extrainfo."<TR><TD>$row[s_info]</TD></TR>";
    }
  if ($num_rows>0)
    {
      $extrainfo=$extrainfo."</TABLE>";
    }

  $DB->free_samsdb_query();

  $htmlcode="<HTML><HEAD>
  <link rel=\"STYLESHEET\" type=\"text/css\" href=\"$SAMSConf->ICONSET/tree.css\">
  </HEAD>
  <BODY><CENTER>
  <TABLE WIDTH=\"95%\" border=0><TR><TD WIDTH=\"10%\"  valign=\"middle\">
  <img src=\"$SAMSConf->ICONSET/user.jpg\" align=\"RIGHT\" valign=\"middle\" >
  <TD  valign=\"middle\"><h2  align=\"CENTER\">Proxy server <BR><FONT COLOR=\"BLUE\">$PROXYConf->s_description</FONT></h2>
  </TABLE>

  <TABLE>
  <TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_53<TD>$PROXYConf->s_proxy_id 
  <TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_2<TD>$auth 
  <TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_38<TD>$parser
  <TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_6<TD>$redirector
  <TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_46<TD>$traffic
  <TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_54<TD>$PROXYConf->s_endvalue 
  </TABLE>";

  $htmlcode=$htmlcode.$extrainfo;

  $htmlcode=$htmlcode."
  <IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">
  <A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/squid.html\">$documentation</A>
  <P>
  </CENTER></BODY></HTML>";
  $htmlcode=str_replace("\"","\\\"",$htmlcode);
  $htmlcode=str_replace("\n","",$htmlcode);
  print(" parent.basefrm.document.write(\"$htmlcode\");\n");
  print(" parent.basefrm.document.close();\n");

}



function ProxyTray()
{
  global $SAMSConf;
  global $PROXYConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1 )
  {
	print("<SCRIPT>\n");
	JSProxyInfo();
	print("</SCRIPT> \n");

	print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=\"30%\">");
	print("<B>Proxy<BR><FONT COLOR=\"BLUE\">$PROXYConf->s_description</FONT></B>\n");

	ExecuteFunctions("./src", "proxybuttom","1");
  
	print("<TD>\n");
	print("</TABLE>\n");
  }

}

?>
