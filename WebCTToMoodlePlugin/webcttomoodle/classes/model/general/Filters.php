<?php
require_once 'classes/model/IBackupModel.php';

class Filters implements \IBackupModel {
	
	/**
	 * @var FilterActive | Array
	 */
	public $filter_actives = array() ;
	
	/**
	 * @var FilterConfig | Array
	 */
	public $filter_configs = array() ;
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/filters.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('filters');
		
			$writer->startElement('filter_actives');
			foreach ($this->filter_actives as $fitlerActive){
			}
			$writer->endElement();
			
			$writer->startElement('filter_configs');
			foreach ($this->filter_actives as $filterConfig){
			}
			$writer->endElement();
				
			
		$writer->endElement();
		$writer->endDocument();
		
	}
}

class FilterActive {

}

class FilterConfig {

}