<?php
/* 
	LDAP FUNCTIONS FOR MANIPULATING ACTIVE DIRECTORY
	Version 1.3

	Maintained by Scott Barnett
	email: scott@wiggumworld.com
	http://adldap.sourceforge.net/

	Written for PHP 4, should still work fine on PHP 5.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	********************************************************************
	Something to keep in mind is that Active Directory is a permissions
	based directory. If you bind as a domain user, you can't fetch as
	much information on other users as you could as a domain admin.
	********************************************************************

	FUNCTIONS:
		
	authenticate($username,$password)
	Authenticate to the directory with a specific username and password
	
	user_info($user,$fields=NULL)
	Returns an array of information for a specific user

	user_groups($user)
	Returns an array of groups that a user is a member off

	user_ingroup($user,$group)
	Returns true if the user is a member of the group
	
	all_users($include_desc = false, $search = "*", $sorted = true)
	Returns all AD users
	
	all_groups($include_desc = false, $search = "*", $sorted = true)
	Returns all AD groups

*/

// Different type of accounts in AD
global $SAMSConf;

define ('ADLDAP_NORMAL_ACCOUNT', 805306368);
define ('ADLDAP_WORKSTATION_TRUST', 805306369);
define ('ADLDAP_INTERDOMAIN_TRUST', 805306370);
define ('ADLDAP_SECURITY_GLOBAL_GROUP', 268435456);
define ('ADLDAP_DISTRIBUTION_GROUP', 268435457);
define ('ADLDAP_SECURITY_LOCAL_GROUP', 536870912);
define ('ADLDAP_DISTRIBUTION_LOCAL_GROUP', 536870913);

class adLDAP {

	// You will need to edit these variables to suit your installation
//	var $_account_suffix="@security.loc";
//	var $_base_dn = "DC=security,DC=loc"; 
	var $_account_suffix;
	var $_base_dn; 
	
	var $_LDAPBASEDN;
	var $_GROUP;
	// An array of domain controllers. Specify multiple controllers if you 
	// would like the class to balance the LDAP queries amongst multiple servers
//var $_domain_controllers = array ("");
	var $_domain_controllers = array ("");
	
	// optional account for searching
	var $_ad_username=NULL;
	var $_ad_password=NULL;
	
	// AD does not return the primary group. http://support.microsoft.com/?kbid=321360
	// This tweak will resolve the real primary group, but may be resource intensive. 
	// Setting to false will fudge "Domain Users" and is much faster.
	var $_real_primarygroup=true;
	
	//other variables
	var $_user_dn;
	var $_user_pass;
	var $_conn;
	var $_bind;

	// default constructor
	function adLDAP(){
		global $SAMSConf;
		
		$this->_LDAPBASEDN="$SAMSConf->LDAPBASEDN";
		$this->_account_suffix="@$SAMSConf->LDAPDOMAIN";
		$this->_base_dn = $SAMSConf->LDAPBASEDN;
		$this->_domain_controllers[0]=$SAMSConf->LDAPSERVER;
		print(" \$_account_suffix=$this->_account_suffix <BR>\$_base_dn=$this->_base_dn <BR>\$_domain_controllers=$this->_domain_controllers<BR>");
		
		//connect to the LDAP server as the username/password
		$this->_conn = ldap_connect($this->random_controller());
		ldap_set_option($this->_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->_conn, LDAP_OPT_REFERRALS, 0); //disable plain text passwords
		
		return true;
	}

	// default destructor
	function __destruct(){ ldap_close ($this->_conn); }

	function random_controller(){
		//select a random domain controller
		mt_srand(doubleval(microtime()) * 100000000);
		$rand=mt_rand(1,count($this->_domain_controllers));
		$rand--;
		return $this->_domain_controllers[$rand];
	}

	// authenticate($username,$password)
	//	Authenticate to the directory with a specific username and password
	//	Extremely useful for validating login credentials
	function authenticate($username,$password){
		//validate a users login credentials
		$returnval=false;
		if ($username!=NULL && $password!=NULL)
		  { //prevent null bind
			$this->_user_dn=$username.$this->_account_suffix;
			$this->_user_pass=$password;
//echo "<BR>this->_conn=$this->_conn<BR>";
			$this->_bind = @ldap_bind($this->_conn,$this->_user_dn,$this->_user_pass);
			//this->_bind = @ldap_bind(this->_conn);
			if ($this->_bind)
				$returnval=true;
		  }
		return $returnval;
	}
	
	// rebind()
	//	Binds to the directory with the default search username and password
	//	specified above.
	function rebind(){
		//connect with another account to search with if necessary
		$ad_dn=$this->_ad_username.$this->_account_suffix;
		$this->_bind = @ldap_bind($this->_conn,$ad_dn,$this->_ad_password);
		if ($this->_bind){ return true; }
		return false;
	}

	// user_info($user,$fields)
	//	Returns an array of information for a specific user
	function user_info($user,$fields=NULL){
		if ($user!=NULL){
			if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary
			
			if ($this->_bind){ //perform the search and grab all their details
				$filter="samaccountname=".$user;
				if ($fields==NULL){ $fields=array("samaccountname","mail","memberof","department","displayname","telephonenumber","primarygroupid"); }
				$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
				$entries = ldap_get_entries($this->_conn, $sr);
				
				// AD does not return the primary group in the ldap query, we may need to fudge it
				if ($this->_real_primarygroup){
					$entries[0]["memberof"][]=$this->group_cn($entries[0]["primarygroupid"][0]);
				} else {
					$entries[0]["memberof"][]="CN=Domain Users,CN=Users,".$this->_base_dn;
				}
				$entries[0]["memberof"]["count"]++;

				return $entries;
			}
		}

		return false;
	}
	
	// user_groups($user)
	//	Returns an array of groups that a user is a member off
	function user_groups($user){
		if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary
		
		if ($this->_bind){
			//search the directory for their information
			$info=@$this->user_info($user,array("memberof","primarygroupid"));
			//echo ("<pre>\n"); print_r($info); echo ("</pre>\n");
			$groups=$info[0][memberof]; //presuming the entry returned is our guy (unique usernames)
			
			$group_array=array();
			
			for ($i=0; $i<$groups["count"]; $i++){ //for each group
				$line=$groups[$i];
				
				$group_name="";
				$line_length=strlen($line);
				//more presumptions, they're all prefixed with CN=
				//so we ditch the first three characters and the group
				//name goes up to the first comma
				for ($j=3; $j<$line_length; $j++){
					if ($line[$j]==","){
						$j=$line_length;
					} else {
						$group_name.=$line[$j];
					}

				}
				$group_array[$i] = $group_name;
			}
			return $group_array;
		}
		return false;	
	}

	// user_ingroup($user,$group)
	//	Returns true if the user is a member of the group
	function user_ingroup($user,$group){
		if (($user!=NULL) && ($group!=NULL)){
			if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary

			if ($this->_bind){
				$groups=$this->user_groups($user,array("memberof"));
				if (in_array($group,$groups)){ return true; }
			}
		}
		return false;
	}
	
	function group_cn($gid){
		if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary
		
		// coping with AD not returning the primary group
		// http://support.microsoft.com/?kbid=321360
		// for some reason it's not possible to search on primarygrouptoken=XXX
		// if someone can show otherwise, I'd like to know about it :)
		// this way is resource intensive and generally a pain in the @#%^
		
		$r=false;
		
		if ($this->_bind){
			$filter="(&(objectCategory=group)(samaccounttype=". ADLDAP_SECURITY_GLOBAL_GROUP ."))";
			$fields=array("primarygrouptoken","samaccountname","distinguishedname");
			$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
			$entries = ldap_get_entries($this->_conn, $sr);
			//echo ("<pre>"); print_r($entries); echo ("</pre>");
			
			for ($i=0; $i<$entries["count"]; $i++){
				if ($entries[$i]["primarygrouptoken"][0]==$gid){
					$r=$entries[$i]["distinguishedname"][0];
					$i=$entries["count"];
				}
			}
		}
		return $r;
	}
	
	
	function all_users($include_desc = false, $search = "*", $sorted = true)
		{
		// Returns all AD users
		if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary
		
		if ($this->_bind){
			$users_array = array();
		
			//perform the search and grab all their details
			$filter = "(&(objectClass=user)(samaccounttype=". ADLDAP_NORMAL_ACCOUNT .")(objectCategory=person)(cn=$search))";
			$fields=array("samaccountname","displayname");
			$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
			$entries = ldap_get_entries($this->_conn, $sr);
		        
			$users[0]=$entries[count];
			$counter=1;
			
			for ($i=0; $i<$entries["count"]; $i++)
			  {
				if( $include_desc && strlen($entries[$i]["displayname"][0]) > 0 )
				  {	
					$users_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["displayname"][0];
					$users[$counter++]=$entries[$i]["samaccountname"][0];
					$users[$counter++]=$users_array[ $entries[$i]["samaccountname"][0] ];
				  }	
				else if( $include_desc )
				  {
					$users_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["samaccountname"][0];
					$users[$counter++]=$entries[$i]["samaccountname"][0];
					$users[$counter++]=$users_array[ $entries[$i]["samaccountname"][0] ];
				   }	
				else
					array_push($users_array, $entries[$i]["samaccountname"][0]);
			  //$c1=$counter-1;
			  //$c2=$counter-2;
			  //echo "$users[$c1], $users[$c2]<BR>";
			  }
			if( $sorted )
				asort($users_array);
//			return $users_array;
			return $users;
		}
		return false;
	}
	
	
	function group_users($include_desc = false, $search = "*", $sorted = true)
		{
		// Returns all AD users
		if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary
		
		if ($this->_bind){
			$users_array = array();
		
			//perform the search and grab all their details
                        $filter = "(&(objectClass=user)(memberOf= CN=$this->_GROUP,$this->_LDAPBASEDN)(samaccounttype=". ADLDAP_NORMAL_ACCOUNT .")(objectCategory=person)(cn=$search))"; 			
			$fields=array("samaccountname","displayname");
			$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
			$entries = ldap_get_entries($this->_conn, $sr);
		        
			$users[0]=$entries[count];
			$counter=1;
			
			for ($i=0; $i<$entries["count"]; $i++)
			  {
				if( $include_desc && strlen($entries[$i]["displayname"][0]) > 0 )
				  {	
					$users_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["displayname"][0];
					$users[$counter++]=$entries[$i]["samaccountname"][0];
					$users[$counter++]=$users_array[ $entries[$i]["samaccountname"][0] ];
				  }	
				else if( $include_desc )
				  {
					$users_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["samaccountname"][0];
					$users[$counter++]=$entries[$i]["samaccountname"][0];
					$users[$counter++]=$users_array[ $entries[$i]["samaccountname"][0] ];
				   }	
				else
					array_push($users_array, $entries[$i]["samaccountname"][0]);
			  //$c1=$counter-1;
			  //$c2=$counter-2;
			  //echo "$users[$c1], $users[$c2]<BR>";
			  }
			if( $sorted )
				asort($users_array);
//			return $users_array;
			return $users;
		}
		return false;
	}
	
	function all_groups($include_desc = false, $search = "*", $sorted = true){
		// Returns all AD groups
		if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary
		
		if ($this->_bind){
			$groups_array = array();
		
			//perform the search and grab all their details
//			$filter = "(&(objectCategory=group)(samaccounttype=". ADLDAP_SECURITY_GLOBAL_GROUP .")(cn=$search))";
			$filter = "(&(objectCategory=group)(samaccounttype=". ADLDAP_SECURITY_LOCAL_GROUP .")(cn=$search))";
			$fields=array("samaccountname","description");
			$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
			$entries = ldap_get_entries($this->_conn, $sr);
			
			$users[0]=$entries[count];
			$counter=1;
			
			for ($i=0; $i<$entries["count"]; $i++)
			  {
				if( $include_desc && strlen($entries[$i]["description"][0]) > 0 )
				  {
					$groups_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["description"][0];
					$users[$counter++]=$entries[$i]["samaccountname"][0];
					$users[$counter++]=$users_array[ $entries[$i]["samaccountname"][0] ];
				   }	
				else if( $include_desc )
				  {
					$groups_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["samaccountname"][0];
					$users[$counter++]=$entries[$i]["samaccountname"][0];
					$users[$counter++]=$users_array[ $entries[$i]["samaccountname"][0] ];
				  }	
				else
					array_push($groups_array, $entries[$i]["samaccountname"][0]);
			  }
			if( $sorted ){ asort($groups_array); }
			return $users;
			//return $groups_array;
		}
		return false;
	}
} // End class

?>