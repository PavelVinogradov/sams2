<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function LoadSquidLog()
{
   global $SAMSConf;
   
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  copy($_FILES['userfile']['tmp_name'],"data/loadsquid.sql");
  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);
  $finp=gzopen("data/loadsquid.sql","r");
  while(gzeof($finp)==0)
    {
      $string=gzgets($finp, 10000);
      $string2=strtok($string,";");
      if(!strstr($string,"#" ))
        {
          $result=mysql_query("$string2");
        }
     }
  gzclose($finp);
  unlink("data/loadsquid.sql");
}

function LoadSquidLogForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("reark_48.jpg","$squidbuttom_2_loadbase_LoadSquidLogForm_1");
  print("<BR>$squidbuttom_2_loadbase_LoadSquidLogForm_2 \n");
  print("<FORM NAME=\"LOADBACKUP\" ENCTYPE=\"multipart/form-data\" ACTION=\"main.php?show=exe&function=loadsquidlog&filename=squidbuttom_2_loadbase.php\" METHOD=POST>\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILES_SIZE\" value=\"1048576\">\n");
  print("<INPUT TYPE=\"FILE\" name=\"userfile\" value=\"$squidbuttom_2_loadbase_LoadSquidLogForm_3\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$squidbuttom_2_loadbase_LoadSquidLogForm_4\">\n");
  print("</FORM>\n");

}



function squidbuttom_2_loadbase()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);


   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=loadsquidlogform&filename=squidbuttom_2_loadbase.php","basefrm","loadbase_32.jpg","loadbase_48.jpg","$squidbuttom_2_loadbase_squidbuttom_2_loadbase_1");
	}

}




?>
