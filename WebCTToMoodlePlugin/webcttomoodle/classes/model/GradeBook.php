<?php
require_once 'classes/model/IBackupModel.php';
require_once 'classes/model/general/Grades.php';


class GradeBook implements \IBackupModel {
	/**
	 * @var GradeCategory|Array
	 */
	public $grade_categories = array();
	/**
	 * @var GradeItem|Array
	 */
	public $grade_items = array();
	/**
	 * @var GradeLetter|Array
	 */
	public $grade_letters = array();
	/**
	 * @var GradeSetting|Array
	 */
	public $grade_settings = array();
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/gradebook.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
		$writer->startElement('gradebook');
		
			$writer->startElement('grade_categories');
				foreach ($this->grade_categories as $category){
					$category->toXML($writer);
				}
			$writer->endElement();
			
			$writer->startElement('grade_items');
				foreach ($this->grade_items as $item){
					$item->toXML($writer);
				}
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
	public $id;// id="148">
	public $parent;//<parent>$@NULL@$</parent>
	public $depth;//<depth>1</depth>
	public $path;//<path>/148/</path>
	public $fullname;//<fullname>?</fullname>
	public $aggregation;//<aggregation>11</aggregation>
	public $keephigh;//<keephigh>0</keephigh>
	public $droplow;//<droplow>0</droplow>
	public $aggregateonlygraded;//<aggregateonlygraded>1</aggregateonlygraded>
	public $aggregateoutcomes;//<aggregateoutcomes>0</aggregateoutcomes>
	public $aggregatesubcats;//<aggregatesubcats>0</aggregatesubcats>
	public $timecreated;//<timecreated>1392640698</timecreated>
	public $timemodified;//<timemodified>1392640698</timemodified>
	public $hidden;//<hidden>0</hidden>
	
	/**
	 * @param XMLWriter $writer
	 */
	public function toXML(&$writer){
		$writer->startElement("grade_category");
			$writer->writeAttribute("id", $this->id);
				
			$writer->writeElement("parent", $this->parent);
			$writer->writeElement("depth", $this->depth);
			$writer->writeElement("path", $this->path);
			$writer->writeElement("fullname", $this->fullname);
			$writer->writeElement("aggregation", $this->aggregation);
			$writer->writeElement("keephigh", $this->keephigh);
			$writer->writeElement("droplow", $this->droplow);
			$writer->writeElement("aggregateonlygraded", $this->aggregateonlygraded);
			$writer->writeElement("aggregateoutcomes", $this->aggregateoutcomes);
			$writer->writeElement("aggregatesubcats", $this->aggregatesubcats);
			$writer->writeElement("timecreated", $this->timecreated);
			$writer->writeElement("timemodified", $this->timemodified);
			$writer->writeElement("hidden", $this->hidden);
		$writer->endElement();	
	}
}


class GradeSetting{

}