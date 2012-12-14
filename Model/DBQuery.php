<?php

require_once( __DIR__ . "/login.php" );

class CDBQuery{
	// the return value of many functions will be an associate array
	// ret["rowResult"] : the fetched row of sql result
	// ret["error"] : a string contain error message if error occur.
	public $sqlObj;
	public function __construct($sqlObj){
		$this->sqlObj = $sqlObj;
	}
	public function RangeSearch($s_drawingNo, $s_description, 
								$s_dateOperation, $s_revisionDate){						
		$loginObj = new CLogin($this->sqlObj);
		$ret = array();
		if ( !$loginObj->IsLogin() ){
			$ret["error"] = "Permission deny, user has not login";
			return $ret;
		}
		
		$sqlQuery = "select `Drawing`.`DrawingNo`, `Drawing`.`Description`,
					 `DrawingRevision`.`Date`
					 from `Drawing` left join `DrawingRevision`
					 on `Drawing`.`DrawingNo` = `DrawingRevision`.`DrawingNo` 
					 where 1 ";
					
		$sqlQuery .= sprintf("and `Drawing`.`DrawingNo` like '%%%s%%' 
							  and `Drawing`.`Description` like '%%%s%%'
							  and `DrawingRevision`.`Date` %s '%%%s%%'",
							  $s_drawingNo, $s_description, $s_dateOperation,
							  $s_revisionDate);
		$result = $this->sqlObj->query($sqlQuery);
		
		if ($this->sqlObj->error){
			$ret["error"] = "SQL error:" . $this->sqlObj->error;
			//echo "<br>" . $sqlQuery;
			return $ret;
		}else{
			$rowResult = array();
			while($row = $result->fetch_row() ){
				$rowResult[] = $row; // ignore the final row which is null
			}
			$ret["rowResult"] = $rowResult;
			return $ret;
		}
	}
}
?>