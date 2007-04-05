<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function about()
{
  global $SAMSConf;
 
  print("<TABLE WIDTH=\"80%\" BORDER=0 BGCOLOR=\"beige\" >");
  print("<TR><TD ALIGN=CENTER><IMG SRC=\"$SAMSConf->ICONSET/proxy_big.gif\">");
  print("<TD ALIGN=CENTER><P><H1>SAMS</H1>");
  print("<H2>Squid Account Management System</H2>");
  print("Copyright (C) 2003 - 2005 Dmitry Chemerik");
  print("<BR>http://sams.perm.ru");
  print("<BR>http://sams.irc.perm.ru");
  print("<P>email: chemerik@mail.ru <P></TABLE>");
 
}
 
 
function db_connect($basename)
{
  global $SAMSConf;
  
//  echo "<BR>DB_TEST $basename  $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD";
  
  $link=@mysql_connect($SAMSConf->MYSQLHOSTNAME,$SAMSConf->MYSQLUSER,$SAMSConf->MYSQLPASSWORD) || die (mysql_error());
  if($link && mysql_select_db($basename))
    return($link);
  return(FALSE);
}
 
 
class SAMSCONFIG
{
//  var $SAMSPATH;
  var $access;
  var $groupauditor;
  var $domainusername;
  var $adminname;
  var $DELAYPOOL;
  var $USERACCESS;
  var $URLACCESS;
  var $MYSQLDATABASE;    
  var $SQUIDCTRLDATABASE;    
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
//         if(!strcasecmp($str2,"SAMSPATH" ))               $this->MYSQLDATABASE=trim(strtok("="));
         if(!strcasecmp($str2,"SAMS_DB" ))               $this->MYSQLDATABASE=trim(strtok("="));
         if(!strcasecmp($str2,"SQUID_DB" ))              $this->SQUIDCTRLDATABASE=trim(strtok("="));
         if(!strcasecmp($str2,"MYSQLHOSTNAME" ))         $this->MYSQLHOSTNAME=trim(strtok("="));
         if(!strcasecmp($str2,"MYSQLUSER" ))   	         $this->MYSQLUSER=trim(strtok("="));
         if(!strcasecmp($str2,"MYSQLPASSWORD" ))         $this->MYSQLPASSWORD=trim(strtok("="));
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
         if(!strcasecmp($str2,"LDAPBASEDN" ))
           {
              $str2=trim(strtok($string,"="));
	      $LDAPBASEDN_=trim(strtok("="));
	      $LDAPDOMAIN=$LDAPBASEDN_;
              $LDAPBASEDN1=strtok($LDAPBASEDN_,".");
              $LDAPBASEDN2=strtok(".");
	      $this->LDAPDOMAIN=$LDAPDOMAIN;
	      $this->LDAPBASEDN="DC=$LDAPBASEDN1,DC=$LDAPBASEDN2";
           }
       }
      fclose($finp);
    }
  function ReadSAMSSettings()
    {
      $result=mysql_query("SELECT * FROM ".$this->MYSQLDATABASE.".sams");
      $row=mysql_fetch_array($result);
      
      $this->REDIRECTOR=$row['redirector'];
      $this->DELAYPOOL=$row['delaypool'];
      $this->AUTH=$row['auth'];
      $this->WBINFOPATH=$row['wbinfopath'];
      $this->NTLMDOMAIN=$row['ntlmdomain'];
      $this->DEFAULTDOMAIN=$row['defaultdomain'];
      $this->realtraffic=$row['realsize'];
      $this->SQUIDBASE=$row['squidbase'];
      $this->SEPARATOR=$row['separator'];
 
      $result=mysql_query("SELECT * FROM ".$this->MYSQLDATABASE.".globalsettings");
      $row=mysql_fetch_array($result);
      $this->LANG=$row['lang'];
      $this->ICONSET="icon/$row[iconset]";
      $this->USERACCESS=$row['useraccess'];
      $this->URLACCESS=$row['urlaccess'];
      $this->SHOWUTREE=$row['showutree'];
      $this->SHOWNAME=$row['showname'];
      $this->KBSIZE=$row['kbsize'];
      $this->MBSIZE=$row['mbsize'];
      $this->SHOWGRAPH=$row['showgraph'];
      $this->PDFLIB=$row['createpdf'];

      $result=mysql_query("SELECT MAX(id) FROM ".$this->MYSQLDATABASE.".proxyes ");
      $row=mysql_fetch_array($result);
      $this->PROXYCOUNT=$row[0]+1;
      
      $result=mysql_query("USE samstraf");
      if($result==FALSE)
        $this->SWITCHTO=0;
      else  
        $this->SWITCHTO=1;
      mysql_query("USE ".$this->MYSQLDATABASE);
    }
  function SAMSCONFIG()
    {
      require('./config.php');      
      $this->ReadSAMSConfFile($configfile);
      
      $link=@mysql_connect($this->MYSQLHOSTNAME,$this->MYSQLUSER,$this->MYSQLPASSWORD) || die (mysql_error());
      if($link && mysql_select_db($this->MYSQLDATABASE)==FALSE)
        echo "Error connection to database<BR>";
      $link=@mysql_connect($this->MYSQLHOSTNAME,$this->MYSQLUSER,$this->MYSQLPASSWORD) || die (mysql_error());
      if($link && mysql_select_db($this->SQUIDCTRLDATABASE)==FALSE)
        echo "Error connection to database<BR>";
      $this->ReadSAMSSettings();
    }

  function LoadConfig()
    {
      require('./config.php');      
      $this->ReadSAMSConfFile($configfile);
      
      $link=@mysql_connect($this->MYSQLHOSTNAME,$this->MYSQLUSER,$this->MYSQLPASSWORD) || die (mysql_error());
      if($link && mysql_select_db($this->MYSQLDATABASE)==FALSE)
        echo "Error connection to database<BR>";
      $link=@mysql_connect($this->MYSQLHOSTNAME,$this->MYSQLUSER,$this->MYSQLPASSWORD) || die (mysql_error());
      if($link && mysql_select_db($this->SQUIDCTRLDATABASE)==FALSE)
        echo "Error connection to database<BR>";
      $this->ReadSAMSSettings();
    }
      
  function PrintSAMSSettings()
    {
      echo "adminname = $this->adminname<BR>";
      echo "groupauditor = $this->groupauditor<BR>";
      echo "access = $this->access<BR>";
      echo "domainusername = $this->domainusername<BR>";
      echo "MYSQLDATABASE = $this->MYSQLDATABASE<BR>";    
      echo "SQUIDCTRLDATABASE = $this->SQUIDCTRLDATABASE<BR>";    
      echo "MYSQLHOSTNAME = $this->MYSQLHOSTNAME<BR>";    
      echo "MYSQLUSER = $this->MYSQLUSER<BR>";        
      echo "DELAYPOOL = $this->DELAYPOOL<BR>";
      echo "USERACCESS = $this->USERACCESS<BR>";
      echo "URLACCESS = $this->URLACCESS<BR>";
      echo "MYSQLPASSWORD = $this->MYSQLPASSWORD<BR>";    
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

 
 
function ReturnTrafficFormattedSize($size)
{
  global $SAMSConf;
    $gsize=floor($size/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
    $ostatok=$size-$gsize*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE;
    $msize=floor($ostatok/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
    $ostatok=$ostatok-$msize*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE;
    $ksize=floor($ostatok/$SAMSConf->KBSIZE);         
  if($gsize>0)
    $str="$gsize Gb";
  $str="$str $msize Mb $ksize kb ";
  return($str);
} 

function PrintTrafficSize($size)
{
  $str=ReturnTrafficFormattedSize($size);
  print($str);
} 
 
 
function ReturnGroupNick($groupname)
{
    global $SAMSConf;
    
//  db_connect($SAMSConf->SQUIDCTRLDATABASE) or exit();
//  mysql_select_db($SAMSConf->MYSQLDATABASE);
  $result=mysql_query("SELECT * FROM ".$SAMSConf->MYSQLDATABASE.".groups WHERE name=\"$groupname\"");
  $row=mysql_fetch_array($result);
  return($row['nick']);
}
 
function TestWI()
{
  global $SAMSConf;

//  print("<h2>TestWI $WI</h2>");
  if($SAMSConf->access==0&&strlen($SAMSConf->domainusername)==0)
    exit;
}

function DateTimeSelect($id,$str)
{
  global $SAMSConf;
  
 // print("NewDateSelect LANG=$LANG");
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $year=strftime("%Y");
  $mon=strftime("%m");
  $day=strftime("%d");

print("<TABLE border=0 width=\"100%\" >\n");
print("  <TR>\n");
print("  <TD>\n");
print("      <INPUT TYPE=\"HIDDEN\" NAME=\"SHou\" id=SHou value=\"0\">\n");
print("      <INPUT TYPE=\"HIDDEN\" NAME=\"EHou\" id=EHou value=\"24\">\n");
print("      <TABLE >\n");
print("        <TR>\n");
print("        <TD><B>$DateTimeSelect_mysqltools_1:</B>\n");
print("        <TD><SELECT NAME=\"SDay\"> \n");
for($i=1;$i<32;$i++)
   {
     if($day==$i)
        print("	       <OPTION value=$i  SELECTED>$i\n");
     else
        print("	       <OPTION value=$i>$i\n");
   }
print("	       </SELECT> \n");
print("     <SELECT NAME=\"SMon\" size=1> \n");
for($i=1;$i<13;$i++)
   {
     if($mon==$i)
        print("	       <OPTION value=$i SELECTED>$month[$i]\n");
     else
        print("	       <OPTION value=$i>$month[$i]\n");
   }
print("	       </SELECT> \n");
print("     <SELECT NAME=\"SYea\" size=1> \n");
for($i=2001;$i<2010;$i++)
   {
     if($year==$i)
        print("	       <OPTION value=$i SELECTED>$i\n");
      else
        print("	       <OPTION value=$i>$i\n");
   }
print("     </SELECT> \n");
print("        <TD><INPUT TYPE=\"SUBMIT\" NAME=\"sbutton\" id=sbutton value=\"$mysqltools_dateselect2\" >\n");
//print("</TABLE>\n");


//print("<TABLE border=0 width=\"100%\" >\n");
print("  <TR>\n");
print("  <TD><B>$DateTimeSelect_mysqltools_2:</B>\n");
print("     <TD> $DateTimeSelect_mysqltools_3 <SELECT NAME=\"SHou\" size=0> \n");
for($i=0;$i<24;$i++)
   {
        print("	       <OPTION value=$i>$i\n");
   }
print("     </SELECT>  \n");

print("      $DateTimeSelect_mysqltools_4 <SELECT NAME=\"EHou\" size=0> \n");
for($i=0;$i<25;$i++)
   {
     if($i==24)
        print("	       <OPTION value=$i SELECTED>$i\n");
      else
        print("	       <OPTION value=$i>$i\n");
   }
print("     </SELECT> $DateTimeSelect_mysqltools_5 \n");


print("</TABLE>\n");
//print("<IMG SRC=\"white_data/empty.gif\" onload=SetDateValues()>\n");

}


function NewDateSelect($id,$str)
{
  global $SAMSConf;
  
 // print("NewDateSelect LANG=$LANG");
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $year=strftime("%Y");
  $mon=strftime("%m");
  $day=strftime("%d");

print("<TABLE border=0 width=\"100%\" >\n");
print("  <TR>\n");
print("  <TD>\n");
print("      <INPUT TYPE=\"HIDDEN\" NAME=\"SHou\" id=SHou value=\"0\">\n");
print("      <INPUT TYPE=\"HIDDEN\" NAME=\"EHou\" id=EHou value=\"24\">\n");
print("      <TABLE >\n");
print("        <TR>\n");
print("        <TD><B>$mysqltools_dateselect1</B>\n");
print("        <TD><SELECT NAME=\"SDay\"> \n");
for($i=1;$i<32;$i++)
   {
     print("	       <OPTION value=$i>$i\n");

   }
print("	       </SELECT> \n");
print("     <SELECT NAME=\"SMon\" size=1> \n");
for($i=1;$i<13;$i++)
   {
     if($mon==$i)
        print("	       <OPTION value=$i SELECTED>$month[$i]\n");
     else
        print("	       <OPTION value=$i>$month[$i]\n");
   }
print("	       </SELECT> \n");
print("     <SELECT NAME=\"SYea\" size=1> \n");
for($i=2001;$i<2010;$i++)
   {
     if($year==$i)
        print("	       <OPTION value=$i SELECTED>$i\n");
      else
        print("	       <OPTION value=$i>$i\n");
   }
print("     </SELECT> \n");
//print("        <TD><INPUT TYPE=\"SUBMIT\" NAME=\"sbutton\" id=sbutton value=\"$mysqltools_dateselect2\"  onclick=SetSelected() //>\n");
print("        <TD><INPUT TYPE=\"SUBMIT\" NAME=\"sbutton\" id=sbutton value=\"$mysqltools_dateselect2\" >\n");
print("  <TR>\n");
print("  <TD>\n");
print("        <TR>\n");
print("        <TD><B>$mysqltools_dateselect3</B>\n");
print("        <TD><SELECT NAME=\"EDay\" size=1 > \n");
for($i=1;$i<32;$i++)
   {
     if($day==$i)
        print("	       <OPTION value=$i  SELECTED>$i\n");
     else
        print("	       <OPTION value=$i>$i\n");
   }

print("	       </SELECT> \n");
print("          <SELECT NAME=\"EMon\" size=1 >  \n");
for($i=1;$i<13;$i++)
   {
     if($mon==$i)
        print("	       <OPTION value=$i SELECTED>$month[$i]\n");
     else
        print("	       <OPTION value=$i>$month[$i]\n");
   }
print("              </SELECT> \n");
print("          <SELECT NAME=\"EYea\" size=1 > \n");
for($i=2001;$i<2010;$i++)
   {
     if($year==$i)
        print("	       <OPTION value=$i SELECTED>$i\n");
      else
        print("	       <OPTION value=$i>$i\n");
   }
print("              </SELECT> \n");
print("     </TABLE>\n");
print("<TABLE WIDTH=\"95%\" >\n");
if($id==1)
  {
    print("        <TR>");
    print("        <TD><B>$str</B>");
    print("        <TD>  <INPUT TYPE=\"TEXT\" NAME=\"size\" SIZE=30>");
  }
if($id==2)
  {
    print("        <TR>");
    print("        <TD WIDTH=\"60%\"><B>$str</B>");
    print("        <TD ALIGN=\"left\">  <INPUT TYPE=\"CHECKBOX\" NAME=\"size\">");
  }
if($id==3)
  {
    print("        <TR>");
//    print("        <TD ID=str1><B>$str</B>");
    print("        <TD ID=str1><B>$mysqltools_dateselect4:</B>");
    print("        <TR><TD>$mysqltools_dateselect5");
    print("        <TD>  <INPUT TYPE=\"RADIO\" NAME=\"check\" VALUE=1 CHECKED>");
    print("        <TD>  <INPUT TYPE=\"TEXT\" NAME=\"size\" SIZE=30>");
    print("        <TR><TD>$mysqltools_dateselect6");
    print("        <TD>  <INPUT TYPE=\"RADIO\" NAME=\"check\" VALUE=2>");

  }
print("     </TABLE>\n");
print("</TABLE>\n");
//print("<IMG SRC=\"white_data/empty.gif\" onload=SetDateValues()>\n");

}


function DateSelect($id,$str)
{
  global $LANG;
  $lang="./lang/lang.$LANG";
  require($lang);

  $year=strftime("%Y");
  $mon=strftime("%m");
  $day=strftime("%d");

print("<TABLE border=0 width=\"100%\" onafterupdate=SetDateValues() onfocus=SetSelected()>\n");
print("  <TR>\n");
print("  <TD>\n");
print("      <FORM name=\"DataForm\" ACTION=\"main.php\" >\n");
print("      <INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show>\n");
print("      <INPUT TYPE=\"HIDDEN\" NAME=\"username\" id=UserName>\n");
print("      <INPUT TYPE=\"HIDDEN\" NAME=\"userdomain\" id=UserDomain>\n");
print("      <INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" id=GroupName>\n");
print("      <INPUT TYPE=\"HIDDEN\" NAME=\"groupnick\" id=GroupNick>\n");
print("      <INPUT TYPE=\"HIDDEN\" NAME=\"SHou\" id=SHou value=\"0\">\n");
print("      <INPUT TYPE=\"HIDDEN\" NAME=\"EHou\" id=EHou value=\"24\">\n");
print("      <TABLE >\n");
print("        <TR>\n");
print("        <TD><B>$mysqltools_dateselect1</B>\n");
print("        <TD><SELECT NAME=\"SDay\"> \n");
for($i=1;$i<32;$i++)
   {
     print("	       <OPTION value=$i>$i\n");

   }
print("	       </SELECT> \n");
print("     <SELECT NAME=\"SMon\" size=1> \n");
for($i=1;$i<13;$i++)
   {
     if($mon==$i)
        print("	       <OPTION value=$i SELECTED>$month[$i]\n");
     else
        print("	       <OPTION value=$i>$month[$i]\n");
   }
print("	       </SELECT> \n");
print("     <SELECT NAME=\"SYea\" size=1> \n");
for($i=2001;$i<2010;$i++)
   {
     if($year==$i)
        print("	       <OPTION value=$i SELECTED>$i\n");
      else
        print("	       <OPTION value=$i>$i\n");
   }
print("     </SELECT> \n");
print("        <TD><INPUT TYPE=\"SUBMIT\" NAME=\"sbutton\" id=sbutton value=\"$mysqltools_dateselect2\"  onclick=SetSelected() >\n");
print("  <TR>\n");
print("  <TD>\n");
print("        <TR>\n");
print("        <TD><B>$mysqltools_dateselect3</B>\n");
print("        <TD><SELECT NAME=\"EDay\" size=1 > \n");
for($i=1;$i<32;$i++)
   {
     if($day==$i)
        print("	       <OPTION value=$i  SELECTED>$i\n");
     else
        print("	       <OPTION value=$i>$i\n");
   }

print("	       </SELECT> \n");
print("          <SELECT NAME=\"EMon\" size=1 >  \n");
for($i=1;$i<13;$i++)
   {
     if($mon==$i)
        print("	       <OPTION value=$i SELECTED>$month[$i]\n");
     else
        print("	       <OPTION value=$i>$month[$i]\n");
   }
print("              </SELECT> \n");
print("          <SELECT NAME=\"EYea\" size=1 > \n");
for($i=2001;$i<2010;$i++)
   {
     if($year==$i)
        print("	       <OPTION value=$i SELECTED>$i\n");
      else
        print("	       <OPTION value=$i>$i\n");
   }
print("              </SELECT> \n");
print("     </TABLE>\n");
print("<TABLE WIDTH=\"95%\" >\n");
if($id==1)
  {
    print("        <TR>");
    print("        <TD><B>$str</B>");
    print("        <TD>  <INPUT TYPE=\"TEXT\" NAME=\"size\" SIZE=30>");
  }
if($id==2)
  {
    print("        <TR>");
    print("        <TD WIDTH=\"60%\"><B>$str</B>");
    print("        <TD ALIGN=\"left\">  <INPUT TYPE=\"CHECKBOX\" NAME=\"size\">");
  }
if($id==3)
  {
    print("        <TR>");
//    print("        <TD ID=str1><B>$str</B>");
    print("        <TD ID=str1><B>$mysqltools_dateselect4:</B>");
    print("        <TR><TD>$mysqltools_dateselect5");
    print("        <TD>  <INPUT TYPE=\"RADIO\" NAME=\"check\" VALUE=1 CHECKED>");
    print("        <TD>  <INPUT TYPE=\"TEXT\" NAME=\"size\" SIZE=30>");
    print("        <TR><TD>$mysqltools_dateselect6");
    print("        <TD>  <INPUT TYPE=\"RADIO\" NAME=\"check\" VALUE=2>");

  }
print("     </TABLE>\n");
print("</TABLE>\n");
//print("<IMG SRC=\"white_data/empty.gif\" onload=SetDateValues()>\n");

}




function ATableCell($data,$url)
{
  //print("<TD bgcolor=blanchedalmond align=right><font size=-1>");
  print("<TD align=right><font size=-1>");
  print("<A HREF=\"$url\">$data</A></TD>"); 
}
function TableCell($data)
{
//  print("<TD bgcolor=blanchedalmond align=right><font size=-1>");
  print("<TD  NOWRAP>");
  print("&nbsp;$data&nbsp;</TD>"); 
}
function RTableCell($data,$percent)
{
  print("<TD WIDTH=\"$percent%\" align=right NOWRAP>");
  print("&nbsp;$data&nbsp;</TD>"); 
}
function LTableCell($data,$percent)
{
  print("<TD WIDTH=\"$percent%\" align=left NOWRAP>");
  print("&nbsp;$data&nbsp;</TD>"); 
}
function RBTableCell($data,$percent)
{
  print("<TD WIDTH=\"$percent%\" align=right NOWRAP>");
  print("&nbsp;<B>$data&nbsp;</TD>"); 
}
function LBTableCell($data,$percent)
{
  print("<TD WIDTH=\"$percent%\" align=left NOWRAP>");
  print("&nbsp;<B>$data&nbsp;</TD>"); 
}




function GraphButton($url,$target,$img_small,$img_big,$title)
{
  global $SAMSConf;

  print("<A HREF=\"$url\" target=\"$target\">\n");
  print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/$img_small\" BORDER=0 \n ");
  print("TITLE=\"$title\" border=0\n");
  print("onmouseover=\"this.src='$SAMSConf->ICONSET/$img_big'\" \n");
  print("onmouseout= \"this.src='$SAMSConf->ICONSET/$img_small'\" \n");
  print("</A>\n");
}


function PageTop($imgname,$text)
{
  global $SAMSConf;
  print("<CENTER>\n");
  print("<TABLE WIDTH=\"95%\" border=0>\n");
  print("<TR>\n");
  print("<TD WIDTH=\"10%\"  valign=\"middle\">\n");
  print("<img src=\"$SAMSConf->ICONSET/$imgname\" align=\"RIGHT\" valign=\"middle\" >\n");
  print("<TD  valign=\"middle\">\n");
  print("<h2  align=\"CENTER\">$text</h2>\n");
  print("</TABLE>\n");
  print("</CENTER>\n");
  print("<BR>\n");
}

function TempName()
{
//  $str=tempnam("","");
//  unlink($str);
//  $str=strtr($str,"/","0");
  $str=strtok(uniqid(""),".");
  return($str);
}


function FormattedString($size)
{
  $count=0;
  $len=strlen(trim($size));
  for($i=$len-1;$i>-1;$i--)     
     {
       $newsize=sprintf("%s%s",substr($size,$i,1),$newsize);
       $count++;
       if($count==3)
          {  
	    $newsize=sprintf("%s%s"," ",$newsize);
	    $count=0;
	  }    
     }
  return($newsize);
}

function PrintFormattedSizeOld($size)
{
 global $SAMSConf;
 $msize=floor($size/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
 $ostatok=$size%($SAMSConf->KBSIZE*$SAMSConf->KBSIZE);
 $ksize=floor($ostatok/$SAMSConf->KBSIZE);
 if($ksize<10)
   $ksize="0$ksize";
 if($ksize<100)
   $ksize="0$ksize";
   
  print("<TD ALIGN=RIGHT>&nbsp;<B>$msize</B>&nbsp;Mb<B>&nbsp;$ksize</B>&nbsp;kb\n");


}

function PrintFormattedSize($size)
{
 global $SAMSConf;
 $msize=floor($size/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
 $ostatok=$size%($SAMSConf->KBSIZE*$SAMSConf->KBSIZE);
 $ksize=floor($ostatok/$SAMSConf->KBSIZE);
 if($ksize<10)
   $ksize="0$ksize";
 if($ksize<100)
   $ksize="0$ksize";
   
  print("<TD ALIGN=RIGHT>&nbsp;");
  print("<B>$msize</B>&nbsp;Mb");
  print("<B>&nbsp;$ksize</B>&nbsp;kb\n");


}


function ReturnDate($string)
{
  $newstring=sprintf("%s.%s.%s",substr($string,8,2),substr($string,5,2),substr($string,0,4));
  return($newstring);
}

?>
