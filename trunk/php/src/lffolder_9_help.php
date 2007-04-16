<?
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_9_help($access,$sams)
 {
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
       print("   licenses = insDoc($sams,gLnk(\"R\",\"$lframe_sams_lframe_sams_6\",\"doc/$SAMSConf->LANG/index.html\",\"help.jpg\"))\n");
    }	 

 }
 
 

 ?>