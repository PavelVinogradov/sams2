<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function lframe_sams()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();

  print("   sams = insFld(foldersTree, gFld2(\"SAMS\", \"tray.php?show=exe&function=proxytray&filename=proxytray.php\", \"proxy.gif\"))\n");

  ExecuteFunctions("./src", "lffolder_","1");

return(0);
}

?>
