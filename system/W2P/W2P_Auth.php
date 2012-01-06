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
	private static $salt = W2P_BASE_SALT;
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
		$pswd = self::password($pswd);
				
		// Check that brid is email
		if(preg_match('/^([_a-z0-9-.])+@([a-z0-9-]+.)+[a-z]{2,4}$/i',$brid) === 1){
			$user = Model::factory("Users")->where_equal("email", $brid)->where_equal("password", $pswd)->find_one();
			
			// Check that query returned a row
			if(count($user)){
				$hash = hash('sha256',hash('sha256',(time()*rand())*5000));
				$remote = hash('sha256',hash('sha256',(time()*rand())*5000).$user->salt);
				setcookie('auth',self::encrypt($hash));
				setcookie('user',self::encrypt($user->email));
				$_SESSION[$hash] = $remote;
				$user->hash = hash('sha256',$remote.$this->ua);
				$user->save();
				return true;
			}else{
				return false;
			}
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
	
	public function logout(){
		setcookie('auth','',1);
		setcookie('user','',1);
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
			if(isset($_COOKIE['auth'])){
				$remote = self::decrypt($_COOKIE['auth']);
				$c_email = self::decrypt($_COOKIE['user']);
				
				if(isset($remote) && strlen($remote) === 64){
				
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
			$remote = self::decrypt($_COOKIE['auth']);
			$c_email = self::decrypt($_COOKIE['user']);
			
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
	 
	public static function encrypt($text, $salt = W2P_COOKIE_SALT){ 
        return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))); 
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
    
    public static function decrypt($text, $salt = W2P_COOKIE_SALT){ 
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))); 
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