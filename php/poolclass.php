<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

class SAMSPOOL
{
  var $s_pool_id;
  var $s_name; 
  var $s_class; 
  var $s_agg1; 
  var $s_agg2; 
  var $s_net1; 
  var $s_net2; 
  var $s_ind1; 
  var $s_ind2; 

  var $alltrange=array();
  var $trange=array();

function SAMSPOOL($poolid)
{
  global $SAMSConf;
  $DB=new SAMSDB();

  $num_rows=$DB->samsdb_query_value("SELECT * FROM delaypool WHERE s_pool_id='$poolid' ");
  $row=$DB->samsdb_fetch_array();

  $this->s_pool_id=$row['s_pool_id'];
  $this->s_name=$row['s_name']; 
  $this->s_class=$row['s_class']; 
  $this->s_agg1=$row['s_agg1']; 
  $this->s_agg2=$row['s_agg2']; 
  $this->s_net1=$row['s_net1']; 
  $this->s_net2=$row['s_net2']; 
  $this->s_ind1=$row['s_ind1']; 
  $this->s_ind2=$row['s_ind2']; 

  $DB->free_samsdb_query();

}

}
?>
