<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function SaveBackUp()
{
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB(&$SAMSConf);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("C")!=1 )
		exit;

	$samsdb=array('auth_param', 'passwd', 'proxy', 'redirect',
	'sconfig', 'sconfig_time', 'sgroup', 'shablon', 'squiduser',
	'sysinfo', 'timerange', 'url', 'websettings');
	$filename=strftime("samsdb-%d%b%Y-%H-%M-%S.sql.gz");


	if(($fout=gzopen("data/$filename","w9"))!=NULL)
	{
		gzwrite($fout,"# ".$SAMSConf->SAMSDB." DUMP FOR ".$SAMSConf->DB_ENGINE." DATABASE\n");
		gzwrite($fout,"USE ".$SAMSConf->SAMSDB.";\n");
		for($tcount=0;$tcount<count($samsdb);$tcount++)
		{
			gzwrite($fout,"DROP TABLE IF EXISTS `".$samsdb[$tcount]."`;\n");
			gzwrite($fout,"CREATE TABLE `".$samsdb[$tcount]."`;\n");
			$QUERY="SELECT * FROM ".$samsdb[$tcount];
			$num_rows=$DB->samsdb_query_value($QUERY);
			while($row=$DB->samsdb_fetch_array())
			{
				$export = "INSERT INTO ".$samsdb[$tcount]." VALUES(";
				$a=count($row);
				for($i=0;$i<$a;$i++)
				{
					$export=$export."'".$row[$i]."'";
					if($i<$a-1)
						$export=$export.",";
				}
				$export=$export.");";
				gzwrite($fout,$export."\n");
			}
		}
		gzclose($fout);
	}
	else
	{
		fwrite($fout,"# ".$SAMSConf->SAMSDB." DUMP FOR ".$SAMSConf->DB_ENGINE." DATABASE\n");
		fwrite($fout,"USE ".$SAMSConf->SAMSDB.";\n");
		for($tcount=0;$tcount<count($samsdb);$tcount++)
		{
			fwrite($fout,"DROP TABLE IF EXISTS `".$samsdb[$tcount]."`;\n");
			fwrite($fout,"CREATE TABLE `".$samsdb[$tcount]."`;\n");
			$QUERY="SELECT * FROM ".$samsdb[$tcount];
			$num_rows=$DB->samsdb_query_value($QUERY);
			while($row=$DB->samsdb_fetch_array())
			{
				$export = "INSERT INTO ".$samsdb[$tcount]." VALUES(";
				$a=count($row);
				for($i=0;$i<$a;$i++)
				{
					$export=$export."'".$row[$i]."'";
					if($i<$a-1)
						$export=$export.",";
				}
				$export=$export.");";
				fwrite($fout,$export."\n");
			}
		}
		fclose($fout);
	}
  print("<P><A HREF=\"data/$filename\">\n");
  print("<BR>$backupbuttom_1_savebase_SaveBackUp_1 <B>$filename</B>\n");
  print("</A>\n");

}


function SaveBackUpForm()
{
	global $SAMSConf;
	global $USERConf;
	
	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	if($USERConf->ToWebInterfaceAccess("C")!=1 )
		exit;
  
	PageTop("backup_48.jpg","$backupbuttom_1_savebase_SaveBackUpForm_1");
	print("<BR>\n");
	print("<FORM NAME=\"BACKUP\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"savebackup\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"configbuttom_4_backup.php\">\n");
	print("<BR>$backupbuttom_1_savebase_SaveBackUpForm_2\n");
	print("<TABLE>\n");
	print("</TABLE>\n");
	print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$backupbuttom_1_savebase_SaveBackUpForm_3\">\n");
	print("</FORM>\n");

}


function configbuttom_4_backup()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("C")==1 )
	{
		GraphButton("main.php?show=exe&function=savebackupform&filename=configbuttom_4_backup.php","basefrm","savebase_32.jpg","savebase_48.jpg","$backupbuttom_1_savebase_backupbuttom_1_savebase_1");
	}

}




?>
