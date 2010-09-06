<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_2_squid()
 {
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($SAMSConf->access==2)
     print("   licenses = insDoc(sams,gLnk(\"D\",\"SQUID\",\"tray.php?show=exe&function=squidtray\",\"pobject.gif\"))\n");

 }
 
 
 ?>