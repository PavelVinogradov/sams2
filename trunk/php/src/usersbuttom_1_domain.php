<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddUsersFromDomain()
{

  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
 
  if(isset($_GET["domainname"])) $domainname=$_GET["domainname"];
  if(isset($_GET["username"])) $userlist=$_GET["username"];
  if(isset($_GET["groupname"])) $usergroup=$_GET["groupname"];
  if(isset($_GET["usershablon"])) $usershablon=$_GET["usershablon"];
  if(isset($_GET["enabled"])) $enabled=$_GET["enabled"];
//  if(isset($_GET["domainname"])) $domainname=$_GET["domainname"];

  if($enabled=="on")
     $enabled=1;
  else
     $enabled=-1;      

//  print("userlist=$userlist usergroup=$usergroup  usershablon=$usershablon<BR>");
  $i=0;

  $result=mysql_query("SELECT traffic FROM shablons WHERE name=\"$usershablon\" ");
  $row=mysql_fetch_array($result);
  $straf=$row[0];
  while(strlen($userlist[$i])>0)
     {
//       print("userlist=$userlist[$i] enabled=$enabled<BR>");

       $string=$userlist[$i];
       $i++;
       if($SAMSConf->NTLMDOMAIN=="Y")
         {
           $domain=trim(strtok($string,"+"));
           $user=trim(strtok("+"));
           //$domain=strtolower($domain);
         }
       else
         {
           $user=$string;
	   $domain="workgroup";
	   //	 
	 }	 

       if(strlen($domainname)>1)
         $domain=$domainname;    
       
       //print("user=$user domain=$domain enabled=$enabled<BR>");
       
       $result2=mysql_query("SELECT * FROM shablons WHERE name=\"$usershablon\" ");
       $row2=mysql_fetch_array($result2);
       $traffic=$row2['traffic'];

       $result=mysql_query("SELECT * FROM squidusers WHERE domain=\"$domain\"&&nick=\"$user\" ");
       $row=mysql_fetch_array($result);
       //print("result = $result row2=$row2 db: $row[nick]/$row[domain]<BR>");

      
       if(strcmp($row['name'],$user)!=0&&strcmp($row['domain'],$domain)!=0)
          {
             $userid=TempName();
			 $result=mysql_query("INSERT INTO squidusers SET
			 id=\"$userid\",nick=\"$user\",domain=\"$domain\",name=\"\",
			 family=\"\",shablon=\"$usershablon\" ,quotes=\"$traffic\",size=\"0\",
			 enabled=\"$enabled\",squidusers.group=\"$usergroup\",squidusers.soname=\"\",
			 squidusers.ip=\"\",squidusers.ipmask=\"\",squidusers.passwd=\"none\",hit=\"0\",
			 squidusers.autherrorc=\"0\", squidusers.autherrort=\"0\" ");
		if($result!=FALSE)
                   UpdateLog("$SAMSConf->adminname","Added user $user ","01");
          }

     }
  print("<SCRIPT>\n");
  print(" parent.tray.location.href=\"tray.php?show=exe&function=userstray\";\n");
  print(" parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");

}



function AddUsersFromDomainFormNew()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  PageTop("user.jpg"," $usersbuttom_1_domain_AddUsersFromDomainForm_1");
  
  $test=ExecuteShellScript("getwbinfousers","$SAMSConf->WBINFOPATH/");

  print("USERSLIST=$userlist<BR>");
  $len=substr_count($userlist,"\n");
  print("users count= $len<BR>");

  print("<BR><B>$usersbuttom_1_domain_AddUsersFromDomainForm_2</B>");
  print("<FORM NAME=\"AddDomainUsers\" ACTION=\"main.php\">\n");
  print("<SELECT NAME=\"username[]\" MULTIPLE>\n");
      $finp=fopen("data/userlist","r");
      if($finp==FALSE)
        {
          echo "can't open file data/userlist<BR>";
          exit(0);
        }
      while(feof($finp)==0)  
         {
           $string=fgets($finp,10000);
	   $domainuser="$string";
	   print("<OPTION VALUE=\"$domainuser\"> $domainuser $domain $user");

         }
      fclose($finp);
  print("</SELECT>\n");
  print("<P>" );

  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addusersfromdomain\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_1_domain.php\">\n");
  print("<TABLE>\n");
  print("<TR><TD>\n");
  print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_7\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"domainname\" id=Show >\n");

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
  print("<TR><TD><B>$usersbuttom_1_domain_AddUsersFromDomainForm_6");
  print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"enabled\" CHECKED>");
  print("</TABLE>\n");

  print("<INPUT TYPE=\"SUBMIT\" value=\"$usersbuttom_1_domain_AddUsersFromDomainForm_5\">\n");
  print("</FORM>\n");

}

function AddUsersFromDomainForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);

  PageTop("user.jpg"," $usersbuttom_1_domain_AddUsersFromDomainForm_1");

  $value=ExecuteShellScript("getwbinfousers","$SAMSConf->WBINFOPATH/");
  $a=explode(" ",$value);
  $acount=count($a);
  print("<BR><B>$usersbuttom_1_domain_AddUsersFromDomainForm_2</B>");
  print("<FORM NAME=\"AddDomainUsers\" ACTION=\"main.php\">\n");
  print("<SELECT NAME=\"username[]\" MULTIPLE>\n");

  for($i=0;$i<$acount;$i++)
     {
       $mem[$i]=$a[$i];
       //$string=fgets($finp,10000);
       if($SAMSConf->NTLMDOMAIN=="Y")
         {
           if(strstr($a[$i],"\\"))
	     {
	       $domain=trim(strtok($a[$i],"\\"));
               $user=trim(strtok("\\"));
	     }
	   else
	     {
	       $domain=trim(strtok($a[$i],"+"));
               $user=trim(strtok("+"));
	     }  
           $domainlen=strlen($domain);
           $userlen=strlen($user);
           if($domainlen==0||$userlen==0)
             {
	       $user=$domain;
	       $domain=$SAMSConf->DEFAULTDOMAIN;
	     }
           //$domain=strtolower($domain);
          }
       else
          {
	     $domain=$SAMSConf->DEFAULTDOMAIN;
             $user=trim($a[$i]);
           }
       //print("$user/$domain domainlen=$domainlen userlen=$userlen<BR>");
//echo "  SELECT * FROM squidusers WHERE domain=\"$domain\"&&nick=\"$user\"<BR>";
       $result=mysql_query("SELECT * FROM squidusers WHERE domain=\"$domain\"&&nick=\"$user\" ");
       $row=mysql_fetch_array($result);
       if(strcmp($row['name'],$user)!=0&&strcmp($row['domain'],$domain)!=0)
          {
            if($SAMSConf->NTLMDOMAIN=="Y")
              print("<OPTION VALUE=\"$domain+$user\"> $user+$domain  ");
            else 
              print("<OPTION VALUE=\"$user\"> $user  ");
          }
     }
//  fclose($finp);
  print("</SELECT>\n");
  print("<P>" );

  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addusersfromdomain\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_1_domain.php\">\n");
  print("<TABLE>\n");
  print("<TR><TD>\n");
  print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_7\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"domainname\" id=Show VALUE=\"$SAMSConf->DEFAULTDOMAIN\" >\n");

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
  print("<TR><TD><B>$usersbuttom_1_domain_AddUsersFromDomainForm_6");
  print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"enabled\" CHECKED>");
  print("</TABLE>\n");

  print("<INPUT TYPE=\"SUBMIT\" value=\"$usersbuttom_1_domain_AddUsersFromDomainForm_5\">\n");
  print("</FORM>\n");

}


function usersbuttom_1_domain()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->AUTH=="ntlm"&&$SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=addusersfromdomainform&filename=usersbuttom_1_domain.php","basefrm","domain-32.jpg","domain-48.jpg","$usersbuttom_1_domain_usersbuttom_1_domain_1");
	}

}

?>
