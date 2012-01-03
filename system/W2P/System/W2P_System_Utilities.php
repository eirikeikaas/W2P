<?php

// DO NOT REMOVE!
// THIS FILE IS NEEDED BEFORE AUTOLOADER IS READY
require_once('system/W2P/W2P_System.php');

/**
 * 
 *
 * @author Eirik Eikaas
 * @version [REPLACE]
 * @since [REPLACE]
 * @package [REPLACE]
 * @[VISIBILITY]
 * @param [TYPE] $[NAME] [DESC]
 * @return [TYPE]
 */

class W2P_System_Utilities extends W2P_System{
	private static $custom_paths = array();
	
	/**
	 * SPL Autoloader
	 *
	 * @public
	 * @static
	 * @param $classname string
	 * @return void
	 */ 

	public static function autoload($classname){
		W2P_System_ErrorNotifier::debug('Autoload:'.$classname);
		if(!class_exists($classname)){
					
			if(substr($classname,0,3) == "W2P"){
				// Split into folders
				$classpath = array_splice(explode("_",$classname),0,-1);
				$sep = (count($classpath)>0)?"/":"";
				$classpath = implode("/",$classpath);
				$path = CORE_DIR."system/".$classpath.$sep.$classname.'.php';
		
				if(file_exists($path))	{
					include_once($path);	
				}
				
				W2P_System_ErrorNotifier::debug("Loaded class: ".$classname);
			}else if($keys = array_key_exists($classname,self::$custom_paths)){
				$path = CORE_DIR.self::$custom_paths[$classname];
				W2P_System_ErrorNotifier::debug("SPL: ".$path);
				
				if(file_exists($path)){
					include_once($path);
				}
			}else{
				// Oops. What to do?
				W2P_System_ErrorNotifier::notify("Could not reckognize class. Bad format or 3rd-party?",W2P_FATAL);
			}
		}
	}
	
	/**
	 * 
	 *
	 * @author Eirik Eikaas
	 * @version [REPLACE]
	 * @since [REPLACE]
	 * @package [REPLACE]
	 * @[VISIBILITY]
	 * @param [TYPE] $[NAME] [DESC]
	 * @return [TYPE]
	 */
	 
	 public static function autoload_register($class, $path){
	 	self::$custom_paths[$class] = $path;
	 }
	 
	 /**
	  * 
	  *
	  * @author Eirik Eikaas
	  * @version [REPLACE]
	  * @since [REPLACE]
	  * @package [REPLACE]
	  * @[VISIBILITY]
	  * @param [TYPE] $[NAME] [DESC]
	  * @return [TYPE]
	  */
	 
	 public static function autoload_unregister($class, $path){
	 	unset(self::$custom_paths[$class]);
	 }
	 
	 /**
	  * 
	  *
	  * @author Eirik Eikaas
	  * @version [REPLACE]
	  * @since [REPLACE]
	  * @package [REPLACE]
	  * @[VISIBILITY]
	  * @param [TYPE] $[NAME] [DESC]
	  * @return [TYPE]
	  */
	  
	  public static function serverload($windows = 0) {
		$os = strtolower(PHP_OS);
		if(strpos($os, "win") === false) {
			if(file_exists("/proc/loadavg")) {
				$load = file_get_contents("/proc/loadavg");
				$load = explode(' ', $load);
				return $load[0];
			}elseif(function_exists("shell_exec")) {
				$load = explode(' ', `uptime`);
				return $load[count($load)-1];
			}else{
				return "";
			}
        }elseif($windows) {
			if(class_exists("COM")) {
				$wmi = new COM("WinMgmts:\\\\.");
				$cpus = $wmi->InstancesOf("Win32_Processor");
				$cpuload = 0;
				$i = 0;
				while ($cpu = $cpus->Next()) {
					$cpuload += $cpu->LoadPercentage;
					$i++;
				}
				$cpuload = round($cpuload / $i, 2);
				return "$cpuload%";
			}else{
				return "";
			}
		}
	}
}

?>