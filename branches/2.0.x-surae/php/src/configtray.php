<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

class UpTime
{
  var $days="";
  var $hours="";
  var $minits="";
}

function GetSamsHostName()
{
  if(!($value=getenv('SERVER_NAME')))
     {  $value="N.A."; }
  return($value);
}

function GetIPAddr()
{
  if(!($value=getenv('SERVER_ADDR')))
     {  $value="N.A."; }
  return($value);
}

function MemoryUsage()
{
  global $SAMSConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $phpos=PHP_OS;
  $value=ExecuteShellScript("freemem",$phpos);
  $swapvalue=ExecuteShellScript("freeswap",$phpos);

  $a=explode(" ",$value);
  for($i=1;$i<4;$i++)
     {
           $mem[$i-1]=$a[$i];
     }
  $a=explode(" ",$swapvalue);
  for($i=1;$i<4;$i++)
     {
           $swap[$i-1]=$a[$i];
     }

  print("<P><TABLE CLASS=samstable>");
  print("<TR >");
  print("<TH>");
  print("<TH><B>$configtray_1_MemTotal</B>");
  print("<TH><B>$configtray_1_MemUsed</B>");
  print("<TH><B>$configtray_1_MemFree</B>\n");
  print("<TR >");
  print("<TD>$configtray_1_Mem");
  print("<TD>$mem[0]");
  print("<TD>$mem[1]");
  print("<TD>$mem[2]\n");
  print("<TR >");
  print("<TD>$configtray_1_Swap");
  print("<TD>$swap[0]");
  print("<TD>$swap[1]");
  print("<TD>$swap[2]\n");
  print("</TABLE>");
}

function FileSystemUsage()
{
  global $SAMSConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

    $fstest=ExecuteShellScript("fsusage","");
    $a=explode(" ",$fstest);
    $acount=count($a)/6;

      print("<P><TABLE CLASS=samstable>");
      print("<TR>");
      print("<TH><B>$configtray_2_FS</B>");
      print("<TH><B>$configtray_2_Size</B>");
      print("<TH><B>$configtray_2_Used</B>");
      print("<TH><B>$configtray_2_Avail</B>");
      print("<TH><B>$configtray_2_Percent</B>");
      print("<TH><B>$configtray_2_Mnt</B>");

      for($i=0;$i<$acount;$i++)
        {
	    print("<TR>");
	    for($j=0;$j<6;$j++)
	      {
	        $fs=$a[$i*6+$j];
	        print("<TD>$fs");
	      }
         }

      print("</TABLE>");
}



function SysInfo()
{
  global $SAMSConf;
  $DB=new SAMSDB();

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   PageTop("stat_48.jpg","$configtray_0_Head");

   $hostname=GetSamsHostName();
   $ipaddr=GetIPAddr();

   $uptime=ExecuteShellScript("uptime","");
   print("<TABLE WIDTH=90%>");
   print("<TR>");
   print("<TD WIDTH=\"25%\"><B>$configtray_0_Hostname</B>");
   print("<TD WIDTH=\"75%\">$hostname");
   print("<TR>");
   print("<TD WIDTH=\"25%\"><B>$configtray_0_IP</B>");
   print("<TD WIDTH=\"75%\">$ipaddr");
   print("<TR>");
   print("<TD WIDTH=\"25%\"><B>$configtray_0_Uptime</B>");
   print("<TD WIDTH=\"75%\">$uptime");
   print("</TABLE>");

   MemoryUsage();
   FileSystemUsage();
   
  $syea=strftime("%Y");
  $smon=strftime("%m");
  $eday=strftime("%d");

  $sdate="$syea-$smon-1";
  $edate="$syea-$smon-$eday";
  $stime="0:00:00";
  $etime="0:00:00";

   print("<P><TABLE CLASS=samstable>\n");
   print("<TH>\n");
   print("<TH width=\"33%\" >$configtray_3_SumTraffic\n");
   print("<TH width=\"33%\" >$configtray_3_FromCache\n");
   print("<TH width=\"33%\" >$configtray_3_Traffic\n");

   $num_rows=$DB->samsdb_query_value("SELECT sum(s_size),sum(s_hit) FROM cachesum WHERE s_date>='$sdate' AND s_date<='$edate' ");
   $row=$DB->samsdb_fetch_array();
   print("<TR>\n");
   print("<TD >$configtray_3_M\n");
   $aaa=FormattedString("$row[0]");
   RTableCell($aaa,33);
   $aaa=FormattedString("$row[1]");
   RTableCell($aaa,33);
   $aaa=$row[0]-$row[1];
   $aaa=FormattedString($row[0]-$row[1]);
   RTableCell($aaa,33);
   
  $num_rows=$DB->samsdb_query_value("SELECT sum(s_size),sum(s_hit) FROM cachesum WHERE s_date='$edate' ");
  $row=$DB->samsdb_fetch_array();
   print("<TR>\n");
   print("<TD >$configtray_3_D\n");
   $aaa=FormattedString("$row[0]");
   RTableCell($aaa,33);
   $aaa=FormattedString("$row[1]");
   RTableCell($aaa,33);
   $aaa=$row[0]-$row[1];
   $aaa=FormattedString($row[0]-$row[1]);
   RTableCell($aaa,33);
   
   print("</TABLE>\n");
   
}

 
function CUserDoc()
{
  global $SAMSConf;
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("user.jpg","$admintray_UserDoc_1");

  print("<H2>$admintray_UserDoc_2</H2>");
  print("</CENTER>");
  print("<IMG SRC=\"$SAMSConf->ICONSET/lframe.jpg\" ALIGN=LEFT>");
  print("$admintray_UserDoc_3");
  print("$admintray_UserDoc_4");
}


function ConfigTray()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1 )
  {
	print("<SCRIPT>\n");
	print("parent.basefrm.location.href=\"main.php?show=exe&function=sysinfo&filename=configtray.php\";\n");    
	print("</SCRIPT> \n");
	print("<TABLE WIDTH=\"95%\" BORDER=0>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=\"25%\"\">");
	print("<B>$adminbuttom_1_prop_SamsReConfigForm_1</B>\n");

	ExecuteFunctions("./src", "configbuttom","1");

	print("<TD>\n");
	print("</TABLE>\n");
  }
  else
  {
	print("<SCRIPT>\n");
	 print("parent.basefrm.location.href=\"main.php?show=exe&function=cuserdoc&filename=configtray.php\";\n");    
	print("</SCRIPT> \n");
  }



}

?>
