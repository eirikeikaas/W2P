<?php


try{

	// Get configuration
	require_once('system/config.php');
	
	// Load Utilities for autoloader and ErrorNotifier for error handling
	require_once('system/W2P.php');
	require_once('system/W2P/W2P_System.php');
	require_once('system/W2P/System/W2P_System_Utilities.php');
	require_once('system/W2P/System/W2P_System_Enviroment.php');
	require_once('system/W2P/System/W2P_System_ErrorNotifier.php');
	
	// Set autoloaders and error handler
	spl_autoload_register("W2P_System_Utilities::autoload");
	set_error_handler("W2P_System_ErrorNotifier::handler");
	
	// Get class-aliases
	require_once('system/alias.php');
	
	// Register 3rd party classes to autoload
	W2PSU::autoload_register("Slim","system/3rd/Slim/Slim.php");
	W2PSU::autoload_register("Live","system/3rd/Live.php");
	W2PSU::autoload_register("ORM","system/3rd/idiorm.php");
	W2PSU::autoload_register("ORMWrapper","system/3rd/paris.php");
	W2PSU::autoload_register("Model","system/3rd/paris.php");
	
	// Start debug benchmarking
	W2P_System_Benchmark::start("main");
	
	// Set up database
	W2PDB::setup(W2P_MYSQL_USER, W2P_MYSQL_PSWD, W2P_MYSQL_DB, W2P_MYSQL_HOST);
	
	// Get enviroment
	$env = W2PSE::getEnv();
	
	// Start Slim
	$app = new Slim();
	
	$app->get('/', function(){
		echo "YE";
	});
	
	$app->get('/hello/:name', function($name){
		echo "YELLO, $name!";
	});
	
	// Run SLim
	$app->run();
	
	// Stop debug benchmarking
	W2PSB::stop("main");
	W2PSB::log("main");
	
}catch(Exception $e){
	echo $e;
}

?>