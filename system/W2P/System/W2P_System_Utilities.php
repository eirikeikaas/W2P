<?php

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
		W2PSEN::debug('Autoload:'.$classname);
		if(!class_exists($classname)){
					
			if(substr($classname,0,5) == "W2P"){
				// Split into folders
				$classpath = array_splice(explode("_",$classname),0,-1);
				$sep = (count($classpath)>0)?"/":"";
				$classpath = implode("/",$classpath);
				$path = CORE_DIR."system/".$classpath.$sep.$classname.'.php';
		
				if(file_exists($path))	{
					include_once($path);	
				}
				
				W2PSEN::debug("Loaded class: ".$classname);
			}else if($keys = array_key_exists($classname,self::$custom_paths)){
				$path = CORE_DIR.self::$custom_paths[$classname];
				W2PSEN::debug("SPL: ".$path);
				
				if(file_exists($path)){
					include_once($path);
				}
			}else{
				// Oops. What to do?
				W2PSEN::log("Could not reckognize class. Bad format or 3rd-party?",W2P_FATAL);
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
}

?>