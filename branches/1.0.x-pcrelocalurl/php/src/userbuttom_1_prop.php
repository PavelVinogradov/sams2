<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UpdateUser()
{
  global $SAMSConf;
  $gauditor="";
  if(isset($_GET["userid"])) $userid=$_GET["userid"];
  if(isset($_GET["domain"])) $domain=$_GET["domain"];
  if(isset($_GET["usernick"])) $usernick=$_GET["usernick"];
  if(isset($_GET["userfamily"])) $userfamily=$_GET["userfamily"];
  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["usersoname"])) $usersoname=$_GET["usersoname"];
  if(isset($_GET["usergroup"])) $usergroup=$_GET["usergroup"];
  if(isset($_GET["userquote"])) $userquote=$_GET["userquote"];
  if(isset($_GET["userip"])) $userip=$_GET["userip"];
  if(isset($_GET["useripmask"])) $useripmask=$_GET["useripmask"];
  if(isset($_GET["usershablon"])) $usershablon=$_GET["usershablon"];
  if(isset($_GET["enabled"])) $enabled=$_GET["enabled"];
  if(isset($_GET["gauditor"])) $gauditor=$_GET["gauditor"];
  if(isset($_GET["saveenabled"])) $saveenabled=$_GET["saveenabled"];
  if(isset($_GET["auth"])) $auth=$_GET["auth"];
  if(isset($_GET["defstatus"])) $defstatus=$_GET["defstatus"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {      exit;    }
  
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  if($gauditor=="on")
     $gauditor=1;
  else
     $gauditor=0;
 
  if($defstatus!=$enabled)
    {
      if($enabled==1)
           UpdateLog("$SAMSConf->adminname","Activate user $usernick","01");
      if($enabled==-1)
           UpdateLog("$SAMSConf->adminname","Deactivate user $usernick","01");
      if($enabled==0)
           UpdateLog("$SAMSConf->adminname","Deactivate user $usernick","01");
    }
     
  $passwd="none";
  if($auth=="ncsa"||$auth=="ip")
   {
     if(isset($_GET["passwd"])) $passwd=$_GET["passwd"];
   }

  $result=mysql_query("UPDATE squidusers SET gauditor=\"$gauditor\",domain=\"$domain\",nick=\"$usernick\",family=\"$userfamily\",name=\"$username\",squidusers.soname=\"$usersoname\",squidusers.group=\"$usergroup\",squidusers.quotes=\"$userquote\",enabled=\"$enabled\",shablon=\"$usershablon\",ip=\"$userip\",ipmask=\"$useripmask\",passwd=\"$passwd\" WHERE id=\"$userid\" ");

  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=userform&userid=$userid\";\n");
  print("</SCRIPT> \n");

}


/****************************************************************/
function UpdateUserForm()
{

  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["userid"])) $userid=$_GET["userid"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {      exit;    }
  
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT squidusers.*,shablons.auth FROM squidusers LEFT JOIN shablons ON squidusers.shablon=shablons.name WHERE id=\"$userid\" ");
  $row=mysql_fetch_array($result);

  PageTop("user.jpg","$userbuttom_1_prop_UpdateUserForm_1 <FONT COLOR=\"BLUE\">$row[nick]</FONT>");

  print("<FORM NAME=\"UPDATEUSER\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"updateuser\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"userbuttom_1_prop.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"userid\" value=\"$row[id]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"auth\" value=\"$row[auth]\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"defstatus\" value=\"$row[enabled]\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>Nickname:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$row[nick]\" NAME=\"usernick\" SIZE=15> \n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_2:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$row[domain]\" NAME=\"domain\" SIZE=15> \n");

  if( $row['auth']=="ncsa"||$row['auth']=="ip")
    {
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$userbuttom_1_prop_UpdateUserForm_3:");
       print("<TD>\n");
       print("<INPUT TYPE=\"PASSWORD\" NAME=\"passwd\" SIZE=20 VALUE=\"$row[passwd]\"");
    }

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_4: \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$row[ip]\" NAME=\"userip\" SIZE=15>/ \n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$row[ipmask]\" NAME=\"useripmask\" SIZE=15> \n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_5: \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$row[name]\" NAME=\"username\" SIZE=30> \n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_6: \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$row[soname]\" NAME=\"usersoname\" SIZE=30> \n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_7: \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$row[family]\" NAME=\"userfamily\" SIZE=30> \n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_8: \n");
  print("<TD>\n");
  print("<SELECT NAME=\"usergroup\" ID=\"groupname\" SIZE=1 TABINDEX=30 >\n");

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");
  $result2=mysql_query("SELECT name,nick FROM groups");
  $result_u=mysql_query("SELECT * FROM squidusers WHERE id=\"$row[id]\" ");
  $row_u=mysql_fetch_array($result_u);
  while($row2=mysql_fetch_array($result2))
      {
       if($row2['name']==$row_u['group'])
         {
           print("<OPTION VALUE=$row2[name] SELECTED> $row2[nick]");
         }
       else
         {
           print("<OPTION VALUE=$row2[name]> $row2[nick]");
         }
      }
  print("</SELECT>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_9 \n");
  print("<TD>\n");
  print(" \n");
  
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_10 \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"userquote\" SIZE=10 VALUE=\"$row[quotes]\"> <B>0 - unlimited traffic \n");
  print("<TD>\n");
  
  
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"saveenabled\" value=\"$row[enabled]\">\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_11:  \n");
  print("<TD>\n");
  print("<SELECT NAME=\"enabled\" ID=\"enabled\" SIZE=1 TABINDEX=10 >\n");
  if($row['enabled']==1)
    print("<OPTION VALUE=\"1\" SELECTED> $userbuttom_1_prop_UpdateUserForm_15");
  else
    print("<OPTION VALUE=\"1\"> $userbuttom_1_prop_UpdateUserForm_15"); 
  if($row['enabled']==0)
    print("<OPTION VALUE=\"0\" SELECTED> $userbuttom_1_prop_UpdateUserForm_16");
  else
    print("<OPTION VALUE=\"0\"> $userbuttom_1_prop_UpdateUserForm_16");
  if($row['enabled']==-1)
    print("<OPTION VALUE=\"-1\" SELECTED> $userbuttom_1_prop_UpdateUserForm_17");
  else
    print("<OPTION VALUE=\"-1\"> $userbuttom_1_prop_UpdateUserForm_17");
  print("</SELECT>\n");

//  if($row['enabled']>0)
//     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"enabled\" CHECKED> \n");
//  else
//     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"enabled\" > \n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_12: \n");
  print("<TD>\n");
  print("<SELECT NAME=\"usershablon\" ID=\"usershablon\" SIZE=1 TABINDEX=30 >\n");

  $result2=mysql_query("SELECT name,nick FROM shablons");
  while($row2=mysql_fetch_array($result2))
      {
       if($row2['name']==$row_u['shablon'])
         {
            print("<OPTION VALUE=$row2[name] SELECTED> $row2[nick]");
         }
       else
         {
            print("<OPTION VALUE=$row2[name]> $row2[nick]");
         }
      }
  print("</SELECT>\n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_14  \n");
  print("<TD>\n");
  if($row['gauditor']==1)
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"gauditor\" CHECKED> \n");
  else
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"gauditor\" > \n");

  
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$userbuttom_1_prop_UpdateUserForm_13\">\n");
  print("</FORM>\n");

}



function userbuttom_1_prop($userid)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

 $SAMSConf->access=UserAccess();
 if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=updateuserform&filename=userbuttom_1_prop.php&userid=$userid",
	               "basefrm","config_32.jpg","config_48.jpg","$userbuttom_1_prop_userbuttom_1_prop_1");
    }

}







?>
