<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
/*  
  print("<P>\n");
  print("<FORM NAME=\"EDITURL\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"editurlfromlist\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"execute\" id=function value=\"redirlistform\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"urllistfunction.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"$id\"> \n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"oldvalue\" VALUE=\"\"> \n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"editurl\" SIZE=30> \n");
  print("<INPUT TYPE=\"SUBMIT\" value=\"Change\">\n");
  print("</FORM>\n");
*/
 
function EditURLFromList()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $exefilename="";
  if(isset($_GET["type"])) $type=$_GET["type"];
  if(isset($_GET["editurl"])) $url=$_GET["editurl"];
  if(isset($_GET["oldvalue"])) $oldvalue=$_GET["oldvalue"];
  if(isset($_GET["execute"])) $execute=$_GET["execute"];
  if(isset($_GET["exefilename"])) $exefilename=$_GET["exefilename"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }

   $oldvalue = UnecranChars ($oldvalue);
   $url = UnecranChars ($url);

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");
  $result=mysql_query("UPDATE urls SET url=\"$url\" WHERE url=\"$oldvalue\"&&type=\"$type\" ");
  $result=mysql_query("SELECT * FROM redirect WHERE filename=\"$type\" ");
  $row=mysql_fetch_array($result);
  UpdateLog("$SAMSConf->adminname","$urllistfunction_AddURLFromList_1 $row[name] $urllistfunction_AddURLFromList_2 $url","02");

  print("<SCRIPT>\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&function=$execute&filename=$exefilename&id=$type\";\n");
  print("</SCRIPT> \n");
  
}
 
 
function DeleteList()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["execute"])) $execute=$_GET["execute"];

  //print("<BR>SELECT * FROM redirect WHERE redirect.filename=\"$id\" ");
  $result=mysql_query("SELECT * FROM redirect WHERE redirect.filename=\"$id\" ");
  $row=mysql_fetch_array($result);
  $result=mysql_query("DELETE FROM redirect WHERE filename=\"$id\" ") || die (mysql_error());
  UpdateLog("$SAMSConf->adminname","$urllistfunction_DeleteList_1 $row[name]","02");

  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=$execute\";\n");
  print("</SCRIPT> \n");

}

function AddNewList()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["type"])) $type=$_GET["type"];
  if(isset($_GET["name"])) $name=$_GET["name"];
  if(isset($_GET["function"])) $function=$_GET["function"];
  if(isset($_GET["execute"])) $execute=$_GET["execute"];
  if(isset($_GET["exefilename"])) $exefilename=$_GET["exefilename"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }

  $id=TempName();
  //print("<BR> INSERT INTO redirect SET name=\"$name\",filename=\"$id\",type=\"$type\" ") || die (mysql_error());
  $result=mysql_query("INSERT INTO redirect SET name=\"$name\",filename=\"$id\",type=\"$type\" ") || die (mysql_error());
  UpdateLog("$SAMSConf->adminname","$urllistfunction_AddNewList_1 $name","02");

  //print("<BR>AddNewList(): tray.php?show=exe&function=$execute&id=$id");

  //tray.php?show=deniedlisttray&id=$row[filename]
  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("  parent.tray.location.href=\"tray.php?show=exe&function=$execute&id=$id\";\n");
  print("</SCRIPT> \n");

}


function DeleteAllURLFromList()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $exefilename="";
  if(isset($_GET["type"])) $type=$_GET["type"];
  if(isset($_GET["execute"])) $execute=$_GET["execute"];
  if(isset($_GET["exefilename"])) $exefilename=$_GET["exefilename"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");
  $result=mysql_query("DELETE FROM urls WHERE type=\"$type\" ");
  UpdateLog("$SAMSConf->adminname","$urllistfunction_DeleteAllURLFromList_1 $type","02");

  print("<SCRIPT>\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&function=$execute&filename=$exefilename&id=$type\";\n");
  print("</SCRIPT> \n");

}

function AddURLFromList()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $exefilename="";
  if(isset($_GET["type"])) $type=$_GET["type"];
  if(isset($_GET["addurl"])) $url=$_GET["addurl"];
  if(isset($_GET["execute"])) $execute=$_GET["execute"];
  if(isset($_GET["exefilename"])) $exefilename=$_GET["exefilename"];

  $url = UnecranChars($url);

  if(strlen($url)>0)
    {   
      $SAMSConf->access=UserAccess();
      if($SAMSConf->access!=2)     {       exit;     }
  
      db_connect($SAMSConf->SAMSDB) or exit();
      mysql_select_db($SAMSConf->SAMSDB)
           or print("Error\n");
      $result=mysql_query("INSERT INTO urls SET url=\"$url\",type=\"$type\" ");
      $result=mysql_query("SELECT * FROM redirect WHERE filename=\"$type\" ");
      $row=mysql_fetch_array($result);
      UpdateLog("$SAMSConf->adminname","$urllistfunction_AddURLFromList_1 $row[name] $urllistfunction_AddURLFromList_2 $url","02");
    }
  
  print("<SCRIPT>\n");
    print("        parent.basefrm.location.href=\"main.php?show=exe&function=$execute&filename=$exefilename&id=$type\";\n");
  print("</SCRIPT> \n");

}

function DeleteURLFromList()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $exefilename="";
 if(isset($_GET["type"])) $type=$_GET["type"];
  if(isset($_GET["delete"])) $url=$_GET["delete"];
  if(isset($_GET["execute"])) $execute=$_GET["execute"];
  if(isset($_GET["exefilename"])) $exefilename=$_GET["exefilename"];
  if(isset($_GET["deletedurl"])) $deletedurl=$_GET["deletedurl"];

  $deletedurl = UnecranChars ($deletedurl);

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");
  $result=mysql_query("DELETE FROM urls WHERE urls.url=\"$deletedurl\"&&urls.type=\"$type\" ");
  $result=mysql_query("SELECT * FROM redirect WHERE filename=\"$type\" ");
  $row=mysql_fetch_array($result);
  UpdateLog("$SAMSConf->adminname","$urllistfunction_DeleteURLFromList_1 $row[name] $urllistfunction_DeleteURLFromList_2 $url","02");

  print("<SCRIPT>\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&function=$execute&filename=$exefilename&id=$type\";\n");
  print("</SCRIPT> \n");
 
}



?>
