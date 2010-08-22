<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

class SAMSUSER
{
  var $s_user_id;
  var $s_group_id; 
  var $s_shablon_id;
  var $s_nick;
  var $s_family;
  var $s_name;
  var $s_soname;
  var $s_domain;
  var $s_quote;
  var $s_size;
  var $s_hit;
  var $s_enabled;
  var $s_ip;
  var $s_passwd;
  var $s_gauditor;
  var $s_autherrorc;
  var $s_autherrort;
  var $s_shablon_name;
  var $s_group_name;
  var $s_webaccess;
  var $s_defquote;
  var $s_samsadmin;
  var $s_auth;

  var $W_access;
  var $G_access;
  var $S_access;
  var $A_access;
  var $U_access;
  var $L_access;
  var $C_access;

function SAMSUSER()
{

}

function sams_user($userid)
{
  global $SAMSConf;
  $DB=new SAMSDB();

  $num_rows=$DB->samsdb_query_value("SELECT squiduser.*,shablon.s_name as s_shablon_name,shablon.s_quote as def_quote,sgroup.s_name as s_group_name,shablon.s_auth FROM squiduser LEFT JOIN shablon ON squiduser.s_shablon_id=shablon.s_shablon_id LEFT JOIN sgroup ON squiduser.s_group_id=sgroup.s_group_id WHERE s_user_id='$userid' ");
  $row=$DB->samsdb_fetch_array();
  $this->s_user_id=$row['s_user_id'];
  $this->s_group_id=$row['s_group_id']; 
  $this->s_shablon_id=$row['s_shablon_id'];
  $this->s_nick=$row['s_nick'];
  $this->s_family=$row['s_family'];
  $this->s_name=$row['s_name'];
  $this->s_soname=$row['s_soname'];
  $this->s_domain=$row['s_domain'];
  $this->s_quote=$row['s_quote'];
  $this->s_size=$row['s_size'];
  $this->s_hit=$row['s_hit'];
  $this->s_enabled=$row['s_enabled'];
  $this->s_ip=$row['s_ip'];
  $this->s_passwd=$row['s_passwd'];
  $this->s_gauditor=$row['s_gauditor'];
  $this->s_autherrorc=$row['s_autherrorc'];
  $this->s_autherrort=$row['s_autherrort'];
  $this->s_shablon_name=$row['s_shablon_name'];
  $this->s_group_name=$row['s_group_name'];
  $this->s_webaccess=$row['s_webaccess'];
  $this->s_defquote=$row['def_quote'];
  $this->s_auth=$row['s_auth'];

  if(strstr($this->s_webaccess,"W"))
	$this->W_access = 1;
  if(strstr($this->s_webaccess,"G"))
	$this->G_access = 1;
  if(strstr($this->s_webaccess,"S"))
	$this->S_access = 1;
  if(strstr($this->s_webaccess,"A"))
	$this->A_access = 1;
  if(strstr($this->s_webaccess,"U"))
	$this->U_access = 1;
  if(strstr($this->s_webaccess,"L"))
	$this->L_access = 1;
  if(strstr($this->s_webaccess,"C"))
	$this->C_access = 1;

  $DB->free_samsdb_query();

}

function sams_admin()
{
  global $SAMSConf;
  $this->s_samsadmin=1;
  $this->s_user_id=-1;
  $this->s_group_id=-1; 
  $this->s_shablon_id=-1;
  $this->s_webaccess="WGSAULC";

  $this->W_access = 1;
  $this->G_access = 1;
  $this->S_access = 1;
  $this->A_access = 1;
  $this->U_access = 1;
  $this->L_access = 1;
  $this->C_access = 1;

}


function sams_admin_authentication($username,$passwd)
  {
     global $SAMSConf;
     $DB=new SAMSDB();

     $time=time();
     $num_rows=$DB->samsdb_query_value("SELECT * FROM passwd WHERE s_user='$username' ");
     if($num_rows==0)
     {
	echo "<H2><FONT COLOR=\"RED\">Authorisation ERROR</FONT></H2>";
	exit;
     }
     $row=$DB->samsdb_fetch_array();
     $autherrorc=$row['s_autherrorc'];
     $autherrort=$row['s_autherrort'];
     $admname=$row['s_user'];
     $admpasswd=$row['s_pass'];
     $DB->free_samsdb_query();
     if($autherrorc==0||$time>$autherrort+60)
       {  
         if($time>$autherrort+60)
           {  
		$newpasswd=crypt("$passwd","00");
		if( $admpasswd == $newpasswd )
		  {
			$SAMSConf->adminname=$username;
			if( $autherror > 0 )
				$DB->samsdb_query("UPDATE passwd SET s_autherrorc='0',s_autherrort='0'  WHERE s_user='$username' ");	        
			setcookie("samsadmin","1");
			setcookie("user","$username");
			setcookie("passwd","$newpasswd");
		  }
		else
		  {
			echo "<H2><FONT COLOR=\"RED\">Authorisation ERROR</FONT></H2>";
			if($autherrorc>=2)
			{
	                    $DB->samsdb_query("UPDATE passwd SET s_autherrorc='0',s_autherrort='$time' WHERE s_user='$username'  ");
			    print("<h2>next logon after 60 second</h2> \n");
			}
			else
	                    $DB->samsdb_query("UPDATE passwd SET s_autherrorc=s_autherrorc+1,s_autherrort='0'  WHERE s_user='$username'  ");        
			exit;
		  }
	    }   
         else
           {  
		print("<h1><FONT COLOR=\"RED\">Authentication ERROR</FONT></h1> \n");
		$time2 = 60 - ($time - $autherrort);
		print("<h2>next logon after $time2 second</h2> \n");
		exit(0);
		$user="";
		$function="autherror";
           }
       }

  }   



function sams_user_id_authentication()
  {   
	if(isset($_POST["id"])) $id=$_POST["id"];
	if(isset($_POST["userid"])) $password=$_POST["userid"];
	if(isset($_POST["usernick"])) $user=$_POST["usernick"];
	if(isset($_POST["auth"])) $auth=$_POST["auth"];

	require('authclass.php');

	$time=time();
	if(strtolower($auth)=="ntlm")
	{
		$USERAUTH = new NTLMAuthenticate();
	}
	else if(strtolower($auth)=="adld")
	{
		$USERAUTH = new ADLDAuthenticate();
	}
	else if(strtolower($auth)=="ldap")
		$USERAUTH = new LDAPAuthenticate();
	else 
	{
		$USERAUTH = new NCSAAuthenticate();
	}
	if($USERAUTH->UserIDAuthenticate($id, $password)==1)
	{
		if($USERAUTH->authOk==1)
		{
			if($USERAUTH->autherrorc<=2&&$time>$USERAUTH->autherrort+60)
			{  
				$SAMSConf->groupauditor=$USERAUTH->gauditor;
				setcookie("domainuser",$USERAUTH->UserName);
				setcookie("gauditor",$USERAUTH->gauditor);
				setcookie("userid",$USERAUTH->userid);
				setcookie("webaccess",$USERAUTH->webaccess);
				setcookie("samsadmin","0");
			}
			else
			{  
				print("<h1><FONT COLOR=\"RED\">Authentication ERROR</FONT></h1> \n");
				$time2=60 - ($time - $USERAUTH->autherrort);
				if($USERAUTH->autherrorc==0&&$time<$USERAUTH->autherrort+60)
				{
					print("<h2>next logon after $time2 second</h2> \n");
				}   
				$USERAUTH->SetUserAuthErrorVariables();
				exit(0);
			}
		}
		else
		{  
			print("<h1><FONT COLOR=\"RED\">Authentication ERROR</FONT></h1> \n");
			$time2=60 - ($time - $autherrort);
			if($USERAUTH->autherrorc==0&&$time<$USERAUTH->autherrort+60)
			{
				print("<h2>next logon after $time2 second</h2> \n");
			}   
			$USERAUTH->SetUserAuthErrorVariables();
			exit(0);
		}
	}
	else
	{
		print("<h1><FONT COLOR=\"RED\">Authentication ERROR</FONT></h1>\n");
		$time2=60 - ($time - $USERAUTH->autherrort);
		if($USERAUTH->autherrorc==0&&$time<$USERAUTH->autherrort+60)
		{
			print("<h2>next logon after $time2 second</h2> \n");
		}   
		$USERAUTH->SetUserAuthErrorVariables();
		exit(0);
	}
	$USERAUTH->SetUserAuthErrorVariables();
  }  



function sams_user_name_authentication()
{   
     global $SAMSConf;
     $DB=new SAMSDB();

	if(isset($_POST["id"])) $id=$_POST["id"];
	if(isset($_POST["userid"])) $password=$_POST["userid"];
	if(isset($_POST["user"])) $user=$_POST["user"];
	require('./authclass.php');

     $SQL="SELECT squiduser.s_user_id,shablon.s_auth FROM squiduser LEFT JOIN shablon ON squiduser.s_shablon_id=shablon.s_shablon_id WHERE s_nick='$user';";

     $time=time();
     $num_rows=$DB->samsdb_query_value($SQL);
     if($num_rows==1)
     {
	$row=$DB->samsdb_fetch_array();

	if($row['s_auth']=="ntlm")
		$USERAUTH = new NTLMAuthenticate();
	else if($row['s_auth']=="adld")
		$USERAUTH = new ADLDAuthenticate();
	else if($row['s_auth']=="ldap")
		$USERAUTH = new LDAPAuthenticate();
	else 
		$USERAUTH = new NCSAAuthenticate();

	$USERAUTH->UserAuthenticate($user, $password);

	if($USERAUTH->authOk==1)
	{
		if($USERAUTH->autherrorc<=2&&$time>$USERAUTH->autherrort+60)
		{  
			$SAMSConf->groupauditor=$USERAUTH->gauditor;
			setcookie("domainuser",$USERAUTH->UserName);
			setcookie("gauditor",$USERAUTH->gauditor);
			setcookie("userid",$USERAUTH->userid);
			setcookie("webaccess",$USERAUTH->webaccess);
			setcookie("samsadmin","0");
		}
		else
		{  
			print("<h1><FONT COLOR=\"RED\">Authentication ERROR</FONT></h1> \n");
			$time2=60 - ($time - $USERAUTH->autherrort);
			if($USERAUTH->autherrorc==0&&$time<$USERAUTH->autherrort+60)
			{
				print("<h2>next logon after $time2 second</h2> \n");
			}   
			$USERAUTH->SetUserAuthErrorVariables();
			exit(0);
		}
	}
	else
	{  
		print("<h1><FONT COLOR=\"RED\">Authentication ERROR</FONT></h1> \n");
		$time2=60 - ($time - $USERAUTH->autherrort);
		if($USERAUTH->autherrorc==0&&$time<$USERAUTH->autherrort+60)
		{
			print("<h2>next logon after $time2 second</h2> \n");
		}
		$USERAUTH->SetUserAuthErrorVariables();
		exit(0);
	}
	$USERAUTH->SetUserAuthErrorVariables();
      }
      else
      {
	print("<h1><FONT COLOR=\"RED\">Authentication ERROR</FONT></h1> \n");
	exit(0);

      }
     print("<SCRIPT>\n");
     print("        parent.lframe.location.href=\"lframe.php\";\n");
     print("        parent.tray.location.href = \"tray.php?show=exe&filename=usertray.php&function=usertray&id=".$row['s_user_id']."\";\n");
     print("</SCRIPT> \n");
     exit(0);
}  





  function ToWebInterfaceAccess($str)
    {
	$maslen=strlen($str);
	for($i=0;$i<$maslen;$i++)
	{
		if($str[$i]=="W" && $this->W_access == 1)
		{
			return(1);
		}
		if($str[$i]=="G" && $this->G_access == 1)
		{
			return(1);
		}
		if($str[$i]=="S" && $this->S_access == 1)
		{
			return(1);
		}
		if($str[$i]=="A" && $this->A_access == 1)
		{
			return(1);
		}
		if($str[$i]=="U" && $this->U_access == 1)
		{
			return(1);
		}
		if($str[$i]=="L" && $this->L_access == 1)
		{
			return(1);
		}
		if($str[$i]=="C" && $this->C_access == 1)
		{
			return(1);
		}
	}	
	return(0);
 }

  function ToGroupStatAccess($str, $groupid)
  {
	$maslen=strlen($str);
	for($i=0;$i<$maslen;$i++)
	{
		if($str[$i]=="G" && $this->G_access == 1 && $this->s_group_id==$groupid)
		{
			return(1);
		}
		if($str[$i]=="S" && $this->S_access == 1)
		{
			return(1);
		}
	}	
	return(0);
  }


}

?>
