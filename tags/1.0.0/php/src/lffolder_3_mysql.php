<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_3_mysql()
 {
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
  print("   licenses = insDoc(sams,gLnk(\"D\",\"MySQL\",\"tray.php?show=exe&function=dbtray\",\"db.gif\"))\n");

 }
 
 
 ?>