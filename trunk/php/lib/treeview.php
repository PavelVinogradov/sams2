<?php

function treeItem($array)
{
	global $SAMSConf;

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	echo "<style type=\"text/css\">\n
	.filetree span.".$array['classname']." { padding: 1px 0 1px 25px; display: block; }\n
	.filetree span.".$array['classname']." { background: url($SAMSConf->ICONSET/".$array['icon'].") 0 0 no-repeat; }\n
	</style>\n";
	echo "<li>";
	echo "<span class=\"".$array['classname']."\">\n";
	echo "<A TARGET=\"".$array['target']."\"  HREF=\"".$array['url']."\">";
	echo $array['text'];
	echo "</A></span></li>\n";
}


function treeFolder($array)
{
	global $SAMSConf;

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	echo "<style type=\"text/css\">\n
	.filetree span.".$array['classname']." { padding: 1px 0 1px 25px; display: block; }\n
	.filetree span.".$array['classname']." { background: url($SAMSConf->ICONSET/".$array['icon'].") 0 0 no-repeat; }\n
	</style>\n";
	echo "<li class=\"closed collapsable\"> <div class=\"hitarea collapsable-hitarea\"></div>";
	echo "<span class=\"".$array['classname']."\">\n";
	echo "<A TARGET=\"".$array['target']."\"  HREF=\"".$array['url']."\">";
	echo $array['text'];
	echo "</A></span>\n";
	echo "<ul id=\"".$array['classname']."\">\n";
}

function treeFolderClose()
{
	echo "</ul></li>\n";
}

function treeFolderItem($array)
{
	global $SAMSConf;

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);

	echo "<li>";
	echo "<span class=\"".$array['classname']."\">";
	echo "<A TARGET=\"".$array['target']."\"  HREF=\"".$array['url']."\">";
	echo $array['text'];
	echo "</A></span></li>\n";
}


?>