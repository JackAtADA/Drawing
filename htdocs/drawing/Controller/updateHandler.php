<?php

/* this page (program) will insert or update the DB records
 * 1. $_GET["op"] = "update", this will triger the update operation
 *  the remaining variable: 
 * 2. $_GET["op"] = "insert"
 */


require_once(__DIR__ . "/utility.php");

require_once( $gModelPath . "/connect.php" ); // get global sql obj
require_once( $gModelPath . "/DBQuery.php" );

$ret = array();
$ret["ret"] = -1;

if ( isset( $_GET["op"] ) && ($_GET["op"] == "update") ){
	
}else if ( isset( $_GET["op"] ) && ($_GET["op"] == "insert") ){
	if ( $_GET[] )
	
	$dbQuery = new CDBQuery($gSqlObj);
	
	echo json_encode($ret);
}else{
	$ret["error"] = "no op";
	echo json_encode($ret);
}