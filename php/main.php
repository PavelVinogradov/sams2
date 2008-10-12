<?php
/*      SAMS 2(Squid Account Management System ver. 2 
 *      Author: Dmitry Chemerik chemerik@mail.ru
 *
 *	This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this programt, write to the Free Software
 *	Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */



  global $DATE;
  global $SAMSConf;
  global $PROXYConf;
  global $USERConf;
  global $TRANGEConf;
  global $SHABLONConf;

  require('./dateclass.php');
  require('./dbclass.php');
  require('./samsclass.php');
  require('./tools.php');
  include('./pluginmanager.php');  

/******************************/
$sday=0;
$smon=0;
$syea=0;
$shou=0;
$eday=0;
$emon=0;
$eyea=0;
$ehou=0;
$sdate=0; 
$edate=0;
$user=0;
$function="";
$filename=0;
$username=0;
$usergroup=0;
$usernick=0;
$userid=0;
$userid=0;
$gb=0;

if(isset($_GET["show"])) $user=$_GET["show"];
if(isset($_GET["module"])) $module=$_GET["module"]; else $module=null;
if(isset($_GET["function"])) $function=$_GET["function"];
if(isset($_GET["filename"])) $filename=$_GET["filename"];
if(isset($_GET["userid"])) $userid=$_GET["userid"];
if(isset($_GET["username"])) $username=$_GET["username"];

if(isset($_POST["show"])) $user=$_POST["show"];
if(isset($_POST["function"])) $function=$_POST["function"];
if(isset($_POST["filename"])) $filename=$_POST["filename"];
if(isset($_POST["userid"])) $userid=$_POST["userid"];
if(isset($_POST["username"])) $username=$_POST["username"];

if(isset($_GET["id"])) $userid=$_GET["id"];
if(isset($_GET["gb"])) $gb=$_GET["gb"];

if(isset($_GET["SDay"])) $sday=$_GET["SDay"];
if(isset($_GET["EDay"])) $eday=$_GET["EDay"];
if(isset($_GET["SMon"])) $smon=$_GET["SMon"];
if(isset($_GET["EMon"])) $emon=$_GET["EMon"];
if(isset($_GET["SYea"])) $syea=$_GET["SYea"];
if(isset($_GET["EYea"])) $eyea=$_GET["EYea"];
if(isset($_GET["SHou"])) $shou=$_GET["SHou"];
if(isset($_GET["EHou"])) $ehou=$_GET["EHou"];

if(isset($_GET["sdate"]))
	{
		$a=explode("-",$_GET["sdate"]);
		$syea=$a[0];
		$smon=$a[1];
		$sday=$a[2];
	}
if(isset($_GET["edate"]))
	{
		$a=explode("-",$_GET["edate"]);
		$eyea=$a[0];
		$emon=$a[1];
		$eday=$a[2];
	}
$reloadleftframe=0;
$settime=0;

if($ehou==0)
   $ehou=24;
if($shou==0)
   $shou=0;
$DATE=new DATE(Array($sday,$smon,$syea,$shou,$eday,$emon,$eyea,$ehou), $sdate, $edate);
$SAMSConf=new SAMSCONFIG();

//$DB=new SAMSDB(&$SAMSConf);
$DB=new SAMSDB(&$SAMSConf);

$lang="./lang/lang.$SAMSConf->LANG";
require($lang);

$WI=1;
  
if($function=="logoff")
  {
     setcookie("user","");
     setcookie("passwd","");
     setcookie("domainuser","");
     setcookie("gauditor","");
     setcookie("userid","");
     setcookie("webaccess","");
     $function="setcookie";
  }   
/*
Авторизация администратора
*/
if($function=="setcookie")
  {
     $time=time();
     $num_rows=$DB->samsdb_query_value("SELECT * FROM passwd WHERE s_user='$username' ");
     if($num_rows==0)
	echo "ERROR<BR>";
     $row=$DB->samsdb_fetch_array();
     //$row=mysql_fetch_array($result);
     $autherrorc=$row['s_autherrorc'];
     $autherrort=$row['s_autherrort'];
     $admname=$row['s_user'];
     $admpasswd=$row['s_pass'];
     $DB->free_samsdb_query();
     if($autherrorc==0||$time>$autherrort+60)
       {  
         if($time>$autherrort+60)
           {  
		$newpasswd=crypt("$userid","00");
		if( $admpasswd == $newpasswd )
		  {
			$SAMSConf->adminname=$username;
			if( $autherror > 0 )
				$DB->samsdb_query("UPDATE passwd SET s_autherrorc='0',s_autherrort='0'  WHERE s_user='$username' ");	        
		  }
		else
		  {
			if($autherrorc>=2)
	                    $DB->samsdb_query("UPDATE passwd SET s_autherrorc='0',s_autherrort='$time' WHERE s_user='$username'  ");         
			else
	                    $DB->samsdb_query("UPDATE passwd SET s_autherrorc=s_autherrorc+1,s_autherrort='0'  WHERE s_user='$username'  ");        
		  }
		setcookie("user","$username");
		setcookie("passwd","$newpasswd");
	    }   
         else
           {  
               $user="";
               $function="autherror";
           }
       }
  }   
//exit(0);

/*
Авторизация пользователя
*/


if($function=="userauth")
  {   
	if(isset($_POST["id"])) $id=$_POST["id"];
	if(isset($_POST["userid"])) $password=$_POST["userid"];
	if(isset($_POST["usernick"])) $user=$_POST["usernick"];
	require('./authclass.php');

	$USERAUTH = new SAMSAuthenticate();
	$USERAUTH->UserIDAuthenticate($id, $password);
//$USERAUTH->ShowVariables();
//exit(0);

	$time=time();
	if($USERAUTH->authOk==1)
	{
		if($USERAUTH->autherrorc<=2&&$time>$USERAUTH->autherrort+60)
		{  
			$SAMSConf->groupauditor=$USERAUTH->gauditor;
			setcookie("domainuser",$USERAUTH->UserName);
			setcookie("gauditor",$USERAUTH->gauditor);
			setcookie("userid",$USERAUTH->userid);
			setcookie("webaccess",$USERAUTH->webaccess);
		}
		else
		{  
			$user="";
			$function="autherror";
		}
	}
	else
	{  
		$user="";
		$function="autherror";
	}
	$USERAUTH->SetUserAuthErrorVariables();
//$USERAUTH->ShowVariables();
//echo "1.function = $function<BR>";
//exit(0);
  }  

if($function=="nuserauth")
  {   
	if(isset($_POST["id"])) $id=$_POST["id"];
	if(isset($_POST["userid"])) $password=$_POST["userid"];
	if(isset($_POST["user"])) $user=$_POST["user"];
	require('./authclass.php');
	$USERAUTH = new SAMSAuthenticate();
	$USERAUTH->UserAuthenticate($user, $password);
//$USERAUTH->ShowVariables();
//exit(0);

	$time=time();
	if($USERAUTH->authOk==1)
	{
		if($USERAUTH->autherrorc<=2&&$time>$USERAUTH->autherrort+60)
		{  
			$SAMSConf->groupauditor=$USERAUTH->gauditor;
			setcookie("domainuser",$USERAUTH->UserName);
			setcookie("gauditor",$USERAUTH->gauditor);
			setcookie("userid",$USERAUTH->userid);
			setcookie("webaccess",$USERAUTH->webaccess);
		}
		else
		{  
			$user="";
			$function="autherror";
		}
	}
	else
	{  
		$user="";
		$function="autherror";
	}
	$USERAUTH->SetUserAuthErrorVariables();
//$USERAUTH->ShowVariables();
//exit(0);
  }  



/****************************************************

if($function=="userauth")
  {   
     $user="";
     if(isset($_POST["id"])) $id=$_POST["id"];
     $SAMSConf->groupauditor=UserAuth();
     $nick=$SAMSConf->domainusername;
     $time=time();
     if($SAMSConf->AUTHERRORRC==0||$time>$SAMSConf->AUTHERRORRT+60)
       {  
         if($time>$SAMSConf->AUTHERRORRT+60)
           {  
             setcookie("domainuser","$SAMSConf->domainusername");
             setcookie("gauditor","$SAMSConf->groupauditor");
             setcookie("userid","$SAMSConf->USERID");
             setcookie("webaccess","$SAMSConf->USERWEBACCESS");
             if(strlen($SAMSConf->domainusername)!=0)
			$num_rows=$DB->samsdb_query("UPDATE squiduser SET s_autherrorc='0',s_autherrort='0' WHERE s_user_id='$SAMSConf->USERID' ");
	       else  
	          {
	           $user="";
	           $function="autherror";
	           if($SAMSConf->AUTHERRORRC>=2)
			    $num_rows=$DB->samsdb_query("UPDATE squiduser SET s_autherrorc='0',s_autherrort='$time' WHERE s_user_id='$SAMSConf->USERID' ");
	           else
			    $num_rows=$DB->samsdb_query("UPDATE squiduser SET s_autherrorc=s_autherrorc+1,s_autherrort='0' WHERE s_user_id='$SAMSConf->USERID' ");
	          } 
               }   
         else
           {  
               $user="";
               $function="autherror";
           }
       }

     if($SAMSConf->groupauditor != "")
       {
         print("<SCRIPT>\n");
         print(" parent.lframe.location.href=\"lframe.php\"; \n");
	 print("</SCRIPT> \n");
       }
  }  

if($function=="nuserauth")
  {   
     $user="";
     if(isset($_POST["userid"])) $id=$_POST["userid"];
     if(isset($_POST["user"])) $nick=$_POST["user"];
     $SAMSConf->groupauditor=NotUsersTreeUserAuth();
     $nick=$SAMSConf->domainusername;
     $time=time();
     if($SAMSConf->AUTHERRORRC==0||$time>$SAMSConf->AUTHERRORRT+60)
       {  
         if($time>$SAMSConf->AUTHERRORRT+60)
           {  
             setcookie("domainuser","$SAMSConf->domainusername");
             setcookie("gauditor","$SAMSConf->groupauditor");
             setcookie("userid","$SAMSConf->USERID");
             setcookie("webaccess","$SAMSConf->USERWEBACCESS");
             if(strlen($SAMSConf->domainusername)!=0)
			$num_rows=$DB->samsdb_query("UPDATE squiduser SET s_autherrorc='0',s_autherrort='0' WHERE s_user_id='$SAMSConf->USERID' ");
	       else  
	          {
	           $user="";
	           $function="autherror";
	           if($SAMSConf->AUTHERRORRC>=2)
			    $num_rows=$DB->samsdb_query("UPDATE squiduser SET s_autherrorc='0',s_autherrort='$time' WHERE s_user_id='$SAMSConf->USERID' ");
	           else
			    $num_rows=$DB->samsdb_query("UPDATE squiduser SET s_autherrorc=s_autherrorc+1,s_autherrort='0' WHERE s_user_id='$SAMSConf->USERID' ");
	          } 
               }   
         else
           {  
               $user="";
               $function="autherror";
           }
       }

     if($SAMSConf->groupauditor != "")
       {
         print("<SCRIPT>\n");
         print(" parent.lframe.location.href=\"lframe.php\"; \n");
	 print("</SCRIPT> \n");
       }
  }  


***************************************************************/





//else
//  {
	$cookie_user="";
	$cookie_passwd="";
	$cookie_domainuser="";
	$cookie_gauditor="";
	if(isset($HTTP_COOKIE_VARS['user'])) $cookie_user=$HTTP_COOKIE_VARS['user'];
	if(isset($HTTP_COOKIE_VARS['passwd'])) $cookie_passwd=$HTTP_COOKIE_VARS['passwd'];
	if(isset($HTTP_COOKIE_VARS['domainuser'])) $cookie_domainuser=$HTTP_COOKIE_VARS['domainuser'];
	if(isset($HTTP_COOKIE_VARS['gauditor'])) $cookie_gauditor=$HTTP_COOKIE_VARS['gauditor'];
	if(isset($HTTP_COOKIE_VARS['userid'])) $SAMSConf->USERID=$HTTP_COOKIE_VARS['userid'];
	if(isset($HTTP_COOKIE_VARS['webaccess'])) $SAMSConf->USERWEBACCESS=$HTTP_COOKIE_VARS['webaccess'];
//             setcookie("userid","$SAMSConf->USERID");
//             setcookie("webaccess","$SAMSConf->USERWEBACCESS");
	if($SAMSConf->PHPVER<5)
	  {
		$SAMSConf->adminname=UserAuthenticate($cookie_user,$cookie_passwd);
		$SAMSConf->domainusername=$cookie_domainuser;
		$SAMSConf->groupauditor=$cookie_gauditor;
	  }  
	else
	  {
		$SAMSConf->adminname=UserAuthenticate($_COOKIE['user'],$_COOKIE['passwd']);
		$SAMSConf->domainusername=$_COOKIE['domainuser'];
		$SAMSConf->groupauditor=$_COOKIE['gauditor'];
		$SAMSConf->USERID=$_COOKIE['userid'];
		$SAMSConf->USERWEBACCESS=$_COOKIE['webaccess'];
	  }  
//  }

 $SAMSConf->access=UserAccess();
 //$SAMSConf->access=2;
if($gb!=1)
  { 
    print("<HTML><HEAD>");
    print("<META  content=\"text/html; charset=$CHARSET\" http-equiv='Content-Type'>");
    print("<META HTTP-EQUIV=\"expires\" CONTENT=\"THU, 01 Jan 1970 00:00:01 GMT\">");
    print("<META HTTP-EQUIV=\"pragma\" CONTENT=\"no-cache\">");
    print("<link rel=\"STYLESHEET\" type=\"text/css\" href=\"$SAMSConf->ICONSET/tree.css\">\n");

    print("</head>\n");
    print("<body LINK=\"#ffffff\" VLINK=\"#ffffff\">\n");//     if($autherrorc==1&&$autherrort>0)
    print("<center>\n");
  }
//echo "BD CONFIG: &$SAMSConf";

  if(strstr($filename,"proxy"))
	{
	require('./proxyclass.php');
	$PROXYConf=new SAMSPROXY($_GET["id"]);
	//$PROXYConf->PrintProxyClass();
	}

  if((strstr($filename,"userb")||strstr($filename,"usertray"))&&$function!="userauth")
	{
	 if(isset($_GET["id"])) $id=$_GET["id"];
	require('./userclass.php');
	$USERConf=new SAMSUSER($id);
	}

  if(strstr($filename,"trange")&&$function!="addtrangeform"&&$function!="addtrange")
	{
	require('./trangeclass.php');
	 if(isset($_GET["id"])) $id=$_GET["id"];
	$TRANGEConf=new SAMSTRANGE($id);
	//$PROXYConf->PrintProxyClass();
	}

  if(strstr($filename,"shablon")&&$function!="newshablonform"&&$function!="addshablon")
	{
	 if(isset($_GET["id"])) $id=$_GET["id"];
	require('./shablonclass.php');
	$SHABLONConf=new SAMSSHABLON($id);
	//$PROXYConf->PrintProxyClass();
	}

  if($user=="exe"&&$function!="setcookie"&&$function!="userauth"&&$function!="nuserauth")
     {
	if(stristr($filename,".php" )==FALSE) 
  	{
    		$filename="";
  	}
  	$req="src/$filename";
  	if(strlen($req)>4)
    	{
      		require($req);
    	}
  	if(strlen($function)>0)
       		$function();

     } 

  if ($module !== null) {
	$manager = new PluginManager($DB, 1, $SAMSConf);
	print ($manager->dispatch($module, $function));
  }
//exit(0);

if($function=="nuserauth"|| $function=="userauth")
  {
     print("<SCRIPT>\n");
     print("        parent.lframe.location.href=\"lframe.php\";\n");
     print("        parent.tray.location.href=\"tray.php?show=exe&filename=usertray.php&function=usertray&id=$USERAUTH->userid\";\n");
     print("</SCRIPT> \n");
  }   
if($function=="setcookie")
  {
     print("<SCRIPT>\n");
     print("        parent.lframe.location.href=\"lframe.php\";\n");
     print("        parent.tray.location.href=\"tray.php?show=exe&filename=admintray.php&function=admintray\";\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&function=sysinfo&filename=configtray.php\";\n");    
     print("</SCRIPT> \n");
  }   
if($function=="autherror")
  {
     print("<h1><FONT COLOR=\"RED\">Authentication ERROR</FONT></h1> \n");
     $time2=60 - ($time - $autherrort);
     if($autherrorc==0&&$time<$autherrort+60)
       {
          print("<h2>next logon after $time2 second</h2> \n");
       }   
  }   
//$SAMSConf->PrintSAMSSettings();
print("</center>\n");
print("</body></html>\n");

?>
