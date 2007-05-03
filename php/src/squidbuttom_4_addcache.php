<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */


function RemoveCache()
{
  global $SAMSConf;
 if(isset($_GET["cache"])) $cache=$_GET["cache"];
  
  $result=mysql_query("SELECT * FROM proxyes ");
  while($row=mysql_fetch_array($result))
     {
        $id=$row['id'];
	if($cache[$id]=="on")
	  {
	    //echo "remove cache $row[id] $row[description]<BR>";
            $result2=mysql_query("DELETE FROM $SAMSConf->SAMSDB.proxyes WHERE id=\"$id\" ");
          }
    }
  print("<SCRIPT>\n");
  print("  parent.basefrm.location.href=\"main.php?show=exe&function=cacheform&filename=squidbuttom_4_addcache.php\"; \n");
  print("</SCRIPT> \n");
}


function AddCache()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
 
  if(isset($_GET["description"])) $description=$_GET["description"];

  $userid=TempName();

  db_connect($SAMSConf->SAMSDB) or exit();
    mysql_select_db($SAMSConf->SAMSDB);
    
      $result=mysql_query("SELECT MAX(id) FROM proxyes ");
      $row=mysql_fetch_array($result);
      $id=$row[0]+1;
      $result=mysql_query("INSERT INTO proxyes SET id=\"$id\", description=\"$description\" ");
     //if($result!=FALSE)
     //    UpdateLog("$SAMSConf->adminname","Added SQUID-cache $description ","01");
  print("<SCRIPT>\n");
  print("  parent.basefrm.location.href=\"main.php?show=exe&function=cacheform&filename=squidbuttom_4_addcache.php\"; \n");
  print("</SCRIPT> \n");
}

 
 
function CacheForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();
  if($SAMSConf->access==2)
    {
      PageTop("proxyes_48.jpg","$CacheForm_squidbuttom_4_addcache_1");
      //print("<H2>$CacheForm_squidbuttom_4_addcache_1</H2>\n");
      print("<FORM NAME=\"cacheform\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"removecache\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"squidbuttom_4_addcache.php\">\n");
      print("<TABLE CLASS=samstable WIDTH=\"80%\">");
      print("<TH width=20%>$CacheForm_squidbuttom_4_addcache_2");
      print("<TH width=60%>$CacheForm_squidbuttom_4_addcache_3");
      print("<TH width=20%>$CacheForm_squidbuttom_4_addcache_4");
      $result=mysql_query("SELECT id,description FROM $SAMSConf->SAMSDB.proxyes ORDER BY id");
       while($row=mysql_fetch_array($result))
           {
             print("<TR><TD>$row[id]<TD> $row[description]");
              print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=cache[$row[id]]> \n");
           }
      print("</TABLE>");
      print("<INPUT TYPE=\"SUBMIT\" value=\"$CacheForm_squidbuttom_4_addcache_5\" >\n");
      print("</FORM>\n");
  
      print("<P>\n");
      print("<H2>$CacheForm_squidbuttom_4_addcache_6</H2>\n");
      print("<FORM NAME=\"ADDCACHE\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addcache\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"squidbuttom_4_addcache.php\">\n");
      print("<B>$CacheForm_squidbuttom_4_addcache_7:</B>\n");
      print("<INPUT TYPE=\"TEXT\" NAME=\"description\" SIZE=30> \n");
      print("<INPUT TYPE=\"SUBMIT\" value=\"$CacheForm_squidbuttom_4_addcache_8\" >\n");
      print("</FORM>\n");
     
    
    }
}

 
function squidbuttom_4_addcache()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);


   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=cacheform&filename=squidbuttom_4_addcache.php","basefrm","proxyes_32.jpg","proxyes_48.jpg","$squidbuttom_4_addcache_squidbuttom_4_addcache_1");
	}

}




?>
