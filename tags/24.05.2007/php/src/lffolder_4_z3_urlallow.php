<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_4_z3_urlallow()
 {
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
      print("   allow = insFld(sams, gFld(\"$lffolder_4_z3_urlallow_lffolder_4_z3_urlallow_1\", \"main.php?show=exe&function=addallowlistform\", \"adir.gif\"))\n");
      $result=mysql_query("SELECT * FROM redirect WHERE type=\"allow\"");
      while($row=mysql_fetch_array($result))
         {
           print("      insDoc(allow, gLnk(\"D\", \"$row[name]\", \"tray.php?show=exe&function=allowlisttray&id=$row[filename]\",\"pfile.gif\"))\n");
         }
    }	 

 }
 
 

 ?>