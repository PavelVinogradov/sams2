<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ReconfigSquid()
{
  global $SAMSConf;
 
 if(isset($_GET["cache"])) $cache=$_GET["cache"];
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
       PageTop("reconfig_48.jpg","$squidbuttom_0_reconfig_ReconfigSquid_1");
       if($SAMSConf->PROXYCOUNT>1)
          {
            for($i=0;$i<=$SAMSConf->PROXYCOUNT;$i++)
	       {
	           if($cache[$i]=="on")
	              {
	                 //echo "remove cache $row[id] $row[description]<BR>";
                         $result=mysql_query("INSERT INTO reconfig SET number=\"$i\",service=\"squid\",action=\"reconfig\" ");
        	         for($j=0;$j<10;$j++)
        	           {
                	      $result=mysql_query("SELECT count(*) FROM reconfig WHERE  service=\"squid\"&&number=\"$i\"&&action=\"reconfig\" ");
                	      $row=mysql_fetch_array($result);
            	              if($row[0]==0)
                                {
	            	           $str="<FONT color=\"BLUE\" SIZE=+1> $squidbuttom_0_reconfig_ReconfigSquid_3</FONT><BR>\n";
                            	   break;
            	                 }
            	              else
                	         { 
	        	            $str="<FONT color=\"RED\" SIZE=+1>  $squidbuttom_0_reconfig_ReconfigSquid_4</FONT><BR>\n";
            	                 }
            	             sleep(1);
            	           }
        	          print("$str");
		        }
                }
          
          }
	else 
          {   	 
            $result=mysql_query("INSERT INTO reconfig SET service=\"squid\",action=\"reconfig\",number=\"0\" ");
            if($result!=FALSE)
               UpdateLog("$SAMSConf->adminname","Send request to reconfigure SQUID ","03");
            $result=mysql_query("SELECT * FROM sams");
            $row=mysql_fetch_array($result);

            if($row[' parser']=="analog")
              $result=mysql_query("INSERT INTO reconfig SET service=\"samsf\",action=\"reconfig\"");
            print("$squidbuttom_0_reconfig_ReconfigSquid__2<BR>\n");
            for($i=0;$i<10;$i++)
	        {
                  $result=mysql_query("SELECT count(*) FROM reconfig WHERE  service=\"squid\"&&action=\"reconfig\" ");
                  $row=mysql_fetch_array($result);
	          if($row[0]==0)
                    {
		              $str="<FONT color=\"BLUE\" SIZE=+1> $squidbuttom_0_reconfig_ReconfigSquid_3</FONT><BR>\n";
                              break;
	            }
	         else
                    {
		         $str="<FONT color=\"RED\" SIZE=+1> $squidbuttom_0_reconfig_ReconfigSquid_4</FONT><BR>\n";
	            }
	         sleep(1);
                }
            print("$str");
         }
      }
}

function ReconfigSquidForm()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  PageTop("reconfig_48.jpg","$squidbuttom_0_reconfig_ReconfigSquidForm_1 ");

  print("<FORM NAME=\"adddenied\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"reconfigsquid\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"squidbuttom_0_reconfig.php\">\n");
  print("<P>\n");
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
 
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$squidbuttom_0_reconfig_ReconfigSquidForm_2\">\n");
  print("</FORM>\n");

}


function squidbuttom_0_reconfig()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["id"])) $id=$_GET["id"];

   if($SAMSConf->access==2)
    {
       print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
       GraphButton("main.php?show=exe&function=reconfigsquidform&filename=squidbuttom_0_reconfig.php","basefrm","recsquid_32.jpg","recsquid_48.jpg","$squidbuttom_0_reconfig_squidbuttom_0_reconfig_1");
	}

}




?>
