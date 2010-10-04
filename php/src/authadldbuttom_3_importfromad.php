<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddGroup()
{
  global $SAMSConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")!=1 )
	exit(0);

  if(isset($_GET["groupnick"])) $groupnick=$_GET["groupnick"];

//  $groupname=TempName();

  $result=$DB->samsdb_query_value("SELECT s_name FROM sgroup where s_name = '$groupnick'");
//  $result=mysql_query("SELECT nick FROM groups where nick = '$groupnick';");
  if($result == 0) 
  {
    $result=$DB->samsdb_query("INSERT INTO sgroup (s_name) VALUES('$groupnick') ");
    $result=$DB->samsdb_query("INSERT INTO auth_param (s_auth, s_param, s_value) VALUES('adld', 'adldgroup', '$groupnick') ");
//    if($result!=FALSE)
//      UpdateLog("$SAMSConf->adminname","Added group  $groupnick ","02");

    print("<SCRIPT>\n");
    print("  parent.lframe.location.href=\"lframe.php\"; \n");
    print("  parent.tray.location.href=\"tray.php?show=usergrouptray&groupname=$groupname&groupnick=$groupnick\";\n");
    print("</SCRIPT> \n");
  } else {
    PageTop("usergroup_48.jpg","$newgroupbuttom_5_addgroup_newgrpbuttom_5_groupexist");
  }
}


function AddShablon()
{
  global $SAMSConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")!=1 )
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
  
  $QUERY="INSERT INTO shablon ( s_name, s_quote, s_auth, s_period, s_clrdate, s_alldenied ) VALUES ( '$snick', '$defaulttraf', '$auth', '$period', '$clrdate', '0' ) ";
echo "$QUERY<BR>";
  $DB->samsdb_query($QUERY);
  $DB->samsdb_query_value("SELECT s_shablon_id FROM shablon WHERE s_name='$snick' ");
  $row=$DB->samsdb_fetch_array();
  $sid=$row['s_shablon_id'];
  $DB->free_samsdb_query();
  $DB->samsdb_query("INSERT INTO sconfig_time ( s_shablon_id, s_trange_id ) VALUES ( '$sid', '$trange' ) ");
//  UpdateLog("$SAMSConf->adminname","$shablonnew_AddShablon_1 $snick","01");

  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");
}



function ImportFromAD()
{
  require_once("adldap.php");

  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")!=1 )
	exit(0);

echo "ImportFromAD<BR>";
 
  if(isset($_GET["addtemplates"])) $addtemplates=$_GET["addtemplates"];
  if(isset($_GET["addgroups"])) $addgroups=$_GET["addgroups"];
  if(isset($_GET["addgroupname"])) $addgroupname=$_GET["addgroupname"];

  if(isset($_GET["defaulttraf"])) $defaulttraf=$_GET["defaulttraf"];

  if(isset($_GET["period"])) $period=$_GET["period"];
  if(isset($_GET["newperiod"])) $newperiod=$_GET["newperiod"];
   
  if(isset($_GET["clryear"])) $clryear=$_GET["clryear"];
  if(isset($_GET["clrmonth"])) $clrmonth=$_GET["clrmonth"];
  if(isset($_GET["clrday"])) $clrday=$_GET["clrday"];
  if(isset($_GET["trange"])) $trange=$_GET["trange"];

  if(isset($_GET["enabled"])) $enabled=$_GET["enabled"];

	$addgroups="on";
	$addtemplates="on";

	if($enabled=="on")
		$enabled=1;
	else  
		$enabled=0;

	if($period=="A")
	{
		$period=$newperiod;
		$clrdate="$clryear-$clrmonth-$clrday";  
	}  

	$adldserver=GetAuthParameter("adld","adldserver");
	$basedn=GetAuthParameter("adld","basedn");
	$adadmin=GetAuthParameter("adld","adadmin");
	$adadminpasswd=GetAuthParameter("adld","adadminpasswd");
	$usergroup=GetAuthParameter("adld","usergroup");

	$LDAPBASEDN2=strtok($basedn,".");
	$LDAPBASEDN="DC=$LDAPBASEDN2";
	while(strlen($LDAPBASEDN2)>0)
	{
		$LDAPBASEDN2=strtok(".");
		if(strlen($LDAPBASEDN2)>0)
			$LDAPBASEDN="$LDAPBASEDN,DC=$LDAPBASEDN2";
	}

 	$pdc=array("$adldserver");
	$options=array(account_suffix=>"@$basedn", base_dn=>"$LDAPBASEDN",domain_controllers=>$pdc, 
	ad_username=>"$adadmin",ad_password=>"$adadminpasswd","","","");

	$ldap=new adLDAP($options);




	$i=0;
	while(strlen($addgroupname[$i])>0)
	{
echo "$i: $addgroupname[$i]<BR>";
		if($addtemplates=="on")
		{
echo " add template: $addgroupname[$i]<BR>";


			$result=$DB->samsdb_query_value("SELECT s_name FROM shablon where s_name = '$addgroupname[$i]'");
			if($result == 0) 
			{
				if($clrdate=="")
					$clrdate="1980-01-01";

				$DB->samsdb_query("INSERT INTO shablon ( s_name, s_quote, s_auth, s_period, s_clrdate, s_alldenied, 	s_shablon_id2 ) VALUES ( '$addgroupname[$i]', '$defaulttraf', 'adld', '$period', '$clrdate', '0', '-1' ) ");
				$DB->samsdb_query_value("SELECT s_shablon_id FROM shablon WHERE s_name='$addgroupname[$i]' ");
				$row=$DB->samsdb_fetch_array();
				$sid=$row['s_shablon_id'];
				$DB->free_samsdb_query();
				$DB->samsdb_query("INSERT INTO sconfig_time ( s_shablon_id, s_trange_id ) VALUES ( '$sid', '$trange' ) ");
				echo "create template $addgroupname[$i] "; 
			}

		}
		if($addgroups=="on")
		{
echo " add group: $addgroupname[$i]<BR>";
			$result=$DB->samsdb_query_value("SELECT s_name FROM sgroup where s_name = '$addgroupname[$i]'");
			if($result == 0) 
			{
				$result=$DB->samsdb_query("INSERT INTO sgroup (s_name) VALUES('$addgroupname[$i]') ");
				echo "create group $addgroupname[$i] "; 
				$result=$DB->samsdb_query("INSERT INTO auth_param (s_auth, s_param, s_value) VALUES('adld', 'adldgroup', '$addgroupname[$i]') ");
			}



//	$groupid=1;
//	$shablonid=1;

	$result=$DB->samsdb_query_value("SELECT s_name, s_group_id FROM sgroup where s_name = '$addgroupname[$i]'");
	$row=$DB->samsdb_fetch_array();
	$groupid=$row['s_group_id'];

	$result=$DB->samsdb_query_value("SELECT s_name, s_shablon_id FROM shablon where s_name = '$addgroupname[$i]'");
	$row=$DB->samsdb_fetch_array();
	$shablonid=$row['s_shablon_id'];

	$a=$ldap->all_users($include_desc = false, $search = "*", $sorted = true);
	$acount=count($a);
	foreach ($a as $user) 
	{
		$samaccountname = UTF8ToSAMSLang($user["samaccountname"]);
		$displayname = UTF8ToSAMSLang($user["displayname"]);
		$givenname = UTF8ToSAMSLang($user["givenname"]);
		$sn = UTF8ToSAMSLang($user["sn"]);
		$memberof = UTF8ToSAMSLang($user["memberof"]);
		$adldgroups=explode ( "|", $memberof );
		$cadldgroups=count($adldgroups);
		$memberofgroup="Users";
		for($j=0;$j<$cadldgroups;$j++)
		{
			$adldgroupname=explode ( "=", $adldgroups[$j] );
			if(strlen($adldgroupname[1])>3)
				$memberofgroup=$memberofgroup."|".substr($adldgroupname[1],0,strlen($adldgroupname[1])-3);
		}

		if(strstr($memberofgroup,"|".$addgroupname[$i]))
		{
echo " add user: $samaccountname ( $givenname $sn )<BR>";

			if($enabled=="")
				$enabled=1;

			$QUERY="INSERT INTO squiduser ( s_nick, s_domain, s_name, s_family, s_shablon_id, s_quote,  s_size, s_enabled, s_group_id, s_soname, s_ip, s_passwd, s_hit, s_autherrorc, s_autherrort ) VALUES ( '$samaccountname', '$basedn', '$givenname', '$sn', '$shablonid', '$defaulttraf',  '0', '$enabled', '$groupid', '$usersoname', '$userip', '$pass', '0', '0', '0') ";
			$DB->samsdb_query($QUERY);

		}


	}

		}
		print(" <BR>");
		$i++;
	}
	print("<SCRIPT>\n");
	print("  parent.lframe.location.href=\"lframe.php\"; \n");
	print("  parent.tray.location.href=\"tray.php?show=usergrouptray&groupname=$groupname&groupnick=$groupnick\";\n");
	print("</SCRIPT> \n");

}

function ImportFromADForm()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")!=1 )
	exit(0);  

  PageTop("importfromad-48.jpg"," $authadldbuttom_3_importfromad_ImportFromADForm_1 ");
  
	require_once("src/adldap.php");

	print("<FORM NAME=\"AddDomainUsers\" ACTION=\"main.php\">\n");
	$DB=new SAMSDB();

  	$adldserver=GetAuthParameter("adld","adldserver");
	$basedn=GetAuthParameter("adld","basedn");
	$adadmin=GetAuthParameter("adld","adadmin");
	$adadminpasswd=GetAuthParameter("adld","adadminpasswd");
	$usergroup=GetAuthParameter("adld","usergroup");

	$LDAPBASEDN2=strtok($basedn,".");
	$LDAPBASEDN="DC=$LDAPBASEDN2";
	while(strlen($LDAPBASEDN2)>0)
	{
		$LDAPBASEDN2=strtok(".");
		if(strlen($LDAPBASEDN2)>0)
			$LDAPBASEDN="$LDAPBASEDN,DC=$LDAPBASEDN2";
	}

 	$pdc=array("$adldserver");
	$options=array(account_suffix=>"@$basedn", base_dn=>"$LDAPBASEDN",domain_controllers=>$pdc, 
	ad_username=>"$adadmin",ad_password=>"$adadminpasswd","","","");

	$ldap=new adLDAP($options);

	$groupinfo=$ldap->all_groups($include_desc = false, $search = "*", $sorted = true);
	$gcount=count($groupinfo);

	print("<FORM NAME=\"AddFromAD\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"importfromad\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"authadldbuttom_3_importfromad.php\">\n"); 
/* */
	echo "<TABLE WIDTH=90%>";
	print("<TR><TD WIDTH=30%><B>$authadldbuttom_3_importfromad_ImportFromADForm_2:\n");
	print("<TD WIDTH=70%><SELECT NAME=\"addgroupname[]\" SIZE=15 TABINDEX=30 MULTIPLE>\n");

	asort($groupinfo);
	foreach ($groupinfo as $group) 
	{
		$groupname = UTF8ToSAMSLang($group);
		if(SearchAuthParameter("adld","adldgroup","$groupname")==0)
			print("<OPTION VALUE=\"$groupname\"> $groupname \n");
	}

	print("</SELECT>\n");

	print("<TR><TD><B>$usersbuttom_1_domain_AddUsersFromDomainForm_6");
	print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"enabled\" CHECKED>");

	print("<TR>\n");
	print("<TD>\n");
	print("$shablonnew_NewShablonForm_3:\n");
	print("<TD>\n");
	print("<INPUT TYPE=\"TEXT\" NAME=\"defaulttraf\" SIZE=6 VALUE=\"100\"> <B> 0 - unlimited traffic\n" );

	print("<TR>\n");
	print("<TD>\n");
	print("$shablonnew_NewShablonForm_10\n");
	print("<TD>\n");
	print("<SELECT NAME=\"period\" onchange=EnterPeriod(AddDomainUsers)  $CCLEAN> \n");
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
	print("<TD> $shablonnew_NewShablonForm_14: \n");
	print("<INPUT TYPE=\"TEXT\" NAME=\"newperiod\" SIZE=5 DISABLED>$shablonnew_NewShablonForm_15\n");
	print("<TR><TD><TD> $shablonnew_NewShablonForm_16: \n");
	print("<BR><INPUT TYPE=\"TEXT\" NAME=\"clryear\" SIZE=4 DISABLED VALUE=\"$YCLRVALUE\">:\n");
	print("<INPUT TYPE=\"TEXT\" NAME=\"clrmonth\" SIZE=2 DISABLED VALUE=\"$MCLRVALUE\">:\n");
	print("<INPUT TYPE=\"TEXT\" NAME=\"clrday\" SIZE=2 DISABLED VALUE=\"$DCLRVALUE\">\n");

	print("<TR><TD>$AddTRangeForm_trangetray_1:<TD><SELECT NAME=\"trange\" ID=\"trange\" >\n");
	$num_rows=$DB->samsdb_query_value("SELECT * FROM timerange ");
	while($row=$DB->samsdb_fetch_array())
	{
           print("<OPTION VALUE=$row[s_trange_id]> $row[s_name] ($row[s_timestart] - $row[s_timeend] )");
	
	}
	print("</SELECT>\n");



/*
	print("<TR><TD WIDTH=30%><B>Create SAMS groups with AD groups name:\n");
	print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"addgroups\" CHECKED>");
*/
	echo "</TABLE>";
	print("<INPUT TYPE=\"SUBMIT\" value=\"Import\">\n");
	print("</FORM>\n");

}



function authadldbuttom_3_importfromad()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")==1 )
    {
       GraphButton("main.php?show=exe&function=importfromadform&filename=authadldbuttom_3_importfromad.php","basefrm","importfromad-32.jpg","importfromad-48.jpg","$authadldbuttom_3_importfromad_ImportFromADForm_1");
	}

}

?>
