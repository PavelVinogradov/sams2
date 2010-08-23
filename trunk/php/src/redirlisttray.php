<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
function DeleteList()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("LC")!=1 )
	exit;
  
  if(isset($_GET["id"])) $id=$_GET["id"];

  $num_rows=$DB->samsdb_query("DELETE FROM redirect WHERE s_redirect_id='$id' ");

  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("  parent.tray.location.href=\"tray.php\"; \n");
  print("</SCRIPT> \n");

}
function AddNewList()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["type"])) $type=$_GET["type"];
  if(isset($_GET["name"])) $name=$_GET["name"];

  if($USERConf->ToWebInterfaceAccess("LC")!=1 )
	exit;

  $num_rows=$DB->samsdb_query("INSERT INTO redirect (s_name,s_type) VALUES ( '$name', '$type' ) ");

  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("  parent.tray.location.href=\"main.php?show=exe&filename=redirlisttray.php&function=addurllistform&type=$type\";\n");
  print("</SCRIPT> \n");

}

function SaveDestination()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["row_id"])) $row_id=$_GET["row_id"];
  if(isset($_GET["dest"])) $dest=$_GET["dest"];
  if(isset($_GET["type"])) $type=$_GET["type"];

  if($USERConf->ToWebInterfaceAccess("LC")!=1 )
	exit;

  $num_rows=$DB->samsdb_query("UPDATE redirect SET s_dest='$dest' WHERE s_redirect_id='$row_id'");

  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("  parent.tray.location.href=\"main.php?show=exe&filename=redirlisttray.php&function=redirlisttray&id=$row_id\";\n");
  print("</SCRIPT> \n");
}

function AddURLListForm()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["type"])) $type=$_GET["type"];

  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php\";\n");
  print("</SCRIPT> \n");

  if($USERConf->ToWebInterfaceAccess("LC")!=1 )
	exit;
  
 switch($type)
	{
	case 'redir':  PageTop("redirect_48.jpg","$redir_addredirectform1 ");
				break;
	case 'replace': PageTop("redirect_48.jpg","$redir_addreplaceform1 ");
				break;
	case 'denied':  PageTop("redirect_48.jpg","$denied_adddeniedform1 ");
				break;
	case 'allow':  PageTop("redirect_48.jpg","$addallowlistform_AddAllowListForm_1 ");
				break;
	case 'files':  PageTop("redirect_48.jpg","$redir_filetypesform1 ");
				break;
	case 'regex':  PageTop("redirect_48.jpg","$urlregex_addform1 ");
				break;
	case 'local':  PageTop("redirect_48.jpg","$redir_openurlbase2 ");
				break;
	}
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/accesslists.html\">$documentation</A>");
  print("<P>\n");
  print("<BR>\n");
 
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function TestName(formname)\n");
       print("{\n");
       print("  var shablonname=formname.name.value; \n");
       print("  if(shablonname.length==0) \n");
       print("    {\n");
       print("       alert(\"$redirlisttray_AddRedirListForm_5\");\n");
       print("       return false");
       print("    }\n");
       print("  return true");
       print("}\n");
       print("</SCRIPT> \n");

  print("<FORM NAME=\"REDIRECT\" ACTION=\"main.php\" onsubmit=\"return TestName(REDIRECT)\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addnewlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=execute value=\"redirlisttray\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"redirlisttray.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" id=type value=\"$type\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$redirlisttray_AddRedirListForm_2:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"name\" SIZE=50> \n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$redirlisttray_AddRedirListForm_3\">\n");
  print("</FORM>\n");

       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\"><A HREF=\"doc/$SAMSConf->LANGCODE/urllists.html\">");
       print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
       print("<TD>$redirlisttray_AddRedirListForm_4");

}


function AddRedirListForm()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php\";\n");
  print("</SCRIPT> \n");

  if($USERConf->ToWebInterfaceAccess("LC")!=1 )
	exit;
  
  PageTop("redirect_48.jpg","$redirlisttray_AddRedirListForm_1 ");
 
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function TestName(formname)\n");
       print("{\n");
       print("  var shablonname=formname.name.value; \n");
       print("  if(shablonname.length==0) \n");
       print("    {\n");
       print("       alert(\"$redirlisttray_AddRedirListForm_5\");\n");
       print("       return false");
       print("    }\n");
       print("  return true");
       print("}\n");
       print("</SCRIPT> \n");
 
  print("<FORM NAME=\"REDIRECT\" ACTION=\"main.php\" onsubmit=\"return TestName(REDIRECT)\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addnewlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=execute value=\"redirlisttray\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$redirlisttray_AddRedirListForm_2:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" id=type value=\"redir\">\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"name\" SIZE=50> \n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$redirlisttray_AddRedirListForm_3\">\n");
  print("</FORM>\n");

       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\"><A HREF=\"doc/$SAMSConf->LANGCODE/urllists.html\">");
       print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
       print("<TD>$redirlisttray_AddRedirListForm_4");

}

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

function AddURLFromList()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $exefilename="";
  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["addurl"])) $url=$_GET["addurl"];

  $url = UnecranChars($url);

  if(strlen($url)>0)
    {   
	if($USERConf->ToWebInterfaceAccess("LC")!=1 )
	exit;

      $DB->samsdb_query("INSERT INTO url (s_url,s_redirect_id) VALUES ('$url', '$id') ");
    }
  
  print("<SCRIPT>\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&filename=redirlisttray.php&function=redirlistform&id=$id\";\n");
  print("</SCRIPT> \n");
}

function DeleteURLFromList()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("LC")!=1 )
	exit;

  $exefilename="";
 if(isset($_GET["type"])) $type=$_GET["type"];
  if(isset($_GET["delete"])) $url=$_GET["delete"];
  if(isset($_GET["deletedurl"])) $deletedurl=$_GET["deletedurl"];

  $deletedurl = UnecranChars ($deletedurl);

  $num_rows=$DB->samsdb_query("DELETE FROM url WHERE s_url='$deletedurl' and s_redirect_id='$type' ");

  print("<SCRIPT>\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&filename=redirlisttray.php&function=redirlistform&id=$type\";\n");
  print("</SCRIPT> \n");
 
}

function EditURLFromList()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $exefilename="";
  if(isset($_GET["type"])) $type=$_GET["type"];
  if(isset($_GET["editurl"])) $url=$_GET["editurl"];
  if(isset($_GET["oldvalue"])) $oldvalue=$_GET["oldvalue"];

  if($USERConf->ToWebInterfaceAccess("LC")!=1 )
	exit;

   $oldvalue = UnecranChars ($oldvalue);
   $url = UnecranChars ($url);

  $num_rows=$DB->samsdb_query("UPDATE url SET s_url='$url' WHERE s_url='$oldvalue' and s_redirect_id='$type' ");

  print("<SCRIPT>\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&filename=redirlisttray.php&function=redirlistform&id=$type\";\n");
  print("</SCRIPT> \n");
  
}

function DeleteAllURLFromList()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $exefilename="";
  if(isset($_GET["type"])) $type=$_GET["type"];

  if($USERConf->ToWebInterfaceAccess("LC")!=1 )
	exit;

  $num_rows=$DB->samsdb_query("DELETE FROM url WHERE s_redirect_id='$type' ");

  print("<SCRIPT>\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&filename=redirlisttray.php&function=redirlistform&id=$type\";\n");
  print("</SCRIPT> \n");

}

function RedirListForm()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];
  $count=0;

  if($USERConf->ToWebInterfaceAccess("LC")!=1 )
	exit;
  
  $num_rows=$DB->samsdb_query_value("SELECT * FROM redirect WHERE s_redirect_id='$id' ");
  $row=$DB->samsdb_fetch_array();
  $show1=$row['s_type'];
  $nick1=$row['s_name'];

	if($row['s_type']=="redir")
		$type="$redirlisttray_RedirListTray_1";
	if($row['s_type']=="replace")
		$type="$redirlisttray_ReplaceListTray_1";
	if($row['s_type']=="denied")
		$type="$deniedlisttray_DeniedListTray_1";
	if($row['s_type']=="allow")
		$type="$allowlisttray_allowlisttray_1";
	if($row['s_type']=="files")
		$type=" $filedeniedlisttray_filedeniedlisttray_1";
	if($row['s_type']=="local")
		$type=" $redirlisttray_RedirListTray_2";
	if($row['s_type']=="regex")
		$type=" $regexlisttray_regexlisttray_1";
  PageTop("redirect_48.jpg","$type <FONT COLOR=\"BLUE\">$row[s_name]</FONT>");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/accesslists.html#fileext\">$documentation</A>");
  print("<P>\n");

  print("<FORM NAME=\"destination\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"savedestination\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"redirlisttray.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"row_id\" VALUE=\"$id\"> \n");
  if($row['s_type']=="replace")
    {
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"replace\"> \n");
      print("Substitute destination <INPUT TYPE=\"TEXT\" NAME=\"dest\" SIZE=50 VALUE=\"$row[s_dest]\">  </INPUT>");
      print("<INPUT TYPE=\"SUBMIT\" value=\"Save\">\n");
      print("<BR>");
    }
  print("</FORM>");
  $DB->free_samsdb_query();
  print("<BR>\n");

  print("<FORM NAME=\"table\" ACTION=\"main.php\">\n");
  print("<SELECT NAME=\"delete\" ID=\"deleteurl\" SIZE=10 TABINDEX=20 ");
  print("STYLE=\"font-size:10pt\">\n");
  $num_rows=$DB->samsdb_query_value("SELECT s_url FROM url WHERE s_redirect_id='$id' ORDER BY s_url");
  while($row=$DB->samsdb_fetch_array())
     {
       $count++;
       $string=$row['s_url'];
       $string2=EkranChars($string);
       print("<OPTION VALUE=$string onclick=EditURL(\"$string2\")> $string\n");
     }
  print("</SELECT>\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"deleteurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"redirlisttray.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"deletedurl\" VALUE=\"\"> \n");
  print("</BR>\n");
  print("\n");
  print("<INPUT TYPE=\"BUTTON\" value=\"$redirlisttray_RedirListForm_1\" OnClick=DeleteURL(table)>\n");
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

  print("function SaveDestination(formname)\n");
  print("{\n");
  //print(" var s=formname.deleteurl.value;\n");
  //print(" var res=AppendURLString(s);\n");
  //print(" document.forms[\"table\"].elements[\"deletedurl\"].value=res;\n");
  //print(" document.forms[\"table\"].submit();\n");
  print("}\n");
  
  print("</script>\n");
  
  print("<P>\n");
  print("<FORM NAME=\"EDITURL\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"editurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"redirlisttray.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"oldvalue\" VALUE=\"\"> \n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"editurl\" VALUE=\"\"> \n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"editurlstr\" SIZE=30> \n");
  print("<INPUT TYPE=\"BUTTON\" value=\"Change\" OnClick=ChangeURL(EDITURL)>\n");
  print("</FORM>\n");

  
  print("<P><BR>\n");
  print("<FORM NAME=\"ADDURL\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"redirlisttray.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"addurl\" SIZE=30> \n");
  print("<INPUT TYPE=\"BUTTON\" value=\"$redirlisttray_RedirListForm_2\"  OnClick=AddURL(ADDURL)>\n");
  print("</FORM>\n");

  print("<P>\n");
  print("<FORM NAME=\"CLEARLIST\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"deleteallurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"redirlisttray.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"SUBMIT\" value=\"$redirlisttray_RedirListForm_3\">\n");
  print("</FORM>\n");

}




function RedirListTray()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  print("<SCRIPT>\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&filename=redirlisttray.php&function=redirlistform&id=$id\";\n");
  print("</SCRIPT> \n");

  $num_rows=$DB->samsdb_query_value("SELECT * FROM redirect WHERE s_redirect_id='$id' ");
  $row=$DB->samsdb_fetch_array();

  if($USERConf->ToWebInterfaceAccess("LC")==1 )
    {
	print("<TABLE border=0 WIDTH=\"100%\">\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=25%>");
	if($row['s_type']=="redir")
		$type="$redirlisttray_RedirListTray_1";
	if($row['s_type']=="replace")
		$type="$redirlisttray_ReplaceListTray_1";
	if($row['s_type']=="denied")
		$type="$deniedlisttray_DeniedListTray_1";
	if($row['s_type']=="allow")
		$type="$allowlisttray_allowlisttray_1";
	if($row['s_type']=="local")
		$type=" $redirlisttray_RedirListTray_2";
	if($row['s_type']=="files")
		$type=" $filedeniedlisttray_filedeniedlisttray_1";
      print("<B>$type <FONT SIZE=\"+1\" COLOR=\"blue\">$row[s_name]</FONT></B>\n");

      ExecuteFunctions("./src", "redirbuttom","1");

     }
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
