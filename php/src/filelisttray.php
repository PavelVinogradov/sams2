<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function FileListForm()
{
  global $SAMSConf;
  $filename="";  

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];
  $count=0;

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  $result=mysql_query("SELECT * FROM redirect WHERE filename=\"$id\" ");
  $row=mysql_fetch_array($result);
  $show1=$row['type'];
  $nick1=$row['name'];

  PageTop("redirect_48.jpg","$redirlisttray_RedirListTray_1 <FONT COLOR=\"BLUE\">$row[name]</FONT>");

  print("<BR>\n");

  print("<FORM NAME=\"table\" ACTION=\"main.php\">\n");
  print("<SELECT NAME=\"delete\" ID=\"deleteurl\" SIZE=10 TABINDEX=20 ");
  print("STYLE=\"font-size:10pt\">\n");
  $result=mysql_query("SELECT url FROM urls WHERE urls.type=\"$id\" ORDER BY url");
  while($row=mysql_fetch_array($result))
     {
       $count++;
       $string=$row['url'];
       $string2=EkranChars($string);
       print("<OPTION VALUE=$string onclick=EditURL(\"$string2\")> $string\n");
     }
  print("</SELECT>\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"deleteurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=function value=\"redirlistform\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"deletedurl\" VALUE=\"\"> \n");
  print("</BR>\n");
  print("\n");
  print("<INPUT TYPE=\"BUTTON\" value=\"$redir_filetypesform2\" OnClick=DeleteURL(table)>\n");
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
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=function value=\"redirlistform\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"oldvalue\" VALUE=\"\"> \n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"editurl\" VALUE=\"\"> \n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"editurlstr\" SIZE=30> \n");
  print("<INPUT TYPE=\"BUTTON\" value=\"$redir_filetypesform3\" OnClick=ChangeURL(EDITURL)>\n");
  print("</FORM>\n");

  
  print("<P><BR>\n");
  print("<FORM NAME=\"ADDURL\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=function value=\"redirlistform\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"addurl\" SIZE=30> \n");
  print("<INPUT TYPE=\"BUTTON\" value=\"$redir_filetypesform4\"  OnClick=AddURL(ADDURL)>\n");
  print("</FORM>\n");

  print("<P>\n");
  print("<FORM NAME=\"CLEARLIST\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"deleteallurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=function value=\"redirlistform\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"SUBMIT\" value=\"$redirlisttray_RedirListForm_3\">\n");
  print("</FORM>\n");

  
  $result=mysql_query("SELECT * FROM redirect WHERE filename=\"$filename\" ");
  $row=mysql_fetch_array($result);
  if($row['type']=="$id")
     {
       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\">");
       print("<A HREF=\"doc/$SAMSConf->LANGCODE/localhost.html\"><IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT></A>");
       print("<TD>$redirlisttray_RedirListForm_4");
       print(" $redirlisttray_RedirListForm_5");
       print(" <BR>$redirlisttray_RedirListForm_6");
       print(" <BR>$redirlisttray_RedirListForm_7");
       print(" <BR>$redirlisttray_RedirListForm_8");
       print(" <BR>$redirlisttray_RedirListForm_9");
       printf("</TABLE>");
     }

}


function AddFileListForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php\";\n");
  print("</SCRIPT> \n");

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("redirect_48.jpg","$redir_filetypesform1");
  print("<BR>\n");
  print("<FORM NAME=\"REDIRECT\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addnewlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=execute value=\"filelisttray\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$redirlisttray_AddRedirListForm_2:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" id=type value=\"files\">\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"name\" SIZE=50> \n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$redirlisttray_AddRedirListForm_3\">\n");
  print("</FORM>\n");

       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\"><A HREF=\"doc/$SAMSConf->LANGCODE/urllists.html\">");
       print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
       print("<TD>$redirlisttray_AddRedirListForm_4");

}



function FileListTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  print("<SCRIPT>\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&function=filelistform&id=$id\";\n");
  print("</SCRIPT> \n");

  $result=mysql_query("SELECT * FROM redirect WHERE filename=\"$id\" ");
  $row=mysql_fetch_array($result);
  if($SAMSConf->access==2)
    {
      print("<TABLE border=0 WIDTH=\"100%\">\n");
      print("<TR>\n");
      print("<TD VALIGN=\"TOP\" WIDTH=\"30%\">");
      print("<B>$redirlisttray_RedirListTray_1.  <FONT SIZE=\"+1\" COLOR=\"blue\">$row[name]</FONT></B>\n");

      ExecuteFunctions("./src", "filebuttom","1");

     }
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
