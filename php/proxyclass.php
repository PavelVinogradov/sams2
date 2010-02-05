<?php
class SAMSPROXY
{

  var $s_proxy_id;
  var $s_description;
  var $s_endvalue;
  var $s_redirect_to;
  var $s_denied_to;
  var $s_redirector;
  var $s_delaypool;
  var $s_auth;
  var $s_wbinfopath;
  var $s_separator;
  var $s_usedomain;
  var $s_bigd;
  var $s_bigu;
  var $s_sleep;
  var $s_parser;
  var $s_parser_time;
  var $s_count_clean;
  var $s_nameencode;
  var $s_realsize;
  var $s_checkdns;
  var $s_debuglevel;
  var $s_defaultdomain;
  var $s_squidbase;
  var $s_udscript;
  var $s_adminaddr;
  var $s_kbsize;
  var $s_mbsize;
  var $s_ldapserver;
  var $s_ldapbasedn;
  var $s_ldapuser;
  var $s_ldappasswd;
  var $s_ldapusergroup;
  var $s_autouser;
  var $s_autotpl;
  var $s_autogrp;

function SAMSPROXY($proxy_id)
{
  global $SAMSConf;
  $DB=new SAMSDB();
  $num_rows=$DB->samsdb_query_value("SELECT * FROM proxy WHERE s_proxy_id='$proxy_id' ");
  $row=$DB->samsdb_fetch_array();
  $this->s_proxy_id=$row['s_proxy_id'];
  $this->s_description=$row['s_description'];
  $this->s_endvalue=$row['s_endvalue'];
  $this->s_redirect_to=$row['s_redirect_to'];
  $this->s_denied_to=$row['s_denied_to'];
  $this->s_redirector=$row['s_redirector'];
  $this->s_delaypool=$row['s_delaypool'];
  $this->s_auth=$row['s_auth'];
  $this->s_wbinfopath=$row['s_wbinfopath'];
  $this->s_separator=$row['s_separator'];
  $this->s_usedomain=$row['s_usedomain'];
  $this->s_bigd=$row['s_bigd'];
  $this->s_bigu=$row['s_bigu'];
  $this->s_sleep=$row['s_sleep'];
  $this->s_parser=$row['s_parser'];
  $this->s_parser_time=$row['s_parser_time'];
  $this->s_count_clean=$row['s_count_clean'];
  $this->s_nameencode=$row['s_nameencode'];
  $this->s_realsize=$row['s_realsize'];
  $this->s_checkdns=$row['s_checkdns'];
  $this->s_debuglevel=$row['s_debuglevel'];
  $this->s_defaultdomain=$row['s_defaultdomain'];
  $this->s_squidbase=$row['s_squidbase'];
  $this->s_udscript=$row['s_udscript'];
  $this->s_adminaddr=$row['s_adminaddr'];
  $this->s_kbsize=$row['s_kbsize'];
  $this->s_mbsize=$row['s_mbsize'];
  $this->s_ldapserver=$row['s_ldapserver'];
  $this->s_ldapbasedn=$row['s_ldapbasedn'];
  $this->s_ldapuser=$row['s_ldapuser'];
  $this->s_ldappasswd=$row['s_ldappasswd'];
  $this->s_ldapusergroup=$row['s_ldapusergroup'];
  $this->s_autouser=$row['s_autouser'];
  $this->s_autotpl=$row['s_autotpl'];
  $this->s_autogrp=$row['s_autogrp'];
	
  $DB->free_samsdb_query();

}
function PrintProxyClass()
{

  echo "s_proxy_id=$this->s_proxy_id<BR>";
  echo "s_description=$this->s_description<BR>";
  echo "$this->s_endvalue<BR>";
  echo "$this->s_redirect_to<BR>";
  echo "$this->s_denied_to<BR>";
  echo "$this->s_redirector<BR>";
  echo "$this->s_delaypool<BR>";
  echo "$this->s_auth<BR>";
  echo "$this->s_wbinfopath<BR>";
  echo "$this->s_separator<BR>";
  echo "$this->s_usedomain<BR>";
  echo "$this->s_bigd<BR>";
  echo "$this->s_bigu<BR>";
  echo "$this->s_sleep<BR>";
  echo "$this->s_parser<BR>";
  echo "$this->s_parser_time<BR>";
  echo "$this->s_count_clean<BR>";
  echo "$this->s_nameencode<BR>";
  echo "$this->s_realsize<BR>";
  echo "$this->s_checkdns<BR>";
  echo "$this->s_debuglevel<BR>";
  echo "$this->s_defaultdomain<BR>";
  echo "$this->s_squidbase<BR>";
  echo "$this->s_udscript<BR>";
  echo "$this->s_adminaddr<BR>";
  echo "$this->s_kbsize<BR>";
  echo "$this->s_mbsize<BR>";
  echo "$this->s_ldapserver<BR>";
  echo "$this->s_ldapbasedn<BR>";
  echo "$this->s_ldapuser<BR>";
  echo "$this->s_ldappasswd<BR>";
  echo "$this->s_ldapusergroup<BR>";
}


}
?>