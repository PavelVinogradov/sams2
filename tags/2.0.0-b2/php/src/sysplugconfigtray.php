<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Andrey Ovcharov mclight77@permlug.org
 * (see the file 'main.php' for license details)
 */

function SysPlugConfig()
{
  global $SAMSConf;
  $DB=new SAMSDB();
  $DB2=new SAMSDB();

  $num_rows=$DB->samsdb_query_value("SELECT * FROM sysinfo");
  while($row=$DB->samsdb_fetch_array())
    {
      if(isset($_GET[$row['s_row_id']])) $val=$_GET[$row['s_row_id']];
      else $val="off";
      if($val=="on") $val=1; else $val=0;
      $num_rows=$DB2->samsdb_query("UPDATE sysinfo SET s_status='$val' WHERE s_row_id='$row[s_row_id]'");
    }

  $DB->free_samsdb_query();

  print("<SCRIPT>\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=sysplugconfigform&filename=sysplugconfigtray.php\";\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");
}

function SysPlugConfigForm()
{
  global $SAMSConf;
  $DB=new SAMSDB();
  $num_rows=$DB->samsdb_query_value("SELECT * FROM sysinfo");

  PageTop("sysplug_64.png","System Plugins");

  print("<FORM NAME=\"sysplugconfigform\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"sysplugconfig\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"sysplugconfigtray.php\">\n");

  print("<TABLE CLASS=samstable>\n");
  print("<TH>Name</TH>\n");
  print("<TH>Version</TH>\n");
  print("<TH>Author</TH>\n");
  print("<TH>Enabled</TH>\n");
  while($row=$DB->samsdb_fetch_array())
    {
/*      
      print("<TR>\n");
      if(isset($_GET["$row[s_row_id]"])) $val=$_GET["$row[s_row_id]"];

      print("<TD>s_row_id=$row[s_row_id]</TD>\n");
      print("<TD>val=$val</TD>\n");
      if($val=="on") $val=1; else $val=0;
      print("<TD>val=$val</TD>\n");
      print("<TD>s_status=$row[s_status]</TD>\n");
      print("</TR>\n");
*/

      print("<TR>\n");
      print("  <TD>$row[s_name]</TD>\n");
      print("  <TD>$row[s_version]</TD>\n");
      print("  <TD>$row[s_author]</TD>\n");

      $CHECKED="";
      if ($row['s_status'] == 1)
        $CHECKED="CHECKED";
      print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"$row[s_row_id]\" $CHECKED></TD>\n");

      print("</TR>\n");
    }
  $DB->free_samsdb_query();
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Configure\">\n");
  print("</FORM>\n");
}

function SysPlugInfo()
{
}

function SysPlugConfigTray()
{
  global $SAMSConf;
  global $USERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  print("<SCRIPT>\n");
  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
       print("parent.basefrm.location.href=\"main.php?show=exe&function=sysplugconfigform&filename=sysplugconfigtray.php\";\n");
    }
  else
    {
       print("parent.basefrm.location.href=\"main.php?show=exe&function=syspluginfo&filename=sysplugconfigtray.php\";\n");
    }
  print("</SCRIPT> \n");

  //print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
  //print("<TR>\n");
  //print("<TD VALIGN=\"TOP\" WIDTH=\"30%\"\">");
  
  //print("<B>$webconfigbuttom_1_prop_webconfigbuttom_1_propadmintray_1</B>\n");

  //ExecuteFunctions("./src", "sysplugconfigbuttom","1");

  //print("<TD>\n");
  //print("</TABLE>\n");
}

?>
