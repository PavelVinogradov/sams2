<?

require('../php/mysqltools.php');
$passwd=crypt("qwerty","00");
db_connect("squidctrl") or exit;
mysql_select_db("squidctrl");
echo "UPDATE squidctrl.passwd SET pass='$passwd' WHERE user='Admin'\n";
$result=mysql_query("UPDATE squidctrl.passwd SET pass='$passwd' WHERE user='Admin'");
echo "result=$result\n";

?>
