<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ChUserPasswd()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $userid=$_GET["id"];
  if(isset($_GET["passw1"])) $newpasswd=$_GET["passw1"];

  $result=mysql_query("SELECT nick FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);
  
  if($SAMSConf->domainusername!=$row['nick']&&strlen($SAMSConf->adminname)==0)
    exit(0);
  
  PageTop("userpasswd_48.jpg"," $userbuttom_6_chpasswd_ChUserPasswd_1 <BR><FONT COLOR=\"BLUE\">$row[nick]</FONT>");
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("UPDATE ".$SAMSConf->SAMSDB.".squidusers SET passwd=\"$newpasswd\" WHERE id=\"$userid\" ");
  if($SAMSConf->AUTH=="ncsa")
         $result=mysql_query("INSERT INTO reconfig SET service=\"squid\",action=\"reconfig\",number=\"0\" ");

}

function ChUserPasswdForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["userid"])) $userid=$_GET["userid"];
   
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);

  PageTop("userpasswd_48.jpg","$userbuttom_6_chpasswd_ChUserPasswdForm_1 <FONT COLOR=\"BLUE\">$row[nick]</FONT>");
  
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
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$userid\">\n");
      print("<TABLE WIDTH=\"90%\">");
      print("<TR><TD><B>login:</B><TD>");
      print("$row[nick]");
      print("<TR><TD><B>Password:</B><TD>");
      print("<BR><INPUT TYPE=\"PASSWORD\" NAME=\"passw1\" SIZE=30> \n");
      print("<TR><TD><B>Retype:</B><TD>");
      print("<BR><INPUT TYPE=\"PASSWORD\" NAME=\"passw2\" SIZE=30> \n");
      print("<BR><INPUT TYPE=\"BUTTON\" value=\"$adminbuttom_4_chpasswd_ChangeAdminPasswdForm_2\" onclick=TestUserData(form1)>\n");
      print("</FORM>\n");

}


function userbuttom_6_chpasswd($userid)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);

   if($SAMSConf->USERACCESS=="Y"&&$SAMSConf->domainusername=="$row[nick]"&&($SAMSConf->AUTH=="ncsa"||$SAMSConf->AUTH=="ip"))
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=chuserpasswdform&filename=userbuttom_6_chpasswd.php&userid=$userid","basefrm","userpasswd_32.jpg","userpasswd_48.jpg"," $userbuttom_6_chpasswd_userbuttom_6_chpasswd_1");
	}
}

?>
