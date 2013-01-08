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
$ret["ret"] = 0;

if ( isset( $_GET["op"] ) && ($_GET["op"] == "update") ){
	
}else if ( isset( $_GET["op"] ) && ($_GET["op"] == "insert") ){
	
	// convert the input message into safe format.
	
	$indexs = array("insertType", "drawingNo", "description", "revisionNo", "date", "fileLocation", "typeName", "workOrder", "followUp");
	$s_para = array();
	
	foreach ($indexs as $index){
		$s_para[$index] = addslashes($_GET[$index]);
	}
	
	$dbQuery = new CDBQuery($gSqlObj);
	$ret = $dbQuery->Insert($s_para);
	
	//$ret = $_GET;
	
	echo json_encode($ret);
}else{
	$ret["error"] = "no op";
	echo json_encode($ret);
}