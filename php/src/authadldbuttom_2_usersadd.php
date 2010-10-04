<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */


function AddUsersFromAdLDAP()
{
  require_once("adldap.php");

  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")!=1 )
	exit(0);
 
  if(isset($_GET["domainname"])) $domainname=$_GET["domainname"];
  if(isset($_GET["username"])) $userlist=$_GET["username"];
  if(isset($_GET["groupname"])) $usergroup=$_GET["groupname"];
  if(isset($_GET["usershablon"])) $usershablon=$_GET["usershablon"];
  if(isset($_GET["enabled"])) $enabled=$_GET["enabled"];
  if(isset($_GET["domain"])) $domain=$_GET["domain"];

  if($enabled=="on")
     $enabled=1;
  else
     $enabled=-1;      
  if(strlen($domainname)>1)
       $domain=$domainname;    

  $i=0;



  	$adldserver=GetAuthParameter("adld","adldserver");
	$basedn=GetAuthParameter("adld","basedn");
	$adadmin=GetAuthParameter("adld","adadmin");
	$adadminpasswd=GetAuthParameter("adld","adadminpasswd");
	$adldusergroup=GetAuthParameter("adld","usergroup");

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


  $query="select s_quote from shablon where s_shablon_id='$usershablon'";
  $num_rows=$DB->samsdb_query_value($query);
  $row=$DB->samsdb_fetch_array();
  $s_quote=$row['s_quote'];
  $DB->free_samsdb_query();

  while(strlen($userlist[$i])>0)
     {
       $string=$userlist[$i];
       $i++;
       $user="$string";
	$username = UTF8ToSAMSLang($user);
	$num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE s_nick='$user'");
       if($num_rows==0)
          {
 		$userinfo=$ldap->user_info( $user, $fields=NULL);
		$givenname = $userinfo[0]["givenname"][0];
		$sn = $userinfo[0]["sn"][0];

		$QUERY="INSERT INTO squiduser (s_group_id, s_shablon_id, s_nick, s_domain, s_enabled, s_quote, s_family, s_name) VALUES('$usergroup', '$usershablon', '$username', '$domain', '$enabled', '$s_quote', '$sn', '$givenname')";
		$num_rows=$DB->samsdb_query($QUERY);

          }

     }

  print("<SCRIPT>\n");
  print(" parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");

}



function AddUsersFromADLDForm()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["ldapgroup"])) $ldapgroup=$_GET["ldapgroup"];
  if(isset($_GET["getgroup"])) $getgroup=$_GET["getgroup"];
   
  if($USERConf->ToWebInterfaceAccess("UC")!=1 )
	exit(0);  

  PageTop("user.jpg"," $usersbuttom_1_domain_AddUsersFromDomainForm_1 Active Directory ");
  
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

	$usersgroupname="Users";
	$a=$ldap->all_users($include_desc = false, $search = "*", $sorted = true);
	$acount=count($a);
	if(strlen($ldapgroup)>0&&$ldapgroup!="_allgroups_"&&$ldapgroup!="_gettxtinput_")
	{
		$usersgroupname=$ldapgroup;
	}
	else if(strlen($ldapgroup)>0&&$ldapgroup=="_gettxtinput_")
	{
		$usersgroupname=$getgroup;
	}

    $groupinfo=$ldap->all_groups($include_desc = false, $search = "*", $sorted = true);
    $gcount=count($groupinfo);





    print("<FORM NAME=\"AddDomainUsers\" ACTION=\"main.php\">\n");

/* */
    print("<SCRIPT language=JAVASCRIPT>\n");
    print("function SelectADGroup(formname)\n");
    print("{\n");
    print("  var group=formname.addgroupname.value; \n");
    print("  var getgroup=formname.getgroup.value; \n");
    print("  var str=\"main.php?show=exe&ldapgroup=\"+group+\"&getgroup=\"+getgroup+\"&function=addusersfromadldform&filename=authadldbuttom_2_usersadd.php\"; \n");
    print("  parent.basefrm.location.href=str;\n");
    print("}\n");
    print("function EnableTxtInput(formname)\n");
    print("{\n");
    print("  value=document.forms[\"AddDomainUsers\"].elements[\"addgroupname\"].value;\n");
    print("  if(value==\"_gettxtinput_\") \n");
    print("     {\n");
     print("       document.forms[\"AddDomainUsers\"].elements[\"getgroup\"].disabled=false\n");
    print("     }\n");
    print("  else \n");
    print("     {\n");
     print("       document.forms[\"AddDomainUsers\"].elements[\"getgroup\"].disabled=true\n");
    print("     }\n");
    print("}\n");
    print("</SCRIPT> \n");
    print("<TABLE WIDTH=90%>\n");
    print("<TR><TD WIDTH=40%>$usersbuttom_1_adldap_AddUsersFromAdLDAPForm_1</TD>\n");
    print("<TD WIDTH=60%><SELECT NAME=\"addgroupname\" onChange=EnableTxtInput(AddDomainUsers)>\n");
    print("<OPTION VALUE=\"_allgroups_\" SELECT  onselect=EnableTxtInput(AddDomainUsers)> $usersbuttom_1_adldap_AddUsersFromAdLDAPForm_5");
    print("<OPTION VALUE=\"_gettxtinput_\" onselect=EnableTxtInput(AddDomainUsers)> $usersbuttom_1_adldap_AddUsersFromAdLDAPForm_6");
    foreach ($groupinfo as $group) 
	{
		$groupname = UTF8ToSAMSLang($group);
        	print("<OPTION VALUE=\"$groupname\"  onselect=EnableTxtInput(AddDomainUsers)> $groupname");
	}
/*
    for($i=0;$i<$gcount;$i++)
      {
	$groupname = UTF8ToSAMSLang($groupinfo[$i]);
        print("<OPTION VALUE=\"$groupname\"  onselect=EnableTxtInput(AddDomainUsers)> $groupname");
      }
*/
    print("</SELECT>\n");
    print("<TR><TD WIDTH=40%>$usersbuttom_1_adldap_AddUsersFromAdLDAPForm_7\n");
    print("<TD WIDTH=60%><INPUT TYPE=\"TEST\" NAME=\"getgroup\" SIZE=\"20\" DISABLED>\n");
    print("</TABLE>\n");
    print("<INPUT TYPE=\"BUTTON\" value=\"$usersbuttom_1_adldap_AddUsersFromAdLDAPForm_2\" onclick=SelectADGroup(AddDomainUsers)>\n");
    print("<P>\n");
/* */    

    
    if(strlen($ldapgroup)>0&&$ldapgroup!="_allgroups_"&&$ldapgroup!="_gettxtinput_")
      printf("<B>$usersbuttom_1_adldap_AddUsersFromAdLDAPForm_4: <FONT COLOR=BLUE>$ldapgroup</FONT></B><BR>");
    else if(strlen($ldapgroup)>0&&$ldapgroup=="_gettxtinput_")
      printf("<B>$usersbuttom_1_adldap_AddUsersFromAdLDAPForm_4: <FONT COLOR=BLUE>$getgroup</FONT></B><BR>");
    else
      print("<BR><B>$usersbuttom_1_domain_AddUsersFromDomainForm_2</B><BR>");
    print("<SELECT NAME=\"username[]\" MULTIPLE>\n");
    
    asort($a);
    foreach ($a as $user) 
	{
		$samaccountname = UTF8ToSAMSLang($user["samaccountname"]);
		$num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE s_nick='$samaccountname'");
		if($num_rows==0)  
		{
			$displayname = UTF8ToSAMSLang($user["displayname"]);
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
			if(strstr($memberofgroup,$usersgroupname))
				print("<OPTION VALUE=\"$samaccountname\"> $samaccountname ($displayname)\n");
		}
		$DB->free_samsdb_query();
	}

    print("</SELECT>\n");
    print("<P>" );

  
    print("<P>" );
  
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"domain\" id=Show value=\"$domain\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addusersfromadldap\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"authadldbuttom_2_usersadd.php\">\n");
    print("<TABLE>\n");
  
    print("<TR><TD><P>\n");
    print("<TR><TD>\n");
    print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_7\n");
    print("<TD>\n");
    print("<INPUT TYPE=\"TEXT\" NAME=\"domainname\" id=Show VALUE=\"$basedn\">\n");

    print("<TR><TD>\n");
    print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_3 \n");
    print("<TD>\n");
    print("<SELECT NAME=\"groupname\" ID=\"groupname\" SIZE=1 TABINDEX=30 >\n");

    $num_rows=$DB->samsdb_query_value("SELECT * FROM sgroup");
    while($row2=$DB->samsdb_fetch_array())
      {
       print("<OPTION VALUE=\"$row2[s_group_id]\"> $row2[s_name] ");
      }
    $DB->free_samsdb_query();
    print("</SELECT>\n");

    print("<TR>\n");
    print("<TD>\n");
    print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_4 \n");
    print("<TD>\n");
    print("<SELECT NAME=\"usershablon\" ID=\"usershablon\" SIZE=1 TABINDEX=30 >\n");

    $num_rows=$DB->samsdb_query_value("SELECT s_shablon_id, s_name FROM shablon");
    while($row=$DB->samsdb_fetch_array())
      {
       print("<OPTION VALUE=$row[s_shablon_id]> $row[s_name]");
      }
    $DB->free_samsdb_query();
    print("</SELECT>");
    print("<TR><TD><B>$usersbuttom_1_domain_AddUsersFromDomainForm_6");
    print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"enabled\" CHECKED>");
    print("</TABLE>\n");

    print("<INPUT TYPE=\"SUBMIT\" value=\"$usersbuttom_1_domain_AddUsersFromDomainForm_5\">\n");
    print("</FORM>\n");



exit(0);
}



function authadldbuttom_2_usersadd()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")==1 )
    {
       GraphButton("main.php?show=exe&function=addusersfromadldform&filename=authadldbuttom_2_usersadd.php","basefrm","domain-32.jpg","domain-48.jpg","$usersbuttom_1_domain_AddUsersFromDomainForm_1 Active Directory");
	}

}

?>
