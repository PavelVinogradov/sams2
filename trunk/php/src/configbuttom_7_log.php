<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */



function ShowLogPeriod()
{
  global $SAMSConf;
  global $DATE;
  global $USERConf;
  global $SquidUSERConf;

  if(isset($_GET["id"])) $id=$_GET["id"];
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
  require("reportsclass.php");
  $dateselect=new DATESELECT($DATE->sdate(),$DATE->edate());
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("WAUC")!=1)
	exit(0);


  PageTop("usertraffic_48.jpg","$configbuttom_7_log_1");

  print("<TABLE WIDTH=\"90%\"><TR><TD>");
  print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=id value=\"$SquidUSERConf->s_user_id\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"showlogperiod\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"configbuttom_7_log.php\">\n");
	$dateselect->SetPeriod();
  print("<TD><IMG SRC=\"$SAMSConf->ICONSET/printer.gif\" TITLE=\"Print\" ALT=\"Print\" onClick=\"JavaScript:window.print();\"></TABLE>\n");
  print("</FORM>\n");
  printf("<BR><B>$traffic_2 $bdate $traffic_3 $eddate</B> ");

  $count=1;
  $cache=0;
  print("<TABLE CLASS=samstable>");
  print("<THEAD>\n");
//  if($USERConf->ToWebInterfaceAccess("C")==1)
//    {
      print("<TH>$configbuttom_7_log_2");
      print("<TH>$configbuttom_7_log_3");
      print("<TH>$configbuttom_7_log_4");
      print("<TH>$configbuttom_7_log_5");
      print("<TH>Значение $configbuttom_7_log_6");
//    }   
  print("<TH>Код $configbuttom_7_log_7");

  print("</THEAD>\n");
  print("<TBODY>\n");
  $size=0;
  $QUERY="SELECT * FROM samslog WHERE s_date>='$sdate' AND s_date<='$edate' ORDER BY s_date, s_time";
  $num_rows=$DB->samsdb_query_value($QUERY);

  while($row=$DB->samsdb_fetch_array())
       {
         print("<TR>");

         RTableCell($row['s_log_id'],25);

         LTableCell($row['s_date'],15);

         RTableCell($row['s_time'],25);

         RTableCell($row['s_issuer'],25);

         RTableCell($row['s_value'],25);

         RTableCell($row['s_code'],25);

         print("</TR>");
         $count=$count+1;
         $size=$size+$row[0];
	 $cache=$cache+$row[4];
       }
  print("<TR>");
  print("</TBODY>\n");
  print("<TD>");
  print("</TABLE>");
}






/****************************************************************/
function SamsLogForm()
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
		PageTop("usertraffic_48.jpg","$configbuttom_7_log_1");

		print("<FORM NAME=\"UserIDForm\" ACTION=\"main.php\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=UserName value=\"$SquidUSERConf->s_user_id\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"showlogperiod\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"configbuttom_7_log.php\">\n");
		$dateselect->SetPeriod();
		print("</FORM>\n");
	}
}


function configbuttom_7_log()
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("WAUCS")==1 || $USERConf->ToGroupStatAccess("G", $SquidUSERConf->s_group_id))
	{
		GraphButton("main.php?show=exe&function=samslogform&filename=configbuttom_7_log.php&id=$SquidUSERConf->s_user_id","basefrm","usertraffic_32.jpg","usertraffic_48.jpg","$configbuttom_7_log_1");
	}

}




?>
