<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function NotUsersTreeUserAuth()
{
  global $SAMSConf;

  if(isset($_POST["userid"])) $password=$_POST["userid"];
  if(isset($_POST["user"])) $userdomain=$_POST["user"];
  $grauditor="";
  $SAMSConf->domainusername="";
  if($SAMSConf->AUTH=="adld")
    {
	require_once("adldap.php");
	//create the LDAP connection
	$pdc=array("$SAMSConf->LDAPSERVER");
	$options=array(account_suffix=>"@$SAMSConf->LDAPDOMAIN", base_dn=>"$SAMSConf->LDAPBASEDN",domain_controllers=>$pdc, 
	ad_username=>"$SAMSConf->LDAPUSER",ad_password=>"$SAMSConf->LDAPUSERPASSWD","","","");
	$ldap=new adLDAP($options);
      if ($ldap->authenticate($userdomain,$password))
         {
            $aflag=1;
           $SAMSConf->domainusername=$userdomain;
	 }   
    }
  if($SAMSConf->AUTH=="ntlm")
    {
	$aflag=0;
//	$e = escapeshellcmd("$SAMSConf->WBINFOPATH $userdomain $password");
//	$aaa=ExecuteShellScript("testwbinfopasswd", $e);
	$aaa=ExecuteShellScript("testwbinfopasswd", "$SAMSConf->WBINFOPATH $userdomain $password");
	$aflag=0;
	if(stristr($aaa,"authentication succeeded" )!=false||stristr($aaa,"NT_STATUS_OK" )!=false)
	  { 
		$aflag=1;
	  }  
      if($aflag>0)
        {
           if($SAMSConf->NTLMDOMAIN=="Y")
             {
	       if(strrpos($userdomain,"+" )!=false)
	         {
                   $user=strtok($userdomain,"+");
                   $SAMSConf->domainusername=strtok("+");
		 }
	       if(stristr($userdomain,"\\" )!=false)
	         {
                   $user=strtok($userdomain,"\\");
                   $SAMSConf->domainusername=strtok("\\");
		 }
	       if(stristr($userdomain,"@" )!=false)
	         {
                   $user=strtok($userdomain,"@");
                   $SAMSConf->domainusername=strtok("@");
		 }
	     }
	   else
             $SAMSConf->domainusername=$userdomain;
       
           db_connect($SAMSConf->SAMSDB) or exit();
           mysql_select_db($SAMSConf->SAMSDB);
           $result=mysql_query("SELECT nick,passwd,domain,gauditor,squidusers.group,autherrorc,autherrort FROM squidusers WHERE nick=\"$SAMSConf->domainusername\" ");
           $row=mysql_fetch_array($result);
           $SAMSConf->domainusername="$row[nick]";
       }
    }
  if(($SAMSConf->AUTH=="ip"||$SAMSConf->AUTH=="ncsa"|| strlen($SAMSConf->domainusername)==0)&&$password!="none")
//  if($SAMSConf->AUTH=="ip"||$SAMSConf->AUTH=="ncsa")
    {
       db_connect($SAMSConf->SAMSDB) or exit();
       mysql_select_db($SAMSConf->SAMSDB);
       $result=mysql_query("SELECT nick,passwd,domain,gauditor,squidusers.group,autherrorc,autherrort,id FROM squidusers WHERE nick=\"$userdomain\"&&passwd=\"$password\" ");
       $row=mysql_fetch_array($result);
       //$gauditor=$row['gauditor'];
       if(strlen($row['nick'])>0||strlen($row['passwd'])>0)
         {
           $SAMSConf->domainusername="$row[nick]";
         }
     }

  if($row['gauditor']>0&&strlen($SAMSConf->domainusername)>0)
    {
      $grauditor=$row['group'];
    }
     
 return($grauditor);
}
 
function UserAuth()
{
  global $SAMSConf;
  
  if(isset($_POST["usernick"])) $user=$_POST["usernick"];
  if(isset($_POST["userdomain"])) $domain=$_POST["userdomain"];
  if(isset($_POST["userid"])) $password=$_POST["userid"];
  if(isset($_POST["id"])) $id=$_POST["id"];
  if(isset($_POST["authtype"])) $auth=$_POST["authtype"];

  $SAMSConf->grauditor=0;
  $SAMSConf->domainusername="";
  $aflag=0;
  if($auth=="ntlm")
    {
      if($SAMSConf->NTLMDOMAIN=="Y")
        {
          $mas=array();
           $mas=$SAMSConf->SEPARATOR;
           $slashe="\\";

           for($j=strlen($SAMSConf->SEPARATOR)-1;$j>0;$j--)
            {
	      if($mas[$j]==$slashe)
	        {
		  $separator="\\";
		} 
	      else
	        {	
		  $separator=$mas[$j];
		}  
                $userdomain="$domain$separator$user";
                $aaa=ExecuteShellScript("testwbinfopasswd","$SAMSConf->WBINFOPATH $userdomain $password");
		$aflag=0;
		if(stristr($aaa,"authentication succeeded" )!=false||stristr($aaa,"NT_STATUS_OK" )!=false)
		  { 
			$aflag=1;
			$SAMSConf->domainusername=$user;
		  }  
	    } 
           if($aflag==0)
	     {
	        $userdomain="$user";
                $aaa=ExecuteShellScript("testwbinfopasswd","$SAMSConf->WBINFOPATH $userdomain $password");
		$aflag=0;
		if(stristr($aaa,"authentication succeeded" )!=false||stristr($aaa,"NT_STATUS_OK" )!=false)
		  { 
			$aflag=1;
			$SAMSConf->domainusername=$user;
		  }  
	     }
         }
       else
         {
                $userdomain="$user";
                $aaa=ExecuteShellScript("testwbinfopasswd","$SAMSConf->WBINFOPATH $userdomain $password");
		$aflag=0;
		if(stristr($aaa,"authentication succeeded" )!=false||stristr($aaa,"NT_STATUS_OK" )!=false)
		  { 
			$aflag=1;
			$SAMSConf->domainusername=$user;
		  }  
          }
	 
       db_connect($SAMSConf->SAMSDB) or exit();
       mysql_select_db($SAMSConf->SAMSDB);
       $result=mysql_query("SELECT nick,passwd,domain,gauditor,squidusers.group,autherrorc,autherrort FROM squidusers WHERE id=\"$id\" ");
       $row=mysql_fetch_array($result);
       $gauditor=$row['gauditor'];
 
// echo"USER = $SAMSConf->domainusername<BR>";
// exit(0);   
    }
  if(($auth=="ip"||$auth=="ncsa")&&$password!="none")
    {
      db_connect($SAMSConf->SAMSDB) or exit();
       mysql_select_db($SAMSConf->SAMSDB);
       
       $result2=mysql_query("SELECT nick,id FROM squidusers WHERE id=\"$id\" ");
       $row2=mysql_fetch_array($result2);
       $passwd=$password;
       $password=crypt($passwd,$row2['nick']);
       //echo "password=$password<BR>";
       
       $result=mysql_query("SELECT nick,passwd,domain,gauditor,squidusers.group,autherrorc,autherrort,id FROM squidusers WHERE id=\"$id\"&&passwd=\"$passwd\" ");
       $row=mysql_fetch_array($result);
       $gauditor=$row['gauditor'];
       if(strlen($row['nick'])>0||strlen($row['passwd'])>0)
         {
           $SAMSConf->domainusername="$row[nick]";
         }
     }
  if($auth=="adld")
    {

	require_once("adldap.php");
	//create the LDAP connection
	$pdc=array("$SAMSConf->LDAPSERVER");
	$options=array(account_suffix=>"@$SAMSConf->LDAPDOMAIN", base_dn=>"$SAMSConf->LDAPBASEDN",domain_controllers=>$pdc, 
	ad_username=>"$SAMSConf->LDAPUSER",ad_password=>"$SAMSConf->LDAPUSERPASSWD","","","");
	$ldap=new adLDAP($options);

        if($ldap==NULL)
          {
             //print("Connection not created!");
             exit(0);
          }
        else
          {
            $result=mysql_query("SELECT nick,passwd,domain,gauditor,squidusers.group,id FROM squidusers WHERE id=\"$id\" ");
            $row=mysql_fetch_array($result);

	    if($ldap -> authenticate( $row['nick'], $password ))
            {
              $SAMSConf->domainusername=$row['nick'];
            }
          }
     }
  $grauditor=0;
  if($row['gauditor']>0&&strlen($SAMSConf->domainusername)>0)
     $grauditor=$row['group'];
     
    
 return($grauditor);
}



function UserAuthForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["userid"])) $userid=$_GET["userid"];

  $result=mysql_query("SELECT squidusers.*,shablons.auth FROM squidusers LEFT JOIN shablons ON squidusers.shablon=shablons.name WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);

  PageTop("getpassword.jpg","$usertray_UserAuthForm_1 <FONT COLOR=\"BLUE\">$row[nick]</FONT>");
  print("<P>\n");
  print("<FORM NAME=\"USERPASSWORD\" ACTION=\"main.php\" method=\"POST\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"userauth\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$row[id]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"usergroup\" value=\"$row[group]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userdomain\" value=\"$row[domain]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"usernick\" value=\"$row[nick]\">\n");
//  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$row[id]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"authtype\" value=\"$row[auth]\">\n");
  print("<TABLE WIDTH=\"90%\">\n");
  print("<TR>\n");
  print("<TD><B>login:</B>\n");

  print("<TD><B>$row[nick]\n");
  print("<TR>\n");
  print("<TD><B>password:</B>\n");
  print("<TD><INPUT TYPE=\"PASSWORD\" NAME=\"userid\" SIZE=30> \n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Ok\">\n");
  print("</FORM>\n");
}



function UserForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["userid"])) $userid=$_GET["userid"];

  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);
  $result2=mysql_query("SELECT * FROM groups WHERE name=\"$row[group]\" ");
  $row2=mysql_fetch_array($result2);

  PageTop("user.jpg","$usertray_UserForm_1 <FONT COLOR=\"BLUE\">$row[nick]</FONT>");

  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>Nickname:\n");
  print("<TD>\n");
  print("$row[nick]\n");
  if($SAMSConf->NTLMDOMAIN=="Y")
    {
      print("<TR>\n");
      print("<TD>\n");
      print("<B>$usertray_UserForm_2\n");
      print("<TD>\n");
      print("$row[domain]\n");
    }  
  if($SAMSConf->access==2)
    {
      print("<TR>\n");
      print("<TD>\n");
      print("<B>$usertray_UserForm_3:\n");
      print("<TD>\n");
      print("$row[ip]/$row[ipmask]\n");
	}
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$usertray_UserForm_4: \n");
  print("<TD>\n");
  print("$row[name]\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$usertray_UserForm_5: \n");
  print("<TD>\n");
  print("$row[soname]\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$usertray_UserForm_6: \n");
  print("<TD>\n");
  print("$row[family] \n");
  print("<TR>\n");
  print("<TD>\n");

  print("<B>$usertray_UserForm_7: \n");
  print("<TD>\n");
  print("$row2[nick]\n");
  
  if($SAMSConf->access==2||strcasecmp($SAMSConf->domainusername,$row[nick])==0||$SAMSConf->groupauditor==$row[group])
    {
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$usertray_UserForm_8: \n");
       print("<TD>\n");
             
       if($row['quotes']>0)
          print(" $row[quotes] Mb");
       else  
          print(" unlimited ");
//       print("$row[quotes] Mb\n");
       
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$usertray_UserForm_9: \n");
       print("<TD>\n");

       $syea=strftime("%Y");
       $smon=strftime("%m");
       $eday=strftime("%d");

       $sdate="$syea-$smon-1";
       $edate="$syea-$smon-$eday";
       $stime="0:00:00";
       $etime="0:00:00";
       if($SAMSConf->realtraffic=="real")
	     PrintTrafficSize($row['size']-$row['hit']);
       else
	     PrintTrafficSize($row['size']);
    }
  if($SAMSConf->access==2)
    {
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$usertray_UserForm_10:\n");
       print("<TD>\n");
       if($row['enabled']>0)
          print("$usertray_UserForm_13\n");
       else
          print("$usertray_UserForm_11 \n");

       $result3=mysql_query("SELECT * FROM shablons WHERE shablons.name=\"$row[shablon]\" ");
       $row3=mysql_fetch_array($result3);
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$usertray_UserForm_12: \n");
       print("<TD>\n");
       print("<A HREF=\"tray.php?show=exe&function=shablontray&id=$row3[name]\" TARGET=\"tray\">$row3[nick]</A>\n");
       print("</TABLE>\n");
    }
}


function UserTray($userid,$usergroup)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$userid\"&&squidusers.group=\"$usergroup\" ");
  $row=mysql_fetch_array($result);

  $result2=mysql_query("SELECT * FROM groups WHERE groups.name=\"$row[group]\" ");
  $row2=mysql_fetch_array($result2);

  print("<SCRIPT>\n");

  if($SAMSConf->access>0)
    {
      print(" parent.basefrm.location.href=\"main.php?show=exe&function=userform&userid=$row[id]\";\n");
    }
  else
    {
      if($SAMSConf->NTLMDOMAIN!="Y")
        $un="$row[nick]";
      else	
        $un="$row[domain]+$row[nick]";

      if((strlen($SAMSConf->domainusername)>0&&$SAMSConf->domainusername==$row[nick])||$SAMSConf->groupauditor==$row[group])
        {
          print(" parent.basefrm.location.href=\"main.php?show=exe&function=userform&userid=$row[id]\";\n");
        }
      else
        {
          print("parent.basefrm.location.href=\"main.php?show=exe&function=userauthform&userid=$row[id]\";\n");
        }
    }
  print("</SCRIPT> \n");

  print("<TABLE border=0 WIDTH=\"100%\">\n");
  print("<TR>\n");
  print("<TD VALIGN=\"TOP\" WIDTH=\"30%\">");
  print("<B>$usertray_UserTray_1 <BR><FONT SIZE=\"+1\" COLOR=\"blue\">$row[nick]</FONT></B>\n");

      ExecuteFunctions("./src", "userbuttom", $row['id']);

  print("<TD>\n");
  print("</TABLE>\n");


}

?>
