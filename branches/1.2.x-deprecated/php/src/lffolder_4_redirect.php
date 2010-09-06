<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_4_redirect()
 {
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
      print("   redir = insFld(sams, gFld(\"$lframe_sams_FolderRedirect_1\", \"main.php?show=exe&function=addredirlistform\", \"redirect.gif\"))\n");
      $result=mysql_query("SELECT * FROM redirect WHERE type=\"redir\"");
      while($row=mysql_fetch_array($result))
         {
              print("      insDoc(redir, gLnk(\"D\", \"$row[name]\", \"tray.php?show=exe&function=redirlisttray&id=$row[filename]\",\"pfile.gif\"))\n");
         }
    }	 

 }
 
 

 ?>