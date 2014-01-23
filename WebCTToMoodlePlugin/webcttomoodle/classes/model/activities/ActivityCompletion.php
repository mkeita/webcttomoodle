<?php
require_once 'classes/model/IBackupModel.php';

class ActivityCompletion implements \IBackupModel {
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/completion.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('completions');
		
	
		$writer->endElement();
		$writer->endDocument();
		
	}
}