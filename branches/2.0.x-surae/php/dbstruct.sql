<?php

 $pgdb[0] = "CREATE TABLE websettings (
	s_lang varchar(15) NOT NULL default 'EN', 
	s_iconset varchar(25) NOT NULL default 'classic', 
	s_useraccess varchar(1) NOT NULL default '1', 
	s_urlaccess varchar(1) NOT NULL default '1', 
	s_showutree varchar(1) NOT NULL default '1' , 
	s_showname varchar(5) NOT NULL default 'name', 
	s_kbsize varchar(15) NOT NULL default '1024', 
	s_mbsize varchar(15) NOT NULL default '1048576', 
	s_showgraph varchar(1) NOT NULL default '0', 
	s_createpdf varchar(1) NOT NULL default '0')";
 
$pgdb[1] = "INSERT INTO websettings VALUES('EN','classic','1','1','1','nick','1024','1048576','0','0')";
$pgdb[2] = "CREATE TABLE proxy ( 
	s_proxy_id int default '0', 
	s_description varchar(50) default 'Proxy server', 
	s_endvalue int default '0',
	s_redirect_to varchar(100), 
	s_denied_to varchar(100), 
	s_redirector varchar(25), 
	s_delaypool varchar(1), 
	s_useraccess varchar(1), 
	s_auth varchar(4), 
	s_wbinfopath varchar(100), 
	s_urlaccess varchar(1), 
	s_separator varchar(15) default '+', 
	s_ntlmdomain varchar(1), 
	s_bigd varchar(1), 
	s_bigu varchar(1), 
	s_sleep int, 
	s_parser_on varchar(1), 
	s_parser varchar(10), 
	s_parser_time int, 
	s_count_clean varchar(1), 
	s_nameencode varchar(1), 
	s_realsize varchar(4), 
	s_checkdns varchar(1), 
	s_loglevel int NOT NULL default '0', 
	s_defaultdomain varchar(25) NOT NULL default 'workgroup', 
	s_squidbase int NOT NULL default '0', 
	s_udscript varchar(25) NOT NULL default 'NONE', 
	s_adminaddr varchar(60) ) ";

$pgdb[3] = "INSERT INTO proxy VALUES('1','main proxy server','0''http://your.ip.address/sams2/icon/classic/blank.gif','http://your.ip.address/sams2','none','1','1','ip','/usr/bin','Y','+','0','0','0','1','0','','1','1','0','real','0','0','workgroup','0','NONE','')";
 
?>
