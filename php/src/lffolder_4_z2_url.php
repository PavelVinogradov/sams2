<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_4_z2_url()
 {
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
      print("   context = insFld(sams, gFld(\"$lframe_sams_FolderContextDenied_1\", \"main.php?show=exe&function=addcontextlistform\", \"stop.gif\"))\n");
      $result=mysql_query("SELECT * FROM redirect WHERE type=\"regex\"");
      while($row=mysql_fetch_array($result))
         {
           print("      insDoc(context, gLnk(\"D\", \"$row[name]\", \"tray.php?show=exe&function=contextlisttray&id=$row[filename]\",\"pfile.gif\"))\n");
         }
    }	 

 }
 
 

 ?>