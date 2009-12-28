<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function SaveBackUp()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");

  $tablecount=0;

  $filename=strftime("samsdb-%d%b%Y-%H-%M-%S.sql.gz");
//  if(is_dir("/tmp/sams")!=TRUE)
//      mkdir("/tmp/sams",0777);
  if(($fout=gzopen("data/$filename","w9"))!=NULL)
    {
       gzwrite($fout,"# ".$SAMSConf->SAMSDB." DUMP FOR MYSQL DATABASE\n");
       gzwrite($fout,"USE ".$SAMSConf->SAMSDB.";\n");
       $result=mysql_query("SHOW TABLES");
       while($row=mysql_fetch_array($result)) //берем список таблиц
         {
            $tablecount++;
            $columncount=0;
            $result2=mysql_query("SHOW COLUMNS FROM $row[0]"); //берем количество
            gzwrite($fout,"DROP TABLE IF EXISTS `$row[0]`;\n");
            gzwrite($fout,"CREATE TABLE `$row[0]` (");
            $count=0;
            while($row2=mysql_fetch_array($result2))           //столбцов
              {
                 $count++;
	      }
            $result2=mysql_query("SHOW COLUMNS FROM $row[0]"); //берем количество
            while($row2=mysql_fetch_array($result2))           //столбцов
              {
                 gzwrite($fout," `$row2[0]` $row2[1]");
                 if($columncount<($count-1))
                   gzwrite($fout,",");
                 //gzwrite($fout,"\n");
                 $columncount++;
	      }
            gzwrite($fout,") TYPE=MyISAM;\n");
            $result3=mysql_query("SELECT * FROM $row[0]");
            while($row3=mysql_fetch_array($result3))
                {
                   gzwrite($fout," INSERT INTO $row[0] VALUES(");
                   for($i=0;$i<$columncount;$i++)
                      {
		        gzwrite($fout,"'$row3[$i]'");
		        if($i<$columncount-1)
		        gzwrite($fout,",");

		      }
                   gzwrite($fout,");\n");
	        }
         }
       gzclose($fout);
    }   
  else if(($fout=fopen("data/$filename","w9"))!=NULL)
    {
       fwrite($fout,"# ".$SAMSConf->SAMSDB." DUMP FOR MYSQL DATABASE\n");
       fwrite($fout,"USE ".$SAMSConf->SAMSDB.";\n");
       $result=mysql_query("SHOW TABLES");
       while($row=mysql_fetch_array($result)) //берем список таблиц
         {
            $tablecount++;
            $columncount=0;
            $result2=mysql_query("SHOW COLUMNS FROM $row[0]"); //берем количество
            fwrite($fout,"DROP TABLE IF EXISTS `$row[0]`;\n");
            fwrite($fout,"CREATE TABLE `$row[0]` (");
            $count=0;
            while($row2=mysql_fetch_array($result2))           //столбцов
              {
                 $count++;
	      }
            $result2=mysql_query("SHOW COLUMNS FROM $row[0]"); //берем количество
            while($row2=mysql_fetch_array($result2))           //столбцов
              {
                 fwrite($fout," `$row2[0]` $row2[1]");
                 if($columncount<($count-1))
                   fwrite($fout,",");
                 //gzwrite($fout,"\n");
                 $columncount++;
	      }
            fwrite($fout,") TYPE=MyISAM;\n");
            $result3=mysql_query("SELECT * FROM $row[0]");
            while($row3=mysql_fetch_array($result3))
                {
                   fwrite($fout," INSERT INTO $row[0] VALUES(");
                   for($i=0;$i<$columncount;$i++)
                      {
		        fwrite($fout,"'$row3[$i]'");
		        if($i<$columncount-1)
		          fwrite($fout,",");

		      }
                   fwrite($fout,");\n");
	        }
         }
       fclose($fout);
    }   

  
  print("<P><A HREF=\"data/$filename\">\n");
  print("<BR>$backupbuttom_1_savebase_SaveBackUp_1\n");
  print("</A>\n");

  return("$filename");
}


function SaveBackUpForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("backup_48.jpg","$backupbuttom_1_savebase_SaveBackUpForm_1");
  print("<BR>\n");
  print("<FORM NAME=\"BACKUP\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"savebackup\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"backupbuttom_1_savebase.php\">\n");
  print("<BR>$backupbuttom_1_savebase_SaveBackUpForm_2\n");
  print("<TABLE>\n");
  print("</TABLE>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$backupbuttom_1_savebase_SaveBackUpForm_3\">\n");
  print("</FORM>\n");
}


function backupbuttom_1_savebase()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=savebackupform&filename=backupbuttom_1_savebase.php","basefrm","savebase_32.jpg","savebase_48.jpg","$backupbuttom_1_savebase_backupbuttom_1_savebase_1");
	}

}




?>
