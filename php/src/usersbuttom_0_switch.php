<?
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */





function usersbuttom_0_switch()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->SWITCHTO>0)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"50\">\n");
       GraphButton("main.php?show=exe","basefrm","switch_32.jpg","switch_48.jpg","$usersbuttom_1_domain_usersbuttom_1_domain_1 Active Directory");
	}

}

?>
