<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_1_config()
 {
  global $SAMSConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    print("   licenses = insDoc(sams,gLnk(\"D\",\"$lframe_sams_lframe_sams_2\",\"tray.php?show=exe&function=configtray&filename=configtray.php\",\"config_20.jpg\"))\n");

 }
 
 
 ?>