<?php
require_once 'classes/model/IBackupModel.php';

class Outcomes implements \IBackupModel {
	
	/**
	 * @var OutcomeDefinition | Array
	 */
	public $outcomeDefinitions = array() ;
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/outcomes.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('outcomes_definitions');
		
		foreach ($this->outcomeDefinitions as $outcomeDefinition){
// 			$writer->startElement('role');
// 				$writer->writeAttribute('id',$role->id);
				
// 				$writer->writeElement('name',$role->name);
// 				$writer->writeElement('shortname',$role->shortname);
// 				$writer->writeElement('nameincourse',$role->nameincourse);
// 				$writer->writeElement('description',$role->description);
// 				$writer->writeElement('sortorder',$role->sortorder);
// 				$writer->writeElement('archetype',$role->archetype);
// 			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		
	}
}

class OutcomeDefinition {

}