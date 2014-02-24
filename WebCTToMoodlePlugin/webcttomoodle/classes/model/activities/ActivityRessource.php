<?php

require_once 'classes/model/IBackupModel.php';

class ActivityRessource implements \IBackupModel{
	
	public $id;
	public $moduleid;
	public $modulename;
	public $contextid ;
	public $ressourceId;
	public $name;
	public $intro;
	public $introformat;
	public $tobemigrated;
	public $legacyfiles;
	public $legacyfileslast;
	public $display;
	public $displayoptions;
	public $filterFiles;
	public $revision;
	public $timemodified;
	
	/**
	 * @var int|Array
	 */
	public $filesIds = array();
	
	/* (non-PHPdoc)
	 * @see IBackupModel::toXMLFile()
	 */
	public function toXMLFile($repository) {
		$writer = new XMLWriter ();
		$writer->openURI ( $repository. '/resource.xml' );
		$writer->startDocument ( '1.0', 'UTF-8' );
		$writer->setIndent(true);
		$writer->startElement ( 'activity' );
		$writer->writeAttribute ( 'id', $this->id );
		$writer->writeAttribute ( 'moduleid', $this->moduleid );
		$writer->writeAttribute ( 'modulename', $this->modulename );
		$writer->writeAttribute ( 'contextid', $this->contextid );
		$writer->startElement ( 'resource' );
		$writer->writeAttribute ( 'id', $this->ressourceId );
		$writer->writeElement ( 'name', $this->name );
		$writer->writeElement ( 'intro', $this->intro );
		$writer->writeElement ( 'introformat', $this->introformat );
		$writer->writeElement ( 'tobemigrated', $this->tobemigrated );
		$writer->writeElement ( 'legacyfiles', $this->legacyfiles );
		$writer->writeElement ( 'legacyfileslast', $this->legacyfileslast );
		$writer->writeElement ( 'display', $this->display );
		$writer->writeElement ( 'displayoptions', $this->displayoptions );
		$writer->writeElement ( 'filterfiles', $this->filterFiles );
		$writer->writeElement ( 'revision', $this->revision );
		$writer->writeElement ( 'timemodified', $this->timemodified );
		$writer->endElement ();
		$writer->endElement ();
		$writer->endDocument ();

	}
}
