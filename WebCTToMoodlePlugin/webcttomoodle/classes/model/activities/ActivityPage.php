<?php
require_once 'classes/model/IBackupModel.php';

class ActivityPage implements \IBackupModel {
	public $id;
	public $moduleid;
	public $modulename;	
	public $contextid ;

	public $pageId;
	public $name;
	public $intro; //vide
	public $introformat;
	public $content;
	public $contentformat;
	public $legacyfiles;
	public $legacyfileslast;
	public $display;
	public $displayoptions;
	public $revision;
	public $timemodified;

	
public function toXMLFile($repository) {
		
		$writer = new XMLWriter ();	
		$writer->openURI ( $repository. '/page.xml' );
		$writer->startDocument ( '1.0', 'UTF-8' );
		$writer->setIndent(true);
		$writer->startElement ( 'activity' );
		$writer->writeAttribute ( 'id', $this->id );
		$writer->writeAttribute ( 'moduleid', $this->moduleid );
		$writer->writeAttribute ( 'modulename', $this->modulename );
		$writer->writeAttribute ( 'contextid', $this->contextid );	
		$writer->startElement ( 'page' );
		$writer->writeAttribute ( 'id', $this->pageId );
		$writer->writeElement ( 'name', $this->name );
		$writer->writeElement ( 'intro', $this->intro );
		$writer->writeElement ( 'introformat', $this->introformat );
		$writer->writeElement ( 'content', $this->content );
		$writer->writeElement ( 'contentformat', $this->contentformat );
		$writer->writeElement ( 'legacyfiles', $this->legacyfiles );
		$writer->writeElement ( 'legacyfileslast', $this->legacyfileslast );
		$writer->writeElement ( 'display', $this->display );
		$writer->writeElement ( 'displayoptions', $this->displayoptions );
		$writer->writeElement ( 'revision', $this->revision );
		$writer->writeElement ( 'timemodified', $this->timemodified );
		$writer->endElement ();
		$writer->endElement ();
		$writer->endDocument ();
	}




}


?>