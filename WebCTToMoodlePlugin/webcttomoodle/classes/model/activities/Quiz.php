<?php
require_once 'classes/model/IBackupModel.php';

class ActivityQuiz implements \IBackupModel {
	
	public $id;//id="4" moduleid="57" modulename="quiz" contextid="107"
	public $moduleid;
	public $modulename="quiz";	
	public $contextid ;
	
	public $quizId; // id="1"
	

	public $name;
	public $intro;
	public $introformat;
	
	public $timeopen;//<timeopen>1396518120</timeopen>
    public $timeclose;//<timeclose>0</timeclose>
    public $timelimit;//<timelimit>7200</timelimit>
    public $overduehandling;//<overduehandling>autoabandon</overduehandling>
    public $graceperiod;//<graceperiod>0</graceperiod>
   	public $preferredbehaviour;//<preferredbehaviour>deferredfeedback</preferredbehaviour>
    public $attempts_number;//<attempts_number>0</attempts_number>
    public $attemptonlast;//<attemptonlast>0</attemptonlast>
    public $grademethod;//<grademethod>1</grademethod>
    public $decimalpoints;//<decimalpoints>2</decimalpoints>
    public $questiondecimalpoints;//<questiondecimalpoints>-1</questiondecimalpoints>
    public $reviewattempt;//<reviewattempt>69904</reviewattempt>
    public $reviewcorrectness;//<reviewcorrectness>4368</reviewcorrectness>
    public $reviewmarks;//<reviewmarks>4368</reviewmarks>
    public $reviewspecificfeedback;//<reviewspecificfeedback>4368</reviewspecificfeedback>
    public $reviewgeneralfeedback;//<reviewgeneralfeedback>4368</reviewgeneralfeedback>
    public $reviewrightanswer;//<reviewrightanswer>4368</reviewrightanswer>
    public $reviewoverallfeedback;//<reviewoverallfeedback>4368</reviewoverallfeedback>
    public $questionsperpage;//<questionsperpage>0</questionsperpage>
    public $navmethod;//<navmethod>free</navmethod>
    public $shufflequestions;//<shufflequestions>0</shufflequestions>
    public $shuffleanswers;//<shuffleanswers>1</shuffleanswers>
    public $sumgrades;//<sumgrades>1.00000</sumgrades>
    public $grade;//<grade>10.00000</grade>
    public $timecreated;//<timecreated>0</timecreated>
    public $timemodified;//<timemodified>1392280878</timemodified>
    public $password;//<password>password</password>
    public $subnet;//<subnet></subnet>
    public $browsersecurity;//<browsersecurity>-</browsersecurity>
    public $delay1;//<delay1>0</delay1>
    public $delay2;//<delay2>0</delay2>
    public $showuserpicture;//<showuserpicture>0</showuserpicture>
    public $showblocks;//<showblocks>0</showblocks>
		

    /**
     * @var int|Array
     */
    public $questions;//<questions>10750,0</questions>
    

	public $questionInstances = array();
	
	
	/**
	 * @var Feedback|Array
	 */
	public $feedbacks = array(); 
	
	
	public $filesIds = array();
	
	public function toXMLFile($repository) {
		
		$writer = new XMLWriter();
		$writer->openURI($repository.'/quiz.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
			$writer->startElement('activity');
				$writer->writeAttribute('id', $this->id);
				$writer->writeAttribute('moduleid', $this->moduleid);
				$writer->writeAttribute('modulename', $this->modulename);
				$writer->writeAttribute('contextid', $this->contextid);
				
				$writer->startElement('quiz');
					$writer->writeAttribute('id', $this->quizId);
					$writer->startElement('name');
						$writer->text($this->name);
					$writer->endElement();
					$writer->startElement('intro');
						$writer->text($this->intro);
					$writer->endElement();
					$writer->writeElement('introformat',$this->introformat);
					$writer->writeElement('timeopen',$this->timeopen);
					$writer->writeElement('timeclose',$this->timeclose);
					$writer->writeElement('timelimit',$this->timelimit);
					$writer->writeElement('overduehandling',$this->overduehandling);
					$writer->writeElement('graceperiod',$this->graceperiod);
					$writer->writeElement('preferredbehaviour',$this->preferredbehaviour);
					$writer->writeElement('attempts_number',$this->attempts_number);
					$writer->writeElement('attemptonlast',$this->attemptonlast);
					$writer->writeElement('grademethod',$this->grademethod);
					$writer->writeElement('decimalpoints',$this->decimalpoints);
					$writer->writeElement('questiondecimalpoints',$this->questiondecimalpoints);
					$writer->writeElement('reviewattempt',$this->reviewattempt);
					$writer->writeElement('reviewcorrectness',$this->reviewcorrectness);
					$writer->writeElement('reviewmarks',$this->reviewmarks);
					$writer->writeElement('reviewspecificfeedback',$this->reviewspecificfeedback);
					$writer->writeElement('reviewgeneralfeedback',$this->reviewgeneralfeedback);
					$writer->writeElement('reviewrightanswer',$this->reviewrightanswer);
					$writer->writeElement('reviewoverallfeedback',$this->reviewoverallfeedback);
					$writer->writeElement('questionsperpage',$this->questionsperpage);
					$writer->writeElement('navmethod',$this->navmethod);
					$writer->writeElement('shufflequestions',$this->shufflequestions);
					$writer->writeElement('shuffleanswers',$this->shuffleanswers);

					$questions = "0";
					foreach ($this->questions as $question){
						$questions = ",".$question;
					}
					$writer->writeElement('questions',$questions);
					
					$writer->writeElement('sumgrades',$this->sumgrades);
					$writer->writeElement('grade',$this->grade);
					$writer->writeElement('timecreated',$this->timecreated);
					$writer->writeElement('timemodified',$this->timemodified);
					$writer->writeElement('password',$this->password);
					$writer->writeElement('subnet',$this->subnet);
					$writer->writeElement('browsersecurity',$this->browsersecurity);
					$writer->writeElement('delay1',$this->delay1);
					$writer->writeElement('delay2',$this->delay2);
					$writer->writeElement('showuserpicture',$this->showuserpicture);
					$writer->writeElement('showblocks',$this->showblocks);
				
					$writer->startElement('question_instances');
					foreach ($this->questionInstances as $questionInstance){
						$writer->startElement('question_instance');
							$writer->writeAttribute('id', $questionInstance->id);
							$writer->writeElement('question',$questionInstance->question);
							$writer->writeElement('grade',$questionInstance->grade);
						$writer->endElement();
					}
					$writer->endElement();

					$writer->startElement('feedbacks');
					foreach ($this->feedbacks as $feedback){
						$writer->startElement('feedback');
							$writer->writeAttribute('id', $feedback->id);

							$writer->writeElement('feedbacktext',$feedback->feedbacktext);
							$writer->writeElement('feedbacktextformat',$feedback->feedbacktextformat);
							$writer->writeElement('mingrade',$feedback->mingrade);
							$writer->writeElement('maxgrade',$feedback->maxgrade);
							
						$writer->endElement();
					}
					$writer->endElement();
						
					$writer->startElement('overrides');
					$writer->endElement();
					
					$writer->startElement('grades');
					$writer->endElement();
						
					$writer->startElement('attempts');
					$writer->endElement();
						
				$writer->endElement();
			$writer->endElement();
		$writer->endDocument();
	}
}

class QuestionInstance {
	public $id; //id="1"
	
	public $question;// 		<userid>2</userid>
	public $grade;//         <concept>Entry1</concept>
	
	public function __construct($id,$question,$grade){
		$this->id = $id;
		$this->question = $question;
		$this->grade = $grade;
	}
	
}

class Feedback {
	public $id;
	
	public $feedbacktext;
	public $feedbacktextformat;
	public $mingrade;
	public $maxgrade;
	
	public function __construct($id,$feedbacktext,$feedbacktextformat,$mingrade,$maxgrade){
		$this->id = $id;
		$this->feedbacktext = $feedbacktext;
		$this->feedbacktextformat = $feedbacktextformat;
		$this->mingrade = $mingrade;
		$this->maxgrade = $maxgrade;			
	}
	
}