<?php
require_once 'classes/model/IBackupModel.php';
require_once 'classes/model/general/Grades.php';

class ActivityGradeBook implements \IBackupModel {
	/**
	 * @var GradeItem | Array
	 */
	public $grade_items = array();
	/**
	 * @var GradeLetter | Array
	 */
	public $grade_letters = array();
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/grades.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('activity_gradebook');
		
			$writer->startElement('grade_items');
			$writer->endElement();
			$writer->startElement('grade_letters');
			$writer->endElement();
				
		$writer->endElement();
		$writer->endDocument();
	}
}