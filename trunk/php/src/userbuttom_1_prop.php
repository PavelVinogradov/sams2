<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UpdateUser()
{
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
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
  if(isset($_GET["individuallimit"])) $individuallimit=$_GET["individuallimit"];

  $s_webaccess="";
  if(isset($_GET["W_access"])) $s_webaccess="W";
  if(isset($_GET["G_access"])) $s_webaccess=$s_webaccess."G";
  if(isset($_GET["S_access"])) $s_webaccess=$s_webaccess."S";
  if(isset($_GET["A_access"])) $s_webaccess=$s_webaccess."A";
  if(isset($_GET["U_access"])) $s_webaccess=$s_webaccess."U";
  if(isset($_GET["L_access"])) $s_webaccess=$s_webaccess."L";
  if(isset($_GET["C_access"])) $s_webaccess=$s_webaccess."C";

  if($USERConf->ToWebInterfaceAccess("AUC")!=1)
	{
		exit;    
	}
  
  if($gauditor=="on")
     $gauditor=1;
  else
     $gauditor=0;

  if($domain=="") 
	$domain="workgroup";
  if($individuallimit!="on")
	$userquote=-1;

  $DB->samsdb_query("UPDATE squiduser SET s_webaccess='$s_webaccess',s_gauditor='$gauditor', s_domain='$domain', s_nick='$usernick', s_family='$userfamily', s_name='$username', s_soname='$usersoname', s_group_id='$usergroup', s_quote='$userquote', s_enabled='$enabled', s_shablon_id='$usershablon', s_ip='$userip' WHERE s_user_id='$userid' ");
  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("        parent.tray.location.href=\"tray.php?show=exe&filename=usertray.php&function=usertray&id=$userid\";\n");
  print("</SCRIPT> \n");

}


/****************************************************************/
function UpdateUserForm()
{

  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;

  $DB=new SAMSDB();
  $DB2=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $userid=$_GET["id"];

 if($USERConf->ToWebInterfaceAccess("AUC")!=1)
	{      
		exit;    
	}

  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($userid);

  
  PageTop("user.jpg","$userbuttom_1_prop_UpdateUserForm_1 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT>");

  print("<FORM NAME=\"UPDATEUSER\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"updateuser\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"userbuttom_1_prop.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$userid\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"auth\" value=\"$SquidUSERConf->s_auth\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"defstatus\" value=\"$SquidUSERConf->s_enabled\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>Nickname:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$SquidUSERConf->s_nick\" NAME=\"usernick\" SIZE=15> \n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_2:\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$SquidUSERConf->s_domain\" NAME=\"domain\" SIZE=15> \n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_4: \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$SquidUSERConf->s_ip\" NAME=\"userip\" SIZE=15>/ \n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_5: \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$SquidUSERConf->s_name\" NAME=\"username\" SIZE=30> \n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_6: \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$SquidUSERConf->s_soname\" NAME=\"usersoname\" SIZE=30> \n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_7: \n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" VALUE=\"$SquidUSERConf->s_family\" NAME=\"userfamily\" SIZE=30> \n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_8: \n");
  print("<TD>\n");
  print("<SELECT NAME=\"usergroup\" ID=\"groupname\" SIZE=1 TABINDEX=30 >\n");

  $num_rows=$DB2->samsdb_query_value("SELECT s_group_id, s_name FROM sgroup");

  while($row2=$DB2->samsdb_fetch_array())
      {
       if($row2['s_group_id']==$SquidUSERConf->s_group_id)
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

  print("<SCRIPT LANGUAGE=JAVASCRIPT>\n");
  print("function EnableIndividualQuote(formname)\n");
  print("{\n");
  print("  if(formname.individuallimit.checked==true)\n");
  print("  {\n");
  print("    formname.userquote.disabled=false\n");
  print("  }\n");
  print("  if(formname.individuallimit.checked==false)\n");
  print("  {\n");
  print("    formname.userquote.disabled=true\n");
  print("  }\n");
  print("}\n");
  print("</SCRIPT>\n");
  
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_25 \n");
  print("<TD>\n");
  if($SquidUSERConf->s_quote!=-1)
  {
	print("<INPUT TYPE=\"CHECKBOX\" NAME=\"individuallimit\" CHECKED onclick=EnableIndividualQuote(UPDATEUSER)> \n");
	$QDISABLED="";
  }
  else
  {
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"individuallimit\" onclick=EnableIndividualQuote(UPDATEUSER) > \n");
	$QDISABLED="DISABLED";
  }
  
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_9 $userbuttom_1_prop_UpdateUserForm_10 </B>\n");
  print("<TD>\n");
  if($SquidUSERConf->s_quote==-1)
	$uquote=$SquidUSERConf->s_defquote;
  else
	$uquote=$SquidUSERConf->s_quote;
  print("<INPUT TYPE=\"TEXT\" NAME=\"userquote\" SIZE=10 VALUE=\"$uquote\" $QDISABLED> <B>0 - unlimited traffic \n");
  print("<TD>\n");
  
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"saveenabled\" value=\"$SquidUSERConf->s_enabled\">\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_11:  \n");
  print("<TD>\n");
  print("<SELECT NAME=\"enabled\" ID=\"enabled\" SIZE=1 TABINDEX=10 >\n");
  if($SquidUSERConf->s_enabled==1)
    print("<OPTION VALUE=\"1\" SELECTED> $userbuttom_1_prop_UpdateUserForm_15");
  else
    print("<OPTION VALUE=\"1\"> $userbuttom_1_prop_UpdateUserForm_15"); 
  if($SquidUSERConf->s_enabled==2)
    print("<OPTION VALUE=\"2\" SELECTED> $userbuttom_1_prop_UpdateUserForm_25");
  else
    print("<OPTION VALUE=\"2\"> $userbuttom_1_prop_UpdateUserForm_25"); 
  if($SquidUSERConf->s_enabled==0)
    print("<OPTION VALUE=\"0\" SELECTED> $userbuttom_1_prop_UpdateUserForm_16");
  else
    print("<OPTION VALUE=\"0\"> $userbuttom_1_prop_UpdateUserForm_16");
  if($SquidUSERConf->s_enabled==-1)
    print("<OPTION VALUE=\"-1\" SELECTED> $userbuttom_1_prop_UpdateUserForm_17");
  else
    print("<OPTION VALUE=\"-1\"> $userbuttom_1_prop_UpdateUserForm_17");
  print("</SELECT>\n");

  print("<TR>\n");
  print("<TD>\n");
  print("<B>$userbuttom_1_prop_UpdateUserForm_12: \n");
  print("<TD>\n");
  print("<SELECT NAME=\"usershablon\" ID=\"usershablon\" SIZE=1 TABINDEX=30 >\n");

  $num_rows=$DB2->samsdb_query_value("SELECT s_shablon_id,s_name,s_quote FROM shablon");
  while($row2=$DB2->samsdb_fetch_array())
      {
       if($row2['s_shablon_id']==$SquidUSERConf->s_shablon_id)
         {
            print("<OPTION VALUE=\"$row2[s_shablon_id]\" SELECTED> $row2[s_name]");
         }
       else
         {
            print("<OPTION VALUE=\"$row2[s_shablon_id]\"> $row2[s_name]");
         }
      }
  print("</SELECT>\n");

  print("<TR><TD><B>$userbuttom_1_prop_UpdateUserForm_18 (W)\n");
  print("<TD>\n");
  $WCHECKED="";
  if($SquidUSERConf->W_access==1)
	$WCHECKED="CHECKED";
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"W_access\" $WCHECKED> \n");

  print("<TR><TD><B>$userbuttom_1_prop_UpdateUserForm_19 (G)\n");
  print("<TD>\n");
  $GCHECKED="";
  if($SquidUSERConf->G_access==1)
	$GCHECKED="CHECKED";
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"G_access\" $GCHECKED> \n");

  print("<TR><TD><B>$userbuttom_1_prop_UpdateUserForm_20 (S)\n");
  print("<TD>\n");
  $SCHECKED="";
  if($SquidUSERConf->S_access==1)
	$SCHECKED="CHECKED";
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"S_access\" $SCHECKED> \n");

  print("<TR><TD><B>$userbuttom_1_prop_UpdateUserForm_21 (A)\n");
  print("<TD>\n");
  $ACHECKED="";
  if($SquidUSERConf->A_access==1)
	$ACHECKED="CHECKED";
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"A_access\" $ACHECKED> \n");

  print("<TR><TD><B>$userbuttom_1_prop_UpdateUserForm_22 (U)\n");
  print("<TD>\n");
  $UCHECKED="";
  if($SquidUSERConf->U_access==1)
	$UCHECKED="CHECKED";
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"U_access\" $UCHECKED> \n");

  print("<TR><TD><B>$userbuttom_1_prop_UpdateUserForm_23 (L)\n");
  print("<TD>\n");
  $LCHECKED="";
  if($SquidUSERConf->L_access==1)
	$LCHECKED="CHECKED";
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"L_access\" $LCHECKED> \n");

  print("<TR><TD><B>$userbuttom_1_prop_UpdateUserForm_24 (C)\n");
  print("<TD>\n");
  $CCHECKED="";
  if($SquidUSERConf->C_access==1)
	$CCHECKED="CHECKED";
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"C_access\" $CCHECKED> \n");

  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$userbuttom_1_prop_UpdateUserForm_13\">\n");
  print("</FORM>\n");

}



function userbuttom_1_prop()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);


 if($USERConf->ToWebInterfaceAccess("AUC")==1)
    {
       GraphButton("main.php?show=exe&function=updateuserform&filename=userbuttom_1_prop.php&id=$SquidUSERConf->s_user_id", "basefrm","config_32.jpg","config_48.jpg","$userbuttom_1_prop_userbuttom_1_prop_1 ");
    }

}







?>
