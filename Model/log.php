<?php

class CLog{
	private $fp;
	private $fName; // file name
	//private $stderrFlag;
	public function __construct($fileName = NULL){
		$this->fp = NULL;
		//$this->stderrFlag = false;
		if ( $fileName == NULL){
			$this->fp = fopen('php://stderr', 'w');
			//$this->stderrFlag = true;
		}else{
			$this->fp = fopen($fileName, "a");
			if ($this->fp == NULL){
				$this->fp = fopen('php://stderr', 'w');
				//$this->stderrFlag = true;
				//
			}
		}
	}
	public function __destruct(){
		if ($this->fp != NULL){
			fclose($this->fp);
		}
	}
	public function WriteLog($data) {
		if ( flock($this->fp, LOCK_EX) ){
			fwrite($this->fp, $data . "\r\n");
			fflush($this->fp);
			flock($this->fp, LOCK_UN);
		}
	}
	public function Test(){
		for ($i = 0; $i< 100; $i++){
			$this->WriteLog($i);
		}
		fseek($this->fp, 0);
		for ($i = 0; $i< 100; $i++){
			$this->WriteLog($i);
		}
	}
}

?>