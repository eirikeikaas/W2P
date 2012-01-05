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

class W2P_Auth extends W2P{
	private $conn = false;
	private static $salt = 'dsfg464$YT$&"#F#SAFG$&/H3tyf3$#"FSf#"%FHASG3$&%"$fytd3Y4$"&#/(';
	private $ua = "";
	private static $loggedIn = false;
	private static $admin = false;
	private static $user = false;
	private static $loginhash = "";
	
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
	
	public function __construct(){
		//$this->conn = &DB::getInstance();
		@session_start();
		$this->ua = md5($_SERVER['HTTP_USER_AGENT']);

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
	
	public function login($brid, $pswd){
		$c_brid = $this->conn->escape_string($brid);
		$c_pswd = self::password($pswd);
				
		// Check that brid is email
		if(preg_match('/^([_a-z0-9-.])+@([a-z0-9-]+.)+[a-z]{2,4}$/i',$c_brid) === 1){
			$q = $this->conn->query("SELECT * FROM tr_users WHERE email = '{$c_brid}' AND password = '{$c_pswd}' LIMIT 1");
			$q = Model::factory("Users")->where_equal("email", $c_brid)->where_equal("password", $c_pswd)->find_one();
			
			// Check that query didn't fail
			if($q !== false){
				// Check that query returned a row
				if($q->num_rows === 1){
					$r = $q->fetch_assoc();
					$hash = hash('sha256',hash('sha256',(time()*rand())*5000));
					$remote = hash('sha256',hash('sha256',(time()*rand())*5000).$this->salt);
					setcookie('auth',$hash);
					setcookie('user',$r['email']);
					$_SESSION[$hash] = $remote;
					$remote = hash('sha256',$remote.$this->ua);
					$this->conn->query("UPDATE tr_users SET loginhash = '{$remote}' WHERE id = {$r['id']}");
					header("Location: ".$_SERVER['PHP_SELF']);
				}else{
					header("Location: ?e=login");
				}
			}else{
				throw new Exception($this->conn->error);
			}
		}else{
			header("Location: ?e=login");
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
	
	public function logout(){
		setcookie('auth','');
		setcookie('user','');
		unset($_SESSION['auth']);
		unset($_SESSION['admin']);
		session_destroy();
		header("Location: ".$_SERVER['PHP_SELF']);
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
	
	public function isLoggedIn(){
		if(self::$loggedIn===false){
			if(isset($_COOKIE['auth']) && strlen($remote) === 64){
				$remote = $_COOKIE['auth'];	
				$c_email = $this->conn->escape_string(urldecode($_COOKIE['user']));
			
				self::$loginhash = $loginhash = hash('sha256',$_SESSION[$remote].$this->ua);
				$q = $this->conn->query("SELECT *, CONCAT(firstname, ' ', lastname) AS name FROM tr_users WHERE loginhash = '{$loginhash}' AND email = '{$c_email}' LIMIT 1");
				
				self::$user = $q->fetch_assoc();
				
				if($q !== false){
					if($q->num_rows === 1){
						self::$loggedIn = true;
						return true;
					}
				}else{
					throw new Exception("Could not authenticate due to an SQL error");
				}
			}else{
				return false;
			}
		}else{
			return true;
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
	
	public function isAdmin($id = false){
		if(self::$admin===false && !$id){
			$remote = $_COOKIE['auth'];
			$c_email = $this->conn->escape_string($_COOKIE['user']);
			
			if(strlen($remote) === 64){
				$loginhash = hash('sha256',$_SESSION[$remote].$this->ua);
				$q = $this->conn->query("SELECT admin FROM tr_users WHERE loginhash = '{$loginhash}' AND email = '{$c_email}' LIMIT 1");
				
				if($q !== false){
					if($q->num_rows === 1){
						$r = $q->fetch_assoc();
						self::$admin = (bool)$r['admin'];
						return self::$admin;
					}else{
						return false;
					}
				}else{
					throw new Exception("Could not authenticate due to an SQL error");
				}
			}
		}else if($id !== false){
			$q = $this->conn->query("SELECT admin FROM tr_users WHERE id = {$c_id}");
			
			if($q !== false){
				if($q->num_rows === 1){
					$r = $q->fetch_assoc();
					return (bool)$r['admin'];
				}else{
					return false;
				}
			}else{
				throw new Exception("Could not authenticate due to an SQL error");
			}
		}else{
			return true;
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
	
	public function hasClearance($level){
		if($this->isLoggedIn()){
			return true;
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
	
	public static function userData($id = false){
		if($id !== false){
			$d = &DB::getInstance();
			$c_id = $d->escape_string($id);
			$q = $d->query("SELECT *, CONCAT(firstname, ' ', lastname) AS name FROM tr_users WHERE id = {$c_id} LIMIT 1");
			return $q->fetch_assoc();
		}else{
			return self::$user;
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
	
	public static function password($pass){
		return hash("sha256",hash("sha256",$pass.self::$salt));
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
	
	public function updatePassword($id, $old, $new, $cnf){
		$c_pswd = self::password($old);
		$c = (bool)DB::get("SELECT id FROM tr_users WHERE password = '{$c_pswd}' AND id = {$id}") !== false;
		if($new === $cnf && $this->ifAdmin($c,true)){
			$c_new = self::password($new);
			return (bool)DB::set("UPDATE tr_users SET password = '{$c_new}' WHERE id = {$id}");
		}else{
			return false;
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
	
	public static function key($check=false){
		if($check === false){
			// Generate
			$key = hash('sha256',(time()*(rand()*10)).$_SESSION[$_COOKIE['auth']]);
			$stamp = time();
			
			$_SESSION['key'] = $key;
			$_SESSION[$key] = $stamp;
			
			return $key;
		}else{
			// Check ( Session-key is the same as $check, the stamp is not older than 10 minutes and the stamp is not from the future )
			if($check===$_SESSION['key'] && $_SESSION[$check] > time()-600 && $_SESSION[$check] < time()){
				unset($_SESSION['key']);
				unset($_SESSION[$check]);
				return true;
			}else{
				return false;
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
	
	public function ifAdmin($condition, $negative = false){
		if($negative){
			if(!$this->isAdmin()){
				return $condition;
			}else{
				return true;
			}
		}else{
			if($this->isAdmin()){
				return $condition;
			}else{
				return true;
			}
		}
	}
}

?>