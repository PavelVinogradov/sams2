<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UpdateShablon()
{
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB(&$SAMSConf);
  $DB2=new SAMSDB(&$SAMSConf);
  $sguardgroups=array("ads","aggressive","audio-video","drugs","gambling",
   "hacking","mail","porn","proxy","violence","warez");

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

   $period="M";
 if(isset($_GET["id"])) $sname=$_GET["id"];
  if(isset($_GET["defaulttraf"])) $defaulttraf=$_GET["defaulttraf"];
  if(isset($_GET["userid"])) $shablonpool=$_GET["userid"];
  if(isset($_GET["userip"])) $userpool=$_GET["userip"];

  if(isset($_GET["auth"])) $auth=$_GET["auth"];
  if(isset($_GET["period"])) $period=$_GET["period"];
  if(isset($_GET["newperiod"])) $newperiod=$_GET["newperiod"];
   
  if(isset($_GET["clryear"])) $clryear=$_GET["clryear"];
  if(isset($_GET["clrmonth"])) $clrmonth=$_GET["clrmonth"];
  if(isset($_GET["clrday"])) $clrday=$_GET["clrday"];
  if(isset($_GET["alldenied"])) $alldenied=$_GET["alldenied"];
   
  if(isset($_GET["trange"])) $trange=$_GET["trange"];

$tc=count($trange);
//echo "$trange [$tc]<BR>";
for($i=0;$i<$tc;$i++)
  echo "$trange[$i] ";
//exit(0);
   if($alldenied=="on")   $alldenied="1"; else $alldenied="0";  

//  if($clrday=="") $clrday="0000-00-00";
   $clrdate="$clryear-$clrmonth-$clrday";
   if($clrdate="=--") $clrdate="1980-01-01";
   if($period=="A")
     {
       $period=$newperiod;
       $clrdate="$clryear-$clrmonth-$clrday";  
     }  
  if($smin<0&&smin>60)
     $smin="00";
  if($emin<0&&emin>60)
     $emin="00";

  $num_rows=$DB->samsdb_query("DELETE FROM sconfig WHERE s_shablon_id='$sname' ");

  $num_rows=$DB->samsdb_query_value("SELECT * FROM redirect");
  while($row=$DB->samsdb_fetch_array())
     {
//$qqq=$_GET["$row[s_redirect_id]"];
       if($_GET["$row[s_redirect_id]"]=="on")
          {
            $num_rows=$DB2->samsdb_query("INSERT INTO sconfig VALUES('$sname','$row[s_redirect_id]') ");
          }
     }
//echo "UPDATE shablon SET s_alldenied='$alldenied', s_quote='$_GET[defaulttraf]', s_shablonpool='$shablonpool', s_userpool='$userpool', s_auth='$auth', s_period='$period', s_clrdate='$clrdate'  WHERE s_shablon_id='$sname'";
  $num_rows=$DB2->samsdb_query("UPDATE shablon SET s_alldenied='$alldenied', s_quote='$_GET[defaulttraf]', s_shablonpool='$shablonpool', s_userpool='$userpool', s_auth='$auth', s_period='$period', s_clrdate='$clrdate'  WHERE s_shablon_id='$sname' ");

  print("<SCRIPT>\n");
  print("  parent.basefrm.location.href=\"main.php?show=exe&function=updateshablonform&filename=shablonbuttom_1_prop.php&id=$sname\"; \n");
  print("</SCRIPT> \n");

}



function UpdateShablonForm()
{
  global $SAMSConf;
  global $SHABLONConf;
  global $USERConf;
  $s_name=array();
  $s_type=array();
  $s_id=array();
  $s_selected=array();
  $credir=0;

  $DB=new SAMSDB(&$SAMSConf);
  $DB2=new SAMSDB(&$SAMSConf);
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

   $DENIEDDISABLED="";
   $ALLOWDISABLED="";
   $NTLMSELECTED="";
   $IPSELECTED="";
//  $num_rows=$DB->samsdb_query_value("SELECT s_name FROM shablon WHERE s_shablon_id='$id' ");
//  $row=$DB->samsdb_fetch_array($result2);
//  $alldenied=$row2['alldenied'];
  PageTop("shablon.jpg","$shablon_1<BR>$shablonbuttom_1_prop_UpdateShablonForm_1 <FONT COLOR=\"BLUE\">$SHABLONConf->s_name</FONT>");
//  $result=mysql_query("SELECT name,filename,type FROM redirect ORDER BY type DESC");
  print("<FORM NAME=\"UPDATESHABLON\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"updateshablon\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"shablonbuttom_1_prop.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$SHABLONConf->s_shablon_id\">\n");
  print("<TABLE  WIDTH=\"80%\">\n");
  print("<TR  bgcolor=blanchedalmond><TD ALIGN=RIGHT WIDTH=\"40%\"><B> </B>\n");
  print("<TD><B>$shablonbuttom_1_prop_UpdateShablonForm_2</B>\n");
  
  $credir=0;
  $cselect=0;
  $num_rows=$DB->samsdb_query_value("SELECT s_redirect_id, s_name, s_type FROM redirect ORDER BY s_type ");
  while($row=$DB->samsdb_fetch_array())
      {
	$s_name[$credir]=$row['s_name'];
	$s_type[$credir]=$row['s_type'];
	$s_id[$credir]=$row['s_redirect_id'];
	$credir++;
     }
  $DB->free_samsdb_query();
  $num_rows=$DB->samsdb_query_value("SELECT redirect.s_redirect_id FROM redirect LEFT JOIN sconfig ON redirect.s_redirect_id=sconfig.s_redirect_id WHERE sconfig.s_shablon_id='$id' ");
  while($row=$DB->samsdb_fetch_array())
      {
	$s_selected[$cselect]=$row['s_redirect_id'];
	$cselect++;
     }
  $DB->free_samsdb_query();

 // перенаправление запроса
  print("<TR  bgcolor=blanchedalmond><TD WIDTH=\"40%\">\n");
  print("<TD ALIGN=LEFT WIDTH=\"60%\"><B>$shablonbuttom_1_prop_UpdateShablonForm_8\n");
  for($i=0; $i<$credir; $i++)
      {
	if($s_type[$i]=="redir")
	  {
	        print("<TR><TD ALIGN=RIGHT WIDTH=\"40%\">\n");
        	print("<IMG SRC=\"$SAMSConf->ICONSET/redir.jpg\"> ");
		$checked="";
		if(in_array($s_id[$i], $s_selected))
			$checked="CHECKED";
		print("<INPUT TYPE=\"CHECKBOX\" NAME=\"$s_id[$i]\" $checked>\n");
		print("<TD WIDTH=\"60%\"> $s_name[$i]\n");

	  }
     }

  print("<TR bgcolor=blanchedalmond><TD WIDTH=\"40%\" ALIGN=RIGHT>\n");
  print("<INPUT TYPE=\"CHECKBOX\" NAME=\"alldenied\" onclick=EnableDeniedLists(UPDATESHABLON) $DENIEDCHECKED>\n");
  print("<TD ALIGN=LEFT WIDTH=\"60%\"><B>$shablonbuttom_1_prop_UpdateShablonForm_22 \n");

 // запрет доступа
  print("<TR  bgcolor=blanchedalmond><TD WIDTH=\"40%\">\n");
  print("<TD ALIGN=LEFT WIDTH=\"60%\"><B>$shablonbuttom_1_prop_UpdateShablonForm_9\n");
  for($i=0; $i<$credir; $i++)
      {
	if($s_type[$i]=="denied")
	  {
	        print("<TR><TD ALIGN=RIGHT WIDTH=\"40%\">\n");
        	print("<IMG SRC=\"$SAMSConf->ICONSET/redir.jpg\"> ");
		$checked="";
		if(in_array($s_id[$i], $s_selected))
			$checked="CHECKED";
		print("<INPUT TYPE=\"CHECKBOX\" NAME=\"$s_id[$i]\" $checked>\n");
		print("<TD WIDTH=\"60%\"> $s_name[$i]\n");

	  }
     }

 // запрет доступа по регулярным выражениям
  print("<TR  bgcolor=blanchedalmond><TD WIDTH=\"40%\">\n");
  print("<TD ALIGN=LEFT WIDTH=\"60%\"><B>$shablonbuttom_1_prop_UpdateShablonForm_10\n");
  for($i=0; $i<$credir; $i++)
      {
	if($s_type[$i]=="regex")
	  {
	        print("<TR><TD ALIGN=RIGHT WIDTH=\"40%\">\n");
        	print("<IMG SRC=\"$SAMSConf->ICONSET/redir.jpg\"> ");
		$checked="";
		if(in_array($s_id[$i], $s_selected))
			$checked="CHECKED";
		print("<INPUT TYPE=\"CHECKBOX\" NAME=\"$s_id[$i]\" $checked>\n");
		print("<TD WIDTH=\"60%\"> $s_name[$i]\n");

	  }
     }

 // доступ разрешен
  print("<TR  bgcolor=blanchedalmond><TD WIDTH=\"40%\">\n");
  print("<TD ALIGN=LEFT WIDTH=\"60%\"><B>$shablonbuttom_1_prop_UpdateShablonForm_11\n");
  for($i=0; $i<$credir; $i++)
      {
	if($s_type[$i]=="allow")
	  {
	        print("<TR><TD ALIGN=RIGHT WIDTH=\"40%\">\n");
        	print("<IMG SRC=\"$SAMSConf->ICONSET/redir.jpg\"> ");
		$checked="";
		if(in_array($s_id[$i], $s_selected))
			$checked="CHECKED";
		print("<INPUT TYPE=\"CHECKBOX\" NAME=\"$s_id[$i]\" $checked>\n");
		print("<TD WIDTH=\"60%\"> $s_name[$i]\n");

	  }
     }

 // afqks
  print("<TR  bgcolor=blanchedalmond><TD WIDTH=\"40%\">\n");
  print("<TD ALIGN=LEFT WIDTH=\"60%\"><B>$shablonbuttom_1_prop_UpdateShablonForm_23\n");
  for($i=0; $i<$credir; $i++)
      {
	if($s_type[$i]=="files")
	  {
	        print("<TR><TD ALIGN=RIGHT WIDTH=\"40%\">\n");
        	print("<IMG SRC=\"$SAMSConf->ICONSET/redir.jpg\"> ");
		$checked="";
		if(in_array($s_id[$i], $s_selected))
			$checked="CHECKED";
		print("<INPUT TYPE=\"CHECKBOX\" NAME=\"$s_id[$i]\" $checked>\n");
		print("<TD WIDTH=\"60%\"> $s_name[$i]\n");

	  }
     }

    
  print("</TABLE>\n");
 
  $sguardgroups=array("ads","aggressive","audio-video","drugs","gambling",
   "hacking","mail","porn","proxy","violence","warez");
  //$num_rows=$DB->samsdb_query_value("SELECT * FROM shablon WHERE s_shablon_id='$id' ");
  //$row=$DB->samsdb_fetch_array();
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
  print("<INPUT TYPE=\"TEXT\" NAME=\"defaulttraf\" SIZE=6 VALUE=\"$SHABLONConf->s_quote\"> <B> 0 - unlimited traffic \n" );

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_5:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"userid\" SIZE=6 VALUE=\"$SHABLONConf->s_shablonpool\"> \n" );
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_6:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"userip\" SIZE=6 VALUE=\"$SHABLONConf->s_userpool\"> \n" );
  
  
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_19 \n");
  print("<TD>\n");
  print("<SELECT NAME=\"auth\"> \n");
  

  $DB->samsdb_query_value("SELECT s_auth,s_value FROM auth_param WHERE s_param='enabled' AND s_value='1' ");
  while($row=$DB->samsdb_fetch_array())
  {
     $SELECTED="";
     if($row['s_auth']==$SHABLONConf->s_auth)
	$SELECTED="SELECTED";
     print("<OPTION value=".$row['s_auth']." $SELECTED>".$row['s_auth']." \n");
  }
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
  
  $CCLEAN="";
  if($SAMSConf->CCLEAN!="Y")  
    $CCLEAN="DISABLED";
  $CCLEAN="";

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$shablonbuttom_1_prop_UpdateShablonForm_20 \n");
  print("<TD>\n");
  print("<SELECT NAME=\"period\" onchange=EnterPeriod(UPDATESHABLON) $CCLEAN> \n");

  $MSELECTED="";
  $WSELECTED="";
  $ASELECTED="";
  $ADISABLED="DISABLED";
  $AVALUE="1";
  if($SHABLONConf->s_period=="M")   
     $MSELECTED="SELECTED";
  print("<OPTION value=\"M\" $MSELECTED>$shablonbuttom_1_prop_UpdateShablonForm_24 \n");
  
  if($SHABLONConf->s_period=="W")   
     $WSELECTED="SELECTED";
  print("<OPTION value=\"W\" $WSELECTED>$shablonbuttom_1_prop_UpdateShablonForm_25 \n");

  if($SHABLONConf->s_period!="M"&&$SHABLONConf->s_period!="W")
    {   
      $ASELECTED="SELECTED";
      $ADISABLED="";
      $AVALUE=$SHABLONConf->s_period;
      $YCLRVALUE=substr($SHABLONConf->s_clrdate,0,4);
      $MCLRVALUE=substr($SHABLONConf->s_clrdate,5,2);
      $DCLRVALUE=substr($SHABLONConf->s_clrdate,8,2);
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
   
  print("<TR><TD>\n");
//  if($SAMSConf->CCLEAN!="Y")
//    print("<FONT COLOR=\"RED\"> $shablonbuttom_1_prop_UpdateShablonForm_26</FONT>\n");
  print("<TD> $shablonbuttom_1_prop_UpdateShablonForm_16: \n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"newperiod\" SIZE=5 $ADISABLED VALUE=\"$AVALUE\"> $shablonbuttom_1_prop_UpdateShablonForm_17\n");
  
  print("<TR><TD><TD>$shablonbuttom_1_prop_UpdateShablonForm_18: \n");
  print("<BR><INPUT TYPE=\"TEXT\" NAME=\"clryear\" SIZE=4 $ADISABLED VALUE=\"$YCLRVALUE\">:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"clrmonth\" SIZE=2 $ADISABLED VALUE=\"$MCLRVALUE\">:\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"clrday\" SIZE=2 $ADISABLED VALUE=\"$DCLRVALUE\">\n");

  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$shablonbuttom_1_prop_UpdateShablonForm_7\">\n");
  print("</FORM>\n");

       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function RemoveTRange(id)\n");
       print("{\n");
       print("  var href=\"main.php?show=exe&function=removetrange2shablon&filename=shablonbuttom_1_prop.php&shablon_id=$SHABLONConf->s_shablon_id&id=\"+id; ");
//       print("  value=window.confirm(\"Remove time range?\n \" );\n");
       print("  value=window.confirm(\"Remove time range?\");\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=href;\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

  print("<P><B>Shablon Time Range: </B>\n");
  print("<TABLE>\n");
  print("<FORM NAME=\"REMOVETRANGE2shablon\" ACTION=\"main.php\">\n");
  $num_rows=$DB2->samsdb_query_value("SELECT sconfig_time.*, timerange.s_name, timerange.s_timestart, s_timeend FROM sconfig_time LEFT JOIN timerange ON sconfig_time.s_trange_id=timerange.s_trange_id WHERE sconfig_time.s_shablon_id='$id' ");
  if($num_rows>0)
	{
  	while($row2=$DB2->samsdb_fetch_array())
		{
		print("<TR><TD ALIGN=\"RIGHT\" > <B>$row2[s_name]</B> ($row2[s_timestart] - $row2[s_timeend] )");
//		print("<TD > <INPUT TYPE=\"CHECKBOX\" NAME=\"trange[$row2[s_trange_id]]\" CHECKED>");
		print("<INPUT TYPE=\"BUTTON\" value=\"Remove\" Onclick=RemoveTRange($row2[s_trange_id])>\n");
		}
	}
  print("</TABLE>\n");
  $DB2->free_samsdb_query();
  //$DB->free_samsdb_query();


  print("<P><B>ADD Time Range: </B>\n");
  print("<TABLE>\n");
  print("<FORM NAME=\"ADDTRANGE2shablon\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addtrange2shablon\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"shablonbuttom_1_prop.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$id\">\n");
  print("<TD><SELECT NAME=\"trange\" ID=\"trange\" >\n");
  $num_rows=$DB->samsdb_query_value("SELECT * FROM timerange ");
  while($row=$DB->samsdb_fetch_array())
	{
           print("<OPTION VALUE=$row[s_trange_id]> $row[s_name] ($row[s_timestart] - $row[s_timeend] )");
	}
  print("</SELECT>\n");
  $DB->free_samsdb_query();
  print("<INPUT TYPE=\"SUBMIT\" value=\"Add\">\n");
  print("</TABLE>\n");
  print("</FORM>\n");
  
}

function RemoveTRange2Shablon()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB(&$SAMSConf);
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $period="M";
  $clrdate="1980-01-01";
  if(isset($_GET["shablon_id"])) $shablon_id=$_GET["shablon_id"];
  if(isset($_GET["id"])) $id=$_GET["id"];
  
  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;
   
  echo "DELETE FROM sconfig_time WHERE  s_shablon_id='$shablon_id' .AND. s_trange_id='$id' <BR>";
  $DB->samsdb_query("DELETE FROM sconfig_time WHERE  s_shablon_id='$shablon_id' AND s_trange_id='$id' ");
//  UpdateLog("$SAMSConf->adminname","$shablonnew_AddShablon_1 $snick","01");

  print("<SCRIPT>\n");
  print("  parent.basefrm.location.href=\"main.php?show=exe&function=updateshablonform&filename=shablonbuttom_1_prop.php&id=$id\"; \n");
  print("</SCRIPT> \n");
}

function AddTRange2Shablon()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB(&$SAMSConf);
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["trange"])) $trange=$_GET["trange"];
  if(isset($_GET["id"])) $id=$_GET["id"];
  
  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;
  
  $DB->samsdb_query("INSERT INTO sconfig_time ( s_shablon_id, s_trange_id ) VALUES ( '$id', '$trange' ) ");
//  UpdateLog("$SAMSConf->adminname","$shablonnew_AddShablon_1 $snick","01");

  print("<SCRIPT>\n");
  print("  parent.basefrm.location.href=\"main.php?show=exe&function=updateshablonform&filename=shablonbuttom_1_prop.php&id=$id\"; \n");
  print("</SCRIPT> \n");
}



function shablonbuttom_1_prop()
{
  global $SAMSConf;
  global $SHABLONConf;
  global $USERConf;
  
  if(isset($_GET["id"])) $id=$_GET["id"];
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($USERConf->ToWebInterfaceAccess("C")==1 )
   {
       GraphButton("main.php?show=exe&function=updateshablonform&filename=shablonbuttom_1_prop.php&id=$SHABLONConf->s_shablon_id",
	               "basefrm","config_32.jpg","config_48.jpg","$shablonbuttom_1_prop_shablonbuttom_1_prop_1 '$SHABLONConf->s_name'");
    }
}

?>
