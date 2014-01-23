<?php
require_once 'classes/model/IBackupModel.php';

class Questions implements \IBackupModel {
	
	/**
	 * @var QuestionCategory | Array
	 */
	public $question_categories = array() ;
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/questions.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);		
			$writer->startElement('question_categories');
			foreach ($this->question_categories as $question_category){
	 			$writer->startElement('question_category');
	 				$writer->writeAttribute('id',$question_category->id);
					
					$writer->writeElement('name',$question_category->name);
					$writer->writeElement('contextid',$question_category->contextid);
					$writer->writeElement('contextlevel',$question_category->contextlevel);
					$writer->writeElement('contextinstanceid',$question_category->contextinstanceid);
					$writer->writeElement('info',$question_category->info);
					$writer->writeElement('infoformat',$question_category->infoformat);
					$writer->writeElement('stamp',$question_category->stamp);
					$writer->writeElement('parent',$question_category->parent);
					$writer->writeElement('sortorder',$question_category->sortorder);
					
					$writer->startElement('questions');
					$writer->endElement();
						
				$writer->endElement();
			}
			$writer->endElement();
		$writer->endDocument();
		
	}
}


class QuestionCategory{
	
	public $id;
	
	public $name;
	public $contextid;
	public $contextlevel;
	public $contextinstanceid;
	public $info;
	public $infoformat;
	public $stamp;
	public $parent;
	public $sortorder;
	
	
	/**
	 * @var Question | Array
	 */
	public $questions = array() ;	
}

class Question {

}