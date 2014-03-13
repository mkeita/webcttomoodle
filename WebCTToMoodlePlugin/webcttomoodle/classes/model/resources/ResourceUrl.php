<?php
require_once 'classes/model/IBackupModel.php';

class ResourceUrl implements \IBackupModel {
	
	public $id;//id="4" moduleid="57" modulename="assign" contextid="107"
	public $moduleid;
	public $modulename="folder";	
	public $contextid ;
	
	public $urlId; // id="1"
	

	public $name;
	public $intro;
	public $introformat;
	
	public $externalurl;
    public $display;
    public $displayoptions;
    public $parameters;
    public $timemodified;
    
	/**
	 * @var int|Array
	 */
	public $filesIds = array();
	
	public function toXMLFile($repository) {
		
		$writer = new XMLWriter();
		$writer->openURI($repository.'/url.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
			$writer->startElement('activity');
				$writer->writeAttribute('id', $this->id);
				$writer->writeAttribute('moduleid', $this->moduleid);
				$writer->writeAttribute('modulename', $this->modulename);
				$writer->writeAttribute('contextid', $this->contextid);
				
				$writer->startElement('url');
					$writer->writeAttribute('id', $this->urlId);
					$writer->startElement('name');
						$writer->text($this->name);
					$writer->endElement();
					$writer->startElement('intro');
						$writer->text($this->intro);
					$writer->endElement();
					$writer->writeElement('introformat',$this->introformat);
					$writer->writeElement('externalurl',$this->externalurl);
					$writer->writeElement('display',$this->display);
					$writer->writeElement('displayoptions',$this->displayoptions);					
					$writer->writeElement('parameters',$this->parameters);
					$writer->writeElement('timemodified',$this->timemodified);
						
				$writer->endElement();
			$writer->endElement();
		$writer->endDocument();
	}
}