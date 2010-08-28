<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddShablon()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  $period="M";
  $clrdate="1980-01-01";
  if(isset($_GET["groupnick"])) $snick=$_GET["groupnick"];
  if(isset($_GET["defaulttraf"])) $defaulttraf=$_GET["defaulttraf"];
  if(isset($_GET["auth"])) $auth=$_GET["auth"];

  if(isset($_GET["period"])) $period=$_GET["period"];
  if(isset($_GET["newperiod"])) $newperiod=$_GET["newperiod"];
   
  if(isset($_GET["clryear"])) $clryear=$_GET["clryear"];
  if(isset($_GET["clrmonth"])) $clrmonth=$_GET["clrmonth"];
  if(isset($_GET["clrday"])) $clrday=$_GET["clrday"];
  if(isset($_GET["trange"])) $trange=$_GET["trange"];
  
   if($period=="A")
     {
       $period=$newperiod;
       $clrdate="$clryear-$clrmonth-$clrday";  
     }  
  $QUERY="INSERT INTO shablon ( s_name, s_quote, s_auth, s_period, s_clrdate, s_alldenied, s_shablon_id2 ) VALUES ( '$snick', '$defaulttraf', '$auth', '$period', '$clrdate', '0', '-1' ) ";
  $DB->samsdb_query($QUERY);
  $DB->samsdb_query_value("SELECT s_shablon_id FROM shablon WHERE s_name='$snick' ");
  $row=$DB->samsdb_fetch_array();
  $sid=$row['s_shablon_id'];
  $DB->free_samsdb_query();
  $DB->samsdb_query("INSERT INTO sconfig_time ( s_shablon_id, s_trange_id ) VALUES ( '$sid', '$trange' ) ");
//  UpdateLog("$SAMSConf->adminname","$shablonnew_AddShablon_1 $snick","01");

  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("  parent.basefrm.location.href =\"tray.php?show=exe&function=shablontray&filename=shablontray.php&id=$sid\";\n");  
  print("</SCRIPT> \n");
}

function NewShablonForm()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;
  
  PageTop("shablon.jpg","$shablon_1<BR>$shablonnew_NewShablonForm_1");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/templates.html\">$documentation</A>");
  print("<P>\n");

  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php\";\n");
  print("</SCRIPT> \n");

       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function TestShablonName(formname)\n");
       print("{\n");
       print("  var shablonname=formname.groupnick.value; \n");
       print("  if(shablonname.length==0) \n");
       print("    {\n");
       print("       alert(\"$shablonnew_NewShablonForm_19\");\n");
       print("       return false");
       print("    }\n");
       print("  return true");
       print("}\n");
       print("</SCRIPT> \n");

  print("<FORM NAME=\"NEWUSER\" ACTION=\"main.php\" onsubmit=\"return TestShablonName(NEWUSER)\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addshablon\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"shablonnew.php\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$shablonnew_NewShablonForm_6\">\n");
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
  print("<B>$shablonnew_NewShablonForm_9</B>\n");
  print("<BR><A HREF=\"main.php?show=exe&function=samsreconfigform&filename=configbuttom_1_prop.php\">"); 
  print("$shablonnew_NewShablonForm_17</A>!\n");
  print("<TD>\n");
  print("<SELECT NAME=\"auth\"> \n");
  $DB->samsdb_query_value("SELECT s_auth FROM auth_param WHERE s_param='enabled' AND s_value='1' ");
  while($row=$DB->samsdb_fetch_array())
  {
     print("<OPTION value=".$row['s_auth'].">".$row['s_auth']."\n");
  }
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
  
  $CCLEAN="";
//  if($SAMSConf->CCLEAN!="Y")  
//    $CCLEAN="DISABLED";

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonnew_NewShablonForm_10\n");
  print("<TD>\n");
  print("<SELECT NAME=\"period\" onchange=EnterPeriod(NEWUSER)  $CCLEAN> \n");
  print("<OPTION value=\"M\" SELECTED>$shablonnew_NewShablonForm_11\n");
  print("<OPTION value=\"W\">$shablonnew_NewShablonForm_12\n");
  print("<OPTION value=\"D\">$shablonbuttom_1_prop_UpdateShablonForm_27\n");
  print("<OPTION value=\"A\">$shablonnew_NewShablonForm_13\n");
  print("</SELECT>\n");

           print("<SCRIPT LANGUAGE=JAVASCRIPT> \n");
           print("function EnterPeriod(formname) \n");
           print("{ \n");
           print("  var period=formname.period.value; \n");
           print("  var clryear=formname.clryear.value; \n");
           print("  var clrmonth=formname.clrmonth.value; \n");
           print("  var clrday=formname.clrday.value; \n");
      //print("  value=window.confirm(\"1? \" );\n");
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
     
  print("<TR><TD>\n");
//  if($SAMSConf->CCLEAN!="Y")
//    print("<FONT COLOR=\"RED\">$shablonnew_NewShablonForm_18</FONT>\n");
  print("<TD> $shablonnew_NewShablonForm_14: \n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"newperiod\" SIZE=5 DISABLED>$shablonnew_NewShablonForm_15\n");
 
  print("<TR><TD><TD> $shablonnew_NewShablonForm_16: \n");
  print("<BR><INPUT TYPE=\"TEXT\" NAME=\"clryear\" SIZE=4 DISABLED VALUE=\"$YCLRVALUE\">:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"clrmonth\" SIZE=2 DISABLED VALUE=\"$MCLRVALUE\">:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"clrday\" SIZE=2 DISABLED VALUE=\"$DCLRVALUE\">\n");

  print("</TABLE>\n");
 
  
  
      
/* calendar */  
/*
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
*/
/* calendar */  
  
  
  
  print("<P>\n");
  print("<TABLE>\n");
  print("<TR><TD><B>$JSTRangeInfo_trangetray_1:</B><TD><SELECT NAME=\"trange\" ID=\"trange\" >\n");
  $num_rows=$DB->samsdb_query_value("SELECT * FROM timerange ");
  while($row=$DB->samsdb_fetch_array())
	{
           print("<OPTION VALUE=$row[s_trange_id]> $row[s_name] ($row[s_timestart] - $row[s_timeend] )");
	
	}
  print("</SELECT>\n");
 print("</TABLE>\n");
 
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$shablonnew_NewShablonForm_6\">\n");
  print("</FORM>\n");

       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\">");
       print("<A HREF=\"doc/$SAMSConf->LANGCODE/shablons.html\"><IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT></A>");
       print("<TD>$shablonnew_NewShablonForm_7");
       print(" $shablonnew_NewShablonForm_8");

}

?>
