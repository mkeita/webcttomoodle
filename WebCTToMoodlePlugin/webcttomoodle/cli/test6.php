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

class TestClass2{

	public function replaceSpecialCharacter(){
		$xml = "&lt;LI&gt;Plusieurs ‘MyPages’: Plusieurs pages personnalisées en fonction du contenu, des projets, etc.&#13;";
		var_dump($xml);
		
		
		//$xml = utf8_encode($xml);
		$xml2 = str_ireplace(array('','i'), ' ', $xml);
		//$xml2 = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', ' ', $xml);
		
		//\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}
		var_dump($xml2);
		var_dump($xml);
		
	}

}

$testClass2 = new TestClass2();
$testClass2->replaceSpecialCharacter();