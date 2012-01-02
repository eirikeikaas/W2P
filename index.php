<?php

// Get configuration and class aliases
require_once('system/config.php');
require_once('system/alias.php');

// Load Utilities for autoloader and ErrorNotifier for error handling
require_once('system/W2P/System/W2P_System_Utilities.php');
require_once('system/W2P/System/W2P_System_ErrorNotifier.php');

// Set autoloaders and error handler
spl_autoload_register("W2PSU::autoload");
set_error_handler("W2PSEN::handler");

// Register 3rd party classes to autoload
W2PSU::autoload_register("Slim","system/3rd/Slim/Slim.php");
W2PSU::autoload_register("Live","system/3rd/Live.php");

// Start debug benchmarking
W2PSB::start("main");

// Start output buffering
ob_start("ob_gzhandler");

// Set up database
W2PDB::setup(W2P_MYSQL_USER, W2P_MYSQL_PSWD, W2P_MYSQL_DB, W2P_MYSQL_HOST);

// Get enviroment
$env = W2PSE::getEnv();

// Start Slim
$app = new Slim();

// Stop debug benchmarking
W2PSB::stop("main");
W2PSB::log("main");

?>