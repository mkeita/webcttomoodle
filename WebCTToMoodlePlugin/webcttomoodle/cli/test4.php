<?php
//var_dump($links[0]);

class TestClass{

	public function sendFile(){
		$ftp = ftp_connect("164.15.43.24", 21);
		
		
		
		ftp_login($ftp, "anonymous", "");
		
	
		ftp_put($ftp,"/backup-366249217001_1393318420.mbz", "D:/Documents/ULB/Moodle Migration/MoodleBackups/backup-366249217001_1393318420.mbz", FTP_BINARY);
		
		
		ftp_close($ftp);
	}
	
}

$testClass = new TestClass();
$testClass->sendFile();



//foreach ($results[0] as $result){

//	var_dump($result);
//}

