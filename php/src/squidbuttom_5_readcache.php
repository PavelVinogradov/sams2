<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function LoadFileForm()
{
  global $SAMSConf;

   if(isset($_GET["id"])) $id=$_GET["id"];
   if(isset($_GET["name"])) $filename=$_GET["name"];
   for($i=0;$i<10;$i++)
      { 
                $result=mysql_query("SELECT count(*) FROM reconfig WHERE  service=\"squid\"&&action=\"loadfile\" ");
                  $row=mysql_fetch_array($result);
	          if($row[0]==0)
                    {
		        $str="<FONT color=\"BLUE\" SIZE=+1> $squidbuttom_5_readcache_LoadFileForm_3</FONT><BR>\n";
			$fileloaded=1;
                        break;

	            }
  	  //sleep(1);
	}
	if($fileloaded==0)
		printf("<FONT COLOR=\"RED\">$squidbuttom_5_readcache_LoadFileForm_4<BR>$squidbuttom_5_readcache_LoadFileForm_5<BR></FONT>");
	else
  	   {
		printf("$squidbuttom_5_readcache_LoadFileForm_6 $SAMSConf->SAMSPATH/share/sams/data<BR>");
	   }

	$scount=0;
	if ($handle2 = opendir("./data"))
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
		for($i=0;$i<$scount;$i++)
	    	   {
			$filesize=filesize("./data/$script[$i]");
			print("<TR>\n");
			print("<TD WIDTH=\"10%\" ALIGN=\"CENTER\">$i ");
			print("<TD WIDTH=\"70%\" ALIGN=\"LEFT\">");
			print("<B><A HREF=\"data/$script[$i]\">$script[$i]</A></B>\n");
			print("<TD WIDTH=\"20%\" ALIGN=\"CENTER\"> $filesize");
            	   }
		print("</TABLE>");
             }
//sleep(5);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=readcachesquidform&filename=squidbuttom_5_readcache.php\";\n");
      print("</SCRIPT> \n");
		  	     
}


function LoadFile()
{
  global $SAMSConf;

   if(isset($_GET["id"])) $id=$_GET["id"];
   if(isset($_GET["name"])) $filename=$_GET["name"];

   $result=mysql_query("INSERT INTO reconfig SET number=\"$id\",service=\"squid\",action=\"loadfile\",value=\"$filename\" ");
  printf("<SCRIPT LANGUAGE=\"javascript\">\n");
  printf("{\n");
  printf("document.location='main.php?show=exe&function=loadfileform&filename=squidbuttom_5_readcache.php&id=$id&name=$filename'};\n");
  printf("</SCRIPT>\n");

}

function ReadCacheFilesList()
{
  global $SAMSConf;
  
  printf("<SCRIPT LANGUAGE=\"javascript\">\n");
  printf("function Refr() \n");
  printf("{\n");
  printf("document.location='main.php?show=exe&function=readcachefileslist&filename=squidbuttom_5_readcache.php'};\n");
  printf("setTimeout('Refr();',5000);\n");
  printf("</SCRIPT>\n");
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
$today = getdate();
print_r($today);
  $result=mysql_query("SELECT count(*) FROM reconfig WHERE  service=\"squid\"&&action=\"cachescan\" ");
  $row=mysql_fetch_array($result);
  if($row[0]>0)
    echo "<h2>$squidbuttom_5_readcache_ReadCacheFilesList_1</h2>";
  $result=mysql_query("SELECT count(*) FROM reconfig WHERE  service=\"squid\"&&action=\"scanoff\" ");
  $row=mysql_fetch_array($result);
  if($row[0]>0)
   {
     echo "<h2>$squidbuttom_5_readcache_ReadCacheFilesList_2</h2>";
     $result=mysql_query("DELETE FROM reconfig WHERE  service=\"squid\"&&action=\"scanoff\" ");
     print("<SCRIPT>\n");
     print("parent.basefrm.location.href=\"main.php?show=exe&function=readcachesquidform&filename=squidbuttom_5_readcache.php\";\n");
     print("</SCRIPT> \n");
  }

}


function ReadCacheFiles()
{
  global $SAMSConf;
 

 if(isset($_GET["cache"])) $cache=$_GET["cache"];
 if(isset($_GET["size"])) $size=$_GET["size"];
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
       PageTop("reconfig_48.jpg","$squidbuttom_5_readcache_ReadCacheFiles_1");
       if($SAMSConf->PROXYCOUNT>1)
          {
            for($i=0;$i<$SAMSConf->PROXYCOUNT;$i++)
	       {
	           if($cache[$i]=="on")
	              {
	                 //echo "remove cache $row[id] $row[description]<BR>";
                         $result=mysql_query("DELETE FROM ".$SAMSConf->LOGDB.".files  WHERE id=\"$i\" ");
                         $result=mysql_query("INSERT INTO reconfig SET number=\"$i\",service=\"squid\",action=\"files\",value=\"$size\" ");
              print("$squidbuttom_5_readcache_ReadCacheFiles_2<BR>\n");
            for($i=0;$i<10;$i++)
	        {
                  $result=mysql_query("SELECT count(*) FROM reconfig WHERE  service=\"squid\"&&action=\"files\" ");
                  $row=mysql_fetch_array($result);
	          if($row[0]==0)
                    {
		        $str="<FONT color=\"BLUE\" SIZE=+1>$squidbuttom_5_readcache_ReadCacheFiles_1</FONT><BR>\n";
                        print("<SCRIPT>\n");
                        print("        parent.basefrm.location.href=\"main.php?show=exe&function=readcachefileslist&filename=squidbuttom_5_readcache.php\";\n");
                        print("</SCRIPT> \n");
                        break;
	            }
	         else
                    {
		         $str="<FONT color=\"RED\" SIZE=+1> $squidbuttom_5_readcache_ReadCacheFiles_4</FONT><BR>\n";
	            }
	         sleep(1);
                }
            print("$str");
		      }
                }
          
          }
	else 
          {   	 
            $result=mysql_query("DELETE FROM ".$SAMSConf->LOGDB.".files  ");
            $result=mysql_query("INSERT INTO reconfig SET service=\"squid\",action=\"files\",number=\"0\",value=\"$size\" ");
           // if($result!=FALSE)
           //    UpdateLog("$SAMSConf->adminname","Send request to reconfigure SQUID ","03");
            //$result=mysql_query("SELECT * FROM sams");
            //$row=mysql_fetch_array($result);

            print("$squidbuttom_5_readcache_ReadCacheFiles_2<BR>\n");
            for($i=0;$i<10;$i++)
	        {
                  $result=mysql_query("SELECT count(*) FROM reconfig WHERE  service=\"squid\"&&action=\"files\" ");
                  $row=mysql_fetch_array($result);
	          if($row[0]==0)
                    {
		        $str="<FONT color=\"BLUE\" SIZE=+1>$squidbuttom_5_readcache_ReadCacheFiles_3</FONT><BR>\n";
                        print("<SCRIPT>\n");
                        print("        parent.basefrm.location.href=\"main.php?show=exe&function=readcachefileslist&filename=squidbuttom_5_readcache.php\";\n");
                        print("</SCRIPT> \n");
                        break;
	            }
	         else
                    {
		         $str="<FONT color=\"RED\" SIZE=+1> $squidbuttom_5_readcache_ReadCacheFiles_4</FONT><BR>\n";
	            }
	         sleep(1);
                }
            print("$str");
         }
      }
}

function ReadCacheSquidForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("reconfig_48.jpg","$squidbuttom_5_readcache_ReadCacheSquidForm_1 ");

  print("<FORM NAME=\"adddenied\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"readcachefiles\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"squidbuttom_5_readcache.php\">\n");
  print("<P>\n");
  print("$squidbuttom_5_readcache_ReadCacheSquidForm_2 <INPUT TYPE=\"TEXT\" NAME=\"size\" value=\"1024\" SIZE=7> kb\n");
   if($SAMSConf->PROXYCOUNT>1)
    {
      print("<TABLE CLASS=samstable WIDTH=\"80%\">");
      print("<TH width=60%>$squidbuttom_0_reconfig_ReconfigSquidForm_3");
      print("<TH width=20%>$squidbuttom_0_reconfig_ReconfigSquidForm_4");
      $result=mysql_query("SELECT id,description FROM ".$SAMSConf->SAMSDB.".proxyes ORDER BY id");
       while($row=mysql_fetch_array($result))
           {
             print("<TR><TD> $row[description]");
              print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=cache[$row[id]]> \n");
           }
      print("</TABLE>");
    
    }
 
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$squidbuttom_5_readcache_ReadCacheSquidForm_3\">\n");
  print("</FORM>\n");

       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function LoadFile(filename,id,url)\n");
       print("{\n");
//       print("  var path=\"main.php?show=exe&function=loadfile&filename=squidbuttom_5_readcache.php&&id=\" + id + \"&&filename=\" + filename \n");
       print("  var path=\"main.php?show=exe&function=loadfile&filename=squidbuttom_5_readcache.php&id=\" + id + \"&name=\" + filename  \n");
       print("  var txt=\"Load file \"+ filename + \" ? \\nURL: \" + url \n");
       print("  value=window.confirm(txt );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=path;\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

      print("<TABLE CLASS=samstable>\n");
      //print("<TR>");
      print("<THEAD>");
      print("<TH>No");
      print("<TH>CACHE");
      print("<TH>FILE");
      print("<TH>SIZE");
      print("<TH>FILEPATH");
      print("</THEAD>");
      $count=0;
      $result=mysql_query("SELECT files.id,files.url,files.size,filepath FROM ".$SAMSConf->LOGDB.".files ORDER BY size DESC limit 10000");
      while($row=mysql_fetch_array($result))
         {
             print("\n<TR>");
             LTableCell($count,8);
             
             LTableCell($row['id'],15);
	     
             print("<TD onclick=LoadFile(\"$row[filepath]\",\"$row[id]\",\"$row[url]\")> $row[url] ");
             $aaa=FormattedString("$row[size]");
             RTableCell($aaa,20);
             print("<TD onclick=LoadFile(\"$row[filepath]\",\"$row[id]\",\"$row[url]\")> $row[filepath] ");
             $count=$count+1;
         }
      mysql_free_result($result);

      print("</TABLE>");

	$scount=0;
	if ($handle2 = opendir("./data"))
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
                 print("<H3>$squidbuttom_6_readcache_ReadCacheSquidForm_4 $SAMSConf->SAMSPATH/share/sams/data $squidbuttom_6_readcache_ReadCacheSquidForm_5</H3>");
                 print("<P><TABLE border=0 WIDTH=\"60%\">\n");
                 print("<TH >N ");
                 print("<TH >Filename");
                 print("<TH >Size");
		for($i=0;$i<$scount;$i++)
	    	   {
			$filesize=filesize("./data/$script[$i]");
			print("<TR>\n");
			print("<TD WIDTH=\"10%\" ALIGN=\"CENTER\">$i ");
			print("<TD WIDTH=\"70%\" ALIGN=\"LEFT\">");
			print("<B><A HREF=\"data/$script[$i]\">$script[$i]</A></B>\n");
			print("<TD WIDTH=\"20%\" ALIGN=\"CENTER\"> $filesize");
            	   }
		print("</TABLE>");
             }

}


function squidbuttom_5_readcache()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=readcachesquidform&filename=squidbuttom_5_readcache.php","basefrm","cache_32.jpg","cache_48.jpg","$squidbuttom_5_readcache_squidbuttom_5_readcache_1");
	}

}




?>
