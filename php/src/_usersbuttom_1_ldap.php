<?
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddUsersFromLDAP()
{

  global $NTLMDOMAIN;
  global $adminname;
  global $LANG;
  $lang="./lang/lang.$LANG";
  require($lang);

    $access=UserAccess();
   if($access!=2)     {       exit;     }

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
 
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

//  print("userlist=$userlist usergroup=$usergroup  usershablon=$usershablon<BR>");
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

       $result=mysql_query("SELECT * FROM squidusers WHERE domain=\"$domain\"&&nick=\"$user\" ");
       $row=mysql_fetch_array($result);
       //print("result = $result row2=$row2 db: $row[nick]/$row[domain]<BR>");

      
       if(strcmp($row['name'],$user)!=0&&strcmp($row['domain'],$domain)!=0)
          {
             $userid=TempName();
			 $result=mysql_query("INSERT INTO squidusers SET
			 id=\"$userid\",nick=\"$user\",domain=\"$domain\",name=\"\",
			 family=\"\",shablon=\"$usershablon\" ,quotes=\"$traffic\",size=\"0\",
			 enabled=\"$enabled\",squidusers.group=\"$usergroup\",squidusers.soname=\"\",
			 squidusers.ip=\"\",squidusers.ipmask=\"\",squidusers.passwd=\"none\" ");
            UpdateLog("$adminname","$usersbuttom_1_domain_AddUsersFromDomain_1 $user $usersbuttom_1_domain_AddUsersFromDomain_1 $domain","01");
          }

     }
  print("<SCRIPT>\n");
  print(" parent.tray.location.href=\"tray.php?show=exe&function=userstray\";\n");
  print(" parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");

}



function AddUsersFromLDAPForm()
{
  
  global $NTLMDOMAIN;
  global $WBINFOPATH;
  global $LANG;
  global $SAMSConf;
  $lang="./lang/lang.$LANG";
  require($lang);

   $access=UserAccess();
   if($access!=2)     {       exit;     }
    
   PageTop("user.jpg"," $usersbuttom_1_domain_AddUsersFromDomainForm_1 LDAP");

  $server = "127.0.0.1"; // имя контролера домена
  $ldapuser = "admin";  // существующий пользователь (если запрещен анонимный  просмотр)
  $ldapuserpassword = "ldappasswd";
  $ou = "Users"; // название корневой папки

  $ds=ldap_connect($server);
  print("LDAP connect = $ds<BR>");
  
  if($ds) 
    {
//       $r=ldap_bind($ds,$ldapuser,$ldapuserpassword);
       $r=ldap_bind($ds);
       print("LDAP bind = $r<BR>");
 
       echo "Searching for (sn=S*) ...";
       // Search surname entry
       $sr=ldap_search($ds,"dc=biont, dc=ru", "uid=*");  
       echo "Search result is ".$sr."<p>";
       echo "Number of entires returned is ".ldap_count_entries($ds,$sr)."<p>";

    echo "Getting entries ...<p>";
    $info = ldap_get_entries($ds, $sr);
    $userscount= $info["count"];
    echo "Data for  $userscount items returned:<p>";

    for ($i=0; $i<$userscount; $i++) 
        {
          //echo "dn is: ". $info[$i]["dn"] ."<br>";
          $username=$info[$i]["dn"];
	  if(strstr($username,"uid"))
	    {
		  $uid1=strtok($username,"uid=");
		  $uid=strtok($uid1,",ou");
	    	  echo "User $i: $username -$uid1-$uid-<br>";
	    }  
	  //echo "first cn entry is: ". $info[$i]["cn"][0] ."<br>";
          //echo "first email entry is: ". $info[$i]["mail"][0] ."<p>";
        }
       
       
       ldap_close($ds);
    } 
  else 
    {
      echo "SAMS: Unable to connect to LDAP server"; 
    }
	  
exit(0);
  
  
}



function usersbuttom_1_ldap()
{
  global $access;
  global $AUTH;
  global $adminname;
  global $USERACCESS;
  global $URLACCESS;
  global $domainusername;
  global $LANG;
  $lang="./lang/lang.$LANG";
  require($lang);

   if($AUTH=="ntlm"&&$access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=addusersfromldapform&filename=usersbuttom_1_ldap.php","basefrm","domain-32.jpg","domain-48.jpg","$usersbuttom_1_domain_usersbuttom_1_domain_1");
	}

}

?>
