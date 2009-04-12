<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function logoff()
{
 print("<h1>LOGOFF</h1>");
}

function userbuttom_9_logoff()
{
  global $SAMSConf;
  global $USERConf;
    
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];
   if($USERConf->s_user_id==$id)
    {
       GraphButton("main.php?function=logoff",	               "basefrm","logoff_32.jpg","logoff_48.jpg","$userbuttom_9_logoff_userbuttom_9_logoff_1");
    }
}

?>
