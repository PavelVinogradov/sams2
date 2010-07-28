<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 



function UserAuthForm()
{
  global $SAMSConf;
  global $SquidUSERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $userid=$_GET["id"];
  if(isset($_GET["auth"])) $auth=$_GET["auth"];

  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($userid);

  PageTop("getpassword.jpg","$usertray_UserAuthForm_1 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT>");
  print("<P>\n");
  print("<FORM NAME=\"USERPASSWORD\" ACTION=\"main.php\" method=\"POST\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
//  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"usertray.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"userauth\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$SquidUSERConf->s_user_id\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"auth\" value=\"$auth\">\n");
  print("<TABLE WIDTH=\"90%\">\n");
  print("<TR>\n");
  print("<TD><B>login:</B>\n");

  print("<TD><B>$SquidUSERConf->s_nick\n");
  print("<TR>\n");
  print("<TD><B>password:</B>\n");
  print("<TD><INPUT TYPE=\"PASSWORD\" NAME=\"userid\" SIZE=30> \n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Ok\">\n");
  print("</FORM>\n");
}



function UserForm()
{
  global $SAMSConf;
  $DB=new SAMSDB();
  $DB2=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["userid"])) $userid=$_GET["userid"];
  $num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE s_user_id='$userid' ");
  $row=$DB->samsdb_fetch_array();

  $num_rows2=$DB2->samsdb_query_value("SELECT * FROM sgroup WHERE s_group_id='$row[s_group_id]' ");
  $row2=$DB2->samsdb_fetch_array();

  PageTop("user.jpg","$usertray_UserForm_1 <FONT COLOR=\"BLUE\">$row[nick]</FONT>");

  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>Nickname:\n");
  print("<TD>\n");
  print("$row[s_nick]\n");
  if($SAMSConf->NTLMDOMAIN=="Y")
    {
      print("<TR>\n");
      print("<TD>\n");
      print("<B>$usertray_UserForm_2\n");
      print("<TD>\n");
      print("$row[S_domain]\n");
    }  
  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
      print("<TR>\n");
      print("<TD>\n");
      print("<B>$usertray_UserForm_3:\n");
      print("<TD>\n");
      print("$row[s_ip]\n");
	}
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$usertray_UserForm_4: \n");
  print("<TD>\n");
  print("$row[s_name]\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$usertray_UserForm_5: \n");
  print("<TD>\n");
  print("$row[s_soname]\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$usertray_UserForm_6: \n");
  print("<TD>\n");
  print("$row[s_family] \n");
  print("<TR>\n");
  print("<TD>\n");

  print("<B>$usertray_UserForm_7: \n");
  print("<TD>\n");
  print("$row2[s_name]\n");
  $DB2->free_samsdb_query();

  if($USERConf->ToWebInterfaceAccess("C")==1 || strcasecmp($SAMSConf->domainusername,$row[nick])==0 || $SAMSConf->groupauditor==$row[group])
    {
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$usertray_UserForm_8: \n");
       print("<TD>\n");
             
       if($row['s_quote']>0)
          print(" $row[s_quote] Mb");
       else  
          print(" unlimited ");
//       print("$row[quotes] Mb\n");
       
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$usertray_UserForm_9: \n");
       print("<TD>\n");

       $syea=strftime("%Y");
       $smon=strftime("%m");
       $eday=strftime("%d");
       $sdate="$syea-$smon-1";
       $edate="$syea-$smon-$eday";
       $stime="0:00:00";
       $etime="0:00:00";
//       if($SAMSConf->realtraffic=="real")
//	     PrintTrafficSize($row['s_size']-$row['s_hit']);
//       else
//	     PrintTrafficSize($row['s_size']);
    }

  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$usertray_UserForm_10:\n");
       print("<TD>\n");
       if($row['s_enabled']==2)
          print("$usertray_UserForm_14\n");
       else if($row['s_enabled']==1)
          print("$usertray_UserForm_13\n");
       else if($row['s_enabled']==0)
          print("$usertray_UserForm_15\n");
       else if($row['s_enabled']==-1)
          print("$usertray_UserForm_11\n");
       else
          print("Unknown status\n");

       $num_rows2=$DB2->samsdb_query_value("SELECT * FROM shablon WHERE s_shablon_id='$row[s_shablon_id]' ");
       $row2=$DB2->samsdb_fetch_array();
       print("<TR>\n");
       print("<TD>\n");
       print("<B>$usertray_UserForm_12: \n");
       print("<TD>\n");
       print("<A HREF=\"tray.php?show=exe&function=shablontray&id=$row2[s_shablon_id]\" TARGET=\"tray\">$row2[s_name]</A>\n");
       print("</TABLE>\n");
    }
}

function JSUserInfo()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;
  //var $SamsTools;

  $tools = new SamsTools();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SquidUSERConf->s_quote>0)
    $quote=" $SquidUSERConf->s_quote Mb";
  else if($SquidUSERConf->s_quote==-1)
    $quote=" $SquidUSERConf->s_defquote Mb";
  else
    $quote=" unlimited ";

  if($SquidUSERConf->s_enabled==2)
    $enabled="$usertray_UserForm_14";
  else if($SquidUSERConf->s_enabled==1)
    $enabled="$usertray_UserForm_13";
  else if($SquidUSERConf->s_enabled==0)
    $enabled="$usertray_UserForm_15";
  else if($SquidUSERConf->s_enabled==-1)
    $enabled="$usertray_UserForm_11";
  else
    $enabled="Unknown status";


  $htmlcode="<HTML><BODY><CENTER>
  <TABLE WIDTH=\"95%\" border=0><TR><TD WIDTH=\"10%\"  valign=\"middle\">
  <img src=\"$SAMSConf->ICONSET/user.jpg\" align=\"RIGHT\" valign=\"middle\" >
  <TD  valign=\"middle\"><h2  align=\"CENTER\">$usertray_UserForm_1 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT></h2>
  </TABLE>
  <TABLE>
  <TR><TD><B>Nickname:<TD>$SquidUSERConf->s_nick 
  <TR><TD><B>$usertray_UserForm_2<TD>$SquidUSERConf->s_domain";
  if($USERConf->ToWebInterfaceAccess("C")==1)
	$htmlcode=$htmlcode."<TR><TD><B>$usertray_UserForm_3:<TD>$SquidUSERConf->s_ip";
  $htmlcode=$htmlcode."<TR><TD><B>$usertray_UserForm_4:<TD>$SquidUSERConf->s_name
  <TR><TD><B>$usertray_UserForm_5:<TD>$SquidUSERConf->s_soname
  <TR><TD><B>$usertray_UserForm_6:<TD>$SquidUSERConf->s_family
  <TR><TD><B>$usertray_UserForm_7:<TD>$SquidUSERConf->s_group_name
  <TR><TD><B>$usertray_UserForm_10:<TD>$enabled";
	$htmlcode=$htmlcode."<TR><TD><B>$usertray_UserForm_8:<TD>$quote 
	<TR><TD><B>$usertray_UserForm_9:".$tools->PrintFormattedSize($SquidUSERConf->s_size, "LEFT");
	//<TR><TD><B>$usertray_UserForm_9:<TD>$SquidUSERConf->s_size";
  if($USERConf->ToWebInterfaceAccess("C")==1)
	{
	$htmlcode=$htmlcode."<TR><TD><B>$usertray_UserForm_12:<TD>$SquidUSERConf->s_shablon_name";
	if( $SquidUSERConf->W_access == 1 ) 
		$htmlcode=$htmlcode."<TR><TD>$userbuttom_1_prop_UpdateUserForm_18";
	if( $SquidUSERConf->G_access == 1 ) 
		$htmlcode=$htmlcode."<TR><TD>$userbuttom_1_prop_UpdateUserForm_19";
	if($SquidUSERConf->S_access==1) 
		$htmlcode=$htmlcode."<TR><TD>$userbuttom_1_prop_UpdateUserForm_20";
	if($SquidUSERConf->A_access==1) 
		$htmlcode=$htmlcode."<TR><TD>$userbuttom_1_prop_UpdateUserForm_21";
	if($SquidUSERConf->U_access==1) 
		$htmlcode=$htmlcode."<TR><TD>$userbuttom_1_prop_UpdateUserForm_22";
	if($SquidUSERConf->L_access==1)
		$htmlcode=$htmlcode."<TR><TD>$userbuttom_1_prop_UpdateUserForm_23";
	if($SquidUSERConf->C_access==1)
		$htmlcode=$htmlcode."<TR><TD>$userbuttom_1_prop_UpdateUserForm_24";
    }

 $htmlcode=$htmlcode."  </TABLE>";

  $htmlcode=$htmlcode."</CENTER></BODY></HTML>";
  $htmlcode=str_replace("\"","\\\"",$htmlcode);
  $htmlcode=str_replace("\n","",$htmlcode);
  print(" parent.basefrm.document.write(\"$htmlcode\");\n");
  print(" parent.basefrm.document.close();\n");

}


function UserTray()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["auth"])) $auth=$_GET["auth"];
  
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);

//echo "samsadmin=$USERConf->s_samsadmin userid=$USERConf->s_user_id=$id=$SAMSConf->USERID $USERConf->s_webaccess";
	if($USERConf->s_samsadmin==1 || $USERConf->s_user_id==$id || $USERConf->ToGroupStatAccess("GS", $SquidUSERConf->s_group_id)==1 )
	{
		print("<SCRIPT>\n");
		JSUserInfo();
		print("</SCRIPT> \n");

		print("<TABLE border=0 WIDTH=95%>\n");
		print("<TR HEIGHT=60>\n");
		print("<TD WIDTH=25%>");
		print("<B>$usertray_UserTray_1 <BR> <FONT SIZE=\"+1\" COLOR=\"blue\">$SquidUSERConf->s_nick </FONT> </B>\n");
		ExecuteFunctions("./src", "userbuttom", $USERConf->s_user_id);

		print("<TD>\n");
		print("</TABLE>\n");
	}
	else
	{
		print("<SCRIPT>\n");
		print("parent.basefrm.location.href=\"main.php?show=exe&filename=usertray.php&function=userauthform&id=$id&auth=$auth\";\n");
		print("</SCRIPT> \n");
		exit(0);
	}

}

?>
