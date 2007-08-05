<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function HelpDBForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("db_48.jpg","$dbtray_HelpDBForm_1");
  print("<P><P>\n");
       print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\"><A HREF=\"doc/$SAMSConf->LANGCODE/mysql.html\">");
       print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
       print("<TD>$dbtray_HelpDBForm_2");
}


function DBTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=helpdbform\";\n");
      print("</SCRIPT> \n");

  if($SAMSConf->access==2)
    {
      print("<TABLE border=0 WIDTH=\"100%\">\n");
      print("<TR>\n");
      print("<TD VALIGN=\"TOP\" WIDTH=\"30%\">");
      print("<B><FONT SIZE=\"+1\">MySQL</FONT></B>\n");

      ExecuteFunctions("./src", "dbbuttom","1");

     }
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
