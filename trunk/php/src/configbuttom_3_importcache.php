<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

/*
 importcachesumtable()
 Функция импорта данных из таблицы squidlog.cachesum базы sams 1.x
 $hostname - адрес хоста для подключения к базе SAMS 1.x
 $username - логин пользователя для подключения к базе SAMS 1.x
 $pass - пароль пользователя для подключения к базе SAMS 1.x
*/

function importcachesumtable()
{
  global $SAMSConf;
  global $USERConf;
  global $DATE;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  // смотрим, может ли пользователь конфигурировать SAMS
  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	{       exit;     }

  $sdate=$DATE->sdate();
  $edate=$DATE->edate();

 if(isset($_GET["hostname"])) $hostname=$_GET["hostname"];
 if(isset($_GET["username"])) $username=$_GET["username"];
 if(isset($_GET["pass"])) $pass=$_GET["pass"];
 if(isset($_GET["startdate"])) $startdate=$_GET["startdate"];
 if(isset($_GET["enddate"])) $enddate=$_GET["enddate"];

 PageTop("importcachedb_48.jpg","<FONT COLOR=BLUE>$confogbuttom_3_importcache_importcachesumtable_1</FONT>");

 echo "<IMG SRC=\"$SAMSConf->ICONSET/loading.gif\" ALIGN=CENTER>\n";
 // создаем подключение к базе данных SAMS 1.x
 $oldDB=new CREATESAMSDB("MySQL", "0", $hostname, $username, $pass, "squidlog", "0");
 // создаем подключение к базе данных SAMS 2.x
 $DB=new SAMSDB();

 // Выбираем данные трафике пользователей за день из базы SAMS 1.x
 $row_count=$oldDB->samsdb_query_value("SELECT * FROM squidlog.cachesum WHERE date>='$sdate' AND date<='$edate' ");
 $ps=0;
 $count=0;
 while($row=$oldDB->samsdb_fetch_array())
 {
	// Заносим данные о трафике пользователей за день в базу SAMS 2
	$QUERY="INSERT INTO cachesum (s_proxy_id, s_date, s_user, s_domain, s_size, s_hit) VALUES ('1', '$row[date]', '$row[user]', '$row[domain]', '$row[size]', '$row[hit]' )";
	$DB->samsdb_query($QUERY);
	$count++;
	
 }
 echo "<BR><B>$count $confogbuttom_3_importcache_importcachesumtable_3</B><BR>";
 // Переходим на страницу импорта данных о трафике пользователей
 printf("<SCRIPT LANGUAGE=\"javascript\">\n");
 printf("document.location='main.php?show=exe&function=importcachetable&filename=configbuttom_3_importcache.php&hostname=$hostname&username=$username&pass=$pass&startdate=$sdate&enddate=$edate&rowcounter=0';\n");
 printf("</SCRIPT>\n");
}

/*
 importcachetable()
 Функция импорта данных о трафике пользователей 
 из таблицы squidlog.cache базы sams 1.x
 $hostname - адрес хоста для подключения к базе SAMS 1.x
 $username - логин пользователя для подключения к базе SAMS 1.x
 $pass - пароль пользователя для подключения к базе SAMS 1.x
 $startdate - дата, начиная с которой производится импорт данных
 $rowcounter - количество записей, импортированных из таблицы squidlog.cache
*/

function importcachetable()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  // смотрим, может ли пользователь конфигурировать SAMS
  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	{       exit;     }

 if(isset($_GET["hostname"])) $hostname=$_GET["hostname"];
 if(isset($_GET["username"])) $username=$_GET["username"];
 if(isset($_GET["pass"])) $pass=$_GET["pass"];
 if(isset($_GET["startdate"])) $startdate=$_GET["startdate"];
 if(isset($_GET["enddate"])) $enddate=$_GET["enddate"];
 if(isset($_GET["rowcounter"])) $rowcounter=$_GET["rowcounter"];

 // создаем подключение к базе данных SAMS 1.x
 $oldDB=new CREATESAMSDB("MySQL", "0", $hostname, $username, $pass, "squidlog", "0");
 // создаем подключение к базе данных SAMS 2.x
 $DB=new SAMSDB();

 // Получаем список дат, данные за которые находятся в таблице squidlog.cache
 $QUERY="SELECT * FROM squidlog.cachesum WHERE date>='$startdate' AND date<='$enddate' GROUP BY date";

 $num_rows=$oldDB->samsdb_query_value($QUERY);

 $count=0;
 $row=$oldDB->samsdb_fetch_array();
 // Получаем дату, за которую производится импорт данных
 $thisdate=$row['date'];

 if($num_rows>1)
 {
    $row=$oldDB->samsdb_fetch_array();
    $nextdate=$row['date'];
 }
 else
    $nextdate=$thisdate;

 //Выбираем данные 
 $QUERY="SELECT * FROM squidlog.cache WHERE date='$thisdate'";
 $row_count=$oldDB->samsdb_query_value($QUERY);

 if($row_count>0)
 {
  // Переходим на страницу импорта данных о трафике пользователей

	if($num_rows>1)
	{
		PageTop("importcachedb_48.jpg","$confogbuttom_3_importcache_importcachetable_1 <FONT COLOR=BLUE>$thisdate</FONT> <BR>$row_count $confogbuttom_3_importcache_importcachetable_4");

		echo "<P><IMG SRC=\"$SAMSConf->ICONSET/loading.gif\" ALIGN=CENTER>\n";
	}
	else
	{
		PageTop("importcachedb_48.jpg","<FONT COLOR=BLUE>$confogbuttom_3_importcache_importcachetable_3</FONT> <BR>$rowcounter $confogbuttom_3_importcache_importcachetable_2");
	}
	$count=0;
	while($row=$oldDB->samsdb_fetch_array())
	{ 
		// Заносим данные о трафике пользователей в базу SAMS 2
		$QUERY="INSERT INTO squidcache (s_proxy_id, s_date, s_time, s_user, s_domain, s_size, s_hit, s_url, s_method) VALUES ('1', '$row[date]', '$row[time]', '$row[user]', '$row[domain]', '$row[size]', '$row[hit]', '$row[url]', 'GET' )";
 		$DB->samsdb_query($QUERY);
		$count++;
	}
	$rowcounter+=$count;

	// Переходим на страницу импорта данных о трафике пользователей
	if($num_rows>1)
	{
		printf("<SCRIPT LANGUAGE=\"javascript\">\n");
		printf("document.location='main.php?show=exe&function=importcachetable&filename=configbuttom_3_importcache.php&hostname=$hostname&username=$username&pass=$pass&startdate=$nextdate&enddate=$enddate&rowcounter=$rowcounter';\n");
		printf("</SCRIPT>\n");
	}
 }
 else
 {
	 PageTop("importcachedb_48.jpg","<FONT COLOR=BLUE>$confogbuttom_3_importcache_importcachetable_3</FONT> <BR>$rowcounter $confogbuttom_3_importcache_importcachetable_2");

 }
}

function selectimportdateform()
{
  global $SAMSConf;
  global $USERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);
  if(isset($_GET["hostname"])) $hostname=$_GET["hostname"];
  if(isset($_GET["username"])) $username=$_GET["username"];
  if(isset($_GET["pass"])) $pass=$_GET["pass"];

  $oldDB=new CREATESAMSDB("MySQL", "0", $hostname, $username, $pass, "squidlog", "0");
  $num_rows=$oldDB->samsdb_query_value("SELECT min(date) FROM squidlog.cache");
  $row=$oldDB->samsdb_fetch_array();
  $startdate=$row[0];
  $num_rows=$oldDB->samsdb_query_value("SELECT max(date) FROM squidlog.cache");
  $row=$oldDB->samsdb_fetch_array();
  $enddate=$row[0];

 // Получаем дату, за которую производится импорт данных
  PageTop("importcachedb_48.jpg","$configbuttom_3_importcache_selectimportdateform_1");
  print("<P><IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/importfromsams1.html\">$documentation</A>");
  print("<P>\n");
  echo "<B>$configbuttom_3_importcache_selectimportdateform_2 $startdate $configbuttom_3_importcache_selectimportdateform_3 $enddate";
  echo "<BR>$configbuttom_3_importcache_selectimportdateform_4</B>";

  require("reportsclass.php");
  $dateselect=new DATESELECT($startdate,$enddate);
  print("<FORM NAME=\"selectdate\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"importcachesumtable\">\n");
#  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"importcachetable\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"configbuttom_3_importcache.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"hostname\" value=\"$hostname\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"username\" value=\"$username\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"pass\" value=\"$pass\">\n");
  $dateselect->SetPeriod();
  printf("<BR><CENTER>");
  print("</FORM>\n");
}


function importcacheform()
{
  global $SAMSConf;
  global $USERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);
  PageTop("importcachedb_48.jpg","$configbuttom_3_import_importdataform_1 ");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/importfromsams1.html\">$documentation</A>");
  print("<P>\n");

  print("<FORM NAME=\"createdatabase\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"selectimportdateform\">\n");
//  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"importcachesumtable\">\n");

//  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"importcachetable\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"configbuttom_3_importcache.php\">\n");
  print("<TABLE WIDTH=\"90%\">\n");
  print("<TR><TD ALIGN=RIGHT>DB Hostname: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"hostname\" value=\"localhost\">\n");
  print("<TR><TD ALIGN=RIGHT>DB login: <TD ALIGN=LEFT><INPUT TYPE=\"TEXT\" NAME=\"username\" >\n");
  print("<TR><TD ALIGN=RIGHT>DB password: <TD ALIGN=LEFT><INPUT TYPE=\"PASSWORD\" NAME=\"pass\">\n");
  print("</TABLE>\n");

  printf("<BR><CENTER>");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$configbuttom_3_import_importdataform_5\">\n");
  print("</FORM>\n");
}



function configbuttom_3_importcache()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       GraphButton("main.php?show=exe&function=importcacheform&filename=configbuttom_3_importcache.php", "basefrm","importcachedb_32.jpg","importcachedb_48.jpg","$configbuttom_3_importcache_configbuttom_3_importcache_1");
    }
}

?>
