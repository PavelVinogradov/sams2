<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function HelpSquidForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  PageTop("reconfig_48.jpg","$squidtray_HelpSquidForm_1");
  print("<P><P>\n");
  print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\">");
  //print("<A HREF=\"doc/reconfig.html\">");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
  print("<TD>$squidtray_HelpSquidForm_2");
  print("</TABLE>");

  if($SAMSConf->access==2)
    {
      $squidlogfiles=0;
      //print("<TD VALIGN=\"TOP\" WIDTH=\"30%\">$squidtray_HelpSquidForm_3 ");

      $filelist=`ls backup/squidlog*`;
      $filelen=strlen($filelist);
      $filename=strtok($filelist,chr(0x0a));
      $filename=str_replace("backup/","",$filename);
      if(strlen($filename)>2)
        {
          if($squidlogfiles==0)
	      {
                 print("<H3>$squidtray_HelpSquidForm_3 </H3>");
                 print("<P><TABLE border=0 WIDTH=\"60%\">\n");
                 print("<TH >N ");
                 print("<TH >Filename");
                 print("<TH >Size");
             }
	  $squidlogfiles++;
	  $filesize=filesize("backup/$filename");
          print("<TR>\n");
          print("<TD WIDTH=\"10%\" ALIGN=\"CENTER\">$squidlogfiles ");
          print("<TD WIDTH=\"70%\" ALIGN=\"LEFT\">");
          print("<B><A HREF=\"backup/$filename\">$filename</A></B>\n");
          print("<TD WIDTH=\"20%\" ALIGN=\"CENTER\"> $filesize");
	}  
      $len=$len+strlen($filename)+1;
      
      while($len<$filelen)
        {
           $filename=strtok(chr(0x0a));
           $filename=str_replace("backup/","",$filename);
           if(strlen($filename)>2)
             {
	       $squidlogfiles++;
               $filesize=filesize("backup/$filename");
               print("<TR>\n");
               print("<TD WIDTH=\"10%\" ALIGN=\"CENTER\">$squidlogfiles ");
               print("<TD WIDTH=\"70%\" ALIGN=\"LEFT\">");
               print("<B><A HREF=\"backup/$filename\">$filename</A></B>\n");
               print("<TD WIDTH=\"20%\" ALIGN=\"CENTER\"> $filesize");
	     }  
           $len=$len+strlen($filename)+1;
        }
       print("</TABLE>");
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

  $result=mysql_query("SELECT * FROM redirect WHERE filename=\"$id\" ");
  $row=mysql_fetch_array($result);
  if($SAMSConf->access==2)
    {
      print("<TABLE border=0 WIDTH=\"100%\">\n");
      print("<TR>\n");
      print("<TD VALIGN=\"TOP\" WIDTH=\"30%\">");
      print("<B><FONT SIZE=\"+1\">SQUID</FONT></B>\n");

      ExecuteFunctions("./src", "squidbuttom","");

     }
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
