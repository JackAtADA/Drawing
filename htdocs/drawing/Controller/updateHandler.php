<?php

/* this page (program) will insert or update the DB records
 * 1. $_GET["op"] = "update", this will triger the update operation
 *  the remaining variable: 
 * 2. $_GET["op"] = "insert"
 *  the remaining variable:
 *	$_GET["insertType"], "drawingNo", "description", "revisionNo", "date", "typeName", "workOrder", "followUp", "fileName"
 */


require_once(__DIR__ . "/utility.php");

require_once( $gModelPath . "/connect.php" ); // get global sql obj
require_once( $gModelPath . "/DBQuery.php" );

$ret = array();
$ret["ret"] = 0;

if ( isset( $_GET["op"] ) && $_GET["op"] == "update"
	&& isset( $_GET["updateType"] ) && $_GET["updateType"] == "drawing"){
	// For update drawing
	$indexs = array("updateType", "updateDrawingNoTo", "originalDrawingNo", "description");
	$s_para = array();
	
	foreach ($indexs as $index){
		$s_para[$index] = addslashes($_GET[$index]);
	}
	
	$dbQuery = new CDBQuery($gSqlObj);
	$ret = $dbQuery->UpdateDrawing($s_para);
	echo json_encode($ret);
	
}else if ( isset( $_GET["op"] ) && $_GET["op"] == "update"
	&& isset( $_GET["updateType"] ) && $_GET["updateType"] == "revision"){
	// For update revision
	// detect the field of the "fileReplaceOption" to decide how to treat the original file of the record.
	$indexs = array("updateType", "drawingNo", "revisionNo", "date", "workOrder", "followUp", "typeName", "fileReplaceOption", "recordID");
	$s_para = array();
	
	foreach ($indexs as $index){
		$s_para[$index] = addslashes($_GET[$index]);
	}
	$s_para["fileLocation"] = NULL;
	if ( isset($_GET["fileName"]) && !empty($_GET["fileName"]) ){
		global $gUploadPath;
		// cannot apply addslashes to $_GET("fileName"); because it is not for sql directly.
		$s_para["fileLocation"] = $gUploadPath . "/" . $_GET["fileName"];
	}
	
	
	$dbQuery = new CDBQuery($gSqlObj);
	$ret = $dbQuery->UpdateRevision($s_para);
	//$ret = array();
	//$ret["error"] = "test";
	//$ret["ret"] = 0;
	echo json_encode($ret);
}else if ( isset( $_GET["op"] ) && ($_GET["op"] == "insert") ){
	// convert the input message into safe format.
	$indexs = array("insertType", "drawingNo", "description", "revisionNo", "date", "typeName", "workOrder", "followUp", "fileName");
	$s_para = array();
	
	foreach ($indexs as $index){
		$s_para[$index] = addslashes($_GET[$index]);
	}
	
	$s_para["fileLocation"] = NULL;
	//$s_para["fileType"] = "None";
	if ( isset($_GET["fileName"]) && !empty($_GET["fileName"]) ){
		global $gUploadPath;
		//$s_para["fileLocation"] = $gUploadPath . "/" . addslashes($_GET["fileName"]);
		// cannot apply addslashes to $_GET("fileName"); because it is not for sql directly.
		$s_para["fileLocation"] = $gUploadPath . "/" . $_GET["fileName"];
	}
	$dbQuery = new CDBQuery($gSqlObj);
	$ret = $dbQuery->Insert($s_para);
	
	//$ret = $_GET;
	
	echo json_encode($ret);
}else{
	$ret["error"] = "no op";
	echo json_encode($ret);
}