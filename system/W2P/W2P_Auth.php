<?php

class W2P_Auth extends W2P{
	private $conn = false;
	private static $salt = 's5uygd/&%$#fjh3SDHGF3&##sdrgf&%eygASHJG""#$&%RS';
	private $ua = "";
	private static $loggedIn = false;
	private static $admin = false;
	private static $user = false;
	private static $loginhash = "";
	
	public function __construct(){
		$this->conn = &DB::getInstance();
		@session_start();
		$this->ua = md5($_SERVER['HTTP_USER_AGENT']);

	}
	
	public function login($brid, $pswd){
		$c_brid = $this->conn->escape_string($brid);
		$c_pswd = self::password($pswd);
				
		// Check that brid is email
		if(preg_match('/^([_a-z0-9-.])+@([a-z0-9-]+.)+[a-z]{2,4}$/i',$c_brid) === 1){
			$q = $this->conn->query("SELECT * FROM tr_users WHERE email = '{$c_brid}' AND password = '{$c_pswd}' LIMIT 1");
			
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
				echo "WHOOPS";
				echo $this->conn->error;
			}
		}else{
			header("Location: ?e=login");
		}
	}
	
	public function logout(){
		setcookie('auth','');
		setcookie('user','');
		unset($_SESSION['auth']);
		unset($_SESSION['admin']);
		session_destroy();
		header("Location: ".$_SERVER['PHP_SELF']);
	}
	
	public function isLoggedIn(){
		if(self::$loggedIn===false)	{
			$remote = $_COOKIE['auth'];	
			$c_email = $this->conn->escape_string(urldecode($_COOKIE['user']));
			
			if(strlen($remote) === 64){
				self::$loginhash = $loginhash = hash('sha256',$_SESSION[$remote].$this->ua);
				$q = $this->conn->query("SELECT *, CONCAT(firstname, ' ', lastname) AS name FROM tr_users WHERE loginhash = '{$loginhash}' AND email = '{$c_email}' LIMIT 1");
				
				self::$user = $q->fetch_assoc();
				
				if($q !== false){
					if($q->num_rows === 1){
						self::$loggedIn = true;
						return true;
					}
				}else{
					return "ERRRRRR";
				}
			}
		}else{
			return true;
		}
	}
	
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
					return false;
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
				return false;
			}
		}else{
			return true;
		}
	}
	
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
	
	public static function password($pass){
		return hash("sha256",hash("sha256",$pass.self::$salt));
	}
	
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