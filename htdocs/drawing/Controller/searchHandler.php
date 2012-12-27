<?php

/* this page (program) will search the database in N ways.
 * 1. Search the record by Drawing Number, Description or Date
 * and return with column Drawing Number, Description and Date
 * 2. Search the record by Drawing Numebr and return the whole record
 * (including Drawing Number, Description, Date, revision number, ...)
 */


require_once(__DIR__ . "/utility.php");

require_once( $gModelPath . "/connect.php" ); // get global sql obj
require_once( $gModelPath . "/DBQuery.php" );


if ( isset( $_GET["search"] ) && ($_GET["search"] == "range") ){
	//default value
	$s_dateOperation = ">=";
	$s_drawingNo = "";
	$s_description = "";
	$s_revisionDate = "0000-00-00 00:00:00";
	if ( isset($_GET["drawingNo"]) ){
		$s_drawingNo = addslashes($_GET["drawingNo"]);
	}
	if ( isset($_GET["description"]) ){
		$s_description = addslashes($_GET["description"]);
	}
	if ( isset($_GET["revisionDate"]) ){
		$s_revisionDate = addslashes($_GET["revisionDate"]);
	}
	if ( isset($_GET["dateOperation"]) && !empty($_GET["dateOperation"]) ){
		$s_dateOperation = addslashes($_GET["dateOperation"]);
	}
	
	$dbQuery = new CDBQuery($gSqlObj);
	//print_r($gSqlObj);
	$ret = $dbQuery->RangeSearch($s_drawingNo, $s_description, $s_dateOperation, $s_revisionDate);
	echo json_encode($ret);
}else if ( isset( $_GET["search"] ) && ($_GET["search"] == "specific") ){
	// default value -- no result output
	$s_recordID = -1;
	
	if ( isset($_GET["recordID"]) ){
		$s_recordID = intval($_GET["recordID"]);
	}
	
	$dbQuery = new CDBQuery($gSqlObj);
	
	$ret = $dbQuery->SpecificSearch($s_recordID);
	echo json_encode($ret);
}else{
	$ret["ret"] = -1;
	$ret["error"] = "no op";
	$ret["isSet"] = isset( $_GET["search"] );
	$ret["search"] = "range";
	echo json_encode($ret);
}