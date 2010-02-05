<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddTRange()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

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
	exit;

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
   $DB->samsdb_query("INSERT INTO timerange ( s_name, s_days, s_timestart, s_timeend ) VALUES ( '$name', '$days', '$timestart', '$timeend' ) ");

  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print(" parent.basefrm.location.href = \"main.php?show=exe&filename=trangetray.php&function=addtrangeform\"; \n");
  print("</SCRIPT> \n");
}


function AddTRangeForm()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["type"])) $type=$_GET["type"];

  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php\";\n");
  print("</SCRIPT> \n");

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  PageTop("clock_48.jpg","$AddTRangeForm_trangetray_1");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/timerange.html\">$documentation</A>");
  print("<P>\n");
 
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
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addtrange\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"trangetray.php\">\n");

/* calendar */  
  print("<TABLE  BORDER=0>\n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>Name:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"name\" SIZE=50> \n");
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
       print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"day$i\"  trangeday> \n" );
     }  
  print("</TABLE>\n");
  print("<P>\n");
  
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_13\n");
  print("<P>\n");
  print("     <SELECT NAME=\"shour\"> \n");
  for($i=0;$i<24;$i++)
     {
           print("	       <OPTION value=$i>$i\n");
     }
  print("	       </SELECT>:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"smin\" SIZE=2 VALUE=00> - \n" );
  print("     <SELECT NAME=\"ehour\"> \n");
  for($i=23;$i>=0;$i--)
     {
           print("	       <OPTION value=$i>$i\n");
     }
  print("	       </SELECT>:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"emin\" SIZE=2 VALUE=59> \n" );
  print("</TABLE>\n");
/* calendar */  



  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Add\">\n");
  print("</FORM>\n");



       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\"><A HREF=\"doc/$SAMSConf->LANGCODE/urllists.html\">");
       print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
       print("<TD>$redirlisttray_AddRedirListForm_4");

}

function JSTRangeInfo()
{
  global $SAMSConf;
  global $USERConf;
  global $TRANGEConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  $code="<HTML><HEAD>
  <link rel=\"STYLESHEET\" type=\"text/css\" href=\"$SAMSConf->ICONSET/tree.css\">
  </HEAD>
  <BODY><CENTER>
  <TABLE WIDTH=\"95%\" border=0><TR><TD WIDTH=\"10%\"  valign=\"middle\">
  <img src=\"$SAMSConf->ICONSET/clock_48.jpg\" align=\"RIGHT\" valign=\"middle\" >
  <TD  valign=\"middle\"><h2  align=\"CENTER\">".$JSTRangeInfo_trangetray_1." <FONT COLOR=\"BLUE\">$TRANGEConf->s_name</h2>
  </FONT></TABLE>
  <P>
  <TABLE>";
  for($i=1;$i<8;$i++)
     {
	if($TRANGEConf->s_days[$i]=="CHECKED")
	   $code=$code."<TR><TD><B>".$week[$i]." <TD><img src=\"$SAMSConf->ICONSET/galka.gif\"> ";
	else
	   $code=$code."<TR><TD><B>".$week[$i]." <TD><img src=\"$SAMSConf->ICONSET/stop2.gif\"> ";
     }  
  
  $code=$code."</TABLE><B>$TRANGEConf->s_timestart - $TRANGEConf->s_timeend</B>
  <P>
  <IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">
  <A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/templates.html\">$documentation</A>
  <P>
  </CENTER>
  </BODY></HTML>";
  $code=str_replace("\"","\\\"",$code);
  $code=str_replace("\n","",$code);
//echo "$rrr";
//echo "$txt";
  //print(" parent.basefrm.document.write(\" $rrr \");\n");
  print(" parent.basefrm.document.write(\"$code\");\n");
  print(" parent.basefrm.document.close();\n");

}

function TRangeTray()
{
  if(isset($_GET["id"])) $id=$_GET["id"];

  global $SAMSConf;
  global $TRANGEConf;
  global $USERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  print("<SCRIPT>\n");
  JSTRangeInfo();
  print("</SCRIPT> \n");

  print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
  print("<TR HEIGHT=60>\n");
  print("<TD WIDTH=25%>");
  print("<B>$TRangeTray_trangetray_1<BR><FONT COLOR=\"BLUE\">$TRANGEConf->s_name</FONT></B>\n");

  ExecuteFunctions("./src", "trangebuttom","1");
  
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
