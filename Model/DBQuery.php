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
		}else{
			$row = $result->fetch_assoc();
			if ($row){
				$typeID = $row["TypeID"];
			}else { // no record
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
		}
		
		$fileLocation = $this->FormatEmptyStringValue($s_para["fileLocation"]);
		$workOrder = $this->FormatEmptyStringValue($s_para["workOrder"]);
		$followUp = $this->FormatEmptyStringValue($s_para["followUp"]);
		
		$sqlQuery = sprintf(
			"INSERT into `DrawingRevision` (
				`DrawingNo`, `RevisionNo`, `Date`, `FileType`, 
				`FileLocation`, `WorkOrder`, `FollowUp`
			) VALUES (
				'%s', '%s', '%s', '%d', %s, %s, %s
			)",
			$s_para["drawingNo"], $s_para["revisionNo"], $s_para["date"],
			$typeID, $fileLocation, $workOrder, $followUp
		);
		
		$result = $this->sqlObj->query($sqlQuery);
		
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			//$this->UnlockTables();
			//return $ret;
		}else{
			$ret["ret"] = 1;
		}
		
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
		
		$drawingNo = NULL;
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			$this->UnlockTables();
			return $ret;
		}
		
		return $this->_InsertDrawingRevision($s_para);
	}
	public function UpdateRevision($s_para){
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
		
		// update operation
		$sqlQuery = sprintf( 
			"Update `Revision`
			Set `DrawingNo` = '%s', `RevisionNo` = '%s', `Date` = '%s', 
			`FileLocation` = %s, `FileType` = %d, `WorkOrder` = %s,	
			`FollowUp` = %s",
			$s_para["drawingNo"], $s_para["revisionNo"], $s_para["date"],
			$fileLocation, $fileType, $workOrder, $followUp
		);
	}
	private function UnlockTables(){
		$sqlQuery = "Unlock Tables";
		$result = $this->sqlObj->query($sqlQuery);
		return;
	}
	private function FormatEmptyStringValue($var){
		$sqlVar = NULL;
		if ( !empty($var) ){
			$sqlVar = "'".$var."'"; // add a quote for sql
		}else{
			$sqlVar = "NULL"; // add a NULL value to sql
		}
		return $sqlVar;
	}
}
?>