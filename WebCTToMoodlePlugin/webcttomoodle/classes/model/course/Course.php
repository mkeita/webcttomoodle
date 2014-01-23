<?php
require_once 'classes/model/IBackupModel.php';

class Course implements \IBackupModel {
	
	public $id; //id="5"
	public $contextid; //contextid="42"
	
	public $shortname ;// 	<shortname>BA-Course</shortname>
	public $fullname ;// 	<fullname>BackupCourse</fullname>
	public $idnumber ;// 	<idnumber>BA-1</idnumber>
	public $summary ;// 	<summary></summary>
	public $summaryformat ;// 	<summaryformat>1</summaryformat>
	public $format ;// 	<format>weeks</format>
	public $showgrades ;// 	<showgrades>1</showgrades>
	public $newsitems ;// 	<newsitems>5</newsitems>
	public $startdate ;// 	<startdate>1390258800</startdate>
	public $marker ;// 	<marker>0</marker>
	public $maxbytes ;// 	<maxbytes>0</maxbytes>
	public $legacyfiles ;// 	<legacyfiles>0</legacyfiles>
	public $showreports ;// 	<showreports>0</showreports>
	public $visible ;// 	<visible>1</visible>
	public $groupmode ;// 	<groupmode>0</groupmode>
	public $groupmodeforce ;// 	<groupmodeforce>0</groupmodeforce>
	public $defaultgroupingid ;// 	<defaultgroupingid>0</defaultgroupingid>
	public $lang ;// 	<lang></lang>
	public $theme ;// 	<theme></theme>
	public $timecreated ;// 	<timecreated>1390206206</timecreated>
	public $timemodified ;// 	<timemodified>1390206206</timemodified>
	public $requested ;// 	<requested>0</requested>
	public $enablecompletion ;// 	<enablecompletion>0</enablecompletion>
	public $completionnotify ;// 	<completionnotify>0</completionnotify>
	public $numsections ;// 	<numsections>10</numsections>
	public $hiddensections ;// 	<hiddensections>0</hiddensections>
	public $coursedisplay ;// 	<coursedisplay>0</coursedisplay>
	
	/**
	 * @var CourseCategory
	 */
	public $category; // CourseCategory
	
	public function toXMLFile($repository) {
		
		$writer = new XMLWriter();
		$writer->openURI($repository.'/course.xml');
		$writer->startDocument('1.0','UTF-8');
			$writer->startElement('course');
				$writer->writeAttribute('id', $this->id);
				$writer->writeAttribute('contextid', $this->contextid);
				
				$writer->writeElement('shortname',$this->shortname);
				$writer->writeElement('fullname',$this->fullname);
				$writer->writeElement('idnumber',$this->idnumber);
				$writer->writeElement('summary',$this->summary);
				$writer->writeElement('summaryformat',$this->summaryformat);
				$writer->writeElement('format',$this->format);
				$writer->writeElement('showgrades',$this->showgrades);
				$writer->writeElement('newsitems',$this->newsitems);
				$writer->writeElement('startdate',$this->startdate);
				$writer->writeElement('marker',$this->marker);
				$writer->writeElement('maxbytes',$this->maxbytes);
				$writer->writeElement('legacyfiles',$this->legacyfiles);
				$writer->writeElement('showreports',$this->showreports);
				$writer->writeElement('visible',$this->visible);
				$writer->writeElement('groupmode',$this->groupmode);
				$writer->writeElement('groupmodeforce',$this->groupmodeforce);
				$writer->writeElement('defaultgroupingid',$this->defaultgroupingid);
				$writer->writeElement('lang',$this->lang);
				$writer->writeElement('theme',$this->theme);
				$writer->writeElement('timecreated',$this->timecreated);
				$writer->writeElement('timemodified',$this->timemodified);
				$writer->writeElement('requested',$this->requested);
				$writer->writeElement('enablecompletion',$this->enablecompletion);
				$writer->writeElement('completionnotify',$this->completionnotify);
				$writer->writeElement('numsections',$this->numsections);
				$writer->writeElement('hiddensections',$this->hiddensections);
				$writer->writeElement('coursedisplay',$this->coursedisplay);
				
				$writer->startElement('category');
					$writer->writeAttribute('id', $this->category->id);
					
					$writer->writeElement('name',$this->category->name);
					$writer->writeElement('description',$this->category->description);
				$writer->endElement();
				
				$writer->startElement('tags');
				$writer->endElement();
				
			$writer->endElement();
		$writer->endDocument();
	}
}

class CourseCategory {
	public $id; //id="1"
	
	public $name; //<name>Miscellaneous</name>
    public $description; //<description>$@NULL@$</description>
}

