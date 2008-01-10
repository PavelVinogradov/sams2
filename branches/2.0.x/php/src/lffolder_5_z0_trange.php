<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_5_z0_trange()
 {
  global $SAMSConf;
  $DB=new SAMSDB("$SAMSConf->DBNAME", "0", $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

//  if($SAMSConf->access==2)
//    {
//     print("licenses = insDoc(sams, gLnk(\"D\", \"$lframe_sams_lframe_sams_1\",\"tray.php?show=exe&function=localtraftray\",\"pfile.gif\"))\n");
//    }	 
  if($SAMSConf->access==2)
    {
      print("   timerange = insFld(sams, gFld(\"Time Range\", \"main.php?show=exe&filename=trangetray.php&function=addtrangeform\", \"clock.gif\"))\n");
      $num_rows=$DB->samsdb_query_value("SELECT * FROM timerange ");
      while($row=$DB->samsdb_fetch_array())
         {
              print("      insDoc(timerange, gLnk(\"D\", \"$row[s_name]\", \"tray.php?show=exe&filename=trangetray.php&function=trangetray&id=$row[s_trange_id]\",\"pfile.gif\"))\n");
         }
    }	 

 }
 
 

 ?>