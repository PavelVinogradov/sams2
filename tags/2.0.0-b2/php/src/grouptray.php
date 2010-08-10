<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */
function MoveUsersToGroup()
{
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
 if(isset($_GET["id"])) $id=$_GET["id"];
 if(isset($_GET["username"])) $users=$_GET["username"];

  for($i=0;$i<count($users);$i++)
    {
           $num_rows=$DB->samsdb_query("UPDATE squiduser SET s_group_id='$id' WHERE s_user_id='$users[$i]' ");
    }
  print("<SCRIPT>\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&filename=grouptray.php&function=usergroupform&id=$id&gid=ALL\";\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");
}

function DisableGroupUsers()
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
 if(isset($_GET["id"])) $id=$_GET["id"];

 $disable=explode(",",$disable);
 $count1=count($disable);
 $defen=explode(",",$defen);
 $count3=count($defen);
 $delete=explode(",",$delete);
 $count2=count($delete);

 for($i=0; $i<$discount; $i++)
    {
	$QUERY="UPDATE squiduser SET s_enabled='-1' WHERE s_user_id='$disable[$i]'";
	$num_rows=$DB->samsdb_query($QUERY);
   }
 for($i=0; $i<$defcount; $i++)
    {
	$QUERY="UPDATE squiduser SET s_enabled='1' WHERE s_user_id='$defen[$i]'";
       $num_rows=$DB->samsdb_query($QUERY);
    }
 for($i=0; $i<$delcount; $i++)
    {
        $num_rows=$DB->samsdb_query("DELETE FROM squiduser WHERE s_user_id='$delete[$i]' ");
    }

     print("<SCRIPT>\n");
     print("        parent.lframe.location.href=\"lframe.php\";\n");
     print("        parent.tray.location.href=\"tray.php?show=exe&filename=grouptray.php&function=grouptray&id=$id\";\n");
     print("</SCRIPT> \n");
}


function UserGroupForm()
{
  
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["gid"])) $gid=$_GET["gid"];

  $ga=0;
  $num_rows=$DB->samsdb_query_value("SELECT * FROM sgroup WHERE s_group_id='$id' ");
  $row=$DB->samsdb_fetch_array();
  $gname=$row['s_name'];
  if($SAMSConf->groupauditor==$row['s_name'])
    {
      $ga=1;
    }
  PageTop("user.jpg","$grouptray_UserGroupForm_1.<BR>$grouptray_UserGroupForm_2 <FONT COLOR=\"blue\">$gname</FONT>");


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
       print("   var strr= \"main.php?show=exe&filename=grouptray.php&function=disablegroupusers&disable=\" + disable + \"&delete=\" + userdel + \"&defen=\" + defen + \"&delcount=\"+delcount+\"&discount=\"+discount + \"&defcount=\"+defcount +\"&id=$id\"  \n");
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


  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
      print("<FORM NAME=\"groupform\" ACTION=\"main.php\"  METHOD=\"post\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"grouptray.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"disablegroupusers\">\n");
      print(" <INPUT TYPE=\"BUTTON\" VALUE=\"$userstray_AllUsersForm_8\" onclick=SendForm(groupform) > \n");
    } 
  
  print("<TABLE WIDTH=\"100%\" BORDER=0 CLASS=samstable>\n");

  if($USERConf->ToWebInterfaceAccess("CGS")==1)
    {
      print("<TH WIDTH=\"10%\">");
      print("<B>$grouptray_NewGroupForm_3</B></TH>\n");
    }
  print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$grouptray_NewGroupForm_4</B></TH>\n");
  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
      print("<TH WIDTH=\"10%\" bgcolor=beige> <B>$grouptray_NewGroupForm_5</B></TH>\n");
    }   
  if($USERConf->ToWebInterfaceAccess("C")==1||$ga==1)
    {
      print("<TH WIDTH=\"15%\" bgcolor=beige ALIGN=CENTER> <B>$grouptray_NewGroupForm_6</B></TH>\n");
      print("<TH WIDTH=\"15%\" bgcolor=beige ALIGN=CENTER> <B>$grouptray_NewGroupForm_7</B></TH>\n");
    }  
  print("<TH WIDTH=\"40%\" bgcolor=beige> <B>$grouptray_NewGroupForm_8</B></TH>\n");
  if($USERConf->ToWebInterfaceAccess("UAC")==1)
    {
      print("<TH WIDTH=\"15%\" bgcolor=beige> <B>$userstray_AllUsersForm_7</B>\n");
    }  
  $DB->free_samsdb_query();

  $count=0;
  $num_rows=$DB->samsdb_query_value("SELECT squiduser.*, shablon.s_quote AS s_defquote FROM squiduser, shablon WHERE squiduser.s_group_id='$id' AND squiduser.s_shablon_id=shablon.s_shablon_id ORDER BY squiduser.s_nick");
  while($row=$DB->samsdb_fetch_array())
      {
       print("<TR>\n");

       if($row['s_enabled']>0)
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
        if($row['s_enabled']==0)
          {
             $gif="puserd.gif";
          }
        if($row['s_enabled']<0)
          {
              $gif="duserd.gif";
           }
	if($USERConf->ToWebInterfaceAccess("CGS")==1)
           {
              print("  <TD WIDTH=\"10%\">");
              print("<IMG SRC=\"$SAMSConf->ICONSET/$gif\">");
           }
	if($USERConf->ToWebInterfaceAccess("C")==1)
           {
             print(" <INPUT TYPE=\"CHECKBOX\" NAME=\"users\" ID=\"$count\" VALUE=\"$row[s_user_id]\" ");
             if($row['s_enabled']>0)
	       print(" CHECKED ");
	     print("></TD>\n");

             print(" <INPUT TYPE=\"HIDDEN\" NAME=\"dusers\" ID=\"$count\" VALUE=\"$row[s_enabled]\" >");
           }
	 
	 print("  <TD WIDTH=\"15%\"> <B><A HREF=\"tray.php?show=exe&filename=usertray.php&function=usertray&id=$row[s_user_id]\"  TARGET=\"tray\">$row[s_nick] </A></B></TD>\n");
	if($USERConf->ToWebInterfaceAccess("C")==1)
           {
             print("  <TD WIDTH=\"15%\"> <B>$row[s_domain]</B></TD>\n");
	   }   
	 if($USERConf->ToWebInterfaceAccess("C")==1||$ga==1)
           {
	    if($SAMSConf->realtraffic=="real")
	     	PrintFormattedSize($row['s_size'] - $row['s_hit']);
	    else
		PrintFormattedSize($row['s_size']);
             
             if($row['s_quote']>0)
               print("  <TD WIDTH=\"15%\" ALIGN=CENTER><font color=red>$row[s_quote] Mb</font></TD>\n");
             else if($row['s_quote']==0)
               print("  <TD WIDTH=\"15%\" ALIGN=CENTER><font color=red>unlimited</font></TD>\n");
             else if($row['s_defquote']>0)
               print("  <TD WIDTH=\"15%\" ALIGN=CENTER>$row[s_defquote] Mb</TD>\n");
             else
               print("  <TD WIDTH=\"15%\" ALIGN=CENTER>unlimited</TD>\n");
	   }
         print("  <TD WIDTH=\"40%\"> $row[s_family] $row[s_name] $row[s_soname]</TD>\n");

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

      if($USERConf->ToWebInterfaceAccess("UAC")==1)
      {
	print("<TR><TD><INPUT TYPE=\"BUTTON\" VALUE=\"select all\" onclick=EnableAll(groupform) > \n");
	print("<BR><INPUT TYPE=\"BUTTON\" VALUE=\"deselect all\" onclick=DisableAll(groupform) > \n");
	print("<TD><TD><TD> \n");
      }
      if($USERConf->ToWebInterfaceAccess("UC")==1)
      {
	print("<TD><TD>\n");
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
  $DB->free_samsdb_query();

  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"counter\" value=\"$count\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$id\">\n");
      print(" <INPUT TYPE=\"BUTTON\" VALUE=\"$userstray_AllUsersForm_8\" onclick=SendForm(groupform) > \n");
      print("</FORM>\n");
    } 

  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
	print("<SCRIPT language=JAVASCRIPT>\n");
        print("function SelectUsers(id)\n");
        print("{\n");
        print("   var group = \"main.php?show=exe&filename=grouptray.php&function=usergroupform&id=$id&gid=\" +  id ; \n");
        print("   parent.basefrm.location.href=group;\n");
        print("}\n");
	print("</SCRIPT>\n");

      print("<P><B>$grouptray_NewGroupForm_10 $gname:</B> ");
      print("<FORM NAME=\"moveform\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"grouptray.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"moveuserstogroup\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$id\">\n");

      print("<SELECT NAME=\"groupid\" onchange=SelectUsers(moveform.groupid.value)>\n");
      $num_rows=$DB->samsdb_query_value("SELECT * FROM sgroup WHERE s_group_id!='$id' ORDER BY s_name");
      if($gid=="ALL")
        print("<OPTION VALUE=\"ALL\" SELECTED> ALL\n");
      else
        print("<OPTION VALUE=\"ALL\"> ALL\n");

      while($row=$DB->samsdb_fetch_array())
         {
	    $SECTED="";
	    if($row['s_group_id']==$gid)
		$SECTED="SELECTED";
	    if($row['s_group_id']!=$id)
               print("<OPTION VALUE=\"$row[s_group_id]\" $SECTED> $row[s_name]\n");
         }
      print("</SELECT>\n");
	$DB->free_samsdb_query();

      print("<SELECT NAME=\"username[]\" SIZE=10 MULTIPLE>\n");
      if($gid=="ALL")
        $num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE s_group_id!='$id' ORDER BY s_nick");
      else
	$num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE s_group_id='$gid' ORDER BY s_nick ");
      while($row=$DB->samsdb_fetch_array())
         {
            print("<OPTION VALUE=$row[s_user_id]> $row[s_nick]\n");
         }
      print("</SELECT>\n");
      print(" <P><INPUT TYPE=\"SUBMIT\" VALUE=\"$grouptray_NewGroupForm_11 '$gname'\" \n> ");
      print("</TABLE> ");
    } 


}


function GroupTray()
{
  if(isset($_GET["id"])) $id=$_GET["id"];
  global $SAMSConf;
  global $USERConf;

  if($USERConf->ToWebInterfaceAccess("SC")==1 || ($USERConf->ToWebInterfaceAccess("G")==1 && $USERConf->s_group_id==$id ))
  {
	$DB=new SAMSDB();

	$lang="./lang/lang.$SAMSConf->LANG";
	require($lang);
	print("<SCRIPT>\n");
	print("        parent.basefrm.location.href = \"main.php?show=exe&filename=grouptray.php&function=usergroupform&id=$id&gid=ALL\";\n");
	print("</SCRIPT> \n");

	$num_rows=$DB->samsdb_query_value("SELECT * FROM sgroup WHERE s_group_id='$id' ");
	$row=$DB->samsdb_fetch_array();
	print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
	print("<TR HEIGHT=60>\n");
	print("<TD WIDTH=25%>");
	print("<B>$grouptray_GroupTray_1 <BR><FONT SIZE=\"+1\" COLOR=\"blue\">$row[s_name] </FONT> </B>\n");

	ExecuteFunctions("./src", "groupbuttom",$id);

	print("<TD>\n");
	print("</TABLE>\n");
  }
  else
  {
	print("<SCRIPT>\n");
	print("        parent.basefrm.location.href=\"main.php\";\n");
	print("</SCRIPT> \n");
  }

}

?>
