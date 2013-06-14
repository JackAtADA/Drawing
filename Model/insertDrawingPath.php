<?php
require_once __DIR__ . '/connect.php';
global $gSqlObj;

$sqlQuery = sprintf("select SQL_CALC_FOUND_ROWS
`LatestRev`.`RecordID`,
`LatestRev`.`DrawingNo`,
`LatestRev`.`Date`, `LatestRev`.`RevisionNo`,
`LatestRev`.`FileLocation`
From `Drawing` inner join (
select `DrawingRevision`.`RecordID`, `DrawingRevision`.`DrawingNo`, Date(`DrawingRevision`.`Date`) as `Date`, `DrawingRevision`.`RevisionNo`, `DrawingRevision`.`FileLocation`
From `DrawingRevision` inner join(
select `DrawingRevision`.`DrawingNo`, Max(`DrawingRevision`.`RevisionNo`) as `RevisionNo`
from `DrawingRevision`
Group by `DrawingRevision`.`DrawingNo`
		) as `ss` on `DrawingRevision`.`DrawingNo` = `ss`.`DrawingNo` and `DrawingRevision`.`RevisionNo` = `ss`.`RevisionNo`
		) as `LatestRev` on `Drawing`.`DrawingNo` = `LatestRev`.`DrawingNo`
		" );

//global $gSqlObj;
$result = $gSqlObj->query($sqlQuery);

$pathPrefix = "01 DRAWING\\\\";

while($row = $result->fetch_assoc()){
	
	if ($row["DrawingNo"] == "--AR-001"){
		continue;
	}
	
	$pattern = "/(.*?)-(.*?)-(.*)/";
	$ret = preg_match($pattern, $row["DrawingNo"], $matches);
	if ($ret >= 0){
		$parsePath[intval($row["RecordID"])] = $pathPrefix.$matches[1]."\\\\".$matches[2]."\\\\"
			.$matches[1].$matches[2].$matches[3].".dwg";
		//echo $parsePath[intval($row["RecordID"])]."\n";
	}else {
		echo "pattern missmatch:".$row["DrawingNo"];
		return ;
	}
}

foreach ( $parsePath as $recordID => $path){
	echo $recordID . "=>" . $path."\n";
	$sqlQuery = sprintf("
			Update `DrawingRevision` set `FileLocation` = '%s'
			Where `RecordID` = %d", $path, $recordID);
	
	//echo $sqlQuery."\n";
	$gSqlObj->query($sqlQuery);
}
?>