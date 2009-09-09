<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ClearUserTrafficCounter()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB(&$SAMSConf);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("UC")!=1)
	exit(0);
  
  if(isset($_GET["id"])) $id=$_GET["id"];

  $QUERY="UPDATE squiduser SET s_size='0',s_hit='0' WHERE s_user_id='$id'";
  $num_rows=$DB->samsdb_query_value($QUERY);
  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php?show=exe&filename=usertray.php&function=usertray&auth=ip&id=$id\";\n");
//tray.php?show=exe&filename=usertray.php&function=usertray&id=$row_[s_user_id]&auth=$row_[s_auth]
  print("</SCRIPT> \n");

}




function userbuttom_5_clear($userid)
{
  global $SAMSConf;
  global $USERConf;
  global $SquidUSERConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

  if($USERConf->ToWebInterfaceAccess("UC")==1)
    {
	print("<SCRIPT language=JAVASCRIPT>\n");
	print("function ReloadBaseFrame()\n");
	print("{\n");
	print("   window.location.reload();\n");
	print("}\n");
	print("function ClearCounter(username,userid)\n");
	print("{\n");
	print("  value=window.confirm(\"$userbuttom_5_clear_userbuttom_5_clear_1 $SquidUSERConf->s_nick? \");\n");
	print("  if(value==true) \n");
	print("     {\n");
	print("        parent.basefrm.location.href=\"main.php?show=exe&function=clearusertrafficcounter&filename=userbuttom_5_clear.php&id=$SquidUSERConf->s_user_id\";\n");
	print("        window.setInterval(\"ReloadBaseFrame()\",500)\n");
	print("     }\n");
	print("}\n");
	print("</SCRIPT> \n");

	print("<TD CLASS=\"samstraytd\" >\n");
	print("<IMAGE id=Trash name=\"Clear\" src=\"$SAMSConf->ICONSET/erase_32.jpg\" \n ");
	print("TITLE=\"$userbuttom_5_clear_userbuttom_5_clear_1\"  border=0 ");
	print("onclick=ClearCounter() \n");
	print("onmouseover=\"this.src='$SAMSConf->ICONSET/erase_48.jpg'\" \n");
	print("onmouseout= \"this.src='$SAMSConf->ICONSET/erase_32.jpg'\" >\n");
    }

}




?>
