<?php

/* this page is the login handler
 * it support three requests, and it will use $_GET["op"] as the indicator
 * For login: $_GET["op"] == "login", 
 *	if successful, return json {ret:1}
 *	else return json { ret: something is not 1 , error: error message }
 * For logout:
 *	if successful, return json {ret:1}
 *	else return json {ret:0}
 * For loginCheck:
 *	if successful, return json {ret:1}
 *	else return json {ret:0}
 */

require_once(__DIR__ . "/utility.php");

require_once( $gModelPath . "/connect.php" ); // get sql obj

$ret = array();

$loginObj = new CLogin($gSqlObj);
if ( isset($_GET["op"])  && $_GET["op"] == "login" ){
	$s_userName = addslashes( $_GET["userName"] );
	$s_password = md5( $_GET["password"] );
	$ret["ret"] = $loginObj->Login($s_userName, $s_password);
	if ($ret["ret"] == LOGINFAIL){
		$ret["error"] = "User name or password is wrong";
	}else if ($ret["ret"] == SQLERROR){
		$ret["error"] = "DB error please contact the adminstrator";
	}
}else if ( isset($_GET["op"]) && $_GET["op"] == "logout"){
	$loginObj->Logout();
	$ret["ret"] = 1;
}else if ( isset($_GET["op"]) && $_GET["op"] == "islogin"){
	if ( $loginObj->IsLogin() == true){
		$ret["ret"] = 1;
	}else {
		$ret["ret"] = 0;
	};
}
echo json_encode($ret);
?>