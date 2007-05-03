<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_4_z1_urldenied()
 {
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
      print("   denied = insFld(sams, gFld(\"$lframe_sams_FolderDenied_1\", \"main.php?show=exe&function=adddeniedlistform\", \"stop.gif\"))\n");
      $result=mysql_query("SELECT * FROM redirect WHERE type=\"denied\"");
      while($row=mysql_fetch_array($result))
         {
           print("      insDoc(denied, gLnk(\"D\", \"$row[name]\", \"tray.php?show=exe&function=deniedlisttray&id=$row[filename]\",\"pfile.gif\"))\n");
         }
    }	 

 }
 
 

 ?>