<?php

$drawingFileRoot = $argv[1];


function process_dir($dir,$recursive = FALSE) {
	if (is_dir($dir)) {
		if (preg_match("/xSuperseded/", $dir)){
			return array();
		}
		$dirList = array();
		for ($list = array(),$handle = opendir($dir); (FALSE !== ($file = readdir($handle)));) {
        	if (($file != '.' && $file != '..') && (file_exists($path = $dir.'/'.$file))) {
          		if (is_dir($path) && ($recursive)) {
		            $list = array_merge($list, process_dir($path, TRUE));
		            $dirList[] = array( 'filename' => $dir.'/'.strtoupper($file) );
		            if ($path != $dir.'/'.strtoupper($file)){
		            	rename($path, $dir.'/'.strtoupper($file) );
		            }
		            //var_dump($dirList);
		        } else {
		        	$entry = array('filename' => $file, 'dirpath' => $dir);
					
					//---------------------------------------------------------//
					//                     - SECTION 1 -                       //
					//          Actions to be performed on ALL ITEMS           //
					//-----------------    Begin Editable    ------------------//
					$entry['modtime'] = filemtime($path);
					
					//-----------------     End Editable     ------------------//
					
					
					do if (!is_dir($path)) {
						//---------------------------------------------------------//
						//                     - SECTION 2 -                       //
						//         Actions to be performed on FILES ONLY           //
						//-----------------    Begin Editable    ------------------//
					
						$entry['size'] = filesize($path);
						/*
						if (strstr(pathinfo($path,PATHINFO_BASENAME),'log')) {
						    if (!$entry['handle'] = fopen($path,"r")) $entry['handle'] = "FAIL";
						}*/
						//$newFileName = strtoupper($file);
						
						$ret = preg_match("/(.*)(\..*)/", $file, $matches);
						if ($ret){
							$newFileName = strtoupper($matches[1]).strtolower($matches[2]);
						}else{
							$newFileName = strtoupper($file);
						}
						
						//$ret = rename($targetFile, $newPath);
						
					 	//-----------------     End Editable     ------------------//
					 	break;
					} else {
						//---------------------------------------------------------//
						//                     - SECTION 3 -                       //
						//       Actions to be performed on DIRECTORIES ONLY       //
						//-----------------    Begin Editable    ------------------//
	
						//-----------------     End Editable     ------------------//
		            	break;
		            } while (FALSE);
		            $entry['filename'] = $dir.'/'.$newFileName;
		            $list[] = $entry;
		            if ($path != $entry['filename']){
		            	rename($path, $entry['filename']);
		            }
	          	}
        	}
		}
		$list = array_merge($list, $dirList);
    	closedir($handle);
      	return $list;
	} else return FALSE;
}
    
$result = process_dir($argv[1],TRUE);

 // Output each opened file and then close
foreach ($result as $file) {
	if (isset($file['handle']) && is_resource($file['handle'])) {
        //echo "\n\nFILE (" . $file['dirpath'].'/'.$file['filename'] . "):\n\n" . fread($file['handle'], filesize($file['dirpath'].'/'.$file['filename']));
        
        fclose($file['handle']);
    }else if(isset($file["filename"])){
    	echo $file["filename"]."\n"; 
    } 
}

?>