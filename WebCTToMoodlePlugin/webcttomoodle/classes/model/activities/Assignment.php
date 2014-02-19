<?php
require_once 'classes/model/IBackupModel.php';

class ActivityAssignment implements \IBackupModel {
	
	public $id;//id="4" moduleid="57" modulename="assign" contextid="107"
	public $moduleid;
	public $modulename="assign";	
	public $contextid ;
	
	public $assignmentId; // id="1"
	

	public $name;
	public $intro;
	public $introformat;
	
	public $alwaysshowdescription;
    public $submissiondrafts;
    public $sendnotifications;
    public $sendlatenotifications;
    public $duedate;
   	public $cutoffdate;
    public $allowsubmissionsfromdate;
    public $grade;
    public $timemodified;
    public $completionsubmit;
    public $requiresubmissionstatement;
    public $teamsubmission;
    public $requireallteammemberssubmit;
    public $teamsubmissiongroupingid;
    public $blindmarking;
    public $revealidentities;
    public $attemptreopenmethod;
    public $maxattempts;
    public $markingworkflow;
    public $markingallocation;
    
		

//     public $userFlags = array();
    

// 	public $submissions = array();
	
	
// 	public $grades = array(); 

    /**
     * @var PluginConfig
     */
    public $plugin_configs = array();
	
	
	/**
	 * @var int|Array
	 */
	public $filesIds = array();
	
	public function toXMLFile($repository) {
		
		$writer = new XMLWriter();
		$writer->openURI($repository.'/assign.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
			$writer->startElement('activity');
				$writer->writeAttribute('id', $this->id);
				$writer->writeAttribute('moduleid', $this->moduleid);
				$writer->writeAttribute('modulename', $this->modulename);
				$writer->writeAttribute('contextid', $this->contextid);
				
				$writer->startElement('assign');
					$writer->writeAttribute('id', $this->assignmentId);
					$writer->startElement('name');
						$writer->text($this->name);
					$writer->endElement();
					$writer->startElement('intro');
						$writer->text($this->intro);
					$writer->endElement();
					$writer->writeElement('introformat',$this->introformat);
					$writer->writeElement('alwaysshowdescription',$this->alwaysshowdescription);
					$writer->writeElement('submissiondrafts',$this->submissiondrafts);
					$writer->writeElement('sendnotifications',$this->sendnotifications);
					$writer->writeElement('sendlatenotifications',$this->sendlatenotifications);
					$writer->writeElement('duedate',$this->duedate);
					$writer->writeElement('cutoffdate',$this->cutoffdate);
					$writer->writeElement('allowsubmissionsfromdate',$this->allowsubmissionsfromdate);
					$writer->writeElement('grade',$this->grade);
					$writer->writeElement('timemodified',$this->timemodified);
					$writer->writeElement('completionsubmit',$this->completionsubmit);
					$writer->writeElement('requiresubmissionstatement',$this->requiresubmissionstatement);
					$writer->writeElement('teamsubmission',$this->teamsubmission);
					$writer->writeElement('requireallteammemberssubmit',$this->requireallteammemberssubmit);
					$writer->writeElement('teamsubmissiongroupingid',$this->teamsubmissiongroupingid);
					$writer->writeElement('blindmarking',$this->blindmarking);
					$writer->writeElement('revealidentities',$this->revealidentities);
					$writer->writeElement('attemptreopenmethod',$this->attemptreopenmethod);
					$writer->writeElement('maxattempts',$this->maxattempts);
					$writer->writeElement('markingworkflow',$this->markingworkflow);
					$writer->writeElement('markingallocation',$this->markingallocation);

					
					
					$writer->startElement('plugin_configs');
					foreach ($this->plugin_configs as $pluginConfig){
						$writer->startElement('plugin_config');
							$writer->writeAttribute('id', $pluginConfig->id);
							$writer->writeElement('plugin', $pluginConfig->plugin);
							$writer->writeElement('subtype',$pluginConfig->subtype);
							$writer->writeElement('name',$pluginConfig->name);
							$writer->writeElement('value',$pluginConfig->value);
						$writer->endElement();
					}
					$writer->endElement();
					
					$writer->startElement('userflags');
					$writer->endElement();
					
					$writer->startElement('submissions');
					$writer->endElement();
						
					$writer->startElement('grades');
					$writer->endElement();
						
				$writer->endElement();
			$writer->endElement();
		$writer->endDocument();
	}
}

class PluginConfig {
	public $id;
	
	public $plugin;
	public $subtype;
	public $name;
	public $value;
	
	public function __construct($id,$plugin,$subtype,$name,$value){
		$this->id = $id;
		$this->plugin = $plugin;
		$this->subtype = $subtype;
		$this->name = $name;
		$this->value = $value;
	}
	
}