<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
function DisableSelectedUsers()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  if($USERConf->ToWebInterfaceAccess("UAC")!=1)
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
       $num_rows=$DB->samsdb_query("UPDATE squiduser SET s_enabled='-1' WHERE s_user_id='$disable[$i]'");
   }
 for($i=0; $i<$defcount; $i++)
    {
       $num_rows=$DB->samsdb_query("UPDATE squiduser SET s_enabled='1' WHERE s_user_id='$defen[$i]'");
    }
 for($i=0; $i<$delcount; $i++)
    {
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
  global $USERConf;
  $DB=new SAMSDB();
  $DB2=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("AUCS")!=1)
  	exit(0);

  $groupname="";
  $type="all";
  if(isset($_GET["type"])) $type=$_GET["type"];
  if(isset($_GET["username"])) $username=$_GET["username"];

  PageTop("user.jpg","$grouptray_UserGroupForm_1");
  if($USERConf->ToWebInterfaceAccess("UAC")==1)
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

  if($USERConf->ToWebInterfaceAccess("UAC")==1)
    {
      print("<FORM NAME=\"groupform\" ACTION=\"main.php\"  METHOD=\"post\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"userstray.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"disableselectedusers\">\n");
      print(" <INPUT TYPE=\"BUTTON\" VALUE=\"$userstray_AllUsersForm_8\" onclick=SendForm(groupform) > \n");
   } 
  print("<TABLE WIDTH=\"100%\" BORDER=0 CLASS=samstable>\n");
  print("<THEAD>\n");
  print("<TR>\n");

  if($USERConf->ToWebInterfaceAccess("UCGS")==1)
    {
      print("<TH  WIDTH=\"10%\"> <B>$userstray_AllUsersForm_1</B> \n");
    }
  print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$userstray_AllUsersForm_2</B>\n");
  print("<TH WIDTH=\"10%\" bgcolor=beige> <B>$userstray_AllUsersForm_3</B>\n");
  if($USERConf->ToWebInterfaceAccess("UC")==1)
    {
      print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$userstray_AllUsersForm_4</B>\n");
      print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$userstray_AllUsersForm_5</B>\n");
      print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$userstray_AllUsersForm_9</B>\n");
    }  
  print("<TH WIDTH=\"30%\" bgcolor=beige> <B>$userstray_AllUsersForm_6</B>\n");
  if($USERConf->ToWebInterfaceAccess("UAC")==1)
    {
      print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$userstray_AllUsersForm_7</B>\n");
    }  
  print("</THEAD>\n");
  $count=0;
  
  if($type=="search")
  {
    $num_rows=$DB->samsdb_query_value("SELECT squiduser.*,sgroup.s_name AS gnick, shablon.s_period, shablon.s_clrdate, shablon.s_quote AS s_defquote, shablon.s_shablon_id2 as tpl2 FROM squiduser LEFT JOIN sgroup ON sgroup.s_group_id=squiduser.s_group_id LEFT JOIN shablon ON squiduser.s_shablon_id=shablon.s_shablon_id WHERE squiduser.s_nick like '%$username%' ORDER BY squiduser.s_shablon_id,squiduser.s_nick");
  }
  else
    $num_rows=$DB->samsdb_query_value("SELECT squiduser.*,sgroup.s_name AS gnick, shablon.s_period, shablon.s_clrdate, shablon.s_quote AS s_defquote, shablon.s_shablon_id2 as tpl2 FROM squiduser LEFT JOIN sgroup ON sgroup.s_group_id=squiduser.s_group_id LEFT JOIN shablon ON squiduser.s_shablon_id=shablon.s_shablon_id ORDER BY squiduser.s_group_id,squiduser.s_nick");
  
  print("<TBODY>\n");
  while($row=$DB->samsdb_fetch_array())
      {
        $clrdate="";
	$clrdays=0;
       print("<TR>\n");

	if($SAMSConf->realtraffic=="real")
	  $traffic=$row['s_size']-$row['s_hit'];
        else
	  $traffic=$row['s_size'];

        $defaultquote=$row['s_defquote'];

        if($row['s_enabled']==2)
          {
             $gif="user_moved.png";
             if ($row['tpl2'] > 0)
               {
                 $num_rows2=$DB2->samsdb_query_value("SELECT shablon.s_quote FROM shablon WHERE shablon.s_shablon_id=$row[tpl2]");
                 if ($num_rows2 == 1)
                   {
                     $row2=$DB2->samsdb_fetch_array();
                     $defaultquote=$row2['s_quote'];
                   }
               }
          }
        else if($row['s_enabled']==0)
          {
             $gif="user_inactive.png";
          }
        else if($row['s_enabled']<0)
          {
              $gif="user_off.png";
          }
        else if($row['s_enabled']==1)
          {
             if($row['s_quote']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE>=$traffic||$row['s_quote']<=0)
               $gif="user_active.png";
             else
               if($row['s_quote']>0)
                  $gif="quote_alarm.gif";
           }
	if($USERConf->ToWebInterfaceAccess("UACGS")==1)
           {
              print("<TD WIDTH=\"10%\">");
              print("<IMG SRC=\"$SAMSConf->ICONSET/$gif\">");
              
	   }
	if($USERConf->ToWebInterfaceAccess("UAC")==1)
           {
             print(" <INPUT TYPE=\"CHECKBOX\" NAME=\"users\" ID=\"$count\" VALUE=\"$row[s_user_id]\" ");
             if($row['s_enabled']>0)
	       print(" CHECKED ");
	     print("> \n ");
             print(" <INPUT TYPE=\"HIDDEN\" NAME=\"dusers\" ID=\"$count\" VALUE=\"$row[s_enabled]\" >");
           }

	 print("<TD WIDTH=\"15%\"> <B><A HREF=\"tray.php?show=exe&filename=usertray.php&function=usertray&id=$row[s_user_id]\"  TARGET=\"tray\">$row[s_nick] </A></B>");
	 print("<TD WIDTH=\"15%\"> <B>$row[gnick] </B>");
             
	if($USERConf->ToWebInterfaceAccess("UC")==1)
           {
	     PrintFormattedSize($traffic);
             
             $font_start="";
             $font_end="";
             if ($row['s_enabled']==2)
               {
                 $font_start="<font color=#CFCF00>";
                 $font_end="</font>";
               }

	     if($row['s_quote']>0)
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER><font color=red>$row[s_quote] Mb</font>");
             else if ($row['s_quote'] == 0)
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER><font color=red>unlimited</font>");
             else if ($defaultquote > 0)
               print("<TD WIDTH=\"15%\" ALIGN=CENTER>$font_start $defaultquote Mb $font_end");
	     else
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER>$font_start unlimited $font_end");
	   
	      if($row['s_period']!="M"&&$row['s_period']!="W"&&$row['s_period']!="D")
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
	      if($row['s_period']=="D")
                {
		  $period="$userstray_AllUsersForm_14";
	        }
	       print("<TD WIDTH=\"15%\" ALIGN=CENTER> $period ");
	   }
	   
         print("<TD WIDTH=\"40%\"> $row[s_family] $row[s_name] $row[s_soname]");
	if($USERConf->ToWebInterfaceAccess("UC")==1)
           {
              print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"userdel\" ID=\"$count\" VALUE=\"$row[s_user_id]\" > \n");
	   }
	else if($USERConf->ToWebInterfaceAccess("A")==1)
           {
              print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"userdel\" ID=\"$count\" VALUE=\"$row[s_user_id]\" DISABLED > \n");
	   }
	 $count=$count+1;  
      }
      print("</TBODY>\n");
      if($USERConf->ToWebInterfaceAccess("UAC")==1)
      {
	print("<TR><TD><INPUT TYPE=\"BUTTON\" VALUE=\"select all\" onclick=EnableAll(groupform) > \n");
	print("<BR><INPUT TYPE=\"BUTTON\" VALUE=\"deselect all\" onclick=DisableAll(groupform) > \n");
	print("<TD><TD><TD> \n");
      }
      if($USERConf->ToWebInterfaceAccess("UC")==1)
      {
	print("<TD><TD><TD>\n");
      }
      if($USERConf->ToWebInterfaceAccess("UC")==1)
      {
	print("<TD> <INPUT TYPE=\"BUTTON\" VALUE=\"select all\" onclick=DeleteAll(groupform) > \n");
      }
      else if($USERConf->ToWebInterfaceAccess("A")==1)
      {
	print("<TD> <INPUT TYPE=\"BUTTON\" VALUE=\"select all\" DISABLED > \n");
      }
  print("</TABLE>\n");

  if($USERConf->ToWebInterfaceAccess("UAC")==1)
    {
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"counter\" value=\"$count\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" value=\"$groupname\">\n");
    }
  if($USERConf->ToWebInterfaceAccess("AUC")==1)
    {
      print(" <INPUT TYPE=\"BUTTON\" VALUE=\"$userstray_AllUsersForm_8\" onclick=SendForm(groupform) > \n");
      
      print("</FORM>\n");
    } 



}




function UsersTray()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("AUCS")==1)
  {
	print("<SCRIPT>\n");
	print("        parent.basefrm.location.href=\"main.php?show=exe&filename=userstray.php&function=AllUsersForm&type=all\";\n");
	print("</SCRIPT> \n");
  }
  else
  {
	print("<SCRIPT>\n");
	print("        parent.basefrm.location.href=\"main.php\";\n");
	print("</SCRIPT> \n");
  }

  print("<TABLE border=0 WIDTH=95%>\n");
  print("<TR HEIGHT=60>\n");
  print("<TD WIDTH=25%>");
  print("<B><FONT SIZE=\"+1\" COLOR=\"blue\">$userstray_UsersTray_1</FONT></B>\n");

  ExecuteFunctions("./src", "usersbuttom","1");
//  print("<TD>\n");
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
