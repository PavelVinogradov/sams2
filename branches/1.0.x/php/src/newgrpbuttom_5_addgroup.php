<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddGroup()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["groupnick"])) $groupnick=$_GET["groupnick"];

  $groupname=TempName();

  $result=mysql_query("SELECT nick FROM groups where nick = '$groupnick';");
  if($result and mysql_fetch_row($result) == FALSE) {
    $result=mysql_query("INSERT INTO groups VALUES('3','$groupname','$groupnick','open') ");
    if($result!=FALSE)
      UpdateLog("$SAMSConf->adminname","Added group  $groupnick ","02");

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
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=newgroupform&filename=newgrpbuttom_5_addgroup.php",
	               "basefrm","useradd_32.jpg","useradd_48.jpg","$newgroupbuttom_5_addgroup_newgrpbuttom_5_addgroup_1");
    }
}

?>
