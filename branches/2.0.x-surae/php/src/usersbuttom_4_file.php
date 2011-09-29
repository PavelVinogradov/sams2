<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 
 
function UsersTrafficStat()
{
  global $SAMSConf;
  global $USERConf;
  global $DATE;

  if($USERConf->ToWebInterfaceAccess("CS")!=1)
	exit(0);

  $DB=new SAMSDB();
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();
   $size="";
   if(isset($_GET["size"])) $size=$_GET["size"];
   if(isset($_GET["sort"])) $sort=$_GET["sort"];
   if(isset($_GET["desc"])) $desc=$_GET["desc"];
   if(isset($_GET["type"])) $type=$_GET["type"];
   if(isset($_GET["text"])) $text=$_GET["text"];

  require("reportsclass.php");
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("usergroup_48.jpg","$usersbuttom_3_file_usersbuttom_3_file_1");
  print("<BR>\n");

	print("<TABLE WIDTH=\"90%\" ALIGN=CENTER><TR><TD>");
	print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userstrafficstat\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_4_file.php\">\n");
	$dateselect->SetPeriod();
	print("<SELECT NAME=\"type\">");
	if($type == "size")
	{
	print("<OPTION VALUE=\"size\" SELECTED> $mysqltools_dateselect5");
	print("<OPTION VALUE=\"url\"> $mysqltools_dateselect6");
	}
	else
	{
	print("<OPTION VALUE=\"size\"> $mysqltools_dateselect5");
	print("<OPTION VALUE=\"url\" SELECTED> $mysqltools_dateselect6");
	}
	print(" <INPUT TYPE=\"TEXT\" NAME=\"text\" VALUE=\"$text\">\n");
	print("</FORM>\n");
	print("</TABLE>\n");

  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

	if($type == "size")
	{
		$count=1;
		$bytes=$text*1024;
		$QUERY="SELECT squidcache.s_user, squidcache.s_url, squidcache.s_size, squidcache.s_hit, squidcache.s_date, squiduser.s_family, squiduser.s_name, squiduser.s_domain, squiduser.s_nick FROM squidcache LEFT JOIN squiduser ON squidcache.s_user=squiduser.s_nick WHERE squidcache.s_size>='".$bytes."' ORDER BY squidcache.s_size";

		$num_rows=$DB->samsdb_query_value($QUERY);
		print("<TABLE CLASS=samstable id=\"userstraffic\">\n");
		print("<THEAD>\n");
		print("<TH>No\n");
		print("<TH>$usersbuttom_3_file_UsersFileSizePeriod_4\n");
		print("<TH>$usersbuttom_3_file_UsersFileSizePeriod_5\n");
		print("<TH>URL\n");
		print("<TH>$usersbuttom_3_file_UsersFileSizePeriod_6\n");
		print("<TH>$squidbuttom_5_readcache_LoadFileForm_2\n");
		while($row=$DB->samsdb_fetch_array())
		{
			print("<TR>\n");
			print("<TD>$count");
			if($SAMSConf->SHOWNAME=="fam")
				$name=$row['s_family'];
			else if($SAMSConf->SHOWNAME=="famn")
				$name=$row['s_family']." ".$row['s_name'];
			else if($SAMSConf->SHOWNAME=="nickd")
				$name=$row['s_nick']." / ".$row['s_domain'];
			else 
				$name=$row['s_user'];

			LTableCell($name,15);
			LTableCell($row['s_date'],10);
			LTableCell($row['s_url'],16);
			$aaa=FormattedString($row['s_size']);
			LTableCell($aaa,10);
			$aaa=FormattedString($row['s_hit']);
			LTableCell($aaa,10);

			$count++;
		}
	}
	else
	{
		$count=1;
		$QUERY="SELECT squidcache.s_user, squidcache.s_url, squidcache.s_size, squidcache.s_hit, squidcache.s_date, squiduser.s_family, squiduser.s_name, squiduser.s_domain, squiduser.s_nick FROM squidcache LEFT JOIN squiduser ON squidcache.s_user=squiduser.s_nick WHERE squidcache.s_url like '%".$text."%' ORDER BY squidcache.s_size";

		$num_rows=$DB->samsdb_query_value($QUERY);
		print("<TABLE CLASS=samstable id=\"userstraffic\">\n");
		print("<THEAD>\n");
		print("<TH>No\n");
		print("<TH>$usersbuttom_3_file_UsersFileSizePeriod_4\n");
		print("<TH>$usersbuttom_3_file_UsersFileSizePeriod_5\n");
		print("<TH>URL\n");
		print("<TH>$usersbuttom_3_file_UsersFileSizePeriod_6\n");
		print("<TH>$squidbuttom_5_readcache_LoadFileForm_2\n");
		while($row=$DB->samsdb_fetch_array())
		{
			print("<TR>\n");
			print("<TD>$count");
			if($SAMSConf->SHOWNAME=="fam")
				$name="$row[s_family]";
			else if($SAMSConf->SHOWNAME=="famn")
				$name="$row[s_family] $row[s_name]";
			else if($SAMSConf->SHOWNAME=="nickd")
				$name="$row[s_nick] / $row[s_domain]";
			else 
				$name="$row[s_nick]";
			LTableCell($name,15);
			LTableCell($row['s_date'],10);
			LTableCell($row['s_url'],16);
			$aaa=FormattedString($row['s_size']);
			LTableCell($aaa,10);
			$aaa=FormattedString($row['s_hit']);
			LTableCell($aaa,10);

			$count++;
		}
	}


}



/****************************************************************/
function UsersStatForm()
{
  global $SAMSConf;
  global $USERConf;

	if($USERConf->ToWebInterfaceAccess("CS")!=1)
		exit(0);

	require("reportsclass.php");
	$dateselect=new DATESELECT("","");
	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);
	if(isset($_GET["userid"])) $userid=$_GET["userid"];

	PageTop("traffic_48.jpg","$alltraffic_1<BR>files");

	print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userstrafficstat\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"usersbuttom_4_file.php\">\n");
	$dateselect->SetPeriod();
	print("<SELECT NAME=\"type\">");
	print("<OPTION VALUE=\"size\" SELECTED> $mysqltools_dateselect5");
	print("<OPTION VALUE=\"url\"> $mysqltools_dateselect6");
	print(" <INPUT TYPE=\"TEXT\" NAME=\"text\">\n");
	print("</FORM>\n");


}


function usersbuttom_4_file()
{
  global $SAMSConf;
  global $USERConf;
  
  if($USERConf->ToWebInterfaceAccess("CS")==1)
  {

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

       GraphButton("main.php?show=exe&function=usersstatform&filename=usersbuttom_4_file.php","basefrm","userstat_32.jpg","userstat_48.jpg","$usersbuttom_2_traffic_usersbuttom_2_traffic_1");
  }

}




?>
