<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)

     Based on *** sqstat - Squid Proxy Server realtime stat ***
        (c) Alex Samorukov, samm@os2.kiev.ua

 */

function Monitor_3()
{
  global $SAMSConf;
  $timeout=10;
  if(isset($_GET["timeout"])) $timeout=$_GET["timeout"];
  if(isset($_GET["squidip"])) $squidip=$_GET["squidip"];

  include_once("sqstat.class.php");
  $squidclass=new squidstat();
  $use_js=true; // use javascript for the HTML toolkits

	db_connect($SAMSConf->SAMSDB) or exit();
	mysql_select_db($SAMSConf->SAMSDB);
	$usernick=array();
	$userip=array();
	$i=0;
	$result=mysql_query("SELECT nick,ip,size FROM squidusers");
	while($row=mysql_fetch_array($result))
	{
		$usernick[$i]=$row['nick'];
		$userip[$i]=$row['ip'];
//echo "$usernick[$i] $userip[$i]<BR>";
		$i++;
	}

 
	DEFINE("SQSTAT_SHOWLEN",60);
	if($SAMSConf->SQUIDIP=="")
		$squidhost="127.0.0.1";
	else if($SAMSConf->SQUIDIP!=$squidip && $squidip!="")
		$squidhost=$squidip;
	else
		$squidhost="$SAMSConf->SQUIDIP";
	$squidport=3128;
	$cachemgr_passwd="";
	$resolveip=false;
	$group_by="host";

	if($timeout!=0)
	{
		printf("<SCRIPT LANGUAGE=\"javascript\">\n");
		printf("function Refr() \n");
		printf("{\n");
		printf("document.location='main.php?show=exe&function=monitor_3&filename=monitorbuttom_3.php&timeout=$timeout&squidip=$squidhost'};\n");
		printf("setTimeout('Refr();',$timeout*1000);\n");
		printf("</SCRIPT>\n");
	}

	print("<FORM NAME=\"timeoutform\" ACTION=\"main.php\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"monitor_3\">\n");
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"monitorbuttom_3.php\">\n");
	print("<TABLE>");
	print("<TR><TD><B>Timeout:</B> <INPUT TYPE=\"TEXT\" NAME=\"timeout\" SIZE=\"3\" value=\"$timeout\"> sec\n");
	print("<TD><INPUT TYPE=\"SUBMIT\" VALUE=\"Change\">\n");
	print("<TR><TD><B> Proxy ip:</B> <INPUT TYPE=\"TEXT\" NAME=\"squidip\" SIZE=\"15\" value=\"$squidhost\">\n");
	print("</FORM>\n");
	print("</TABLE>");

if(!$squidclass->connect($squidhost,$squidport)) 
{
	$squidclass->showError();
	exit(1);
}
$data=$squidclass->makeQuery($cachemgr_passwd);
if($data==false){
	$squidclass->showError();
	exit(2);
}
		if($squidclass->use_sessions)
		{
			session_name('SQDATA');
			session_start();
		}

		print("<TABLE CLASS=samstable>");
		print("<TH >Username");
		print("<TH >IP");
		print("<TH >URL");
		print("<TH >Connection time");
		print("<TH >Size");


		$group_by_key="host";
		foreach($data["con"] as $key => $v)
		{
			print("<TR>\n");
			if(substr($v["uri"],0,13)=="cache_object:") continue; // skip myself
			$ip=substr($v["peer"],0,strpos($v["peer"],":"));
			if(isset($hosts_array[$ip]))
			{
				$ip=$hosts_array[$ip];
			}
			$v['connection'] = $key;
			if(!isset($v["username"])) $v["username"]="N/A";
			$users[eval($group_by_key)][]=$v;

			$con_id=$v['connection'];
			$uritext=htmlspecialchars($v["uri"]);

			$key=array_search($v["username"],$usernick);
			if($key==NULL)
				$key=array_search($ip,$userip);

			if($key!=NULL)
				print("<TD >$usernick[$key]");
			else
				print("<TD > N/A ");

			print("<TD >$ip");
			$uritext1="";
			$uritext2="";
			$uritext3="";
			if(strlen($v["uri"])>50) 
			{
				$uritext1=htmlspecialchars(substr($v["uri"],0,40));
				$uritext2="...";
				$uritext3=htmlspecialchars(substr($v["uri"],strlen($v["uri"])-10,10));
			}
			else
			{
				$uritext1=htmlspecialchars($v["uri"]);
			}
			print("<TD ><A HREF=\"$v[uri]\">$uritext1$uritext2$uritext3</A>");
			$ttt=PrintFormattedTime($v['seconds']);
			print("<TD ALIGN=\"RIGHT\">$ttt"); 
			$aaa=PrintFormattedSize($v['bytes']);
			print("<TD >$aaa");
/*
echo "-$_SESSION[time]-$session_data[time]-<BR>";
echo " $ip: $con_id $v[username]<BR> $uritext -$v[bytes]- -$v[seconds]-$session_data<BR><P>";
*/

		}
	print("</TABLE>");
	printf("<P><FONT SIZE=\"-2\">based on <A HREF=\"http://samm.kiev.ua/sqstat/\">sqstat - Squid Proxy Server realtime stat</A>");
}

function PrintFormattedTime($timerange)
{
	$min=floor($timerange/60);
	$sec=$timerange-$min*60;
	$hour=floor($min/60);
	$min=$min-$hour*60;
	if($hour>0)
		$time="$hour h";
	if($min>0)
		$time="$time $min m ";
	return("$time $sec s");
}


function monitorbuttom_3()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->access==2)
    {
      print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
      GraphButton("main.php?show=exe&function=monitor_3&filename=monitorbuttom_3.php","basefrm","usermon3-32.jpg","usermon3-48.jpg","$monitorbuttom_1_monitorbuttom_1_1");
    }

}

?>
