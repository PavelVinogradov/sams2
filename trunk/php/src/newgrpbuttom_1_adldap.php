<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddGroupsFromAdLDAP()
{

  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
 
  if(isset($_GET["groupname"])) $usergroup=$_GET["groupname"];
  PageTop("user.jpg"," $newgroupbuttom_1_adldap_AddGroupsFromAdLDAP_1:");
       
  $i=0;
  $userlist=$usergroup;
  while(strlen($userlist[$i])>0)
     {
       print("$usergroup[$i]<BR>");
       $groupname=TempName();
       $result=mysql_query("INSERT INTO groups VALUES('3','$groupname','$usergroup[$i]','open') ");
	if($result!=FALSE)
                   UpdateLog("$SAMSConf->adminname","Added group  $usergroup[$i] ","02");
       $i++;
     }
  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");

}



function AddGroupsFromAdLDAPForm()
{
  
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  PageTop("user.jpg"," $newgroupbuttom_1_adldap_AddGroupsFromAdLDAPForm_1");
  
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
//authenticate a user
if ($adldap -> authenticate($SAMSConf->LDAPUSER,$SAMSConf->LDAPUSERPASSWD))
{
    echo ("Authenticated into AD as user $SAMSConf->LDAPUSER ok!<br><br>\n");

    print("<BR><B>$newgroupbuttom_1_adldap_AddGroupsFromAdLDAPForm_2</B>");
    print("<FORM NAME=\"AddDomainUsers\" ACTION=\"main.php\">\n");
    print("<SELECT NAME=\"groupname[]\" MULTIPLE>\n");
    //$domain="Biont";
    
    $info = $adldap->all_groups(true);
    for($i=1;$i<$info[0]*2+1;$i++)
      {
	$user=$info[$i];
        $i++;
	$username=$info[$i];
       if(strcmp($row['name'],$user)!=0&&strcmp($row['domain'],$domain)!=0)
          {
              print("<OPTION VALUE=\"$user\"> $user ");
          }
      }
  print("</SELECT>\n");
}
  print("<P>" );

  print("<INPUT TYPE=\"HIDDEN\" NAME=\"domain\" id=Show value=\"$domain\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addgroupsfromadldap\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"newgrpbuttom_1_adldap.php\">\n");
  
  print("<INPUT TYPE=\"SUBMIT\" value=\"$usersbuttom_1_domain_AddUsersFromDomainForm_5\">\n");
  print("</FORM>\n");
	  
	  
exit(0);
}



function newgrpbuttom_1_adldap()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->AUTH=="adld"&&$SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=addgroupsfromadldapform&filename=newgrpbuttom_1_adldap.php","basefrm","domain-32.jpg","domain-48.jpg","$newgroupbuttom_1_adldap_AddGroupsFromAdLDAPForm_1");
    }

}

?>
