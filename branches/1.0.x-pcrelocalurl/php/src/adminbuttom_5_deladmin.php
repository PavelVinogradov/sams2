<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function DeleteAdmin()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  if(isset($_GET["username"])) $username=$_GET["username"];

  if(strstr("Admin",$SAMSConf->adminname)||strstr("auditor",$SAMSConf->adminname))
    {
      db_connect($SAMSConf->SAMSDB) or exit();
      mysql_select_db($SAMSConf->SAMSDB) 
    	  or print("Error\n");
      
      $result=mysql_query("DELETE FROM passwd WHERE user=\"$username\" ");
      PageTop("user_48.jpg","$adminbuttom_5_deladmin_DeleteAdmin_1 $username $adminbuttom_5_deladmin_DeleteAdmin_2");
    }
  else
    {
       PageTop("warning.jpg","$adminbuttom_5_deladmin_DeleteAdmin_3");
    }

}

function DeleteAdminForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
 if(strstr("Admin",$SAMSConf->adminname))
    {
      PageTop("user_48.jpg","$adminbuttom_5_deladmin_DeleteAdminForm_1");
      print("<P>\n");
      print("<FORM NAME=\"DELETEADMIN\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"deleteadmin\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"adminbuttom_5_deladmin.php\">\n");
      print("<SELECT NAME=\"username\" >\n");
      db_connect($SAMSConf->SAMSDB) or exit();
      mysql_select_db($SAMSConf->SAMSDB)
          or print("Error\n");
      $result2=mysql_query("SELECT * FROM passwd WHERE user!=\"Admin\"&&user!=\"auditor\"");
      while($row=mysql_fetch_array($result2))
        {
               print("<OPTION VALUE=$row[user] SELECTED> $row[user]");
        }
      print("</SELECT>\n");
      print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$adminbuttom_5_deladmin_DeleteAdminForm_2\">\n");
      print("</FORM>\n");
    }
  else
    {
       PageTop("warning.jpg","$adminbuttom_5_deladmin_DeleteAdminForm_3");
    }

}


function adminbuttom_5_deladmin()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=deleteadminform&filename=adminbuttom_5_deladmin.php",
	               "basefrm","trash_32.jpg","trash_48.jpg","$adminbuttom_5_deladmin_adminbuttom_5_deladmin_1");
    }

}

?>
