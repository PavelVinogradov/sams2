<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddUsersFromNCSA()
{

  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
 
  if(isset($_GET["groupname"])) $usergroup=$_GET["groupname"];
  if(isset($_GET["usershablon"])) $usershablon=$_GET["usershablon"];
  if(isset($_GET["enabled"])) $enabled=$_GET["enabled"];
  if(isset($_GET["passwd"])) $passwd=$_GET["passwd"];

  if($enabled=="on")
     $enabled=1;
  else
     $enabled=-1;      

  $result2=mysql_query("SELECT * FROM shablons WHERE name=\"$usershablon\" ");
  $row2=mysql_fetch_array($result2);
  $traffic=$row2['traffic'];
//  print("userlist=$userlist usergroup=$usergroup  usershablon=$usershablon<BR>");
   $i=0;
   $finp=fopen("data/userlist","r");
   while(feof($finp)==0)  
      {
       $string=fgets($finp,10000);
       $user=trim(strtok($string,":"));
       $passwd=trim(strtok(" "));
       if(strlen($user)>0)
         {
	    $users="";
            if(isset($_GET["users$i"])) $users=$_GET["users$i"];
            print("$user users$i=$users<BR>\n");
	    if($users=="on")
	      {
		$users=1;
                $userid=TempName();
	        $result=mysql_query("INSERT INTO squidusers SET
		 id=\"$userid\",nick=\"$user\",domain=\"\",name=\"\",
		 family=\"\",shablon=\"$usershablon\" ,quotes=\"$traffic\",size=\"0\",
		 enabled=\"$enabled\",squidusers.group=\"$usergroup\",squidusers.soname=\"\",
		 squidusers.ip=\"\",squidusers.ipmask=\"\",squidusers.passwd=\"$passwd\",hit=\"0\",
			 squidusers.autherrorc=\"0\", squidusers.autherrort=\"0\" ");
		if($result!=FALSE)
                   UpdateLog("$SAMSConf->adminname","Added user $user ","01");
	     }	 
           $i++;
	 }   

      }
   fclose($finp);
  print("<SCRIPT>\n");
  print(" parent.tray.location.href=\"tray.php?show=exe&function=userstray\";\n");
  print(" parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");

}


function SelectNCSAUsersForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("reark_48.jpg","$backupbuttom_2_loadbase_LoadBackUp_1 !!!!!!!!");
  copy($_FILES['userfile']['tmp_name'],"data/userlist");
  $finp=fopen("data/userlist","r");
  
       print("<H3>$usersbuttom_1_ncsa_SelectNCSAUsersForm_1:</H3>\n");
  
  print("<FORM NAME=\"LOADNCSAUSERS\" ACTION=\"main.php\" >\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addusersfromncsa\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"usersbuttom_1_ncsa.php\">\n");
  print("<TABLE>\n");
  $i=0;
  while(feof($finp)==0)  
     {
       $string=fgets($finp,10000);
       $user=trim(strtok($string,":"));
       $passwd=trim(strtok(" "));
       //    $user=trim(strtok("+"));
       if(strlen($user)>0)
         {
            print("<TR>\n");
            print("<TD>$user\n");
            print("<TD> <INPUT TYPE=\"CHECKBOX\" NAME=users$i CHECKED> ");
            $i++;
	 }   
     }
  
  print("<TR><TD>\n");
  print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_3 \n");
  print("<TD>\n");
  print("<SELECT NAME=\"groupname\" ID=\"groupname\" SIZE=1 TABINDEX=30 >\n");
  $result2=mysql_query("SELECT name,nick FROM groups");
  while($row2=mysql_fetch_array($result2))
      {
       print("<OPTION VALUE=$row2[name]> $row2[nick]");
      }
  print("</SELECT>\n");

   print("<TR>\n");
  print("<TD>\n");
  print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_4 \n");
  print("<TD>\n");
  print("<SELECT NAME=\"usershablon\" ID=\"usershablon\" SIZE=1 TABINDEX=30 >\n");
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");
  $result=mysql_query("SELECT * FROM shablons");
  while($row=mysql_fetch_array($result))
      {
       print("<OPTION VALUE=$row[name]> $row[nick]");
      }
  print("</SELECT>");
  print("<TR><TD><B>$usersbuttom_1_ncsa_SelectNCSAUsersForm_2");
  print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"enabled\" CHECKED>");
   
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$usersbuttom_1_ncsa_SelectNCSAUsersForm_3\">\n");
  print("</FORM>\n");
  
  fclose($finp);

}



function LoadNCSAUsersForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("reark_48.jpg","$usersbuttom_1_ncsa_LoadNCSAUsersForm_1");
  print("<BR> $usersbuttom_1_ncsa_LoadNCSAUsersForm_2\n");
  print("<FORM NAME=\"LOADBACKUP\" ENCTYPE=\"multipart/form-data\" ACTION=\"main.php?show=exe&function=selectncsausersform&filename=usersbuttom_1_ncsa.php\" METHOD=POST>\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILES_SIZE\" value=\"1048576\">\n");
  print("<INPUT TYPE=\"FILE\" name=\"userfile\" value=\"$backupbuttom_2_loadbase_LoadBackUpForm_2\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$backupbuttom_2_loadbase_LoadBackUpForm_3\">\n");
  print("</FORM>\n");

}


function usersbuttom_1_ncsa()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->AUTH=="ncsa"&&$SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=loadncsausersform&filename=usersbuttom_1_ncsa.php","basefrm","domain-32.jpg","domain-48.jpg","$usersbuttom_1_domain_usersbuttom_1_domain_1");
	}

}

?>
