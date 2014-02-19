<?php
require_once 'classes/model/IBackupModel.php';

class Module implements \IBackupModel {
	
	public $id; //id="11" version="2013110500"
	public $version;
	
	public $modulename;// 	<modulename>glossary</modulename>
	public $sectionid;//   <sectionid>36</sectionid>
	public $sectionnumber;//   <sectionnumber>0</sectionnumber>
	public $idnumber;//   <idnumber></idnumber>
	public $added;//   <added>1390818670</added>
	public $score;//   <score>0</score>
	public $indent;//   <indent>0</indent>
	public $visible;//   <visible>1</visible>
	public $visibleold;//   <visibleold>1</visibleold>
	public $groupmode;//   <groupmode>0</groupmode>
	public $groupingid;//   <groupingid>0</groupingid>
	public $groupmembersonly;//   <groupmembersonly>0</groupmembersonly>
	public $completion;//   <completion>0</completion>
	public $completiongradeitemnumber;//   <completiongradeitemnumber>$@NULL@$</completiongradeitemnumber>
	public $completionview;//   <completionview>0</completionview>
	public $completionexpected;//   <completionexpected>0</completionexpected>
	public $availablefrom;//   <availablefrom>0</availablefrom>
	public $availableuntil;//   <availableuntil>0</availableuntil>
	public $showavailability;//   <showavailability>0</showavailability>
	public $showdescription;//   <showdescription>0</showdescription>
	
	/**
	 * @var AvailabilityInfo
	 */
	public $availability_info; 
	
	public function toXMLFile($repository) {
		
		$writer = new XMLWriter();
		$writer->openURI($repository.'/module.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
			$writer->startElement('module');
				$writer->writeAttribute('id', $this->id);
				$writer->writeAttribute('version', $this->version);
				
				$writer->writeElement('modulename',$this->modulename);
				$writer->writeElement('sectionid',$this->sectionid);
				$writer->writeElement('sectionnumber',$this->sectionnumber);
				$writer->writeElement('idnumber',$this->idnumber);
				$writer->writeElement('added',$this->added);
				$writer->writeElement('score',$this->score);
				$writer->writeElement('indent',$this->indent);
				$writer->writeElement('visible',$this->visible);
				$writer->writeElement('visibleold',$this->visibleold);
				$writer->writeElement('groupmode',$this->groupmode);
				$writer->writeElement('groupingid',$this->groupingid);
				$writer->writeElement('groupmembersonly',$this->groupmembersonly);
				$writer->writeElement('completion',$this->completion);
				$writer->writeElement('completiongradeitemnumber',$this->completiongradeitemnumber);
				$writer->writeElement('completionview',$this->completionview);
				$writer->writeElement('completionexpected',$this->completionexpected);
				$writer->writeElement('availablefrom',$this->availablefrom);
				$writer->writeElement('availableuntil',$this->availableuntil);
				$writer->writeElement('showavailability',$this->showavailability);
				$writer->writeElement('showdescription',$this->showdescription);

				$writer->startElement('availability_info');
				$writer->endElement();
				
			$writer->endElement();
		$writer->endDocument();
	}
}

class AvailabilityInfo {

}

