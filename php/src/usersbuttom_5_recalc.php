<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function RecalcUsersTraffic()
{
  global $SAMSConf;
  global $USERConf;

  $DB=new SAMSDB();
  $DB2=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1)
	{      exit;     }

  $thisdate=strftime("%Y-%m-%d");
  $smdate=strftime("%Y")."-".strftime("%m")."-01";
  $stime="0:00:00";
  $etime=time();

	$shabloncount=$DB->samsdb_query_value("SELECT s_shablon_id, s_name, s_period, s_clrdate FROM shablon");
	$scount=0;
	$stime=array();
	$shablonid=array();
	while($row=$DB->samsdb_fetch_array())
	{
		$shablonid[$scount]=$row['s_shablon_id'];
		$edate[$scount]=$thisdate;
		$a=explode("-",$row['s_clrdate']);
		if($row['s_period']=="W")
		{
			$stime[$scount]=($etime - (strftime("%u")-1)*24*60*60);
			$sdate[$scount]=date("Y-m-d",$stime);
		}
		else if($row['s_period']!="M"&&$row['s_period']!="W")
		{
			$stime[$scount]=mktime(0,1,0,$a[1],$a[2],$a[0]) - $row['s_period']*24*60*60;
			$sdate[$scount]=date("Y-m-d",$stime[$scount]);
			$edate[$scount]=$row['s_clrdate'];
		}
		else
		{
			$stime[$scount]=mktime(0,1,0,strftime("%m",$etime),"1",strftime("%Y",$etime));
			$sdate[$scount]=strftime("%Y",$etime)."-".strftime("%m",$etime)."-01";
		}
		$s_shablon_id[$scount]=$row['s_shablon_id'];
		$s_shablon_name[$scount]=$row['s_name'];

		$scount++;
	}
	$DB->free_samsdb_query();
	sort($stime);

	$QUERY="TRUNCATE TABLE cachesum";
	$num_rows=$DB->samsdb_query($QUERY);

	$QUERY="INSERT INTO cachesum SELECT s_proxy_id,s_date,s_user,s_domain,sum(s_size),sum(s_hit) FROM squidcache GROUP BY s_date,s_user,s_domain,s_proxy_id";
	$num_rows=$DB->samsdb_query($QUERY);

	$QUERY="SELECT s_user_id, s_name, s_nick, s_shablon_id FROM squiduser";	
	$num_rows=$DB->samsdb_query_value($QUERY);
	while($row=$DB->samsdb_fetch_array())
	{
		$key = array_search($row['s_shablon_id'], $shablonid);
		$QUERY="SELECT sum(s_size) as size, sum(s_hit) as hit FROM cachesum WHERE s_user='$row[s_nick]' and s_date >='$sdate[$key]' and s_date <='$edate[$key]' ";

		$DB2->samsdb_query($QUERY);
		$row2=$DB2->samsdb_fetch_array();
		$sumsize=$row2['size']+0;
		$sumhit=$row2['hit']+0;
		$DB2->free_samsdb_query();

		$QUERY="UPDATE squiduser SET s_size='$sumsize', s_hit='$sumhit' WHERE s_user_id=$row[s_user_id]";
		$DB2->samsdb_query($QUERY);
	}
	$DB->free_samsdb_query();
	PageTop("usergroup_48.jpg","$usersbuttom_5_recalc_RecalcUsersTraffic_1 $sdate[$key] - $edate[$key] $usersbuttom_5_recalc_RecalcUsersTraffic_2");
	print("<SCRIPT>\n");
	print("        parent.basefrm.location.href=\"main.php?show=exe&filename=userstray.php&function=AllUsersForm&type=all\";\n");
	print("        parent.lframe.location.href=\"lframe.php\";\n");
	print("</SCRIPT> \n");

}



function usersbuttom_5_recalc()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")==1)
    {
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function RecalcCounter(username,userid)\n");
       print("{\n");
       print("  value=window.confirm(\"$usersbuttom_5_recalc_usersbuttom_5_recalc_1 \" );\n");
       print("  if(value==true) \n");
       print("     {\n");
       print("        parent.basefrm.location.href=\"main.php?show=exe&function=recalcuserstraffic&filename=usersbuttom_5_recalc.php\";\n");
       print("     }\n");
       print("}\n");
       print("</SCRIPT> \n");

       print("<TD CLASS=\"samstraytd\" >\n");
       print("<IMAGE id=Trash name=\"Clear\" src=\"$SAMSConf->ICONSET/recalc_32.jpg\" BORDER=0 \n ");
       print("TITLE=\"$usersbuttom_5_recalc_usersbuttom_5_recalc_2\"  border=0 ");
       print("onclick=RecalcCounter(\"nick\",\"id\") \n");
       print("onmouseover=\"this.src='$SAMSConf->ICONSET/recalc_48.jpg'\" \n");
       print("onmouseout= \"this.src='$SAMSConf->ICONSET/recalc_32.jpg'\" >\n");
       print("</TD>\n");
    }

}



?>
