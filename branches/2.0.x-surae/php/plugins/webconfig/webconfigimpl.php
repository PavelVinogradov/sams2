<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

class WebConfigImpl {

	var $SamsDb;
	var $SamsUser;
	var $SamsConf;
	var $SamsTools;
	
	function WebConfigImpl ($db, $user, $conf, $tools) {
		$this->SamsDb = $db;
		$this->SamsUser = $user;
		$this->SamsConf = $conf;
		$this->SamsTools = $tools;
	}

	function SysInfo() {
		$result = "";
		$result .= $this->SamsTools->PageTop("stat_48.jpg","System Information");
		
		$hostname=$this->SamsTools->GetHostName();
		$ipaddr=$this->SamsTools->GetIPAddr();
		$uptime=$this->SamsTools->GetUpTime();
		$result .= "<TABLE WIDTH=90%  CLASS=samstable>";
		$result .= "<TR>";
		$result .= "<TD WIDTH=\"25%\"><B>Hostname</B>";
		$result .= "<TD WIDTH=\"75%\">".$hostname;
		$result .= "<TR>";
		$result .= "<TD WIDTH=\"25%\"><B>IP addr</B>";
		$result .= "<TD WIDTH=\"75%\">".$ipaddr;
		$result .= "<TR>";
		$result .= "<TD WIDTH=\"25%\"><B>Uptime</B>";
		$result .= "<TD WIDTH=\"75%\">".$uptime;
		$result .= "</TABLE>";
		
		$result .= $this->SamsTools->MemoryUsage();
		$result .= $this->SamsTools->FileSystemUsage();
		
		$syea=strftime("%Y");
		$smon=strftime("%m");
		$eday=strftime("%d");
		
		$sdate="$syea-$smon-1";
		$edate="$syea-$smon-$eday";
		$stime="0:00:00";
		$etime="0:00:00";
		
		$result .= "<P><TABLE CLASS=samstable>\n";
		$result .= "<TH>\n";
		$result .= "<TH width=\"33%\" >All traffic\n";
		$result .= "<TH width=\"33%\" >From cache\n";
		$result .= "<TH width=\"33%\" >Traffic\n";
		
		$num_rows=$this->SamsDb->samsdb_query_value("SELECT sum(s_size),sum(s_hit) FROM ".$SAMSConf->SAMSDB.".cachesum WHERE s_date>=\"$sdate\" && s_date<=\"$edate\" ");
		$row=$this->SamsDb->samsdb_fetch_array();
		$result .= "<TR>\n";
		$result .= "<TD > This month\n";
		$aaa=FormattedString("$row[0]");
		$result .= $this->SamsTools->RTableCell($aaa,33);
		$aaa=FormattedString("$row[1]");
		$result .= $this->SamsTools->RTableCell($aaa,33);
		$aaa=$row[0]-$row[1];
		$aaa=FormattedString($row[0]-$row[1]);
		$result .= $this->SamsTools->RTableCell($aaa,33);
		
		$num_rows=$this->SamsDb->samsdb_query_value("SELECT sum(s_size),sum(s_hit) FROM ".$SAMSConf->SAMSDB.".cachesum WHERE s_date=\"$edate\" ");
		$row=$this->SamsDb->samsdb_fetch_array();
		$result .= "<TR>\n";
		$result .= "<TD > This day\n";
		$aaa=FormattedString("$row[0]");
		$result .= $this->SamsTools->RTableCell($aaa,33);
		$aaa=FormattedString("$row[1]");
		$result .= $this->SamsTools->RTableCell($aaa,33);
		$aaa=$row[0]-$row[1];
		$aaa=FormattedString($row[0]-$row[1]);
		$result .= $this->SamsTools->RTableCell($aaa,33);
		
		$result .= "</TABLE>\n";
		
		return $result;
	}
    
	function CUserDoc() {
		$lang="./lang/lang.".$this->SamsConf->LANG;
		require($lang);
		
		$output = "";
		
		$output .= $this->SamsTools->PageTop("user.jpg","$admintray_UserDoc_1");
		
		$output .= "<H2>".$admintray_UserDoc_2."</H2>";
		$output .= "</CENTER>";
		$output .="<IMG SRC=\"".$this->SamsConf->ICONSET."/lframe.jpg\" ALIGN=LEFT>";
		$output .= $admintray_UserDoc_3;
		$output .= $admintray_UserDoc_4;
		
		return $output;
	}    

	function tray() {
		$lang="./lang/lang.".$this->SamsConf->LANG;
		require($lang);
		
		$output = "";
		$output .= "<SCRIPT>\n";
		if($this->SamsConf->access==2) {
			$output .= "parent.basefrm.location.href=\"main.php?module=WebConfig&function=sysinfo\";\n";
		} else {
			$output .= "parent.basefrm.location.href=\"main.php?module=WebConfig&function=cuserdoc\";\n";
		}
		$output .= "</SCRIPT>\n";
		
		$output .= "<TABLE WIDTH=\"100%\" BORDER=0>\n";
		$output .= "<TR>\n";
		$output .= "<TD VALIGN=\"TOP\" WIDTH=\"30%\"\">";
		
		$output .= "<B>". $webconfigbuttom_1_prop_webconfigbuttom_1_propadmintray_1 ."</B>\n";
		
		$output .= $this->SamsTools->ExecuteFunctions("./plugins/webconfig/buttons", "webconfigbuttom","1");
		
		$output .= "<TD>\n";
		$output .= "</TABLE>\n";
		
		return $output;
	}
}
?>