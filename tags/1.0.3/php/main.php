<?php
/*      SAMS (Squid Account Management System
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



function BlankPage()
{

}
 
class DATE
{
  var $sday,$smon,$syea,$shou,$eday,$emon,$eyea,$ehou,$sdate,$edate;
  function DATE($mas, $sdate, $edate)
    {
       if(strlen($sdate)<=1&&strlen($edate)<=1)
		list($this->sday,$this->smon,$this->syea,$this->shou,$this->eday,$this->emon,$this->eyea,$this->ehou)=$mas;
       else
         {
           list($this->sday,$this->smon,$this->syea,$this->shou,$this->eday,$this->emon,$this->eyea,$this->ehou)=$mas;
           $this->sdate=$sdate;
           $this->syea=strtok($sdate,"-");
           $this->smon=strtok("-");
           $this->sday=strtok("-");
	   
	   $this->edate=$edate;
           $this->eyea=strtok($edate,"-");
           $this->emon=strtok("-");
           $this->eday=strtok("-");
	 }  
          
    }
  function BeginDate()
    {
       return("$this->sday.$this->smon.$this->syea"); 
    }
  function EndDate()
    {
       return("$this->eday.$this->emon.$this->eyea");
    }
  function sdate()
    {
       return("$this->syea-$this->smon-$this->sday");
    }
  function edate()
    {
       return("$this->eyea-$this->emon-$this->eday");
    }
}
 
global $DATE;
global $SAMSConf;
require('./mysqltools.php');
//LoadConfig();
 
require('./src/auth.php');
require('./src/user.php');
require('./src/usertray.php');
require('./src/grouptray.php');
require('./src/locallisttray.php');
require('./src/deniedlisttray.php');
require('./src/allowlisttray.php');
require('./src/redirlisttray.php');
require('./src/contextlisttray.php');
require('./src/squidtray.php');
require('./src/backuptray.php');
require('./src/userstray.php');
require('./src/admintray.php');
require('./src/shablontray.php');
require('./src/monitortray.php');
require('./src/logtray.php');
require('./src/logfunction.php');
require('./src/dbtray.php');
require('./src/filelisttray.php');

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

if(isset($_GET["SDay"])) $sday=$_GET["SDay"];
if(isset($_GET["EDay"])) $eday=$_GET["EDay"];
if(isset($_GET["SMon"])) $smon=$_GET["SMon"];
if(isset($_GET["EMon"])) $emon=$_GET["EMon"];
if(isset($_GET["SYea"])) $syea=$_GET["SYea"];
if(isset($_GET["EYea"])) $eyea=$_GET["EYea"];
if(isset($_GET["SHou"])) $shou=$_GET["SHou"];
if(isset($_GET["EHou"])) $ehou=$_GET["EHou"];

if(isset($_GET["show"])) $user=$_GET["show"];
if(isset($_GET["function"])) $function=$_GET["function"];
if(isset($_GET["filename"])) $filename=$_GET["filename"];

if(isset($_POST["show"])) $user=$_POST["show"];
if(isset($_POST["function"])) $function=$_POST["function"];
if(isset($_POST["filename"])) $filename=$_POST["filename"];
if(isset($_POST["username"])) $username=$_POST["username"];
if(isset($_POST["userid"])) $userid=$_POST["userid"];
if(isset($_POST["id"])) $userid=$_POST["id"];
if(isset($_POST["usergroup"])) $usergroup=$_POST["usergroup"];
if(isset($_POST["usernick"])) $usernick=$_POST["usernick"];
if(isset($_POST["userid"])) $userid=$_POST["userid"];

if(isset($_GET["username"])) $username=$_GET["username"];
if(isset($_GET["usergroup"])) $usergroup=$_GET["usergroup"];
if(isset($_GET["usernick"])) $usernick=$_GET["usernick"];
if(isset($_GET["userid"])) $userid=$_GET["userid"];
if(isset($_GET["id"])) $userid=$_GET["id"];
if(isset($_GET["gb"])) $gb=$_GET["gb"];

if(isset($_GET["sdate"])) $sdate=$_GET["sdate"];
if(isset($_GET["edate"])) $edate=$_GET["edate"];

$reloadleftframe=0;
$settime=0;

if($ehou==0)
   $ehou=24;
if($shou==0)
   $shou=0;

$DATE=new DATE(Array($sday,$smon,$syea,$shou,$eday,$emon,$eyea,$ehou), $sdate, $edate);
$SAMSConf=new SAMSCONFIG();


//if(isset($_GET["setup"])) $setup=$_GET["setup"];
//if($setup=="setup")
//  {
//	require("src/createdb.php");
//	$function();
//  }

$lang="./lang/lang.$SAMSConf->LANG";
require($lang);

$WI=1;
  
if($function=="setcookie")
  {
     $time=time();
     $result=mysql_query("SELECT * FROM sams ");
     $row=mysql_fetch_array($result);
     $autherrorc=$row['autherrorc'];
     $autherrort=$row['autherrort'];
     if($autherrorc==0||$time>$autherrort+60)
       {  
         if($time>$autherrort+60)
           {  
             $newpasswd=crypt($userid,mysql_result(mysql_query("SELECT pass FROM passwd WHERE user='$username' "),0));
//echo "username=$username passwd=$userid crypt=$newpasswd";
//exit(0);
             setcookie("user","$username");
             setcookie("passwd","$newpasswd");
             $SAMSConf->adminname=UserAuthenticate($username,$newpasswd);
             if(strlen($SAMSConf->adminname)!=0)
	       {
	          $result=mysql_query("UPDATE sams SET autherrorc='0',autherrort='0' ");	        
	       }
	     else  
	        {
	           $user="";
	           $function="autherror";
	           if($autherrorc>=2)
	                    $result=mysql_query("UPDATE sams SET autherrorc='0',autherrort='$time' ");         
	           else
	                    $result=mysql_query("UPDATE sams SET autherrorc=autherrorc+1,autherrort='0' ");        
	        } 
	    }   
         else
           {  
               $user="";
               $function="autherror";
           }
       }
     $result=mysql_query("SELECT * FROM sams ");
     $row=mysql_fetch_array($result);
     $autherrorc=$row['autherrorc'];
     $autherrort=$row['autherrort'];
  }   
if($function=="userauth")
  {   
     if(isset($_GET["id"])) $id=$_GET["id"];
     if(isset($_POST["id"])) $id=$_POST["id"];
     $SAMSConf->groupauditor=UserAuth();

     $time=time();
     $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$id\" ");
     $row=mysql_fetch_array($result);
     $autherrorc=$row['autherrorc'];
     $autherrort=$row['autherrort'];
     if($autherrorc==0||$time>$autherrort+60)
       {  
         if($time>$autherrort+60)
           {  
             setcookie("domainuser","$SAMSConf->domainusername");
             setcookie("gauditor","$SAMSConf->groupauditor");
             if(strlen($SAMSConf->domainusername)!=0)
                        $result=mysql_query("UPDATE squidusers SET autherrorc=\"0\",autherrort=\"0\" WHERE id=\"$id\" ");
	     else  
	        {
	           $user="";
	           $function="autherror";
	           if($autherrorc>=2)
                            $result=mysql_query("UPDATE squidusers SET autherrorc=\"0\",autherrort=\"$time\" WHERE id=\"$id\" ");
	           else
                            $result=mysql_query("UPDATE squidusers SET autherrorc=autherrorc+1,autherrort=\"0\" WHERE id=\"$id\" ");
	        } 
            }   
         else
           {  
               $user="";
               $function="autherror";
           }
       }
     $result=mysql_query("SELECT * FROM squidusers WHERE id=\"$id\" ");
     $row=mysql_fetch_array($result);
     $autherrorc=$row[autherrorc];
     $autherrort=$row[autherrort];
//echo "=$id=$SAMSConf->domainusername=<BR>";
//exit(0);
  }  

if($function=="nuserauth")
  {   
     $user="";
     if(isset($_POST["userid"])) $id=$_POST["userid"];
     if(isset($_POST["user"])) $nick=$_POST["user"];
     $SAMSConf->groupauditor=NotUsersTreeUserAuth();
     $nick=$SAMSConf->domainusername;
     
     $time=time();
     $result=mysql_query("SELECT * FROM squidusers WHERE nick=\"$nick\" ");
     $row=mysql_fetch_array($result);
     $autherrorc=$row[autherrorc];
     $autherrort=$row[autherrort];
     if($autherrorc==0||$time>$autherrort+60)
       {  
         if($time>$autherrort+60)
           {  
             setcookie("domainuser","$SAMSConf->domainusername");
             setcookie("gauditor","$SAMSConf->groupauditor");
             if(strlen($SAMSConf->domainusername)!=0)
                        $result=mysql_query("UPDATE squidusers SET autherrorc=\"0\",autherrort=\"0\" WHERE nick=\"$nick\" ");
	     else  
	        {
	           $user="";
	           $function="autherror";
	           if($autherrorc>=2)
                            $result=mysql_query("UPDATE squidusers SET autherrorc=\"0\",autherrort=\"$time\" WHERE nick=\"$nick\" ");
	           else
                            $result=mysql_query("UPDATE squidusers SET autherrorc=autherrorc+1,autherrort=\"0\" WHERE nick=\"$nick\" ");
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
     $result=mysql_query("SELECT * FROM squidusers WHERE nick=\"$nick\" ");
     $row=mysql_fetch_array($result);
     $autherrorc=$row[autherrorc];
     $autherrort=$row[autherrort];
     $id=$row['id'];
     $usergroup=$row['group'];
     //print("<B></B><BR>");
  }  

  
if($function=="logoff")
  {
     setcookie("user","");
     setcookie("passwd","");
     setcookie("domainuser","");
     setcookie("gauditor","");
     $function="setcookie";
  }   

	$cookie_user="";
	$cookie_passwd="";
	$cookie_domainuser="";
	$cookie_gauditor="";
	if(isset($HTTP_COOKIE_VARS['user'])) $cookie_user=$HTTP_COOKIE_VARS['user'];
	if(isset($HTTP_COOKIE_VARS['passwd'])) $cookie_passwd=$HTTP_COOKIE_VARS['passwd'];
	if(isset($HTTP_COOKIE_VARS['domainuser'])) $cookie_domainuser=$HTTP_COOKIE_VARS['domainuser'];
	if(isset($HTTP_COOKIE_VARS['gauditor'])) $cookie_gauditor=$HTTP_COOKIE_VARS['gauditor'];

 if($SAMSConf->PHPVER<5)
   {
//     $SAMSConf->adminname=UserAuthenticate($HTTP_COOKIE_VARS['user'],$HTTP_COOKIE_VARS['passwd']);
//     $SAMSConf->domainusername=$HTTP_COOKIE_VARS['domainuser'];
//     $SAMSConf->groupauditor=$HTTP_COOKIE_VARS['gauditor'];
     $SAMSConf->adminname=UserAuthenticate($cookie_user,$cookie_passwd);
     $SAMSConf->domainusername=$cookie_domainuser;
     $SAMSConf->groupauditor=$cookie_gauditor;
   }  
 else
   {
     $SAMSConf->adminname=UserAuthenticate($_COOKIE['user'],$_COOKIE['passwd']);
     $SAMSConf->domainusername=$_COOKIE['domainuser'];
     $SAMSConf->groupauditor=$_COOKIE['gauditor'];
   }  
 
 $SAMSConf->access=UserAccess();

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
//  echo "<h1>SHUTDOWN=$SAMSConf->SHUTDOWN</h1>";
  }
if(stristr($filename,".php" )==FALSE) 
  {
    $filename="";
  }

/*
printf("language=$SAMSConf->LANG<BR>");
printf("show=$user<BR>");
printf("function=$function<BR>");
printf("filename=$filename<BR>");
printf("id=$id<BR>");
print("domainusername=$domainusername groupauditor=$groupauditor<BR>");
print("access=$SAMSConf->access<BR>");

printf("username=$username<BR>");
printf("passwd=$userid<BR>");
printf("newpasswd=$newpasswd<BR>");
printf("cookie: <BR>");
printf("user=$HTTP_COOKIE_VARS[user]<BR>");
printf("passwd=$HTTP_COOKIE_VARS[passwd]<BR>");
printf("domainuser from cookies=$HTTP_COOKIE_VARS[domainuser]<BR>");
*/


if($user=="exe"&&$function!="setcookie")
  {
	$req="src/$filename";
        if(strlen($req)>4)
           require($req);
	if (is_callable($function))
           call_user_func($function);
  }
// reload LEFT & DOWN frames
if($function=="setcookie")
  {
     print("<SCRIPT>\n");
     print("        parent.lframe.location.href=\"lframe.php\";\n");
     print("        parent.tray.location.href=\"tray.php?show=admintray\";\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&function=sysinfo&filename=configtray.php\";\n");    
     print("</SCRIPT> \n");
  }   
if($function=="webinterfacereconfig")
  {
     print("<SCRIPT>\n");
     print("        parent.lframe.location.href=\"lframe.php\";\n");
     print("        parent.tray.location.href=\"tray.php?show=exe&function=webconfigtray&filename=webconfigtray.php\";\n");
     print("</SCRIPT> \n");
  }   

if($function=="userauth")
  {
     print("<SCRIPT>\n");
     print("        parent.tray.location.href=\"tray.php?show=usertray&userid=$id&usergroup=$usergroup\";\n");
     print("</SCRIPT> \n");
  }   
if($function=="nuserauth")
  {
     print("<SCRIPT>\n");
     print("        parent.tray.location.href=\"tray.php?show=usertray&userid=$id&usergroup=$usergroup\";\n");
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
/*
if (function_exists('ini_get'))
  {
    $safe_switch = @ini_get("safe_mode") ? 1 : 0;
    echo "safe_switch = $safe_switch<BR>";
  }
*/
print("</center>\n");
print("</body></html>\n");



?>


