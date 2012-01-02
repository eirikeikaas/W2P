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

class W2P_System_Benchmark extends W2P_System{
	static $timers = array();
	
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
	
	static function start($name){
		self::$timers[$name] = array();
		self::$timers[$name]['start'] = microtime(true);
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
	
	static function mark($name, $mark){
		$t = microtime(true);
		if($mark !== ("stop" || "start") && self::$timers[$name]['stop'] === null && self::$timers[$name]['start'] !== null){
			self::$timers[$name][$mark] = $t;
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
	
	static function stop($name){
		self::$timers[$name]['stop'] = microtime(true);
		self::$timers[$name]['result'] = self::$timers[$name]['stop'] - self::$timers[$name]['start'];
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
	
	static function log($name){
		$f = fopen(LOG_DIR.'benchmark.log', 'a+');
		fwrite($f, '['.date("d-m-Y h:i:s").'] '.$name.': '.self::$timers[$name]['result']."\n");
		fclose($f);
	}
}

?>