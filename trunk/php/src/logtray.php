<?
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function LogTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php\";\n");
      print("</SCRIPT> \n");

      print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
      print("<TR>\n");
      print("<TD VALIGN=\"TOP\" WIDTH=\"30%\"\">");
      print("<B><FONT SIZE=\"+1\">$logtray_LogTray_1</FONT></B>\n");

      $filelist=`ls src/logbuttom*`;
      $filelen=strlen($filelist);
      $filename=strtok($filelist,chr(0x0a));
      $funcname=str_replace("src/","",$filename);
      $funcname=str_replace(".php","",$funcname);
      require($filename);
      $funcname($SAMSConf->access);
      $len=$len+strlen($filename)+1;
      while($len<$filelen)
        {
	       $filename=strtok(chr(0x0a));
           $funcname=str_replace("src/","",$filename);
           $funcname=str_replace(".php","",$funcname);
           require($filename);
           $funcname($SAMSConf->access);
           $len=$len+strlen($filename)+1;
        }
    }
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
