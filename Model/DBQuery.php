<?php

require_once( __DIR__ . "/login.php" );
require_once( __DIR__ . "/log.php");
require_once( __DIR__ . "/modelConfigure.php");

class CDBQuery{
	// the return value of many functions will be an associate array
	// ret["rowResult"] : the fetched row of sql result
	// ret["error"] : a string contain error message if error occur.
	// ret["ret"] : value indicate the search operation is successful or not
	// 			0 for fail, 1 for success
	public $sqlObj;
	private $loginObj;
	public function __construct($sqlObj){
		$this->sqlObj = $sqlObj;
		$this->loginObj = new CLogin($this->sqlObj);
	}
	public function RangeSearch($s_drawingNo, $s_description, 
								$s_dateOperation, $s_revisionDate){						
		$loginObj = new CLogin($this->sqlObj);
		$ret = array();
		$ret["rowResult"] = NULL;
		$ret["ret"] = 0;
		if ( !$loginObj->IsLogin() ){
			$ret["error"] = "Permission deny, user has not login";
			return $ret;
		}
		
		$loginObj->RefreshLoginTime();
		
		$sqlQuery = "select `DrawingRevision`.`RecordID`, 
					 `Drawing`.`DrawingNo`, `Drawing`.`Description`,
					 Date(`DrawingRevision`.`Date`) as `Date`, `DrawingRevision`.`RevisionNo`,
					 `DrawingRevision`.`FileLocation`, `FileType`.`TypeName`
					 from `Drawing` left join `DrawingRevision`
					 on `Drawing`.`DrawingNo` = `DrawingRevision`.`DrawingNo`
					 left join `FileType` 
					 on `DrawingRevision`.`FileType` = `FileType`.`TypeID`
					 where 1 ";
					
		$sqlQuery .= sprintf("and `Drawing`.`DrawingNo` like '%%%s%%' 
							  and `Drawing`.`Description` like '%%%s%%'
							  and `DrawingRevision`.`Date` %s '%s'",
							  $s_drawingNo, $s_description, $s_dateOperation,
							  $s_revisionDate);
							  
		$sqlQuery .= "order by `Drawing`.`DrawingNo` asc, `DrawingRevision`.`Date` desc";
		$result = $this->sqlObj->query($sqlQuery);
		//$logObj = new CLog("C:\\xampp\\Model\\Log\\Log.txt");
		//$logObj->WriteLog($sqlQuery);
		
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			//echo "<br>" . $sqlQuery;
			return $ret;
		}else{
			$rowResult = array();
			//while($row = $result->fetch_row() ){
			while($row = $result->fetch_assoc() ){
				$rowResult[] = $row; // ignore the final row which is null
			}
			$ret["rowResult"] = $rowResult;
			$ret["ret"] = 1;
			return $ret;
		}
	}
	
	public function SpecificSearch($s_recordID){
		$ret = array();
		$ret["rowResult"] = NULL;
		$ret["ret"] = 0;
		if ( !$this->loginObj->IsLogin() ){
			$ret["error"] = "Permission deny, user has not login";
			return $ret;
		}
		
		$this->loginObj->RefreshLoginTime();
		
		$sqlQuery = "select 
					 `DrawingRevision`.`DrawingNo`, 
					 `Drawing`.`Description`,
					 `DrawingRevision`.`RevisionNo`, 
					 `FileType`.`TypeName`, 
					 `DrawingRevision`.`FileLocation`,
					 Date(`DrawingRevision`.`Date`) as `Date`, 
					 `DrawingRevision`.`WorkOrder`,
					 `DrawingRevision`.`FollowUp`
					 from `DrawingRevision` left join `Drawing`
					 on `DrawingRevision`.`DrawingNo` = `Drawing`.`DrawingNo`
					 left join `FileType` 
					 on `DrawingRevision`.`FileType` = `FileType`.`TypeID`
					 where 1 ";
					
		$sqlQuery .= sprintf("and `DrawingRevision`.`RecordID` = %d",$s_recordID);
		
		$result = $this->sqlObj->query($sqlQuery);
		
		
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			//echo "<br>" . $sqlQuery;
			return $ret;
		}else{
			$rowResult = array();
			while($row = $result->fetch_assoc() ){
				$rowResult[] = $row; // ignore the final row which is null
			}
			$ret["rowResult"] = $rowResult;
			$ret["ret"] = 1;
			return $ret;
		}
	}
	public function Insert($s_para){
		$ret = array();
		$ret["rowResult"] = NULL;
		$ret["ret"] = 0;
		if ( !$this->loginObj->IsLogin() ){
			$ret["error"] = "Permission deny, user has not login";
			return $ret;
		}
		
		$this->loginObj->RefreshLoginTime();
		if ($s_para["insertType"] == "drawing"){
			$ret = $this->InsertDrawing($s_para);
		}else if ($s_para["insertType"] == "revision"){
			$ret = $this->InsertDrawingRevision($s_para);
		}
		return $ret;
	}
	private function InsertDrawingRevision($s_para){
		$ret = array();
		$ret["rowResult"] = NULL;
		$ret["ret"] = 0;
		
		// acquire lock
		$sqlQuery = "Lock Tables `Drawing` Read, `DrawingRevision` Write, `FileType` Write";
		$result = $this->sqlObj->query($sqlQuery);
		
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			return $ret;
		}
		
		// check DrawingNo 's reference
		$sqlQuery = sprintf(
			"Select `DrawingNo` From `Drawing` where `DrawingNo` = '%s'" ,
			$s_para["drawingNo"]
		);
		$result = $this->sqlObj->query($sqlQuery);
		
		$drawingNo = NULL;
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			$this->UnlockTables();
			return $ret;
		}else{
			$row = $result->fetch_assoc();
			if ( !$row ){ // no record
				$ret["error"] = "DrawingNo is invalid.\nPlease create a drawing with DrawingNo".$s_para["drawingNo"];
				$this->UnlockTables();
				return $ret;
			}
		}
		return $this->_InsertDrawingRevision($s_para);
		
	}
	private function _InsertDrawingRevision($s_para){
		$ret = array();
		$ret["rowResult"] = NULL;
		$ret["ret"] = 0;
		
		// check typeName 's reference
		// type name can't be detect by extension of upload file 
		// because some old files are missing soft copy (that is no upload file).
		$sqlQuery = sprintf(
			"Select `TypeID` From `FileType` where `TypeName` like '%s'" ,
			$s_para["typeName"]
		);
		$result = $this->sqlObj->query($sqlQuery);
		
		$typeID = 0;
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			$this->UnlockTables();
			return $ret;
		}
		
		$row = $result->fetch_assoc();
		if ($row){
			$typeID = $row["TypeID"];
		}else { // no record
			// because of changing implementation strategy. it will ask user to download the file
			// instead of asking user to start the default application.
			$sqlQuery = sprintf(
				"Insert Into `FileType` (`TypeName`, `DefaultApplication`) Values ('%s', NULL)",
				$s_para["typeName"]
			);
			$result = $this->sqlObj->query($sqlQuery);
			if ($this->sqlObj->error){
				$ret["error"] = "SQL error:" . $this->sqlObj->error;
				$this->UnlockTables();
				return $ret;
			}else{
				$typeID = $this->sqlObj->insert_id;
			}
		}
		
		
		// insert record;
		$fileLocation = $s_para["fileLocation"];
		$workOrder = $this->FormatSQLStringValue($s_para["workOrder"]);
		$followUp = $this->FormatSQLStringValue($s_para["followUp"]);
		
		$sqlQuery = sprintf(
			"INSERT into `DrawingRevision` (
				`DrawingNo`, `RevisionNo`, `Date`, `FileType`, 
				`FileLocation`, `WorkOrder`, `FollowUp`
			) VALUES (
				'%s', '%s', '%s', '%d', NULL, %s, %s
			)",
			$s_para["drawingNo"], $s_para["revisionNo"], $s_para["date"],
			$typeID, $workOrder, $followUp
		);
		
		$result = $this->sqlObj->query($sqlQuery);
		
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			$this->UnlockTables();
			return $ret;
		}else{
			$recordID = $this->sqlObj->insert_id;
		}
		
		// deal with the file location
		if ($fileLocation != NULL){
			$newLocation = $this->MoveFileToServerInside($fileLocation, $recordID);
			
			if ($newLocation == NULL){
				$ret["error"] = "Move file fail";
				$this->UnlockTables();
				return $ret;
			}
			// update location in table
			$sqlQuery = sprintf(
				"Update `DrawingRevision` Set `Filelocation`='%s'
				Where `RecordID`='%s'",
				$newLocation, $recordID
			);
			
			$result = $this->sqlObj->query($sqlQuery);
			
			if ($this->sqlObj->error){
				$ret["error"] = "SQL error:" . $this->sqlObj->error;
				$this->UnlockTables();
				return $ret;
			}
		}
		$ret["ret"] = 1;
		$this->UnlockTables();
		return $ret;
	}
	private function InsertDrawing($s_para){
		// insert drawing including the first revision of it
		$ret = array();
		$ret["rowResult"] = NULL;
		$ret["ret"] = 0;
		
		// acquire lock
		$sqlQuery = "Lock Tables `Drawing` Write, `DrawingRevision` Write, `FileType` Write";
		$result = $this->sqlObj->query($sqlQuery);
		
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			return $ret;
		}
		
		// Insert Drawing
		$sqlQuery = sprintf(
			"Insert Into `Drawing` (`DrawingNo`, `Description`) Values ('%s', '%s')",
			$s_para["drawingNo"], $s_para["description"]
		);
		$result = $this->sqlObj->query($sqlQuery);
		
		//$drawingNo = NULL; ??
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			$this->UnlockTables();
			return $ret;
		}
		
		return $this->_InsertDrawingRevision($s_para);
	}
	public function UpdateDrawing($s_para){
		$ret = array();
		$ret["ret"] = 0;
		
		// acquire lock
		$sqlQuery = "Lock Tables `Drawing` Write";
		$result = $this->sqlObj->query($sqlQuery);
		
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			return $ret;
		}
		
		// update operation
		$sqlQuery = sprintf( 
			"Update `Drawing`
			Set `DrawingNo` = '%s', `Description` = '%s'
			Where `DrawingNo` = '%s'", 
			$s_para["updateDrawingNoTo"], $s_para["description"], $s_para["originalDrawingNo"]);
		
		$result = $this->sqlObj->query($sqlQuery);
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			$this->UnlockTables();
			return $ret;
		}
		// else, it means update successful
		$ret["ret"] = 1;
		$this->UnlockTables();
		return $ret;
	}
	public function UpdateRevision($s_para){
		$ret = array();
		$ret["ret"] = 0;
		
		// acquire lock
		$sqlQuery = "Lock Tables `Drawing` Read, `DrawingRevision` Write, `FileType` Write";
		$result = $this->sqlObj->query($sqlQuery);
		
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			return $ret;
		}
		
		// check DrawingNo 's reference
		$sqlQuery = sprintf(
			"Select `DrawingNo` From `Drawing` where `DrawingNo` = '%s'" ,
			$s_para["drawingNo"]
		);
		$result = $this->sqlObj->query($sqlQuery);
		
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			$this->UnlockTables();
			return $ret;
		}else{
			$row = $result->fetch_assoc();
			if ( !$row ){ // no record
				$ret["error"] = "DrawingNo is invalid.\nPlease create a drawing with DrawingNo".$s_para["drawingNo"];
				$this->UnlockTables();
				return $ret;
			}
		}
		
		// check FileType 's reference
		$tmp = $this->CheckAndCreateFileTypeReference($s_para["typeName"]);
		if ( isset($tmp["error"]) ){
			$ret["error"] = $tmp["error"];
			$this->UnlockTables();
			return $ret;
		}
		$typeID = $tmp["typeID"];
		
		// preparing other variable for the update field
		$fileLocation = NULL;
		if ($s_para["fileReplaceOption"] == "replaceWithFile"){
			$fileLocation = $this->MoveFileToServerInside($s_para["fileLocation"], $s_para["recordID"]);
			//$fileLocation = $this->FormatSQLStringValue($fileLocation);
			//$ret["error"] = $fileLocation;
			//return $ret;
		}
		// else, it will skip the update option with fileLocation.
		
		$workOrder = $this->FormatSQLStringValue($s_para["workOrder"]);
		$followUp = $this->FormatSQLStringValue($s_para["followUp"]);
		
		// update operation
		$sqlQuery = sprintf( 
			"Update `DrawingRevision`
			Set `DrawingNo` = '%s', `RevisionNo` = '%s', `Date` = '%s', 
			`FileType` = %d, `WorkOrder` = %s,	`FollowUp` = %s",
			$s_para["drawingNo"], $s_para["revisionNo"], $s_para["date"],
			$typeID, $workOrder, $followUp
		);
		if ($s_para["fileReplaceOption"] == "removeOldFile" 
			|| $s_para["fileReplaceOption"] == "replaceWithFile"){
			$fileLocation = $this->FormatSQLStringValue($fileLocation);
			$sqlQuery .= sprintf(", `FileLocation` = %s", $fileLocation);
		}
		$sqlQuery .= sprintf( " Where `recordID` = %d", $s_para["recordID"]);
		
		$result = $this->sqlObj->query($sqlQuery);
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			$this->UnlockTables();
			return $ret;
		}
		// else, it means update successful
		$ret["ret"] = 1;
		$this->UnlockTables();
		return $ret;
	}
	
	private function CheckAndCreateFileTypeReference($s_typeName){
		// return typeID if it is already existed in DB
		// return a new ID if it is not existed
		// $ret["typeID"] : store typeID
		// $ret["error"] : store error message if any error occurs
		
		// check typeName 's reference
		// type name can't be detect by extension of upload file 
		// because some old files are missing soft copy (that is no upload file).
		$sqlQuery = sprintf(
			"Select `TypeID` From `FileType` where `TypeName` like '%s'" ,
			$s_typeName
		);
		$result = $this->sqlObj->query($sqlQuery);
		
		$ret = array();
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			return $ret;
		}
		
		$row = $result->fetch_assoc();
		if ($row){
			$ret["typeID"] = intval($row["TypeID"]);
		}else { // no record
			// because of changing implementation strategy. it will ask user to download the file
			// instead of asking user to start the default application.
			// Thus, column `DefaultApplication` in table `FileType` will be always NULL
			$sqlQuery = sprintf(
				"Insert Into `FileType` (`TypeName`, `DefaultApplication`) Values ('%s', NULL)",
				$s_typeName
			);
			$result = $this->sqlObj->query($sqlQuery);
			if ($this->sqlObj->error){
				$ret["error"] = "SQL error:" . $this->sqlObj->error;
			}else{
				$ret["typeID"] = $this->sqlObj->insert_id;
			}
		}
		return $ret;
	}
	private function UnlockTables(){
		$sqlQuery = "Unlock Tables";
		$result = $this->sqlObj->query($sqlQuery);
		return;
	}
	private function FormatSQLStringValue($var){
		$sqlVar = NULL;
		if ( !empty($var) ){
			$sqlVar = "'".$var."'"; // add a quote for sql
		}else{
			$sqlVar = "NULL"; // add a NULL value to sql
		}
		return $sqlVar;
	}
	private function MoveFileToServerInside($targetFile, $recordID){
		// return the string of "safe" file path after moving the file.
		// if any error occurs, return NULL
		
		$extension = strrchr($targetFile, ".");
		global $gFilePool;
		$newPath = $gFilePool . "/" . $recordID . $extension;
		
		$ret = rename($targetFile, $newPath);
		if ($ret == true){
			// according the security issus, it will only store the file name
			// the download process will not allow to download the file not in $gFilePool 
			
			return $recordID . $extension;
			
			//return addslashes($newPath);
		}else{
			return NULL;
		}
	}
}
?>