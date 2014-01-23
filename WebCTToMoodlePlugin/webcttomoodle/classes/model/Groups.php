<?php
require_once 'classes/model/IBackupModel.php';

class Groups implements \IBackupModel {
	
	/**
	 * @var Grouping | Array
	 */
	public $groupings = array() ;
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/groups.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('groups');		
			$writer->startElement('groupings');
			foreach ($this->groupings as $grouping){
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
		$writer->endElement();
		$writer->endDocument();
		
	}
}

class Grouping {

}