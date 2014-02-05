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
		
		
		//Add a multichoice question
		$question = new MultiChoiceQuestion();
		$question->id="1";// 		<question id="2">
		$question->parent=0;// 		<parent>0</parent>
		$question->name="AM a 01";// 		<name>AM a 01</name>
		$question->questiontext='Quelle est la masse d\'une molecule de nitrate d\'hydrogene';
		// 		<questiontext>&lt;p&gt;&amp;lt;P&amp;gt;&amp;lt;font color="#3366FF"&amp;gt;&amp;amp;nbsp;&amp;amp;nbsp;&amp;amp;nbsp;Quelle est la &lt;br /&gt;masse d'une molecule de nitrate d'hydrogene &lt;br /&gt;(HNO&amp;lt;sub&amp;gt;3&amp;lt;/sub&amp;gt;) ?&amp;lt;/Font&amp;gt;&lt;br /&gt;&amp;lt;/P&amp;gt;&lt;/p&gt;
		// 		&lt;p&gt;&amp;lt;P&amp;gt;&amp;lt;A &lt;br /&gt;HREF="/webct/RelativeResourceManager/Template/Theorie/Tables/TableMassesatomiques.html" &lt;br /&gt;TARGET="_blank"&amp;gt;Tableau des masses atomiques &lt;br /&gt;relatives&amp;lt;/A&amp;gt;&amp;lt;/P&amp;gt;&lt;/p&gt;</questiontext>
		$question->questiontextformat="1";// 		<questiontextformat>1</questiontextformat>
		$question->generalfeedback='1 mole de HNO';// 		<generalfeedback>&lt;p&gt;&amp;lt;P&amp;gt;&amp;lt;FONT COLOR="#000000"&amp;gt;1 mole de HNO&amp;lt;sub&amp;gt;3&amp;lt;/sub&amp;gt; &lt;/p&gt;</generalfeedback>
		$question->generalfeedbackformat="1";// 		<generalfeedbackformat>1</generalfeedbackformat>
		$question->defaultmark="1.0000000";// 		<defaultmark>1.0000000</defaultmark>
		$question->penalty="0.3333333";// 		<penalty>0.3333333</penalty>
		$question->length="1";// 		<length>1</length>
		$question->stamp="0";// 		<stamp>localhost+140131160414+7AXnY7</stamp>
		$question->version="0";// 		<version>localhost+140131160414+HfBHPl</version>
		$question->hidden="0";// 		<hidden>0</hidden>
		$question->timecreated=time();// 		<timecreated>1391184254</timecreated>
		$question->timemodified=time();// 		<timemodified>1391184254</timemodified>
		$question->createdby="2";// 		<createdby>2</createdby>
		$question->modifiedby="2";// 		<modifiedby>2</modifiedby>
		
		$multichoice = new MultiChoice();
		$multichoice->id=$question->id;
		
		$multichoice->layout="0";// 			<layout>0</layout>
		$multichoice->single="1";//             <single>1</single>
		$multichoice->shuffleanswers="0";//             <shuffleanswers>0</shuffleanswers>
		$multichoice->correctfeedback='Your answer is correct';//             <correctfeedback>&lt;p&gt;Your answer is correct.&lt;/p&gt;</correctfeedback>
		$multichoice->correctfeedbackformat="1";//             <correctfeedbackformat>1</correctfeedbackformat>
		$multichoice->partiallycorrectfeedback='Your answer is partially correct';//             <partiallycorrectfeedback>&lt;p&gt;Your answer is partially correct.&lt;/p&gt;</partiallycorrectfeedback>
		$multichoice->partiallycorrectfeedbackformat="1";//             <partiallycorrectfeedbackformat>1</partiallycorrectfeedbackformat>
		$multichoice->incorrectfeedback='Your answer is incorrect';//             <incorrectfeedback>&lt;p&gt;Your answer is incorrect.&lt;/p&gt;</incorrectfeedback>
		$multichoice->incorrectfeedbackformat="1";//             <incorrectfeedbackformat>1</incorrectfeedbackformat>
		$multichoice->answernumbering="abc";//             <answernumbering>abc</answernumbering>
		$multichoice->shownumcorrect="1";//             <shownumcorrect>1</shownumcorrect>
		
		$question->multiChoice = $multichoice;
		
		
		$answer = new Answer();
		$answer->id="4";// 		id="4">
		$answer->answertext='1,05';// 		<answertext>&lt;p&gt;1,05 10&amp;lt;SUP&amp;gt;-22&amp;lt;/SUP&amp;gt; g&lt;/p&gt;</answertext>
		$answer->answerformat="1";// 		<answerformat>1</answerformat>
		$answer->fraction="1.0000000";// 		<fraction>1.0000000</fraction>
		$answer->feedback='C\'est exact.';// 		<feedback>&lt;p&gt;C'est exact.&lt;/p&gt;</feedback>
		$answer->feedbackformat="1";// 		<feedbackformat>1</feedbackformat>

		$question->answers[] = $answer;

		$answer = new Answer();
		$answer->id="5";// 		id="4">
		$answer->answertext='63,0 g';// 		<answertext>&lt;p&gt;1,05 10&amp;lt;SUP&amp;gt;-22&amp;lt;/SUP&amp;gt; g&lt;/p&gt;</answertext>
		$answer->answerformat="1";// 		<answerformat>1</answerformat>
		$answer->fraction="0.0000000";// 		<fraction>1.0000000</fraction>
		$answer->feedback='Non. On vous demande la masse d\'une molecule et non la masse d\'une mole de nitrate d\'hydrogene.';// 		<feedback>&lt;p&gt;C'est exact.&lt;/p&gt;</feedback>
		$answer->feedbackformat="1";// 		<feedbackformat>1</feedbackformat>
		
		$question->answers[] = $answer;
		
		$answer = new Answer();
		$answer->id="6";// 		id="4">
		$answer->answertext='5,15';// 		<answertext>&lt;p&gt;1,05 10&amp;lt;SUP&amp;gt;-22&amp;lt;/SUP&amp;gt; g&lt;/p&gt;</answertext>
		$answer->answerformat="1";// 		<answerformat>1</answerformat>
		$answer->fraction="0.0000000";// 		<fraction>1.0000000</fraction>
		$answer->feedback='Non. Il y a trois moles d\'oxygene par mole de nitrate d\'hydrogene.';// 		<feedback>&lt;p&gt;C'est exact.&lt;/p&gt;</feedback>
		$answer->feedbackformat="1";// 		<feedbackformat>1</feedbackformat>
		
		$question->answers[] = $answer;
		
		$questionCategory->questions[]=$question;
		
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