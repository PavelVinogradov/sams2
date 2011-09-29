<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */


function LoadBackUp()
{
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("C")!=1 )
		exit;
  
  PageTop("reark_48.jpg","$backupbuttom_2_loadbase_LoadBackUp_1");

  if(($finp=gzopen($_FILES['userfile']['tmp_name'],"r"))!=NULL)
  {
	while(gzeof($finp)==0)
	{
		$string=gzgets($finp, 10000);
		$QUERY=strtok($string,";");
		if(strstr ( $QUERY, "#" ) == FALSE)
		{
			echo "$QUERY<BR>";
			$num_rows=$DB->samsdb_query($QUERY.";");
		}
		$count++;
	}
  }
  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");
}

function LoadBackUpForm()
{
	global $SAMSConf;
	global $USERConf;
	
	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	if($USERConf->ToWebInterfaceAccess("C")!=1 )
		exit;
  
  PageTop("reark_48.jpg","$backupbuttom_2_loadbase_LoadBackUpForm_1");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/samsbackup.html\">$documentation</A>");
  print("<P>\n");
  print("<BR>$backupbuttom_2_loadbase_LoadBackUpForm_4\n");
  print("<FORM NAME=\"LOADBACKUP\" ENCTYPE=\"multipart/form-data\" ACTION=\"main.php?show=exe&function=loadbackup&filename=configbuttom_5_restore.php\" METHOD=POST>\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILES_SIZE\" value=\"10485760\">\n");
  print("<INPUT TYPE=\"FILE\" name=\"userfile\" value=\"$backupbuttom_2_loadbase_LoadBackUpForm_2\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"load file and restore backup\">\n");
  print("</FORM>\n");

}



function configbuttom_5_restore()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	if($USERConf->ToWebInterfaceAccess("C")==1 )
	{
		GraphButton("main.php?show=exe&function=loadbackupform&filename=configbuttom_5_restore.php","basefrm","loadbase_32.jpg","loadbase_48.jpg","$backupbuttom_2_loadbase_backupbuttom_2_loadbase_1");
	}

}




?>
