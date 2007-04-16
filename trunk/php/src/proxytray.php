<?
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */




function ProxyTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  print("<SCRIPT>\n");
  print(" parent.basefrm.location.href=\"main.php?show=about\";\n");    
  print("</SCRIPT> \n");

  print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
  print("<TR>\n");
  print("<TD VALIGN=\"TOP\" WIDTH=\"30%\"\">");
  print("<B>Proxy</B>\n");


  $filelist=`ls src/proxybuttom*`;
  //print(" $filelist");
  $filelen=strlen($filelist);
  $filename=strtok($filelist,chr(0x0a));
  $funcname=str_replace("src/","",$filename);
  $funcname=str_replace(".php","",$funcname);
  //print(" $filename  $funcname ");
  require($filename);
  $funcname($SAMSConf->access,$row[name]);
  $len=$len+strlen($filename)+1;
  while($len<$filelen)
    {
 	   $filename=strtok(chr(0x0a));
       $funcname=str_replace("src/","",$filename);
       $funcname=str_replace(".php","",$funcname);
       //print(" $filename  $funcname ");
       require($filename);
       $funcname($SAMSConf->access,$row[name]);
       $len=$len+strlen($filename)+1;
    }

  print("<TD>\n");
  print("</TABLE>\n");


}

?>
