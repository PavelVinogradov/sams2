<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function HelpBackUpForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("backup_48.jpg","$backuptray_HelpBackUpForm_1");
  print("<P><P>\n");
       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\"><A HREF=\"doc/$SAMSConf->LANGCODE/backup.html\">");
       print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
       print("<TD>$backuptray_HelpBackUpForm_2");
}



function BackUpTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  print("<SCRIPT>\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=helpbackupform\";\n");
  print("</SCRIPT> \n");

//  $result=mysql_query("SELECT * FROM redirect WHERE filename=\"$id\" ");
//  $row=mysql_fetch_array($result);
  if($SAMSConf->access==2)
    {
      print("<TABLE border=0 WIDTH=\"100%\">\n");
      print("<TR>\n");
      print("<TD VALIGN=\"TOP\" WIDTH=\"30%\">");
      print("<B><FONT SIZE=\"+1\" COLOR=\"BLUE\">$backuptray_BackUpTray_1</FONT></B>\n");

      ExecuteFunctions("./src", "backupbuttom","1");
     }

  print("<TD>\n");
  print("</TABLE>\n");


}

?>
