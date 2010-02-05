<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
class MAINCONF
{
  var $DB_ENGINE;
  var $DB_SERVER;
  var $DB_USER;
  var $DB_PASSWORD;
  var $SAMSPATH;
  var $access;
  var $groupauditor;
  var $domainusername;
  var $adminname;
  var $DELAYPOOL;
  var $USERACCESS;
  var $URLACCESS;
  var $SAMSDB;    
  var $LOGDB;    
  var $MYSQLHOSTNAME;    
  var $MYSQLUSER;        
  var $MYSQLPASSWORD;    
  var $SQUIDCACHEFILE;   
  var $SQUIDROOTDIR;   
  var $SQUIDLOGDIR;   
  var $SGUARDDBPATH;
  var $SGUARDLOGPATH;
  var $REDIRECTOR;
  var $AUTH;
  var $WBINFOPATH;
  var $ININT;
  var $EXINT;
  var $EXIP;
  var $CHARSET;
  var $NTLMDOMAIN;
  var $ICONSET;
  var $SHOWUTREE;  
  var $SHOWNAME;
  var $LDAPUSER;
  var $LDAPUSERPASSWD;
  var $LDAPUSERSGROUP;
  var $LDAPSERVER;
  var $LDAPBASEDN;
  var $LDAPDOMAIN;
  var $MBSIZE;
  var $KBSIZE;
  var $DEFAULTDOMAIN;
  var $realtraffic;
  var $SQUIDBASE;
  var $SWITCHTO;
  var $SHOWGRAPH;
  var $PDFLIB;
  var $MYSQLVERSION;
  var $SHUTDOWN;
  var $PHPVER;
  var $SEPARATOR;
  var $PROXYCOUNT;
  var $LOGLEVEL;
  var $CCLEAN;
  var $DBNAME;
  var $ODBC=0;
  var $ODBC_DRIVER;
  var $PDO=0;
  var $DBCONN;
  var $ODBCSOURCE;

  function ReadSAMSConfFile($configfile)
    {
      $version=phpversion();
      $this->PHPVER=strtok($version,".");
      
      $finp=fopen($configfile,"rt");
      if($finp==FALSE)
        {
          echo "can't open sams config file $configfile<BR>";
          exit(0);
        }
      while(feof($finp)==0)
       {
         $string=fgets($finp, 10000);
         $str2=trim(strtok($string,"="));

         if(!strcasecmp($str2,"DBNAME" ))       $this->DB_ENGINE=trim(strtok("="));
         if(!strcasecmp($str2,"DB_ENGINE" ))       $this->DB_ENGINE=trim(strtok("="));

         if(!strcasecmp($str2,"MYSQLHOSTNAME" ))         $this->DB_SERVER=trim(strtok("="));
         if(!strcasecmp($str2,"DB_SERVER" ))         $this->DB_SERVER=trim(strtok("="));

         if(!strcasecmp($str2,"ODBC" ))       $this->ODBC=trim(strtok("="));
         if(!strcasecmp($str2,"ODBC_DRIVER" ))       $this->ODBC_DRIVER=trim(strtok("="));
         if(!strcasecmp($str2,"PDO" ))       $this->PDO=trim(strtok("="));

         if(!strcasecmp($str2,"ODBCSOURCE" ))       $this->ODBCSOURCE=trim(strtok("="));

         if(!strcasecmp($str2,"SAMS_DB" ))               $this->SAMSDB=trim(strtok("="));

         if(!strcasecmp($str2,"MYSQLUSER" ))   	         $this->DB_USER=trim(strtok("="));
         if(!strcasecmp($str2,"DB_USER" ))   	         $this->DB_USER=trim(strtok("="));

         if(!strcasecmp($str2,"MYSQLPASSWORD" ))         $this->DB_PASSWORD=trim(strtok("="));
         if(!strcasecmp($str2,"DB_PASSWORD" ))         $this->DB_PASSWORD=trim(strtok("="));

         if(!strcasecmp($str2,"SQUIDCACHEFILE" ))        $this->SQUIDCACHEFILE=trim(strtok("="));
         if(!strcasecmp($str2,"SQUIDROOTDIR" ))          $this->SQUIDROOTDIR=trim(strtok("="));
         if(!strcasecmp($str2,"SQUIDLOGDIR" ))           $this->SQUIDLOGDIR=trim(strtok("="));
         if(!strcasecmp($str2,"SQUIDGUARDLOGPATH" ))     $this->SGUARDLOGPATH=trim(strtok("="));
         if(!strcasecmp($str2,"SQUIDGUARDDBPATH" ))      $this->SGUARDDBPATH=trim(strtok("="));
	 if(!strcasecmp($str2,"WBINFOPATH" ))            $this->WBINFOPATH=trim(strtok("="));
         if(!strcasecmp($str2,"ININT" ))                 $this->ININT=trim(strtok("="));
         if(!strcasecmp($str2,"EXINT" ))                 $this->EXINT=trim(strtok("="));
         if(!strcasecmp($str2,"EXIP" ))                  $this->EXIP=trim(strtok("="));
         if(!strcasecmp($str2,"LDAPUSER" ))              $this->LDAPUSER=trim(strtok("="));
         if(!strcasecmp($str2,"LDAPUSERPASSWD" ))        $this->LDAPUSERPASSWD=trim(strtok("="));
         if(!strcasecmp($str2,"LDAPUSERSGROUP" ))        $this->LDAPUSERSGROUP=trim(strtok("="));
         if(!strcasecmp($str2,"LDAPSERVER" ))            $this->LDAPSERVER=trim(strtok("="));
         if(!strcasecmp($str2,"MYSQLVERSION" ))          $this->MYSQLVERSION=trim(strtok("="));
         if(!strcasecmp($str2,"SHUTDOWNCOMMAND" ))       $this->SHUTDOWN=trim(strtok("="));
         if(!strcasecmp($str2,"SAMSPATH" ))       $this->SAMSPATH=trim(strtok("="));

         if(!strcasecmp($str2,"LDAPBASEDN" ))
           {
              $str2=trim(strtok($string,"="));
	      $LDAPBASEDN_=trim(strtok("="));
	      $LDAPDOMAIN=$LDAPBASEDN_;
	      $this->LDAPDOMAIN=$LDAPDOMAIN;
              $LDAPBASEDN2=strtok($LDAPBASEDN_,".");
	      $this->LDAPBASEDN="DC=$LDAPBASEDN2";
		while(strlen($LDAPBASEDN2)>0)
		{
			$LDAPBASEDN2=strtok(".");
			if(strlen($LDAPBASEDN2)>0)
	      			$this->LDAPBASEDN="$this->LDAPBASEDN,DC=$LDAPBASEDN2";
		}
           }
       }
      fclose($finp);
    }

  function LoadConfig()
    {
      require('./config.php');      
      $this->ReadSAMSConfFile($configfile);
    }
  function MAINCONF()
    {
      $this->LoadConfig();
    }

}

###################################################################################
class SAMSCONFIG extends MAINCONF
{
//  $SAMSConf=new MAINCONF();
/*Авторизация пользователя в веб интерфейсе*/
  var $LANG;
  var $USERID;
  var $USERWEBACCESS;
  var $AUTHERRORRC;
  var $AUTHERRORRT;
  var $USERPASSWD;

  function ToUserDataAccess($userid, $str)
    {
	$this->USERWEBACCESS="W";
	$maslen=strlen($str);
	for($i=0;$i<$maslen;$i++)
	{
		if($str[$i]=="W" && $this->USERID==$userid && strstr($this->USERWEBACCESS,"W") )
		{
			return(1);
		}
		if(strstr($this->USERWEBACCESS,$str[$i]) && $str[$i]!="W")
		{
			return(1);
		}
	}	
	return(0);
 }

  function SAMSCONFIG()
  {
	parent::__construct();
	$this->ReadSAMSSettings();
  }

  function ReadSAMSSettings()
    {
      $dbadmin="root";
	$DB=new SAMSDB();
	if($DB->link==FALSE)
	{
		return(FALSE);
	}

	if($DB->dberror=="1")
	{
		exit(0);
	}
      $DB->samsdb_query("SELECT * FROM websettings");
      $row=$DB->samsdb_fetch_array();
      $this->LANG=$row['s_lang'];
      if ($this->LANG == "WIN1251")
	$this->CHARSET = "windows-1251";
      else
	$this->CHARSET = $this->LANG;

      if ($this->LANG=="EN") 
        $this->LANGCODE = "EN"; 
      else
        $this->LANGCODE = "RU";
      $this->ICONSET="icon/$row[s_iconset]";
      $this->USERACCESS=$row['s_user'];
      $this->URLACCESS=$row['s_urlaccess'];
      $this->SHOWUTREE=$row['s_showutree'];
      $this->SHOWNAME=$row['s_showname'];
	if($this->KBSIZE==0) $this->KBSIZE=1024;
	if($this->MBSIZE==0) $this->MBSIZE=$this->KBSIZE*$this->KBSIZE;
      $this->SHOWGRAPH=$row['s_showgraph'];
      $this->PDFLIB=$row['s_createpdf'];

      $DB->samsdb_query("SELECT COUNT(*) FROM proxy ");
      $row=$DB->samsdb_fetch_array();
      $this->PROXYCOUNT=$row[0];
      if($row[0]==0)
        $this->PROXYCOUNT = 1;
        $this->SWITCHTO=1;
//      $DB->samsdb_query("USE $this->SAMSDB");
    }


  function PrintSAMSSettings()
    {
      echo "database = $this->DB_ENGINE<BR>";
      echo "adminname = $this->adminname<BR>";
      echo "groupauditor = $this->groupauditor<BR>";
      echo "domainusername = $this->domainusername<BR>";
      echo "SAMSDB = $this->SAMSDB<BR>";    
      echo "LOGDB = $this->LOGDB<BR>";    
      echo "MYSQLHOSTNAME = $this->DB_SERVER<BR>";    
      echo "MYSQLUSER = $this->DB_USER<BR>";        
      echo "DELAYPOOL = $this->DELAYPOOL<BR>";
      echo "USERACCESS = $this->USERACCESS<BR>";
      echo "URLACCESS = $this->URLACCESS<BR>";
      echo "MYSQLPASSWORD = $this->DB_PASSWORD<BR>";    
      echo "SQUIDCACHEFILE = $this->SQUIDCACHEFILE<BR>";   
      echo "SQUIDROOTDIR = $this->SQUIDROOTDIR<BR>";   
      echo "SQUIDLOGDIR = $this->SQUIDLOGDIR<BR>";   
      echo "SGUARDDBPATH = $this->SGUARDDBPATH<BR>";
      echo "SGUARDLOGPATH = $this->SGUARDLOGPATH<BR>";
      echo "REDIRECTOR = $this->REDIRECTOR<BR>";
      echo "AUTH = $this->AUTH<BR>";
      echo "WBINFOPATH = $this->WBINFOPATH<BR>";
      echo "ININT = $this->ININT<BR>";
      echo "EXINT = $this->EXINT<BR>";
      echo "EXIP = $this->EXIP<BR>";
      echo "LANG = $this->LANG<BR>";
      echo "NTLMDOMAIN = $this->NTLMDOMAIN<BR>";
      echo "ICONSET = $this->ICONSET<BR>";
      echo "SHOWUTREE = $this->SHOWUTREE<BR>";  
      echo "SHOWNAME = $this->SHOWNAME<BR>";
      echo "LDAPUSER = $this->LDAPUSER<BR>";
      echo "LDAPUSERPASSWD = $this->LDAPUSERPASSWD<BR>";
      echo "LDAPUSERSGROUP = $this->LDAPUSERSGROUP<BR>";
      echo "LDAPSERVER = $this->LDAPSERVER<BR>";
      echo "LDAPBASEDN = $this->LDAPBASEDN<BR>";
      echo "LDAPDOMAIN = $this->LDAPDOMAIN<BR>";
      echo "MBSIZE = $this->MBSIZE<BR>";
      echo "KBSIZE = $this->KBSIZE<BR>";
      echo "DEFAULTDOMAIN = $this->DEFAULTDOMAIN<BR>";
      echo "realtraffic = $this->realtraffic<BR>";
      echo "SQUIDBASE = $this->SQUIDBASE<BR>";
      echo "SWITCHTO = $this->SWITCHTO<BR>";
      echo "SHUTDOWN = $this->SHUTDOWN<BR>";
      
    }

}


?>
