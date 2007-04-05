<?
require('./mysqltools.php');
$passwd=crypt("dima","00");
db_connect("squidctrl") or exit;
mysql_select_db("squidctrl");
$result=mysql_query("UPDATE squidctrl.passwd SET pass="\$passwd"\" WHERE user=\"Admin\");
?>
