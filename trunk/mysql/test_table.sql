# SAMS - SQUID - DB MySQL-Dump
# --------------------------------------------------------
#GRANT ALL ON squidlog.* TO squid@localhost IDENTIFIED BY "redir";
USE squidlog;

DROP TABLE IF EXISTS `redirect_test`;
CREATE TABLE `redirect_test` (
  `inp` char(50),
  `ip` char(15),
  `out` char(50),
  `user` char(30),
  `pid` int(6)
) TYPE=MyISAM;
   
