<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */


function AddUsersFromNTLM()
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

  $query="select s_quote from shablon where s_shablon_id='$usershablon'";
  $num_rows=$DB->samsdb_query_value($query);
  $row=$DB->samsdb_fetch_array();
  $s_quote=$row['s_quote'];
  $DB->free_samsdb_query();
  while(isset($userlist[$i])==TRUE)
     {
       $string=$userlist[$i];
       $i++;
       $user="$string";

//       print("user=$user domain=$domain enabled=$enabled usergroup=$usergroup shablon=$usershablon<BR>");
       
	$num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE s_nick='$user'");
       if($num_rows==0)
          {
		$num_rows=$DB->samsdb_query("INSERT INTO squiduser (s_group_id, s_shablon_id, s_nick, s_domain, s_enabled, s_quote) VALUES('$usergroup', '$usershablon', '$user', '$domain', '$enabled', '$s_quote')");

          }

     }
  print("<SCRIPT>\n");
  print(" parent.lframe.location.href=\"lframe.php\"; \n");
  print(" parent.tray.location.href=\"tray.php?show=exe&function=authntlmtray&filename=authntlmtray.php\"; \n");
  print("</SCRIPT> \n");

}



function AddUsersFromNTLMForm()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["ldapgroup"])) $ldapgroup=$_GET["ldapgroup"];
  if(isset($_GET["getgroup"])) $getgroup=$_GET["getgroup"];
   
  if($USERConf->ToWebInterfaceAccess("UC")!=1 )
	exit(0);  

  $DB=new SAMSDB();
  PageTop("user.jpg"," $usersbuttom_1_domain_AddUsersFromDomainForm_1 NTLM ");
  
  $ntlmserver=GetAuthParameter("ntlm","ntlmserver");
  $ntlmdomain=GetAuthParameter("ntlm","ntlmdomain");
  $ntlmadmin=GetAuthParameter("ntlm","ntlmadmin");
  $ntlmpasswd=GetAuthParameter("ntlm","ntlmadminpasswd");
  

    print("<FORM NAME=\"AddDomainUsers\" ACTION=\"main.php\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"domain\" id=Show value=\"$ntlmdomain\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addusersfromntlm\">\n");
    print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"authntlmbuttom_2_usersadd.php\">\n");
    print("<TABLE>\n");


    print("<TR><TD><B>$usersbuttom_1_domain_AddUsersFromDomainForm_2</B><TD>\n");
    print("<SELECT NAME=\"username[]\" MULTIPLE>\n");
    
    $users=ExecuteShellScript("getntlmusers","$LANG");
    $a=explode("|",$users);
    asort($a);
    $acount=count($a);

    foreach ($a as $user) 
	{
		$num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE s_nick='$user'");
		if($num_rows==0 && strlen($user)>0 )  
		{
			print("<OPTION VALUE=\"$user\"> <B>$user</B>");
		}
		$DB->free_samsdb_query();
	}
    print("</SELECT>\n");
    print("<P>" );


  
    print("<TR><TD><P>\n");
    print("<TR><TD>\n");
    print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_7 \n");
    print("<TD>\n");
    print("<INPUT TYPE=\"TEXT\" NAME=\"domainname\" id=Show VALUE=\"$ntlmdomain\">\n");

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



function authntlmbuttom_2_usersadd()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")==1 )
    {
       GraphButton("main.php?show=exe&function=addusersfromntlmform&filename=authntlmbuttom_2_usersadd.php","basefrm","domain-32.jpg","domain-48.jpg","$usersbuttom_1_domain_AddUsersFromDomainForm_1 Windows");
	}

}

?>
