<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UpdateShablon()
{
  global $SAMSConf;
  $sguardgroups=array("ads","aggressive","audio-video","drugs","gambling",
   "hacking","mail","porn","proxy","violence","warez");

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access!=2)     {       exit;     }

  if(isset($_GET["id"])) $sname=$_GET["id"];
  if(isset($_GET["defaulttraf"])) $defaulttraf=$_GET["defaulttraf"];
  if(isset($_GET["userid"])) $shablonpool=$_GET["userid"];
  if(isset($_GET["userip"])) $userpool=$_GET["userip"];

  if(isset($_GET["day1"])) $day1=$_GET["day1"];
  if(isset($_GET["day2"])) $day2=$_GET["day2"];
  if(isset($_GET["day3"])) $day3=$_GET["day3"];
  if(isset($_GET["day4"])) $day4=$_GET["day4"];
  if(isset($_GET["day5"])) $day5=$_GET["day5"];
  if(isset($_GET["day6"])) $day6=$_GET["day6"];
  if(isset($_GET["day7"])) $day7=$_GET["day7"];
   
  if(isset($_GET["shour"])) $shour=$_GET["shour"];
  if(isset($_GET["smin"])) $smin=$_GET["smin"];
  if(isset($_GET["ehour"])) $ehour=$_GET["ehour"];
  if(isset($_GET["emin"])) $emin=$_GET["emin"];
  if(isset($_GET["auth"])) $auth=$_GET["auth"];
   
  if(isset($_GET["period"])) $period=$_GET["period"];
  if(isset($_GET["newperiod"])) $newperiod=$_GET["newperiod"];
   
  if(isset($_GET["clryear"])) $clryear=$_GET["clryear"];
  if(isset($_GET["clrmonth"])) $clrmonth=$_GET["clrmonth"];
  if(isset($_GET["clrday"])) $clrday=$_GET["clrday"];
  if(isset($_GET["alldenied"])) $alldenied=$_GET["alldenied"];
   
   if($day1=="on")   $day1="M"; else $day1=""; 
   if($day2=="on")   $day2="T"; else $day2="";  
   if($day3=="on")   $day3="W"; else $day3="";  
   if($day4=="on")   $day4="H"; else $day4="";  
   if($day5=="on")   $day5="F"; else $day5="";  
   if($day6=="on")   $day6="A"; else $day6="";  
   if($day7=="on")   $day7="S"; else $day7="";  

   if($alldenied=="on")   $alldenied="1"; else $alldenied="0";  
   
   if($period=="A")
     {
       $period=$newperiod;
       $clrdate="$clryear-$clrmonth-$clrday";  
     }  
  
  if($smin<0&&smin>60)
     $smin="00";
  //$emin=substr($emin,0,2);
  if($emin<0&&emin>60)
     $emin="00";

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("DELETE FROM sconfig WHERE sname=\"$sname\" ");

  $result=mysql_query("SELECT * FROM redirect");
  while($row=mysql_fetch_array($result))
     {
       if($_GET["_$row[filename]"]=="on")
          {
            $result2=mysql_query("INSERT INTO sconfig VALUES('$sname','$row[filename]') ");
          }
     }
  $result=mysql_query("UPDATE shablons SET alldenied=\"$alldenied\",traffic=\"$_GET[defaulttraf]\",shablonpool=\"$shablonpool\",userpool=\"$userpool\",days=\"$day1$day2$day3$day4$day5$day6$day7\",shour=\"$shour\",smin=\"$smin\",ehour=\"$ehour\",emin=\"$emin\",auth=\"$auth\",period=\"$period\",clrdate=\"$clrdate\"  WHERE name=\"$sname\" ");

  if($SAMSConf->REDIRECTOR=="squidguard")
    {
      $result=mysql_query("DELETE FROM sguard WHERE sname=\"$sname\" ");
      for($i=0;$i<11;$i++)
          {
            if($_GET["$sguardgroups[$i]"]=="on")
              {
                $result2=mysql_query("INSERT INTO sguard SET sname=\"$sname\",name=\"$sguardgroups[$i]\",domain=\"Y\",url=\"Y\",expr=\"Y\" ");
              }

          }
    }
}



function UpdateShablonForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
   $DENIEDDISABLED="";
   $ALLOWDISABLED="";
   $NTLMSELECTED="";
   $IPSELECTED="";
  $result2=mysql_query("SELECT * FROM shablons WHERE shablons.name=\"$id\" ");
  $row2=mysql_fetch_array($result2);
  $alldenied=$row2['alldenied'];
  PageTop("shablon.jpg","$shablon_1<BR>$shablonbuttom_1_prop_UpdateShablonForm_1 <FONT COLOR=\"BLUE\">$row2[nick]</FONT>");

//  $result=mysql_query("SELECT name,filename,type FROM redirect ORDER BY type DESC");
  print("<FORM NAME=\"UPDATESHABLON\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"updateshablon\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"shablonbuttom_1_prop.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$id\">\n");
  print("<TABLE  WIDTH=\"80%\">\n");
  print("<TR  bgcolor=blanchedalmond><TD ALIGN=RIGHT WIDTH=\"40%\"><B> </B>\n");
  print("<TD><B>$shablonbuttom_1_prop_UpdateShablonForm_2</B>\n");
  
  
 // перенаправление запроса
  print("<TR  bgcolor=blanchedalmond><TD WIDTH=\"40%\">\n");
  print("<TD ALIGN=LEFT WIDTH=\"60%\"><B>$shablonbuttom_1_prop_UpdateShablonForm_8\n");
  $result=mysql_query("SELECT name,filename,type FROM redirect WHERE type=\"redir\" ");
  while($row=mysql_fetch_array($result))
      {
        print("<TR><TD ALIGN=RIGHT WIDTH=\"40%\">\n");
        print("<IMG SRC=\"$SAMSConf->ICONSET/redir.jpg\">");

        $result2=mysql_query("SELECT * FROM sconfig WHERE sconfig.sname=\"$id\"&&sconfig.set=\"$row[filename]\" ");
        $row2=mysql_fetch_array($result2);
        if($row['filename']==$row2['set'])
          print("<INPUT TYPE=\"CHECKBOX\" NAME=\"_$row[filename]\" CHECKED>\n");
        else
          print("<INPUT TYPE=\"CHECKBOX\" NAME=\"_$row[filename]\">\n");
        print("<TD WIDTH=\"60%\"> $row[name]\n");
      }
  if($alldenied>0)
    {
      $DENIEDDISABLED="DISABLED";
      $DENIEDCHECKED="CHECKED";
    }  
  else
    {
      $DISABLED="";
      $DENIEDCHECKED="";
   }   
        
 // запрет доступа
  $dcount=0;
  
  print("<TR bgcolor=blanchedalmond><TD WIDTH=\"40%\" ALIGN=RIGHT>\n");
  print("<INPUT TYPE=\"CHECKBOX\" NAME=\"alldenied\" onclick=EnableDeniedLists(UPDATESHABLON) $DENIEDCHECKED>\n");
  print("<TD ALIGN=LEFT WIDTH=\"60%\"><B>$shablonbuttom_1_prop_UpdateShablonForm_22 \n");
  
  print("<TR bgcolor=blanchedalmond><TD WIDTH=\"40%\" ALIGN=RIGHT>\n");
  print("<TD ALIGN=LEFT WIDTH=\"60%\"><B>$shablonbuttom_1_prop_UpdateShablonForm_9 \n");
  $result=mysql_query("SELECT name,filename,type FROM redirect WHERE type=\"denied\" ");
  while($row=mysql_fetch_array($result))
      {
        print("<TR><TD ALIGN=RIGHT WIDTH=\"40%\">\n");
        print("<IMG SRC=\"$SAMSConf->ICONSET/stop.jpg\">");

        $result2=mysql_query("SELECT * FROM sconfig WHERE sconfig.sname=\"$id\"&&sconfig.set=\"$row[filename]\" ");
        $row2=mysql_fetch_array($result2);
        if($row['filename']==$row2['set'])
          print("<INPUT TYPE=\"CHECKBOX\" NAME=\"_$row[filename]\" CHECKED $DENIEDDISABLED>\n");
        else
          print("<INPUT TYPE=\"CHECKBOX\" NAME=\"_$row[filename]\" $DENIEDDISABLED>\n");
        print("<TD WIDTH=\"60%\"> $row[name]\n");
	$dlist[$dcount]="_$row[filename]";
        $dcount++;
      }
  
 // запрет доступа
  print("<TR bgcolor=blanchedalmond><TD WIDTH=\"40%\" ALIGN=RIGHT>\n");
  print("<TD ALIGN=LEFT WIDTH=\"60%\"><B>$shablonbuttom_1_prop_UpdateShablonForm_10\n");
  $result=mysql_query("SELECT name,filename,type FROM redirect WHERE type=\"regex\" ");
  while($row=mysql_fetch_array($result))
      {
        print("<TR><TD ALIGN=RIGHT WIDTH=\"40%\">\n");
        print("<IMG SRC=\"$SAMSConf->ICONSET/stop.jpg\">");

        $result2=mysql_query("SELECT * FROM sconfig WHERE sconfig.sname=\"$id\"&&sconfig.set=\"$row[filename]\" ");
        $row2=mysql_fetch_array($result2);
        if($row['filename']==$row2['set'])
          print("<INPUT TYPE=\"CHECKBOX\" NAME=\"_$row[filename]\" CHECKED $DENIEDDISABLED>\n");
        else
          print("<INPUT TYPE=\"CHECKBOX\" NAME=\"_$row[filename]\" $DENIEDDISABLED>\n");
        print("<TD WIDTH=\"60%\"> $row[name]\n");
	$dlist[$dcount]="_$row[filename]";
        $dcount++;
      }
  
 // доступ разрешен
  $acount=0;
  print("<TR bgcolor=blanchedalmond><TD WIDTH=\"40%\" ALIGN=RIGHT>\n");
//  print("<INPUT TYPE=\"RADIO\" NAME=\"denied\" VALUE=\"allowed\"  onclick=DisableDeniedLists(UPDATESHABLON) $ALLOWCHECKED>\n");
  print("<TD ALIGN=LEFT WIDTH=\"60%\"><B> $shablonbuttom_1_prop_UpdateShablonForm_11\n");
  $result=mysql_query("SELECT name,filename,type FROM redirect WHERE type=\"allow\" ");
  while($row=mysql_fetch_array($result))
      {
        print("<TR><TD ALIGN=RIGHT WIDTH=\"40%\">\n");
        print("<IMG SRC=\"$SAMSConf->ICONSET/adir.gif\">");

        $result2=mysql_query("SELECT * FROM sconfig WHERE sconfig.sname=\"$id\"&&sconfig.set=\"$row[filename]\" ");
        $row2=mysql_fetch_array($result2);
        if($row['filename']==$row2['set'])
          print("<INPUT TYPE=\"CHECKBOX\" NAME=\"_$row[filename]\" CHECKED $ALLOWDISABLED>\n");
        else
          print("<INPUT TYPE=\"CHECKBOX\" NAME=\"_$row[filename]\" $ALLOWDISABLED>\n");
        print("<TD WIDTH=\"60%\"> $row[name]\n");
	$alist[$acount]="_$row[filename]";
        $acount++;
      }

   // file extensions
  $acount=0;
  print("<TR bgcolor=blanchedalmond><TD WIDTH=\"40%\" ALIGN=RIGHT>\n");
//  print("<INPUT TYPE=\"RADIO\" NAME=\"denied\" VALUE=\"allowed\"  onclick=DisableDeniedLists(UPDATESHABLON) $ALLOWCHECKED>\n");
  print("<TD ALIGN=LEFT WIDTH=\"60%\"><B> $shablonbuttom_1_prop_UpdateShablonForm_23\n");
  $result=mysql_query("SELECT name,filename,type FROM redirect WHERE type=\"files\" ");
  while($row=mysql_fetch_array($result))
      {
        print("<TR><TD ALIGN=RIGHT WIDTH=\"40%\">\n");
        print("<IMG SRC=\"$SAMSConf->ICONSET/stop.jpg\">");

        $result2=mysql_query("SELECT * FROM sconfig WHERE sconfig.sname=\"$id\"&&sconfig.set=\"$row[filename]\" ");
        $row2=mysql_fetch_array($result2);
        if($row['filename']==$row2['set'])
          print("<INPUT TYPE=\"CHECKBOX\" NAME=\"_$row[filename]\" CHECKED $ALLOWDISABLED>\n");
        else
          print("<INPUT TYPE=\"CHECKBOX\" NAME=\"_$row[filename]\" $ALLOWDISABLED>\n");
        print("<TD WIDTH=\"60%\"> $row[name]\n");
	$alist[$acount]="_$row[filename]";
        $acount++;
      }
    
  print("</TABLE>\n");
 
  $sguardgroups=array("ads","aggressive","audio-video","drugs","gambling",
   "hacking","mail","porn","proxy","violence","warez");
  $result=mysql_query("SELECT * FROM shablons WHERE name=\"$id\" ");
  $row=mysql_fetch_array($result);
  print("<TABLE>\n");

           print("<SCRIPT LANGUAGE=JAVASCRIPT>\n");
           print("function EnableDeniedLists(formname)\n");
           print("{\n");
           print("  if(formname.alldenied.checked==true)\n");
           print("    {\n");
           for($i=0;$i<$dcount;$i++)
	      {
	         print("    formname.$dlist[$i].disabled=true; \n    ");
	      };
           print("    }\n");
           print("  if(formname.alldenied.checked==false)\n");
           print("    {\n");
	   for($i=0;$i<$dcount;$i++)
	      {
	         print("    formname.$dlist[$i].disabled=false; \n    ");
	      };
           print("    }\n");
	   print("}\n");
           print("</SCRIPT>\n");
    
  
  
  
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_4:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"defaulttraf\" SIZE=6 VALUE=\"$row[traffic]\"> <B> 0 - unlimited traffic \n" );

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_5:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"userid\" SIZE=6 VALUE=\"$row[shablonpool]\"> \n" );
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_6:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"userip\" SIZE=6 VALUE=\"$row[userpool]\"> \n" );
  
  
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_19 \n");
  print("<TD>\n");
  print("<SELECT NAME=\"auth\"> \n");
  
  if($row['auth']=="ip")   
     $IPSELECTED="SELECTED";
     print("<OPTION value=ip $IPSELECTED> IP\n");
     
  if($row['auth']=="ntlm")   
     $NTLMSELECTED="SELECTED";
  if($SAMSConf->AUTH=="ntlm")   
     print("<OPTION value=ntlm $NTLMSELECTED> NTLM\n");
     
  if($row['auth']=="adld")
     $ADSELECTED="SELECTED";
  if($SAMSConf->AUTH=="adld")
     print("<OPTION value=adld $ADSELECTED> ADLD\n");
     
  if($row['auth']=="ncsa")   
     $NCSASELECTED="SELECTED";
  if($SAMSConf->AUTH=="ncsa")   
     print("<OPTION value=ncsa $NCSASELECTED> NCSA\n");
  
  print("</SELECT>\n");
  

           print("<SCRIPT LANGUAGE=JAVASCRIPT> \n");
           print("function EnterPeriod(formname) \n");
           print("{ \n");
           print("  var period=formname.period.value; \n");
           print("  var clryear=formname.clryear.value; \n");
           print("  var clrmonth=formname.clrmonth.value; \n");
           print("  var clrday=formname.clrday.value; \n");
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
  
  
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_20\n");
  print("<TD>\n");
  print("<SELECT NAME=\"period\" onchange=EnterPeriod(UPDATESHABLON)> \n");
  
  $MSELECTED="";
  $WSELECTED="";
  $ASELECTED="";
  $ADISABLED="DISABLED";
  $AVALUE="";
  if($row['period']=="M")   
     $MSELECTED="SELECTED";
  print("<OPTION value=\"M\" $MSELECTED>$shablonbuttom_1_prop_UpdateShablonForm_24\n");
  
  if($row['period']=="W")   
     $WSELECTED="SELECTED";
  print("<OPTION value=\"W\" $WSELECTED>$shablonbuttom_1_prop_UpdateShablonForm_25\n");
  
  if($row['period']!="M"&&$row['period']!="W")
    {   
      $ASELECTED="SELECTED";
      $ADISABLED="";
      $AVALUE=$row['period'];
      $YCLRVALUE=substr($row['clrdate'],0,4);
      $MCLRVALUE=substr($row['clrdate'],5,2);
      $DCLRVALUE=substr($row['clrdate'],8,2);
    }
  else
    {
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
    }    
  print("<OPTION value=\"A\" $ASELECTED>$shablonbuttom_1_prop_UpdateShablonForm_15\n");
  print("</SELECT>\n");
   
  print("<TR><TD><TD> $shablonbuttom_1_prop_UpdateShablonForm_16: \n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"newperiod\" SIZE=5 $ADISABLED VALUE=\"$AVALUE\"> $shablonbuttom_1_prop_UpdateShablonForm_17\n");
  
  print("<TR><TD><TD>$shablonbuttom_1_prop_UpdateShablonForm_18: \n");
  print("<BR><INPUT TYPE=\"TEXT\" NAME=\"clryear\" SIZE=4 $ADISABLED VALUE=\"$YCLRVALUE\">:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"clrmonth\" SIZE=2 $ADISABLED VALUE=\"$MCLRVALUE\">:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"clrday\" SIZE=2 $ADISABLED VALUE=\"$DCLRVALUE\">\n");

  print("</TABLE>\n");
  print("<P>\n");
  
/* calendar */  
  $weekday=array("", "M","T","W","H","F","A","S");  

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
       print("<TD>\n");
       $CHECKED="";
       if(strpos(" $row[days]","$weekday[$i]")>0)
         {
           $CHECKED="CHECKED";
	 }   
       print("<INPUT TYPE=\"CHECKBOX\" NAME=\"day$i\"  $CHECKED> \n" );
     }  
  print("</TABLE>\n");
  print("<P>\n");
  
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_13\n");
  print("<P>\n");
  print("     <SELECT NAME=\"shour\"> \n");
  for($i=0;$i<24;$i++)
     {
           if($row['shour']==$i)
             print("	       <OPTION value=$i SELECTED>$i\n");
	   else
             print("	       <OPTION value=$i>$i\n");
     }
  print("	       </SELECT>:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"smin\" SIZE=2 VALUE=$row[smin]> - \n" );
  print("     <SELECT NAME=\"ehour\"> \n");
  for($i=23;$i>=0;$i--)
     {
           if($row['ehour']==$i)
             print("	       <OPTION value=$i SELECTED>$i\n");
	   else
           print("	       <OPTION value=$i>$i\n");
     }
  print("	       </SELECT>:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"emin\" SIZE=2 VALUE=$row[emin]> \n" );
  print("</TABLE>\n");
/* calendar */  
  
  
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$shablonbuttom_1_prop_UpdateShablonForm_7\">\n");
  print("</FORM>\n");


}


function shablonbuttom_1_prop()
{
  global $SAMSConf;
  
  if(isset($_GET["id"])) $id=$_GET["id"];
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=updateshablonform&filename=shablonbuttom_1_prop.php&id=$id",
	               "basefrm","config_32.jpg","config_48.jpg","$shablonbuttom_1_prop_shablonbuttom_1_prop_1");
    }
}

?>
