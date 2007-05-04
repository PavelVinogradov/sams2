<?
require('./mysqltools.php');
LoadConfig();
$passwd=crypt("qwerty","00");
db_connect("squidctrl") or exit;
mysql_select_db("squidctrl");
$result=mysql_query("UPDATE squidctrl.passwd SET pass=\"$passwd\" WHERE user=\"Admin\"");


?>
