<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */




function UserFileSize()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  global $SquidUSERConf;

  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["size"])) $size=$_GET["size"];
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);

	if($USERConf->ToWebInterfaceAccess("GSC")!=1 && ($USERConf->s_user_id != $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")!=1 ) )
	{
		exit(0);
	}

  $DB=new SAMSDB();
 
  $sdate=$DATE->sdate();
  $edate=$DATE->edate();
  $bdate=$DATE->BeginDate();
  $eddate=$DATE->EndDate();
  $sday=$DATE->sday;
  $smon=$DATE->smon;
  $syea=$DATE->syea;
  $shou=$DATE->shou;
  $eday=$DATE->eday;
  $emon=$DATE->emon;
  $eyea=$DATE->eyea;
  require("reportsclass.php");
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("WAUC")!=1)
	exit(0);


	PageTop("filesize_48.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT> <BR>$userbuttom_3_file_UserFileSizeForm_1");

	print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=UserName value=\"$SquidUSERConf->s_user_id\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userfilesize\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_3_file.php\">\n");
		$dateselect->SetPeriod();
	print("<B>$usersbuttom_3_file_UsersFileSizePeriod_3</B> <INPUT TYPE=\"TEXT\" NAME=\"size\"><br>");
	print("</FORM>\n");

	$URL=array("time"=>array(),
	"url"=>array(),
	"method"=>array());

	$fsize=$size*1024;
	$QUERY="select s_date, s_time, s_user, s_size, s_url from squidcache where s_user='$SquidUSERConf->s_nick'AND s_date>='$sdate' AND s_date<='$edate' AND s_size>'$fsize' ORDER BY s_size";
	$num_rows=$DB->samsdb_query_value($QUERY);

	print("<TABLE CLASS=samstable>");
	print("<TH>No");
	print("<TH>$traffic_data");
	print("<TH>$userbuttom_3_file_UserFileSizePeriod_4");
	print("<TH>URL");

	$count=1;
	while($row=$DB->samsdb_fetch_array())
	{
		print("<TR>");
		LTableCell($count,8);
		$aaa=ReturnDate($row['s_date']);
		LTableCell($aaa,15);
		$aaa=FormattedString($row['s_size']);
		RTableCell($aaa,20);
		LTableCell($row['s_url'],57);

		$count=$count+1;
	}
	print("</TABLE>");

}


/****************************************************************/
function UserFileSizeForm()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;

  if(isset($_GET["id"])) $id=$_GET["id"];
  $SquidUSERConf=new SAMSUSER();
  $SquidUSERConf->sams_user($id);

  require("reportsclass.php");
  $dateselect=new DATESELECT("","");
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("GSC")==1 || ($USERConf->s_user_id == $SquidUSERConf->s_user_id && $USERConf->ToWebInterfaceAccess("W")==1 ) )
	{
		PageTop("filesize_48.jpg","$traffic_1 <FONT COLOR=\"BLUE\">$SquidUSERConf->s_nick</FONT> <BR>$userbuttom_3_file_UserFileSizeForm_1");

		print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=UserName value=\"$SquidUSERConf->s_user_id\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"userfilesize\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"userbuttom_3_file.php\">\n");
		$dateselect->SetPeriod();
		print("<B>$usersbuttom_3_file_UsersFileSizePeriod_3</B> <INPUT TYPE=\"TEXT\" NAME=\"size\"><br>");
		print("</FORM>\n");
	}
}


function userbuttom_3_file()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("WAUCS")==1 || $USERConf->ToGroupStatAccess("G", $SquidUSERConf->s_group_id))
	{
		GraphButton("main.php?show=exe&function=userfilesizeform&filename=userbuttom_3_file.php&id=$SquidUSERConf->s_user_id","basefrm","filesize_32.jpg","filesize_48.jpg","$userbuttom_3_file_userbuttom_3_file_3");
	}

}




?>
