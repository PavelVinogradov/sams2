<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddGroup()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1)
	exit;

  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["groupnick"])) $groupnick=$_GET["groupnick"];

  $result=$DB->samsdb_query_value("SELECT s_name FROM sgroup where s_name = '$groupnick'");
  if($result == 0) 
  {
    $result=$DB->samsdb_query("INSERT INTO sgroup (s_name) VALUES('$groupnick') ");

    print("<SCRIPT>\n");
    print("  parent.lframe.location.href=\"lframe.php\"; \n");
    print("  parent.tray.location.href=\"tray.php?show=usergrouptray&groupname=$groupname&groupnick=$groupnick\";\n");
    print("</SCRIPT> \n");
  } else {
    PageTop("usergroup_48.jpg","$newgroupbuttom_5_addgroup_newgrpbuttom_5_groupexist");
  }
}


function NewGroupForm()
{
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("C")!=1)
	exit;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("usergroup_48.jpg","$grouptray_NewGroupForm_1");

  print("<FORM NAME=\"NEWUSER\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addgroup\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"newgrpbuttom_5_addgroup.php\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$grouptray_NewGroupForm_2\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"groupnick\" SIZE=30> \n" );
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$denied_quickadddeniedurlform4\">\n");
  print("</FORM>\n");

}



function newgrpbuttom_5_addgroup()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
       GraphButton("main.php?show=exe&function=newgroupform&filename=newgrpbuttom_5_addgroup.php",
	               "basefrm","useradd_32.jpg","useradd_48.jpg","$newgroupbuttom_5_addgroup_newgrpbuttom_5_addgroup_1");
    }
}

?>
