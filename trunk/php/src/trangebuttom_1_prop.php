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
  $DB=new SAMSDB(&$SAMSConf);
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["name"])) $name=$_GET["name"];

  if(isset($_GET["shour"])) $shour=$_GET["shour"];
  if(isset($_GET["smin"])) $smin=$_GET["smin"];
  if(isset($_GET["ehour"])) $ehour=$_GET["ehour"];
  if(isset($_GET["emin"])) $emin=$_GET["emin"];
  if(isset($_GET["day1"])) $day1=$_GET["day1"];
  if(isset($_GET["day2"])) $day2=$_GET["day2"];
  if(isset($_GET["day3"])) $day3=$_GET["day3"];
  if(isset($_GET["day4"])) $day4=$_GET["day4"];
  if(isset($_GET["day5"])) $day5=$_GET["day5"];
  if(isset($_GET["day6"])) $day6=$_GET["day6"];
  if(isset($_GET["day7"])) $day7=$_GET["day7"];

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	{       exit;     }

   if($day1=="on")   $day1="M"; else $day1=""; 
   if($day2=="on")   $day2="T"; else $day2="";  
   if($day3=="on")   $day3="W"; else $day3="";  
   if($day4=="on")   $day4="H"; else $day4="";  
   if($day5=="on")   $day5="F"; else $day5="";  
   if($day6=="on")   $day6="A"; else $day6="";  
   if($day7=="on")   $day7="S"; else $day7="";  
   $days="$day1$day2$day3$day4$day5$day6$day7";  
   $timestart="$shour:$smin:00";  
   $timeend="$ehour:$emin:59";  
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
  
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_13\n");
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



       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\"><A HREF=\"doc/$SAMSConf->LANGCODE/urllists.html\">");
       print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
       print("<TD>$redirlisttray_AddRedirListForm_4");

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
