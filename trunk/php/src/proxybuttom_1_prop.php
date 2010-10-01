<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
 
function ProxyReConfig()
{
  global $SAMSConf;
  global $USERConf;
  global $PROXYConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

$delaypool=0;
$usedomain=0;
$bigdomain=$PROXYConf->s_bigd;
$bigusername=$PROXYConf->s_bigu;
$parser_on=0;
$parser_time=$PROXYConf->s_parser_time;
$count_clean=0;
$sleep=$PROXYConf->s_sleep;
$nameencode=0;
$checkdns=0;
$loglevel=$PROXYConf->s_debuglevel;
$squidbase=$PROXYConf->s_squidbase;
$adminaddr=$PROXYConf->s_adminaddr;

$autouser=0;
$shablon=$PROXYConf->s_autotpl;
$group=$PROXYConf->s_autogrp;

  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["delaypool"])) $delaypool=SetCheckBoxValue($_GET["delaypool"]);
  if(isset($_GET["redirect_to"])) $redirect_to=$_GET["redirect_to"];
  if(isset($_GET["denied_to"])) $denied_to=$_GET["denied_to"];
  if(isset($_GET["redirector"])) $redirector=$_GET["redirector"];
  if(isset($_GET["authtype"])) $auth=$_GET["authtype"];
  if(isset($_GET["wbinfopath"])) $wbinfopath=$_GET["wbinfopath"];
  if(isset($_GET["usedomain"])) $usedomain=SetCheckBoxValue($_GET["usedomain"]);
  if(isset($_GET["bigdomain"])) $bigdomain=$_GET["bigdomain"];
  if(isset($_GET["bigusername"])) $bigusername=$_GET["bigusername"];
  if(isset($_GET["parser_on"])) $parser_on=SetCheckBoxValue($_GET["parser_on"]);
  if(isset($_GET["parser_time"])) $parser_time=$_GET["parser_time"];
  if(isset($_GET["count_clean"])) $count_clean=SetCheckBoxValue($_GET["count_clean"]);
  if(isset($_GET["sleep"])) $sleep=$_GET["sleep"];
  if(isset($_GET["nameencode"])) $nameencode=SetCheckBoxValue($_GET["nameencode"]);
  if(isset($_GET["traffic"])) $traffic=$_GET["traffic"];
  if(isset($_GET["checkdns"])) $checkdns=SetCheckBoxValue($_GET["checkdns"]);
  if(isset($_GET["loglevel"])) $loglevel=$_GET["loglevel"];
  if(isset($_GET["defaultdomain"])) $defaultdomain=$_GET["defaultdomain"];
  if(isset($_GET["squidbase"])) $squidbase=$_GET["squidbase"];
  $udscript="";
  if(isset($_GET["udscript"])) $udscript=$_GET["udscript"];
  if(isset($_GET["adminaddr"])) $adminaddr=$_GET["adminaddr"];
  if(isset($_GET["kbsize"])) $kbsize=$_GET["kbsize"];
  if(isset($_GET["mbsize"])) $mbsize=$_GET["mbsize"];

  if(isset($_GET["defauth"])) $defauth=$_GET["defauth"];

  if(isset($_GET["autouser"])) $autouser=SetCheckBoxValue($_GET["autouser"]);
  if(isset($_GET["shablon"])) $shablon=$_GET["shablon"];
  if(isset($_GET["group"])) $group=$_GET["group"];

  if(isset($_GET["description"])) $description=$_GET["description"];
  $separator="";
  if(isset($_GET["separator"])) 
  {
	if($_GET["separator"]==0) $separator="0+";
	if($_GET["separator"]==1) $separator="0\\\\\\\\";
	if($_GET["separator"]==2) $separator="0@";
  }

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  $query="UPDATE proxy SET s_description='$description', s_debuglevel='$loglevel', s_checkdns='$checkdns', s_realsize='$traffic', 
      s_nameencode='$nameencode', s_sleep='$sleep', s_count_clean='$count_clean', s_parser='$parser_on',
      s_parser_time='$parser_time', s_bigd='$bigdomain', s_bigu='$bigusername', s_usedomain='$usedomain',
      s_delaypool='$delaypool', s_redirect_to='$redirect_to', s_denied_to='$denied_to', s_redirector='$redirector', s_auth='$auth', 
      s_wbinfopath='$wbinfopath', s_defaultdomain='$defaultdomain', s_squidbase='$squidbase', s_udscript='$udscript', 
      s_adminaddr='$adminaddr', 
      s_autouser='$autouser', s_autotpl='$shablon', s_autogrp='$group', s_separator='$separator'  
      WHERE s_proxy_id='$id'";

  $DB->samsdb_query($query);
  $SAMSConf->LoadConfig();

	print("<SCRIPT>\n");
	print("parent.tray.location.href=\"tray.php?show=exe&function=proxytray&filename=proxytray.php&id=$id\";\n");    
	print("  parent.lframe.location.href=\"lframe.php\"; \n");
	print("</SCRIPT> \n");

}


function ProxyReConfigForm()
{
  global $SAMSConf;
  global $PROXYConf;
  global $USERConf;
  $DB=new SAMSDB();

  $files=array();
  if(isset($_GET["id"])) $proxy_id=$_GET["id"];
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;
  
  $DISABLED_PARSER="";

  PageTop("config_48.jpg","Proxy server<BR><FONT COLOR=\"BLUE\">$PROXYConf->s_description</FONT>");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/squid.html\">$documentation</A>");
  print("<P>\n");
  print("<P>\n");

  print("<SCRIPT LANGUAGE=JAVASCRIPT>\n");
  print("function EnableCheckBox(formname)\n");
  print("{\n");
  print("    formname.ntlmdomain.disabled=false; \n");
  print("    formname.bigdomain.disabled=false; \n");
  print("    formname.bigusername.disabled=false; \n");
  print("    formname.nameencode.disabled=false; \n");
  print("}\n");
  print("function DisableCheckBox(formname)\n");
  print("{\n");
  print("    formname.ntlmdomain.disabled=true; \n");
  print("    formname.bigdomain.disabled=true; \n");
  print("    formname.bigusername.disabled=true; \n");
  print("    formname.nameencode.disabled=true; \n");
  print("}\n");
  print("function ChangeAuthScheme(formname)\n");
  print("{\n");
  print("  var auth=formname.authtype.value; \n");
  print("  var domainenabled=formname.usedomain.checked; \n");
  print("  if(auth==\"ip\")\n");
  print("    {\n");
  print("      formname.usedomain.disabled=true; \n");
  print("      formname.bigdomain.disabled=true; \n");
  print("      formname.bigusername.disabled=true; \n");
  print("      formname.separator.disabled=true; \n");
  print("    }\n");
  print("  else if(auth==\"ncsa\")\n");
  print("    {\n");
  print("      formname.usedomain.disabled=true; \n");
  print("      formname.bigdomain.disabled=true; \n");
  print("      formname.bigusername.disabled=true; \n");
  print("      formname.separator.disabled=true; \n");
  print("    }\n");
  print("  else if(auth==\"ldap\")\n");
  print("    {\n");
  print("      formname.usedomain.disabled=true; \n");
  print("      formname.bigdomain.disabled=true; \n");
  print("      formname.bigusername.disabled=true; \n");
  print("      formname.separator.disabled=true; \n");
  print("    }\n");
  print("  else if(auth==\"adld\")\n");
  print("    {\n");
  print("      formname.usedomain.disabled=false; \n");
  print("      formname.separator.disabled=false; \n");
  print("      if(domainenabled==true)\n");
  print("        formname.bigdomain.disabled=false; \n");
  print("      else\n");
  print("        formname.bigdomain.disabled=true; \n");
//  print("      formname.bigusername.disabled=false; \n");
  print("    }\n");
  print("  else if(auth==\"ntlm\")\n");
  print("    {\n");
  print("      formname.usedomain.disabled=false; \n");
  print("      if(domainenabled==true)\n");
  print("      {\n");
  print("        formname.bigdomain.disabled=false; \n");
  print("        formname.bigusername.disabled=false; \n");
  print("        formname.separator.disabled=false; \n");
  print("      }\n");
  print("      else\n");
  print("      {\n");
  print("        formname.bigdomain.disabled=true; \n");
  print("        formname.bigusername.disabled=true; \n");
  print("        formname.separator.disabled=true; \n");
  print("      }\n");
//  print("      formname.bigusername.disabled=false; \n");
  print("    }\n");
  print("}\n");
  print("function EnableDomainName(formname) \n");
  print("{\n");
  print("  var needdomain=formname.usedomain.checked; \n");
  print("  if(needdomain==true) \n");
  print("      {\n");
  print("        formname.bigdomain.disabled=false; \n");
  print("        formname.bigusername.disabled=false; \n");
  print("        formname.separator.disabled=false; \n");
  print("      }\n");
  print("  else \n");
  print("      {\n");
  print("        formname.bigdomain.disabled=true; \n");
  print("        formname.bigusername.disabled=true; \n");
  print("        formname.separator.disabled=true; \n");
  print("      }\n");
  print("}\n");
  print("function EnableParser(formname)");
  print("{");
  print("  var parser_on=formname.parser_on.checked; \n");
  print("  if(parser_on==true) \n");
  print("    {\n");
  print("      formname.parser_time.disabled=false; ");
  print("    }\n");
  print("  else \n");
  print("    {\n");
  print("      formname.parser_time.disabled=true; ");
  print("    }\n");
  print("}\n");
  print("function EnableUserAdd(formname) \n");
  print("{\n");
  print("  var addenabled=formname.autouser.checked; \n");
  print("  if(addenabled==true) \n");
  print("    {\n");
  print("  	formname.shablon.disabled=false; \n");
  print("  	formname.group.disabled=false; \n");
  print("    }\n");
  print("  if(addenabled==false) \n");
  print("    {\n");
  print("  	formname.shablon.disabled=true; \n");
  print("  	formname.group.disabled=true; \n");
  print("    }\n");
  print("}\n");

  print("</SCRIPT>\n");


  print("<FORM NAME=\"samsreconfigform\" ACTION=\"main.php\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"proxyreconfig\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"proxybuttom_1_prop.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"defauth\" value=\"$PROXYConf->s_auth\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$proxy_id\">\n");

  print("<P><B>$adminbuttom_1_prop_SamsReConfigForm_45:</B>\n");
  
  print("<TABLE CLASS=samstable>\n");
  
  print("<TR><TD><B>$CacheForm_squidbuttom_4_addcache_3: </B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"description\" value=\"$PROXYConf->s_description\">\n");
  print("<TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_46: </B>\n");
  print("<TD><SELECT NAME=\"traffic\">\n");
  if($PROXYConf->s_realsize=="real")
    {
       print("<OPTION VALUE=\"real\" SELECTED> $adminbuttom_1_prop_SamsReConfigForm_47");
       print("<OPTION VALUE=\"full\"> $adminbuttom_1_prop_SamsReConfigForm_48");
    }  
  else
    {
       print("<OPTION VALUE=\"real\"> $adminbuttom_1_prop_SamsReConfigForm_47");
       print("<OPTION VALUE=\"full\" SELECTED> $adminbuttom_1_prop_SamsReConfigForm_48");
    }   
  print("</SELECT>\n");
  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_49</B>\n");
  if($PROXYConf->s_checkdns==1)
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"checkdns\" CHECKED>\n");
  else
     {
	        print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"checkdns\" > \n");
    }

  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_51</B>\n");
  print("<TD><SELECT NAME=\"loglevel\">\n");
  for($i=0;$i<10;$i++)
    {
        if($PROXYConf->s_debuglevel==$i)
             print("<OPTION VALUE=\"$i\" SELECTED> $i");
	 else    
             print("<OPTION VALUE=\"$i\"> $i");
    }

  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_52</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"defaultdomain\" value=\"$PROXYConf->s_defaultdomain\">\n");
  print("<TR>\n");

/*
  $scount=0;
  if ($handle2 = opendir("./src/script"))
  {
	while (false !== ($file = readdir($handle2)))
	{
		if($file!="."&&$file!=".."&&$file!=".svn")
		{
			if(strlen($file)>0)
			{
				$script[$scount]=$file;
				$scount++;
			}
		}
	}
  }

  print("<TD><B>$configbuttom_1_prop_SamsReConfigForm_56</B>\n");
  print("<TD><SELECT NAME=\"udscript\" ID=\"udscript\" >\n");
  $SELECTED="";
  if($PROXYConf->s_udscript=="none")
    $SELECTED="SELECTED";
  print("<OPTION VALUE=\"none\" $SELECTED> NONE\n");
  for($i=0;$i<$scount;$i++)
    {
        $SELECTED="";
	if($PROXYConf->s_udscript==$script[$i])
	  $SELECTED="SELECTED";
	print("<OPTION VALUE=\"$script[$i]\" $SELECTED> $script[$i]\n");
    }
  print("</SELECT>\n");
*/  
  print("<TR>\n");
  print("<TD><B>$configbuttom_1_prop_SamsReConfigForm_57</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adminaddr\" value=\"$PROXYConf->s_adminaddr\">\n");
  
  print("</TABLE>\n");

  
  print("<BR>\n");
  print("<P><B>$adminbuttom_1_prop_SamsReConfigForm_17</B>\n");

  print("<TABLE class=samstable>\n");
  print("<TR>\n");
  print("<TD ROWSPAN=3>\n");

  print("<BR><SELECT NAME=\"authtype\" ID=\"authtype\" onchange=ChangeAuthScheme(samsreconfigform)>\n");
  $QUERY="SELECT s_auth FROM auth_param WHERE s_param='enabled' AND s_value='1'";
  $num_rows=$DB->samsdb_query_value($QUERY);
  $row_num=0;
  $selected_auth="";
  while($row=$DB->samsdb_fetch_array())
    {
      $SELECTED="";
      if ($row_num==0)
        {
          $selected_auth=$row['s_auth'];
          $SELECTED="SELECTED";
          $row_num=1;
        }
      if($row['s_auth']==$PROXYConf->s_auth)
        {
          $selected_auth=$row['s_auth'];
          $SELECTED="SELECTED";
        }
      print("<OPTION VALUE=$row[s_auth] $SELECTED> $row[s_auth]");
    }
  print("</SELECT>\n");
  $DB->free_samsdb_query();

  $DOMAINDISABLE="DISABLED";
  $USERDISABLE="DISABLED";
  if($selected_auth=="adld" || $selected_auth=="ntlm")
    $DOMAINDISABLE="ENABLED";
  if($selected_auth!="ip")
    $USERDISABLE="ENABLED";

  print("</TD>\n");
  print("</TR>\n");


  print("<TR>\n");
  print("<TD>\n");

  if($PROXYConf->s_usedomain==1)
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"usedomain\" $DOMAINDISABLE CHECKED onchange=EnableDomainName(samsreconfigform)>$adminbuttom_1_prop_SamsReConfigForm_18\n");
  else
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"usedomain\" $DOMAINDISABLE onchange=EnableDomainName(samsreconfigform)>$adminbuttom_1_prop_SamsReConfigForm_19\n");
 

  print("<BR>$adminbuttom_1_prop_SamsReConfigForm_20 \n");
  print("<SELECT NAME=\"bigdomain\" $DOMAINDISABLE onchange=EnableDomainName(samsreconfigform)>\n");
  if($PROXYConf->s_bigd==0)
            print("<OPTION VALUE=0 SELECTED >$adminbuttom_1_prop_SamsReConfigForm_20a</OPTION>\n");
  else
            print("<OPTION VALUE=0>$adminbuttom_1_prop_SamsReConfigForm_20a</OPTION>\n");
  if($PROXYConf->s_bigd==1)
            print("<OPTION VALUE=1 SELECTED >$adminbuttom_1_prop_SamsReConfigForm_20b</OPTION>\n");
  else
            print("<OPTION VALUE=1 >$adminbuttom_1_prop_SamsReConfigForm_20b</OPTION>\n");
  if($PROXYConf->s_bigd==2)
            print("<OPTION VALUE=2 SELECTED >$adminbuttom_1_prop_SamsReConfigForm_20c</OPTION>\n");
  else
            print("<OPTION VALUE=2>$adminbuttom_1_prop_SamsReConfigForm_20c</OPTION>\n");
  print("</SELECT >\n");
  
  print("<BR>$adminbuttom_1_prop_SamsReConfigForm_22 \n");
  print("<SELECT NAME=\"bigusername\" $USERDISABLE onchange=EnableDomainName(samsreconfigform)>\n");
  if($PROXYConf->s_bigu==0)
            print("<OPTION VALUE=0 SELECTED>$adminbuttom_1_prop_SamsReConfigForm_20a</OPTION>\n");
  else
            print("<OPTION VALUE=0>$adminbuttom_1_prop_SamsReConfigForm_20a</OPTION>\n");
  if($PROXYConf->s_bigu==1)
            print("<OPTION VALUE=1 SELECTED >$adminbuttom_1_prop_SamsReConfigForm_20b</OPTION>\n");
  else
            print("<OPTION VALUE=1 >$adminbuttom_1_prop_SamsReConfigForm_20b</OPTION>\n");
  if($PROXYConf->s_bigu==2)
            print("<OPTION VALUE=2 SELECTED>$adminbuttom_1_prop_SamsReConfigForm_20c</OPTION>\n");
  else
            print("<OPTION VALUE=2>$adminbuttom_1_prop_SamsReConfigForm_20c</OPTION>\n");
  print("</SELECT>\n");


  print("<P>$adminbuttom_1_prop_SamsReConfigForm_50: \n");
  print("<SELECT NAME=\"separator\" $USERDISABLE>\n");
  if(strstr($PROXYConf->s_separator, "+")!=FALSE)
            print("<OPTION VALUE=0 SELECTED>+</OPTION>\n");
  else
            print("<OPTION VALUE=0>+</OPTION>\n");
  if(strstr($PROXYConf->s_separator, "\\")!=FALSE)
            print("<OPTION VALUE=1 SELECTED >\\</OPTION>\n");
  else
            print("<OPTION VALUE=1 >\\</OPTION>\n");
  if(strstr($PROXYConf->s_separator, "@")!=FALSE)
            print("<OPTION VALUE=2 SELECTED>@</OPTION>\n");
  else
            print("<OPTION VALUE=2>@</OPTION>\n");
  print("</SELECT>\n");
  print("</TD>\n");
  print("</TR>\n");

  print("</TABLE>\n");
  

  print("<BR>\n");
  print("<P><CENTER><B>$adminbuttom_1_prop_SamsReConfigForm_30</B></CENTER>\n");
  print("<TABLE class=samstable>\n");

  $SLEEP=1;
  if($PROXYConf->s_sleep>0)
    $SLEEP=$PROXYConf->s_sleep;
  print("<TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_31</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"sleep\" SIZE=5 VALUE=$SLEEP> $adminbuttom_1_prop_SamsReConfigForm_32\n");

  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_38</B>\n");
  if($PROXYConf->s_parser>0)
      print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"parser_on\" onchange=EnableParser(samsreconfigform) CHECKED>\n");
  else
    {
      $DISABLED_PARSER="DISABLED";
      print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"parser_on\" onchange=EnableParser(samsreconfigform)> \n");
    }

  if($PROXYConf->s_parser_time>0)
     $time=$PROXYConf->s_parser_time;
  else
     $time=1;
  print("<TR><TD ALIGN=\"RIGHT\"><B>$adminbuttom_1_prop_SamsReConfigForm_35</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"parser_time\" SIZE=5 VALUE=\"$time\" $DISABLED_PARSER> $adminbuttom_1_prop_SamsReConfigForm_36\n");
  print("<TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_37 </B>\n");
  if($PROXYConf->s_count_clean==1)
     print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"count_clean\" CHECKED >\n");
  else
     print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"count_clean\" > \n");

  print("</TABLE >\n");

  print("<P><TABLE class=samstable>\n");
  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_3</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"wbinfopath\" SIZE=50 VALUE=\"$PROXYConf->s_wbinfopath\">\n");
  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_4</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"redirect_to\" SIZE=50 VALUE=\"$PROXYConf->s_redirect_to\">\n");
  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_5</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"denied_to\" SIZE=50 VALUE=\"$PROXYConf->s_denied_to\"> \n");
  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_6</B>\n");
  print("<TD><SELECT NAME=\"redirector\">\n");

  if($PROXYConf->s_redirector=="none")
            print("<OPTION VALUE=\"none\" SELECTED> $adminbuttom_1_prop_SamsReConfigForm_43");
  else
            print("<OPTION VALUE=\"none\" > $adminbuttom_1_prop_SamsReConfigForm_43");
  if($PROXYConf->s_redirector=="sams")
            print("<OPTION VALUE=\"sams\" SELECTED> $adminbuttom_1_prop_SamsReConfigForm_7");
  else
            print("<OPTION VALUE=\"sams\" > $adminbuttom_1_prop_SamsReConfigForm_7");
  
/*
  if($PROXYConf->s_redirector=="rejik")
            print("<OPTION VALUE=\"rejik\" SELECTED> Rejik");
  else
            print("<OPTION VALUE=\"rejik\"> Rejik");
  
  if($PROXYConf->s_redirector=="squidguard")
            print("<OPTION VALUE=\"squidguard\" SELECTED> SquidGuard");
  else
            print("<OPTION VALUE=\"squidguard\"> SquidGuard");
  if($PROXYConf->s_redirector=="squid")
            print("<OPTION VALUE=\"squid\" SELECTED> $adminbuttom_1_prop_SamsReConfigForm_8");
  else
            print("<OPTION VALUE=\"squid\"> $adminbuttom_1_prop_SamsReConfigForm_8");
*/
  print("</SELECT>\n");
  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_9</B>\n");
  if($PROXYConf->s_delaypool==1)
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"delaypool\" CHECKED> \n");
  else
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"delaypool\" > \n");

  print("<TR>\n");
  print("<TD><B>$configbuttom_1_prop_SamsReConfigForm_53</B>\n");
  print("<TD><SELECT NAME=\"squidbase\">\n");
  $SELECTED="";
  if($PROXYConf->s_squidbase==0)
      $SELECTED="SELECTED";
  print("<OPTION VALUE=\"0\" $SELECTED> $configbuttom_1_prop_SamsReConfigForm_54");
  for($i=1;$i<=12;$i++)
    {
      $SELECTED="";
      if($PROXYConf->s_squidbase==$i)
        $SELECTED="SELECTED";
      print("<OPTION VALUE=\"$i\" $SELECTED> $i");
    } 
  print("</SELECT>\n");
  print("$configbuttom_1_prop_SamsReConfigForm_55\n");

  $USERADD="";
  if($PROXYConf->s_autouser==0)
	{
		$USERADD="DISABLED";
	}  
  print("<TR>\n");
  print("<TD><B>$configbuttom_1_prop_SamsReConfigForm_58</B>\n");
  if($PROXYConf->s_autouser==1)
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"autouser\" onchange=EnableUserAdd(samsreconfigform) CHECKED> \n");
  else
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"autouser\" onchange=EnableUserAdd(samsreconfigform) > \n");

  print("<TR>\n");
  print("<TD><B>$configbuttom_1_prop_SamsReConfigForm_59</B>\n");
  print("<TD><SELECT NAME=\"shablon\" ID=\"shablon\" SIZE=1 TABINDEX=30 $USERADD >\n");
  print("<OPTION VALUE=\"-1\" SELECTED> NONE");
  $num_rows=$DB->samsdb_query_value("SELECT s_shablon_id,s_name FROM shablon");
  while($row=$DB->samsdb_fetch_array())
      {
       if($row['s_shablon_id']==$PROXYConf->s_autotpl)
         {
            print("<OPTION VALUE=$row[s_shablon_id] SELECTED> $row[s_name]");
         }
       else
         {
            print("<OPTION VALUE=$row[s_shablon_id]> $row[s_name]");
         }
      }
  print("</SELECT>\n");
  $DB->free_samsdb_query();


  print("<TR>\n");
  print("<TD><B>$configbuttom_1_prop_SamsReConfigForm_60 $USERADD</B>\n");
  print("<TD><SELECT NAME=\"group\" ID=\"group\" SIZE=1 TABINDEX=30 $USERADD >\n");
  print("<OPTION VALUE=\"-1\" SELECTED> NONE");
  $num_rows=$DB->samsdb_query_value("SELECT s_group_id, s_name FROM sgroup");
  while($row=$DB->samsdb_fetch_array())
      {
       if($row['s_group_id']==$PROXYConf->s_autogrp)
         {
           print("<OPTION VALUE=$row[s_group_id] SELECTED> $row[s_name] ");
         }
       else
         {
           print("<OPTION VALUE=$row[s_group_id]> $row[s_name] ");
         }
      }
  print("</SELECT>\n");
  $DB->free_samsdb_query();
/**/
  
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("</FORM>\n");
}



function proxybuttom_1_prop()
{
  global $SAMSConf;
  global $USERConf;

 if(isset($_GET["id"])) $id=$_GET["id"];
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       GraphButton("main.php?show=exe&function=proxyreconfigform&filename=proxybuttom_1_prop.php&id=$id",
	               "basefrm","config_32.jpg","config_48.jpg","$proxybuttom_1_prop_proxybuttom_1_prop_1");
    }

}







?>
