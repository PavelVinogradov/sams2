<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddUsersFromAdLDAP()
{

  global $SAMSConf;
  
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
//  if(isset($_GET["domainname"])) $domainname=$_GET["domainname"];

  if($enabled=="on")
     $enabled=1;
  else
     $enabled=-1;      
  if(strlen($domainname)>1)
       $domain=$domainname;    

  //print("userlist=$userlist usergroup=$usergroup  usershablon=$usershablon<BR>");
  $i=0;

  $result=mysql_query("SELECT traffic FROM shablons WHERE name=\"$usershablon\" ");
  $row=mysql_fetch_array($result);
  $straf=$row[0];
  while(strlen($userlist[$i])>0)
     {
       print("userlist=$userlist[$i] enabled=$enabled<BR>");

       $string=$userlist[$i];
       $i++;
      $user=$string;

       print("user=$user domain=$domain enabled=$enabled<BR>");
       
       $result2=mysql_query("SELECT * FROM shablons WHERE name=\"$usershablon\" ");
       $row2=mysql_fetch_array($result2);
       $traffic=$row2['traffic'];

       //$result=mysql_query("SELECT * FROM squidusers WHERE domain=\"$domain\"&&nick=\"$user\" ");
       $result=mysql_query("SELECT * FROM squidusers WHERE nick=\"$user\" ");
       $row=mysql_fetch_array($result);
       //print("result = $result row2=$row2 db: $row[nick]/$row[domain]<BR>");

      
       //if(strcmp($row['name'],$user)!=0&&strcmp($row['domain'],$domain)!=0)
       if(strcmp($row['name'],$user)!=0)
          {
             $userid=TempName();
		$result=mysql_query("INSERT INTO squidusers SET
			 id=\"$userid\",nick=\"$user\",domain=\"$domain\",name=\"\",
			 family=\"\",shablon=\"$usershablon\" ,quotes=\"$traffic\",size=\"0\",
			 enabled=\"$enabled\",squidusers.group=\"$usergroup\",squidusers.soname=\"\",
			 squidusers.ip=\"\",squidusers.ipmask=\"\",squidusers.passwd=\"none\",hit=\"0\",
			 squidusers.autherrorc=\"0\", squidusers.autherrort=\"0\" ");
		if($result!=FALSE)
                   UpdateLog("$SAMSConf->adminname","Added user $user ","01");
          }

     }
  print("<SCRIPT>\n");
  print(" parent.tray.location.href=\"tray.php?show=exe&function=userstray\";\n");
  print(" parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");

}



function AddUsersFromAdLDAPForm()
{
//(memberOf= CN= Internet ,CN=Users,DC= your ,DC= domain )   
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["ldapgroup"])) $ldapgroup=$_GET["ldapgroup"];
   
  $SAMSConf->access=UserAccess();
  if($SAMSConf->access!=2)     {       exit;     }
  
  db_connect($SAMSConf->SQUIDCTRLDATABASE) or exit();
    mysql_select_db($SAMSConf->SQUIDCTRLDATABASE);

  PageTop("user.jpg"," $usersbuttom_1_domain_AddUsersFromDomainForm_1 Active Directory ");
  
  require_once("adldap.php");

//create the LDAP connection
 $adldap = new adLDAP();
 if($adldap==NULL)
   {
      print("Connection not created");
      exit(0);
   }
 else
   {
      print("Connection to AD server created <BR>");
   }  

 echo ("Authenticated into AD as user $SAMSConf->LDAPUSER... \n");

//authenticate a user
 if ($adldap -> authenticate($SAMSConf->LDAPUSER,$SAMSConf->LDAPUSERPASSWD))
  {
    echo (" OK!<br><br>\n");
    if(strlen($ldapgroup)>0&&$ldapgroup!="_allgroups_")
      {
        $adldap->_GROUP="$ldapgroup";
        $info = $adldap->group_users(true);
      }
    else  
      $info = $adldap->all_users(true);
//$rrr=$groupinfo[0]*2+1;
//echo "groupinfo=$rrr";    
    $groupinfo=$adldap->all_groups(true);
    
    print("<FORM NAME=\"AddDomainUsers\" ACTION=\"main.php\">\n");
    
    print("<SCRIPT language=JAVASCRIPT>\n");
    print("function SelectADGroup(formname)\n");
    print("{\n");
    print("  var group=formname.addgroupname.value; \n");
    print("  var str=\"main.php?show=exe&ldapgroup=\"+group+\"&function=addusersfromadldapform&filename=usersbuttom_1_adldap.php\"; \n");
    //print("                 window.confirm(str);\n");
    print("  parent.basefrm.location.href=str;\n");
    print("}\n");
    print("</SCRIPT> \n");
    print("<TABLE>\n");
    print("<TR><TD>������� ������ ������:\n");
    print("<TD><SELECT NAME=\"addgroupname\">\n");
    print("<OPTION VALUE=\"_allgroups_\" SELECT> ALL GROUPS");
    for($i=1;$i<$groupinfo[0]*2+1;$i++)
      {
	$groupname=$groupinfo[$i];
        print("<OPTION VALUE=\"$groupname\"> $groupname");
        $i++;
      }
    print("</SELECT>\n");
    print("</TABLE>\n");
    print("<INPUT TYPE=\"BUTTON\" value=\"������� ������������� ������\" onclick=SelectADGroup(AddDomainUsers)>\n");
    print("<P>\n");
    
    
    if(strlen($ldapgroup)>0&&$ldapgroup!="_allgroups_")
      printf("<B>������� ������������ ������ Active Directory: $ldapgroup</B><BR>");
    else
      print("<BR><B>$usersbuttom_1_domain_AddUsersFromDomainForm_2</B>");
    print("<SELECT NAME=\"username[]\" MULTIPLE>\n");
    
    for($i=1;$i<$info[0]*2+1;$i++)
      {
	$user=$info[$i];
        $i++;
	$username=$info[$i];
        $result=mysql_query("SELECT * FROM squidusers WHERE nick=\"$user\" ");
        if(mysql_num_rows($result)==0)  
	  {
              print("<OPTION VALUE=\"$user\"> $user ($username)");
          }
      }
    print("</SELECT>\n");
    print("<P>" );

  
    print("<P>" );
  
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"domain\" id=Show value=\"$domain\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addusersfromadldap\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_1_adldap.php\">\n");
    print("<TABLE>\n");
  
    print("<TR><TD><P>\n");
    print("<TR><TD>\n");
    print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_7\n");
    print("<TD>\n");
    print("<INPUT TYPE=\"TEXT\" NAME=\"domainname\" id=Show VALUE=\"$SAMSConf->DEFAULTDOMAIN\">\n");

    print("<TR><TD>\n");
    print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_3 \n");
    print("<TD>\n");
    print("<SELECT NAME=\"groupname\" ID=\"groupname\" SIZE=1 TABINDEX=30 >\n");
    $result2=mysql_query("SELECT name,nick FROM groups");
    while($row2=mysql_fetch_array($result2))
      {
       print("<OPTION VALUE=$row2[name]> $row2[nick]");
      }
    print("</SELECT>\n");

    print("<TR>\n");
    print("<TD>\n");
    print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_4 \n");
    print("<TD>\n");
    print("<SELECT NAME=\"usershablon\" ID=\"usershablon\" SIZE=1 TABINDEX=30 >\n");
    db_connect($SAMSConf->SQUIDCTRLDATABASE) or exit();
    mysql_select_db($SAMSConf->SQUIDCTRLDATABASE);
    $result=mysql_query("SELECT * FROM shablons");
    while($row=mysql_fetch_array($result))
      {
       print("<OPTION VALUE=$row[name]> $row[nick]");
      }
    print("</SELECT>");
    print("<TR><TD><B>$usersbuttom_1_domain_AddUsersFromDomainForm_6");
    print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"enabled\" CHECKED>");
    print("</TABLE>\n");

    print("<INPUT TYPE=\"SUBMIT\" value=\"$usersbuttom_1_domain_AddUsersFromDomainForm_5\">\n");
    print("</FORM>\n");
  }
else
  {
        echo (" <FONT COLOR=\"RED\">ERROR!<br><br></FONT>\n");

  }

	  
	  
exit(0);
}



function usersbuttom_1_adldap()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->AUTH=="adld"&&$SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=addusersfromadldapform&filename=usersbuttom_1_adldap.php","basefrm","domain-32.jpg","domain-48.jpg","$usersbuttom_1_domain_usersbuttom_1_domain_1 Active Directory");
	}

}

?>
