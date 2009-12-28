<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */



function dbbuttom_9_upgr()
{
  global $SAMSConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

 $SAMSConf->access=UserAccess();
 if($SAMSConf->access==2)
    {
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function ReloadBaseFrame()\n");
       print("{\n");
       print("   window.location.reload();\n");
       print("}\n");
       print("function DeleteUser(userid)\n");
       print("{\n");
       print("  value=window.confirm(\"Upgrade SAMS database? \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=upgrade_mysql_table&filename=../data/upgrade_mysql_table.php&action=web\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");

       print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/dbupgrade_32.jpg\" \n ");
       print("TITLE=\"Upgrade SAMS database\"  border=0 ");
       print("onclick=DeleteUser(\"$userid\") \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/dbupgrade_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/dbupgrade_32.jpg'\" >\n");

    }

}







?>
