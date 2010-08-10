<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddUsersFromLDAP()
{
  require_once("ldap.php");

  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")!=1 )
	exit;

  if(isset($_GET["username"])) $userlist=$_GET["username"];
  if(isset($_GET["groupname"])) $usergroup=$_GET["groupname"];
  if(isset($_GET["usershablon"])) $usershablon=$_GET["usershablon"];
  if(isset($_GET["enabled"])) $enabled=$_GET["enabled"];

  if($enabled=="on")
     $enabled=1;
  else
     $enabled=-1;      

  $i=0;

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
	$query="INSERT INTO squiduser (s_group_id, s_shablon_id, s_nick, s_enabled, s_quote) VALUES('$usergroup', '$usershablon', '$user', '$enabled', '$s_quote')";
	$num_rows=$DB->samsdb_query($query);

     }

  print("<SCRIPT>\n");
  print(" parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");

}



function AddUsersFromLDAPForm()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["ldapgroup"])) $ldapgroup=$_GET["ldapgroup"];
  if(isset($_GET["getgroup"])) $getgroup=$_GET["getgroup"];
  if(isset($_GET["addgroupname"])) $addgroupname=$_GET["addgroupname"]; 
   
  if($USERConf->ToWebInterfaceAccess("UC")!=1 )
	exit;
  

  PageTop("user.jpg"," $usersbuttom_1_domain_AddUsersFromDomainForm_1 LDAP");
  

	$DB=new SAMSDB();

  	$adldserver=GetAuthParameter("ldap","ldapserver");
	$basedn=GetAuthParameter("ldap","basedn");
	$adadmin=GetAuthParameter("ldap","adadmin");
	$adadminpasswd=GetAuthParameter("ldap","adadminpasswd");
	$usersrdn=GetAuthParameter("ldap","usersrdn");
	$usersfilter=GetAuthParameter("ldap","usersfilter");
	$usernameattr=GetAuthParameter("ldap","usernameattr");
	$groupsrdn=GetAuthParameter("ldap","groupsrdn");
	$groupsfilter=GetAuthParameter("ldap","groupsfilter");

	include('ldap.php');
	$samsldap = new sams_ldap($adldserver, $basedn, $usersrdn, $usersfilter, $usernameattr, $groupsrdn, $groupsfilter, $adadmin, $adadminpasswd);


	if($samsldap != NULL)
	{
		print("<FORM NAME=\"SelectUsersGroup\" ACTION=\"main.php\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addusersfromldapform\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"authldapbuttom_2_usersadd.php\">\n");

		if($addgroupname=="_allgroups_" || $addgroupname=="")
			$a=$samsldap->GetUsersData();
		else
		{
			$a=$samsldap->GetUsersWithPrimaryGroupID($addgroupname);
			$b=$samsldap->GetUsersWithSecondaryGroupID($addgroupname);

			for($i=0;$i<$a['userscount'];$i++)
			{
				$user=$a['uid'][$i];
				$username=$a['name'][$i];

				print("<B>$user</B> ($username) <BR>\n");
			}

		}
		$groupinfo=$samsldap->GetGroupsData();

		$SELECTED="";
		if($addgroupname=="_allgroups_" || $addgroupname=="")
			$SELECTED="SELECTED";
		print("<TR><TD>$usersbuttom_1_adldap_AddUsersFromAdLDAPForm_1\n");
		print("<TD><SELECT NAME=\"addgroupname\">\n");
		print("<OPTION VALUE=\"_allgroups_\"> $usersbuttom_1_adldap_AddUsersFromAdLDAPForm_5 \n");
		for($i=0;$i<$groupinfo['groupscount'];$i++)
		{
			$groupname=$groupinfo['cn'][$i];
			$gid=$groupinfo['gidNumber'][$i];
			$SELECTED="";
			if ($groupname==$addgroupname)
				$SELECTED="SELECTED";
			print("<OPTION VALUE=\"$groupname\" $SELECTED> $groupname \n");
		}
		print("</SELECT>\n");
		print("</TABLE>\n");
		print("<INPUT TYPE=\"SUBMIT\" value=\"$usersbuttom_1_adldap_AddUsersFromAdLDAPForm_2\" >\n");
		print("<P>\n");
		print("</FORM>\n");



		print("<FORM NAME=\"AddDomainUsers\" ACTION=\"main.php\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"domain\" id=Show value=\"$domain\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addusersfromldap\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"authldapbuttom_2_usersadd.php\">\n");

		if(strlen($ldapgroup)>0&&$ldapgroup!="_allgroups_"&&$ldapgroup!="_gettxtinput_")
			printf("<B>$usersbuttom_1_adldap_AddUsersFromAdLDAPForm_4: $ldapgroup</B><BR>");
		else if(strlen($ldapgroup)>0&&$ldapgroup=="_gettxtinput_")
			printf("<B>$usersbuttom_1_adldap_AddUsersFromAdLDAPForm_4: $getgroup</B><BR>");
		else
			print("<BR><B>$usersbuttom_1_domain_AddUsersFromDomainForm_2</B><BR>");

		print("<SELECT NAME=\"username[]\" MULTIPLE>\n");
		for($i=0;$i<$a['userscount'];$i++)
		{
			$user=$a['uid'][$i];
			$username=$a['name'][$i];

			$num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE s_nick='$user'");
			if($num_rows==0)  
			{
					print("<OPTION VALUE=\"$user\"> <B>$user</B> ($username) \n");
			}
			$DB->free_samsdb_query();
		}

		for($i=0;$i<$b['userscount'];$i++)
		{
			$user=$b['uid'][$i];
			$username=$b['name'][$i];

			$num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE s_nick='$user'");
			if($num_rows==0)  
			{
					print("<OPTION VALUE=\"$user\"> <B>$user</B> ($username) \n");
			}
			$DB->free_samsdb_query();
		}
		print("</SELECT>\n");
		print("<P>" );
  

		print("<TABLE>\n");
  
		print("<TR><TD><P>\n");
		print("<TR><TD>\n");

		print("<TR><TD>\n");
		print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_3 \n");
		print("<TD>\n");
		print("<SELECT NAME=\"groupname\" ID=\"groupname\" SIZE=1 TABINDEX=30 >\n");

		$num_rows=$DB->samsdb_query_value("SELECT * FROM sgroup");
		while($row2=$DB->samsdb_fetch_array())
		{
			print("<OPTION VALUE=\"$row2[s_group_id]\"> $row2[s_name] \n");
		}
		$DB->free_samsdb_query();
		print("</SELECT>\n");

		print("<TR>\n");
		print("<TD>\n");
		print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_4 \n");
		print("<TD>\n");
		print("<SELECT NAME=\"usershablon\" ID=\"usershablon\" SIZE=1 TABINDEX=30 > \n");

		$num_rows=$DB->samsdb_query_value("SELECT s_shablon_id, s_name FROM shablon");
		while($row=$DB->samsdb_fetch_array())
		{
			print("<OPTION VALUE=$row[s_shablon_id]> $row[s_name]\n");
		}
		$DB->free_samsdb_query();
		print("</SELECT>");
		print("<TR><TD><B>$usersbuttom_1_domain_AddUsersFromDomainForm_6");
		print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"enabled\" CHECKED>");
		print("</TABLE>\n");

		print("<INPUT TYPE=\"SUBMIT\" value=\"$usersbuttom_1_domain_AddUsersFromDomainForm_5\">\n");




		print("</FORM>\n");


/*
    print("<FORM NAME=\"AddDomainUsers\" ACTION=\"main.php\">\n");

    print("<SCRIPT language=JAVASCRIPT>\n");
    print("function SelectADGroup(formname)\n");
    print("{\n");
    print("  var group=formname.addgroupname.value; \n");
    print("  var getgroup=formname.getgroup.value; \n");
    print("  var str=\"main.php?show=exe&ldapgroup=\"+group+\"&getgroup=\"+getgroup+\"&function=addusersfromldapform&filename=authldapbuttom_2_usersadd.php\"; \n");
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


  
  
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"domain\" id=Show value=\"$domain\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addusersfromldapap\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"authldapbuttom_2_usersadd.php\">\n");
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
*/


	}
exit(0);
}



function authldapbuttom_2_usersadd()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")==1 )
    {
       GraphButton("main.php?show=exe&function=addusersfromldapform&filename=authldapbuttom_2_usersadd.php","basefrm","domain-32.jpg","domain-48.jpg","$usersbuttom_1_domain_AddUsersFromDomainForm_1 LDAP");
	}

}

?>
