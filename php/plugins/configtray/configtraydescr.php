<?php

class ConfigTrayDescr {
    var $name = "ConfigTray";
	var $descr = "SAMS administration";
    var $author = "Pavel Vinogradov";
    var $path = "configtray";
    var $content = "SysInfo";
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
	
	function getInstance($db, $user, $conf, $tools) {
		include ($this->classFile);
		return new $this->className($db, $user, $conf, $tools);
	}
	
}

?>
