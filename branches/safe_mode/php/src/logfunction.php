<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

/****************************************************************/

/****************************************************************/
function UpdateLog($username,$value,$code)
{
  $year=date("Y")*1;
  $month=date("m")*1;
  $day=date("d")*1;
  $date="$year-$month-$day";
  $hour=date("H")*1;
  $min=date("i")*1;
  $sec=date("s")*1;
  $time="$hour:$min:$sec";
  db_connect($SAMSConf->SQUIDCTRLDATABASE) or exit();
    mysql_select_db($SAMSConf->SQUIDCTRLDATABASE);
  $result=mysql_query("INSERT INTO log SET user=\"$username\",date=\"$date\",time=\"$time\",value=\"$value\",code=\"$code\" ");
}


?>
