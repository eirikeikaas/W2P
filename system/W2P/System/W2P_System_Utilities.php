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
			// Dumb mapping..
			$horrible =	 array(	'classGUI'		 => 'class/classGUI.php',
								'dbClass'		 => 'class/dbClass.php',
								'cPayment'		 => 'communication/cpayment.php',
								'cSMS'			 => 'communication/csms.php',
								'cDatabase'		 => 'database/cdatabase.php',
								'cDatabaseTable' => 'database/cdatabase.php',
								'dbComment'		 => 'dbObjects/dbComment.php',
								'dbContent'		 => 'dbObjects/dbContent.php',
								'dbFile'		 => 'dbObjects/dbFile.php',
								'dbFolder'		 => 'dbObjects/dbFolder.php',
								'dbImage'		 => 'dbObjects/dbImage.php',
								'dbObject'		 => 'dbObjects/dbObject.php',
								'dbUser'		 => 'dbObjects/dbUser.php',
								'cDebug'		 => 'debug/cdebug.php',
								'cPagination'	 => 'pagination/cpagination.php',
								'Session'		 => 'session/session.php',
							//	'Enviroment'	 => 'system/Enviroment.php',	
								'cDocument'		 => 'template/cDocument.php',	
								'cPTemplate'	 => 'template/cPTemplate.php',
								'cTemplate'		 => 'template/ctemplate.php',
								'cTime'			 => 'time/ctime.php',
								'XmlParser'		 => 'xml/xmlparser.php'
							);	
					
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
			else{
				// Oops. What to do?
				W2PSEN::log("Could not reckognize class. Bad format or 3rd-party?",W2P_FATAL);
			}
		}
	}
}

?>