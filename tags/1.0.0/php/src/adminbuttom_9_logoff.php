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

function adminbuttom_9_logoff()
{
  global $SAMSConf;
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access>0)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?function=logoff",
	               "basefrm","logoff_32.jpg","logoff_48.jpg","$adminbuttom_9_logoff_logoff_1");
    }

}

?>
