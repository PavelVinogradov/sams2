<?php

class WebConfigDescr {
	var $name = "webconfig";
	var $descr = "WEB interface settings";
	var $author = "Pavel Vinogradov";
	var $version = "1.0";
	var $api = 2;
	var $active = true;
	
	var $path = "webconfig";
	var $content = "sysinfo";
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
	
	function getInstance($db, $user, $conf, $tools, $function) {
		include ($this->classFile);
		$func = new $this->className($db, $user, $conf, $tools);
		return $func->$function();
	}
	
}

?>
