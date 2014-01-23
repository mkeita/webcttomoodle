<?php
require_once 'classes/model/IBackupModel.php';

class Completion implements \IBackupModel {
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/completion.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('course_completion');
		
	
		$writer->endElement();
		$writer->endDocument();
		
	}
}