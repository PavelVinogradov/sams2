<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_5_z2_groups()
 {
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
   
  if($SAMSConf->access==2)
    {
//      print("   licenses = insDoc($sams,gLnk(\"R\",\"$lframe_sams_UserGroupsFolder_1\", \"tray.php?show=exe&filename=newgrptray.php&function=newgrptray\", \"paddressbook.gif\"))\n");
      print("   licenses = insDoc(sams,gLnk(\"D\",\"$lframe_sams_UserGroupsFolder_1\", \"tray.php?show=exe&filename=newgrptray.php&function=newgrptray\", \"paddressbook.gif\"))\n");
    }	 

 }
 
 

 ?>