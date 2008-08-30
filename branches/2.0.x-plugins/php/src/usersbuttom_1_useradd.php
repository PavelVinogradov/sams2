<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */


function GetDomainUsersList()
{
  global $SAMSConf;
   
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  if($SAMSConf->AUTH=="ntlm")
     {
        $value=ExecuteShellScript("getwbinfousers","$SAMSConf->WBINFOPATH/");
	$a=explode(" ",$value);
        sort($a);
	$acount=count($a);
     }
  else
      {
         require_once("adldap.php");
         //create the LDAP connection
 	  $pdc=array("$SAMSConf->LDAPSERVER");
	  $options=array(account_suffix=>"@$SAMSConf->LDAPDOMAIN", base_dn=>"$SAMSConf->LDAPBASEDN",domain_controllers=>$pdc, 
	  ad_username=>"$SAMSConf->LDAPUSER",ad_password=>"$SAMSConf->LDAPUSERPASSWD","","","");

	  $ldap=new adLDAP($options);
	  $a=$ldap->all_users($include_desc = false, $search = "*", $sorted = true);
          sort($a);
	  $acount=count($a);
      }
	      
  print("<SELECT NAME=\"usernick\" ID=\"usernick\" SIZE=1 >\n");
  for($i=0;$i<$acount;$i++)
     {
         if($SAMSConf->NTLMDOMAIN=="Y")
	   {
		if(strstr($a[$i],"+")!=NULL)
		  {
			$domain=trim(strtok($a[$i],"+"));
           		$user=trim(strtok("+"));
           		$domainlen=strlen($domain);
           		$userlen=strlen($user);
		  }
		else
		  {
			$domain=trim(strtok($a[$i],"\\"));
           		$user=trim(strtok("\\"));
           		$domainlen=strlen($domain);
           		$userlen=strlen($user);
		  }

           if(strlen($domain)==0||strlen($user)==0)
             {
	       $user=$domain;
	       $domain=$SAMSConf->DEFAULTDOMAIN;
	     }
	   //$domain=strtolower($domain);
         }
      else
	 {
           $domain="$SAMSConf->DEFAULTDOMAIN";
           $user=trim($a[$i]);
           //$user=strtolower($user);
         }

       //print("$user/$domain domainlen=$domainlen userlen=$userlen<BR>");

       $result=mysql_query("SELECT * FROM squidusers WHERE domain=\"$domain\"&&nick=\"$user\" ");
       $row=mysql_fetch_array($result);
       if(strcmp($row['name'],$user)!=0&&strcmp($row['domain'],$domain)!=0)
          {
            if($SAMSConf->NTLMDOMAIN=="Y")
		print("<OPTION VALUE=\"$domain+$user\"> $user/$domain ");
	    else  
	      print("<OPTION VALUE=\"$user\"> $user ");
	  }

     }
  print("</SELECT>\n");
}


function AddUser()
{
  global $SAMSConf;
  $DB=new SAMSDB("$SAMSConf->DB_ENGINE", $SAMSConf->ODBC, $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB, $SAMSConf->PDO);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->access!=2)     {       exit;     }
 
  if(isset($_GET["usernick"])) $usernick=$_GET["usernick"];
  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["usersoname"])) $usersoname=$_GET["usersoname"];
  if(isset($_GET["userfamily"])) $userfamily=$_GET["userfamily"];
  if(isset($_GET["usergroup"])) $usergroup=$_GET["usergroup"];
  if(isset($_GET["userquote"])) $userquote=$_GET["userquote"];
  if(isset($_GET["enabled"])) $enabled=$_GET["enabled"];
  if(isset($_GET["usershablon"])) $usershablon=$_GET["usershablon"];
  if(isset($_GET["userip"])) $userip=$_GET["userip"];
  if(isset($_GET["useripmask"])) $useripmask=$_GET["useripmask"];
  if(isset($_GET["newusernick"])) $newusernick=$_GET["newusernick"];
  if(isset($_GET["userdomain"])) $userdomain=$_GET["userdomain"];

  if(($SAMSConf->AUTH=="ntlm"||$SAMSConf->AUTH=="adld")&&$SAMSConf->NTLMDOMAIN=="Y"&&strlen($newusernick)>0)
    { 
      $nick="$newusernick";
      $domain="$userdomain";
      //print("hand: nick=$nick domain=$domain<BR>");
    }  
  if(($SAMSConf->AUTH=="ntlm"||$SAMSConf->AUTH=="adld")&&$SAMSConf->NTLMDOMAIN!="Y"&&strlen($newusernick)>0)
    { 
      $nick="$newusernick";
      //print("hand: nick=$nick<BR>");
    }  

  if(($SAMSConf->AUTH=="ntlm"||$SAMSConf->AUTH=="adld")&&$SAMSConf->NTLMDOMAIN!="Y"&&strlen($newusernick)==0)
    { 
      $nick="$usernick";
      //print("nick=$nick<BR>");
    }  
  if(($SAMSConf->AUTH=="ntlm"||$SAMSConf->AUTH=="adld")&&$SAMSConf->NTLMDOMAIN=="Y"&&strlen($newusernick)==0)
    { 
      $domain=strtok($usernick,"+");
      $nick=strtok("+");
      //print("nick=$nick domain=$domain<BR>");
    }  
  
   if(strlen($domain)<1)
       $domain="$SAMSConf->DEFAULTDOMAIN";



  $usergroup=trim($usergroup);

  if($SAMSConf->AUTH=="ncsa"||$SAMSConf->AUTH=="ip")
    {
      $pass = "";

      if(isset($_GET["passwd"])) $pass = $_GET["passwd"];

      if(strlen($pass) >= 3)
        $pass=crypt($pass, substr($pass,0,2));
      else
	$pass=crypt("none", substr("none",0,2));
    }

  if($enabled=="on")
     $enabled=1;
  else
     $enabled=-1;

  if(strlen($userip)>7)
    {
      $num_rows=$DB->samsdb_query_value("SELECT s_ip FROM squiduser WHERE s_ip='$userip' ");
      if($num_rows>0)
        {
           PageTop("denied.gif","<FONT COLOR=\"RED\">$usersbuttom_1_useradd_AddUser_1 $userip $usersbuttom_1_useradd_AddUser_2</FONT>");
           exit(0);
        }
     }
  if($SAMSConf->AUTH=="ncsa"||$SAMSConf->AUTH=="ip")
    {
      $DB->samsdb_query("INSERT INTO squiduser ( s_nick, s_domain, s_name, s_family, s_shablon_id, s_quote, s_size, s_enabled, s_group_id, s_soname, s_ip, s_passwd, s_hit, s_autherrorc, s_autherrort ) VALUES ( '$newusernick', '$userdomain', '$username', '$userfamily', '$usershablon', '$userquote', '0', '$enabled', '$usergroup', '$usersoname', '$userip', '$pass', '0', '0', '0') ");
//     if($result!=FALSE)
//         UpdateLog("$SAMSConf->adminname","Added user $newusernick ","01");
    }
  else
    {
	if(strlen($nick)==0) $nick=$newusernick;
	if(strlen($userdomain)==0) $userdomain=$domain;
//echo "INSERT INTO squiduser ( s_nick, s_domain, s_name, s_family, s_shablon_id, s_quote, s_size, s_enabled, s_group_id, s_soname, s_ip, s_passwd, s_hit, s_autherrorc, s_autherrort ) VALUES (  '$nick', '$domain', '$username', '$userfamily', '$usershablon', '$userquote', '0', '$enabled', '$usergroup', '$usersoname','$userip', '$pass', '0', '0', '0' )";
//exit(0);
        $DB->samsdb_query("INSERT INTO squiduser ( s_nick, s_domain, s_name, s_family, s_shablon_id, s_quote, s_size, s_enabled, s_group_id, s_soname, s_ip, s_passwd, s_hit, s_autherrorc, s_autherrort ) VALUES (  '$nick', '$domain', '$username', '$userfamily', '$usershablon', '$userquote', '0', '$enabled', '$usergroup', '$usersoname','$userip', '$pass', '0', '0', '0' ) ");

//     if($result!=FALSE)
//         UpdateLog("$SAMSConf->adminname","Added user $nick ","01");
    }
//  if($result==0)
//    {
//       print("<FONT COLOR=\"RED\">Error creating user (mysql database)</FONT>");
//    }

//  print("<CENTER>\n");
//  NewUserForm();
  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");
}

function NewUserForm()
{
  global $SAMSConf;
  $DB=new SAMSDB("$SAMSConf->DB_ENGINE", $SAMSConf->ODBC, $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB, $SAMSConf->PDO);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access==2)
    {
       if($SAMSConf->AUTH=="ntlm"||$SAMSConf->AUTH=="adld")
         {
           print("<SCRIPT LANGUAGE=JAVASCRIPT>");
           print("function SetChange()");
           print("{");
           print("if(document.forms[\"NEWUSER\"].elements[\"ud\"].checked==true)\n");
           print("  {\n");
           print("    document.forms[\"NEWUSER\"].elements[\"usernick\"].disabled=true\n");
           print("    document.forms[\"NEWUSER\"].elements[\"newusernick\"].disabled=false\n");
           print("    document.forms[\"NEWUSER\"].elements[\"userdomain\"].disabled=false\n");
           //print("    document.forms[\"NEWUSER\"].elements[\"show\"].value=\"addnewuser\"\n");
           print("  }\n");
           print("if(document.forms[\"NEWUSER\"].elements[\"ud\"].checked==false)\n");
           print("  {\n");
           print("    document.forms[\"NEWUSER\"].elements[\"usernick\"].disabled=false\n");
           print("    document.forms[\"NEWUSER\"].elements[\"newusernick\"].disabled=true\n");
           print("    document.forms[\"NEWUSER\"].elements[\"userdomain\"].disabled=true\n");
           print("  }\n");
           print("}\n");
           print("</SCRIPT>");
         }
       PageTop("user.jpg","$userstray_NewUserForm_1");

       print("<FORM NAME=\"NEWUSER\" ACTION=\"main.php\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"adduser\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_1_useradd.php\">\n");
       
       print("<TABLE BORDER=0>\n");
       if($SAMSConf->AUTH=="ntlm"||$SAMSConf->AUTH=="adld")
         {
           print("<TR>\n");
           print("<TD>\n");
           print("<B>$userstray_NewUserForm_2:\n");
           print("<TD>\n");
           GetDomainUsersList();
         }
       print("<TR>\n");
       print("<TD>\n");
       if($SAMSConf->AUTH=="ntlm"||$SAMSConf->AUTH=="adld")
         {
           print("<INPUT TYPE=\"CHECKBOX\" NAME=\"ud\" onclick=SetChange()>");
         }
       print("<B>$userstray_NewUserForm_3: \n");
       print("<TD>\n");
       print("<INPUT TYPE=\"TEXT\" NAME=\"newusernick\" SIZE=30 ");
       if($SAMSConf->AUTH=="ntlm"||$SAMSConf->AUTH=="adld")
         {
           print("DISABLED");
         }
       print("> \n");
       print("<TR><TD>\n");
       print("<B>$userstray_NewUserForm_4: \n");
       print("<TD>\n");
       print("<INPUT TYPE=\"TEXT\" NAME=\"userdomain\" SIZE=30 VALUE=\"$SAMSConf->DEFAULTDOMAIN\" ");
       if($SAMSConf->AUTH=="ntlm"||$SAMSConf->AUTH=="adld")
         {
           print("DISABLED");
         }
       print("> \n");

       if($SAMSConf->AUTH=="ncsa"||$SAMSConf->AUTH=="ip")
         {
           if($SAMSConf->AUTH=="ncsa")
              print("<TR><TD><B>$userstray_NewUserForm_5:\n");
           if($SAMSConf->AUTH=="ip")
              print("<TR><TD><B>$userstray_NewUserForm_6:\n");
           print("<TD><INPUT TYPE=\"PASSWORD\" NAME=\"passwd\" SIZE=20 >\n");
         }
       //if($SAMSConf->AUTH=="ip")
       //  {
           print("<TR><TD><B>$userstray_NewUserForm_7: \n");
           print("<TD><INPUT TYPE=\"TEXT\" NAME=\"userip\" SIZE=15> \n");
       //  }
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$userstray_NewUserForm_8: \n");
       print("<TD>\n");
       print("<INPUT TYPE=\"TEXT\" NAME=\"username\" SIZE=30> \n");
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$userstray_NewUserForm_9: \n");
       print("<TD>\n");
       print("<INPUT TYPE=\"TEXT\" NAME=\"usersoname\" SIZE=30> \n");
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$userstray_NewUserForm_10: \n");
       print("<TD>\n");
       print("<INPUT TYPE=\"TEXT\" NAME=\"userfamily\" SIZE=30> \n");
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$userstray_NewUserForm_11: \n");

       print("<TD>\n");
       print("<SELECT NAME=\"usergroup\" ID=\"groupname\" SIZE=1 TABINDEX=30 >\n");
       $num_rows=$DB->samsdb_query_value("SELECT s_group_id, s_name FROM sgroup");
      while($row=$DB->samsdb_fetch_array())
           {
            print("<OPTION VALUE=$row[s_group_id]> $row[s_name]");
           }
       print("</SELECT>\n");
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$userstray_NewUserForm_12 \n");
       print("<TD>\n");
       print(" \n");
       $DB->free_samsdb_query();

       $num_rows=$DB->samsdb_query_value("SELECT * FROM shablon");
       $row=$DB->samsdb_fetch_array();
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$userstray_NewUserForm_13 \n");
       print("<TD>\n");
       print("<INPUT TYPE=\"TEXT\" NAME=\"userquote\" SIZE=10 VALUE=\"$row[s_quote]\"> \n");
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$userstray_NewUserForm_14:  \n");
       print("<TD>\n");
       print("<INPUT TYPE=\"CHECKBOX\" NAME=\"enabled\" CHECKED> \n");

       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function SetQuote()\n");
       print("{\n");
       $result=mysql_query("SELECT * FROM shablons");
       while($row=mysql_fetch_array($result))
           {
              print("if(document.forms[\"NEWUSER\"].elements[\"usershablon\"].value==\"$row[s_shablon_id]\" )\n");
              print("   document.forms[\"NEWUSER\"].elements[\"userquote\"].value=\"$row[s_quote]\" \n");
           }
//       print("   window.location.reload();\n");
       print("}\n");
       print("</SCRIPT> \n");

       $DB->free_samsdb_query();

       print("<TR>\n");
       print("<TD>\n");
       print("<B>$userstray_NewUserForm_15: \n");
       print("<TD>\n");
       print("<SELECT NAME=\"usershablon\" ID=\"usershablon\" SIZE=1 TABINDEX=30 onChange=\"SetQuote()\">\n");
       $num_rows=$DB->samsdb_query_value("SELECT * FROM shablon");
      while($row=$DB->samsdb_fetch_array())
           {
            print("<OPTION VALUE=$row[s_shablon_id] > $row[s_name]\n");
           }
       print("</SELECT>\n");
       print("</TABLE>\n");
       print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$userstray_NewUserForm_16\">\n");
       print("</FORM>\n");
       $DB->free_samsdb_query();
    }
}




function usersbuttom_1_useradd()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->access==2||($SAMSConf->USERACCESS=="Y"&&$SAMSConf->domainusername=="$row[domain]+$row[nick]"))
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=newuserform&filename=usersbuttom_1_useradd.php","basefrm","useradd_32.jpg","useradd_48.jpg","$usersbuttom_1_useradd_usersbuttom_1_useradd_1");
	}

}

?>
