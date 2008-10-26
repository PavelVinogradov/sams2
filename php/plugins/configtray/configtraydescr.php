<?php

class ConfigTrayDescr {
	var $name = "configtray";
	var $descr = "SAMS administration";
	var $author = "Pavel Vinogradov";
	var $version = "1.0";
	var $api = 2;
	var $active = true;
	
	var $path = "configtray";
	var $content = "sysinfo";
	var $tray = "tray";
	var $functions = array();
	var $classFile = "configtrayimpl.php";
	var $className = "ConfigTrayImpl";
	var $icon = "config_20.jpg";
	
    function ConfigTrayDescr() {
		$this->functions[] = "sysinfo";
		$this->functions[] = "tray";
		$this->functions[] = "cuserdoc";
    }
	
	function getInstance($db, $user, $conf, $tools, $function) {
		include ($this->classFile);
		$func = new $this->className($db, $user, $conf, $tools);
		return $func->$function();
	}
	
}

?>
