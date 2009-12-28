<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_4_z4_local()
 {
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
     print("licenses = insDoc(sams, gLnk(\"D\", \"$lframe_sams_lframe_sams_1\",\"tray.php?show=exe&function=localtraftray\",\"pfile.gif\"))\n");
    }	 

 }
 
 

 ?>