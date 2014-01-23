<?php

require_once 'classes/model/GlobalModel.php';

class MockModel extends \GlobalModel {
	
	
	/**
	 * @return GlobalModel
	 */
	public function __construct(){
		parent::__construct();
		
		$this->retrieveQuestions();
		
	}
	
	public function preInitialization(){
		
	}
	
	/**
	 *
	 */
	public function initializeMoodleBackupModel(){		
		parent::initializeMoodleBackupModel();
		
		$this->moodle_backup->original_course_fullname = "WebCt Course 0";//WebCt Course 0";
		$this->moodle_backup->original_course_shortname = "WEBCT-0";//WEBCT-0";
		$this->moodle_backup->name = "backup-".$this->moodle_backup->original_course_shortname.".mbz"; //test_backup.mbz
		$this->moodle_backup->settings[0] = new MoodleBackupBasicSetting("root","filename",$this->moodle_backup->name);
		
	}
	
	
	public function retrieveQuestions(){
		
		$questionCategory = new QuestionCategory();
		
		$questionCategory->id = 1;
		$questionCategory->name = "WEBCT-0 Category";
		$questionCategory->contextid = 0;
		$questionCategory->contextlevel = 50;
		$questionCategory->contextinstanceid = 0;
		$questionCategory->info = "Description de la categorie 'WEBCT-0'.";
		$questionCategory->infoformat = 1;
		$questionCategory->stamp = 0; // localhost+140131155733+469Glc
		$questionCategory->parent = 0;
		$questionCategory->sortorder = 999;
		// 		<name>Default for WEBCT-0</name>
		// 		<contextid>48</contextid>
		// 		<contextlevel>50</contextlevel>
		// 		<contextinstanceid>6</contextinstanceid>
		// 		<info>The default category for questions shared in context 'WEBCT-0'.</info>
		// 		<infoformat>0</infoformat>
		// 		<stamp>localhost+140131155733+469Glc</stamp>
		// 		<parent>0</parent>
		// 		<sortorder>999</sortorder>
		
		
		
		$this->questions->question_categories[] = $questionCategory;		
		$this->course->inforef->questioncategoryids[]=$questionCategory->id;
		
		
		$questionCategory = new QuestionCategory();
		
		$questionCategory->id = 2;
		$questionCategory->name = "WEBCT-0 Category 2";
		$questionCategory->contextid = 0;
		$questionCategory->contextlevel = 50;
		$questionCategory->contextinstanceid = 0;
		$questionCategory->info = "Description de la categorie 'WEBCT-0 2'.";
		$questionCategory->infoformat = 1;
		$questionCategory->stamp = 0; // localhost+140131155733+469Glc
		$questionCategory->parent = 0;
		$questionCategory->sortorder = 999;
		
		$this->questions->question_categories[] = $questionCategory;
		$this->course->inforef->questioncategoryids[]=$questionCategory->id;
		
	}
	
	/**
	 * Initialize CourseModel
	 */
	public function initializeCourseModel(){
		parent::initializeCourseModel();

		$this->course->course->fullname = $this->moodle_backup->original_course_fullname;
		$this->course->course->shortname = $this->moodle_backup->original_course_shortname;
		$this->course->course->idnumber = $this->moodle_backup->original_course_id;
				
		$this->moodle_backup->contents->course->title=$this->moodle_backup->original_course_shortname;
		$this->moodle_backup->contents->course->courseid=$this->moodle_backup->original_course_id;
	}
	
	
}