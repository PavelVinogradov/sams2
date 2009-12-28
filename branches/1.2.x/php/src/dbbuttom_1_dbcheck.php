<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function dbrepair()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  $tablecount=0;
  if(isset($_GET["tablecount"])) $tablecount=$_GET["tablecount"];
  if(isset($_GET["table"])) $table=$_GET["table"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("dbcheck_48.jpg","$dbbuttom_1_dbcheck_dbrepair_1");
  for($i=0;$i<$tablecount;$i++)
    {
       if(strlen($table[$i])>0)
	     {
			   $result2=mysql_query("repair table $table[$i]");
               $row2=mysql_fetch_array($result2);
		       print(" <BR> Repair table  $table[$i]: $row2[3]");
		}
	}
}

function dbcheck()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  $tablecount=0;
  $squidctrlerrorcount=0;
  $squidlogerrorcount=0;
  PageTop("dbcheck_48.jpg","$dbbuttom_1_dbcheck_dbcheck_1");

  print("<TABLE WIDTH=\"90%\" BORDER=0> ");
  $result=mysql_query("show tables");
  while($row=mysql_fetch_array($result))
       {
		  print("<TR><TD WIDTH=\"30%\">check table  $row[0]: ");
          $result2=mysql_query("check table $row[0]");
          $row2=mysql_fetch_array($result2);
          if($row2[3]=="OK")
		{
		       print("<TD WIDTH=\"10%\"><FONT COLOR=\"BLUE\">  OK </FONT><TD> -");
		}
          else
		{
			$squidctrlerrorcount=$squidctrlerrorcount+1;
			$tablecount=$tablecount+1;
			$table[$tablecount]="squidctrl.$row[0]";
			print(" <TD WIDTH=\"10%\"><FONT COLOR=\"RED\">   ERROR:</FONT><TD> $row2[3]");
			print("<TD>  repair table  $row[0]: ");
			$result2=mysql_query("repair table $row[0]");
			$row2=mysql_fetch_array($result2);
		       print(" <TD>  $row2[3]");
		}
	   }
  print("</TABLE> ");

  db_connect($SAMSConf->LOGDB) or exit();
  mysql_select_db($SAMSConf->LOGDB);

  print("<P><TABLE WIDTH=\"90%\" BORDER=0> ");
  $result=mysql_query("show tables");
  while($row=mysql_fetch_array($result))
       {
		  print("<TR><TD WIDTH=\"30%\">check table  $row[0]: ");
          $result2=mysql_query("check table $row[0]");
          $row2=mysql_fetch_array($result2);
          if($row2[3]=="OK")
		    {
		       print("<TD WIDTH=\"10%\"><FONT COLOR=\"BLUE\">  OK </FONT><TD> -");
			}
          else
		    {
               $squidlogerrorcount=$squidlogerrorcount+1;
			   $tablecount=$tablecount+1;
			   $table[$tablecount]="squidlog.$row[0]";
		       print(" <TD WIDTH=\"10%\"><FONT COLOR=\"RED\">  ERROR:</FONT><TD> $row2[3]");
		       print("<TD>  repair table  $row[0]: ");
               $result2=mysql_query("repair table $row[0]");
               $row2=mysql_fetch_array($result2);
		       print(" <TD>  $row2[3]");
			}

	   }
  print("</TABLE> ");
  print("<P>$dbbuttom_1_dbcheck_dbcheck_2 $squidctrlerrorcount <BR>");
  print("$dbbuttom_1_dbcheck_dbcheck_3 $squidlogerrorcount  <BR>");
  if($tablecount>0)
    {
       print("<P><B>$dbbuttom_1_dbcheck_dbcheck_4 </B>");
       print(" <P><FORM NAME=\"UPDATEUSER\" ACTION=\"main.php\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"dbrepair\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"dbbuttom_1_dbcheck.php\">\n");
       print("<INPUT TYPE=\"HIDDEN\" NAME=\"tablecount\" value=\"$tablecount\">\n");

	   print("<TABLE WIDTH=\"90%\" BORDER=0> ");
	   print("<TR><TD WIDTH=\"50%\" ALIGN=\"CENTER\">\n");
	   print("<SELECT NAME=\"table[]\" MULTIPLE>\n");
	   for($i=0;$i<$tablecount;$i++)
          {
            print("<OPTION VALUE=\"$table[$i]\"> $table[$i]");
	      }
       print("</SELECT>\n");
	   print("<TD WIDTH=\"50%\" VALIGN=\"TOP\">\n");
       print("<BR>$dbbuttom_1_dbcheck_dbcheck_5");
       print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$dbbuttom_1_dbcheck_dbcheck_6\">\n");
    }


}


function dbbuttom_1_dbcheck()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=dbcheck&filename=dbbuttom_1_dbcheck.php","basefrm","dbcheck_32.jpg","dbcheck_48.jpg","$dbbuttom_1_dbcheck_dbbuttom_1_dbcheck_1");
	}

}




?>
