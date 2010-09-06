<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function lframe_0ident()
{
global $SAMSConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
//exit(0);

  if(strlen($SAMSConf->domainusername)>0||strlen($SAMSConf->adminname)>0)
    {
      print("   logoff = insFld(foldersTree, gFld2(\" logoff\", \"main.php?function=logoff\", \"logoff_20.gif\"))\n");
    }  
  
  print("   auth = insFld(foldersTree, gFld2(\"$lframe_0ident_lframe_0ident_1\", \"tray.php?show=exe&function=admintray\", \"ident.gif\"))\n");

}

?>
