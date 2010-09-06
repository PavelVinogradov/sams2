<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
function MoveUsersToGroup()
{
 if(isset($_GET["groupname"])) $id=$_GET["groupname"];
 if(isset($_GET["username"])) $users=$_GET["username"];

  for($i=0;$i<count($users);$i++)
    {
           $result=mysql_query("UPDATE squidusers SET squidusers.group=\"$id\" WHERE squidusers.id=\"$users[$i]\"");
    }
  print("<SCRIPT>\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=usergroupform&groupname=$id&gid=ALL\";\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");
}

function DisableGroupUsers()
{
 
 if(isset($_GET["counter"])) $counter=$_GET["counter"];
 if(isset($_GET["groupname"])) $groupname=$_GET["groupname"];
 if(isset($_GET["users"])) $users=$_GET["users"];

  $result=mysql_query("SELECT * FROM squidusers WHERE squidusers.group=\"$groupname\"");
  while($row=mysql_fetch_array($result))
     {
       $id=$row[id];
       $enabled=-1;
       if($users[$id]=="on")
         $enabled=1;
       if($enabled==1&&$row['enabled']!=1)
         {
           //print("enabled user<BR>");
           $result2=mysql_query("UPDATE squidusers SET enabled=\"$enabled\" WHERE squidusers.id=\"$row[id]\"");
	 }
       if($enabled<=0&&$row['enabled']==1)
         {
           print("disabled user<BR>");
           $result2=mysql_query("UPDATE squidusers SET enabled=\"$enabled\" WHERE squidusers.id=\"$row[id]\"");
	 }
     }
     print("<SCRIPT>\n");
     print("        parent.lframe.location.href=\"lframe.php\";\n");
     print("        parent.tray.location.href=\"tray.php?show=usergrouptray&groupname=$groupname\";\n");
     print("</SCRIPT> \n");

}


function UserGroupForm()
{
  
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  $SAMSConf->access=UserAccess();

  if(isset($_GET["groupname"])) $groupname=$_GET["groupname"];
  if(isset($_GET["gid"])) $gid=$_GET["gid"];

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");
  $ga=0;
  $result=mysql_query("SELECT * FROM groups WHERE groups.name=\"$groupname\" ");
  $row=mysql_fetch_array($result);
  if($SAMSConf->groupauditor==$row['name'])
    {
      $ga=1;
    }
  PageTop("user.jpg","$grouptray_UserGroupForm_1.<BR>$grouptray_UserGroupForm_2 <FONT COLOR=\"blue\">$row[nick]</FONT>");

  if($SAMSConf->access==2)
    {
      print("<FORM NAME=\"groupform\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"disablegroupusers\">\n");
    } 
  
  print("<TABLE WIDTH=\"100%\" BORDER=0 CLASS=samstable>\n");

  if($SAMSConf->access>0)
    {
      print("<TH  WIDTH=\"10%\">\n");
      print(" <B>$grouptray_NewGroupForm_3 </B> \n");
    }
  print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$grouptray_NewGroupForm_4</B>\n");
  if($SAMSConf->access==2)
    {
      print("<TH WIDTH=\"10%\" bgcolor=beige> <B>$grouptray_NewGroupForm_5</B>\n");
    }   
  if($SAMSConf->access==2||$ga==1)
    {
      print("<TH WIDTH=\"15%\" bgcolor=beige ALIGN=CENTER> <B>$grouptray_NewGroupForm_6</B>\n");
      print("<TH WIDTH=\"15%\" bgcolor=beige ALIGN=CENTER> <B>$grouptray_NewGroupForm_7</B>\n");
    }  
  print("<TH WIDTH=\"40%\" bgcolor=beige> <B>$grouptray_NewGroupForm_8</B>\n");

  $count=0;
  $gtraffic=0;
  $gquote=0;
  $result=mysql_query("SELECT * FROM squidusers WHERE squidusers.group=\"$groupname\" ORDER BY nick");
  while($row=mysql_fetch_array($result))
      {
       print("<TR>\n");

       if($SAMSConf->realtraffic=="real")
 	 $traffic=$row['size']-$row['hit'];
       else
         $traffic=$row['size'];
       $gtraffic+=$traffic;
       $gquote+=$row['quotes']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE;
      
       if($row['enabled']>0)
         {
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
	 
	 print("<TD WIDTH=\"15%\"> <B><A HREF=\"tray.php?show=usertray&userid=$row[id]&usergroup=$row[group]\"  TARGET=\"tray\">$row[nick] </A></B>");
         if($SAMSConf->access==2)
           {
             print("<TD WIDTH=\"15%\"> <B>$row[domain] </B>");
	   }   
	 if($SAMSConf->access==2||$ga==1)
           {
	    if($SAMSConf->realtraffic=="real")
	     	PrintFormattedSize($row['size'] - $row['hit']);
	    else
		PrintFormattedSize($row['size']);
             
	     if($row['quotes']>0)
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER> $row[quotes] Mb");
	     else  
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER> unlimited ");
	   }
         print("<TD WIDTH=\"40%\"> $row[family] $row[name] $row[soname]");
	 
	 $count=$count+1;  
      }
      if($SAMSConf->access==2||$ga==1)
        {
          print("<TR><TD><TD><TD>");
          PrintFormattedSize($gtraffic);
          if($gquote!=0)
            PrintFormattedSize($gquote);
          else
            print("<TD ALIGN=\"CENTER\">unlimited");	
          print("<TD>\n");
	  print("</TR>\n");
        }
    print("</TABLE>\n");

    if($SAMSConf->access==2)
    {
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"counter\" value=\"$count\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" value=\"$groupname\">\n");
      print(" <INPUT TYPE=\"SUBMIT\" VALUE=\"$grouptray_NewGroupForm_9\" \n> ");
      
      print("</FORM>\n");
    } 

  if($SAMSConf->access==2)
    {
	print("<SCRIPT language=JAVASCRIPT>\n");
        print("function SelectUsers(id)\n");
        print("{\n");
        print("   var group = \"main.php?show=exe&function=usergroupform&groupname=$groupname&gid=\" +  id ; \n");
        print("   parent.basefrm.location.href=group;\n");
        print("}\n");
	print("</SCRIPT>\n");

      print("<P><B>$grouptray_NewGroupForm_10 $gname:</B> ");
      print("<FORM NAME=\"moveform\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"moveuserstogroup\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" value=\"$groupname\">\n");

      print("<SELECT NAME=\"groupid\" onchange=SelectUsers(moveform.groupid.value)>\n");
      $result=mysql_query("SELECT * FROM groups WHERE name!=\"$groupname\" ORDER BY nick");
      if($gid=="ALL")
        print("<OPTION VALUE=\"ALL\" SELECTED> ALL\n");
      else
        print("<OPTION VALUE=\"ALL\"> ALL\n");

      while($row=mysql_fetch_array($result))
         {
	    $SECTED="";
	    if($row['name']==$gid)
		$SECTED="SELECTED";
	    if($row['name']!=$id)
               print("<OPTION VALUE=\"$row[name]\" $SECTED> $row[nick]\n");
         }
      print("</SELECT>\n");

      print("<SELECT NAME=\"username[]\" SIZE=10 MULTIPLE>\n");
      if($gid=="ALL")
        $result=mysql_query("SELECT * FROM squidusers WHERE squidusers.group!=\"$groupname\" ORDER BY nick");
      else
	$result=mysql_query("SELECT * FROM squidusers WHERE squidusers.group=\"$gid\"&&squidusers.group!=\"$groupname\" ORDER BY nick ");
      while($row=mysql_fetch_array($result))
         {
            print("<OPTION VALUE=$row[id]> $row[nick]\n");
         }
      print("</SELECT>\n");
      print(" <P><INPUT TYPE=\"SUBMIT\" VALUE=\"$grouptray_NewGroupForm_11 '$gname'\" \n> ");
      print("</TABLE> ");
    } 


}


function GroupTray($groupname,$groupnick)
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  print("<SCRIPT>\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=usergroupform&groupname=$groupname&gid=ALL\";\n");
  print("</SCRIPT> \n");

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB);
  $result=mysql_query("SELECT * FROM groups WHERE name=\"$groupname\" ");
  $row=mysql_fetch_array($result);
  print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
  print("<TR>\n");
  print("<TD VALIGN=\"TOP\" WIDTH=\"30%\"\">");
  print("<B>$grouptray_GroupTray_1 <BR><FONT SIZE=\"+1\" COLOR=\"blue\">$row[nick]</FONT></B>\n");

      ExecuteFunctions("./src", "groupbuttom",$groupname);

  print("<TD>\n");
  print("</TABLE>\n");


}

?>
