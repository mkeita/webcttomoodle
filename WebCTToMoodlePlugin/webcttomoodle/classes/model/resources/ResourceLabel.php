<?php
require_once 'classes/model/IBackupModel.php';

class ResourceLabel implements \IBackupModel {
	
	public $id;//id="4" moduleid="57" modulename="assign" contextid="107"
	public $moduleid;
	public $modulename="folder";	
	public $contextid ;
	
	public $labelId; // id="1"
	

	public $name;
	public $intro;
	public $introformat;
	
    public $timemodified;
    
	/**
	 * @var int|Array
	 */
	public $filesIds = array();
	
	public function toXMLFile($repository) {
		
		$writer = new XMLWriter();
		$writer->openURI($repository.'/label.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
			$writer->startElement('activity');
				$writer->writeAttribute('id', $this->id);
				$writer->writeAttribute('moduleid', $this->moduleid);
				$writer->writeAttribute('modulename', $this->modulename);
				$writer->writeAttribute('contextid', $this->contextid);
				
				$writer->startElement('folder');
					$writer->writeAttribute('id', $this->folderId);
					$writer->startElement('name');
						$writer->text($this->name);
					$writer->endElement();
					$writer->startElement('intro');
						$writer->text($this->intro);
					$writer->endElement();
					$writer->writeElement('introformat',$this->introformat);
					$writer->writeElement('timemodified',$this->timemodified);
						
				$writer->endElement();
			$writer->endElement();
		$writer->endDocument();
	}
}