<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function EkranChars($string)
{
  $newstring="";
  for($i=0;$i<strlen($string);$i++)
     {
      $letter=substr ( $string, $i ,1);
      if($letter=="\\")
        $newstring="$newstring$letter";
      $newstring="$newstring$letter";
      
     }
  return($newstring);
} 

function EcranChars ($string)
{
	return (str_replace ("\\", "\\\\", $string));
}

function UnecranChars($string)
{
	// Check is magic_quotes_gpc = On
	if (get_magic_quotes_gpc () ) {
		return ( stripslashes ($string) );
	} else {
		return ($string);
	}
} 

function ContextListForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];
  $count=0;

  $result=mysql_query("SELECT * FROM redirect WHERE filename=\"$id\" ");
  $row=mysql_fetch_array($result);
  $show1=$row['type'];
  $nick1=$row['name'];

  PageTop("denied_48.jpg","$deniedlisttray_DeniedListTray_1 <FONT COLOR=\"BLUE\">$row[name]</FONT>");

  print("<BR>\n");

  print("<FORM NAME=\"table\" ACTION=\"main.php\">");
  print("<SELECT NAME=\"deleteurl\" ID=\"deleteurl\" SIZE=10 TABINDEX=20 ");
  print("STYLE=\"font-size:10pt\">\n");
  $result=mysql_query("SELECT url FROM urls WHERE urls.type=\"$id\" ORDER BY url");
  while($row=mysql_fetch_array($result))
     {
       $count++;
       $string=$row['url'];
       //$string=str_replace("'","",$string);
       //$string=str_replace(",","",$string);
       $string2=EkranChars($string);
       print("<OPTION VALUE=$string onclick=EditURL(\"$string2\")> $string\n");
     }
  print("</SELECT>\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"deleteurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=function value=\"contextlistform\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"deletedurl\" VALUE=\"\"> \n");
  print("</BR>\n");
  print("\n");
  print("<INPUT TYPE=\"BUTTON\" value=\"$contextlisttray_RedirListForm_1\" OnClick=DeleteURL(table)>\n");
  print("</FORM>\n");

  print("<script language=JAVASCRIPT>\n");
  print("function AppendURLString(s)\n");
  print("{\n");
  print(" var res=\"\";\n");
  print(" for (var i=0; i <= s.length; i++)\n");
  print("    {\n");
  print("       var letter =s.substr(i,1);\n");
  print("       if ( letter == \"\\\\\" ) \n");
  print("          { res  = res + letter  }\n");
  print("       res  = res + letter  \n");
  print("    } \n");
  print("  return res\n");
  print("}\n");
  
  print("function AddURL(formname)\n");
  print("{\n");
  print(" var s=formname.addurl.value;\n");
  print(" var res=AppendURLString(s);\n");
  print(" document.forms[\"ADDURL\"].elements[\"addurl\"].value=res;\n");
  print(" document.forms[\"ADDURL\"].submit();\n");
  print("}\n");
  
  print("function DeleteURL(formname)\n");
  print("{\n");
  print(" var s=formname.deleteurl.value;\n");
  print(" var res=AppendURLString(s);\n");
  print(" document.forms[\"table\"].elements[\"deletedurl\"].value=res;\n");
  print(" document.forms[\"table\"].submit();\n");
  print("}\n");
  
  print("function ChangeURL(formname)\n");
  print("{\n");
  print(" var s=formname.editurlstr.value;\n");
  print(" var res=AppendURLString(s);\n");
  print(" document.forms[\"EDITURL\"].elements[\"editurl\"].value=res;\n");
  print(" document.forms[\"EDITURL\"].submit();\n");
  print("}\n");
  
  print("function EditURL(URL)\n");
  print("{\n");
  print(" document.forms[\"EDITURL\"].elements[\"editurlstr\"].value=URL;\n");
  print(" var res=AppendURLString(URL);\n");
  print(" document.forms[\"EDITURL\"].elements[\"oldvalue\"].value=res;\n");
  print("}\n");
  print("</script>\n");
  
  print("<P>\n");
  print("<FORM NAME=\"EDITURL\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"editurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=function value=\"contextlistform\">\n");
//  print("<INPUT TYPE=\"HIDDEN\" NAME=\"exefilename\" id=filename value=\"contextlisttray.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"oldvalue\" VALUE=\"\"> \n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"editurl\" VALUE=\"\"> \n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"editurlstr\" SIZE=30> \n");
  print("<INPUT TYPE=\"BUTTON\" value=\"Change\" OnClick=ChangeURL(EDITURL)>\n");
  print("</FORM>\n");
  
  print("<P>\n");
  print("<FORM NAME=\"ADDURL\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=function value=\"contextlistform\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"addurl\" SIZE=30> \n");
  print("<INPUT TYPE=\"BUTTON\" value=\"$contextlisttray_RedirListForm_2\"  OnClick=AddURL(ADDURL)>\n");
  print("</FORM>\n");

  print("<P>\n");
  print("<FORM NAME=\"CLEARLIST\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"deleteallurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=function value=\"contextlistform\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"SUBMIT\" value=\"$contextlisttray_RedirListForm_3\">\n");
  print("</FORM>\n");

  if($row['type']=="$id")
     {
       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\">");
       print("<A HREF=\"doc/$SAMSConf->LANGCODE/localhost.html\"><IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT></A>");
       print("<TD>$redir_openurlbase10");
       print(" $redir_openurlbase11");
       print(" <BR>$redir_openurlbase12");
       print(" <BR>$redir_openurlbase13");
       print(" <BR>$redir_openurlbase14");
       print(" <BR>$redir_openurlbase15");
     }

}


function AddContextListForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php\";\n");
  print("</SCRIPT> \n");

  PageTop("denied_48.jpg","$urlregex_addform1 ");
  print("<BR>\n");
  print("<FORM NAME=\"REDIRECT\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addnewlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=execute value=\"contextlisttray\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$redir_addredirectform2:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" id=type value=\"regex\">\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"name\" SIZE=50> \n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$redir_addredirectform3\">\n");
  print("</FORM>\n");

       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\"><A HREF=\"doc/$SAMSConf->LANGCODE/urllists.html\">");
       print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
       print("<TD>$urlregex_addform4");

}



function ContextListTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  print("<SCRIPT>\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&function=contextlistform&id=$id\";\n");
  print("</SCRIPT> \n");

  $result=mysql_query("SELECT * FROM redirect WHERE filename=\"$id\" ");
  $row=mysql_fetch_array($result);
  if($SAMSConf->access==2)
    {
      print("<TABLE border=0 WIDTH=\"100%\">\n");
      print("<TR>\n");
      print("<TD VALIGN=\"TOP\" WIDTH=\"30%\">");
      print("<B>$deniedlisttray_DeniedListTray_1 <FONT SIZE=\"+1\" COLOR=\"blue\">$row[name]</FONT></B>\n");

      ExecuteFunctions("./src", "contextbuttom","1");

     }
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
