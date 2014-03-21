<?php


class TestClass{

	public function findShortCodeCaracter(){
		$input = "366249217001__BACKUP_1395407166.mbz";
		
		$pattern = "/(?i)(.+?)__BACKUP/";
		$fileNames = array();
		preg_match($pattern, $input,$test);
		
		var_dump($test);
	}
	
}

$testClass = new TestClass();
$testClass->findShortCodeCaracter();


