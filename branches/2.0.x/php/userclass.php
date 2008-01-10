<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

class SAMSUSER
{
  var $s_user_id;
  var $s_group_id; 
  var $s_shablon_id;
  var $s_nick;
  var $s_family;
  var $s_name;
  var $s_soname;
  var $s_domain;
  var $s_quote;
  var $s_size;
  var $s_hit;
  var $s_enabled;
  var $s_ip;
  var $s_passwd;
  var $s_gauditor;
  var $s_autherrorc;
  var $s_autherrort;
  var $s_shablon_name;
  var $s_group_name;

function SAMSUSER($userid)
{
  global $SAMSConf;
  $DB=new SAMSDB("$SAMSConf->DBNAME", "0", $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);

  $num_rows=$DB->samsdb_query_value("SELECT squiduser.*,shablon.s_name as s_shablon_name,sgroup.s_name as s_group_name FROM squiduser LEFT JOIN shablon ON squiduser.s_shablon_id=shablon.s_shablon_id LEFT JOIN sgroup ON squiduser.s_group_id=sgroup.s_group_id WHERE s_user_id='$userid' ");
  $row=$DB->samsdb_fetch_array();
  $this->s_user_id=$row['s_user_id'];
  $this->s_group_id=$row['s_group_id']; 
  $this->s_shablon_id=$row['s_shablon_id'];
  $this->s_nick=$row['s_nick'];
  $this->s_family=$row['s_family'];
  $this->s_name=$row['s_name'];
  $this->s_soname=$row['s_soname'];
  $this->s_domain=$row['s_domain'];
  $this->s_quote=$row['s_quote'];
  $this->s_size=$row['s_size'];
  $this->s_hit=$row['s_hit'];
  $this->s_enabled=$row['s_enabled'];
  $this->s_ip=$row['s_ip'];
  $this->s_passwd=$row['s_passwd'];
  $this->s_gauditor=$row['s_gauditor'];
  $this->s_autherrorc=$row['s_autherrorc'];
  $this->s_autherrort=$row['s_autherrort'];
  $this->s_shablon_name=$row['s_shablon_name'];
  $this->s_group_name=$row['s_group_name'];

  $DB->free_samsdb_query();

}






}

?>
