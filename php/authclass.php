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
	$DB=new SAMSDB(&$SAMSConf);
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
    $DB=new SAMSDB(&$SAMSConf);
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
		$adldserver=GetAuthParameter("ldap","ldapserver");
		$basedn=GetAuthParameter("ldap","basedn");
		$adadmin=GetAuthParameter("ldap","adadmin");
		$adadminpasswd=GetAuthParameter("ldap","adadminpasswd");
		$usergroup=GetAuthParameter("ldap","usergroup");

		include('src/ldap.php');
		$samsldap = new sams_ldap($adldserver, $basedn, $usergroup, $adadmin, $adadminpasswd);

		if ($samsldap->Authenticate($this->UserName,$password))
		{
			$this->authOk=1;
		} 
	}
	return($this->authOk);
}
function UserIDAuthenticate($userid, $password)
{
        $request="SELECT s_nick,s_passwd,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_user_id='$userid'";
	if($this->LoadUserVariables($request)>0)
	{
		$adldserver=GetAuthParameter("ldap","ldapserver");
		$basedn=GetAuthParameter("ldap","basedn");
		$adadmin=GetAuthParameter("ldap","adadmin");
		$adadminpasswd=GetAuthParameter("ldap","adadminpasswd");
		$usergroup=GetAuthParameter("ldap","usergroup");

		include('src/ldap.php');
		$samsldap = new sams_ldap($adldserver, $basedn, $usergroup, $adadmin, $adadminpasswd);

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
	$this->UserName=$user;
	$request="SELECT s_nick,s_passwd,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_nick=\"$user\" ";
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

		if ($ldap->authenticate($this->UserName,$password))
		{
			$this->authOk=1;
		} 
	}
 return($this->authOk);
}
function UserIDAuthenticate($userid, $password)
{
echo "UserIDAuthenticate: $userid $password<BR>";

        $request="SELECT s_nick,s_passwd,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_user_id='$userid'";
	if($this->LoadUserVariables($request)>0)
	{
		require_once("src/adldap.php");
/*
		$pdc=array($this->SAMSConf->LDAPSERVER);
		$options=array(account_suffix=>"@".$this->SAMSConf->LDAPDOMAIN, base_dn=>$this->SAMSConf->LDAPBASEDN,domain_controllers=>$pdc, 
			ad_username=>$this->SAMSConf->LDAPUSER,ad_password=>$this->SAMSConf->LDAPUSERPASSWD,"","","");
		$ldap=new adLDAP($options);
*/

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

		if ($ldap->authenticate($this->UserName,$password))
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
	$request="SELECT s_nick,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_nick='$user' ";
	$this->LoadUserVariables($request);

	$STR=$this->SAMSConf->WBINFOPATH." ".$this->UserName." \"$password\"";
	$e = escapeshellcmd( $STR );
//	$aaa=ExecuteShellScript("bin/testwbinfopasswd", $e);
	$aaa=ExecuteShellScript("bin/testwbinfopasswd", $e);

	if(stristr($aaa,"authentication succeeded" )!=false||stristr($aaa,"NT_STATUS_OK" )!=false)
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
	$request="SELECT s_nick,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_user_id='$userid'";
	$this->LoadUserVariables($request);

	$STR=$this->SAMSConf->WBINFOPATH." ".$this->UserName." \"$password\"";
	$e = escapeshellcmd( $STR );
//	$aaa=ExecuteShellScript("bin/testwbinfopasswd", $e);
	$aaa=ExecuteShellScript("bin/testwbinfopasswd", $e);

	if(stristr($aaa,"authentication succeeded" )!=false||stristr($aaa,"NT_STATUS_OK" )!=false)
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
    $passwd=crypt($password, substr($password, 0, 2));
    $request=("SELECT s_nick,s_passwd,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_nick='$user'&&s_passwd='$passwd' ");
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
	$passwd=crypt($password, substr($password, 0, 2));
	$request="SELECT s_nick,s_passwd,s_domain,s_gauditor,squiduser.s_group_id,s_autherrorc,s_autherrort,s_user_id FROM squiduser WHERE s_user_id='$userid'&&s_passwd='$passwd'";
	if($this->LoadUserVariables($request)>0)
	{
		$this->authOk=1;
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
?>










