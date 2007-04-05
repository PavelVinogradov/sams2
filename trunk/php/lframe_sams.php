<?
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
  //print("   sams = insFld(foldersTree, gFld(\"SAMS\", \"tray.php?show=exe&function=proxytray\", \"proxy.gif\"))\n");
  //print("   sams = insDoc(foldersTree,gLnk(\"D\",\"SAMS\",\"tray.php?show=exe&function=proxytray&filename=proxytray.php\", \"proxy.gif\"))\n");
  
  $filelist=`ls src/lffolder*`;
  $filelen=strlen($filelist);
  $filename=strtok($filelist,chr(0x0a));
  $funcname=str_replace("src/","",$filename);
  $funcname=str_replace(".php","",$funcname);
  //     print("filename = $filename $funcname");
  require($filename);
//  exit(0);
  $funcname("$SAMSConf->access","sams");
  $len=$len+strlen($filename)+1;
  while($len<$filelen)
    {
       //print("$len = $filelen");
       $filename=strtok(chr(0x0a));
       $funcname=str_replace("src/","",$filename);
       $funcname=str_replace(".php","",$funcname);
//       print("filename = $filename $funcname");
       require($filename);
       $funcname("$SAMSConf->access","sams");
       $len=$len+strlen($filename)+1;
    }


}

?>
