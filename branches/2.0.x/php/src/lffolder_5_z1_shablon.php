<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_5_z1_shablon()
 {
  global $SAMSConf;
 $DB=new SAMSDB($SAMSConf->DB_ENGINE, $SAMSConf->ODBC, $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB);
 $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

 if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "C")==1)
    {
      print("   groups = insFld(sams, gFld(\"$lframe_sams_UserShablonFolder_1\", \"main.php?show=exe&function=newshablonform&filename=shablonnew.php\", \"paddressbook.gif\"))\n");
      $DB->samsdb_query("SELECT * FROM shablon");
      while($row=$DB->samsdb_fetch_array())
         {
           print("      insDoc(groups, gLnk(\"D\", \"$row[s_name]\", \"tray.php?show=exe&function=shablontray&filename=shablontray.php&id=$row[s_shablon_id]\",\"pgroup.gif\"))\n");
         }
    }	 

 }
 
 

 ?>