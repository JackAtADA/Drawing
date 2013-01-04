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
					 `DrawingRevision`.`Date`, `DrawingRevision`.`RevisionNo`,
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
					 `DrawingRevision`.`Date`, 
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
		$this->InsertDrawingRevision($s_para);
	}
	private function InsertDrawingRevision($s_para){
		$sqlQuery = sprintf(
			"INSERT into `DrawingRevision` (
				`DrawingNo`, `RevisionNo`, `FileType`, `FileLocation`,
				`Date`, `WorkOrder`, `FollowUp`
			) VALUES (
				'%s', '%s', %d, '%s', '%s', '%s', '%s'
			)",
			$s_para["drawingNo"], $s_para["revisionNo"], 
			$s_para["fileType"], $s_para["fileLocation"], 
			$s_para["date"], $s_para["workOrder"], 
			$s_para["followUp"]
		);
		
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
}
?>