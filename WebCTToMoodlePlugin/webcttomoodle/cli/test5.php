<?php


class TestClass{

	function replaceJavascript ($matches)
	{
		var_dump($matches);
		if(isset($matches[0])){
			return $matches[0];
		}
		
		return "";
	}
	
	
	function replace ($matches)
	{
		var_dump($matches);
		if(isset($matches[0])){
			
 			if(strpos($matches[0], 'javascript:showPage')!==FALSE){

				//$pattern = "/(?<=javascript:doWindowOpen\((\"|'))[^\"']+(?=(\"|'))/";
 				$pattern = "/(?<=(\"|'))[^\"']+(?=(\"|'))/";
				
 				$test = preg_match($pattern,$matches[0],$result);
				var_dump($result);
				
				return $result[0]."\" target=\"_blank";
				
			}else {
				return $matches[0];
			}
		}
		
		return "";
	}
	
	
	public function replaceCaracter(){
		$htmlContent = "Vous devez faire cette tâche avec le fichier attaché.
<a href=\"/webct/mediadb/viewEntryFrameset.jsp?id=368139658001\" class=\"glossarylink\" target=\"_blank\">TEST</a>
		testtsdsd <a HREF=\"test.doc\" >test.doc</a> encore du text <a src=\"http://www.google.com\" >test.doc</a>
				<IMG SRC=\"test.doc\" />
				<IMG src=\"@@PLUGINFILE@@/super.jpg \"/>
				<a href=\"javascript:doWindowOpen('http://elements.chimiques.free.fr/fr/proTable.php?champ=chiRA1','new_frame','width=600,height=420,menubar=1,toolbar=1,scrollbars=1,status=1,location=1,resizable=1',0)\">tableau périodique</a>
				
				<a href=\"javascript:showPage(156139291002, 156139697002, 156139695002, '/manipMed/med01Dosage2.html', 'WEBCT_NO_ANCHOR_VALUE', '3');\">tableau périodique</a>
				
				
				;
		";
		$pattern = "/(?i)(<a href=\"\/webct\/mediadb\/viewEntryFrameset.jsp.*?>)(.+?)(<\/a>)/";
		$pattern = "/((?<=href=(\"|'))|(?<=src=(\"|')))[^\"']+(?=(\"|'))/";
		$pattern = "/(?i)((?<=href=\")|(?<=src=\"))[^\"]+(?=\")/";
		//$pattern = "/(?<=javascript:doWindowOpen\((\"|'))[^\"']+(?=(\"|'))/";
		$fileNames = array();
		$test = preg_replace_callback(
				$pattern, array($this, 'replace'),
				$htmlContent);
		
		var_dump($test);
	}
	
}

$testClass = new TestClass();
$testClass->replaceCaracter();


