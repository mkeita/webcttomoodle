<?php
require_once 'classes/model/IBackupModel.php';

class Section implements \IBackupModel {
	
	public $id;// id="36"
	
	public $number;// 	<number>0</number>
	public $name;// 	<name>$@NULL@$</name>
	public $summary;// 	<summary></summary>
	public $summaryformat;// 	<summaryformat>1</summaryformat>
	public $sequence;// 	<sequence></sequence>
	public $visible;// 	<visible>1</visible>
	public $availablefrom;// 	<availablefrom>0</availablefrom>
	public $availableuntil;// 	<availableuntil>0</availableuntil>
	public $showavailability;// 	<showavailability>0</showavailability>
	public $groupingid;// 	<groupingid>0</groupingid>	
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/section.xml');
		$writer->startDocument('1.0','UTF-8');
			$writer->startElement('section');
				$writer->writeAttribute('id', $this->id);
				
				$writer->writeElement('number',$this->number);
				$writer->writeElement('name',$this->name);
				$writer->writeElement('summary',$this->summary);
				$writer->writeElement('summaryformat',$this->summaryformat);
				$writer->writeElement('sequence',$this->sequence);
				$writer->writeElement('visible',$this->visible);
				$writer->writeElement('availablefrom',$this->availablefrom);
				$writer->writeElement('availableuntil',$this->availableuntil);
				$writer->writeElement('showavailability',$this->showavailability);
				$writer->writeElement('groupingid',$this->groupingid);
				
			$writer->endElement();
		$writer->endDocument();
	}
}
