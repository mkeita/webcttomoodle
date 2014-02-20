<?php
require_once 'classes/model/IBackupModel.php';

class Glossary implements \IBackupModel {
	
	public $id;//id="1" moduleid="11" modulename="glossary" contextid="54"
	public $moduleid;
	public $modulename;	
	public $contextid ;
	
	public $glossaryid; // id="1"
	

	public $name;// 	<name>Marc glossary</name>
	public $intro;// 	<intro>&lt;p&gt;An alphabetical list of terms relating to this course, and their descriptions.&lt;/p&gt;</intro>
	public $introformat;// 	<introformat>1</introformat>
	public $allowduplicatedentries;// 	<allowduplicatedentries>0</allowduplicatedentries>
	public $displayformat;// 	<displayformat>dictionary</displayformat>
	public $mainglossary;// 	<mainglossary>0</mainglossary>
	public $showspecial;// 	<showspecial>1</showspecial>
	public $showalphabet;// 	<showalphabet>1</showalphabet>
	public $showall;// 	<showall>1</showall>
	public $allowcomments;// 	<allowcomments>0</allowcomments>
	public $allowprintview;// 	<allowprintview>1</allowprintview>
	public $usedynalink;// 	<usedynalink>1</usedynalink>
	public $defaultapproval;// 	<defaultapproval>1</defaultapproval>
	public $globalglossary;// 	<globalglossary>0</globalglossary>
	public $entbypage;// 	<entbypage>10</entbypage>
	public $editalways;// 	<editalways>0</editalways>
	public $rsstype;// 	<rsstype>0</rsstype>
	public $rssarticles;// 	<rssarticles>0</rssarticles>
	public $assessed;// 	<assessed>0</assessed>
	public $assesstimestart;// 	<assesstimestart>0</assesstimestart>
	public $assesstimefinish;// 	<assesstimefinish>0</assesstimefinish>
	public $scale;// 	<scale>0</scale>
	public $timecreated;// 	<timecreated>1390818670</timecreated>
	public $timemodified;// 	<timemodified>1390818670</timemodified>
	public $completionentries;// 	<completionentries>0</completionentries>
		
	/**
	 * @var int|Array
	 */
	public $filesIds = array();
	
	
	/**
	 * @var Entry | Array
	 */
	public $entries = array(); 
	
	public function toXMLFile($repository) {
		
		$writer = new XMLWriter();
		$writer->openURI($repository.'/glossary.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
			$writer->startElement('activity');
				$writer->writeAttribute('id', $this->id);
				$writer->writeAttribute('moduleid', $this->moduleid);
				$writer->writeAttribute('modulename', $this->modulename);
				$writer->writeAttribute('contextid', $this->contextid);
				
				$writer->startElement('glossary');
					$writer->writeAttribute('id', $this->glossaryid);
					$writer->startElement('name');
						$writer->text($this->name);
					$writer->endElement();
					$writer->startElement('intro');
						$writer->text($this->intro);
					$writer->endElement();
					$writer->writeElement('introformat',$this->introformat);
					$writer->writeElement('allowduplicatedentries',$this->allowduplicatedentries);
					$writer->writeElement('displayformat',$this->displayformat);
					$writer->writeElement('mainglossary',$this->mainglossary);
					$writer->writeElement('showspecial',$this->showspecial);
					$writer->writeElement('showalphabet',$this->showalphabet);
					$writer->writeElement('showall',$this->showall);
					$writer->writeElement('allowcomments',$this->allowcomments);
					$writer->writeElement('allowprintview',$this->allowprintview);
					$writer->writeElement('usedynalink',$this->usedynalink);
					$writer->writeElement('defaultapproval',$this->defaultapproval);
					$writer->writeElement('globalglossary',$this->globalglossary);
					$writer->writeElement('entbypage',$this->entbypage);
					$writer->writeElement('editalways',$this->editalways);
					$writer->writeElement('rsstype',$this->rsstype);
					$writer->writeElement('rssarticles',$this->rssarticles);
					$writer->writeElement('assessed',$this->assessed);
					$writer->writeElement('assesstimestart',$this->assesstimestart);
					$writer->writeElement('assesstimefinish',$this->assesstimefinish);
					$writer->writeElement('scale',$this->scale);
					$writer->writeElement('timecreated',$this->timecreated);
					$writer->writeElement('timemodified',$this->timemodified);
					$writer->writeElement('completionentries',$this->completionentries);
				
					$writer->startElement('entries');
					foreach ($this->entries as $entry){
						$writer->startElement('entry');
							$writer->writeAttribute('id', $entry->id);
							$writer->writeElement('userid',$entry->userid);
							$writer->startElement('concept');
								$writer->text($entry->concept);
							$writer->endElement();
							$writer->startElement('definition');
								$writer->text($entry->definition);
							$writer->endElement();							
							$writer->writeElement('definitionformat',$entry->definitionformat);
							$writer->writeElement('definitiontrust',$entry->definitiontrust);
							$writer->writeElement('attachment',$entry->attachment);
							$writer->writeElement('timecreated',$entry->timecreated);
							$writer->writeElement('timemodified',$entry->timemodified);
							$writer->writeElement('teacherentry',$entry->teacherentry);
							$writer->writeElement('sourceglossaryid',$entry->sourceglossaryid);
							$writer->writeElement('usedynalink',$entry->usedynalink);
							$writer->writeElement('casesensitive',$entry->casesensitive);
							$writer->writeElement('fullmatch',$entry->fullmatch);
							$writer->writeElement('approved',$entry->approved);
								
							
							$writer->startElement('aliases');
							foreach ($entry->aliases as $alias){
								$writer->startElement('alias');
									$writer->writeAttribute('id', $alias->id);
									
									$writer->startElement('alias_text');
										$writer->text($alias->alias_text);
									$writer->endElement();
								$writer->endElement();								
							}
							$writer->endElement();
							
							$writer->startElement('ratings');
							foreach ($entry->ratings as $rating){
								
							}
							$writer->endElement();
								
						$writer->endElement();
					}
					$writer->endElement();
				
				$writer->endElement();
			$writer->endElement();
		$writer->endDocument();
	}
}

class Entry {
	public $id; //id="1"
	
	public $userid;// 		<userid>2</userid>
	public $concept;//         <concept>Entry1</concept>
	public $definition;//         <definition>&lt;p&gt;Entry 1 of glossary&lt;/p&gt;</definition>
	public $definitionformat;//         <definitionformat>1</definitionformat>
	public $definitiontrust;//         <definitiontrust>0</definitiontrust>
	public $attachment;//         <attachment>1</attachment>
	public $timecreated;//         <timecreated>1390818856</timecreated>
	public $timemodified;//         <timemodified>1390818883</timemodified>
	public $teacherentry;//         <teacherentry>1</teacherentry>
	public $sourceglossaryid;//         <sourceglossaryid>0</sourceglossaryid>
	public $usedynalink;//         <usedynalink>0</usedynalink>
	public $casesensitive;//         <casesensitive>0</casesensitive>
	public $fullmatch;//         <fullmatch>0</fullmatch>
	public $approved;//         <approved>1</approved>
	
	
	/**
	 * @var Alias | Array
	 */
	public $aliases = array();//         <aliases>
	
	/**
	 * @var RatingBackup | Array
	 */
	public $ratings = array();//         <ratings>
	
	
	/**
	 * @var Glossary
	 */
	public $glossary;

	
	/**
	 * @param Glossary $glossary
	 */
	public function __construct($glossary){
		$this->glossary = $glossary;
	}
	
}

class Alias {
	public $id;//           <alias id="1">
	
	public $alias_text;//             <alias_text>test</alias_text>
	
	public function __construct($id,$alias_text){
		$this->id = $id;
		$this->alias_text = $alias_text;		
	}
	
}

class RatingBackup {
	
}
