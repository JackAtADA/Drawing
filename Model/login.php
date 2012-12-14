<?php
// it will get a sqlObj for querying DB
require_once( __DIR__ . "/connect.php" ); 

session_start();

define("SQLERROR", -2);
define("LOGINFAIL", -1);
define("LOGINSUCC", 1);
define("STATELOGIN", 2);
define("STATELOGOUT", 3);

class CLogin{
	public $sID; // sessionID
	public $loginTime; // for expire checking
	public $user; // user name
	private $sqlObj;
	public function __construct($sqlObj){
		$this->sID = date("Ymd") . session_id();
		if ( !isset($_SESSION[$this->sID]) ){
			$_SESSION[$this->sID] = false;
		}
		if ( !isset($_SESSION["loginTime"]) ){
			$_SESSION["loginTime"] = 0;
		}
		$this->sqlObj = $sqlObj;
	}
	public function IsLogin(){
		$ret = $this->IsExpire();
		$_SESSION[$this->sID] &= !$ret;
		return $_SESSION[$this->sID];
	}
	public function Login($s_userName, $md5_password){
		// return value
		// LOGINSUCC: login successful
		// LOGINFAIL: login fail
		// SQLERROR: sql error
		$_SESSION["loginTime"] = $_SERVER["REQUEST_TIME"];
		$query = sprintf(
			"select `UserName`, `Password`, `Permission` from `user`
			where `UserName` = '%s' and `Password` = '%s'
			",
			$s_userName, $md5_password
		);
		$result = $this->sqlObj->query($query);
		if ($this->sqlObj->error){
			return SQLERROR;
		}else if( $row = $result->fetch_row() ){
			$_SESSION[$this->sID] = true;
			$_SESSION["DBPermission"] = $row[2];
			return LOGINSUCC;
		}else {
			return LOGINFAIL;
		}
	}
	private function IsExpire(){
		$interval = $_SERVER["REQUEST_TIME"] - $_SESSION["loginTime"];
		//echo "interval:" . $interval. "<bar>";
		if ( $interval > 1800) { // 30 mins
			return true;
		}else {
			return false;
		}
	}
	public function Logout(){
		$_SESSION[$this->sID] = false;
		unset($_SESSION["DBPermission"]);
		unset($_SESSION["loginTime"]);
	}
}

/*
$loginObj = new CLogin($sqlObj);

$ret = $loginObj->Login("TestUser", md5("adaits"));

echo "login ret" .$ret. "<br>";

echo "is Login:" . $loginObj->IsLogin()."<br>";
*/



?>