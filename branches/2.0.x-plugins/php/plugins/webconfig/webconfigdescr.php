<?php

class WebConfigDescr {
    var $name = "WebConfig";
	var $descr = "WEB interface settings";
    var $author = "Pavel Vinogradov";
    var $path = "webconfig";
    var $content = "SysInfo";
    var $tray = "tray";
	var $functions = array();
	var $classFile = "webconfigimpl.php";
	var $className = "WebConfigImpl";
	var $icon = "webinterface.gif";
	
    function WebConfigDescr() {
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
