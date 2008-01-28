<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_4_z1_urldenied()
 {
  global $SAMSConf;
  $DB=new SAMSDB("$SAMSConf->DB_ENGINE", "0", $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

 if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "LC")==1)
    {
      print("   denied = insFld(sams, gFld(\"$lframe_sams_FolderDenied_1\", \"main.php?show=exe&&filename=redirlisttray.php&function=addurllistform&type=denied\", \"stop.gif\"))\n");
      $num_rows=$DB->samsdb_query_value("SELECT * FROM redirect WHERE s_type='denied' ");
      while($row=$DB->samsdb_fetch_array())
         {
           print("      insDoc(denied, gLnk(\"D\", \"$row[s_name]\", \"tray.php?show=exe&filename=redirlisttray.php&function=redirlisttray&id=$row[s_redirect_id]\",\"pfile.gif\"))\n");
         }
    }	 

 }
 
 

 ?>