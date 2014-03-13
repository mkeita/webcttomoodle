<?php


class TestClass{

	function replace ($matches)
	{
		if(isset($matches[2])){
			return $matches[2];
		}
		
		return "";
	}
	public function replaceCaracter(){
		$htmlContent = "Vous devez faire cette tâche avec le fichier attaché.
<a href=\"/webct/mediadb/viewEntryFrameset.jsp?id=368139658001\" class=\"glossarylink\" target=\"_blank\">TEST</a>
		testtsdsd <a HREF=\"test.doc\" >test.doc</a> encore du text <a src=\"http://www.google.com\" >test.doc</a>
				<IMG SRC=\"test.doc\" />
		";
	//	$pattern = "/((?<=href=(\"|'))|(?<=src=(\"|')))[^\"']+(?=(\"|'))/";
		$pattern = "/(?i)(<a href=\"\/webct\/mediadb\/viewEntryFrameset.jsp.*?>)(.+?)(<\/a>)/";
		$fileNames = array();
		$test = preg_replace_callback(
				$pattern, array($this, 'replace'),
				$htmlContent);
		
		var_dump($test);
	}
	
}

$testClass = new TestClass();
$testClass->replaceCaracter();


