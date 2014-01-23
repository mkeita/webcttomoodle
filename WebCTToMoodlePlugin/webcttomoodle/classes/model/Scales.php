<?php
require_once 'classes/model/IBackupModel.php';

class Scales implements \IBackupModel {
	
	/**
	 * @var ScaleDefinition | Array
	 */
	public $scalesDefinition = array() ;
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/scales.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('scales_definition');
		
		foreach ($this->scalesDefinition as $scaleDefinition){
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

class ScaleDefinition {

}