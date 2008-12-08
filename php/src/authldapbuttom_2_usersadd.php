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
  $DB=new SAMSDB(&$SAMSConf);
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
 
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
echo "AddUsersFromLDAP";

  while(strlen($userlist[$i])>0)
     {
       print("userlist=$userlist[$i] enabled=$enabled  '$usergroup', '$usershablon', '$domain'<BR>");

       $string=$userlist[$i];
       $i++;
       $user="$string";

	$num_rows=$DB->samsdb_query("INSERT INTO squiduser (s_group_id, s_shablon_id, s_nick, s_enabled) VALUES('$usergroup', '$usershablon', '$user', '$enabled')");


     }

  print("<SCRIPT>\n");
  print(" parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");

}



function AddUsersFromLDAPForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["ldapgroup"])) $ldapgroup=$_GET["ldapgroup"];
  if(isset($_GET["getgroup"])) $getgroup=$_GET["getgroup"];
  if(isset($_GET["addgroupname"])) $addgroupname=$_GET["addgroupname"]; 
   
  $SAMSConf->access=UserAccess();
  if($SAMSConf->access!=2)     {       exit;     }
  

  PageTop("user.jpg"," $usersbuttom_1_domain_AddUsersFromDomainForm_1 LDAP $addgroupname");
  

	$DB=new SAMSDB(&$SAMSConf);

  	$adldserver=GetAuthParameter("ldap","ldapserver");
	$basedn=GetAuthParameter("ldap","basedn");
	$adadmin=GetAuthParameter("ldap","adadmin");
	$adadminpasswd=GetAuthParameter("ldap","adadminpasswd");
	$usergroup=GetAuthParameter("ldap","usergroup");

	include('ldap.php');
	$samsldap = new sams_ldap($adldserver, $basedn, $usergroup, $adadmin, $adadminpasswd);


	if($samsldap != NULL)
	{


/*
		if(strlen($ldapgroup)>0&&$ldapgroup!="_allgroups_"&&$ldapgroup!="_gettxtinput_")
		{
			$a=$samsldap->GetGroupsData();
//			$a=$ldap->group_users($ldapgroup);
			$acount=count($a);
		}
		else if(strlen($ldapgroup)>0&&$ldapgroup=="_gettxtinput_")
		{
			$a=$samsldap->GetGroupsData();
//			$a=$ldap->group_users($ldapgroup);
			$acount=count($a);
		}
		else
		{
			$a=$samsldap->GetUsersData();
//			$a=$ldap->group_users($ldapgroup);
			$acount=count($a);
		}
*/

/*
		$userdata=$samsldap->GetUsersData();
		for($j=0;$j<$userdata['userscount'];$j++)
		{
			echo "$j: - ".$userdata['userid'][$j]." - ".$userdata['cn'][$j]." - ".$userdata['uid'][$j]."<BR>";
		}
*/
/*
		$userdata=$samsldap->GetUsersFromGroupID(500);
		for($j=0;$j<$userdata['userscount'];$j++)
		{
			echo "$j: - ".$userdata['userid'][$j]." - ".$userdata['cn'][$j]." - ".$userdata['uid'][$j]."<BR>";
		}
*/


/*
		$groupdata=$samsldap->GetGroupsData();
		for($j=0;$j<$groupdata['groupscount'];$j++)
		{
			echo "$j: - ".$groupdata['dn'][$j]." - ".$groupdata['cn'][$j]." - ".$groupdata['gidNumber'][$j]."<BR>";
		}
		echo "</TABLE>";
*/

		if($addgroupname=="_allgroups_" || $addgroupname=="")
			$a=$samsldap->GetUsersData();
		else
			$a=$samsldap->GetUsersFromGroupID(500);
		$groupinfo=$samsldap->GetGroupsData();

		print("<FORM NAME=\"SelectUsersGroup\" ACTION=\"main.php\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addusersfromldapform\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"authldapbuttom_2_usersadd.php\">\n");
	        print("<TABLE CLASS=samstable>");
        	print("<TH width=5%>No");
        	print("<TH >LDAP users");
        	print("<TH >");
		print("<TR><TD>$usersbuttom_1_adldap_AddUsersFromAdLDAPForm_1\n");
		print("<TD><SELECT NAME=\"addgroupname\">\n");
		print("<OPTION VALUE=\"_allgroups_\"> $usersbuttom_1_adldap_AddUsersFromAdLDAPForm_5 \n");
		for($i=0;$i<$groupinfo['groupscount'];$i++)
		{
			$groupname=$groupinfo['cn'][$i];
			$gid=$groupinfo['gidNumber'][$i];
			print("<OPTION VALUE=\"$gid\"> $groupname \n");
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
			$username=$a['cn'][$i];

			$num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE s_nick='$user'");
			if($num_rows==0)  
			{
				print("<OPTION VALUE=\"$user\"> <B>$username</B> ($user) \n");
			}
			$DB->free_samsdb_query();
		}
		print("</SELECT>\n");
		print("<P>" );
  

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
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "C")==1)
    {
       GraphButton("main.php?show=exe&function=addusersfromldapform&filename=authldapbuttom_2_usersadd.php","basefrm","domain-32.jpg","domain-48.jpg","$usersbuttom_1_domain_usersbuttom_1_domain_1 LDAP");
	}

}

?>
