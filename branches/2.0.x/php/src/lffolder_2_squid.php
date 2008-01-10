<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_2_squid()
 {
  global $SAMSConf;
 $DB=new SAMSDB($SAMSConf->DBNAME, $SAMSConf->ODBC, $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($SAMSConf->access==2)
  {
        print("   proxy = insFld(sams, gFld2(\"SQUID\", \"tray.php?show=exe&filename=squidtray.php&function=squidtray\", \"pobject.gif\"))\n");
	$DB->samsdb_query("SELECT * FROM proxy");
	while($row=$DB->samsdb_fetch_array())
	{
		print("      insDoc(proxy, gLnk(\"D\", \"$row[s_description]\", \"tray.php?show=exe&function=proxytray&filename=proxytray.php&id=$row[s_proxy_id]\",\"pgroup.gif\"))\n");
         }
  }
 
}
 
 
 ?>