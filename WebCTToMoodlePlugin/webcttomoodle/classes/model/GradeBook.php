<?php
require_once 'classes/model/IBackupModel.php';
require_once 'classes/model/general/Grades.php';


class GradeBook implements \IBackupModel {
	/**
	 * @var GradeCategory | Array
	 */
	public $grade_categories = array();
	/**
	 * @var GradeItem | Array
	 */
	public $grade_items = array();
	/**
	 * @var GradeLetter | Array
	 */
	public $grade_letters = array();
	/**
	 * @var GradeSetting | Array
	 */
	public $grade_settings = array();
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/gradebook.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('gradebook');
		
			$writer->startElement('grade_categories');
			$writer->endElement();
			$writer->startElement('grade_items');
			$writer->endElement();
			$writer->startElement('grade_letters');
			$writer->endElement();
			$writer->startElement('grade_settings');
			$writer->endElement();
				
		$writer->endElement();
		$writer->endDocument();
		
	}
}

class GradeCategory{
	
}

class GradeSetting{

}