<?php
class HtmlContentClass {
	
	public $filesName = array();
	

	private function replaceAllLinksCallBack($matches)
	{
		$fileName = $matches[0];
	
		error_log("FILE URL = ".$fileName);
		
		$pos = strpos($fileName, "ttp:");
		if($pos<=0){
			$pos = strrpos($fileName, "/");
			if($pos>0){
				$fileName = substr($matches[0], $pos+1);
			}
			$this->filesName[]=$fileName;
			
			$fileName="@@PLUGINFILE@@/".$fileName;
		}
		return $fileName;
	}
	
	public function replaceAllLinks($htmlContent){
		
		$pattern = "/(?i)((?<=href=(\"|'))|(?<=src=(\"|')))[^\"']+(?=(\"|'))/";
		$this->fileNames = array();
		
		return 	preg_replace_callback(
				$pattern, array($this, 'replaceAllLinksCallBack'),
				$htmlContent);
	}
	
	private function removeGlossaryLinksCallBack($matches)
	{
		if(isset($matches[2])){
			return $matches[2];
		}
		
		return "";
	}
	
	public function removeGlossaryLinks($htmlContent){
	
		$pattern = "/(?i)(<a href=\"\/webct\/mediadb\/viewEntryFrameset.jsp.*?>)(.+?)(<\/a>)/";
		return 	preg_replace_callback(
				$pattern, array($this, 'removeGlossaryLinksCallBack'),
				$htmlContent);
	}
	
}