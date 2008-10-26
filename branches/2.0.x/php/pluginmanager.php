<?php
include ("./samstools.php");
class PluginManager {

	var $SamsDb;
	var $SamsUser;
	var $SamsConf;
	var $SamsTools;

	var $path = "./plugins/";
	var $plugins = array();
	var $coreFunctions = array("listPlugins");
	
	var $apiVersion = 2;
	
	function PluginManager ($db, $user, $conf) {
		$this->SamsDb = $db;
		$this->SamsUser = $user;
		$this->SamsConf = $conf;
		$this->SamsTools = new SamsTools();
		
		$this->loadPlugins();
	}

	function loadPlugins () {
    		$count = 0;
	
		if ($handle = opendir($this->path)) {
			while (false !== ($file = readdir($handle))) {
				if (is_dir($this->path . $file) and $file != '.' and $file != '..') {
					$descr = $this->loadPlugin($file);
					if ($descr !== null)
						$this->plugins[$count++]  = $descr;
				}
			}
		}
	}

	function loadPlugin ($name) {
		$descr = null;
		$class = $name . "descr";
		$file = $this->path . $name ."/". $class.".php";
		if (is_file($file)) {
			include	 ($file);
			$descr = new $class();
			
			//Implement Auto-Disable plugin with old API
			//if ($descr->apiVersion < $this->apiVersion) {
			//	$descr->active = false;
			//}
		}
		return $descr; 
	}

	function listPlugins () {
		$result = "";
		$result .= "<TABLE CLASS=samstable><br>";
		$result .= "<TR><TH>Plugin</TH><TH>Descr</TH><TH>Author</TH><TH>Version</TH><TH>Status</TH></TR>";
		for ($i = 0; $i < count($this->plugins); $i++) {
			$result .= "<TR><TD>" . $this->plugins[$i]->name ."</TD><TD>". $this->plugins[$i]->descr ."</TD><TD>". $this->plugins[$i]->author .
			"</TD><TD>" . $this->plugins[$i]->version ."</TD><TD>". ($this->plugins[$i]->active ? "Enabled" : "Disabled" )."</TD></TR>";
		}
		$result .= "</TABLE>";
		
		return $result;
	}
	
	function findModule($name) {
		$result = null;
		
		for ($i = 0; $i <= count($this->plugins); $i++) {
			//TODO: Optimize this
			if (strcasecmp($name, $this->plugins[$i]->name) == 0 && $this->plugins[$i]->active)
				$result =  $this->plugins[$i];
		}
		
		return $result;
	}
	
	function hasFunction($plugin, $name) {
		$result = false;
		
		for ($i = 0; $i <= count($plugin->functions); $i++) {
			if (strcasecmp($name, $plugin->functions[$i]) == 0) {
				$result = true;
			}
		}
		
		return $result;
	}
	
	function dispatch($module, $function) {
		$result = "";
		
		if ($module == "core") {
			if (strcasecmp ($function, "listPlugins") == 0) {
				$result .= "<SCRIPT>\n";
				$result .= "parent.basefrm.location.href=\"main.php?module=core&function=list\";\n";
				$result .= "</SCRIPT>\n";
			} else if (strcasecmp ($function, "list") == 0) {
				$result = $this->listPlugins();
			} else {
				$result = "I don't like you";
			}
		} else {
			$plugin = $this->findModule($module);
		
			if ($plugin !== null) {
				if ($this->hasFunction($plugin, $function)) {
					//$result = $plugin->getInstance($this->SamsDb, $this->SamsUser, $this->SamsConf, $this->SamsTools)->$function();
					$result = $plugin->getInstance($this->SamsDb, $this->SamsUser, $this->SamsConf, $this->SamsTools, $function);
				} else {
					$result = "Function ". $function ." not implemented in module ". $module;
				}
			} else {
				$result = "Module ". $module ." not installed";
			}
		}
		
		return $result;
	}
	
	function generateTree() {
		$result = "   plugins = insFld(foldersTree, gFld2(\" Plugins  \", \"main.php?module=core&function=listPlugins\", \"proxy.gif\"))\n";
		for ($i = 0; $i < count($this->plugins); $i++) {
			$result .= "     plugin".$i." = insFld(plugins, gFld2(\"".$this->plugins[$i]->descr."\", \"tray.php?module=".$this->plugins[$i]->name."&function=".$this->plugins[$i]->tray."\", \"".$this->plugins[$i]->icon."\"))\n";
		}
		
		return $result;
	}
}
?>
