<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
function DisableSelectedUsers()
{
  global $SAMSConf;
  $DB=new SAMSDB($SAMSConf->DB_ENGINE, $SAMSConf->ODBC, $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB);
  if($SAMSConf->access!=2)
    exit(0);

 if(isset($_GET["counter"])) $counter=$_GET["counter"];
 if(isset($_GET["disable"])) $disable=$_GET["disable"];
 if(isset($_GET["defen"])) $defen=$_GET["defen"];
 if(isset($_GET["delete"])) $delete=$_GET["delete"];
 if(isset($_GET["discount"])) $discount=$_GET["discount"];
 if(isset($_GET["delcount"])) $delcount=$_GET["delcount"];
 if(isset($_GET["defcount"])) $defcount=$_GET["defcount"];


 $disable=explode(",",$disable);
 $count1=count($disable);
 $defen=explode(",",$defen);
 $count3=count($defen);
 $delete=explode(",",$delete);
 $count2=count($delete);

 for($i=0; $i<$discount; $i++)
    {
//       if($SAMSConf->LOGLEVEL >= 3&&strlen($disable[$i])>0)
//         {
//            $num_rows=$DB->samsdb_query_value("SELECT s_nick FROM squiduser WHERE s_user_id='$disable[$i]' ");
//            $row=$DB->samsdb_fetch_array();
//            UpdateLog("$SAMSConf->adminname","Deactivate user $row[nick]","01");
//         }
//echo "UPDATE squiduser SET s_enabled='-1' WHERE s_user_id='$disable[$i]' count1=$count1  discount=$discount<BR>";
       $num_rows=$DB->samsdb_query("UPDATE squiduser SET s_enabled='-1' WHERE s_user_id='$disable[$i]'");
   }
 for($i=0; $i<$defcount; $i++)
    {
//       if($SAMSConf->LOGLEVEL >= 3&&strlen($defen[$i])>0)
//         {
//             $num_rows=$DB->samsdb_query_value("SELECT s_nick FROM squiduser WHERE s_user_id='$defen[$i]' ");
//            $row=$DB->samsdb_fetch_array();
//            UpdateLog("$SAMSConf->adminname","Activate user $row[nick]","01");
//         }
//echo "UPDATE squiduser SET s_enabled='1' WHERE s_user_id='$defen[$i]'<BR>";
       $num_rows=$DB->samsdb_query("UPDATE squiduser SET s_enabled='1' WHERE s_user_id='$defen[$i]'");
    }
 for($i=0; $i<$delcount; $i++)
    {
//       if($SAMSConf->LOGLEVEL >= 3&&strlen($delete[$i])>0)
//         {
//             $num_rows=$DB->samsdb_query_value("SELECT s_nick FROM squiduser WHERE s_user_id='$delete[$i]' ");
//            $row=$DB->samsdb_fetch_array();
//            UpdateLog("$SAMSConf->adminname","Delete user $row[nick] ","01");
//         }
//echo "DELETE FROM squiduser WHERE s_user_id='$delete[$i]'<BR>";
        $num_rows=$DB->samsdb_query("DELETE FROM squiduser WHERE s_user_id='$delete[$i]' ");
    }
     print("<SCRIPT>\n");
     print("        parent.lframe.location.href=\"lframe.php\";\n");
     print("        parent.basefrm.location.href=\"main.php?show=exe&filename=userstray.php&function=AllUsersForm&type=all\";\n");
     print("</SCRIPT> \n");

}


function AllUsersForm()
{
  global $SAMSConf;
  $DB=new SAMSDB($SAMSConf->DB_ENGINE, $SAMSConf->ODBC, $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB);
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
//  TestWI();
  $SAMSConf->access=UserAccess();

  $groupname="";
  $type="all";
  if(isset($_GET["type"])) $type=$_GET["type"];
  if(isset($_GET["username"])) $username=$_GET["username"];

  PageTop("user.jpg","$grouptray_UserGroupForm_1");
  if($SAMSConf->access==2)
    {
      print("<FORM NAME=\"searchform\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"userstray.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"allusersform\">\n");
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
       print("   var dis = \" \"; \n");
       print("   var def = \" \"; \n");
       print("   var del = \" \"; \n");
       print("   for(var i=0; i < groupform.counter.value; i +=1 ) \n");
       print("       {\n");
       print("           if(groupform.users[i].checked==false && groupform.dusers[i].value==\"1\")\n");
       print("             {\n");
       print("                  disable[discount] = groupform.users[i].value; \n");
       print("                  discount+=1; \n");
       print("                  dis = dis + groupform.users[i].value + \" \"; \n");
       print("             }\n");
       print("           if(groupform.users[i].checked==true && ( groupform.dusers[i].value==\"-1\" || groupform.dusers[i].value==\"0\" ))\n");
       print("             {\n");
       print("                  defen[defcount] = groupform.users[i].value; \n");
       print("                  defcount+=1; \n");
       print("                  def = def + groupform.users[i].value + \" \"; \n");
       print("             }\n");
      print("           if(groupform.userdel[i].checked==true)\n");
       print("             {\n");
       print("                  userdel[delcount] = groupform.userdel[i].value; \n");
       print("                  delcount+=1; \n");
       print("                  del = del + groupform.userdel[i].value + \" \"; \n");
       print("             }\n");
       print("        }\n");
       print("   var strr= \"main.php?show=exe&filename=userstray.php&function=disableselectedusers&disable=\" + disable + \"&delete=\" + userdel + \"&defen=\" + defen + \"&delcount=\"+delcount+\"&discount=\"+discount + \"&defcount=\"+defcount  \n");
//       print("   var value=window.confirm( \"disable:\" + dis + \" delete: \" + del + \"default:\" + def );\n");
       print("   parent.basefrm.location.href=strr;\n");
       print("}\n");
       print("function EnableAll(formname)\n");
       print("{\n");
       print("   for(var i=0; i < groupform.counter.value; i +=1 ) \n");
       print("       {\n");
       print("           if(groupform.users[i].checked==false )\n");
       print("             {\n");
       print("                  groupform.users[i].checked=true; \n");
       print("             }\n");
       print("        }\n");
       print("}\n");
       print("function DisableAll(formname)\n");
       print("{\n");
       print("   for(var i=0; i < groupform.counter.value; i +=1 ) \n");
       print("       {\n");
       print("           if(groupform.users[i].checked==true )\n");
       print("             {\n");
       print("                  groupform.users[i].checked=false; \n");
       print("             }\n");
       print("        }\n");
       print("}\n");
       print("function DeleteAll(formname)\n");
       print("{\n");
       print("   for(var i=0; i < groupform.counter.value; i +=1 ) \n");
       print("       {\n");
       print("           if(groupform.userdel[i].checked==false )\n");
       print("             {\n");
       print("                  groupform.userdel[i].checked=true; \n");
       print("             }\n");
       print("        }\n");
       print("}\n");
       print("</SCRIPT> \n");

  if($SAMSConf->access==2)
    {
      print("<FORM NAME=\"groupform\" ACTION=\"main.php\"  METHOD=\"post\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"userstray.php\">\n");
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
    $num_rows=$DB->samsdb_query_value("SELECT squiduser.*,sgroup.s_name AS gnick, shablon.s_period, shablon.s_clrdate FROM squiduser LEFT JOIN sgroup ON sgroup.s_group_id=squiduser.s_group_id LEFT JOIN shablon ON squiduser.s_shablon_id=shablon.s_shablon_id WHERE squiduser.s_nick like '%$username%' ORDER BY squiduser.s_shablon_id,squiduser.s_nick");
  }
  else
    $num_rows=$DB->samsdb_query_value("SELECT squiduser.*,sgroup.s_name AS gnick, shablon.s_period, shablon.s_clrdate FROM squiduser LEFT JOIN sgroup ON sgroup.s_group_id=squiduser.s_group_id LEFT JOIN shablon ON squiduser.s_shablon_id=shablon.s_shablon_id ORDER BY squiduser.s_group_id,squiduser.s_nick");
  
  while($row=$DB->samsdb_fetch_array())
      {
        $clrdate="";
	$clrdays=0;
       print("<TR>\n");

       //if($)
       if($row['s_enabled']>0)
         {
	    if($SAMSConf->realtraffic=="real")
	        $traffic=$row['s_size']-$row['s_hit'];
            else
	        $traffic=$row['s_size'];
            if($row['s_quote']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE>=$traffic||$row['s_quote']<=0)
               $gif="puser.gif";
            else
               if($row['s_quote']>0)
                  $gif="quote_alarm.gif";
          }
        if($row['s_enabled']==0)
          {
             $gif="puserd.gif";
          }
        if($row['s_enabled']<0)
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
             print(" <INPUT TYPE=\"CHECKBOX\" NAME=\"users\" ID=\"$count\" VALUE=\"$row[s_user_id]\" ");
             if($row['s_enabled']==1)
	       print(" CHECKED ");
	     print("> \n ");
             print(" <INPUT TYPE=\"HIDDEN\" NAME=\"dusers\" ID=\"$count\" VALUE=\"$row[s_enabled]\" >");
           }

	 print("<TD WIDTH=\"15%\"> <B><A HREF=\"tray.php?show=exe&filename=usertray.php&function=usertray&id=$row[s_user_id]\"  TARGET=\"tray\">$row[s_nick] </A></B>");
	 print("<TD WIDTH=\"15%\"> <B>$row[gnick] </B>");
             
         if($SAMSConf->access==2)
           {
	    if($SAMSConf->realtraffic=="real")
	        PrintFormattedSize($row['s_size']-$row['s_hit']);
            else
	        PrintFormattedSize($row['s_size']);

             
	     if($row['s_quote']>0)
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER> $row[s_quote] Mb");
	     else  
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER> unlimited ");
	   
	      if($row['s_period']!="M"&&$row['s_period']!="W")
                {
		  $period="$row[period] $userstray_AllUsersForm_10";
	        }
	      if($row['s_period']=="M")
                {
		  $period="$userstray_AllUsersForm_11";
	        }
	      if($row['s_period']=="W")
                {
		  $period="$userstray_AllUsersForm_12";
	        }
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER> $period ");
	   }
	   
         print("<TD WIDTH=\"40%\"> $row[s_family] $row[s_name] $row[s_soname]");
         if($SAMSConf->access==2)
           {
              print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"userdel\" ID=\"$count\" VALUE=\"$row[s_user_id]\" > \n");
	   }
	 $count=$count+1;  
      }
      print("<TR><TD><INPUT TYPE=\"BUTTON\" VALUE=\"select all\" onclick=EnableAll(groupform) > \n");
      print("<BR><INPUT TYPE=\"BUTTON\" VALUE=\"deselect all\" onclick=DisableAll(groupform) > \n");
      print("<TD><TD><TD><TD><TD><TD><TD> \n");
      print("<INPUT TYPE=\"BUTTON\" VALUE=\"select all\" onclick=DeleteAll(groupform) > \n");
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
  print("        parent.basefrm.location.href=\"main.php?show=exe&filename=userstray.php&function=AllUsersForm&type=all\";\n");
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
