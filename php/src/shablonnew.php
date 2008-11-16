<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddShablon()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["groupnick"])) $snick=$_GET["groupnick"];
  if(isset($_GET["defaulttraf"])) $defaulttraf=$_GET["defaulttraf"];
  if(isset($_GET["userid"])) $shablonpool=$_GET["userid"];
  if(isset($_GET["userip"])) $userpool=$_GET["userip"];
  if(isset($_GET["shour"])) $shour=$_GET["shour"];
  if(isset($_GET["smin"])) $smin=$_GET["smin"];
  if(isset($_GET["ehour"])) $ehour=$_GET["ehour"];
  if(isset($_GET["emin"])) $emin=$_GET["emin"];
  if(isset($_GET["auth"])) $auth=$_GET["auth"];

  if(isset($_GET["day1"])) $day1=$_GET["day1"];
  if(isset($_GET["day2"])) $day2=$_GET["day2"];
  if(isset($_GET["day3"])) $day3=$_GET["day3"];
  if(isset($_GET["day4"])) $day4=$_GET["day4"];
  if(isset($_GET["day5"])) $day5=$_GET["day5"];
  if(isset($_GET["day6"])) $day6=$_GET["day6"];
  if(isset($_GET["day7"])) $day7=$_GET["day7"];
   
  if(isset($_GET["period"])) $period=$_GET["period"];
  if(isset($_GET["newperiod"])) $newperiod=$_GET["newperiod"];
   
  if(isset($_GET["clryear"])) $clryear=$_GET["clryear"];
  if(isset($_GET["clrmonth"])) $clrmonth=$_GET["clrmonth"];
  if(isset($_GET["clrday"])) $clrday=$_GET["clrday"];
  
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
   
   if($day1=="on")   $day1="M"; else $day1=""; 
   if($day2=="on")   $day2="T"; else $day2="";  
   if($day3=="on")   $day3="W"; else $day3="";  
   if($day4=="on")   $day4="H"; else $day4="";  
   if($day5=="on")   $day5="F"; else $day5="";  
   if($day6=="on")   $day6="A"; else $day6="";  
   if($day7=="on")   $day7="S"; else $day7="";  

   if($period=="A")
     {
       $period=$newperiod;
       $clrdate="$clryear-$clrmonth-$clrday";  
     }  
  
  $sname=TempName();
//  $result=mysql_query("INSERT INTO shablons (name,nick,shablonpool,userpool,traffic) VALUES('$sname','$snick','$shablonpool','$userpool','$defaulttraf') ");
  $result=mysql_query("INSERT INTO shablons SET name=\"$sname\",nick=\"$snick\",shablonpool=\"$shablonpool'\",userpool=\"$userpool\",traffic=\"$defaulttraf'\",days=\"$day1$day2$day3$day4$day5$day6$day7\",shour=\"$shour\",smin=\"$smin\",ehour=\"$ehour\",emin=\"$emin\",auth=\"$auth\",period=\"$period\",clrdate=\"$clrdate\",alldenied=\"0\" ");
  UpdateLog("$SAMSConf->adminname","$shablonnew_AddShablon_1 $snick","01");

  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");
}

function NewShablonForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("shablon.jpg","$shablon_1<BR>$shablonnew_NewShablonForm_1");

  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php\";\n");
  print("</SCRIPT> \n");

  print("<FORM NAME=\"NEWUSER\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addshablon\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"shablonnew.php\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonnew_NewShablonForm_2:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"groupnick\" SIZE=30> \n" );

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonnew_NewShablonForm_3:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"defaulttraf\" SIZE=6 VALUE=\"100\"> <B> 0 - unlimited traffic\n" );

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonnew_NewShablonForm_4:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"userid\" SIZE=6 VALUE=\"524288\"> \n" );
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonnew_NewShablonForm_5:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"userip\" SIZE=6 VALUE=\"524288\"> \n" );

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonnew_NewShablonForm_9</B>\n");
  print("<BR><A HREF=\"main.php?show=exe&function=samsreconfigform&filename=configbuttom_1_prop.php\">"); 
  print("$shablonnew_NewShablonForm_17</A>!\n");
  print("<TD>\n");
  print("<SELECT NAME=\"auth\"> \n");
     print("<OPTION value=ip>IP\n");
  if($SAMSConf->AUTH=="ntlm")   
     print("<OPTION value=ntlm SELECTED>NTLM\n");
  if($SAMSConf->AUTH=="adld")   
     print("<OPTION value=adld SELECTED>ADLD\n");
  if($SAMSConf->AUTH=="ncsa")   
     print("<OPTION value=ncsa SELECTED>NCSA\n");
  print("</SELECT>\n");

           print("<SCRIPT LANGUAGE=JAVASCRIPT> \n");
           print("function EnterPeriod(formname) \n");
           print("{ \n");
           print("  var period=formname.period.value; \n");
           print("  if(period==\"A\") \n");
           print("    {\n");
           print("      formname.newperiod.disabled=false;  \n");
           print("    }\n");
           print("  else \n");
           print("    {\n");
           print("      formname.newperiod.disabled=true;  \n");
           print("    }\n");
           print("}\n");
           print("</SCRIPT> \n");
  
  
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonnew_NewShablonForm_10\n");
  print("<TD>\n");
  print("<SELECT NAME=\"period\" onchange=EnterPeriod(NEWUSER)> \n");
  print("<OPTION value=\"M\" SELECTED>$shablonnew_NewShablonForm_11\n");
  print("<OPTION value=\"W\">$shablonnew_NewShablonForm_12\n");
  print("<OPTION value=\"A\">$shablonnew_NewShablonForm_13\n");
  print("</SELECT>\n");

           print("<SCRIPT LANGUAGE=JAVASCRIPT> \n");
           print("function EnterPeriod(formname) \n");
           print("{ \n");
           print("  var period=formname.period.value; \n");
           print("  var clryear=formname.clryear.value; \n");
           print("  var clrmonth=formname.clrmonth.value; \n");
           print("  var clrday=formname.clrday.value; \n");
      print("  value=window.confirm(\"1? \" );\n");
            print("  if(period==\"A\") \n");
           print("    {\n");
           print("      formname.newperiod.disabled=false;  \n");
           print("      formname.clryear.disabled=false;  \n");
           print("      formname.clrmonth.disabled=false;  \n");
           print("      formname.clrday.disabled=false;  \n");
           print("    }\n");
           print("  else \n");
           print("    {\n");
           print("      formname.newperiod.disabled=true;  \n");
           print("      formname.clryear.disabled=true;  \n");
           print("      formname.clrmonth.disabled=true;  \n");
           print("      formname.clrday.disabled=true;  \n");
           print("    }\n");
           print("}\n");
           print("</SCRIPT> \n");
  
      $month=array(0,1,2,3,4,5,6,7,8,9,10,11,12); 
      $days=array(0,31,28,31,30,31,30,31,31,30,31,30,31); 
      $YCLRVALUE=strftime("%Y");
      $MCLRVALUE=strftime("%m");
      $DCLRVALUE=strftime("%d");
      if($DCLRVALUE+1>$days[$MCLRVALUE])
        {
	  $DCLRVALUE=1;
	  $MCLRVALUE+=1;
	  if($MCLRVALUE>12)
	    {
	      $MCLRVALUE=1;
	      $YCLRVALUE+=1;
	    }
	}
       else
        $DCLRVALUE+=1; 	
     
  print("<TR><TD><TD> $shablonnew_NewShablonForm_14: \n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"newperiod\" SIZE=5 DISABLED>$shablonnew_NewShablonForm_15\n");
 
  print("<TR><TD><TD> $shablonnew_NewShablonForm_16: \n");
  print("<BR><INPUT TYPE=\"TEXT\" NAME=\"clryear\" SIZE=4 DISABLED VALUE=\"$YCLRVALUE\">:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"clrmonth\" SIZE=2 DISABLED VALUE=\"$MCLRVALUE\">:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"clrday\" SIZE=2 DISABLED VALUE=\"$DCLRVALUE\">\n");
  
  print("</TABLE>\n");
 
  
  
      
/* calendar */  
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
       print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"day$i\"  CHECKED> \n" );
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
  
  
  
  
  
  
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$shablonnew_NewShablonForm_6\">\n");
  print("</FORM>\n");

       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\">");
       print("<A HREF=\"doc/$SAMSConf->LANGCODE/shablons.html\"><IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT></A>");
       print("<TD>$shablonnew_NewShablonForm_7");
       print(" $shablonnew_NewShablonForm_8");

}

?>
