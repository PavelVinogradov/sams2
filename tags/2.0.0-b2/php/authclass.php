<?php

class SAMSAuthenticate {
var $SAMSConf;
var $authOk;
var $UserName;
var $UserGroup;
var $DomainName;
var $gauditor;
var $autherrorc;
var $autherrort;
var $userid;
var $accessdenied;
var $salt;

function SAMSAuthenticate()
{
  global $SAMSConf;

  $this->SAMSConf=$SAMSConf;
  $this->authOk=0;
  $this->UserName="";
  $this->DomainName="";
  $this->accessdenied=0;
}
function SetUserAuthErrorVariables()
{
	global $SAMSConf;
	$DB=new SAMSDB();

	$time=time();

	if($this->authOk!=0 && $this->autherrorc!=0 )
	{
		$result=$DB->samsdb_query("UPDATE squiduser SET s_autherrorc='0',s_autherrort='0' WHERE s_user_id='$this->userid' ");
		return(0);
	}
	if( $this->authOk==0 )
	{
		if($this->autherrorc>=2)
		{
			$result=$DB->samsdb_query("UPDATE squiduser SET s_autherrorc='0',s_autherrort='$time' WHERE s_user_id='$this->userid' ");
			return(1);
		}
		if($this->autherrorc<2 && $time>$this->autherrort+60)
		{
			$result=$DB->samsdb_query("UPDATE squiduser SET s_autherrorc=s_autherrorc+1,s_autherrort='0' WHERE s_nick='$this->UserName' ");
			return(-1);
		}
	} 
}

function LoadUserVariables($request)
{
    global $SAMSConf;
    $DB=new SAMSDB();
    $num_rows=$DB->samsdb_query_value($request);

    if($num_rows==1)
    {
	$row=$DB->samsdb_fetch_array();
	$this->UserName=$row['s_nick'];
	$this->UserGroup=$row['s_group_id'];
	$this->gauditor=$row['s_gauditor'];
	$this->autherrorc=$row['s_autherrorc'];
	$this->autherrort=$row['s_autherrort'];
	$this->userid=$row['s_user_id'];
	$this->salt=substr($row['s_passwd'],0,2);
	return(1);
    }
    else
	$this->UserName="";

return(0);
}

function LoadUndefinedUserVariables($request)
{
    global $SAMSConf;
    $DB=new SAMSDB();
    $num_rows=$DB->samsdb_query_value($request);

    if($num_rows>0)
    {
	$row=$DB->samsdb_fetch_array();
	$this->UserName=$row['s_nick'];
	$this->UserGroup=$row['s_group_id'];
	$this->gauditor=$row['s_gauditor'];
	$this->autherrorc=$row['s_autherrorc'];
	$this->autherrort=$row['s_autherrort'];
	$this->userid=$row['s_user_id'];
	$this->salt=substr($row['s_passwd'],0,2);
	return(1);
    }
    else
	$this->UserName="";

return(0);
}


function ShowVariables()
{
echo "authOk=$this->authOk<BR>";
echo "UserName=$this->UserName<BR>";
echo "UserGroup=$this->UserGroup<BR>";
echo "DomainName=$this->DomainName<BR>";
echo "gauditor=$this->gauditor<BR>";
echo "autherrorc=$this->autherrorc<BR>";
echo "autherrort=$this->autherrort<BR>";
echo "userid=$this->userid<BR>";

}

}


class LDAPAuthenticate extends SAMSAuthenticate {
function UserAuthenticate($user, $password)
{
	$this->UserName=$user;
	$request="SELECT s_nick,s_passwd,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_nick=\"$user\" ";
	if($this->LoadUserVariables($request)>0)
	{

		$host=GetAuthParameter("ldap","ldapserver");
		$basedn=GetAuthParameter("ldap","basedn");
		$usersrdn=GetAuthParameter("ldap","usersrdn");
		$usersfilter=GetAuthParameter("ldap","usersfilter");
		$usernameattr=GetAuthParameter("ldap","usernameattr");
		$groupsrdn=GetAuthParameter("ldap","groupsrdn");
		$groupsfilter=GetAuthParameter("ldap","groupsfilter");

		include('src/ldap.php');
		$samsldap = new sams_ldap($host, $basedn, $usersrdn, $usersfilter, $usernameattr, $groupsrdn, $groupsfilter, $user, $passwd);

		if ($samsldap->Authenticate($this->UserName,$password))
		{
			$this->authOk=1;
		} 
	}
	return($this->authOk);
}
function UserIDAuthenticate($userid, $password)
{
	$this->userid=$userid;
        $request="SELECT s_nick,s_passwd,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_user_id='$userid'";
	if($this->LoadUserVariables($request)>0)
	{
		$host=GetAuthParameter("ldap","ldapserver");
		$basedn=GetAuthParameter("ldap","basedn");
		$usersrdn=GetAuthParameter("ldap","usersrdn");
		$usersfilter=GetAuthParameter("ldap","usersfilter");
		$usernameattr=GetAuthParameter("ldap","usernameattr");
		$groupsrdn=GetAuthParameter("ldap","groupsrdn");
		$groupsfilter=GetAuthParameter("ldap","groupsfilter");

		include('src/ldap.php');
		$samsldap = new sams_ldap($host, $basedn, $usersrdn, $usersfilter, $usernameattr, $groupsrdn, $groupsfilter, $user, $passwd);

		if ($samsldap->Authenticate($this->UserName,$password))
		{
			$this->authOk=1;
		} 
	}
	return($this->authOk);
}
}


class ADLDAuthenticate extends SAMSAuthenticate {
function UserAuthenticate($user, $password)
{
	$request="SELECT s_nick,s_passwd,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_nick='$user' ";

	if($this->LoadUserVariables($request)>0)
	{
		require_once("src/adldap.php");

		$adldserver=GetAuthParameter("adld","adldserver");
		$basedn=GetAuthParameter("adld","basedn");
		$adadmin=GetAuthParameter("adld","adadmin");
		$adadminpasswd=GetAuthParameter("adld","adadminpasswd");
		$adldusergroup=GetAuthParameter("adld","usergroup");

		$LDAPBASEDN2=strtok($basedn,".");
		$LDAPBASEDN="DC=$LDAPBASEDN2";
		while(strlen($LDAPBASEDN2)>0)
		{
			$LDAPBASEDN2=strtok(".");
			if(strlen($LDAPBASEDN2)>0)
				$LDAPBASEDN="$LDAPBASEDN,DC=$LDAPBASEDN2";
		}

 		$pdc=array("$adldserver");
		$options=array(account_suffix=>"@$basedn", base_dn=>"$LDAPBASEDN",domain_controllers=>$pdc, 
		ad_username=>"$adadmin",ad_password=>"$adadminpasswd","","","");

		$ldap=new adLDAP($options);
		$this->UserName=SAMSLangToUTF8($user);
		if ($ldap->authenticate($this->UserName,$password))
		{
			$this->authOk=1;
		} 
	}
 return($this->authOk);
}
function UserIDAuthenticate($userid, $password)
{
	$this->userid=$userid;
        $request="SELECT s_nick, s_passwd, s_domain, s_gauditor, squiduser.s_group_id, s_autherrorc, s_autherrort, s_user_id FROM squiduser WHERE s_user_id='$userid'";

	if($this->LoadUserVariables($request)>0)
	{
		require_once("src/adldap.php");
		$adldserver=GetAuthParameter("adld","adldserver");
		$basedn=GetAuthParameter("adld","basedn");
		$adadmin=GetAuthParameter("adld","adadmin");
		$adadminpasswd=GetAuthParameter("adld","adadminpasswd");
		$adldusergroup=GetAuthParameter("adld","usergroup");

		$LDAPBASEDN2=strtok($basedn,".");
		$LDAPBASEDN="DC=$LDAPBASEDN2";
		while(strlen($LDAPBASEDN2)>0)
		{
			$LDAPBASEDN2=strtok(".");
			if(strlen($LDAPBASEDN2)>0)
				$LDAPBASEDN="$LDAPBASEDN,DC=$LDAPBASEDN2";
		}

 		$pdc=array("$adldserver");
		$options=array(account_suffix=>"@$basedn", base_dn=>"$LDAPBASEDN", domain_controllers=>$pdc, ad_username=>"$adadmin", ad_password=>"$adadminpasswd", "", "", "");

		$ldap=new adLDAP($options);

		if ($ldap->authenticate(SAMSLangToUTF8($this->UserName),$password))
		{
			$this->authOk=1;
		} 
	}
 return($this->authOk);
}

}


class NTLMAuthenticate extends SAMSAuthenticate {
function UserAuthenticate($user, $password)
{
	global $SAMSConf; //added by DogEater
	$request="SELECT s_nick,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_nick='$user' ";
	$this->LoadUserVariables($request);

//	$STR=$this->SAMSConf->WBINFOPATH." ".$this->UserName." \"$password\"";
//	$e = escapeshellcmd( $STR );
//	$aaa=ExecuteShellScript("bin/testwbinfopasswd", $e);
//	$aaa=ExecuteShellScript("bin/testwbinfopasswd", $e);
        $aaa=ntlm_auth ($this->UserName,$password,$SAMSConf->WBINFOPATH);
        if(stristr($aaa,"OK" )!=false||stristr($aaa,"ERR" )!=true)
//	if(stristr($aaa,"authentication succeeded" )!=false||stristr($aaa,"NT_STATUS_OK" )!=false)
	{ 
		$this->authOk=1;
		if($SAMSConf->NTLMDOMAIN=="Y")
		{
    	    		if(strrpos($user,"+" )!=false)
    	    		{
				$domainname=strtok($user,"+");
				$username=strtok("+");
    	    		}
    	    		if(stristr($user,"\\" )!=false)
    	    		{
        			$domainname=strtok($user,"\\");
				$username=strtok("\\");
    	    		}
    	    		if(stristr($user,"@" )!=false)
    	    		{
        			$domainname=strtok($user,"@");
				$username=strtok("@");
    	    		}
	}		
	else
            $username=$user;
	}	

 return($this->authOk);
}


function UserIDAuthenticate($userid, $password)
{
	global $SAMSConf; //added by DogEater
	$this->userid=$userid;
	$request="SELECT s_nick, s_domain, s_gauditor, squiduser.s_group_id, s_autherrorc, s_autherrort, s_user_id FROM squiduser WHERE s_user_id='$userid'";
	$this->LoadUserVariables($request);

//	$STR=$this->SAMSConf->WBINFOPATH." ".$this->UserName." \"$password\"";
//	$e = escapeshellcmd( $STR );
//	$aaa=ExecuteShellScript("bin/testwbinfopasswd", $e);
//	$aaa=ExecuteShellScript("bin/testwbinfopasswd", $e);
	$aaa=ntlm_auth($this->UserName,$password,$SAMSConf->WBINFOPATH);
//	if(stristr($aaa,"authentication succeeded" )!=false||stristr($aaa,"NT_STATUS_OK" )!=false)
	if(stristr($aaa,"OK" )!=false||stristr($aaa,"ERR" )!=true)
	{ 
		$this->authOk=1;
		if($SAMSConf->NTLMDOMAIN=="Y")
		{
    	    		if(strrpos($user,"+" )!=false)
    	    		{
				$domainname=strtok($user,"+");
				$username=strtok("+");
    	    		}
    	    		if(stristr($user,"\\" )!=false)
    	    		{
        			$domainname=strtok($user,"\\");
				$username=strtok("\\");
    	    		}
    	    		if(stristr($user,"@" )!=false)
    	    		{
        			$domainname=strtok($user,"@");
				$username=strtok("@");
    	    		}
		}		
		else
            		$username=$user;
	}	

 return($this->authOk);
}


}


class NCSAAuthenticate extends SAMSAuthenticate {
function UserAuthenticate($user, $password)
{

    $this->UserName=$user;
    $request="SELECT s_nick, s_passwd, s_domain, s_gauditor, squiduser.s_group_id, s_autherrorc, s_autherrort, s_user_id FROM squiduser WHERE  s_nick='$user'";

    $this->LoadUndefinedUserVariables($request);
    $passwd=crypt($password, $this->salt);

    $request=("SELECT s_nick,s_passwd,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_nick='$user' AND s_passwd='$passwd' ");
    if($this->LoadUserVariables($request)>0)
	$this->authOk=1;

    if($this->authOk==0)
    {
	$request="SELECT s_nick,s_passwd,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_nick=\"$user\" ";
	$this->LoadUserVariables($request);
    }

 return($this->authOk);
}
function UserIDAuthenticate($userid, $password)
{
	$this->userid=$userid;
	$request="SELECT s_nick, s_passwd, s_domain, s_gauditor, squiduser.s_group_id, s_autherrorc, s_autherrort, s_user_id FROM squiduser WHERE s_user_id='$userid'";

	$this->LoadUndefinedUserVariables($request);
	$passwd=crypt($password, $this->salt);

	$request="SELECT s_nick, s_passwd, s_domain, s_gauditor, squiduser.s_group_id, s_autherrorc, s_autherrort, s_user_id FROM squiduser WHERE s_user_id='$userid' AND s_passwd='$passwd'";

	if($this->LoadUserVariables($request)>0)
	{
		$this->authOk=1;
	}
	else
	{
		$request="SELECT s_nick, s_passwd, s_domain, s_gauditor, squiduser.s_group_id, s_autherrorc, s_autherrort, s_user_id FROM squiduser WHERE s_user_id='$userid'";
		$this->LoadUndefinedUserVariables($request);
	}
 return($this->authOk);
}


}




/*
global $SAMSConf;
require('./mysqltools.php');
$SAMSConf=new SAMSCONFIG();
$RRR_SAMS=&$SAMSConf;
*/
/*
$USERAUTH = new NTLMAuthenticate(&$SAMSConf);
$rrr=$USERAUTH->UserAuthenticate("GOZNAK+adm","7 pfobnf~2~ADM"); 
echo "NTLM AUTHENTICATE user goznak+adm: $rrr\n";
*/
/*
echo "SAMSDB=$RRR_SAMS->SAMSDB\n";
$USERAUTH = new NCSAAuthenticate(&$SAMSConf);
$rrr=$USERAUTH->UserAuthenticate("chemerik_nb","qazwsx"); 
echo "NCSA AUTHENTICATE user chemerik: $rrr\n";
*/
/*
$USERAUTH = new ADLDAuthenticate(&$SAMSConf);
$rrr=$USERAUTH->UserAuthenticate("chemerik","qazwsx"); 
$USERAUTH->ShowVariables();
echo "ADLD AUTHENTICATE user chemerik: $rrr\n";
*/
function ntlm_auth ($login, $password,$path){
$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
   1 => array("pipe", "w")  // stdout is a pipe that the child will write to
);
//$ntlm_auth = proc_open("bin/ntlm_auth ".$path, $descriptorspec, $pipes);
$ntlm_auth = proc_open("bin/testwbinfopasswd ".$path, $descriptorspec, $pipes);
if (is_resource($ntlm_auth)) {
    // $pipes now looks like this:
    // 0 => writeable handle connected to child stdin
    // 1 => readable handle connected to child stdout
    // Any error output will be appended to /tmp/error-output.txt

    fwrite($pipes[0], "$login $password\n");
    fclose($pipes[0]);

    $ntlm_auth_says =  stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_value = proc_close($ntlm_auth);
    if ($return_value == 0 ){
	return $ntlm_auth_says;
    }
    else {
	die ("Error talking to ntlm_auth: ".$ntlm_auth_says.".\n");
    }
}
}
?>
