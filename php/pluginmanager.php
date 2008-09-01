<?php
include ("./samstools.php");
class PluginManager {

    var $SamsDb;
    var $SamsUser;
    var $SamsConf;
	var $SamsTools;

    var $path = "./plugins/";
    var $plugins = array();

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
			#print "loadPlugin:  " . $file. "\n";
			include	 ($file);
			$descr = new $class();
		}
        return $descr; 
    }

    function listPlugins () {		
        for ($i = 0; $i <= count($this->plugins); $i++) {
            print $this->plugins[$i]->name;
        }
    }
	
	function findModule($name) {
		$result = null;
		
		for ($i = 0; $i <= count($this->plugins); $i++) {
            if (strtolower($name) == $this->plugins[$i]->name)
				$result =  $this->plugins[$i];
        }	
		
		return $result;
	}
	
	function hasFunction($plugin, $name) {
		$result = 0;
		
		for ($i = 0; $i <= count($plugin->functions); $i++) {
			if (strtolower($name) == $plugin->functions[$i]) {
				$result = 1;
			}
		}
		
		return $result;
	}
	
	function dispatch($module, $function) {
		$result = "";
		$plugin = $this->findModule($module);
		
		if ($plugin !== null) {
			if ($this->hasFunction($plugin, $function)) {
				$result = $plugin->getInstance($this->SamsDb, $this->SamsUser, $this->SamsConf, $this->SamsTools)->$function();
			} else {
				$result = "Function ". $function ." not implemented in module ". $module;
			}
		} else {
			$result = "Module ". $module ." not installed";
		}
		
		return $result;
	}
	
	function generateTree() {
		$result = "   plugins = insFld(foldersTree, gFld2(\" Plugins  \", \"\", \"proxy.gif\"))\n";
		for ($i = 0; $i < count($this->plugins); $i++) {
            $result .= "     plugin".$i." = insFld(plugins, gFld2(\"".$this->plugins[$i]->descr."\", \"tray.php?module=".$this->plugins[$i]->name."&function=".$this->plugins[$i]->tray."\", \"".$this->plugins[$i]->icon."\"))\n";
        }
		
		return $result;
	}
}
?>
