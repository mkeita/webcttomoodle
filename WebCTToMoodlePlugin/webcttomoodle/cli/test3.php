<?php
$htmlContent = "Vous devez faire cette tâche avec le fichier attaché.
<a href=\"/webct/RelativeResourceManager/Template/Theorie/Tables/TableMassesatomiques.html\">TEST</a>
		testtsdsd <a href=\"test.doc\" >test.doc</a> encore du text <a src=\"http://www.google.com\" >test.doc</a>
		";
$pattern = "/((?<=href=(\"|'))|(?<=src=(\"|')))[^\"']+(?=(\"|'))/";
//$pattern = "/\(?<=href=(\"|'))/";
preg_match_all($pattern, $htmlContent, $links);

//var_dump($links[0]);

class TestClass{

	public $fileNames = array();

	function replace ($matches)
	{
		$this->fileNames[]="OK";
		$fileName = $matches[0];
	
		$pos = strpos($fileName, "ttp:");
		if($pos<=0){
			$pos = strrpos($fileName, "/");
			if($pos>0){
				$fileName = substr($matches[0], $pos+1);
			}
			$this->fileNames[]=$fileName;
			
			$fileName="@@PLUGINFILE@@/".$fileName;
		}
	
		return $fileName;
	}
	public function replaceCaracter(){
		$htmlContent = "Vous devez faire cette tâche avec le fichier attaché.
<a href=\"/webct/RelativeResourceManager/Template/Theorie/Tables/TableMassesatomiques.html\">TEST</a>
		testtsdsd <a HREF=\"test.doc\" >test.doc</a> encore du text <a src=\"http://www.google.com\" >test.doc</a>
				<IMG SRC=\"test.doc\" />
		";
	//	$pattern = "/((?<=href=(\"|'))|(?<=src=(\"|')))[^\"']+(?=(\"|'))/";
		$pattern = "/(?i)((?<=href=(\"|'))|(?<=src=(\"|')))[^\"']+(?=(\"|'))/";
		$fileNames = array();
		$test = preg_replace_callback(
				$pattern, array($this, 'replace'),
				$htmlContent);
		
		var_dump($test);
		var_dump($this->fileNames);
	}
	
}

$testClass = new TestClass();
$testClass->replaceCaracter();


$mytime = "1393930800000";

$test = substr($mytime,0, -3);

echo $test;

//foreach ($results[0] as $result){

//	var_dump($result);
//}

