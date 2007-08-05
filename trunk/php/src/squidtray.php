<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function HelpSquidForm()
{
  global $SAMSConf;
  $files=array();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("reconfig_48.jpg","$squidtray_HelpSquidForm_1");
  print("<P><P>\n");
  print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\">");
  //print("<A HREF=\"doc/$SAMSConf->LANGCODE/reconfig.html\">");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
  print("<TD>$squidtray_HelpSquidForm_2");
  print("</TABLE>");

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
                 print("<H3>$squidtray_HelpSquidForm_3 </H3>");
                 print("<P><TABLE border=0 WIDTH=\"60%\">\n");
                 print("<TH >N ");
                 print("<TH >Filename");
                 print("<TH >Size");
             }
	for($i=0;$i<$scount;$i++)
	    {
		$filesize=filesize("./backup/$script[$i]");
		print("<TR>\n");
		print("<TD WIDTH=\"10%\" ALIGN=\"CENTER\">$i ");
		print("<TD WIDTH=\"70%\" ALIGN=\"LEFT\">");
		print("<B><A HREF=\"backup/$script[$i]\">$script[$i]</A></B>\n");
		print("<TD WIDTH=\"20%\" ALIGN=\"CENTER\"> $filesize");
            }
    }
}



function SquidTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=helpsquidform\";\n");
      print("</SCRIPT> \n");

  if($SAMSConf->access==2)
    {
      print("<TABLE border=0 WIDTH=\"100%\">\n");
      print("<TR>\n");
      print("<TD VALIGN=\"TOP\" WIDTH=\"30%\">");
      print("<B><FONT SIZE=\"+1\">SQUID</FONT></B>\n");

      ExecuteFunctions("./src", "squidbuttom","1");

     }
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
