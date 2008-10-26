<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
 
function TestPDC()
{
  global $SAMSConf;
  $info=array();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["auth"])) $auth=$_GET["auth"];
  print("<H1>TEST PDC</H1>");

  $value=ExecuteShellScript("getwbinfousers","$SAMSConf->WBINFOPATH");
  $a=explode(" ",$value);
  sort($a);
  $acount=count($a);
	  
  if($auth=="ntlm")
    {
      for($i=0;$i<$acount;$i++)
         print("$a[$i]<BR>\n");

   }   
 if($auth=="adld")
    {
       require_once("adldap.php");
       //create the LDAP connection

 	$pdc=array("$SAMSConf->LDAPSERVER");
	$options=array(account_suffix=>"@$SAMSConf->LDAPDOMAIN", base_dn=>"$SAMSConf->LDAPBASEDN",domain_controllers=>$pdc, 
	ad_username=>"$SAMSConf->LDAPUSER",ad_password=>"$SAMSConf->LDAPUSERPASSWD","","","");

	$ldap=new adLDAP($options);

	$groups=$ldap->all_groups($include_desc = false, $search = "*", $sorted = true);
	$gcount=count($groups);
        print("<TABLE CLASS=samstable>");
        print("<TH width=5%>No");
        print("<TH >$SAMSConf->LDAPDOMAIN groups");
	for($i=0;$i<$gcount;$i++)
		echo "<TR><TD>$i:<TD>$groups[$i]<BR>";
	echo "</TABLE><P>";

	$users=$ldap->all_users($include_desc = false, $search = "*", $sorted = true);
	$count=count($users);
        print("<TABLE CLASS=samstable>");
        print("<TH width=5%>No");
        print("<TH >$SAMSConf->LDAPDOMAIN users");
	for($i=0;$i<$count;$i++)
   	{
		$userinfo=$ldap->user_info( $users[$i], $fields=NULL);
		//$mcount=count($userinfo);
        	echo "<TR><TD>$i:<TD> $users[$i] ";
		$aaa = $userinfo[0]["displayname"][0];
		//$aaa2 = $userinfo[0]["givenname"][0];
		//$aaa3 = $userinfo[0]["sn"][0];
		echo "<TD>$aaa ";
    	}
	echo "</TABLE>";
   }
}   

function SamsReConfig()
{
  global $SAMSConf;
  $DB=new SAMSDB("$SAMSConf->DB_ENGINE", $SAMSConf->ODBC, $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB, $SAMSConf->PDO);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $at="";
  $nameencode="N";
  $parser_on="N";
  $checkdns="N";
  $parser="digit";
  $parser_time="1";
  $adminaddr="root@localhost";
  if(isset($_GET["delaypool"])) $delaypool=$_GET["delaypool"];
  if(isset($_GET["redirect_to"])) $redirect_to=$_GET["redirect_to"];
  if(isset($_GET["denied_to"])) $denied_to=$_GET["denied_to"];
  if(isset($_GET["redirector"])) $redirector=$_GET["redirector"];
  if(isset($_GET["auth"])) $auth=$_GET["auth"];
  if(isset($_GET["wbinfopath"])) $wbinfopath=$_GET["wbinfopath"];
  if(isset($_GET["ntlmdomain"])) $ntlmdomain=$_GET["ntlmdomain"];
  if(isset($_GET["bigdomain"])) $bigdomain=$_GET["bigdomain"];
  if(isset($_GET["bigusername"])) $bigusername=$_GET["bigusername"];
  if(isset($_GET["parser_on"])) $parser_on=$_GET["parser_on"];
  if(isset($_GET["parser"])) $parser=$_GET["parser"];
  if(isset($_GET["parser_time"])) $parser_time=$_GET["parser_time"];
  if(isset($_GET["count_clean"])) $count_clean=$_GET["count_clean"];
  if(isset($_GET["sleep"])) $sleep=$_GET["sleep"];
  if(isset($_GET["nameencode"])) $nameencode=$_GET["nameencode"];
  if(isset($_GET["traffic"])) $traffic=$_GET["traffic"];
  if(isset($_GET["checkdns"])) $checkdns=$_GET["checkdns"];
  if(isset($_GET["loglevel"])) $loglevel=$_GET["loglevel"];
  
  if(isset($_GET["at"])) $at=$_GET["at"];
  if(isset($_GET["slashe"])) $slashe=$_GET["slashe"];
  if(isset($_GET["plus"])) $plus=$_GET["plus"];
  
  if(isset($_GET["defaultdomain"])) $defaultdomain=$_GET["defaultdomain"];
  if(isset($_GET["squidbase"])) $squidbase=$_GET["squidbase"];

  if(isset($_GET["udscript"])) $udscript=$_GET["udscript"];
  if(isset($_GET["adminaddr"])) $adminaddr=$_GET["adminaddr"];
  if(isset($_GET["defauth"])) $defauth=$_GET["defauth"];

   if($SAMSConf->access!=2)     {       exit;     }
  
  if($at=="on")
    $at="@";
  if($slashe=="on")
    $slashe="\\";
  if($plus=="on")
    $plus="+";

  if($nameencode=="on")
    $nameencode="Y";
  if($count_clean=="on")
    $count_clean="Y";
  if($parser_on=="on")
    $parser_on="Y";
  if($ntlmdomain=="on")
    $ntlmdomain="Y";
  if($delaypool=="on")
    $delaypool="Y";
  if($checkdns=="on")
    $checkdns="Y";
  if(  strlen($adminaddr)=="")
	  $adminaddr="root@localhost";

  $DB->samsdb_query("UPDATE sams SET s_loglevel='$loglevel', s_separator='0$plus$at$slashe$slashe', s_checkdns='$checkdns', s_realsize='$traffic', 
                                    s_nameencode='$nameencode', s_sleep='$sleep', s_count_clean='$count_clean', s_parser_on='$parser_on', s_parser='$parser', 
                                    s_parser_time='$parser_time', s_bigd='$bigdomain', s_bigu='$bigusername', s_ntlmdomain='$ntlmdomain',
                                    s_delaypool='$delaypool', s_redirect_to='$redirect_to', s_denied_to='$denied_to', s_redirector='$redirector', s_auth='$auth', 
                                    s_wbinfopath='$wbinfopath', s_defaultdomain='$defaultdomain', s_squidbase='$squidbase', s_udscript='$udscript', 
                                    s_adminaddr='$adminaddr' ");
  if($defauth!=$auth)
    $DB->samsdb_query("UPDATE shablons SET s_auth='$auth' WHERE s_auth!='ip' ");
  $SAMSConf->LoadConfig();
  PageTop("config_48.jpg","$adminbuttom_1_prop_SamsReConfig_1");

}


function SamsReConfigForm()
{
  global $SAMSConf;
  $files=array();
  $DB=new SAMSDB("$SAMSConf->DB_ENGINE", $SAMSConf->ODBC, $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB, $SAMSConf->PDO);
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
 if($SAMSConf->access!=2)     {       exit;     }
  

  PageTop("config_48.jpg","$adminbuttom_1_prop_SamsReConfigForm_1");
  print("<P>\n");

           print("<SCRIPT LANGUAGE=JAVASCRIPT>\n");
           print("function EnableCheckBox(formname)\n");
           print("{\n");
           print("    formname.ntlmdomain.disabled=false; \n");
           print("    formname.bigdomain.disabled=false; \n");
           print("    formname.bigusername.disabled=false; \n");
           print("    formname.nameencode.disabled=false; \n");
           print("    formname.testpdc.disabled=false; \n");
           print("    formname.plus.disabled=false; \n");
           print("    formname.at.disabled=false; \n");
           print("    formname.slashe.disabled=false; \n");
//		   print("    document.getElementById('c1').innerHTML='YES'; ");
		   print("}\n");
           print("function DisableCheckBox(formname)\n");
           print("{\n");
           print("    formname.ntlmdomain.disabled=true; \n");
           print("    formname.bigdomain.disabled=true; \n");
           print("    formname.bigusername.disabled=true; \n");
           print("    formname.nameencode.disabled=true; \n");
           print("    formname.testpdc.disabled=true; \n");
           print("    formname.plus.disabled=true; \n");
           print("    formname.at.disabled=true; \n");
           print("    formname.slashe.disabled=true; \n");
           print("}\n");

	   print("function TestPDC(formname)\n");
           print("{\n");
           print("  if(formname.auth[0].checked==true)\n");
           print("    {\n");
           print("      window.open('main.php?show=exe&function=testpdc&filename=configbuttom_1_prop.php&auth=ntlm'); \n");
           print("    }\n");
           print("  if(formname.auth[1].checked==true)\n");
           print("    {\n");
           print("      window.open('main.php?show=exe&function=testpdc&filename=configbuttom_1_prop.php&auth=adld'); \n");
           print("    }\n");
           print("}\n");

           print("</SCRIPT>\n");

           print("</SCRIPT>\n");

  $num_rows=$DB->samsdb_query_value("SELECT * FROM sams ");
  $row=$DB->samsdb_fetch_array();
  print("<FORM NAME=\"samsreconfigform\" ACTION=\"main.php\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"samsreconfig\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"configbuttom_1_prop.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"defauth\" value=\"$row[s_auth]\">\n");

  print("<P><B>$adminbuttom_1_prop_SamsReConfigForm_45:</B>\n");
  
  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");
  
  print("<TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_46: </B>\n");
  print("<TD><SELECT NAME=\"traffic\">\n");
  if($row['s_realsize']=="real")
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
  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_49</B>\n");
  if($row['s_checkdns']=="Y")
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"checkdns\" CHECKED>\n");
  else
     {
	        print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"checkdns\" > \n");
    }

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_51</B>\n");
  print("<TD><SELECT NAME=\"loglevel\">\n");
  for($i=0;$i<10;$i++)
    {
        if($row['s_loglevel']==$i)
             print("<OPTION VALUE=\"$i\" SELECTED> $i");
	 else    
             print("<OPTION VALUE=\"$i\"> $i");
    }

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_52</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"defaultdomain\" value=\"$row[s_defaultdomain]\">\n");
            
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
  print("<TR>\n");
  print("<TD><B>$configbuttom_1_prop_SamsReConfigForm_56</B>\n");
  print("<TD><SELECT NAME=\"udscript\" ID=\"udscript\" >\n");
  $SELECTED="";
  if($row['s_udscript']=="none")
    $SELECTED="SELECTED";
  print("<OPTION VALUE=\"none\" $SELECTED> NONE\n");
  for($i=0;$i<$scount;$i++)
    {
        $SELECTED="";
	if($row['s_udscript']==$script[$i])
	  $SELECTED="SELECTED";
	print("<OPTION VALUE=\"$script[$i]\" $SELECTED> $script[$i]\n");
    }
  print("</SELECT>\n");
  
  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$configbuttom_1_prop_SamsReConfigForm_57</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adminaddr\" value=\"$row[s_adminaddr]\">\n");
  
  print("</TABLE>\n");

  
  print("<P><B>$adminbuttom_1_prop_SamsReConfigForm_17</B>\n");


  print("<TABLE WIDTH=\"90%\" BORDER=0 >\n");
  print("<TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_2</B><TD>\n");
  $NTLMCHECKED="";
  $NCSACHECKED="";
  $IPCHECKED="";
  $ADLDCHECKED="";
  $DOMAINDISABLE="DISABLED";
  if($row['s_auth']=="ip")
	        		$IPCHECKED="CHECKED";
  else if($row['s_auth']=="ncsa")
    	   			 $NCSACHECKED="CHECKED";
  else if($row['s_auth']=="adld")
        {
    	   			 $ADLDCHECKED="CHECKED";
				 $DOMAINDISABLE="ENABLED";
	} 
  else
        {
       				 $NTLMCHECKED="CHECKED";
				 $DOMAINDISABLE="ENABLED";
	}
  print("<TR bgcolor=blanchedalmond><TD VALIGN=TOP >");
  print("<INPUT TYPE=\"RADIO\" NAME=\"auth\" VALUE=\"ntlm\" $NTLMCHECKED  onclick=EnableCheckBox(samsreconfigform) onchange=EnableDomainName(samsreconfigform)>\n");
  $sdomain="";
  $suser="";
  if($row['s_ntlmdomain']=="Y")
    {
      if($row['s_bigd']=="Y")            $sdomain="DOMAIN +";
      if($row['s_bigd']=="S")            $sdomain="domain +";
      if($row['s_bigd']=="N")            $sdomain="Domain +";
    }
  if($row['s_bigu']=="Y")            $suser="USER";
  if($row['s_bigu']=="S")            $suser="user";
  if($row['s_bigu']=="N")            $suser="User";
  
  print("  <B>NTLM (</B>\n<B ID=\"DomainUser\"> $sdomain $suser");
  print("  </B>\n<B>)</B>\n");
  print("<BR><INPUT TYPE=\"RADIO\" NAME=\"auth\" VALUE=\"adld\" $ADLDCHECKED  onclick=EnableCheckBox(samsreconfigform) onchange=EnableDomainName(samsreconfigform)>\n");
  print("  <B>Active Directory</B><BR>(Experimental)\n");
  
  print("<TD name=c1  ID=\"c1\">");
  if($row['s_ntlmdomain']=="Y")
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"ntlmdomain\" $DOMAINDISABLE CHECKED onchange=EnableDomainName(samsreconfigform)>$adminbuttom_1_prop_SamsReConfigForm_18\n");
  else
     print("<INPUT TYPE=\"CHECKBOX\" NAME=\"ntlmdomain\" $DOMAINDISABLE onchange=EnableDomainName(samsreconfigform)>$adminbuttom_1_prop_SamsReConfigForm_19\n");
  
//  print("<BR>$adminbuttom_1_prop_SamsReConfigForm_20\n");
  print("<BR><LI>$adminbuttom_1_prop_SamsReConfigForm_20 \n");
  print("<SELECT NAME=\"bigdomain\" onchange=EnableDomainName(samsreconfigform)>\n");
  if($row['s_bigd']=="Y")
            print("<OPTION VALUE=\"Y\" SELECTED >$adminbuttom_1_prop_SamsReConfigForm_20a</OPTION>\n");
  else
            print("<OPTION VALUE=\"Y\">$adminbuttom_1_prop_SamsReConfigForm_20a</OPTION>\n");
     
  if($row['s_bigd']=="S")
            print("<OPTION VALUE=\"S\" SELECTED >$adminbuttom_1_prop_SamsReConfigForm_20b</OPTION>\n");
  else
            print("<OPTION VALUE=\"S\" >$adminbuttom_1_prop_SamsReConfigForm_20b</OPTION>\n");
  if($row['s_bigd']!="Y"&&$row['s_bigd']!="S")
            print("<OPTION VALUE=\"N\" SELECTED >$adminbuttom_1_prop_SamsReConfigForm_20c</OPTION>\n");
  else
            print("<OPTION VALUE=\"N\">$adminbuttom_1_prop_SamsReConfigForm_20c</OPTION>\n");
  if($row['s_bigd']=="A")
            print("<OPTION VALUE=\"A\" SELECTED >$adminbuttom_1_prop_SamsReConfigForm_20a & $adminbuttom_1_prop_SamsReConfigForm_20b</OPTION>\n");
  else
            print("<OPTION VALUE=\"A\" >$adminbuttom_1_prop_SamsReConfigForm_20a & $adminbuttom_1_prop_SamsReConfigForm_20b</OPTION>\n");
  
  
  
  print("</SELECT > $adminbuttom_1_prop_SamsReConfigForm_20d\n");

  print("<BR><LI>$adminbuttom_1_prop_SamsReConfigForm_22 \n");
  print("<SELECT NAME=\"bigusername\" onchange=EnableDomainName(samsreconfigform)>\n");
  if($row['s_bigu']=="Y")
            print("<OPTION VALUE=\"Y\" SELECTED>$adminbuttom_1_prop_SamsReConfigForm_20a</OPTION>\n");
  else
            print("<OPTION VALUE=\"Y\">$adminbuttom_1_prop_SamsReConfigForm_20a</OPTION>\n");
     
  if($row['s_bigu']=="S")
            print("<OPTION VALUE=\"S\" SELECTED >$adminbuttom_1_prop_SamsReConfigForm_20b</OPTION>\n");
  else
            print("<OPTION VALUE=\"S\" >$adminbuttom_1_prop_SamsReConfigForm_20b</OPTION>\n");
  if($row['s_bigu']!="Y"&&$row['s_bigu']!="S")
            print("<OPTION VALUE=\"N\" SELECTED>$adminbuttom_1_prop_SamsReConfigForm_20c</OPTION>\n");
  else
            print("<OPTION VALUE=\"N\">$adminbuttom_1_prop_SamsReConfigForm_20c</OPTION>\n");
  print("</SELECT >$adminbuttom_1_prop_SamsReConfigForm_20d\n");
  
  print("<P><B> $adminbuttom_1_prop_SamsReConfigForm_50 </B>\n");
  if(strpos($row['s_separator'],"+")!=false)
     print("<P><INPUT TYPE=\"CHECKBOX\" NAME=\"plus\" CHECKED $DOMAINDISABLE> <B>+</B>\n");
  else
     print("<P><INPUT TYPE=\"CHECKBOX\" NAME=\"plus\" $DOMAINDISABLE> <B>+</B>\n");
  if(strpos($row['s_separator'],chr(92) )!=false)
     print("<BR><INPUT TYPE=\"CHECKBOX\" NAME=\"slashe\" CHECKED $DOMAINDISABLE> <B>\\</B> \n");
  else
     print("<BR><INPUT TYPE=\"CHECKBOX\" NAME=\"slashe\" $DOMAINDISABLE> <B>\\</B> \n");
  if(strpos($row['s_separator'],chr(64) )!=false)
     print("<BR><INPUT TYPE=\"CHECKBOX\" NAME=\"at\" CHECKED $DOMAINDISABLE> <B>@</B> \n");
  else
     print("<BR><INPUT TYPE=\"CHECKBOX\" NAME=\"at\"  $DOMAINDISABLE> <B>@</B> \n");
     

  
  print("<P >\n");

     
  if($row['s_auth']=="ntlm"||$row['s_auth']=="adld")
     print("<BR><INPUT TYPE=\"BUTTON\" NAME=\"testpdc\" VALUE=\"$adminbuttom_1_prop_SamsReConfigForm_39\" onclick=TestPDC(samsreconfigform) >\n");
  else
     print("<BR><INPUT TYPE=\"BUTTON\" NAME=\"testpdc\" VALUE=\"$adminbuttom_1_prop_SamsReConfigForm_39\" onclick=TestPDC(samsreconfigform) DISABLED>\n");

  if($row['s_nameencode']=="Y")
     print("<P><INPUT TYPE=\"CHECKBOX\" NAME=\"nameencode\" $DOMAINDISABLE CHECKED >\n");
  else
     print("<P><INPUT TYPE=\"CHECKBOX\" NAME=\"nameencode\" $DOMAINDISABLE >\n");
  print("$adminbuttom_1_prop_SamsReConfigForm_28");
  print("<BR>$adminbuttom_1_prop_SamsReConfigForm_29");
  print("<TR bgcolor=blanchedalmond><TD><INPUT TYPE=\"RADIO\" NAME=\"auth\" VALUE=\"ncsa\" $NCSACHECKED  onclick=DisableCheckBox(samsreconfigform)><B>NCSA</B><TD>\n");
  print("<TR bgcolor=blanchedalmond><TD><INPUT TYPE=\"RADIO\" NAME=\"auth\" VALUE=\"ip\" $IPCHECKED  onclick=DisableCheckBox(samsreconfigform)><B>IP</B><TD>\n");
  print("</TABLE>\n");

       print("<SCRIPT LANGUAGE=JAVASCRIPT>\n");
       print("function EnableDomainName(formname) \n");
       print("{\n");
       print("  var domain='domain'; \n");
       print("  var user='user'; \n");
       print("  var enabled=formname.ntlmdomain.checked; \n");
       print("  var bigdomain=formname.bigdomain.value; \n");
       print("  var bigusername=formname.bigusername.value; \n");
       print("  if(bigdomain=='Y') \n");
       print("    {\n");
       print("       domain='DOMAIN'; \n");
       print("    }\n");
       print("  if(bigdomain=='N') \n");
       print("    {\n");
       print("       domain='Domain'; \n");
       print("    }\n");
       print("  if(bigusername=='Y') \n");
       print("    {\n");
       print("       user='USER'; \n");
       print("    }\n");
       print("  if(bigusername=='N') \n");
       print("    {\n");
       print("       user='User'; \n");
       print("    }\n");
       print("  if(enabled==true) \n");
       print("    {\n");
       print("       var domainuser=domain+'+'+user; \n");
       print("       document.getElementById('DomainUser').innerHTML=domainuser;  \n");
       print("    }\n");
       print("  else \n");
       print("    {\n");
       print("       document.getElementById('DomainUser').innerHTML=user;  \n");
       print("    }\n");
       print("}\n");

       print("function DisableDomainName() \n");
       print("{\n");
       print("  document.getElementById('DomainUser').innerHTML=\"1234qwer\";  \n");
       print("  output1.innerText=\"123\"; \n");
       print("}\n");
       print("</SCRIPT>\n");

  print("<TABLE WIDTH=\"90%\" BORDER=0>\n");
  print("<TR><TD>$adminbuttom_1_prop_SamsReConfigForm_24\n");
  print("<TR><TD> $adminbuttom_1_prop_SamsReConfigForm_25\n");
  print("<TR><TD><LI> $adminbuttom_1_prop_SamsReConfigForm_26\n");
  print("<TR><TD><LI> $adminbuttom_1_prop_SamsReConfigForm_27\n");

           print("<SCRIPT LANGUAGE=JAVASCRIPT>");
           print("function EnableParser(formname)");
           print("{");
           print("  var parser_on=formname.parser_on.checked; \n");
  	       print("  if(parser_on==true) \n");
           print("    {\n");
           print("      formname.parser.disabled=false; ");
           print("      formname.parser_time.disabled=false; ");
           print("      DisableParserTime(formname); ");
		   print("    }\n");
  	       print("  else \n");
           print("    {\n");
           print("      formname.parser.disabled=true; ");
           print("      formname.parser_time.disabled=true; ");
           print("    }\n");
           print("}\n");
           print("</SCRIPT>");


  print("<P>\n");
  print("<P><CENTER><B>$adminbuttom_1_prop_SamsReConfigForm_30</B></CENTER>\n");
  print("<TABLE WIDTH=\"90%\" BORDER=0 >\n");

  $SLEEP=1;
  if($row['s_sleep']>0)
    $SLEEP=$row['s_sleep'];
  print("<TR bgcolor=blanchedalmond><TD><B>$adminbuttom_1_prop_SamsReConfigForm_31 </B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"sleep\" SIZE=5 VALUE=$SLEEP> $adminbuttom_1_prop_SamsReConfigForm_32\n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_38</B>\n");
  if($row['s_parser_on']=="Y")
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"parser_on\" CHECKED onchange=EnableParser(samsreconfigform)>\n");
  else
     {
            $DISABLED_PARSER="DISABLED";
	        print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"parser_on\" onchange=EnableParser(samsreconfigform)> \n");
    }

  print("<TR bgcolor=blanchedalmond><TD ALIGN=\"RIGHT\"><B> $adminbuttom_1_prop_SamsReConfigForm_40</B>\n");
  print("<TD><SELECT NAME=\"parser\" $DISABLED_PARSER  onchange=DisableParserTime(samsreconfigform)>\n");
  if($row['s_parser']=="analog")
    {
	   print("<OPTION VALUE=\"analog\" SELECTED > $adminbuttom_1_prop_SamsReConfigForm_33\n");
       $DISABLED_PARSER="DISABLED";
	}
  else
    {
       print("<OPTION VALUE=\"analog\" >  $adminbuttom_1_prop_SamsReConfigForm_33\n");
	}
  if($row['s_parser']=="diskret")
     print("<OPTION VALUE=\"diskret\" SELECTED >  $adminbuttom_1_prop_SamsReConfigForm_34\n");
  else
     print("<OPTION VALUE=\"diskret\" >  $adminbuttom_1_prop_SamsReConfigForm_34\n");
  print("</SELECT>\n");
  if($row['s_parser_time']>0)
     $time=$row['s_parser_time'];
  else
     $time=1;
  print("<TR bgcolor=blanchedalmond><TD ALIGN=\"RIGHT\"><B>$adminbuttom_1_prop_SamsReConfigForm_35 </B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"parser_time\" SIZE=5 VALUE=\"$time\" $DISABLED_PARSER> $adminbuttom_1_prop_SamsReConfigForm_36\n");
  print("<TR bgcolor=blanchedalmond><TD><B>$adminbuttom_1_prop_SamsReConfigForm_37 </B>\n");
  if($row['s_count_clean']=="Y")
     print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"count_clean\" CHECKED >\n");
  else
     print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"count_clean\" > \n");

  print("</TABLE >\n");

           print("<SCRIPT LANGUAGE=JAVASCRIPT> \n");
           print("function DisableParserTime(formname) \n");
           print("{ \n");
           print("  var parser_on=formname.parser.value; \n");
  	       print("  if(parser_on==\"diskret\") \n");
           print("    {\n");
           print("      formname.parser_time.disabled=false;  \n");
           print("    }\n");
  	       print("  else \n");
           print("    {\n");
           print("      formname.parser_time.disabled=true;  \n");
           print("    }\n");
           print("}\n");
           print("</SCRIPT> \n");






  print("<P><TABLE WIDTH=\"90%\" BORDER=0 >\n");
  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_3</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"wbinfopath\" SIZE=50 VALUE=\"$row[s_wbinfopath]\">\n");
  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_4</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"redirect_to\" SIZE=50 VALUE=\"$row[s_redirect_to]\">\n");
  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_5</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"denied_to\" SIZE=50 VALUE=\"$row[s_denied_to]\"> \n");
  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_6</B>\n");
  print("<TD><SELECT NAME=\"redirector\">\n");
  if($row['s_redirector']=="none")
            print("<OPTION VALUE=\"none\" SELECTED> $adminbuttom_1_prop_SamsReConfigForm_43");
  else
            print("<OPTION VALUE=\"none\" > $adminbuttom_1_prop_SamsReConfigForm_43");
  if($row['s_redirector']=="sams")
            print("<OPTION VALUE=\"sams\" SELECTED> $adminbuttom_1_prop_SamsReConfigForm_7");
  else
            print("<OPTION VALUE=\"sams\" > $adminbuttom_1_prop_SamsReConfigForm_7");
  
  if($row['s_redirector']=="rejik")
            print("<OPTION VALUE=\"rejik\" SELECTED> Rejik");
  else
            print("<OPTION VALUE=\"rejik\"> Rejik");
  
  if($row['s_redirector']=="squidguard")
            print("<OPTION VALUE=\"squidguard\" SELECTED> SquidGuard");
  else
            print("<OPTION VALUE=\"squidguard\"> SquidGuard");
  if($row['s_redirector']=="squid")
            print("<OPTION VALUE=\"squid\" SELECTED> $adminbuttom_1_prop_SamsReConfigForm_8");
  else
            print("<OPTION VALUE=\"squid\"> $adminbuttom_1_prop_SamsReConfigForm_8");
  print("</SELECT>\n");
  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_9</B>\n");
  if($row['s_delaypool']=="Y")
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"delaypool\" CHECKED> \n");
  else
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"delaypool\" > \n");

  print("<TR bgcolor=blanchedalmond>\n");
  print("<TD><B>$configbuttom_1_prop_SamsReConfigForm_53</B>\n");
  print("<TD><SELECT NAME=\"squidbase\">\n");
  $SELECTED="";
  if($row['s_squidbase']==0)
      $SELECTED="SELECTED";
  print("<OPTION VALUE=\"0\" $SELECTED> $configbuttom_1_prop_SamsReConfigForm_54");
  for($i=1;$i<=12;$i++)
    {
      $SELECTED="";
      if($row['s_squidbase']==$i)
        $SELECTED="SELECTED";
      print("<OPTION VALUE=\"$i\" $SELECTED> $i");
    } 
  print("</SELECT>\n");
  print("$configbuttom_1_prop_SamsReConfigForm_55\n");

  
  
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("</FORM>\n");
}



function configbuttom_1_prop()
{
	global $SAMSConf;
	$result = "";
	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);
	$SamsTools = new SamsTools();  

	//  if($SAMSConf->access==2)
	if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "C")==1)
	{
		$result .= "<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n";
		$result .= $SamsTools->GraphButton("main.php?show=exe&function=samsreconfigform&filename=configbuttom_1_prop.php",
		"basefrm","config_32.jpg","config_48.jpg","$adminbuttom_1_prop_adminbuttom_1_propadmintray_1");
	}
	
	return $result;
}

?>
