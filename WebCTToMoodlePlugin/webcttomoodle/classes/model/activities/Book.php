<?php
require_once 'classes/model/IBackupModel.php';

class ActivityBook implements \IBackupModel {
	
	public $id;//id="4" moduleid="57" modulename="assign" contextid="107"
	public $moduleid;
	public $modulename="book";	
	public $contextid ;
	
	public $bookId; // id="1"
	

	public $name;
	public $intro;
	public $introformat;
	
	public $numbering;
    public $customtitles;
    public $timecreated;    
    public $timemodified;
	
    
    /**
     * @var Chapter|Array
     */
    public $chapters = array();
    
	/**
	 * @var int|Array
	 */
	public $filesIds = array();

	
	/**
	 * @param Chapter $chapter
	 */
	public function addChapter($chapter){
		$this->chapters[]=$chapter;
		$chapter->book = $this;
	}
	
	public function toXMLFile($repository) {
		
		$writer = new XMLWriter();
		$writer->openURI($repository.'/book.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
			$writer->startElement('activity');
				$writer->writeAttribute('id', $this->id);
				$writer->writeAttribute('moduleid', $this->moduleid);
				$writer->writeAttribute('modulename', $this->modulename);
				$writer->writeAttribute('contextid', $this->contextid);
				
				$writer->startElement('book');
					$writer->writeAttribute('id', $this->bookId);
					$writer->startElement('name');
						$writer->text($this->name);
					$writer->endElement();
					$writer->startElement('intro');
						$writer->text($this->intro);
					$writer->endElement();
					$writer->writeElement('introformat',$this->introformat);
					$writer->writeElement('numbering',$this->numbering);
					$writer->writeElement('customtitles',$this->customtitles);
					$writer->writeElement('timecreated',$this->timecreated);
					$writer->writeElement('timemodified',$this->timemodified);					

					$writer->startElement('chapters');
						foreach ($this->chapters as $chapter){
							$chapter->toXMLFile($writer);
						}
					$writer->endElement();
					
				$writer->endElement();
			$writer->endElement();
		$writer->endDocument();
	}
}

class Chapter {
	public $id;

	public $pagenum;
	public $subchapter;
	public $title;
	public $content;
	public $contentformat;
	public $hidden;
	public $timemodified;
	public $importsrc;

	
	public $book;
	
	/**
	 * @param XMLWriter $writer
	 */
	public function toXMLFile(&$writer) {
		$writer->startElement('chapter');
			$writer->writeAttribute("id", $this->id);
			
			$writer->writeElement("pagenum", $this->pagenum);
			$writer->writeElement("subchapter", $this->subchapter);
			$writer->writeElement("title", $this->title);
			$writer->writeElement("content", $this->content);
			$writer->writeElement("contentformat", $this->contentformat);
			$writer->writeElement("hidden", $this->hidden);
			$writer->writeElement("timemodified", $this->timemodified);
			$writer->writeElement("importsrc", $this->importsrc);
	
		$writer->endElement();
	}
}