#!/usr/bin/php

<?php

function AddShablonFieldAllDenied()
{
  if(isset($_GET["action"])) $action=$_GET["action"];

  $result=mysql_query("SELECT shablons.nick,shablons.name,sconfig.sname,redirect.filename,redirect.type FROM redirect LEFT JOIN sconfig ON sconfig.set=redirect.filename LEFT JOIN shablons ON shablons.name=sconfig.sname WHERE redirect.type='allow' GROUP BY shablons.nick");
  while($row=mysql_fetch_array($result))
      {
        echo "UPDATE squidctrl.shablons SET alldenied='1' WHERE name=$row[name] \n";
        $result2=mysql_query("UPDATE squidctrl.shablons SET alldenied='1' WHERE name='$row[name]'");
      }
//SELECT shablons.nick,shablons.name,sconfig.sname,redirect.filename,redirect.type FROM redirect LEFT JOIN sconfig ON sconfig.set=redirect.filename LEFT JOIN shablons ON shablons.name=sconfig.sname WHERE redirect.type='allow' GROUP BY shablons.nick;

}


function UpdateShablonsAuth()
{
  if(isset($_GET["action"])) $action=$_GET["action"];

  $result=mysql_query("SHOW COLUMNS FROM squidctrl.shablons");
  while($row=mysql_fetch_array($result))
      {
        if($row[0]=="auth"&&$row[1]=="varchar(4)")
           {
             print("Old field type into squidctrl.shablons.auth found... ");
             $result2=mysql_query("ALTER TABLE squidctrl.shablons MODIFY auth varchar(5)");
             $result2=mysql_query("SELECT auth FROM squidctrl.sams");
	     $row2=mysql_fetch_array($result2);
	     $result2=mysql_query("UPDATE squidctrl.shablons SET auth='$row2[0]'");
	     print("Modify\n");
 	     if($action=="web")
	        print("<BR>");
         return(0);
	   }
      }
   print("New field type into squidctrl.shablons.auth found\n");
 	    if($action=="web")
	     print("<BR>");
   return(0);
}

function RenameTable($tablename,$newtablename)
{
  if(isset($_GET["action"])) $action=$_GET["action"];

  print("SEARCH TABLE $newtablename ");
  $result=mysql_query("SELECT * FROM $newtablename");
  if($result==NULL)
    {
       print("... NOT FOUND \n ");
       $result=mysql_query("ALTER TABLE $tablename RENAME AS $newtablename ");
       print(" TABLE RENAME\n");
 	    if($action=="web")
	     print("<BR>");
       return(1);
    }
  else
    {
       print("... FOUND\n");
 	    if($action=="web")
	     print("<BR>");
	}
  return(0);
}


function InsertData($txt)
{
  $result=mysql_query("$txt");

}

function CreatePrimaryKey($basename,$tablename,$fieldname)
{
  if(isset($_GET["action"])) $action=$_GET["action"];

  $flag=0;
  print("ALTER TABLE $basename.$tablename ADD PRIMARY KEY ($fieldname)...\n");
  $result=mysql_query("SHOW COLUMNS FROM $basename.$tablename");
  while($row=mysql_fetch_array($result))
      {
        print("$row[0]-$row[1]-$row[2]-$row[3]-$row[4]\n");
        if($fieldname==$row[0]&&$row[3]!="PRI")
           {
             $result2=mysql_query("ALTER TABLE $basename.$tablename ADD PRIMARY KEY ($fieldname)");
             print("YES\n");
 	         if($action=="web")
	           print("<BR>");
           }
        if($fieldname==$row[0]&&$row[3]=="PRI")
           {
             print("NO\n");
 	         if($action=="web")
	           print("<BR>");
           }
      }
}


function CreateIndex($basename,$tablename,$fieldname)
{
  if(isset($_GET["action"])) $action=$_GET["action"];

  $flag=0;
  print("CREATE INDEX $basename.$tablename ($fieldname)...");
  $result=mysql_query("SHOW COLUMNS FROM $basename.$tablename");
  while($row=mysql_fetch_array($result))
      {
        if($fieldname==$row[0]&&$row[3]!="MUL"&&$row[3]!="PRI")
           {
             $result2=mysql_query("ALTER TABLE $basename.$tablename ADD INDEX ($fieldname)");
             print("YES\n");
 	         if($action=="web")
	           print("<BR>");
           }
        if($fieldname==$row[0]&&($row[3]=="MUL"||$row[3]=="PRI"))
           {
             print("NO\n");
 	         if($action=="web")
	           print("<BR>");
           }
      }
}

function ModifyColumn($tablename,$fieldname,$type,$newtype)
{
  if(isset($_GET["action"])) $action=$_GET["action"];

  $flag=0;
  print("MODIFY COLUMN $tablename.$fieldname ");
  $result=mysql_query("SHOW COLUMNS FROM $tablename");
  while($row=mysql_fetch_array($result))
      {
        if($row[Field]=="$fieldname"&&$row[Type]=="$type")
          {
            $result2=mysql_query("ALTER TABLE $tablename MODIFY $fieldname $newtype");
            print(" ... MODIFY \n");
 	        if($action=="web")
	          print("<BR>");
            $flag=1;
          }
      }
  if($flag==0)
      {
        print(" ... NO \n");
 	    if($action=="web")
	     print("<BR>");
      }

}

function RenameColumn($tablename,$fieldname,$newfieldname,$newtype)
{
  if(isset($_GET["action"])) $action=$_GET["action"];

  $flag=0;
  print("RENAME COLUMN $tablename.$fieldname ");
  $result=mysql_query("SHOW COLUMNS FROM $tablename");
  while($row=mysql_fetch_array($result))
      {
        if($row['Field']==$fieldname)
          {
            printf("\nALTER TABLE $tablename CHANGE $fieldname $newfieldname $newtype \n");
            $result2=mysql_query("ALTER TABLE $tablename CHANGE $fieldname $newfieldname $newtype ");
	      if($result2>0)
	        {
	          print(" ... MODIFY \n");
	          if($action=="web")
	            print("<BR>");
		      $flag=1;
	 	    }
          }
      }
  if($flag==0)
      {
        print(" ... NO \n");
 	    if($action=="web")
	     print("<BR>");
      }
}

function UpgradeTable($tablename,$fieldname,$string)
{
  if(isset($_GET["action"])) $action=$_GET["action"];

  print("SEARCH FIELD $tablename.$fieldname ");
  $result=mysql_query("SELECT $fieldname FROM $tablename");
  if($result==NULL)
    {
       print("... NOT FOUND \n ");
       $result=mysql_query("ALTER TABLE $tablename ADD $fieldname $string");
       print(" UPGRADED\n");
	   if($action=="web")
	     print("<BR>");
	   return(1);
    }
  else
    {
       print("... FOUND\n");
	   if($action=="web")
	     print("<BR>");
	}
  return(0);
}
function UpgradeTable2($tablename,$fieldname,$string,$position)
{
  if(isset($_GET["action"])) $action=$_GET["action"];

  print("SEARCH FIELD $tablename.$fieldname ");
  $result=mysql_query("SELECT $fieldname FROM $tablename");
  if($result==NULL)
    {
       print("... NOT FOUND \n ");
       $result=mysql_query("ALTER TABLE $tablename ADD $fieldname $string AFTER $position ");
       print(" UPGRADED\n");
	   if($action=="web")
	     print("<BR>");
       return(1);
    }
  else
    {
       print("... FOUND\n");
	   if($action=="web")
	     print("<BR>");
	}
  return(0);
}

function AddTable($basename,$tablename,$fieldname,$string)
{
  if(isset($_GET["action"])) $action=$_GET["action"];
  print("SEARCH TABLE $basename.$tablename ");
  $result=mysql_query("SHOW COLUMNS FROM $basename.$tablename");
  if($result==NULL)
    {
       print("... NOT FOUND \n ");
       $result=mysql_query("CREATE TABLE $basename.$tablename ($fieldname $string)");
       print(" ADDED\n");
	   if($action=="web")
	     print("<BR>");
       return(1);
    }
  else
    {
       print("... FOUND\n");
	   if($action=="web")
	     print("<BR>");
	}
  return(0);
}


function upgrade_mysql_table()
{
global $SAMSConf;
global $DELAYPOOL;
global $USERACCESS;
global $SAMSDB;
global $LOGDB;
global $MYSQLHOSTNAME;
global $MYSQLUSER;
global $MYSQLPASSWORD;
global $SQUIDCACHEFILE;
global $SQUIDROOTDIR;
global $SQUIDLOGDIR;
global $SGUARDDBPATH;
global $SGUARDLOGPATH;
global $REDIRECTOR;
global $AUTH;

if(isset($_GET["action"])) $action=$_GET["action"];

if($action!="web")
  require('../php/mysqltools.php');
else
  print("<BR>");

 // require('mysqltools.php');
//else
//  require('../php/mysqltools.php');

$SAMSConf=new SAMSCONFIG();

//LoadConfig();
db_connect("squidctrl") or exit();
mysql_select_db("squidctrl");

$result=UpgradeTable("shablons","shablonpool","BIGINT NOT NULL");
$result=UpgradeTable("shablons","userpool","BIGINT NOT NULL");
$result=UpgradeTable("redirect","redirect_to","VARCHAR(100)");
$result=UpgradeTable("sams","redirect_to","VARCHAR(100)");
$result=UpgradeTable("sams","denied_to","VARCHAR(100)");
$result=UpgradeTable("sams","redirector","VARCHAR(25)");
$result=UpgradeTable("sams","delaypool","CHAR(1)");
$result=UpgradeTable("sams","useraccess","CHAR(1)");
$result=UpgradeTable("sams","auth","VARCHAR(4)");
$result=UpgradeTable("sams","wbinfopath","VARCHAR(100)");

AddTable("squidctrl","sguard","sname","VARCHAR(25)");
$result=UpgradeTable("sguard","sname","VARCHAR(25)");
$result=UpgradeTable("sguard","name","VARCHAR(100)");
$result=UpgradeTable("sguard","domain","CHAR(1)");
$result=UpgradeTable("sguard","url","CHAR(1)");
$result=UpgradeTable("sguard","expr","CHAR(1)");

$result=UpgradeTable("shablons","redirect_to","VARCHAR(100)");
$result=UpgradeTable("squidusers","ipmask","CHAR(15)");
ModifyColumn("shablons","shablonpool","float","BIGINT NOT NULL");
ModifyColumn("shablons","userpool","float","BIGINT NOT NULL");

CreateIndex("squidlog","cache","date");
CreateIndex("squidlog","cache","user");
CreateIndex("squidlog","cache","domain");
CreateIndex("squidctrl","urls","url");
CreateIndex("squidctrl","urls","type");

$result=UpgradeTable("squidusers","passwd","CHAR(20)");

AddTable("squidlog","cachesum","date","date NOT NULL default '0000-00-00'");
$result=UpgradeTable("squidlog.cachesum","user","varchar(25)");
$result=UpgradeTable("squidlog.cachesum","domain","varchar(25)");
$result=UpgradeTable("squidlog.cachesum","size","bigint(20)");
$result=UpgradeTable("squidlog.cachesum","hit","bigint(20)");
CreateIndex("squidlog","cachesum","date");
CreateIndex("squidlog","cachesum","user");
CreateIndex("squidlog","cachesum","domain");
InsertData("insert into squidlog.cachesum select squidlog.cache.date,squidlog.cache.user,squidlog.cache.domain,sum(squidlog.cache.size),sum(squidlog.cache.hit) from squidlog.cache group by squidlog.cache.date,squidlog.cache.user");
$result=UpgradeTable("squidctrl.sams","urlaccess","varchar(1)");
$result=UpgradeTable("squidctrl.shablons","traffic","int(25)");
ModifyColumn("squidlog.cache","url","char(50)","char(100) NOT NULL");
// 15.09.2004

$result=UpgradeTable("squidctrl.sams","lang","varchar(15)");
if($result==1) InsertData("update sams set lang='EN' ");

$result=UpgradeTable("squidctrl.sams","ntlmdomain","char(1)");
if($result==1) InsertData("update sams set ntlmdomain='Y' ");

$result=UpgradeTable("squidctrl.sams","bigd","char(1)");

$result=UpgradeTable("squidctrl.sams","bigu","char(1)");

$result=UpgradeTable("squidctrl.sams","sleep","int(3)");
if($result==1) InsertData("update sams set sleep='2' ");

$result=UpgradeTable("squidctrl.sams","parser_on","char(1)");
if($result==1) InsertData("update sams set parser_on='N' ");

$result=UpgradeTable("squidctrl.sams","parser","varchar(10)");
if($result==1) InsertData("update sams set parser='diskret' ");

$result=UpgradeTable("squidctrl.sams","parser_time","int(2)");
if($result==1) InsertData("update sams set parser_time='5' ");

$result=UpgradeTable("squidctrl.sams","count_clean","char(1)");
if($result==1) InsertData("update sams set count_clean='N' ");

$result=UpgradeTable("squidctrl.sams","nameencode","char(1)");
if($result==1) InsertData("update sams set nameencode='N' ");

AddTable("squidctrl","reconfig","number","INT(2)");
$result1=UpgradeTable("squidctrl.reconfig","service","varchar(15)");
$result2=UpgradeTable("squidctrl.reconfig","action","varchar(10)");
if($result1==1||$result2==1) InsertData("insert into squidctrl.reconfig set number='1',service='',action='' ");

$result=UpgradeTable("squidctrl.sams","iconset","varchar(25)");
if($result==1) InsertData("insert into squidctrl.sams set iconset='classic' ");

$result=UpgradeTable("squidctrl.squidusers","gauditor","int(1)");

$result=UpgradeTable("squidctrl.shablons","days","varchar(14)");
if($result==1) InsertData("update shablons set days='MTWHFAS' ");

$result=UpgradeTable("squidctrl.shablons","shour","tinyint(2)");
if($result==1) InsertData("update shablons set shour='0' ");

$result=UpgradeTable("squidctrl.shablons","smin","tinyint(2)");
if($result==1) InsertData("update shablons set smin='0' ");

$result=UpgradeTable("squidctrl.shablons","ehour","tinyint(2)");
if($result==1) InsertData("update shablons set ehour='24' ");

$result=UpgradeTable("squidctrl.shablons","emin","tinyint(2)");
if($result==1) InsertData("update shablons set emin='0' ");

$result=UpgradeTable("squidctrl.sams","autherrorc","tinyint(1)");
if($result==1) InsertData("update sams set autherrorc='0' ");

$result=UpgradeTable("squidctrl.sams","autherrort","varchar(16)");
if($result==1) InsertData("update sams set autherrort='0' ");

$result=UpgradeTable("squidctrl.squidusers","autherrorc","tinyint(1)");
if($result==1) InsertData("update squidusers set autherrorc='0' ");

$result=UpgradeTable("squidctrl.squidusers","autherrort","varchar(16)");
if($result==1) InsertData("update squidusers set autherrort='0' ");

$result=UpgradeTable2("squidctrl.shablons","auth","varchar(4)","redirect_to");
if($result==1) InsertData("update shablons set auth='ip' ");

$result=AddTable("squidctrl","globalsettings","lang","varchar(15) NOT NULL default 'EN' ");
$result=UpgradeTable("squidctrl.globalsettings","iconset","varchar(25) NOT NULL default 'classic'");
$result=UpgradeTable("squidctrl.globalsettings","useraccess","char(1) NOT NULL default 'Y' ");
$result=UpgradeTable("squidctrl.globalsettings","urlaccess","char(1) NOT NULL default 'Y' ");
$result=UpgradeTable("squidctrl.globalsettings","showutree","char(1) NOT NULL default 'Y' ");
if($result==1)
  InsertData("INSERT INTO squidctrl.globalsettings VALUES('EN','classic','Y','Y','Y')");

$result=UpgradeTable("squidctrl.squidusers","hit","bigint(20) NOT NULL default '0' ");

$result=UpgradeTable("squidctrl.sams","realsize","varchar(4)");
if($result==1) InsertData("update sams set realsize='real' ");
$result=UpgradeTable("squidctrl.sams","checkdns","varchar(1)");
if($result==1) InsertData("update sams set checkdns='N' ");

$result=UpgradeTable("squidctrl.globalsettings","showname","varchar(5) NOT NULL default 'nick' ");

$result=RenameColumn("sams","lang","separator"," VARCHAR(15) DEFAULT '+' ");
if($result==1) InsertData("update squidctrl.sams set separator='+' ");

$result=UpgradeTable("squidctrl.sams","loglevel","tinyint(1) NOT NULL default '0'");
if($result==1) InsertData("update sams set loglevel='0' ");

$result=UpgradeTable("squidctrl.globalsettings","kbsize","char(15) NOT NULL default '1024'");
if($result==1) InsertData("update globalsettings set mbsize='1024' ");

$result=UpgradeTable("squidctrl.globalsettings","mbsize","char(15) NOT NULL default '1048576'");
if($result==1) InsertData("update globalsettings set mbsize='1048576' ");

//RenameTable("webisettings","globalsettings");

$result=UpgradeTable("squidctrl.sams","defaultdomain","char(25) NOT NULL default 'workgroup'");
if($result==1) InsertData("update sams set defaultdomain='workgroup' ");

$result=UpgradeTable("squidctrl.sams","squidbase","tinyint(2) NOT NULL default '0'");
if($result==1) InsertData("update sams set squidbase='0' ");

$result=UpgradeTable("squidctrl.sams","udscript","char(25) NOT NULL default 'NONE'");
if($result==1) InsertData("update sams set udscript='NONE' ");
$result=UpgradeTable("squidctrl.sams","adminaddr","char(25) NOT NULL default 'NONE'");
if($result==1) InsertData("update sams set adminaddr='' ");

$result=UpgradeTable("squidctrl.globalsettings","showgraph","char(1) NOT NULL default 'N' ");

UpdateShablonsAuth();

$result=UpgradeTable("squidctrl.shablons","period","char(3) NOT NULL default 'M' ");
if($result==1) InsertData("update squidctrl.shablons set period='M' ");
$result=UpgradeTable("squidctrl.shablons","clrdate","date NOT NULL default '0000-00-00' ");

$result=UpgradeTable("squidctrl.shablons","alldenied","tinyint(1) NOT NULL default '0'");
if($result==1)
  AddShablonFieldAllDenied();

$result=UpgradeTable("squidctrl.globalsettings","createpdf","char(6) NOT NULL default 'none' ");
if($result==1) InsertData("update squidctrl.globalsettings set createpdf='none' ");

CreateIndex("squidctrl","squidusers","nick");
CreateIndex("squidctrl","squidusers","group");
CreateIndex("squidctrl","squidusers","family");

$result=AddTable("squidctrl","proxyes","id","tinyint(3) NOT NULL default '0' ");
$result=UpgradeTable("squidctrl.proxyes","description","varchar(50) NOT NULL default 'proxy server'");
if($result==1)
  InsertData("INSERT INTO squidctrl.proxyes VALUES('0','main proxy server')");

$result=UpgradeTable("squidctrl.reconfig","value","varchar(110) ");
$result=AddTable("squidlog","files","id","tinyint(3) NOT NULL default '0' ");
$result=UpgradeTable("squidlog.files","filepath","varchar(50)");
$result=UpgradeTable("squidlog.files","url","varchar(120)");
$result=UpgradeTable("squidlog.files","size","int(12)");
$result=UpgradeTable("squidctrl.proxyes","endvalue","int(20)");

ModifyColumn("squidctrl.sams","shour","tinyint(2)","tinyint(2) UNSIGNED ZEROFILL");
ModifyColumn("squidctrl.sams","smin","tinyint(2)","tinyint(2) UNSIGNED ZEROFILL");
ModifyColumn("squidctrl.sams","ehour","tinyint(2)","tinyint(2) UNSIGNED ZEROFILL");
ModifyColumn("squidctrl.sams","emin","tinyint(2)","tinyint(2) UNSIGNED ZEROFILL");

ModifyColumn("squidctrl.shablons","shour","tinyint(2)","tinyint(2) UNSIGNED ZEROFILL");
ModifyColumn("squidctrl.shablons","smin","tinyint(2)","tinyint(2) UNSIGNED ZEROFILL");
ModifyColumn("squidctrl.shablons","ehour","tinyint(2)","tinyint(2) UNSIGNED ZEROFILL");
ModifyColumn("squidctrl.shablons","emin","tinyint(2)","tinyint(2) UNSIGNED ZEROFILL");
}

 upgrade_mysql_table();

?>
