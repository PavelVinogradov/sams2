<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
function DisableSelectedUsers()
{
 if(isset($_GET["counter"])) $counter=$_GET["counter"];
 if(isset($_GET["users"])) $users=$_GET["users"];
 if(isset($_GET["delete"])) $delete=$_GET["delete"];

  TestWI();
  print("found $counter users in group $groupname:<BR><BR>");
  print("delete=$delete<BR><BR>");
  
  $result=mysql_query("SELECT * FROM squidusers ");
  while($row=mysql_fetch_array($result))
     {
       $id=$row[id];
       $enabled=-1;
       $deleteuser=0;
       if($users[$id]=="on")
         $enabled=1;
       if($delete[$id]=="on")
         $deleteuser=1;
       print("user $row[nick] enabled=$users[$id] delete=$deleteuser<BR>");
       if($enabled==1&&$row['enabled']!=1)
         {
           print("enabled user<BR>");
           $result2=mysql_query("UPDATE squidusers SET enabled=\"$enabled\" WHERE squidusers.id=\"$row[id]\"");
	 }
       if($enabled<=0&&$row['enabled']==1)
         {
           print("disabled user<BR>");
           $result2=mysql_query("UPDATE squidusers SET enabled=\"$enabled\" WHERE squidusers.id=\"$row[id]\"");
	 }
       if($deleteuser==1)
         {
           print("delete user<BR>");
           $result2=mysql_query("DELETE FROM squidusers WHERE id=\"$row[id]\" ");
	 }
       
     }
     print("<SCRIPT>\n");
     print("        parent.lframe.location.href=\"lframe.php\";\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&function=AllUsersForm\";\n");
     print("</SCRIPT> \n");

}


function AllUsersForm()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
  TestWI();
  $SAMSConf->access=UserAccess();

  $groupname="";
  if(isset($_GET["groupname"])) $groupname=$_GET["groupname"];

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");
  $result=mysql_query("SELECT * FROM groups WHERE groups.name=\"$groupname\" ");
  $row=mysql_fetch_array($result);
  PageTop("user.jpg","$grouptray_UserGroupForm_1");

  if($SAMSConf->access==2)
    {
      print("<FORM NAME=\"groupform\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"disableselectedusers\">\n");
    } 
  
  print("<TABLE WIDTH=\"100%\" BORDER=0 CLASS=samstable>\n");
  print("<TR>\n");
  print("<TH  WIDTH=\"10%\">\n");
  if($SAMSConf->access==2)
    {
      print("<B>$userstray_AllUsersForm_1</B> \n");
    }
  print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$userstray_AllUsersForm_2</B>\n");
  print("<TH WIDTH=\"10%\" bgcolor=beige> <B>$userstray_AllUsersForm_3</B>\n");
  if($SAMSConf->access==2)
    {
      print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$userstray_AllUsersForm_4</B>\n");
      print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$userstray_AllUsersForm_5</B>\n");
      print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$userstray_AllUsersForm_9</B>\n");
    }  
  print("<TH WIDTH=\"30%\" bgcolor=beige> <B>$userstray_AllUsersForm_6</B>\n");
  if($SAMSConf->access==2)
    {
      print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$userstray_AllUsersForm_7</B>\n");
    }  
  $count=0;
  
 $result=mysql_query("SELECT squidusers.*,groups.nick AS gnick, shablons.period, year(shablons.clrdate) as year, month(shablons.clrdate) as month, dayofmonth(shablons.clrdate) as day FROM squidusers LEFT JOIN $SAMSConf->SAMSDB.groups ON groups.name=squidusers.group LEFT JOIN $SAMSConf->SAMSDB.shablons ON squidusers.shablon=shablons.name ORDER BY squidusers.group,squidusers.nick");
  
  while($row=mysql_fetch_array($result))
      {
        $clrdate="";
	$clrdays=0;
       print("<TR>\n");

       //if($)
       if($row['enabled']>0)
         {
	    if($SAMSConf->realtraffic=="real")
	        $traffic=$row['size']-$row['hit'];
            else
	        $traffic=$row['size'];
            if($row['quotes']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE>=$traffic||$row['quotes']<=0)
               $gif="puser.gif";
            else
               if($row['quotes']>0)
                  $gif="quote_alarm.gif";
          }
        if($row['enabled']==0)
          {
             $gif="puserd.gif";
          }
        if($row['enabled']<0)
          {
              $gif="duserd.gif";
           }
        if($SAMSConf->access>0)
           {
              print("<TD WIDTH=\"10%\">");
              print("<IMG SRC=\"$SAMSConf->ICONSET/$gif\">");
              
	   }
         if($SAMSConf->access==2)
           {
             print(" <INPUT TYPE=\"CHECKBOX\" NAME=users[$row[id]] ");
             if($row['enabled']==1)
	       print(" CHECKED ");
	     print("> \n ");
           }
	 
	 
	 print("<TD WIDTH=\"15%\"> <B>$row[1] </B>");
	 print("<TD WIDTH=\"15%\"> <B>$row[gnick] </B>");
             
         if($SAMSConf->access==2)
           {
	    if($SAMSConf->realtraffic=="real")
	        PrintFormattedSize($row['size']-$row['hit']);
            else
	        PrintFormattedSize($row['size']);

             
	     if($row['quotes']>0)
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER> $row[quotes] Mb");
	     else  
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER> unlimited ");
	   
	      if($row['period']!="M"&&$row['period']!="W")
                {
                  //$clrdays=round((mktime(0, 0, 0, $row['month'], $row['day'], $row['year'])-time())/86400);
                  //$year=$row['year']; 
		  $period="$row[period] $userstray_AllUsersForm_10";
	        }
	      if($row['period']=="M")
                {
                  //$month=date("m", time());
                  //$year=date("Y", time());
		  //$clrdays=round((mktime(0, 0, 0, $month+1, 1, $year)-time())/86400)-1;
		  $period="$userstray_AllUsersForm_11";
	        }
	      if($row['period']=="W")
                {
                  //$weekday=date("w", time());
                  //if($weekday==0)
		  //    $weekday=7;
		  //$clrdays=7-$weekday;
		  $period="$userstray_AllUsersForm_12";
	        }
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER> $period ");
	   }
	   
         print("<TD WIDTH=\"40%\"> $row[family] $row[name] $row[soname]");
         if($SAMSConf->access==2)
           {
              print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=delete[$row[id]]> \n");
	   }
	 $count=$count+1;  
      }
  print("</TABLE>\n");

    if($SAMSConf->access==2)
    {
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"counter\" value=\"$count\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" value=\"$groupname\">\n");
      print(" <INPUT TYPE=\"SUBMIT\" VALUE=\"$userstray_AllUsersForm_8\" \n> ");
      
      print("</FORM>\n");
    } 



}




function UsersTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();

  print("<SCRIPT>\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=AllUsersForm\";\n");
  print("</SCRIPT> \n");

  print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
  print("<TR>\n");
  print("<TD VALIGN=\"TOP\" WIDTH=\"30%\"\">");
  print("<B><FONT SIZE=\"+1\" COLOR=\"blue\">$userstray_UsersTray_1</FONT></B>\n");

  ExecuteFunctions("./src", "usersbuttom","1");
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
