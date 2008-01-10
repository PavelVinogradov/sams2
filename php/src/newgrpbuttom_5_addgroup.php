<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddGroup()
{
  global $SAMSConf;
  $DB=new SAMSDB($SAMSConf->DBNAME, $SAMSConf->ODBC, $SAMSConf->MYSQLHOSTNAME, $SAMSConf->MYSQLUSER, $SAMSConf->MYSQLPASSWORD, $SAMSConf->SAMSDB);

  if(isset($_GET["groupname"])) $groupname=$_GET["groupname"];

  $num_rows=$DB->samsdb_query_value("INSERT INTO sgroup ( s_name ) VALUES('$groupname') ");
//  if($result!=FALSE)
//      UpdateLog("$SAMSConf->adminname","Added group  $groupnick ","02");

  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("  parent.tray.location.href=\"tray.php?show=exe&filename=newgrptray.php&function=newgrptray\";\n");
  print("</SCRIPT> \n");
}


function NewGroupForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("usergroup_48.jpg","$grouptray_NewGroupForm_1");

       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function TestName(formname)\n");
       print("{\n");
       print("  var shablonname=formname.groupnick.value; \n");
       print("  if(shablonname.length==0) \n");
       print("    {\n");
       print("       alert(\"$grouptray_NewGroupForm_12\");\n");
       print("       return false");
       print("    }\n");
       print("  return true");
       print("}\n");
       print("</SCRIPT> \n");
 
  print("<FORM NAME=\"NEWUSER\" ACTION=\"main.php\" onsubmit=\"return TestName(NEWUSER)\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"addgroup\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"newgrpbuttom_5_addgroup.php\">\n");
  print("<TABLE>\n");
  print("<TR>\n");
  print("<TD>\n");
  print("<B>$grouptray_NewGroupForm_2\n");
  print("<TD>\n");
  print("<INPUT TYPE=\"TEXT\" NAME=\"groupname\" SIZE=30> \n" );
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
