<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function lframe_1webconf()
{
global $SAMSConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
 if($SAMSConf->access==2)
    print("   web = insFld(foldersTree, gFld2(\" $lframe_1webconf_lframe_1webconf_1\", \"tray.php?show=exe&function=webconfigtray&filename=webconfigtray.php\", \"webinterface.gif\"))\n");
//return(0);

}

?>
