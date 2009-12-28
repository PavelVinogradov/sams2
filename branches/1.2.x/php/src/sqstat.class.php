<?php
/*               *** sqstat - Squid Proxy Server realtime stat ***
(c) Alex Samorukov, samm@os2.kiev.ua
*/

// sqstat class

class squidstat{
	var $fp;
	var $errstr;
	var $errno;
	
	var $use_sessions=false;
	function squidstat(){
		if (!function_exists("preg_match")){
			$this->errno=5;
			$this->errstr='You need to install <a href="http://www.php.net/pcre/" target="_blank">PHP pcre extension</a> to run this script';
			$this->showError();
			exit(5);
		}
		
		// we need session support to gather avg. speed
		if (function_exists("session_start")){
			$this->use_sessions=true;
		}
		
	}
	
	function formatXHTML($body,$refresh,$use_js=false){
		$text='<?xml version="1.0" encoding="UTF-8"?>'."\n".
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n"
		.'<html>'
		.'<head>'
		.'<link href="sqstat.css" rel="stylesheet" type="text/css"/>';
		if($refresh) $text.='<META HTTP-EQUIV=Refresh CONTENT="'.$refresh.'; URL='.$_SERVER["PHP_SELF"].'?refresh='.$refresh.'&config='.$GLOBALS["config"].'"/>';
		$text.='<title>SqStat '.SQSTAT_VERSION.'</title>'
		.($use_js?'<script src="zhabascript.js" type="text/javascript"></script>':'').'</head>'
		.($use_js?'<body onload="jsInit();"><div id="dhtmltooltip"></div><img id="dhtmlpointer" src="arrow.gif">':'<body>')
		.$body.'</body></html>';
		return $text;
	}
	
	function showError(){
		$text='<h1>SqStat error</h1>'.
		'<h2 style="color:red">Error ('.$this->errno.'): '.$this->errstr.'</span>';
		echo $this->formatXHTML($text,0);
	}
	function connect($squidhost,$squidport){
		$this->fp = false;
		// Connecting to the squidhost
		$this->fp = @fsockopen($squidhost, $squidport, $this->errno, $this->errstr, 10);
		if (!$this->fp) {
			// failed to connect
			return false;
		}
		return true;
	}
	
	// based @ (c) moritz at barafranca dot com
	function duration ($seconds) {
		$takes_time = array(604800,86400,3600,60,0);
		$suffixes = array("w","d","h","m","s");
		$output = "";
		foreach ($takes_time as $key=>$val) {
			${$suffixes[$key]} = ($val == 0) ? $seconds : floor(($seconds/$val));
			$seconds -= ${$suffixes[$key]} * $val;
			if (${$suffixes[$key]} > 0) {
				$output .=  ${$suffixes[$key]};
				$output .= $suffixes[$key]." ";
			}
		}
		return trim($output);
	}
	/**
	* Format a number of bytes into a human readable format.
	* Optionally choose the output format and/or force a particular unit
	*
	* @param   int     $bytes      The number of bytes to format. Must be positive
	* @param   string  $format     Optional. The output format for the string
	* @param   string  $force      Optional. Force a certain unit. B|KB|MB|GB|TB
	* @return  string              The formatted file size
	*/
	function filesize_format($bytes, $format = '', $force = '')
	{
		$force = strtoupper($force);
		$defaultFormat = '%01d %s';
		if (strlen($format) == 0)
		$format = $defaultFormat;
		$bytes = max(0, (int) $bytes);
		$units = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
		$power = array_search($force, $units);
		if ($power === false)
		$power = $bytes > 0 ? floor(log($bytes)/log(1024)) : 0;
		return sprintf($format, $bytes / pow(1024, $power), $units[$power]);
	}
	function makeQuery($pass=""){
		$raw=array();
		// sending request
		if(!$this->fp) die("Please connect to server");
		$out = "GET cache_object://localhost/active_requests HTTP/1.0\r\n";
		if($pass!="") $out.="Authorization: Basic ".base64_encode("cachemgr:$pass")."\r\n";
		$out.="\r\n";
		fwrite($this->fp, $out);
		
		while (!feof($this->fp)) {
			$raw[]=trim(fgets($this->fp, 2048));
		}
		fclose($this->fp);
		
		if($raw[0]!="HTTP/1.0 200 OK"){
			$this->errno=1;
			$this->errstr="Cannot get data. Server answered: $raw[0]";
			return false;
		}
		// parsing output;
		$header=1;
		$connection=0;
		$parsed["server_version"]="Unknown";
		foreach($raw as $key=>$v){
			// cutoff http header
			if($header==1 && $v=="") $header=0;
			if($header){
				if(substr(strtolower($v),0,7)=="server:"){ // parsing server version
					$parsed["server_version"]=substr($v,8);
				}
			}
			else {
				if(substr($v,0,11)=="Connection:"){ // parsing connection
					$connection=substr($v,12);
				}
				if($connection){
					/* username field is avaible in Squid 2.6 stable */
					if(substr($v,0,9)=="username ") $parsed["con"][$connection]["username"]=substr($v,9);
					if(substr($v,0,5)=="peer:") $parsed["con"][$connection]["peer"]=substr($v,6);
					if(substr($v,0,3)=="me:") $parsed["con"][$connection]["me"]=substr($v,4);
					if(substr($v,0,4)=="uri ") $parsed["con"][$connection]["uri"]=substr($v,4);
					if(substr($v,0,10)=="delay_pool") $parsed["con"][$connection]["delay_pool"]=substr($v,11);
					
					if(preg_match('/out.offset \d+, out.size (\d+)/',$v,$matches)){
						$parsed["con"][$connection]["bytes"]=$matches[1];
					}
					if(preg_match('/start \d+\.\d+ \((\d+).\d+ seconds ago\)/',$v,$matches)){
						$parsed["con"][$connection]["seconds"]=$matches[1];
					}
				}
			}
		}
		return $parsed;
	}
	function implode_with_keys($array, $glue) {
		foreach ($array as $key=>$v){
			$ret[]=$key.'='.htmlspecialchars($v);
		}
		return implode($glue, $ret);
	}
	function makeHtmlReport($data,$resolveip=false,$hosts_array=array(),$use_js=true)
	{
		global $group_by;
		if($this->use_sessions){
			session_name('SQDATA');
			session_start();
		}

		$total_avg = $total_curr = 0;
		// resort data array
		$users=array();
$group_by="host";
echo "switch($group_by)<BR>";
		switch($group_by){
			case "host":
			$group_by_name="Host";
			$group_by_key='return $ip;';
			break;
			case "username":
			$group_by_name="User";
			$group_by_key='return $v["username"];';
			break;
			default:
			die("wrong group_by!");
		}

		foreach($data["con"] as $key => $v){
			if(substr($v["uri"],0,13)=="cache_object:") continue; // skip myself
			$ip=substr($v["peer"],0,strpos($v["peer"],":"));
			if(isset($hosts_array[$ip])){
				$ip=$hosts_array[$ip];
			}
			// i use ip2long() to make ip sorting work correctly
			elseif($resolveip){
				$hostname=gethostbyaddr($ip);
				if($hostname==$ip) $ip=ip2long($ip);// resolve failed
				else $ip=$hostname;
			}			
			else{
				$ip=ip2long(substr($v["peer"],0,strpos($v["peer"],":")));
			}
			$v['connection'] = $key;
			if(!isset($v["username"])) $v["username"]="N/A";
			$users[eval($group_by_key)][]=$v;
		}
		ksort($users);
		$refresh=0;
		if(isset($_GET["refresh"]) && !isset($_GET["stop"])) $refresh=(int)$_GET["refresh"];
		$text='';
		if(count($GLOBALS["configs"])==1) $servers=$GLOBALS["squidhost"].':'.$GLOBALS["squidport"];
		else{	
			$servers='<select onchange="this.form.submit();" name="config">';
			foreach ($GLOBALS["configs"] as $key=>$v){
				$servers.='<option '.($GLOBALS["config"]==$key?' selected="selected" ':'').' value="'.$key.'">'.htmlspecialchars($v).'</option>';
			}
			$servers.='</select>';
		}
		$text.='<div class="header"><form method="get" action="'.$_SERVER["PHP_SELF"].'">'.
		'Squid RealTime stat for the '.$servers.' proxy server ('.$data["server_version"].').<br/>'.
		'Auto refresh: <input name="refresh" type="text" size="4" value="'.$refresh.'"/> sec. <input type="submit" value="Update"/> <input name="stop" type="submit" value="Stop"/> Created at: <tt>'.date("h:i:s d/m/Y").'</tt><br/>'.
		'</div>'.
		'<table class="result" align="center" width="100%" border="0">'.
		'<tr>'.
		'<th>'.$group_by_name.'</th><th>URI</th>'.
		($this->use_sessions?'<th>Curr. Speed</th><th>Avg. Speed</th>':'').
		'<th>Size</th><th>Time</th>'.
		'</tr>';
		$ausers=$acon=0;
		unset($session_data);
		if (isset($_SESSION['time']) && ((time() - $_SESSION['time']) < 3*60) && isset($_SESSION['sqdata']) && is_array($_SESSION['sqdata'])) {
			//only if the latest data was less than 3 minutes ago
			$session_data = $_SESSION['sqdata'];
		}
//echo "$_SESSION['sqdata']";
		$table='';
		foreach($users as $key=>$v){
			$ausers++;
			$table.='<tr><td style="border-right:0;" colspan="2"><b>'.(is_int($key)?long2ip($key):$key).'</b></td>'.
			'<td style="border-left:0;" colspan="5">&nbsp;</td></tr>';
			$user_avg = $user_curr = $con_color =  0;				
			foreach ($v as $con){
				if(substr($con["uri"],0,7)=="http://" || substr($con["uri"],0,6)=="ftp://"){
					if(strlen($con["uri"])>SQSTAT_SHOWLEN) $uritext=htmlspecialchars(substr($con["uri"],0,SQSTAT_SHOWLEN)).'</a> ....';
					else $uritext=htmlspecialchars($con["uri"]).'</a>';
					$uri='<a target="_blank" href="'.htmlspecialchars($con["uri"]).'">'.$uritext;
				}
				else $uri=htmlspecialchars($con["uri"]);
				$acon++;
				//speed stuff
				$con_id = $con['connection'];
				$is_time = time();
				$curr_speed=0;
				$avg_speed=0;
				if (isset($session_data[$con_id]) && $con_data = $session_data[$con_id] ) {
					// if we have info about current connection, we do analyze its data
					// current speed
					$was_time = $con_data['time'];
					$was_size = $con_data['size'];
					if ($was_time && $was_size) {
						$delta = $is_time - $was_time;
						if ($delta == 0) {
							$delta = 1;
						}
						if ($con['bytes'] >= $was_size) {
							$curr_speed = ($con['bytes'] - $was_size) / 1024 / $delta;
						}
					} else {
						$curr_speed = $con['bytes'] / 1024;
					}
					
					//avg speed
					$avg_speed = $con['bytes'] / 1024;
					if ($con['seconds'] > 0) {
						$avg_speed /= $con['seconds'];
					}
				}
				
				$new_data[$con_id]['time'] = $is_time;
				$new_data[$con_id]['size'] = $con['bytes'];
				
				//sum speeds
				$total_avg += $avg_speed;
				$user_avg += $avg_speed;
				$total_curr += $curr_speed;
				$user_curr += $curr_speed;
				
				if($use_js) $js='onMouseout="hideddrivetip()" onMouseover="ddrivetip(\''.$this->implode_with_keys($con,'<br/>').'\')"';
				else $js='';
				$table.='<tr'.( (++$con_color % 2 == 0) ? ' class="odd"' : '' ).'><td id="white"></td>'.
				'<td nowrap '.$js.' width="80%" >'.$uri.'</td>';
				if($this->use_sessions){
					$table .= '<td nowrap align="right">'.( (round($curr_speed, 2) > 0) ? sprintf("%01.2f KB/s", $curr_speed) : '' ).'</td>'.
					'<td nowrap align="right">'.( (round($avg_speed, 2) > 0) ? sprintf("%01.2f KB/s", $avg_speed) : '' ). '</td>';
				}
				$table .= '<td nowrap align="right">'.$this->filesize_format($con["bytes"]).'</td>'.
				'<td nowrap align="right">'.$this->duration($con["seconds"],"short").'</td>'.
				'</tr>';
			}
			if($this->use_sessions){
				$table.=sprintf("<tr><td colspan=\"2\"></td><td align=\"right\" id=\"highlight\">%01.2f KB/s</td><td align=\"right\" id=\"highlight\">%01.2f KB/s</td><td colspan=\"2\"></td>",
				$user_curr, $user_avg);
			}
			
		}
		$_SESSION['time'] = time();
		if(isset($new_data)) $_SESSION['sqdata'] = $new_data;
		$stat_row='';
		if($this->use_sessions){
			$stat_row.=sprintf("<tr class=\"total\"><td><b>Total:</b></td><td align=\"right\" colspan=\"5\"><b>%d</b> users and <b>%d</b> connections @ <b>%01.2f/%01.2f</b> KB/s (CURR/AVG)</td></tr>",
			$ausers, $acon, $total_curr, $total_avg);
		}
		else {
			$stat_row.=sprintf("<tr class=\"total\"><td><b>Total:</b></td><td align=\"right\" colspan=\"5\"><b>%d</b> users and <b>%d</b> connections</td></tr>",
			$ausers, $acon);
		}
		if($ausers==0){
			$text.='<tr><td colspan=6><b>No active connections</b></td></tr>';
		}
		else {
			$text.=$stat_row.$table.$stat_row;
		}
		$text .= '</table>'.
		'<p class="copyleft">&copy; <a href="mailto:samm@os2.kiev.ua?subject=SqStat '.SQSTAT_VERSION.'">Alex Samorukov</a>, 2006</p>';
		return $this->formatXHTML($text,$refresh,$use_js);
	}
}
?>