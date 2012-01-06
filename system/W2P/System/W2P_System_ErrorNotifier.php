<?php

// DO NOT REMOVE!
// THIS FILE IS NEEDED BEFORE AUTOLOADER IS READY
require_once('system/W2P/W2P_System.php');
require_once('system/W2P/System/W2P_System_Enviroment.php');

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

class W2P_System_ErrorNotifier extends W2P_System{
	private static $live = true;
	
	/**
	 * Handles verbose error reporting, NOT FOR PRODUCTION
	 *
	 * @public
	 * @static
	 * @param $msg string
	 */
	
	public static function debug($msg){
		#if(W2P_System_Enviroment::getEnv() === ARENA_ENVIROMENT_DEVELOPMENT){
		#	self::write($msg, ARENA_ERROR_DEBUG);
		#}
	}
	
	/**
	 * Notifier, handles all normal errors
	 *
	 * @public
	 * @static
	 * @param $msg string
	 * @param $level int
	 * @return string
	 */
	
	public static function notify($msg, $level, $stackpos = 1, $use_live = true){
		self::$live = $use_live;
		
		$stack = self::stack($stackpos);
		
		switch($level){
			case E_NOTICE:
			case E_USER_NOTICE:
			case W2P_NOTICE:
				
				break;
			case E_WARNING:
			case E_USER_WARNING:
			case W2P_WARNING:
				self::write($msg, $stack['file'], $stack['line'], $stack['class'], $stack['function'], $level);
				break;
			case E_ERROR:
			case E_USER_ERROR:
			case E_RECOVERABLE_ERROR:
			case W2P_FATAL:
				self::write($msg, $stack['file'], $stack['line'], $stack['class'], $stack['function'], $level);
				if(W2P_LOCKDOWN && W2P_LOCKDOWN_ALLOW){
					self::write("A severe error has occured, the site will be locked down immediately!", $stack['file'], $stack['line'], $stack['class'], $stack['function'],$level);
					
					// Collect additional stats
					$data = array(	'load' => W2P_System_Utilities::serverload(),
									'mysql' => @mysql_stat()
								);
								
					//self::live($msg, $stack['file'], $stack['line'], $stack['class'], $stack['function'], W2P_FATAL, $data);
					//self::lock();
				}else{
					//self::live($msg, $stack['file'], $stack['line'], $stack['class'], $stack['function'], W2P_FATAL);
				}
				break;
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
			case W2P_DEBUG:
			case W2P_DEPRECATION:
				self::write($msg, $stack['file'], $stack['line'], $stack['class'], $stack['function'], $level);
				
				if(W2P_System_Enviroment::getENv() === W2P_ENV_DEVELOPMENT){
					self::write($msg, $stack['file'], $stack['line'], $stack['class'], $stack['function'], W2P_DEBUG);
					//self::live($msg, $stack['file'], $stack['line'], $stack['class'], $stack['function'], W2P_DEBUG, null, true);
				}
				break;
			default:
				self::notify('Error level not reckognized',W2P_WARNING);
				break;
		}
		
		return $msg;
	}
	
	/**
	 * Logs a deprecation warning
	 *
	 * @param $function string
	 * @param $replacement string
	 * @param $version string 
	 */
	
	public static function deprecated($function, $replacement, $version, $gone){
		include_once(CORE_DIR.'lib/classes/Arena/Arena_System.php');
		include_once(CORE_DIR.'lib/classes/Arena/System/Arena_System_Enviroment.php');
		
		if(W2P_System_Enviroment::getEnv() === W2P_ENV_DEVELOPMENT){
			self::notify($function."() will be removed by Arena CM v$gone and should therefore be avoided. It will be replaced by $replacement()", W2P_DEPRECATION, 2);
		}
	} 
	
	/**
	 * If a severe error occurs, this method is there to lock down the site by creating lockdown.html
	 * which should be defined as the first index in .htaccess
	 *
	 */ 
	
	public static function lock(){
		// TODO: Lock down the site
		touch(BASE_DIR."lockdown.html");
		
		if($f = fopen(BASE_DIR."lockdown.html","w+")){
			fwrite($f,ARENA_LOCKDOWN_TEXT);
			fclose($f);
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
	 
	public static function throwException($msg, $code = 500){
		if(W2P_EXCEPTIONS){
			throw new Exception($msg, $code);
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
	 
	public static function formatException(Exception $e){
		@ob_end_clean();
		@header("HTTP/1.0 500 Server Error");
		echo <<<EOT
			<html>
				<head>
					<title>Server Error</title>
					<style type="text/css">
						body{
							margin: 50px;
							font-family: Helvetica, Arial, sans-serif;
						}
						h1{
							font-size: 48px;
						}
						p{
							font-size: 13px;
							width: 600px;
							line-height: 17px;
						}
						span{
							font-weight: bold;
							width: 45px;
							float: left;
						}
					</style>
				</head>
				<body>
					<div>
						<h1>Server Error</h1>
						<p>
							{$e->getMessage()}
						</p>
						<p>
							<span>Code: </span>{$e->getCode()}<br />
							<span>File: </span>{$e->getFile()} ({$e->getLine()})
						</p>
						<pre>{$e->getTraceAsString()}</pre>
					</div>
				</body>
			</html>
EOT;
	}
	
	/**
	 * ErrorHandler for PHP itself
	 *
	 * @public
	 * @static
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 * @param $errcontext
	 */
	
	public static function handler($errno,$errstr,$errfile,$errline,$errcontext){
		if($errno==E_USER_ERROR){
			self::notify($errstr, $errno, 2);
		}
	}
	
	/**
	 * Write to file
	 *
	 * @private
	 * @static
	 * @param $msg string
	 * @param $file string
	 */
	
	private static function write($msg, $file, $line, $class, $function, $level, $logfile = LOG_FILE){
		$str = "[".date(LOG_DATEFORMAT)." / {$_SERVER['REMOTE_ADDR']}] $level: $msg ({$class}::{$function}() in $file:$line)\n";
		
		for($i=0;$i<LOG_MAXTRY;$i++){
			if($f = fopen($logfile.".$i","a+")){
					flock($f,LOCK_EX | LOCK_NB);
					fwrite($f,$str);
					flock($f,LOCK_UN);
					fclose($f);
				return;
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
	
	private static function stack($stackpos = 1){
		$stack = debug_backtrace();
		
		$r = array();
		
		$r['file'] = $stack[$stackpos]['file'];
		$r['line'] = $stack[$stackpos]['line'];
		$r['class'] = $stack[$stackpos]['class'];
		$r['function'] = $stack[$stackpos]['function'];
		$r['type'] = $stack[$stackpos]['type'];
		
		return $r;
	}
}

?>