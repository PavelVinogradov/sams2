<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function LoadLocalList()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["listfilename"])) $listfilename=$_GET["listfilename"];
  if(isset($_GET["type"])) $type=$_GET["type"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  

  PageTop("import_48.jpg","$redir_loadfurllist1 $listfilename");
  $listfilename=$_FILES["userfile"]["name"];
  copy($_FILES['userfile']['tmp_name'], "data/urllist.txt");

  $finp=fopen("data/urllist.txt","r");
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");
  while(feof($finp)==0)
    {
       $string=fgets($finp, 10000);
       $string=trim($string);
       if(strlen($string)>1)
         $result=mysql_query("INSERT INTO urls SET urls.url=\"$string\",type=\"$type\" ");
    }
  fclose($finp);
  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php?show=localtraftray\";\n");
  print("</SCRIPT> \n");


}


/****************************************************************/

function LoadLocalListForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("import_48.jpg","$redir_loadfurllist1");
  print("<FORM NAME=\"LOADBACKUP\" ENCTYPE=\"multipart/form-data\" ACTION=\"main.php?show=exe&function=loadlocallist&filename=localbuttom_2_loadlist.php&type=local \" METHOD=POST>\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILES_SIZE\" value=\"1048576\">\n");
  print("<BR><INPUT TYPE=\"FILE\" NAME=\"userfile\" value=\"$redir_importurllistform1\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Load file\">\n");
  print("</FORM>\n");

}




function localbuttom_2_loadlist()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");

GraphButton("main.php?show=exe&function=loadlocallistform&filename=localbuttom_2_loadlist.php","basefrm","import_32.jpg","import_48.jpg","$user_usertray3");
	}

}




?>
