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
  $grauditor=0;
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
	$e = escapeshellcmd("$SAMSConf->WBINFOPATH $userdomain $password");
	$aaa=ExecuteShellScript("testwbinfopasswd", $e);
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
    {
       db_connect($SAMSConf->SAMSDB) or exit();
       mysql_select_db($SAMSConf->SAMSDB);
       $passwd=crypt($password, substr($password, 0, 2));
       $result=mysql_query("SELECT nick,passwd,domain,gauditor,squidusers.group,autherrorc,autherrort,id FROM squidusers WHERE nick=\"$userdomain\"&&passwd=\"$passwd\" ");
       $row=mysql_fetch_array($result);
       //$gauditor=$row['gauditor'];
       if(strlen($row['nick'])>0||strlen($row['passwd'])>0)
         {
           $SAMSConf->domainusername="$row[nick]";
         }
     }
/*
  if(($auth=="ip"||$auth=="ncsa")&&$password!="none")
    {
      db_connect($SAMSConf->SAMSDB) or exit();
       mysql_select_db($SAMSConf->SAMSDB);
//update squidusers set passwd=ENCRYPT(passwd, SUBSTRING(passwd,1,2));       
       $result2=mysql_query("SELECT nick,id,passwd FROM squidusers WHERE id=\"$id\" ");
       $row2=mysql_fetch_array($result2);
       $passwd=crypt($password, substr($password, 0, 2));
       $result=mysql_query("SELECT nick,passwd,domain,gauditor,squidusers.group,autherrorc,autherrort,id FROM squidusers WHERE id=\"$id\"&&passwd=\"$passwd\" ");
       $row=mysql_fetch_array($result);
       $gauditor=$row['gauditor'];
       if(strlen($row['nick'])>0||strlen($row['passwd'])>0)
         {
           $SAMSConf->domainusername="$row[nick]";
         }
     }
*/

  $grauditor=0;
  if($row['gauditor']>0&&strlen($SAMSConf->domainusername)>0)
    {
         $grauditor=$row['group'];
         print("<SCRIPT>\n");
         print(" parent.lframe.location.href=\"lframe.php\"; \n");
         print("</SCRIPT> \n");
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
//update squidusers set passwd=ENCRYPT(passwd, SUBSTRING(passwd,1,2));       
//       $result2=mysql_query("SELECT nick,id,passwd FROM squidusers WHERE id=\"$id\" ");
//       $row2=mysql_fetch_array($result2);
      $passwd=crypt($password, substr($password, 0, 2));
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
  $DB=new SAMSDB("$SAMSConf->DBNAME", "0", $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);
  $DB2=new SAMSDB("$SAMSConf->DBNAME", "0", $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["userid"])) $userid=$_GET["userid"];
  $num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE s_user_id='$userid' ");
  $row=$DB->samsdb_fetch_array();

  $num_rows2=$DB2->samsdb_query_value("SELECT * FROM sgroup WHERE s_group_id='$row[s_group_id]' ");
  $row2=$DB2->samsdb_fetch_array();

  PageTop("user.jpg","$usertray_UserForm_1 <FONT COLOR=\"BLUE\">$row[nick]</FONT>");

  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>Nickname:\n");
  print("<TD>\n");
  print("$row[s_nick]\n");
  if($SAMSConf->NTLMDOMAIN=="Y")
    {
      print("<TR>\n");
      print("<TD>\n");
      print("<B>$usertray_UserForm_2\n");
      print("<TD>\n");
      print("$row[S_domain]\n");
    }  
  if($SAMSConf->access==2)
    {
      print("<TR>\n");
      print("<TD>\n");
      print("<B>$usertray_UserForm_3:\n");
      print("<TD>\n");
      print("$row[s_ip]\n");
	}
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$usertray_UserForm_4: \n");
  print("<TD>\n");
  print("$row[s_name]\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$usertray_UserForm_5: \n");
  print("<TD>\n");
  print("$row[s_soname]\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$usertray_UserForm_6: \n");
  print("<TD>\n");
  print("$row[s_family] \n");
  print("<TR>\n");
  print("<TD>\n");

  print("<B>$usertray_UserForm_7: \n");
  print("<TD>\n");
  print("$row2[s_name]\n");
  $DB2->free_samsdb_query();
  
  if($SAMSConf->access==2||strcasecmp($SAMSConf->domainusername,$row[nick])==0||$SAMSConf->groupauditor==$row[group])
    {
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$usertray_UserForm_8: \n");
       print("<TD>\n");
             
       if($row['s_quote']>0)
          print(" $row[s_quote] Mb");
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
//       if($SAMSConf->realtraffic=="real")
//	     PrintTrafficSize($row['s_size']-$row['s_hit']);
//       else
//	     PrintTrafficSize($row['s_size']);
    }
  if($SAMSConf->access==2)
    {
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$usertray_UserForm_10:\n");
       print("<TD>\n");
       if($row['s_enabled']>0)
          print("$usertray_UserForm_13\n");
       else
          print("$usertray_UserForm_11 \n");

       $num_rows2=$DB2->samsdb_query_value("SELECT * FROM shablon WHERE s_shablon_id='$row[s_shablon_id]' ");
       $row2=$DB2->samsdb_fetch_array();
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$usertray_UserForm_12: \n");
       print("<TD>\n");
       print("<A HREF=\"tray.php?show=exe&function=shablontray&id=$row2[s_shablon_id]\" TARGET=\"tray\">$row2[s_name]</A>\n");
       print("</TABLE>\n");
    }
}

function JSUserInfo()
{
  global $SAMSConf;
  global $USERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->s_quote>0)
	$quote=" $USERConf->s_quote Mb";
  else  
	$quote=" unlimited ";
  if($USERConf->s_enabled>0)
		$enabled="$usertray_UserForm_13";
  else
		$enabled="$usertray_UserForm_11";


  $htmlcode="<HTML><BODY><CENTER>
  <TABLE WIDTH=\"95%\" border=0><TR><TD WIDTH=\"10%\"  valign=\"middle\">
  <img src=\"$SAMSConf->ICONSET/user.jpg\" align=\"RIGHT\" valign=\"middle\" >
  <TD  valign=\"middle\"><h2  align=\"CENTER\">$usertray_UserForm_1 <FONT COLOR=\"BLUE\">$USERConf->s_nick</FONT></h2>
  </TABLE>
  <TABLE>
  <TR><TD><B>Nickname:<TD>$USERConf->s_nick
  <TR><TD><B>$usertray_UserForm_2<TD>$USERConf->s_domain";
  if($SAMSConf->access==2)
	$htmlcode=$htmlcode."<TR><TD><B>$usertray_UserForm_3:<TD>$USERConf->s_ip";
  $htmlcode=$htmlcode."<TR><TD><B>$usertray_UserForm_4:<TD>$USERConf->s_name
  <TR><TD><B>$usertray_UserForm_5:<TD>$USERConf->s_soname
  <TR><TD><B>$usertray_UserForm_6:<TD>$USERConf->s_family
  <TR><TD><B>$usertray_UserForm_7:<TD>$USERConf->s_name";
  if($SAMSConf->access==2)
	{
	$htmlcode=$htmlcode."<TR><TD><B>$usertray_UserForm_8:<TD>$quote
	<TR><TD><B>$usertray_UserForm_9:<TD>$USERConf->s_size
	<TR><TD><B>$usertray_UserForm_10:<TD>$enabled";
	}
  $htmlcode=$htmlcode."<TR><TD><B>$usertray_UserForm_12:<TD>$USERConf->s_shablon_name
  </TABLE>";

  $htmlcode=$htmlcode."</CENTER></BODY></HTML>";
  $htmlcode=str_replace("\"","\\\"",$htmlcode);
  $htmlcode=str_replace("\n","",$htmlcode);
  print(" parent.basefrm.document.write(\"$htmlcode\");\n");
  print(" parent.basefrm.document.close();\n");

}


function UserTray()
{
  global $SAMSConf;
  global $USERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

  print("<SCRIPT>\n");
  JSUserInfo();
/*
  if($SAMSConf->access>0)
    {
      print(" parent.basefrm.location.href=\"main.php?show=exe&filename=usertray.php&function=userform&userid=$id\";\n");
    }
  else
    {
      if($SAMSConf->NTLMDOMAIN!="Y")
        $un="$USERConf->s_nick";
      else	
        $un="$USERConf->s_domain+$USERConf->s_nick";

      if((strlen($SAMSConf->domainusername)>0&&$SAMSConf->domainusername==$USERConf->s_nick)||$SAMSConf->groupauditor==$USERConf->s_group)
        {
          print(" parent.basefrm.location.href=\"main.php?show=exe&function=userform&userid=$USERConf->s_user_id\";\n");
        }
      else
        {
          print("parent.basefrm.location.href=\"main.php?show=exe&function=userauthform&userid=$USERConf->s_user_id\";\n");
        }
    }
*/
  print("</SCRIPT> \n");

  print("<TABLE border=0 WIDTH=\"100%\">\n");
  print("<TR>\n");
  print("<TD VALIGN=\"TOP\" WIDTH=\"30%\">");
  print("<B>$usertray_UserTray_1 <BR><FONT SIZE=\"+1\" COLOR=\"blue\">$USERConf->s_nick</FONT></B>\n");

      ExecuteFunctions("./src", "userbuttom", $USERConf->s_user_id);

  print("<TD>\n");
  print("</TABLE>\n");


}

?>
