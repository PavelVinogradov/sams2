<?php

class SquidDescr {
	var $name = "squid";
	var $descr = "SQUID proxy servers";
	var $author = "Dmitry Chemerik";
	var $version = "1.0";
	var $api = 2;
	var $active = true;

	var $path = "squid";
	var $content = "HelpSquidForm";
	var $tray = "tray";
	var $functions = array();
	var $classFile = "squidimpl.php";
	var $className = "SquidImpl";
	var $icon = "pobject.gif";
	
	function SquidDescr() {
		$this->functions[] = "helpsquidform";
		$this->functions[] = "tray";
	}
	
	function getInstance($db, $user, $conf, $tools, $function) {
		include ($this->classFile);
		$func = new $this->className($db, $user, $conf, $tools);
		return $func->$function();
	}
	
}

?>
