<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

class SAMSCONFIG
{
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
  var $LANG;
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
  var $PDO=0;
  var $DBCONN;
  var $ODBCSOURCE;
/*Авторизация пользователя в веб интерфейсе*/
  var $USERID;
  var $USERWEBACCESS;
  var $AUTHERRORRC;
  var $AUTHERRORRT;
  var $USERPASSWD;

  function ToUserDataAccess($userid, $str)
    {
	//if($this->USERID==$userid&&strstr($this->USERWEBACCESS,"W"))
	//	return(1);
	$maslen=strlen($str);
//echo "$str<BR>";
	for($i=0;$i<$maslen;$i++)
	{
//	echo " -$this->USERID==$userid- -$str[$i]==\"W\" && $this->USERID==$userid && strstr($this->USERWEBACCESS,\"W\")-<BR>";
		if($str[$i]=="W" && $this->USERID==$userid && strstr($this->USERWEBACCESS,"W") )
		{
//			echo "W $this->USERID==$userid ";
			return(1);
		}
		if(strstr($this->USERWEBACCESS,$str[$i]) && $str[$i]!="W")
		{
//			echo "-$str[$i]- ";
			return(1);
		}
	}	
	return(0);
 }

  function SAMSCONFIG()
    {
      require('./config.php');
      $this->ReadSAMSConfFile($configfile);
      $this->ReadSAMSSettings();
      //$this->PrintSAMSSettings();
    }
  function LoadConfig()
    {
      require('./config.php');      
      $this->ReadSAMSConfFile($configfile);
    }

  function ReadSAMSSettings()
    {
      $dbadmin="root";

//	echo "BD CONFIG: $this->DB_ENGINE, $this->ODBC, $this->DB_SERVER, $this->DB_USER, $this->DB_PASSWORD, $this->SAMSDB, $this->PDO<BR>";

	if($this->ODBC == "1" )
	{
		$DB=new SAMSDB($this->DB_ENGINE, $this->ODBC, $this->DB_SERVER, $this->DB_USER, $this->DB_PASSWORD, $this->SAMSDB,  $this->PDO);
		if($DB->dberror != '1')
			{
				$num_rows=$DB->samsdb_query_value("select s_lang from websettings");         
				$row=$DB->samsdb_fetch_array();
				if($row[0] != "EN" )
				  {
					$dbadmin="";
					echo "table is NOT created<BR>";
					$DB->dberror=1;
					//CreateSAMSdbPgSQL($this->DB_SERVER, $this->DB_USER, $this->DB_PASSWORD, $this->SAMSDB);
				  }
			}

	}

	if($this->DB_ENGINE == "MySQL" && $this->ODBC == "0" )
		$DB=new SAMSDB($this->DB_ENGINE, $this->ODBC, $this->DB_SERVER, $this->DB_USER, $this->DB_PASSWORD, $this->SAMSDB, $this->PDO, $this->PDO);
	if($this->DB_ENGINE == "PostgreSQL" && $this->ODBC == "0" )
	{
		$DB=new SAMSDB($this->DB_ENGINE, $this->ODBC, $this->DB_SERVER, $this->DB_USER, $this->DB_PASSWORD, $this->SAMSDB, $this->PDO, $this->PDO);
		if($DB->dberror != '1')
			{
				$num_rows=$DB->samsdb_query_value("select count(tablename) from pg_tables where tablename LIKE 'squiduser' ");         
				$row=$DB->samsdb_fetch_array();
				if($row[0] == 0 )
				  {
					$dbadmin="postgres";
					echo "table is NOT created<BR>";
					$DB->dberror=1;
					//CreateSAMSdbPgSQL($this->DB_SERVER, $this->DB_USER, $this->DB_PASSWORD, $this->SAMSDB);
				  }
			}
	}
	if($DB->dberror=="1")
	{
		echo "<FONT COLOR=\"RED\">Access denied for user $this->DB_USER@$this->DB_SERVER to database $this->DB_ENGINE</FONT><BR>";
		if(isset($_GET["function"])) $function=$_GET["function"];
		if($function=="userdoc")
		  {
       			print("<TABLE><TR> \n");
     			echo "<TD><IMG SRC=\"icon/classic/warning.jpg\" ALIGN=LEFT>";
			echo "<TD>SAMS databases not connected<BR>";
			print("</TABLE> \n");
			print("<hr>\n");
			if($squidlogdb==1)
				echo "The base $this->LOGDB not created or the user $this->DB_USER has no rights to connection to it<BR>";
			if($squidctrldb==1)
				echo "The base $this->SAMSDB not created or the user $this->DB_USER has no rights to connection to it<BR>";

			print("<SCRIPT LANGUAGE=JAVASCRIPT>");
			print("function SetChange()");
			print("{");
			print("if(document.forms[\"createdatabase\"].elements[\"create\"].checked==true)\n");
			print("  {\n");
			print("    document.forms[\"createdatabase\"].elements[\"muser\"].disabled=false\n");
			print("    document.forms[\"createdatabase\"].elements[\"mpass\"].disabled=false\n");
			print("  }\n");
			print("if(document.forms[\"createdatabase\"].elements[\"create\"].checked==false)\n");
			print("  {\n");
			print("    document.forms[\"createdatabase\"].elements[\"muser\"].disabled=true\n");
			print("    document.forms[\"createdatabase\"].elements[\"mpass\"].disabled=true\n");
			print("  }\n");
			print("}\n");
			print("</SCRIPT>");

			print("<H2 ALIGN=\"CENTER\">Create database</H2>");
			print("<FORM NAME=\"createdatabase\" ACTION=\"createdb.php\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"action\" value=\"createdatabase\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"dbname\" value=\"$this->DB_ENGINE\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"samsdb\" value=\"$this->SAMSDB\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"odbc\" value=\"$this->ODBC\">\n");
			print("<INPUT TYPE=\"HIDDEN\" NAME=\"pdo\" value=\"$this->PDO\">\n");
			print("<TABLE WIDTH=\"90%\">\n");
			print("<TR><TD ALIGN=RIGHT>DB Hostname: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"hostname\" value=\"localhost\">\n");
			print("<TR><TD ALIGN=RIGHT>DB login: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"username\" value=\"$dbadmin\">\n");
			print("<TR><TD ALIGN=RIGHT>DB password: <TD ALIGN=LEFT><INPUT TYPE=\"PASSWORD\" NAME=\"pass\">\n");
			if($this->DB_ENGINE == "MySQL")
			{
			print("<TR><TD ALIGN=RIGHT><P>Create SAMS DB user <INPUT TYPE=\"CHECKBOX\" NAME=\"create\" CHECKED  onclick=SetChange()><TD>\n");
			print("<TR><TD ALIGN=RIGHT><P>SAMS DB user: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"muser\" value=\"sams@localhost\">\n");
			print("<TR><TD ALIGN=RIGHT>SAMS DB user password: <TD ALIGN=LEFT><INPUT TYPE=\"PASSWORD\" NAME=\"mpass\">\n");
			}
			print("</TABLE>\n");

			printf("<BR><CENTER>");
			print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Create Database\">\n");
			print("</FORM>\n");

  			print("<P><B>SAMS documentation</B><BR>\n");
  			print("<A HREF=\"doc/EN/index.html\">english<BR>\n");
  			print("<A HREF=\"doc/RU/index.html\">russian<BR>\n");
			
		  }
	  exit(0);
	}
/*
//      $DB->samsdb_query("SELECT * FROM $this->SAMSDB.sams");
      $DB->samsdb_query("SELECT * FROM sams");

      $row=$DB->samsdb_fetch_array();
      $this->REDIRECTOR=$row['s_redirector'];
      $this->DELAYPOOL=$row['s_delaypool'];
      $this->AUTH=$row['s_auth'];
      $this->WBINFOPATH=$row['s_wbinfopath'];
      $this->NTLMDOMAIN=$row['s_ntlmdomain'];
      $this->DEFAULTDOMAIN=$row['s_defaultdomain'];
      $this->realtraffic=$row['s_realsize'];
      $this->SQUIDBASE=$row['s_squidbase'];
      $this->SEPARATOR=$row['s_separator'];
      $this->LOGLEVEL=$row['s_loglevel'];
      $this->CCLEAN=$row['s_count_clean'];
*/
      $DB->samsdb_query("SELECT * FROM websettings");
      $row=$DB->samsdb_fetch_array();
//echo "samsclass.php: ".$row['s_lang']."<BR>";
//exit(0);
      $this->LANG=$row['s_lang'];
      if ($this->LANG=="EN") 
        $this->LANGCODE = "EN"; 
      else
        $this->LANGCODE = "RU";
      $this->ICONSET="icon/$row[s_iconset]";
      $this->USERACCESS=$row['s_useraccess'];
      $this->URLACCESS=$row['s_urlaccess'];
      $this->SHOWUTREE=$row['s_showutree'];
      $this->SHOWNAME=$row['s_showname'];
//      $this->KBSIZE=$row['s_kbsize'];
	if($this->KBSIZE==0) $this->KBSIZE=1024;
//      $this->MBSIZE=$row['s_mbsize'];
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
//         if(!strcasecmp($str2,"SAMSPATH" ))               $this->SAMSDB=trim(strtok("="));
         if(!strcasecmp($str2,"DBNAME" ))       $this->DB_ENGINE=trim(strtok("="));
         if(!strcasecmp($str2,"DB_ENGINE" ))       $this->DB_ENGINE=trim(strtok("="));

         if(!strcasecmp($str2,"MYSQLHOSTNAME" ))         $this->DB_SERVER=trim(strtok("="));
         if(!strcasecmp($str2,"DB_SERVER" ))         $this->DB_SERVER=trim(strtok("="));

         if(!strcasecmp($str2,"ODBC" ))       $this->ODBC=trim(strtok("="));
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

  function PrintSAMSSettings()
    {
      echo "database = $this->DB_ENGINE<BR>";
      echo "adminname = $this->adminname<BR>";
      echo "groupauditor = $this->groupauditor<BR>";
      echo "access = $this->access<BR>";
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

/*
  function LoadConfig()
    {
      require('./config.php');      
      $this->ReadSAMSConfFile($configfile);
    }
      
*/

?>
