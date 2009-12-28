<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
function DisableSelectedUsers()
{
  global $SAMSConf;
  if($SAMSConf->access!=2)
    exit(0);

 if(isset($_GET["counter"])) $counter=$_GET["counter"];
 if(isset($_GET["disable"])) $disable=$_GET["disable"];
 if(isset($_GET["defen"])) $defen=$_GET["defen"];
 if(isset($_GET["delete"])) $delete=$_GET["delete"];
 if(isset($_GET["discount"])) $discount=$_GET["discount"];
 if(isset($_GET["delcount"])) $delcount=$_GET["delcount"];


 $disable=explode(",",$disable);
 $count1=count($disable);
 $defen=explode(",",$defen);
 $count3=count($defen);
 $delete=explode(",",$delete);
 $count2=count($delete);

 for($i=0; $i<$count1; $i++)
    {
       if($SAMSConf->LOGLEVEL >= 3&&strlen($disable[$i])>0)
         {
            $result=mysql_query("SELECT nick FROM $SAMSConf->SAMSDB.squidusers WHERE id=\"$disable[$i]\" ");
            $row=mysql_fetch_array($result);
            UpdateLog("$SAMSConf->adminname","Deactivate user $row[nick]","01");
         }
       $result2=mysql_query("UPDATE squidusers SET enabled=\"-1\" WHERE squidusers.id=\"$disable[$i]\"");
   }
 for($i=0; $i<$count3; $i++)
    {
       if($SAMSConf->LOGLEVEL >= 3&&strlen($defen[$i])>0)
         {
             $result=mysql_query("SELECT nick FROM $SAMSConf->SAMSDB.squidusers WHERE id=\"$defen[$i]\" ");
             $row=mysql_fetch_array($result);
            UpdateLog("$SAMSConf->adminname","Activate user $row[nick]","01");
         }
       $result2=mysql_query("UPDATE squidusers SET enabled=\"1\" WHERE squidusers.id=\"$defen[$i]\"");
    }
 for($i=0; $i<$count2; $i++)
    {
       if($SAMSConf->LOGLEVEL >= 3&&strlen($delete[$i])>0)
         {
             $result=mysql_query("SELECT nick FROM $SAMSConf->SAMSDB.squidusers WHERE id=\"$delete[$i]\" ");
             $row=mysql_fetch_array($result);
            UpdateLog("$SAMSConf->adminname","Delete user $row[nick] ","01");
         }
        $result2=mysql_query("DELETE FROM squidusers WHERE id=\"$delete[$i]\" ");
    }
     print("<SCRIPT>\n");
     print("        parent.lframe.location.href=\"lframe.php\";\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&function=AllUsersForm&type=all\";\n");
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
  $type="all";
  if(isset($_GET["type"])) $type=$_GET["type"];
  if(isset($_GET["username"])) $username=$_GET["username"];

  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");

  PageTop("user.jpg","$grouptray_UserGroupForm_1");
  if($SAMSConf->access==2)
    {
      print("<FORM NAME=\"searchform\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"AllUsersForm\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"type\" value=\"search\">\n");
      print("$userstray_AllUsersForm_13 \n");
      print("<INPUT TYPE=\"TEXT\" NAME=\"username\" >\n");
      print("<INPUT TYPE=\"SUBMIT\" VALUE=\"Search\" >\n");
      print("</FORM>\n");
//show=exe&function=AllUsersForm&type=all
    } 

  print("</TABLE>\n");
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function SendForm(formname)\n");
       print("{\n");
       print("   var disable = new Array(); \n");
       print("   var defen = new Array(); \n");
       print("   var userdel = new Array(); \n");
       print("   var discount=0; \n");
       print("   var defcount=0; \n");
       print("   var delcount=0; \n");
       print("   if (groupform.counter.value == 1) \n");
       print("       {\n");
       print("           if(groupform.users.checked==false && groupform.dusers.value==\"1\")\n");
       print("             {\n");
       print("                  disable[discount] = groupform.users.value; \n");
       print("                  discount+=1; \n");
       print("             }\n");
       print("           if(groupform.users.checked==true && ( groupform.dusers.value==\"-1\" || groupform.dusers.value==\"0\" ))\n");
       print("             {\n");
       print("                  defen[defcount] = groupform.users.value; \n");
       print("                  defcount+=1; \n");
       print("             }\n");
       print("           if(groupform.userdel.checked==true)\n");
       print("             {\n");
       print("                  userdel[delcount] = groupform.userdel.value; \n");
       print("                  delcount+=1; \n");
       print("             }\n");
       print("       }\n");
       print("   else \n");
       print("       {\n");
       print("           for(var i=0; i < groupform.counter.value; i +=1 ) \n");
       print("               {\n");
       print("                   if(groupform.users[i].checked==false && groupform.dusers[i].value==\"1\")\n");
       print("                     {\n");
       print("                          disable[discount] = groupform.users[i].value; \n");
       print("                          discount+=1; \n");
       print("                     }\n");
       print("                   if(groupform.users[i].checked==true && ( groupform.dusers[i].value==\"-1\" || groupform.dusers[i].value==\"0\" ))\n");
       print("                     {\n");
       print("                           defen[defcount] = groupform.users[i].value; \n");
       print("                           defcount+=1; \n");
       print("                     }\n");
       print("                   if(groupform.userdel[i].checked==true)\n");
       print("                     {\n");
       print("                           userdel[delcount] = groupform.userdel[i].value; \n");
       print("                           delcount+=1; \n");
       print("                     }\n");
       print("                }\n");
       print("       }\n");
       print("   var strr= \"main.php?show=exe&function=disableselectedusers&disable=\" + disable + \"&delete=\" + userdel + \"&defen=\" + defen + \"&delcount=\"+delcount+\"&discount=\"+discount  \n");
//       print("   var value=window.confirm( strr );\n");
       print("   parent.basefrm.location.href=strr;\n");
       print("}\n");
       print("</SCRIPT> \n");

  if($SAMSConf->access==2)
    {
      print("<FORM NAME=\"groupform\" ACTION=\"main.php\"  METHOD=\"post\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"disableselectedusers\">\n");
      print(" <INPUT TYPE=\"BUTTON\" VALUE=\"$userstray_AllUsersForm_8\" onclick=SendForm(groupform) > \n");
   } 
  
  print("<TABLE WIDTH=\"100%\" BORDER=0 CLASS=samstable>\n");
  print("<TR>\n");

  if($SAMSConf->access>0)
    {
      print("<TH  WIDTH=\"10%\">\n");
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
  
  if($type=="search")
  {
    $result=mysql_query("SELECT squidusers.*,groups.nick AS gnick, shablons.period, year(shablons.clrdate) as year, month(shablons.clrdate) as month, dayofmonth(shablons.clrdate) as day FROM squidusers LEFT JOIN $SAMSConf->SAMSDB.groups ON groups.name=squidusers.group LEFT JOIN $SAMSConf->SAMSDB.shablons ON squidusers.shablon=shablons.name WHERE squidusers.nick like \"%$username%\" ORDER BY squidusers.group,squidusers.nick");
  }
  else
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
             print(" <INPUT TYPE=\"CHECKBOX\" NAME=\"users\" ID=\"$count\" VALUE=\"$row[id]\" ");
             if($row['enabled']==1)
	       print(" CHECKED ");
	     print("> \n ");
             print(" <INPUT TYPE=\"HIDDEN\" NAME=\"dusers\" ID=\"$count\" VALUE=\"$row[enabled]\" >");
           }

	 print("<TD WIDTH=\"15%\"> <B><A HREF=\"tray.php?show=usertray&userid=$row[id]&usergroup=$row[group]\"  TARGET=\"tray\">$row[1] </A></B>");
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
		  $period="$row[period] $userstray_AllUsersForm_10";
	        }
	      if($row['period']=="M")
                {
		  $period="$userstray_AllUsersForm_11";
	        }
	      if($row['period']=="W")
                {
		  $period="$userstray_AllUsersForm_12";
	        }
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER> $period ");
	   }
	   
         print("<TD WIDTH=\"40%\"> $row[family] $row[name] $row[soname]");
         if($SAMSConf->access==2)
           {
              print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"userdel\" ID=\"$count\" VALUE=\"$row[id]\" > \n");
	   }
	 $count=$count+1;  
      }
  print("</TABLE>\n");

    if($SAMSConf->access==2)
    {
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"counter\" value=\"$count\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" value=\"$groupname\">\n");
      print(" <INPUT TYPE=\"BUTTON\" VALUE=\"$userstray_AllUsersForm_8\" onclick=SendForm(groupform) > \n");
      
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
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=AllUsersForm&type=all\";\n");
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
