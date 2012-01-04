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

		// Configure development mode
		$this->app->configureMode(W2P_ENV_DEVELOPMENT, function () {
 		   $this->app->config(array(
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
	
	public function get($path){
		// Set up routes
		
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