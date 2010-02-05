<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

class SAMSSHABLON
{
  var $s_shablon_id;
  var $s_shablon_id2;
  var $s_name;
  var $s_auth;
  var $s_quote;
  var $s_period;
  var $s_clrdate;
  var $s_alldenied;

function SAMSSHABLON($shablonid)
{
  global $SAMSConf;
  $DB=new SAMSDB();

  $num_rows=$DB->samsdb_query_value("SELECT * FROM shablon WHERE s_shablon_id='$shablonid' ");
  $row=$DB->samsdb_fetch_array();
  $this->s_shablon_id=$row['s_shablon_id'];
  $this->s_shablon_id2=$row['s_shablon_id2'];
  $this->s_name=$row['s_name'];
  $this->s_auth=$row['s_auth'];
  $this->s_quote=$row['s_quote'];
  $this->s_period=$row['s_period'];
  $this->s_clrdate=$row['s_clrdate'];
  $this->s_alldenied=$row['s_alldenied'];

  $DB->free_samsdb_query();

}






}

?>
