<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */


function RemoveCache()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1)
	exit;

  $DB=new SAMSDB();
  $DB2=new SAMSDB();

 if(isset($_GET["cache"])) $cache=$_GET["cache"];
  
  $num_rows=$DB->samsdb_query_value("SELECT * FROM proxy ");
  while($row=$DB->samsdb_fetch_array())
     {
        $id=$row['s_proxy_id'];
	if($cache[$id]=="on")
	  {
            $DB2->samsdb_query("DELETE FROM $SAMSConf->SAMSDB.proxy WHERE s_proxy_id=\"$id\" ");
          }
    }
  print("<SCRIPT>\n");
  print("  parent.basefrm.location.href=\"main.php?show=exe&function=cacheform&filename=squidbuttom_4_addcache.php\"; \n");
  print("  parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");
}

function AddCache()
{
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();

$delaypool=0;
$ntlmdomain=0;
$bigdomain=0;
$bigusername=0;
$parser_on=0;
$parser_time=1;
$count_clean=0;
$sleep=1;
$nameencode=0;
$checkdns=0;
$loglevel=0;
$squidbase=0;
$parser=0;
$adminaddr="root@localhost";

  if(isset($_GET["description"])) $description=$_GET["description"];
  if(isset($_GET["delaypool"])) $delaypool=SetCheckBoxValue($_GET["delaypool"]);
  if(isset($_GET["redirect_to"])) $redirect_to=$_GET["redirect_to"];
  if(isset($_GET["denied_to"])) $denied_to=$_GET["denied_to"];
  if(isset($_GET["redirector"])) $redirector=$_GET["redirector"];
  if(isset($_GET["auth"])) $auth=$_GET["auth"];
  if(isset($_GET["wbinfopath"])) $wbinfopath=$_GET["wbinfopath"];
  if(isset($_GET["ntlmdomain"])) $ntlmdomain=SetCheckBoxValue($_GET["ntlmdomain"]);
  if(isset($_GET["bigdomain"])) $bigdomain=$_GET["bigdomain"];
  if(isset($_GET["bigusername"])) $bigusername=$_GET["bigusername"];
  if(isset($_GET["parser_on"])) $parser_on=SetCheckBoxValue($_GET["parser_on"]);
  if(isset($_GET["parser"])) $parser=$_GET["parser"];
  if(isset($_GET["parser_time"])) $parser_time=$_GET["parser_time"];
  if(isset($_GET["count_clean"])) $count_clean=SetCheckBoxValue($_GET["count_clean"]);
  if(isset($_GET["sleep"])) $sleep=$_GET["sleep"];
  if(isset($_GET["nameencode"])) $nameencode=SetCheckBoxValue($_GET["nameencode"]);
  if(isset($_GET["traffic"])) $traffic=$_GET["traffic"];
  if(isset($_GET["checkdns"])) $checkdns=SetCheckBoxValue($_GET["checkdns"]);
  if(isset($_GET["loglevel"])) $loglevel=$_GET["loglevel"];
  if(isset($_GET["defaultdomain"])) $defaultdomain=$_GET["defaultdomain"];
  if(isset($_GET["squidbase"])) $squidbase=SetCheckBoxValue($_GET["squidbase"]);
  if(isset($_GET["udscript"])) $udscript=$_GET["udscript"];
  if(isset($_GET["adminaddr"])) $adminaddr=$_GET["adminaddr"];
  if(isset($_GET["kbsize"])) $kbsize=$_GET["kbsize"];
  if(isset($_GET["mbsize"])) $mbsize=$_GET["mbsize"];
  if(isset($_GET["separator"])) $separator=$_GET["separator"];
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1)
	exit;

  if($separator==0)
    $separator="0+";
  if($separator==1)
    $separator="0\\\\\\\\";
  if($separator==2)
    $separator="0@";

	$query = "INSERT INTO proxy ( s_description) VALUES ( '$description' ) ";

	$DB->samsdb_query($query);
	$query = "UPDATE proxy SET  s_endvalue='0', s_redirect_to='$redirect_to', s_denied_to='$denied_to', s_redirector='$redirector', s_delaypool='$delaypool', s_auth='$auth', s_wbinfopath='$wbinfopath', s_separator='$separator', s_usedomain='$ntlmdomain', s_bigd='$bigdomain', s_bigu='$bigusername', s_sleep='$sleep', s_parser='$parser', s_parser_time='$parser_time', s_count_clean='$count_clean', s_nameencode='$nameencode', s_realsize='$traffic', s_checkdns='$checkdns', s_debuglevel='$loglevel', s_defaultdomain='workgroup', s_squidbase='$squidbase', s_udscript='$udscript', s_adminaddr='$adminaddr', s_kbsize='$kbsize', s_mbsize='$mbsize' WHERE s_description='$description' ";
//echo "$query<BR>";
	$DB->samsdb_query($query);

     //    UpdateLog("$SAMSConf->adminname","Added SQUID-cache $description ","01");
  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\";\n");
  print("  parent.basefrm.location.href = \"main.php?show=exe&function=cacheform&filename=squidbuttom_4_addcache.php\"; \n");
  print("</SCRIPT> \n");
}

 
 
function CacheForm()
{
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
      PageTop("proxyes_48.jpg","$CacheForm_squidbuttom_4_addcache_6");

           print("<SCRIPT LANGUAGE=JAVASCRIPT>\n");
           print("function EnableCheckBox(formname)\n");
           print("{\n");
           print("    formname.ntlmdomain.disabled=false; \n");
           print("    formname.bigdomain.disabled=false; \n");
           print("    formname.bigusername.disabled=false; \n");
           print("    formname.nameencode.disabled=false; \n");
           print("    formname.testpdc.disabled=false; \n");
           print("    formname.separator.disabled=false; \n");
//		   print("    document.getElementById('c1').innerHTML='YES'; ");
		   print("}\n");
           print("function DisableCheckBox(formname)\n");
           print("{\n");
           print("    formname.ntlmdomain.disabled=true; \n");
           print("    formname.bigdomain.disabled=true; \n");
           print("    formname.bigusername.disabled=true; \n");
           print("    formname.nameencode.disabled=true; \n");
           print("    formname.testpdc.disabled=true; \n");
           print("    formname.separator.disabled=true; \n");
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


     print("<P>\n");
//      print("<H2>$CacheForm_squidbuttom_4_addcache_6</H2>\n");
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function TestName(formname)\n");
       print("{\n");
       print("  var shablonname=formname.description.value; \n");
       print("  if(shablonname.length==0) \n");
       print("    {\n");
       print("       alert(\"$CacheForm_squidbuttom_4_addcache_9\");\n");
       print("       return false");
       print("    }\n");
       print("  return true");
       print("}\n");
       print("</SCRIPT> \n");
 
       print("<FORM NAME=\"ADDCACHE\" ACTION=\"main.php\" onsubmit=\"return TestName(ADDCACHE)\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addcache\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"squidbuttom_4_addcache.php\">\n");
	print("<INPUT TYPE=\"SUBMIT\" value=\"$CacheForm_squidbuttom_4_addcache_8\" >\n");

	print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");

	print("<TR><TD><B>$CacheForm_squidbuttom_4_addcache_7:</B>\n");
	print("<TD><INPUT TYPE=\"TEXT\" NAME=\"description\" SIZE=30> \n");

	/*      ������� ������: REAL/FULL     */
	print("<TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_46: </B>\n");
	print("<TD><SELECT NAME=\"traffic\">\n");
	print("<OPTION VALUE=\"full\"> $adminbuttom_1_prop_SamsReConfigForm_48");
	print("<OPTION VALUE=\"real\"> $adminbuttom_1_prop_SamsReConfigForm_47");
	print("</SELECT>\n");
	/*      ��������� DNS     */
	print("<TR bgcolor=blanchedalmond>\n");
	print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_49</B>\n");
	print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"checkdns\" > \n");
	/*      ������� �����     */
	print("<TR bgcolor=blanchedalmond>\n");
	print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_51</B>\n");
	print("<TD><SELECT NAME=\"loglevel\">\n");
	for($i=0;$i<10;$i++)
	  {
		if($row['loglevel']==$i)
			print("<OPTION VALUE=\"$i\" SELECTED> $i");
		else    
			print("<OPTION VALUE=\"$i\"> $i");
	  }
	print("</SELECT>\n");
	/*     ����� ��-���������     */
	print("<TR bgcolor=blanchedalmond>\n");
	print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_52</B>\n");
	print("<TD><INPUT TYPE=\"TEXT\" NAME=\"defaultdomain\" value=\"$row[defaultdomain]\">\n");
            
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
	/*     ������, ���������� ��� ���������� ������������     */
	print("<TR>\n");
	print("<TD><B>$configbuttom_1_prop_SamsReConfigForm_56</B>\n");
	print("<TD><SELECT NAME=\"udscript\" ID=\"udscript\" >\n");
	$SELECTED="";
	print("<OPTION VALUE=\"none\"> NONE\n");
	for($i=0;$i<$scount;$i++)
	{
		print("<OPTION VALUE=\"$script[$i]\"> $script[$i]\n");
	}
	print("</SELECT>\n");
	/*     ����� ��������������     */
	print("<TR bgcolor=blanchedalmond>\n");
	print("<TD><B>$configbuttom_1_prop_SamsReConfigForm_57</B>\n");
	print("<TD><INPUT TYPE=\"TEXT\" NAME=\"adminaddr\" value=\"root@localhost\">\n");
 
	print("<TR><TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_7 (byte)</B>\n");
	print("<TD><INPUT TYPE=\"TEXT\" NAME=\"kbsize\" value=\"1024\">\n");
	print("<TR><TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_8 (byte)</B>\n");
	print("<TD><INPUT TYPE=\"TEXT\" NAME=\"mbsize\" value=\"1048576\">\n");
 
	print("</TABLE>\n");


     
	//********* ����������� ������������  **************************
  $QUERY="SELECT s_auth FROM auth_param WHERE s_param='enabled' AND s_value='1'";
  $num_rows=$DB->samsdb_query_value($QUERY);
  $ntlmauth=0;
  $ncsaauth=0;
  $ldapauth=0;
  $adldauth=0;
  while($row=$DB->samsdb_fetch_array())
  {
	if($row[s_auth]=="ntlm")
	{
		$ntlmauth=1;
	}
	if($row[s_auth]=="adld")
	{
		$adldauth=1;
	}
	if($row[s_auth]=="ldap")
	{
		$ldapauth=1;
	}
	if($row[s_auth]=="ncsa")
	{
		$ncsaauth=1;
	}

  }

  print("<P><B>$adminbuttom_1_prop_SamsReConfigForm_17</B>\n");
  print("<TABLE WIDTH=\"90%\" BORDER=0 >\n");

  print("<TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_2</B><TD>\n");
  if($ntlmauth==1)
  {
	print("<TR bgcolor=blanchedalmond><TD VALIGN=TOP >");
	print("<INPUT TYPE=\"RADIO\" NAME=\"auth\" VALUE=\"ntlm\" onclick=EnableCheckBox(ADDCACHE) onchange=EnableDomainName(ADDCACHE)>\n");
	$sdomain="";
	$suser="";
	if($row['bigu']=="Y")            $suser="USER";
	if($row['bigu']=="S")            $suser="user";
	if($row['bigu']=="N")            $suser="User";
  
	print("  <B>NTLM (</B>\n<B ID=\"DomainUser\"> $sdomain $suser");
	print("  </B>\n<B>)</B>\n");
  }
  if($adldauth==1)
  {
	print("<BR><INPUT TYPE=\"RADIO\" NAME=\"auth\" VALUE=\"adld\" onclick=EnableCheckBox(ADDCACHE) onchange=EnableDomainName(ADDCACHE)>\n");
	print("  <B>Active Directory</B><BR>\n");
  }
  if($ntlmauth==1||$adldauth==1)
  {

	print("<TD name=c1  ID=\"c1\">");
	print("<INPUT TYPE=\"CHECKBOX\" NAME=\"ntlmdomain\" DISABLED onchange=EnableDomainName(ADDCACHE)>$adminbuttom_1_prop_SamsReConfigForm_19\n");
  
	print("<BR><LI>$adminbuttom_1_prop_SamsReConfigForm_20 \n");
	print("<SELECT NAME=\"bigdomain\" DISABLED onchange=EnableDomainName(ADDCACHE)>\n");
	print("<OPTION VALUE=\"0\">$adminbuttom_1_prop_SamsReConfigForm_20a</OPTION>\n");
	print("<OPTION VALUE=\"1\" >$adminbuttom_1_prop_SamsReConfigForm_20b</OPTION>\n");
	print("<OPTION VALUE=\"2\">$adminbuttom_1_prop_SamsReConfigForm_20c</OPTION>\n");
	print("</SELECT > $adminbuttom_1_prop_SamsReConfigForm_20d\n");

	print("<BR><LI>$adminbuttom_1_prop_SamsReConfigForm_22 \n");
	print("<SELECT NAME=\"bigusername\" DISABLED onchange=EnableDomainName(ADDCACHE)>\n");
	print("<OPTION VALUE=\"0\">$adminbuttom_1_prop_SamsReConfigForm_20a</OPTION>\n");
	print("<OPTION VALUE=\"1\" >$adminbuttom_1_prop_SamsReConfigForm_20b</OPTION>\n");
	print("<OPTION VALUE=\"2\">$adminbuttom_1_prop_SamsReConfigForm_20c</OPTION>\n");
	print("</SELECT >$adminbuttom_1_prop_SamsReConfigForm_20d\n");

	print("<P>$adminbuttom_1_prop_SamsReConfigForm_50: \n");
	print("<SELECT NAME=\"separator\"  DISABLED>\n");
        print("<OPTION VALUE=0>+</OPTION>\n");
        print("<OPTION VALUE=1 SELECTED >\\</OPTION>\n");
        print("<OPTION VALUE=2>@</OPTION>\n");
	print("</SELECT>\n");



	print("<P >\n");
	print("<BR><INPUT TYPE=\"BUTTON\" NAME=\"testpdc\" VALUE=\"$adminbuttom_1_prop_SamsReConfigForm_39\" onclick=TestPDC(ADDCACHE) DISABLED>\n");

	print("<P><INPUT TYPE=\"CHECKBOX\" NAME=\"nameencode\" DISABLED >\n");
	print("$adminbuttom_1_prop_SamsReConfigForm_28");
	print("<BR>$adminbuttom_1_prop_SamsReConfigForm_29");
  }
  if($ldapauth==1)
  {
	print("<TR bgcolor=blanchedalmond><TD><INPUT TYPE=\"RADIO\" NAME=\"auth\" VALUE=\"ldap\"  onclick=DisableCheckBox(ADDCACHE)><B>LDAP</B><TD>\n");
  }
  if($ncsaauth==1)
  {
	print("<TR bgcolor=blanchedalmond><TD><INPUT TYPE=\"RADIO\" NAME=\"auth\" VALUE=\"ncsa\"  onclick=DisableCheckBox(ADDCACHE)><B>NCSA</B><TD>\n");
  }
	print("<TR bgcolor=blanchedalmond><TD><INPUT TYPE=\"RADIO\" NAME=\"auth\" VALUE=\"ip\"  CHECKED  onclick=DisableCheckBox(ADDCACHE)><B>IP</B><TD>\n");
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


//***********************************
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
	print("<TR bgcolor=blanchedalmond><TD><B>$adminbuttom_1_prop_SamsReConfigForm_31 </B>\n");
	print("<TD><INPUT TYPE=\"TEXT\" NAME=\"sleep\" SIZE=5 VALUE=$SLEEP> $adminbuttom_1_prop_SamsReConfigForm_32\n");

	print("<TR bgcolor=blanchedalmond>\n");
	print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_38</B>\n");
	print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"parser_on\" CHECKED onchange=EnableParser(ADDCACHE)>\n");

	print("<TR bgcolor=blanchedalmond><TD ALIGN=\"RIGHT\"><B> $adminbuttom_1_prop_SamsReConfigForm_40</B>\n");
	print("<TD><SELECT NAME=\"parser\" $DISABLED_PARSER  onchange=DisableParserTime(ADDCACHE)>\n");
	print("<OPTION VALUE=\"1\" SELECTED >  $adminbuttom_1_prop_SamsReConfigForm_34\n");
	print("<OPTION VALUE=\"2\" >  $adminbuttom_1_prop_SamsReConfigForm_33\n");
	print("</SELECT>\n");

	$time=1;

	print("<TR bgcolor=blanchedalmond><TD ALIGN=\"RIGHT\"><B>$adminbuttom_1_prop_SamsReConfigForm_35 </B>\n");
	print("<TD><INPUT TYPE=\"TEXT\" NAME=\"parser_time\" SIZE=5 VALUE=\"$time\" > $adminbuttom_1_prop_SamsReConfigForm_36\n");

	print("<TR bgcolor=blanchedalmond><TD><B>$adminbuttom_1_prop_SamsReConfigForm_37 </B>\n");
	print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"count_clean\" CHECKED >\n");

	print("</TABLE >\n");

           print("<SCRIPT LANGUAGE=JAVASCRIPT> \n");
           print("function DisableParserTime(formname) \n");
           print("{ \n");
           print("  var parser_on=formname.parser.value; \n");
  	       print("  if(parser_on==\"0\") \n");
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
	print("<TD><INPUT TYPE=\"TEXT\" NAME=\"wbinfopath\" SIZE=50 VALUE=\"/usr/bin\">\n");

	print("<TR bgcolor=blanchedalmond>\n");
	print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_4</B>\n");
	print("<TD><INPUT TYPE=\"TEXT\" NAME=\"redirect_to\" SIZE=50 VALUE=\"http://ip.addr/sams/icon/classic/blank.gif\">\n");

	print("<TR bgcolor=blanchedalmond>\n");
	print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_5</B>\n");
	print("<TD><INPUT TYPE=\"TEXT\" NAME=\"denied_to\" SIZE=50 VALUE=\"http://ip.addr/sams/messages\"> \n");

	print("<TR bgcolor=blanchedalmond>\n");
	print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_6</B>\n");
	print("<TD><SELECT NAME=\"redirector\">\n");
	print("<OPTION VALUE=\"none\" > $adminbuttom_1_prop_SamsReConfigForm_43");
	print("<OPTION VALUE=\"sams\" > $adminbuttom_1_prop_SamsReConfigForm_7");
	print("<OPTION VALUE=\"rejik\"> Rejik");
	print("<OPTION VALUE=\"squidguard\"> SquidGuard");
	print("<OPTION VALUE=\"squid\"> $adminbuttom_1_prop_SamsReConfigForm_8");

	print("<TR bgcolor=blanchedalmond>\n");
	print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_9</B>\n");
	print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"delaypool\" > \n");

	print("<TR bgcolor=blanchedalmond>\n");
	print("<TD><B>$configbuttom_1_prop_SamsReConfigForm_53</B>\n");
	print("<TD><SELECT NAME=\"squidbase\">\n");
	print("<OPTION VALUE=\"0\" $SELECTED> $configbuttom_1_prop_SamsReConfigForm_54");
	for($i=1;$i<=12;$i++)
	{
		$SELECTED="";
		print("<OPTION VALUE=\"$i\" > $i");
	} 
	print("</SELECT>\n");
	print("$configbuttom_1_prop_SamsReConfigForm_55\n");

  
  
  print("</TABLE>\n");







	print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$CacheForm_squidbuttom_4_addcache_8\" >\n");
      print("</FORM>\n");

    
    }
}

 
function squidbuttom_4_addcache()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);


  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
       GraphButton("main.php?show=exe&function=cacheform&filename=squidbuttom_4_addcache.php","basefrm","proxyes_32.jpg","proxyes_48.jpg","$squidbuttom_4_addcache_squidbuttom_4_addcache_1");
	}

}




?>
