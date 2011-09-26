<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
 

function WebInterfaceReConfig()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  $showgraph="";
  if(isset($_GET["lang"])) $lang=$_GET["lang"];
  if(isset($_GET["urlaccess"])) $urlaccess=$_GET["urlaccess"];
  if(isset($_GET["useraccess"])) $useraccess=$_GET["useraccess"];
  if(isset($_GET["iconset"])) $iconset=$_GET["iconset"];
  if(isset($_GET["showutree"])) $showutree=$_GET["showutree"];
  if(isset($_GET["showname"])) $showname=$_GET["showname"];
  if(isset($_GET["kbsize"])) $kbsize=$_GET["kbsize"];
  if(isset($_GET["mbsize"])) $mbsize=$_GET["mbsize"];
  if(isset($_GET["showgraph"])) $showgraph=$_GET["showgraph"];
  if(isset($_GET["createpdf"])) $createpdf=$_GET["createpdf"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  if($urlaccess=="on")
    $urlaccess="Y";
  if($useraccess=="on")
    $useraccess="Y";
  if($showutree=="on")
    $showutree="Y";
  if($showgraph=="on")
    $showgraph="Y";
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("UPDATE globalsettings SET createpdf=\"$createpdf\",showname=\"$showname\",showutree=\"$showutree\",iconset=\"$iconset\",lang=\"$lang\", urlaccess=\"$urlaccess\",useraccess=\"$useraccess\",kbsize=\"$kbsize\",mbsize=\"$mbsize\",showgraph=\"$showgraph\" ");
  $SAMSConf->LoadConfig();
}


function WebInterfaceReConfigForm()
{
  global $SAMSConf;
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   //$SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }

  $aaa = strtolower($SAMSConf->adminname);  
  
  PageTop("config_48.jpg","$webconfigbuttom_1_prop_webconfigbuttom_1_propadmintray_1");
  print("<P>\n");

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM globalsettings");
  $row=mysql_fetch_array($result);
  print("<FORM NAME=\"samsreconfigform\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"webinterfacereconfig\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"webconfigbuttom_1_prop.php\">\n");

  print("<P><B>$adminbuttom_1_prop_SamsReConfigForm_41</B>\n");
  
  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");
  
  print("<TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_13</B>\n");
  print("<TD><SELECT NAME=\"lang\">\n");

    if ($handle2 = opendir("./lang"))
        {
	  while (false !== ($file = readdir($handle2)))
            {
 	      if(strstr($file, "lang.")!=FALSE)
                {
  		   $filename2=str_replace("lang.","",$file);
		   $language=ReturnLanguage("lang/$file");
                  // echo "$file $language<BR>";
 		 if($row['lang']=="$filename2")
     			print("<OPTION VALUE=\"$filename2\" SELECTED> $language");
  		else
     			print("<OPTION VALUE=\"$filename2\"> $language");
                }
            }
        }
   print("</SELECT>\n");


  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_14</B><TD>\n");
  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_15</B>\n");
  if($row['useraccess']=="Y")
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"useraccess\" CHECKED> \n");
  else
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"useraccess\"> \n");
  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_16</B>\n");
  if($row['urlaccess']=="Y")
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"urlaccess\" CHECKED> \n");
  else
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"urlaccess\"> \n");

##############################
   print("<TR><TD><B>$adminbuttom_1_prop_SamsReConfigForm_42</B>\n");
  print("<TD><SELECT NAME=\"iconset\">\n");


    if ($handle2 = opendir("./icon"))
        {
	  while (false !== ($file = readdir($handle2)))
            {
 	      if(strlen($file)>4)
                {
 		 if($row['iconset']=="$file")
     			print("<OPTION VALUE=\"$file\" SELECTED> $file");
  		else
     			print("<OPTION VALUE=\"$file\"> $file");
               }
            }
        }
  print("</SELECT>\n");

##############################
  print("<TR>\n");
  print("<TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_1</B>\n");
  if($row['showutree']=="Y")
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"showutree\" CHECKED> \n");
  else
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"showutree\"> \n");

  print("<TR><TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_2</B>\n");
  print("<TD><SELECT NAME=\"showname\">\n");
       if($row['showname']=="nick")
          print("<OPTION VALUE=\"nick\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_3");
       else
          print("<OPTION VALUE=\"nick\">$webconfigbuttom_1_prop_WebInterfaceReConfigForm_3");
       if($row['showname']=="nickd")
          print("<OPTION VALUE=\"nickd\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_4");
       else
          print("<OPTION VALUE=\"nickd\">$webconfigbuttom_1_prop_WebInterfaceReConfigForm_4");
       if($row['showname']=="fam")
          print("<OPTION VALUE=\"fam\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_5");
       else
          print("<OPTION VALUE=\"fam\">$webconfigbuttom_1_prop_WebInterfaceReConfigForm_5");
       if($row['showname']=="famn")
          print("<OPTION VALUE=\"famn\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_6");
       else
          print("<OPTION VALUE=\"famn\">$webconfigbuttom_1_prop_WebInterfaceReConfigForm_6");
    
  print("</SELECT>\n");
  
  print("<TR><TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_7 (byte)</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"kbsize\" value=\"$row[kbsize]\">\n");
  print("<TR><TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_8 (byte)</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"mbsize\" value=\"$row[mbsize]\">\n");
  
  print("<TR>\n");
  print("<TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_9</B>\n");
  if($row['showgraph']=="Y")
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"showgraph\" CHECKED> \n");
  else
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"showgraph\"> \n");
  
  
  print("<TR><TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_10:</B>\n");
  print("<TD><SELECT NAME=\"createpdf\">\n");
       if($SAMSConf->PDFLIB=="none")
          print("<OPTION VALUE=\"none\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_11");
       else
          print("<OPTION VALUE=\"none\">$webconfigbuttom_1_prop_WebInterfaceReConfigForm_11");
       if($SAMSConf->PDFLIB=="fpdf")
          print("<OPTION VALUE=\"fpdf\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_12 ");
       else
          print("<OPTION VALUE=\"fpdf\">$webconfigbuttom_1_prop_WebInterfaceReConfigForm_12");
       if($SAMSConf->PDFLIB=="pdflib")
          print("<OPTION VALUE=\"pdflib\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_13");
       else
          print("<OPTION VALUE=\"pdflib\">$webconfigbuttom_1_prop_WebInterfaceReConfigForm_13");
    
  print("</SELECT>\n");
  
  
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_1_prop_SamsReConfigForm_12\">\n");
  print("</FORM>\n");

}

function webconfigbuttom_1_prop()
{
	global $SAMSConf;
	$result = "";
	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);
	$SamsTools = new SamsTools();

	$SAMSConf->access=UserAccess();
	if($SAMSConf->access==2)
	{
		$result .= "<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n";
		$result .= $SamsTools->GraphButton("main.php?module=WebConfig&function=webinterfacereconfigform",
		"basefrm","config_32.jpg","config_48.jpg","$webconfigbuttom_1_prop_webconfigbuttom_1_propadmintray_1");
	}
	
	return $result;
}

?>