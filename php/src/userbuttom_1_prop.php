<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UpdateUser()
{
  global $SAMSConf;
  $DB=new SAMSDB("$SAMSConf->DBNAME", "0", $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);
  $gauditor="";
  if(isset($_GET["id"])) $userid=$_GET["id"];
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
   if($SAMSConf->access!=2)     {      exit;    }
  
  if($gauditor=="on")
     $gauditor=1;
  else
     $gauditor=0;
/* 
  if($defstatus!=$enabled)
    {
      if($enabled==1)
           UpdateLog("$SAMSConf->adminname","Activate user $usernick","01");
      if($enabled==-1)
           UpdateLog("$SAMSConf->adminname","Deactivate user $usernick","01");
      if($enabled==0)
           UpdateLog("$SAMSConf->adminname","Deactivate user $usernick","01");
    }
*/
  $num_rows=$DB->samsdb_query_value("SELECT s_passwd FROM squiduser WHERE s_user_id='$userid' ");
  $row=$DB->samsdb_fetch_array();
  $passwd=$row['passwd'];
  $DB->free_samsdb_query();
/* 
  if($auth=="ncsa"||$auth=="ip")
   {
     if(isset($_GET["passwd"])) $passwd=$_GET["passwd"];
     $num_rows=$DB->samsdb_query_value("SELECT s_passwd FROM squiduser WHERE s_user_id='$userid' ");
     $row=$DB->samsdb_fetch_array();
     $defpassw=$row['passwd'];
     $password=crypt($passwd, substr($passwd, 0, 2));
     if($password!=$defpassw)
       $passwd=$password;
   }
*/
  if($domain=="") $domain="workgroup";
  $DB->samsdb_query("UPDATE squiduser SET s_gauditor='$gauditor', s_domain='$domain', s_nick='$usernick', s_family='$userfamily', s_name='$username', s_soname='$usersoname', s_group_id='$usergroup', s_quote='$userquote', s_enabled='$enabled', s_shablon_id='$usershablon', s_ip='$userip', s_passwd='$passwd' WHERE s_user_id='$userid' ");

  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&filename=usertray.php&function=userform&userid=$userid\";\n");
  print("</SCRIPT> \n");

}


/****************************************************************/
function UpdateUserForm()
{

  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB("$SAMSConf->DBNAME", "0", $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);
  $DB2=new SAMSDB("$SAMSConf->DBNAME", "0", $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["userid"])) $userid=$_GET["userid"];

   if($SAMSConf->access!=2)     {      exit;    }
  
  PageTop("user.jpg","$userbuttom_1_prop_UpdateUserForm_1 <FONT COLOR=\"BLUE\">$USERConf->s_nick</FONT>");

  print("<FORM NAME=\"UPDATEUSER\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"updateuser\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"userbuttom_1_prop.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$USERConf->s_user_id\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"auth\" value=\"$USERConf->s_auth\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"defstatus\" value=\"$USERConf->s_enabled\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>Nickname:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$USERConf->s_nick\" NAME=\"usernick\" SIZE=15> \n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_2:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$USERConf->s_domain\" NAME=\"domain\" SIZE=15> \n");

  if( $USERConf->auth=="ncsa"||$USERConf->auth=="ip")
    {
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$userbuttom_1_prop_UpdateUserForm_3:");
       print("<TD>\n");
       //print("<INPUT TYPE=\"PASSWORD\" NAME=\"passwd\" SIZE=20 VALUE=\"$row[passwd]\">");
       print("<INPUT TYPE=\"PASSWORD\" NAME=\"passwd\" SIZE=20>");
    }

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_4: \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$USERConf->s_ip\" NAME=\"userip\" SIZE=15>/ \n");
//  print("<INPUT TYPE=\"TEXT\" VALUE=\"$row[ipmask]\" NAME=\"useripmask\" SIZE=15> \n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_5: \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$USERConf->s_name\" NAME=\"username\" SIZE=30> \n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_6: \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$USERConf->s_soname\" NAME=\"usersoname\" SIZE=30> \n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_7: \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$USERConf->s_family\" NAME=\"userfamily\" SIZE=30> \n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_8: \n");
  print("<TD>\n");
  print("<SELECT NAME=\"usergroup\" ID=\"groupname\" SIZE=1 TABINDEX=30 >\n");

  $num_rows=$DB2->samsdb_query_value("SELECT s_group_id, s_name FROM sgroup");
//  $result_u=mysql_query("SELECT * FROM squidusers WHERE id=\"$row[id]\" ");
//  $row_u=mysql_fetch_array($result_u);
  while($row2=$DB2->samsdb_fetch_array())
      {
       if($row2['s_group_id']==$row['s_group_id'])
         {
           print("<OPTION VALUE=$row2[s_group_id] SELECTED> $row2[s_name]");
         }
       else
         {
           print("<OPTION VALUE=$row2[s_group_id]> $row2[s_name]");
         }
      }
  print("</SELECT>\n");
  $DB2->free_samsdb_query();
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_9 \n");
  print("<TD>\n");
  print(" \n");
  
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_10 \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"userquote\" SIZE=10 VALUE=\"$USERConf->s_quote\"> <B>0 - unlimited traffic \n");
  print("<TD>\n");
  
  
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"saveenabled\" value=\"$USERConf->s_enabled\">\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_11:  \n");
  print("<TD>\n");
  print("<SELECT NAME=\"enabled\" ID=\"enabled\" SIZE=1 TABINDEX=10 >\n");
  if($USERConf->s_enabled==1)
    print("<OPTION VALUE=\"1\" SELECTED> $userbuttom_1_prop_UpdateUserForm_15");
  else
    print("<OPTION VALUE=\"1\"> $userbuttom_1_prop_UpdateUserForm_15"); 
  if($USERConf->s_enabled==0)
    print("<OPTION VALUE=\"0\" SELECTED> $userbuttom_1_prop_UpdateUserForm_16");
  else
    print("<OPTION VALUE=\"0\"> $userbuttom_1_prop_UpdateUserForm_16");
  if($USERConf->s_enabled==-1)
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

  $num_rows=$DB2->samsdb_query_value("SELECT s_shablon_id,s_name FROM shablon");
  while($row2=$DB2->samsdb_fetch_array())
      {
       if($row2['s_shablon_id']==$row['s_shablon_id'])
         {
            print("<OPTION VALUE=$row2[s_shablon_id] SELECTED> $row2[s_name]");
         }
       else
         {
            print("<OPTION VALUE=$row2[s_shablon_id]> $row2[s_name]");
         }
      }
  print("</SELECT>\n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_14  \n");
  print("<TD>\n");
  if($USERConf->gauditor==1)
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
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

 if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe&function=updateuserform&filename=userbuttom_1_prop.php&id=$USERConf->s_user_id",
	               "basefrm","config_32.jpg","config_48.jpg","$userbuttom_1_prop_userbuttom_1_prop_1");
    }

}







?>
