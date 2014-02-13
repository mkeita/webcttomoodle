<?php
require_once 'classes/model/IBackupModel.php';

class InfoRef implements \IBackupModel {

	/**
	 * @var int|Array
	 */
	public $userids = array();
	
	/**
	 * @var int|Array
	 */
	public $roleids = array();

	/**
	 * @var int|Array
	 */
	public $fileids = array();
	
	
	/**
	 * @var int|Array
	 */
	public $questioncategoryids = array();
	
	
	/**
	 * @var int|Array
	 */
	public $gradeItemids = array();
	
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/inforef.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
		$writer->startElement('inforef');
		if(!empty($this->userids)){		
			$writer->startElement('userref');
			foreach ($this->userids as $id){
				$writer->startElement('user');
					$writer->writeElement('id',$id);
				$writer->endElement();
			}
			$writer->endElement();
		}
		if(!empty($this->roleids)){
			$writer->startElement('roleref');
			foreach ($this->roleids as $id){
				$writer->startElement('role');
				$writer->writeElement('id',$id);
				$writer->endElement();
			}
			$writer->endElement();
		}
		if(!empty($this->fileids)){
			$writer->startElement('fileref');
			foreach ($this->fileids as $id){
				$writer->startElement('file');
				$writer->writeElement('id',$id);
				$writer->endElement();
			}
			$writer->endElement();
		}
		if(!empty($this->questioncategoryids)){
			$writer->startElement('question_categoryref');
			foreach ($this->questioncategoryids as $id){
				$writer->startElement('question_category');
				$writer->writeElement('id',$id);
				$writer->endElement();
			}
			$writer->endElement();
		}

		if(!empty($this->gradeItemids)){
			$writer->startElement('grade_itemref');
			foreach ($this->gradeItemids as $id){
				$writer->startElement('grade_item');
				$writer->writeElement('id',$id);
				$writer->endElement();
			}
			$writer->endElement();
		}
		
		$writer->endElement();
		$writer->endDocument();
	}
}