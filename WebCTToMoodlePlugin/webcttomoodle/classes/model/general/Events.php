<?php
require_once 'classes/model/IBackupModel.php';

class Events implements \IBackupModel {
	
	/**
	 * @var Event | Array
	 */
	public $events = array() ;
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/calendar.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
		$writer->startElement('events');
		
		foreach ($this->events as $event){
 			$writer->startElement('event');
				$writer->writeAttribute('id',$event->id);
				
				$writer->writeElement('name',$event->name);
				$writer->writeElement('description',$event->description);
				$writer->writeElement('format',$event->format);
				$writer->writeElement('courseid',$event->courseid);
				$writer->writeElement('groupid',$event->groupid);
				$writer->writeElement('userid',$event->userid);
				$writer->writeElement('repeatid',$event->repeatid);
				$writer->writeElement('modulename',$event->modulename);
				$writer->writeElement('instance',$event->instance);
				$writer->writeElement('eventtype',$event->eventtype);
				$writer->writeElement('timestart',$event->timestart);
				$writer->writeElement('timeduration',$event->timeduration);
				$writer->writeElement('visible',$event->visible);
				$writer->writeElement('uuid',$event->uuid);
				$writer->writeElement('sequence',$event->sequence);
				$writer->writeElement('timemodified',$event->timemodified);
				
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		
	}
}

class Event {
	public $id;//id="6">
	public $name;//<name>Eval 2 (Quiz opens)</name>
	public $description;//<description>&lt;div class="no-overflow"&gt;&lt;p&gt;Voici ma description de mon évaluation...&lt;/p&gt;&lt;/div&gt;</description>
	public $format;//<format>1</format>
	public $courseid;//<courseid>6</courseid>
	public $groupid;//<groupid>0</groupid>
	public $userid;//<userid>2</userid>
	public $repeatid;//<repeatid>0</repeatid>
	public $modulename;//<modulename>quiz</modulename>
	public $instance;//<instance>40</instance>
	public $eventtype;//<eventtype>open</eventtype>
	public $timestart;//<timestart>-152423940</timestart>
	public $timeduration;//<timeduration>0</timeduration>
	public $visible;//<visible>0</visible>
	public $uuid;//<uuid></uuid>
	public $sequence;//<sequence>1</sequence>
	public $timemodified;//<timemodified>1392650251</timemodified>
	
	

}