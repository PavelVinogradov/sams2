<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UsersImportFromNCSAFile()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")!=1 )
	exit;

  PageTop("user.jpg","$authbuttom_1_ncsaimport_LoadFileForm_1");

  if(isset($_GET["username"])) $userlist=$_GET["username"];
  if(isset($_GET["groupname"])) $usergroup=$_GET["groupname"];
  if(isset($_GET["usershablon"])) $usershablon=$_GET["usershablon"];
  if(isset($_GET["enabled"])) $enabled=$_GET["enabled"];

  if($enabled=="on")
     $enabled=1;
  else
     $enabled=-1;      

  $i=0;

  $finp=fopen("data/ncsauserslist.txt","r");
  if($finp==FALSE)
    {
      echo "can't open file data/userslist.txt<BR>";
      exit(0);
    }
	$QUERY="SELECT s_quote FROM shablon WHERE s_shablon_id='$usershablon'";
//echo "$QUERY<BR>";
  	$num_rows=$DB->samsdb_query_value($QUERY);
        if($num_rows!=0)  
	  {
		$row=$DB->samsdb_fetch_array();
		$userquote=$row['s_quote'];
          }
	$DB->free_samsdb_query();
//echo "group=$usergroup shablon=$usershablon enabled=$enabled quote=$userquote<BR>";
//echo "userquote: $userquote<BR>";
//exit;
  while(feof($finp)==0)
    {
       $string=fgets($finp, 10000);
       $string=trim($string);
	$user=strtok($string,":");
	$passwd=strtok(":");
	if(strlen($string)>1)
	{
		echo "user $user $passwd<BR>";
		$QUERY="INSERT INTO squiduser (s_group_id, s_shablon_id, s_nick, s_domain, s_enabled,s_quote, s_passwd) VALUES('$usergroup', '$usershablon', '$user', '', '$enabled','$userquote','$passwd')";
		$DB->samsdb_query($QUERY);
		echo "added<BR>";
	}
    }
  fclose($finp);

  print("<SCRIPT>\n");
  print(" parent.lframe.location.href=\"lframe.php\"; \n");
  print("</SCRIPT> \n");

}



function UsersImportFromNCSAFileForm()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   
  if($USERConf->ToWebInterfaceAccess("UC")!=1 )
	exit;
  
  $DB=new SAMSDB();

  PageTop("loadncsa_48.jpg","$authbuttom_1_ncsaimport_LoadFileForm_1");
  

	$listfilename=$_FILES["userfile"]["name"];

//echo "filename: ".$_FILES["userfile"]["name"]."<BR>";
//echo "filename: ".$_FILES["userfile"]["tmp_name"]."<BR>";
 
	$aaa=copy($_FILES["userfile"]["tmp_name"], "data/ncsauserslist.txt");

		print("<FORM NAME=\"AddUsersFromFile\" ACTION=\"main.php\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"domain\" id=Show value=\"$domain\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"usersimportfromncsafile\">\n");
		print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"authncsabuttom_2_ncsaimport.php\">\n");


		print("<TABLE>\n");
  
		print("<TR><TD><TD>\n");

//  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILES_SIZE\" value=\"1048576\">\n");
//  print("<BR><INPUT TYPE=\"FILE\" NAME=\"userfile\" value=\"$redir_importurllistform1\">\n");

		print("<TR><TD>\n");
		print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_3 \n");
		print("<TD>\n");
		print("<SELECT NAME=\"groupname\" ID=\"groupname\" SIZE=1 TABINDEX=30 >\n");

		$num_rows=$DB->samsdb_query_value("SELECT * FROM sgroup");
		while($row2=$DB->samsdb_fetch_array())
		{
			print("<OPTION VALUE=\"$row2[s_group_id]\"> $row2[s_name] \n");
		}
		$DB->free_samsdb_query();
		print("</SELECT>\n");

		print("<TR>\n");
		print("<TD>\n");
		print("<B>$usersbuttom_1_domain_AddUsersFromDomainForm_4 \n");
		print("<TD>\n");
		print("<SELECT NAME=\"usershablon\" ID=\"usershablon\" SIZE=1 TABINDEX=30 > \n");

		$num_rows=$DB->samsdb_query_value("SELECT s_shablon_id, s_name FROM shablon");
		while($row=$DB->samsdb_fetch_array())
		{
			print("<OPTION VALUE=$row[s_shablon_id]> $row[s_name]\n");
		}
		$DB->free_samsdb_query();
		print("</SELECT>");
		print("<TR><TD><B>$usersbuttom_1_domain_AddUsersFromDomainForm_6");
		print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"enabled\" CHECKED>");
		print("</TABLE>\n");

		print("<INPUT TYPE=\"SUBMIT\" value=\"$usersbuttom_1_domain_AddUsersFromDomainForm_5\">\n");

		print("</FORM>\n");

exit(0);
}

function LoadNCSAFileForm()
{
  global $SAMSConf;
  global $USERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

   if($USERConf->ToWebInterfaceAccess("UCL")!=1 )
	exit;
  
  PageTop("loadncsa_48.jpg","$authbuttom_1_ncsaimport_LoadFileForm_1");
  print("<FORM NAME=\"LOADFILE\" ENCTYPE=\"multipart/form-data\" ACTION=\"main.php?show=exe&function=usersimportfromncsafileform&filename=authncsabuttom_2_ncsaimport.php\" METHOD=POST>\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILES_SIZE\" value=\"1048576\">\n");
  print("<BR><INPUT TYPE=\"FILE\" NAME=\"userfile\" value=\"$redir_importurllistform1\">\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Load file\">\n");
  print("</FORM>\n");

}


function authncsabuttom_2_ncsaimport()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")==1 )
    {
       GraphButton("main.php?show=exe&function=loadncsafileform&filename=authncsabuttom_2_ncsaimport.php","basefrm","loadncsa_32.jpg","loadncsa_48.jpg","$authbuttom_1_ncsaimport_LoadFileForm_1 ");
	}

}

?>
