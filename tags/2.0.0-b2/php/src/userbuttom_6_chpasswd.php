<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ChUserPasswd()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;

  if(isset($_GET["id"])) $id=$_GET["id"];
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);

	if($USERConf->ToWebInterfaceAccess("AUC")==1 || ($USERConf->s_user_id == $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")==1 ) )
	{
		$DB=new SAMSDB();
  
		$lang="./lang/lang.$SAMSConf->LANG";
		require($lang);

		if(isset($_GET["passw1"])) $newpasswd=$_GET["passw1"];

		PageTop("userpasswd_48.jpg"," $userbuttom_6_chpasswd_ChUserPasswd_1 <BR><FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT>");
		$passwd=crypt($newpasswd, substr($newpasswd, 0, 2));
		$num_rows=$DB->samsdb_query("UPDATE squiduser SET s_passwd='$passwd' WHERE s_user_id='$SquidUSERConf->s_user_id'");
	}
}

function ChUserPasswdForm()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);

  if($USERConf->ToWebInterfaceAccess("AUC")==1 || ($USERConf->s_user_id == $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")==1 ) )
  {

   
	PageTop("userpasswd_48.jpg","$userbuttom_6_chpasswd_ChUserPasswdForm_1 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT>");

	print("<P>\n");
	print("<SCRIPT language=JAVASCRIPT>\n");
	print("function TestUserData(formname)\n");
	print("{\n");
	print("  var passw1=formname.passw1.value; \n");
	print("  var passw2=formname.passw2.value; \n");
	print("  var res=0;\n");
	print("  if(passw1.length!=passw2.length) \n");
	print("    {\n");
	print("       window.confirm(\"$adminbuttom_4_chpasswd_ChangeAdminPasswdForm_4\");\n");
	print("       res=1;\n");
	print("    }\n");
	print("  else\n");
	print("    {\n");
	print("       for(var i=0; i < passw1.length; i +=1 ) \n");
	print("          {\n");
	print("             if(passw1.charAt(i)!=passw2.charAt(i)) \n");
	print("               {\n");
	print("                 window.confirm(\"$adminbuttom_4_chpasswd_ChangeAdminPasswdForm_5\");\n");
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
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"chuserpasswd\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"userbuttom_6_chpasswd.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$SquidUSERConf->s_user_id\">\n");
	print("<TABLE WIDTH=\"90%\">");
	print("<TR><TD><B>login:</B><TD>");
	print("$SquidUSERConf->s_nick");
	print("<TR><TD><B>Password:</B><TD>");
	print("<BR><INPUT TYPE=\"PASSWORD\" NAME=\"passw1\" SIZE=30> \n");
	print("<TR><TD><B>Retype:</B><TD>");
	print("<BR><INPUT TYPE=\"PASSWORD\" NAME=\"passw2\" SIZE=30> \n");
	print("<BR><INPUT TYPE=\"BUTTON\" value=\"$adminbuttom_4_chpasswd_ChangeAdminPasswdForm_2\" onclick=TestUserData(form1)>\n");
	print("</FORM>\n");
  }
}


function userbuttom_6_chpasswd()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("AUC")==1 || ($USERConf->s_user_id == $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")==1 ))
	{
		if(strtolower($SquidUSERConf->s_auth)=='adld' || strtolower($SquidUSERConf->s_auth)=='ldap') 
			return(0);
		GraphButton("main.php?show=exe&function=chuserpasswdform&filename=userbuttom_6_chpasswd.php&id=$SquidUSERConf->s_user_id","basefrm","userpasswd_32.jpg","userpasswd_48.jpg"," $userbuttom_6_chpasswd_userbuttom_6_chpasswd_1");
	}
}

?>
