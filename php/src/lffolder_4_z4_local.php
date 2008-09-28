<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_4_z4_local()
 {
  global $SAMSConf;
  $DB=new SAMSDB("$SAMSConf->DB_ENGINE", $SAMSConf->ODBC, $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB, $SAMSConf->ODBCSOURCE);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

//  if($SAMSConf->access==2)
//    {
//     print("licenses = insDoc(sams, gLnk(\"D\", \"$lframe_sams_lframe_sams_1\",\"tray.php?show=exe&function=localtraftray\",\"pfile.gif\"))\n");
//    }	 
 if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "LC")==1)
    {
      print("   redir2 = insFld(sams, gFld(\"$lframe_sams_lframe_sams_1\", \"main.php?show=exe&filename=redirlisttray.php&function=addurllistform&type=local\", \"pfile.gif\"))\n");
      $num_rows=$DB->samsdb_query_value("SELECT * FROM redirect WHERE s_type='local' ");
      while($row=$DB->samsdb_fetch_array())
         {
              print("      insDoc(redir2, gLnk(\"D\", \"$row[s_name]\", \"tray.php?show=exe&filename=redirlisttray.php&function=redirlisttray&id=$row[s_redirect_id]\",\"pfile.gif\"))\n");
         }
    }	 

 }
/*

      print("   redir = insFld(sams, gFld(\"$lframe_sams_FolderRedirect_1\", \"main.php?show=exe&filename=redirlisttray.php&function=addurllistform&type=redir\", \"redirect.gif\"))\n");
      $num_rows=$DB->samsdb_query_value("SELECT * FROM redirect WHERE s_type='redir' ");
      while($row=$DB->samsdb_fetch_array())
         {
              print("      insDoc(redir, gLnk(\"D\", \"$row[s_name]\", \"tray.php?show=exe&filename=redirlisttray.php&function=redirlisttray&id=$row[s_redirect_id]\",\"pfile.gif\"))\n");
         }

*/ 
 

 ?>