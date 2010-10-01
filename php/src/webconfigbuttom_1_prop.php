<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
 

function WebInterfaceReConfig()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  $s_urlaccess=0;
  $s_useraccess=0;
  $s_showutree=0;
  $s_showgraph=0;

  if(isset($_GET["lang"])) $lang=$_GET["lang"];

  if(isset($_GET["urlaccess"])) $s_urlaccess=1;
  if(isset($_GET["useraccess"])) $s_useraccess=1;
  if(isset($_GET["showutree"])) $s_showutree=1;
  if(isset($_GET["showgraph"])) $s_showgraph=1;

  if(isset($_GET["iconset"])) $iconset=$_GET["iconset"];
  if(isset($_GET["showname"])) $showname=$_GET["showname"];
  if(isset($_GET["kbsize"])) $kbsize=$_GET["kbsize"];
  if(isset($_GET["mbsize"])) $mbsize=$_GET["mbsize"];
  if(isset($_GET["createpdf"])) $createpdf=$_GET["createpdf"];

  if($USERConf->ToWebInterfaceAccess("C")!=1)
	{       exit;     }

  $DB->samsdb_query("UPDATE websettings SET s_createpdf='$createpdf', s_showname='$showname', s_showutree='$s_showutree', s_iconset='$iconset', s_lang='$lang', s_urlaccess='$s_urlaccess', s_useraccess='$s_useraccess', s_showgraph='$s_showgraph' ");
  $SAMSConf->LoadConfig();
  PageTop("config_48.jpg","$adminbuttom_1_prop_SamsReConfig_1");
  print("<SCRIPT>\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");

}


function WebInterfaceReConfigForm()
{
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1)
  {       exit;     }

  $aaa = strtolower($SAMSConf->adminname);  
  
  PageTop("config_48.jpg","$webconfigbuttom_1_prop_webconfigbuttom_1_propadmintray_1");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/webinterface.html\">$documentation</A>");
  print("<P>\n");
  print("<P>\n");

  $num_rows=$DB->samsdb_query_value("SELECT * FROM websettings");
  $row=$DB->samsdb_fetch_array();
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
 		 if($row['s_lang']=="$filename2")
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
  if($row['s_useraccess']==1)
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"useraccess\" CHECKED> \n");
  else
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"useraccess\"> \n");
  print("<TR>\n");
  print("<TD><B>$adminbuttom_1_prop_SamsReConfigForm_16</B>\n");
  if($row['s_urlaccess']==1)
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
 		 if($row['s_iconset']=="$file")
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
  if($row['s_showutree']==1)
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"showutree\" CHECKED> \n");
  else
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"showutree\"> \n");

  print("<TR><TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_2</B>\n");
  print("<TD><SELECT NAME=\"showname\">\n");
       if($row['s_showname']=="nick")
          print("<OPTION VALUE=\"nick\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_3");
       else
          print("<OPTION VALUE=\"nick\">$webconfigbuttom_1_prop_WebInterfaceReConfigForm_3");
       if($row['s_showname']=="nickd")
          print("<OPTION VALUE=\"nickd\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_4");
       else
          print("<OPTION VALUE=\"nickd\">$webconfigbuttom_1_prop_WebInterfaceReConfigForm_4");
       if($row['s_showname']=="fam")
          print("<OPTION VALUE=\"fam\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_5");
       else
          print("<OPTION VALUE=\"fam\">$webconfigbuttom_1_prop_WebInterfaceReConfigForm_5");
       if($row['s_showname']=="famn")
          print("<OPTION VALUE=\"famn\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_6");
       else
          print("<OPTION VALUE=\"famn\">$webconfigbuttom_1_prop_WebInterfaceReConfigForm_6");
    
  print("</SELECT>\n");
/*  
  print("<TR><TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_7 (byte)</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"kbsize\" value=\"$row[s_kbsize]\">\n");
  print("<TR><TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_8 (byte)</B>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"mbsize\" value=\"$row[s_mbsize]\">\n");
*/
  print("<TR>\n");
  print("<TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_9</B>\n");
  if($row['s_showgraph']==1)
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"showgraph\" CHECKED> \n");
  else
            print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"showgraph\"> \n");
  
  
  print("<TR><TD><B>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_10:</B>\n");
  print("<TD><SELECT NAME=\"createpdf\">\n");
       if($SAMSConf->PDFLIB=="NONE")
          print("<OPTION VALUE=\"NONE\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_11");
       else
          print("<OPTION VALUE=\"NONE\">$webconfigbuttom_1_prop_WebInterfaceReConfigForm_11");
       if($SAMSConf->PDFLIB=="fpdf")
          print("<OPTION VALUE=\"fpdf\" SELECTED>$webconfigbuttom_1_prop_WebInterfaceReConfigForm_12");
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
  global $USERConf;
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
       GraphButton("main.php?show=exe&function=webinterfacereconfigform&filename=webconfigbuttom_1_prop.php", "basefrm", "config_32.jpg", "config_48.jpg", "$webconfigbuttom_1_prop_webconfigbuttom_1_propadmintray_1");
    }
}
?>
