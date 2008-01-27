<?

require('./mysqltools.php');
$SAMSConfig = new SAMSCONFIG;
$SAMSConfig->LoadConfig();

$passwd=crypt("qwerty","00");
db_connect($SAMSConfig->MYSQLDATABASE) or exit;
mysql_select_db($SAMSConfig->MYSQLDATABASE);
$result=mysql_query("UPDATE ".$SAMSConfig->MYSQLDATABASE.".passwd SET pass=\"$passwd\" WHERE user=\"Admin\"");

?>
