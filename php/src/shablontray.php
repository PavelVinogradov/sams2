<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function MoveUsersToShablon()
{
  global $SAMSConf;
  global $SHABLONConf;
  global $USERConf;
  $DB=new SAMSDB();

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["username"])) $users=$_GET["username"];
  for($i=0;$i<count($users);$i++)
    {
	$a=explode("+",$users[$i]);
	if($a[1]!=$a[2])
		$num_rows=$DB->samsdb_query("UPDATE squiduser SET s_shablon_id='$id' WHERE s_user_id='$a[0]'");
	else
		$num_rows=$DB->samsdb_query("UPDATE squiduser SET s_shablon_id='$id',s_quote='$a[3]' WHERE s_user_id='$a[0]'");
    }
  print("<SCRIPT>\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&filename=shablontray.php&function=shablonusers&id=$id&sid=ALL\";\n");
  print("        parent.lframe.location.href=\"lframe.php\";\n");
  print("</SCRIPT> \n");
}

function ShablonUsers()
{
  global $SAMSConf;
  global $SHABLONConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["sid"])) $sid=$_GET["sid"];

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;
  
  PageTop("shablon.jpg","$shablon_1<BR>$shablontray_ShablonUsers_1 <FONT COLOR=\"BLUE\">$SHABLONConf->s_name</FONT>");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\">");
  print("<A HREF=\"http://sams.perm.ru/sams2/doc/".$SAMSConf->LANG."/templates.html\">$documentation</A>");
  print("<P>\n");

  print("<TABLE>\n");
  print("<TR><TD><B>$shablonbuttom_1_prop_UpdateShablonForm_19</B><TD><FONT COLOR=\"BLUE\">$SHABLONConf->s_auth</FONT>\n");
  print("<TR><TD><B>$shablonbuttom_1_prop_UpdateShablonForm_4</B><TD><FONT COLOR=\"BLUE\">$SHABLONConf->s_quote</FONT>\n");
  if( $SHABLONConf->s_quote == 0 )
 	print(" <FONT COLOR=\"BLUE\">(unlimited traffic)</FONT>");  
  print("<TR><TD><B>$shablonbuttom_1_prop_UpdateShablonForm_20</B><TD>");
  if($SHABLONConf->s_period=="M")   
     print("<FONT COLOR=\"BLUE\">$shablonbuttom_1_prop_UpdateShablonForm_24</FONT>\n");
  else if($SHABLONConf->s_period=="W")   
     print("<FONT COLOR=\"BLUE\">$shablonbuttom_1_prop_UpdateShablonForm_25</FONT>\n");
  else if($SHABLONConf->s_period=="D")   
     print("<FONT COLOR=\"BLUE\">$shablonbuttom_1_prop_UpdateShablonForm_27</FONT>\n");
  else
	{
		print("<FONT COLOR=\"BLUE\">$SHABLONConf->s_period $shablonbuttom_1_prop_UpdateShablonForm_17</FONT>\n");
		 print("<TR><TD>$shablonbuttom_1_prop_UpdateShablonForm_18:<TD><FONT COLOR=\"BLUE\">$SHABLONConf->s_clrdate</FONT>");
	}
  $second_template="NONE";

  print("<TR><TD><B>$AddTRangeForm_trangetray_1: </B>\n");
  $num_rows=$DB->samsdb_query_value("SELECT sconfig_time.*, timerange.s_name, timerange.s_timestart, s_timeend FROM sconfig_time LEFT JOIN timerange ON sconfig_time.s_trange_id=timerange.s_trange_id WHERE sconfig_time.s_shablon_id='$id' ");
  if($num_rows>0)
	{
  	while($row=$DB->samsdb_fetch_array())
		{
		print("<TR><TD><TD ALIGN=\"RIGHT\" > <FONT COLOR=\"BLUE\"><B>$row[s_name]</B> ($row[s_timestart] - $row[s_timeend] ) ");
		}
	}
  $DB->free_samsdb_query();

  if ($SHABLONConf->s_shablon_id2 != -1)
    {
      $num_rows=$DB->samsdb_query_value("SELECT * FROM shablon WHERE s_shablon_id='$SHABLONConf->s_shablon_id2'");
      if($row=$DB->samsdb_fetch_array())
         {
            $second_template = $row["s_name"];
         }
    }

  print("<TR><TD><B>$shablonbuttom_1_prop_UpdateShablonForm_28 </B><TD><FONT COLOR=\"BLUE\">$second_template\n");

  print("<TR><TD><B>Delay pool </B>\n");
  $QUERY="SELECT d_link_s.s_pool_id as delaypoolid, d_link_s.s_shablon_id as shablonid,shablon.s_name, delaypool.s_name as delaypoolname FROM d_link_s LEFT JOIN shablon ON d_link_s.s_shablon_id=shablon.s_shablon_id LEFT JOIN delaypool ON d_link_s.s_pool_id=delaypool.s_pool_id WHERE shablon.s_shablon_id='$id'";
  $num_rows=$DB->samsdb_query_value($QUERY);
  if($num_rows>0)
	{
		$row=$DB->samsdb_fetch_array();
		print("<TD><FONT COLOR=\"BLUE\">$row[delaypoolname]\n");

	}
  $DB->free_samsdb_query();

  print("<TR>\n");
  print("</TABLE>\n");


  $num_rows=$DB->samsdb_query_value("SELECT * FROM squiduser WHERE squiduser.s_shablon_id='$id' ORDER BY s_nick");

 print("<H2>$shablontray_ShablonUsers_4: </H2>\n");
  print("<TABLE>\n");
  while($row=$DB->samsdb_fetch_array())
      {
       print("<TR>\n");
       print("<TD>");
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
       print("<IMG SRC=\"$SAMSConf->ICONSET/$gif\" TITLE=\"\"> ");
       print("<TD> <B>$row[s_nick] </B>");
       print("<TD> $row[s_family] ");
       print("<TD> $row[s_name] ");
       print("<TD> $row[s_soname] ");
      }
  $DB->free_samsdb_query();
  print("</TABLE>\n");

  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {

	print("<SCRIPT language=JAVASCRIPT>\n");
        print("function SelectUsers(id)\n");
        print("{\n");
        print("   var shablon = \"main.php?show=exe&filename=shablontray.php&function=shablonusers&id=$SHABLONConf->s_shablon_id&sid=\" +  id ; \n");
        print("   parent.basefrm.location.href=shablon;\n");
        print("}\n");
	print("</SCRIPT>\n");

      print("<P><BR><B>$shablontray_ShablonUsers_2 $SHABLONConf->s_name:</B> ");
      print("<FORM NAME=\"moveform\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"shablontray.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"moveuserstoshablon\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" value=\"$SHABLONConf->s_shablon_id\">\n");

      print("<SELECT NAME=\"shablonid\" onchange=SelectUsers(moveform.shablonid.value)>\n");
      $num_rows=$DB->samsdb_query_value("SELECT * FROM shablon WHERE s_shablon_id!='$SHABLONConf->s_shablon_id' ORDER BY s_name");
      if($sid=="ALL")
        print("<OPTION VALUE=\"ALL\" SELECTED> ALL\n");
      else
        print("<OPTION VALUE=\"ALL\"> ALL\n");

      while($row=$DB->samsdb_fetch_array())
         {
	    $SECTED="";
	    if($row['s_shablon_id']==$sid)
		$SECTED="SELECTED";
            print("<OPTION VALUE=\"$row[s_shablon_id]\" $SECTED> $row[s_name] \n");
         }
      print("</SELECT>\n");
      $DB->free_samsdb_query();
//echo "<BR>SELECT * FROM squiduser WHERE s_shablon_id='$sid'&&s_shablon_id!='$id' ORDER BY s_nick<BR>";
      print("<SELECT NAME=\"username[]\" SIZE=10 MULTIPLE >\n");
      if($sid=="ALL")
	$num_rows=$DB->samsdb_query_value("SELECT squiduser.s_user_id,squiduser.s_nick,squiduser.s_quote,shablon.s_quote as def_quote FROM squiduser LEFT JOIN shablon ON squiduser.s_shablon_id=shablon.s_shablon_id WHERE shablon.s_shablon_id!='$id' ORDER BY s_nick");
      else
	$num_rows=$DB->samsdb_query_value("SELECT squiduser.s_user_id,squiduser.s_nick,squiduser.s_quote,shablon.s_quote as def_quote FROM squiduser LEFT JOIN shablon ON squiduser.s_shablon_id=shablon.s_shablon_id WHERE shablon.s_shablon_id='$sid' ORDER BY s_nick");
      while($row=$DB->samsdb_fetch_array())
         {
            print("<OPTION ID=\"$row[s_user_id]\" VALUE=$row[s_user_id]+$row[s_quote]+$row[def_quote]+$SHABLONConf->s_quote> $row[s_nick]\n");
         }
      print("</SELECT>\n");
      print("<P> <INPUT TYPE=\"SUBMIT\" VALUE=\"$shablontray_ShablonUsers_3 '$SHABLONConf->s_name'\" \n> ");
      print("</FORM>\n");
    } 

}


function ShablonTray()
{
  global $SAMSConf;
  global $SHABLONConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&filename=shablontray.php&function=shablonusers&id=$SHABLONConf->s_shablon_id&sid=ALL\";\n");
      print("</SCRIPT> \n");

      print("<TABLE border=0 WIDTH=\"100%\">\n");
      print("<TR HEIGHT=60>\n");
      print("<TD WIDTH=25%>");
      print("<B>$shablontray_ShablonTray_1 <BR><FONT SIZE=\"+1\" COLOR=\"BLUE\">$SHABLONConf->s_name</FONT></B>\n");

      ExecuteFunctions("./src", "shablonbuttom","1");
    }
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
