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
 
class W2P_Routing extends W2P{
	private $app = false;
	private static $arguments = array();
	
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
	
	public function __construct($args){
		// Start Slim
		$this->app = new Slim($args);
		$this->app->error('W2P_System_ErrorNotifier::formatException');
	
		// Configure production mode
		$this->app->configureMode(W2P_ENV_PRODUCTION, function () {
    		$this->app->config(array(
				'log.enable' => true,
				'log.path' => LOG_DIR,
				'debug' => false
	 	   ));
		});
		
		// Keep an eye out for PHP 5.4 so that we can use lexical in OO context
		$tmp = $this->app;

		// Configure development mode
		$this->app->configureMode(W2P_ENV_DEVELOPMENT, function () use($tmp) {
 		   $tmp->config(array(
				'log.enable' => false,
				'debug' => true
		    ));
		});
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
	
	public function &app(){
		return $this->app;
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
	
	public function render($template, $args = array()){
		return $this->app->render($template, array_merge(self::$arguments, $args));
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
	 
	public static function set($name, $value, $add = false){
		if($add){
			if(isset(self::$arguments[$name]) && is_array(self::$arguments[$name])){
				array_push(self::$arguments[$name]);
			}else if(isset(self::$arguments[$name])){
				self::$arguments[$name] = array(self::$arguments[$name], $value);
			}else{
				self::$arguments[$name] = array($value);
			}
		}else{
			self::$arguments[$name] = $value;
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
	 
	public static function get($name){
		return self::$arguments[$name];
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
	
	public function run(){
		// Run Slim
		$this->app->run();
	}
}

?>