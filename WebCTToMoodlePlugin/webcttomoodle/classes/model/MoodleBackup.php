<?php
require_once 'classes/model/IBackupModel.php';

class MoodleBackup implements \IBackupModel {
	
	public $name;// 	<name>backup-moodle2-course-5-ba-course-20140120-0924.mbz</name>
	public $moodle_version;// 	<moodle_version>2013111800.07</moodle_version>
	public $moodle_release;// 	<moodle_release>2.6+ (Build: 20131224)</moodle_release>
	public $backup_version;// 	<backup_version>2013111800</backup_version>
	public $backup_release;// 	<backup_release>2.6</backup_release>
	public $backup_date;// 	<backup_date>1390206271</backup_date>
	public $mnet_remoteusers;// 	<mnet_remoteusers>0</mnet_remoteusers>
	public $include_files;// 	<include_files>1</include_files>
	public $include_file_references_to_external_content;// 	<include_file_references_to_external_content>0</include_file_references_to_external_content>
	public $original_wwwroot;// 	<original_wwwroot>http://localhost</original_wwwroot>
	public $original_site_identifier_hash;// 	<original_site_identifier_hash>466e6cbfa1822412e51d78ebf220ec26</original_site_identifier_hash>
	public $original_course_id;// 	<original_course_id>5</original_course_id>
	public $original_course_fullname;// 	<original_course_fullname>BackupCourse</original_course_fullname>
	public $original_course_shortname;// 	<original_course_shortname>BA-Course</original_course_shortname>
	public $original_course_startdate;// 	<original_course_startdate>1390258800</original_course_startdate>
	public $original_course_contextid;// 	<original_course_contextid>42</original_course_contextid>
	public $original_system_contextid;// 	<original_system_contextid>1</original_system_contextid>
	
	
	/**
	 * @var MoodleBackupDetail | Array
	 */
	public $details = array();
	/**
	 * @var MoodleBackupContents
	 */
	public $contents;
		
	/**
	 * @var MoodleBackupSetting | Array
	 */
	public $settings = array();
	
	public function toXMLFile($repository) {
		
		$writer = new XMLWriter();
		$writer->openURI($repository.'/moodle_backup.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
		$writer->startElement('moodle_backup');
			$writer->startElement('information');
				$writer->writeElement('name',$this->name);
				$writer->writeElement('moodle_version',$this->moodle_version);
				$writer->writeElement('moodle_release',$this->moodle_release);
				$writer->writeElement('backup_version',$this->backup_version);
				$writer->writeElement('backup_release',$this->backup_release);
				$writer->writeElement('backup_date',$this->backup_date);
				$writer->writeElement('mnet_remoteusers',$this->mnet_remoteusers);
				$writer->writeElement('include_files',$this->include_files);
				$writer->writeElement('include_file_references_to_external_content',$this->include_file_references_to_external_content);
				$writer->writeElement('original_wwwroot',$this->original_wwwroot);
				$writer->writeElement('original_site_identifier_hash',$this->original_site_identifier_hash);
				$writer->writeElement('original_course_id',$this->original_course_id);
				$writer->writeElement('original_course_fullname',$this->original_course_fullname);
				$writer->writeElement('original_course_shortname',$this->original_course_shortname);
				$writer->writeElement('original_course_startdate',$this->original_course_startdate);
				$writer->writeElement('original_course_contextid',$this->original_course_contextid);
				$writer->writeElement('original_system_contextid',$this->original_system_contextid);
				
				//DETAILS
				$writer->startElement('details');
				foreach($this->details as $detail){
					$writer->startElement('detail');
					$writer->writeAttribute('backup_id',$detail->backup_id);
					
					$writer->writeElement('type',$detail->type);
					$writer->writeElement('format',$detail->format);
					$writer->writeElement('interactive',$detail->interactive);
					$writer->writeElement('mode',$detail->mode);
					$writer->writeElement('execution',$detail->execution);
					$writer->writeElement('executiontime',$detail->executiontime);
					
					$writer->endElement();
				}
				$writer->endElement();
						
				
				//CONTENTS
				$writer->startElement('contents');
					if(!empty($this->contents->activities)){
						$writer->startElement('activities');
						foreach ($this->contents->activities as $activity){
							$writer->startElement('activity');
								$writer->writeElement('moduleid',$activity->moduleid);
								$writer->writeElement('sectionid',$activity->sectionid);
								$writer->writeElement('modulename',$activity->modulename);
								$writer->writeElement('title',$activity->title);
								$writer->writeElement('directory',$activity->directory);
							$writer->endElement();
						}
						$writer->endElement();
					}
				
					$writer->startElement('course');
						$courseContent = $this->contents->course;
						$writer->writeElement('courseid',$courseContent->courseid);
						$writer->writeElement('title',$courseContent->title);
						$writer->writeElement('directory',$courseContent->directory);
					$writer->endElement();
					
					$writer->startElement('sections');
					foreach ($this->contents->sections as $section){
						$writer->startElement('section');
							$writer->writeElement('sectionid',$section->sectionid);
							$writer->writeElement('title',$section->title);
							$writer->writeElement('directory',$section->directory);						
						$writer->endElement();
					}
					$writer->endElement();
					
				$writer->endElement();
				
				//SETTINGS
				$writer->startElement('settings');
				foreach($this->settings as $setting){
					$writer->startElement('setting');
						$writer->writeElement('level',$setting->level);
						
						if($setting instanceof MoodleBackupSectionSetting){
							$writer->writeElement('section',$setting->section);
						}elseif ($setting instanceof MoodleBackupActivitySetting){
							$writer->writeElement('activity',$setting->activity);
						}
						
						$writer->writeElement('name',$setting->name);
						$writer->writeElement('value',$setting->value);
					$writer->endElement();
				}
				$writer->endElement();
				
			$writer->endElement();
		$writer->endElement();
		
		$writer->endDocument();
		
	}
}

class MoodleBackupDetail {
	public $backup_id; //backup_id="56bb12ed0c330f540807609a79fff752"
	
	public $type;// 	<type>course</type>
	public $format;// 	<format>moodle2</format>
	public $interactive;// 	<interactive>1</interactive>
	public $mode;// 	<mode>10</mode>
	public $execution;// 	<execution>1</execution>
	public $executiontime; // 	<executiontime>0</executiontime>
}

class MoodleBackupContents {

	/**
	 * @var MoodleBackupSectionsSection | Array
	 */
	public $sections = array();
	/**
	 * @var MoodleBackupContentsCourse
	 */
	public $course;
	
	/**
	 * @var MoodleBackupActivity | Array
	 */
	public $activities;
	
	
}

class MoodleBackupSectionsSection {
	public $sectionid;// 	<sectionid>36</sectionid>
	public $title;// 	<title>0</title>
	public $directory;// 	<directory>sections/section_36</directory>
	
	public function __construct($sectionid,$title,$directory){
		$this->sectionid = $sectionid;
		$this->title = $title;
		$this->directory = $directory;
	}
}

class MoodleBackupContentsCourse {
	public $courseid;// 	<courseid>5</courseid>
	public $title;// 	<title>BA-Course</title>
	public $directory;// 	<directory>course</directory>
}

abstract class MoodleBackupSetting {
	
// 	const ROOT_LEVEL     = 1; root
// 	const COURSE_LEVEL   = 5; course
// 	const SECTION_LEVEL  = 9; section
// 	const ACTIVITY_LEVEL = 13; activity
	
	public $level;//<level>section</level>
	public $section; //<section> section_36</section>
	public $activity;//<activity>glossary_11</activity>
    public $name;//    <name>section_36_userinfo</name>
    public $value;//    <value>1</value>
	

    public function __construct($level, $section,$activity, $name, $value){
    	$this->level = $level;
    	$this->section = $section;
    	$this->activity= $activity;
    	$this->name = $name;
    	$this->value = $value;
    }
    
}

class MoodleBackupBasicSetting extends MoodleBackupSetting{
	public function __construct($level, $name, $value){
		parent::__construct($level, null, null, $name, $value);
	}
}

class MoodleBackupActivitySetting extends MoodleBackupSetting{	
	public function __construct($level, $activity, $name, $value){
		parent::__construct($level, null, $activity, $name, $value);
	}
}

class MoodleBackupSectionSetting extends MoodleBackupSetting{
	public function __construct($level, $section, $name, $value){
		parent::__construct($level, $section, null, $name, $value);
	}
}



class MoodleBackupActivity {
	public $moduleid;// 	<moduleid>11</moduleid>
	public $sectionid; //   <sectionid>36</sectionid>
	public $modulename;//   <modulename>glossary</modulename>
	public $title;//        <title>Marc glossary</title>
	public $directory;//    <directory>activities/glossary_11</directory>
}
