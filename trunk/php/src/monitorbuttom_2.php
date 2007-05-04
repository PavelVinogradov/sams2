<?
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function Monitor_2()
{
  global $SAMSConf;
  
  printf("<SCRIPT LANGUAGE=\"javascript\">\n");
  printf("function Refr() \n");
  printf("{\n");
  printf("document.location='main.php?show=exe&function=monitor_1&filename=monitorbuttom_1.php'};\n");
  printf("setTimeout('Refr();',5000);\n");
  printf("</SCRIPT>\n");
  db_connect("squidlog") or exit();
  mysql_select_db("squidctrl");

  $result=mysql_query("SELECT * FROM squidusers ORDER BY size DESC");
  $count=0;
  print("<TABLE WIDTH=\"95%\" BORDER=0>");
  while($row=mysql_fetch_array($result))
     {
        if($count==0)
           {
              print("<TR>\n");
           }
        print("<TD WIDTH=\"33%\">");
        if($row['enabled']>0)
           print("<IMAGE align=left src=\"$SAMSConf->ICONSET/personal.gif\" ");
        else
           print("<IMAGE align=left src=\"$SAMSConf->ICONSET/dpersonal.gif\" ");
        print("<B>$row[nick] <BR>");

        if($SAMSConf->realtraffic=="real")
	   $traffic=$row['size']-$row['hit'];
        else
	   $traffic=$row['size'];
	$gsize=floor($traffic/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
        $ostatok=$traffic%($SAMSConf->KBSIZE*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE);
        $msize=floor($ostatok/($SAMSConf->KBSIZE*$SAMSConf->KBSIZE));
        $ostatok=$traffic%($SAMSConf->KBSIZE*$SAMSConf->KBSIZE);
        $ksize=floor($ostatok/$SAMSConf->KBSIZE);         
	

        if($traffic>$row['quotes']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE)
          print("<FONT COLOR=\"RED\">$gsize g  $msize m $ksize k</FONT></B>\n");
        else
	      print("<FONT COLOR=\"BLUE\">$gsize g $msize m $ksize k</FONT></B>\n");

        $count=$count+1;
        if($count>2)
          {
             $count=0;
          }
     }
  print("</TABLE>");
  mysql_free_result($result);
}


function monitorbuttom_2($access)
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
      print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
      GraphButton("main.php?show=exe&function=monitor_2&filename=monitorbuttom_2.php","basefrm","trafmon-32.jpg","trafmon-48.jpg","$monitorbuttom_2_monitorbuttom_2_1");
    }

}

?>
