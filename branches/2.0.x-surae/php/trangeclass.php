<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

class SAMSTRANGE
{
  var $s_trange_id;
  var $s_name; 
  var $s_days;
  var $s_timestart;
  var $s_timeend;
  var $s_shour;
  var $s_smin;
  var $s_ehour;
  var $s_emin;
  var $alltrange=array();
  var $trange=array();
function trangeday($dayofweek)
{
 $ret=$this->s_days[$dayofweek];
 return($ret);
}

function SAMSTRANGE($trangeid)
{
  global $SAMSConf;
  $DB=new SAMSDB();
  $num_rows=$DB->samsdb_query_value("SELECT *, extract(hour from s_timestart) as s_hour, extract(hour from s_timeend) as e_hour, extract(minute from s_timestart) as s_min, extract(minute from s_timeend) as e_min  FROM timerange WHERE s_trange_id='$trangeid' ");
  $row=$DB->samsdb_fetch_array();
  $this->s_trange_id=$row['s_trange_id'];
  $this->s_name=$row['s_name']; 
  $this->s_timestart=$row['s_timestart'];
  $this->s_timeend=$row['s_timeend'];
  $this->s_shour=$row['s_hour'];
  $this->s_ehour=$row['e_hour'];
  $this->s_smin=$row['s_min'];
  $this->s_emin=$row['e_min'];

   if(strstr($row['s_days'], "M"))   $this->s_days[1]="CHECKED"; 
   if(strstr($row['s_days'], "T"))   $this->s_days[2]="CHECKED"; 
   if(strstr($row['s_days'], "W"))   $this->s_days[3]="CHECKED";
   if(strstr($row['s_days'], "H"))   $this->s_days[4]="CHECKED"; 
   if(strstr($row['s_days'], "F"))   $this->s_days[5]="CHECKED"; 
   if(strstr($row['s_days'], "A"))   $this->s_days[6]="CHECKED";
   if(strstr($row['s_days'], "S"))   $this->s_days[7]="CHECKED"; 

  $DB->free_samsdb_query();
}

}
?>
