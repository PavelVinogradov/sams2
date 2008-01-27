<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function CountUserTraffic()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  $syea=strftime("%Y");
  $smon=strftime("%m");
  $eday=strftime("%d");

  $sdate="$syea-$smon-$1";
  $edate="$syea-$smon-$eday";
  $stime="0:00:00";
  $etime="0:00:00";

  PageTop("usergroup_48.jpg","$backupbuttom_2_loadbase_CountUserTraffic_1 1.$smon.$syea - $eday.$smon.$syea $backupbuttom_2_loadbase_CountUserTraffic_2");

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  $result=mysql_query("CREATE TEMPORARY TABLE cache_ SELECT sum(size),user,domain FROM cache WHERE date>=\"$sdate\"&&date<=\"$edate\" GROUP BY user,domain");
  $result=mysql_query("SELECT * FROM cache_ ");
  while($row=mysql_fetch_array($result))
       {
         $result2=mysql_query("UPDATE $SAMSConf->SAMSDB.squidusers SET size=\"$row[0]\" WHERE nick=\"$row[user]\"&&domain=\"$row[domain]\" ");
       }
  UpdateLog("$SAMSConf->adminname","$backupbuttom_2_loadbase_CountUserTraffic_3","01");

}


function RestoreBackUp()
{
  global $SAMSConf;

if(isset($_GET["groups"]))    $groups=$_GET["groups"];
if(isset($_GET["users"]))      $users=$_GET["users"];
if(isset($_GET["lists"]))        $lists=$_GET["lists"];
if(isset($_GET["shablons"])) $shablons=$_GET["shablons"];
if(isset($_GET["samsconfig"])) $samsconfig=$_GET["samsconfig"];
if(isset($_GET["tmp_name"])) $tmp_name=$_GET["tmp_name"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
   db_connect($SAMSConf->SAMSDB) or exit();
   mysql_select_db($SAMSConf->SAMSDB);

  if(($finp=gzopen("data/loadsamsdb.sql.gz","r"))!=NULL)
    {
       while(gzeof($finp)==0)
         {
           $string=gzgets($finp, 10000);
           $string2=strtok($string,";");
           if($samsconfig=="on"&&strstr($string,"IF EXISTS")&&strstr($string,"sams"))
             {
               $result=mysql_query("$string2");
             }
           if($samsconfig=="on"&&strstr($string,"CREATE TABLE")&&strstr($string,"sams"))
             {
               $result=mysql_query("$string2");
             }
           if($samsconfig=="on"&&strstr($string,"INSERT INTO sams" ))
             {
               $result=mysql_query("$string2");
             }

           if($groups=="on"&&strstr($string,"groups" ))
             {
               $result=mysql_query("$string2");
             }
           if($users=="on"&&strstr($string,"squidusers" ))
             {
               $result=mysql_query("$string2");
             }
           if($shablons=="on"&&(strstr($string,"shablons")||strstr($string,"sconfig")))
             {
               $result=mysql_query("$string2");
             }
           if($lists=="on"&&(strstr($string,"redirect")||strstr($string,"urls")))
             {
               $result=mysql_query("$string2");
             }
         }
       gzclose($finp);
     }  
   else if(($finp=fopen("data/loadsamsdb.sql.gz","r"))!=NULL)
    {
       while(feof($finp)==0)
         {
           $string=fgets($finp, 10000);
           $string2=strtok($string,";");
           if($groups=="on"&&strstr($string,"groups" ))
             {
               $result=mysql_query("$string2");
             }
           if($users=="on"&&strstr($string,"squidusers" ))
             {
               $result=mysql_query("$string2");
             }
           if($shablons=="on"&&(strstr($string,"shablons")||strstr($string,"sconfig")))
             {
               $result=mysql_query("$string2");
             }
           if($lists=="on"&&(strstr($string,"redirect")||strstr($string,"urls")))
             {
               $result=mysql_query("$string2");
             }
         }
       fclose($finp);
     }  

   
   unlink("data/loadsamsdb.sql.gz");
   CountUserTraffic();
   print("<SCRIPT>\n");
   print("  parent.lframe.location.href=\"lframe.php\"; \n");
   print("</SCRIPT>\n");

}

function LoadBackUp()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("reark_48.jpg","$backupbuttom_2_loadbase_LoadBackUp_1");
  print("<BR>$backupbuttom_2_loadbase_LoadBackUp_4\n");
  copy($_FILES['userfile']['tmp_name'],"data/loadsamsdb.sql.gz");
  print("<FORM NAME=\"LOADBACKUP\" ACTION=\"main.php\" >\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"restorebackup\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"backupbuttom_2_loadbase.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"tmp_name\" value=\"backupbuttom_2_loadbase.php\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD><B>$backupbuttom_2_loadbase_LoadBackUp_5</B>\n");
  print("<TD><INPUT TYPE=\"CHECKBOX\" name=\"groups\">\n");
  print("<TR>\n");
  print("<TD><B>$backupbuttom_2_loadbase_LoadBackUp_6</B>\n");
  print("<TD><INPUT TYPE=\"CHECKBOX\" name=\"users\">\n");
  print("<TR>\n");
  print("<TD><B>$backupbuttom_2_loadbase_LoadBackUp_7</B>\n");
  print("<TD><INPUT TYPE=\"CHECKBOX\" name=\"lists\">\n");
  print("<TR>\n");
  print("<TD><B>$backupbuttom_2_loadbase_LoadBackUp_8</B>\n");
  print("<TD><INPUT TYPE=\"CHECKBOX\" name=\"shablons\">\n");
  print("<TR>\n");
  print("<TD><B>$backupbuttom_2_loadbase_LoadBackUp_10</B>\n");
  print("<TD><INPUT TYPE=\"CHECKBOX\" name=\"samsconfig\">\n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$backupbuttom_2_loadbase_LoadBackUp_9\">\n");
  print("</FORM>\n");

}

function LoadBackUpForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("reark_48.jpg","$backupbuttom_2_loadbase_LoadBackUpForm_1");
  print("<BR>$backupbuttom_2_loadbase_LoadBackUpForm_4\n");
  print("<FORM NAME=\"LOADBACKUP\" ENCTYPE=\"multipart/form-data\" ACTION=\"main.php?show=exe&function=loadbackup&filename=backupbuttom_2_loadbase.php\" METHOD=POST>\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILES_SIZE\" value=\"1048576\">\n");
  print("<INPUT TYPE=\"FILE\" name=\"userfile\" value=\"$backupbuttom_2_loadbase_LoadBackUpForm_2\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$backupbuttom_2_loadbase_LoadBackUpForm_3\">\n");
  print("</FORM>\n");

}



function backupbuttom_2_loadbase()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);


   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=loadbackupform&filename=backupbuttom_2_loadbase.php","basefrm","loadbase_32.jpg","loadbase_48.jpg","$backupbuttom_2_loadbase_backupbuttom_2_loadbase_1");
	}

}




?>
