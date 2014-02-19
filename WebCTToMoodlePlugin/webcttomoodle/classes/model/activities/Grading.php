<?php
require_once 'classes/model/IBackupModel.php';

class Grading implements \IBackupModel {
	/**
	 * @var Area|Array
	 */
	public $areas = array();
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/grading.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
			$writer->startElement('areas');
			
				foreach ($this->areas as $area){
					$area->toXML($writer);
				}
					
			$writer->endElement();
		$writer->endDocument();
	}
}

class Area {
	public $id;
	
	public $areaname;
	public $activemethod;
	
	public $definitions;
	
	public function __construct($id,$areaname,$activemethod){
		$this->id=$id;
		$this->areaname=$areaname;
		$this->activemethod=$activemethod;
	}
	
	/**
	 * @param XMLWriter $writer
	 */
	public function toXML(&$writer){
		$writer->startElement('area');
			$writer->writeAttribute("id", $this->id);
			
			$writer->writeElement("areaname", $this->areaname);
			$writer->writeElement("activemethod", $this->activemethod);
			
			$writer->startElement('definitions');
			$writer->endElement();
		
		$writer->endElement();
	}
	
}