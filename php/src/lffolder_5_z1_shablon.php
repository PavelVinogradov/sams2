<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_5_z1_shablon()
 {
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB(&$SAMSConf);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

// if($SAMSConf->access==2 || $SAMSConf->ToUserDataAccess($USERConf->s_user_id, "C")==1)
    if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
//	echo "<style type=\"text/css\">\n
//	.filetree span.shablon { padding: 1px 0 1px 25px; display: block; }\n
//	.filetree span.shablon { background: url($SAMSConf->ICONSET/paddressbook.gif) 0 0 no-repeat; }\n
//	</style>\n";
//	echo "<li class=\"closed collapsable\"> <div class=\"hitarea collapsable-hitarea\"></div> <span class=\"shablon\">$lframe_sams_UserShablonFolder_1</span>\n";
//	echo "<ul id=\"shablon\">\n";
	$item=array("classname"=> "shablon",
		"icon" => "paddressbook.gif",
		"target"=> "basefrm",
		"url"=> "main.php?show=exe&function=newshablonform&filename=shablonnew.php",
		"text"=> "$lframe_sams_UserShablonFolder_1");
	treeFolder($item);

//	print("   groups = insFld(sams, gFld(\"$lframe_sams_UserShablonFolder_1\", \"main.php?show=exe&function=newshablonform&filename=shablonnew.php\", \"paddressbook.gif\"))\n");
	$DB->samsdb_query("SELECT * FROM shablon");
	while($row=$DB->samsdb_fetch_array())
	{
//		echo "<li><span class=\"shablon\">$row[s_name]</span></li>";
		$item=array("classname"=> "shablon",
			"target"=> "tray",
			"url"=> "tray.php?show=exe&function=shablontray&filename=shablontray.php&id=$row[s_shablon_id]",
			"text"=> "$row[s_name]");
		treeFolderItem($item);
//           print("      insDoc(groups, gLnk(\"D\", \"$row[s_name]\", \"tray.php?show=exe&function=shablontray&filename=shablontray.php&id=$row[s_shablon_id]\",\"pgroup.gif\"))\n");
	}
	treeFolderClose();
    }	 

 }
 
 

 ?>