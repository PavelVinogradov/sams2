<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UpdateTRange()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["name"])) $name=$_GET["name"];
  $timestart="";
  if(isset($_GET["shour"])) $timestart=$_GET["shour"];
  if(isset($_GET["smin"])) $timestart=$timestart.":".$_GET["smin"].":00";
  $timeend="";
  if(isset($_GET["ehour"])) $timeend=$_GET["ehour"];
  if(isset($_GET["emin"])) $timeend=$timeend.":".$_GET["emin"].":00";
  $days="";
  if(isset($_GET["day1"])) $days="M";
  if(isset($_GET["day2"])) $days=$days."T";
  if(isset($_GET["day3"])) $days=$days."W";
  if(isset($_GET["day4"])) $days=$days."H";
  if(isset($_GET["day5"])) $days=$days."F";
  if(isset($_GET["day6"])) $days=$days."A";
  if(isset($_GET["day7"])) $days=$days."S";

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	{       exit;     }

  $DB->samsdb_query("UPDATE timerange  SET  s_name='$name', s_days='$days', s_timestart='$timestart' , s_timeend='$timeend' WHERE s_trange_id='$id' ");
//  UpdateLog("$SAMSConf->adminname","$shablonnew_AddShablon_1 $snick","01");

  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("  parent.tray.location.href=\"tray.php?show=exe&filename=trangetray.php&function=trangetray&id=$id\"; \n");
  print("</SCRIPT> \n");
}




function UpdateTRangeForm()
{
  global $SAMSConf;
  global $TRANGEConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["type"])) $type=$_GET["type"];

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	{       exit;     }

  PageTop("clock_48.jpg","$trangebuttom_1_prop_trangebuttom_1_prop_1<BR><FONT COLOR=\"BLUE\">$TRANGEConf->s_name</FONT>");
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

//      print("   context = insFld(sams, gFld(\"$lframe_sams_FolderContextDenied_1\", \"main.php?show=exe&filename=redirlisttray.php&function=addurllistform&type=regex\", \"stop.gif\"))\n");
 
  print("<FORM NAME=\"REDIRECT\" ACTION=\"main.php\" onsubmit=\"return TestName(REDIRECT)\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"updatetrange\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"trangebuttom_1_prop.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=id value=\"$TRANGEConf->s_trange_id\">\n");

/* calendar */  
  print("<TABLE  BORDER=0>\n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>Name:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"name\" VALUE=\"$TRANGEConf->s_name\" SIZE=50> \n");
  print("</TABLE>\n");

  print("<P><B>$shablonbuttom_1_prop_UpdateShablonForm_14 \n");
  print("<TABLE  BORDER=0>\n");
  print("<TR>\n");
  for($i=1;$i<8;$i++)
     {
       print("<TD><B><BR>$week[$i]\n");
     }  
  print("<TR>\n");
  for($i=1;$i<8;$i++)
     {
	$checked=$TRANGEConf->trangeday("$i");
       print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"day$i\" $checked> \n" );
     }  
  print("</TABLE>\n");
  print("<P>\n");
  
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_13</B>\n");
  print("<P>\n");
  print("     <SELECT NAME=\"shour\"> \n");
  for($i=0;$i<24;$i++)
     {
	if($TRANGEConf->s_shour==$i)
		print("	       <OPTION value=$i SELECTED>$i\n");
	else
		print("	       <OPTION value=$i>$i\n");
     }
  print("	       </SELECT>:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"smin\" SIZE=2 VALUE=\"$TRANGEConf->s_smin\"> - \n" );
  print("     <SELECT NAME=\"ehour\"> \n");
  for($i=23;$i>=0;$i--)
     {
	if($TRANGEConf->s_ehour==$i)
		print("	       <OPTION value=$i SELECTED>$i\n");
	else
		print("	       <OPTION value=$i>$i\n");
//           print("	       <OPTION value=$i>$i\n");
     }
  print("	       </SELECT>:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"emin\" SIZE=2 VALUE=\"$TRANGEConf->s_emin\"> \n" );
  print("</TABLE>\n");
/* calendar */  

  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Change\">\n");
  print("</FORM>\n");

  print("</B><P><IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/templates.html\">$documentation</A>");


}


function trangebuttom_1_prop()
{
  global $SAMSConf;
  global $TRANGEConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       GraphButton("main.php?show=exe&function=updatetrangeform&filename=trangebuttom_1_prop.php&id=$TRANGEConf->s_trange_id",
	               "basefrm","config_32.jpg","config_48.jpg","$trangebuttom_1_prop_trangebuttom_1_prop_1");
    }
}

?>
