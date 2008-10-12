<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

class SquidImpl {

	var $SamsDb;
    var $SamsUser;
    var $SamsConf;
	var $SamsTools;
	
	function SquidImpl ($db, $user, $conf, $tools) {
        	$this->SamsDb = $db;
        	$this->SamsUser = $user;
        	$this->SamsConf = $conf;
		$this->SamsTools = $tools;
	}	

function HelpSquidForm()
{
  global $SAMSConf;
  $files=array();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	$output = "";		
//	$output .= $this->SamsTools->PageTop("reconfig_48.jpg","$squidtray_HelpSquidForm_1");
	$output .= $this->SamsTools->PageTop("reconfig_48.jpg","$squidtray_HelpSquidForm_1");
        $output .= "<P><P>\n";
	$output .= "<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\">";
  //print("<A HREF=\"doc/reconfig.html\">");
	$output .= "<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>";
	$output .= "<TD>$squidtray_HelpSquidForm_2";
	$output .= "</TABLE>";

	if($SAMSConf->access==2)
	{
		$squidlogfiles=0;

		$scount=0;
		if ($handle2 = opendir("./backup"))
		{
			while (false !== ($file = readdir($handle2)))
			{
				if($file!="."&&$file!=".."&&$file!=".svn")
				{
					if(strlen($file)>0)
					{
						$script[$scount]=$file;
						$scount++;
					}  

				}
			}
		}

		if($scount>0)
		{
			$output .= "<H3>$squidtray_HelpSquidForm_3 </H3>";
			$output .= "<P><TABLE border=0 WIDTH=\"60%\">\n";
			$output .= "<TH >N ";
			$output .= "<TH >Filename";
			$output .= "<TH >Size";
		}
		for($i=0;$i<$scount;$i++)
		{
			$filesize=filesize("./backup/$script[$i]");
			$output .= "<TR>\n";
			$output .= "<TD WIDTH=\"10%\" ALIGN=\"CENTER\">$i ";
			$output .= "<TD WIDTH=\"70%\" ALIGN=\"LEFT\">";
			$output .= "<B><A HREF=\"backup/$script[$i]\">$script[$i]</A></B>\n";
			$output .= "<TD WIDTH=\"20%\" ALIGN=\"CENTER\"> $filesize";
		}
	}
	return $output;
}


function tray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	$output = "";		
        $output .= "<SCRIPT>\n";
        $output .= "        parent.basefrm.location.href=\"main.php?module=Squid&function=helpsquidform&\";\n";
        $output .= "</SCRIPT>\n";
//            $output .= "parent.basefrm.location.href=\"main.php?module=WebConfig&function=cuserdoc\";\n";

	if($SAMSConf->access==2)
	{
	        $output .= "<TABLE border=0 WIDTH=\"100%\">\n";
	        $output .= "<TR>\n";
	        $output .= "<TD VALIGN=\"TOP\" WIDTH=\"30%\">";
	        $output .= "<B><FONT SIZE=\"+1\">SQUID</FONT></B>\n";
		$output .= $this->SamsTools->ExecuteFunctions("./plugins/squid/buttons", "squidbuttom","1");

	}
	$output .= "<TD>\n";
	$output .= "</TABLE>\n";
    return $output;

}



}
?>
