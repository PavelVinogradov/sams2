<?php

function TestTable()
{
  global $SAMSConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
//  TestWI();

  $groupname="";
  $type="all";
  if(isset($_GET["type"])) $type=$_GET["type"];
  if(isset($_GET["username"])) $username=$_GET["username"];

  PageTop("user.jpg","Test ");

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
       print("   parent.basefrm.location.href=strr;\n");
       print("}\n");

       print("  function fullArray( username)\n");
       print("{\n");
       print("          this.username = username;\n"); 
       print("}\n");

       print("function SortTable(formname)\n");
       print("{\n");
       print("   var table = new Array(); \n");
       print("   for(var i=0; i < groupform.counter.value; i +=1 ) \n");
       print("       {\n");
       print("          table[i] = new fullArray( groupform.username[i].value )\n");
       print("        }\n");
       print("   for(var i=0; i < groupform.counter.value; i +=1 ) \n");
       print("       {\n");
       print("          groupform.username[i].value=table[groupform.counter.value - i -1][1]\n"); 
       print("        }\n");
       print("}\n");


       print("</SCRIPT> \n");
  if($SAMSConf->access==2)
    {
      print("<FORM NAME=\"groupform\" ACTION=\"main.php\"  METHOD=\"post\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"userstray.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"disableselectedusers\">\n");
   } 
  
  print("<TABLE WIDTH=\"100%\" BORDER=0 CLASS=samstable>\n");
  print("<THEAD>\n");
  print("<TR>\n");

  if($SAMSConf->access>0)
    {
      print("<TH  WIDTH=\"10%\" onClick=SortTable(groupform)>\n");
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
  
  print("</THEAD><TBODY>\n");
  while($row=$DB->samsdb_fetch_array())
      {
        $clrdate="";
	$clrdays=0;
       print("<TR>\n");

        if($row['s_enabled']==2)
          {
             $gif="user_moved.png";
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
             if($SAMSConf->realtraffic=="real")
                $traffic=$row['s_size']-$row['s_hit'];
             else
                $traffic=$row['s_size'];
             if($row['s_quote']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE>=$traffic||$row['s_quote']<=0)
               $gif="user_active.png";
             else
               if($row['s_quote']>0)
                  $gif="quote_alarm.gif";
           }
        if($SAMSConf->access>0)
           {
              print("<TD WIDTH=\"10%\" NAME=\"enabled\" ID=\"$count\">");
              print("<IMG SRC=\"$SAMSConf->ICONSET/$gif\">");
              
	   }
         if($SAMSConf->access==2)
           {
             print(" <INPUT TYPE=\"CHECKBOX\" NAME=\"users\" ID=\"$count\" VALUE=\"$row[s_user_id]\" ");
             if($row['s_enabled']>0)
	       print(" CHECKED ");
	     print("> \n ");
             print(" <INPUT TYPE=\"HIDDEN\" NAME=\"dusers\" ID=\"$count\" VALUE=\"$row[s_enabled]\" >");
           }

	 print("<TD WIDTH=\"15%\" NAME=\"username\" ID=\"$count\"> <B><A HREF=\"tray.php?show=exe&filename=usertray.php&function=usertray&id=$row[s_user_id]\"  TARGET=\"tray\">$row[s_nick] </A></B>");
	 print("<TD WIDTH=\"15%\" NAME=\"group\" ID=\"$count\"> <B>$row[gnick] </B>");
             
         if($SAMSConf->access==2)
           {
	    if($SAMSConf->realtraffic=="real")
	        PrintFormattedSize($row['s_size']-$row['s_hit']);
            else
	        PrintFormattedSize($row['s_size']);

             
	     if($row['s_quote']>0)
	       print("<TD WIDTH=\"15%\" NAME=\"quote\" ID=\"$count\" ALIGN=CENTER> $row[s_quote] Mb");
	     else  
	       print("<TD WIDTH=\"15%\" NAME=\"quote\" ID=\"$count\" ALIGN=CENTER> unlimited ");
	   
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
	       print("<TD WIDTH=\"15%\" NAME=\"period\" ID=\"$count\" ALIGN=CENTER> $period ");
	   }
	   
         print("<TD WIDTH=\"40%\" NAME=\"fio\" ID=\"$count\"> $row[s_family] $row[s_name] $row[s_soname]");
         if($SAMSConf->access==2)
           {
              print("<TD NAME=\"delete\" ID=\"$count\"><INPUT TYPE=\"CHECKBOX\" NAME=\"userdel\" ID=\"$count\" VALUE=\"$row[s_user_id]\" > \n");
	   }
	 $count=$count+1;  
      }
      print("<TR><TD><INPUT TYPE=\"BUTTON\" VALUE=\"select all\" onclick=EnableAll(groupform) > \n");
      print("<BR><INPUT TYPE=\"BUTTON\" VALUE=\"deselect all\" onclick=DisableAll(groupform) > \n");
      print("<TD><TD><TD><TD><TD><TD><TD> \n");
  print("</TBODY>\n");
  print("</TABLE>\n");

    if($SAMSConf->access==2)
    {
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"counter\" value=\"$count\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"groupname\" value=\"$groupname\">\n");
      
      print("</FORM>\n");
    } 



}

function usersbuttom_91_test()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

   if($SAMSConf->access==2||($SAMSConf->USERACCESS=="Y"&&$SAMSConf->domainusername=="$row[domain]+$row[nick]"))
    {
       GraphButton("main.php?show=exe&function=testtable&filename=usersbuttom_91_test.php","basefrm","useradd_32.jpg","useradd_48.jpg","$usersbuttom_1_useradd_usersbuttom_1_useradd_1");
	}

}


?>
