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
	
	public function __construct($args){
		// Start Slim
		$this->app = new Slim($args);
	
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
	
	public function &app(){
		return $this->app;
	}
	
	public function get($path){
		// Set up routes
		
	}
	
	public function run(){
		// Run Slim
		$this->app->run();
	}
}

?>