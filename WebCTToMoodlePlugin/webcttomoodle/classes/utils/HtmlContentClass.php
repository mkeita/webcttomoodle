<?php
class HtmlContentClass {
	
	public $filesName = array();
	
	protected $quote = "'";
	
	public $errors =array();
	

	private function replaceAllLinksCallBack($matches)
	{
		$fileName = $matches[0];
	
		//error_log("FILE URL = ".$fileName);
		//echo 'Original == '.$fileName.'<br/>';

		$pos = strpos($fileName, "@@PLUGINFILE@@");
		if($pos!==FALSE){
			return $fileName;
		}
		
		$pos = strpos($fileName, "#");
		if($pos!==FALSE){
			return $fileName;
		}
						
		if(strpos($fileName, 'javascript:doWindowOpen')!==FALSE){
		
			$pattern = "/(?<=javascript:doWindowOpen\((\"|'))[^\"']+(?=(\"|'))/";
			preg_match($pattern,$fileName,$result);
		
			$fileName = $result[0];
		
			$pos = strpos($fileName, "http:");
			if($pos!==FALSE){
				if($this->quote=="\""){
					return $fileName."\" target=\"_blank";
				}else {
					return $fileName."' target='_blank";
				}
			}
		}
		
		if(strpos($fileName, 'javascript:showWindow')!==FALSE){
			//On ne peut pas traiter ce cas..
			$this->errors[$fileName] = $fileName;
			//Suppress this link normaly done via the glossary entry..
			//$fileName = "";
			return $fileName;
		}
		
		$pos = strpos($fileName, "http:");
		if($pos!==FALSE){
			return $fileName;
		}

		if(strpos($fileName, 'javascript:showPage')!==FALSE){
			//On ne peut pas traiter ce cas..
			$pattern = "/(?<=(\"|'))[^\"']+(?=(\"|'))/";
			preg_match($pattern,$fileName,$result);
				
			$pos = strpos($result[0], ".html");
			if($pos===FALSE){
				$this->errors[$fileName] = $fileName;
				return $fileName;
			}
			
			$fileName = $result[0];
				
			$pos = strrpos($fileName, "/");
			if($pos>0){
				$fileName = substr($fileName, $pos+1);
			}
		}
				
		$pos = strrpos($fileName, "/");
		if($pos>0){
			$fileName = substr($matches[0], $pos+1);
		}
		
		//Suppress with space
		$fileName = trim($fileName);
		//echo 'Final == '.$fileName.'<br/>';
		
		$this->filesName[]=$fileName;
		
			
		$fileName="@@PLUGINFILE@@/".$fileName;
		
		return $fileName;
	}
	
	public function replaceAllLinks($htmlContent){
		
		//Use the quote --> '
		$this->quote="'";
		
		$pattern = "/(?i)((?<=href=".$this->quote.")|(?<=src=".$this->quote."))[^".$this->quote."]+(?=(".$this->quote."))/";
		$this->fileNames = array();
		
		$htmlContent = preg_replace_callback(
				$pattern, array($this, 'replaceAllLinksCallBack'),
				$htmlContent);
		
		//Use the quote --> "		
		$this->quote="\"";
		
		$pattern = "/(?i)((?<=href=".$this->quote.")|(?<=src=".$this->quote."))[^".$this->quote."]+(?=(".$this->quote."))/";
		$this->fileNames = array();
		
		$htmlContent = preg_replace_callback(
				$pattern, array($this, 'replaceAllLinksCallBack'),
				$htmlContent);
		
		
		return $htmlContent;
	}
	
	
	//GLOSSARY LINKS
	
	private function removeGlossaryLinksCallBack($matches)
	{
		if(isset($matches[2])){
			return $matches[2];
		}
		
		return "";
	}
	
	public function removeGlossaryLinks($htmlContent){
	
		$pattern = "/(?i)(<a href=\"\/webct\/mediadb\/viewEntryFrameset.jsp.*?>)(.+?)(<\/a>)/";
		$htmlContent= preg_replace_callback(
				$pattern, array($this, 'removeGlossaryLinksCallBack'),
				$htmlContent);
		
		$pattern = "/(?i)(<a href=\"javascript:showWindow\(.*?>)(.+?)(<\/a>)/";
		$htmlContent= preg_replace_callback(
				$pattern, array($this, 'removeGlossaryLinksCallBack'),
				$htmlContent);
		
		return $htmlContent;
		
	}
	
	
	//SPECIAL FOR LINK BETWEEN CHAPTER IN BOOK (LEANING MODULE)
	
	protected $chapterFileLinks;
	public function updateBookChapterLinks($htmlContent, $chapterFileLinks){
		$this->chapterFileLinks = $chapterFileLinks;
		
		//Use the quote --> '
		$this->quote="'";
	
		$pattern = "/(?i)((?<=href=".$this->quote.")|(?<=src=".$this->quote."))[^".$this->quote."]+(?=(".$this->quote."))/";
		$this->fileNames = array();
	
		$htmlContent = preg_replace_callback(
				$pattern, array($this, 'updateBookChapterLinksCallBack'),
				$htmlContent);
	
		//Use the quote --> "
		$this->quote="\"";
	
		$pattern = "/(?i)((?<=href=".$this->quote.")|(?<=src=".$this->quote."))[^".$this->quote."]+(?=(".$this->quote."))/";
		$this->fileNames = array();
	
		$htmlContent = preg_replace_callback(
				$pattern, array($this, 'updateBookChapterLinksCallBack'),
				$htmlContent);
	
	
		return $htmlContent;
	}
	
	private function updateBookChapterLinksCallBack($matches)
	{
		$fileName = $matches[0];
		
		$pos1 = strpos($fileName, "@@PLUGINFILE@@");
		$pos2 = strpos($fileName, ".html");
		if($pos1===FALSE || $pos2===FALSE){
			return $fileName;
		}
		
		$realFileName = substr($fileName,$pos1+15);
		
		if(isset($this->chapterFileLinks[$realFileName])){
			$fileName = '$@BOOKVIEWBYIDCH'.$this->chapterFileLinks[$realFileName].'@$';
		}
		
		return $fileName;
	}
}