<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_5_z1_shablon()
 {
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
      print("   groups = insFld(sams, gFld(\"$lframe_sams_UserShablonFolder_1\", \"main.php?show=exe&function=newshablonform&filename=shablonnew.php\", \"paddressbook.gif\"))\n");
      $result=mysql_query("SELECT * FROM shablons");
      while($row=mysql_fetch_array($result))
         {
           print("      insDoc(groups, gLnk(\"D\", \"$row[nick]\", \"tray.php?show=exe&function=shablontray&id=$row[name]\",\"pgroup.gif\"))\n");
         }
    }	 

 }
 
 

 ?>