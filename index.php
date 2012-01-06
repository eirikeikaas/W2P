<?php


try{
	
	// Get configuration and constants
	require_once('system/constants.php');
	if(!file_exists('config.php'))
		throw new Exception("Missing configuration file");
	require_once('config.php');
	
	// Load Utilities for autoloader and ErrorNotifier for error handling
	require_once('system/W2P/System/W2P_System_Utilities.php');
	require_once('system/W2P/System/W2P_System_ErrorNotifier.php');
	
	// Set autoloaders and error handler
	spl_autoload_register("W2P_System_Utilities::autoload");
	set_error_handler("W2P_System_ErrorNotifier::handler");
	
	// Get class-aliases
	require_once('system/alias.php');
	
	// Register 3rd party classes to autoload
	W2PSU::autoload_register("Slim","system/3rd/Slim/Slim.php");
	W2PSU::autoload_register("Slim_View","system/3rd/Slim/View.php");
	W2PSU::autoload_register("Live","system/3rd/Live.php");
	W2PSU::autoload_register("ORM","system/3rd/Idiorm.php");
	W2PSU::autoload_register("ORMWrapper","system/3rd/Paris.php");
	W2PSU::autoload_register("Model","system/3rd/Paris.php");
	W2PSU::autoload_register("TwigView","system/3rd/Slim/Views/TwigView.php");
	W2PSU::autoload_register("lessc","system/3rd/Lessc.php");
	
	// Start debug benchmarking
	W2PSB::start("main");
	
	// Set up database
	W2PDB::setup(W2P_MYSQL_USER, W2P_MYSQL_PSWD, W2P_MYSQL_DB, W2P_MYSQL_HOST);
	
	// Get enviroment
	$env = W2PSE::getEnv();
	
	// Set Twig directory 
	TwigView::$twigDirectory = 'system/3rd/Twig/';
	
	// Make sure that CSS is the latest LESS
	lessc::ccompile(MEDIA_DIR.'less/admin.less', MEDIA_DIR.'css/admin.css');
	lessc::ccompile(MEDIA_DIR.'less/preview.less', MEDIA_DIR.'css/preview.css');
	lessc::ccompile(MEDIA_DIR.'less/styles.less', MEDIA_DIR.'css/styles.css');
	
	// Start Slim wrapper
	$route = new W2PR(array(
		'view' => new TwigView,
		'templates.path' => TEMPLATE_DIR,
		'mode' => $env
	));
	
	// Get app
	$app = &$route->app();
	
	// Set routing parameters
	W2PR::set("includes", array(
		'<link href="http://fonts.googleapis.com/css?family=Marvel:700" rel="stylesheet" type="text/css">',
		'<link rel="stylesheet" href="media/css/styles.css" />'
	));
		
	// Start Auth
	$auth = new W2PA();
	
	// INDEX
	$app->get('/', function() use($app, $route, $auth){
		if($auth->hasClearance(1)){
			return $route->render("index.html");
		}else{
			$app->redirect(W2P_PREFIX.'/login');
		}
	});
	
	// ADMIN
	$app->get('/', function() use($app, $route, $auth){
		if($auth->hasClearance(3)){
			return $route->render("admin.html");
		}else{
			$app->redirect(W2P_PREFIX.'/login');
		}
	});
	
	// LOGIN
	$app->get('/login', function() use($app, $route, $auth){
	
		// Check if the user already is signed in
		if($auth->hasClearance(1)){
			return $route->render("index.html");
		}else{
			$brid = $app->request()->post('email');
			$pswd = $app->request()->post('password');
			
			// Check if we have formdata to check
			if(isset($brid,$pswd)){
			
				// Login and redirect
				if($auth->login($brid, $pswd)){
					$app->redirect(W2P_PREFIX.'/');
				}else{
					// Render the login form with an error
					return $route->render("login.html", array("error" => "Feil brukernavn eller passord"));
				}
			}else{
				// Render loginform
				return $route->render("login.html");
			}
		}
	});
	
	// LOGOUT
	$app->get('/logout', function() use($app, $route, $auth){
		if($auth->logout()){
			$app->redirect(W2P_PREFIX.'/');
		}else{
			throw new Exception("Something really weird just happened…");
		}
	});
	
	// Run routing
	$route->run();
	
	// Stop debug benchmarking
	W2PSB::stop("main");
	W2PSB::log("main");
	
}catch(Exception $e){

	// Exception handling
	require_once('system/W2P/System/W2P_System_ErrorNotifier.php');
	W2P_System_ErrorNotifier::formatException($e);

}

?>