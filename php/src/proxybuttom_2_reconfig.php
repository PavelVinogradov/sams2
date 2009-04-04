<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function ReconfigSquid()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB(&$SAMSConf);
 
  if(isset($_GET["cache"])) $cache=$_GET["cache"];
   
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $reconfigureOK=0;
  if($USERConf->ToWebInterfaceAccess("C")==1 )
  {
	PageTop("reconfig_48.jpg","$squidbuttom_0_reconfig_ReconfigSquid_1");
	$result=mysql_query("insert into reconfig (s_proxy_id,s_service,s_action)  VALUES('$cache', 'squid', 'reconfig'); ");
	for($j=0;$j<10;$j++)
	{
		$num_rows=$DB->samsdb_query_value("SELECT count(*) as count FROM reconfig WHERE  s_service='squid' && s_proxy_id='$cache' && s_action='reconfig' ");
		$row=$DB->samsdb_fetch_array();
		if($row['count']==0)
		{
			$reconfigureOK=1;
			break;
		}
		else
			sleep(1);
          
	}
	if($reconfigureOK==1)
	{
		$str="<FONT color=\"BLUE\" SIZE=+1> $squidbuttom_0_reconfig_ReconfigSquid_3 </FONT><BR>\n";
	}
	else
	{ 
		$str="<FONT color=\"RED\" SIZE=+1> $squidbuttom_0_reconfig_ReconfigSquid_4 </FONT><BR>\n";
	}
	print("$str");
  }
}

function ReconfigSquidForm()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB(&$SAMSConf);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit(0);
  
  PageTop("reconfig_48.jpg","$squidbuttom_0_reconfig_ReconfigSquidForm_1 ");

  print("<FORM NAME=\"adddenied\" ACTION=\"main.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"reconfigsquid\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"proxybuttom_2_reconfig.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"cache\" value=\"1\">\n");
/*
  print("<P>\n");
   if($SAMSConf->PROXYCOUNT>1)
    {
      print("<TABLE CLASS=samstable WIDTH=\"80%\">");
      print("<TH width=60%>$squidbuttom_0_reconfig_ReconfigSquidForm_3");
      print("<TH width=20%>$squidbuttom_0_reconfig_ReconfigSquidForm_4");
      $result=mysql_query("SELECT id,description FROM ".$SAMSConf->SAMSDB.".proxyes ORDER BY id");
       while($row=mysql_fetch_array($result))
           {
             print("<TR><TD> $row[description]");
              print("<TD><INPUT TYPE=\"CHECKBOX\" NAME=cache[$row[id]]> \n");
           }
      print("</TABLE>");
    
    }
*/ 
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$squidbuttom_0_reconfig_ReconfigSquidForm_2\">\n");
  print("</FORM>\n");

}


function proxybuttom_2_reconfig()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       GraphButton("main.php?show=exe&function=reconfigsquidform&filename=proxybuttom_2_reconfig.php","basefrm","recsquid_32.jpg","recsquid_48.jpg","$squidbuttom_0_reconfig_squidbuttom_0_reconfig_1");
    }

}




?>
