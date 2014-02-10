<?php
require_once 'classes/model/IBackupModel.php';

class Questions implements \IBackupModel {
	
	/**
	 * @var QuestionCategory|Array
	 */
	public $question_categories = array() ;
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/questions.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);		
			$writer->startElement('question_categories');
			foreach ($this->question_categories as $question_category){
	 			$writer->startElement('question_category');
	 				$writer->writeAttribute('id',$question_category->id);
					
					$writer->writeElement('name',$question_category->name);
					$writer->writeElement('contextid',$question_category->contextid);
					$writer->writeElement('contextlevel',$question_category->contextlevel);
					$writer->writeElement('contextinstanceid',$question_category->contextinstanceid);
					$writer->writeElement('info',$question_category->info);
					$writer->writeElement('infoformat',$question_category->infoformat);
					$writer->writeElement('stamp',$question_category->stamp);
					$writer->writeElement('parent',$question_category->parent);
					$writer->writeElement('sortorder',$question_category->sortorder);
					
					$writer->startElement('questions');
					foreach ($question_category->questions as $question){
						$question->toXMLFile($writer);
					}
					$writer->endElement();
						
				$writer->endElement();
			}
			$writer->endElement();
		$writer->endDocument();
		
	}
}


class QuestionCategory{
	
	public $id;
	
	public $name;
	public $contextid;
	public $contextlevel;
	public $contextinstanceid;
	public $info;
	public $infoformat;
	public $stamp;
	public $parent;
	public $sortorder;
	
	
	/**
	 * @var Question|Array
	 */
	public $questions = array() ;	
}

abstract class Question {
	
	public $id;// 	<question id="2">
	public $parent;// 	<parent>0</parent>
	public $name;// 	<name>AM a 01</name>
	public $questiontext;// 	<questiontext>&lt;p&gt;&amp;lt;P&amp;gt;&amp;lt;font color="#3366FF"&amp;gt;&amp;amp;nbsp;&amp;amp;nbsp;&amp;amp;nbsp;Quelle est la &lt;br /&gt;masse d'une mol�cule de nitrate d'hydrog�ne &lt;br /&gt;(HNO&amp;lt;sub&amp;gt;3&amp;lt;/sub&amp;gt;) ?&amp;lt;/Font&amp;gt;&lt;br /&gt;&amp;lt;/P&amp;gt;&lt;/p&gt;
		// 	&lt;p&gt;&amp;lt;P&amp;gt;&amp;lt;A &lt;br /&gt;HREF="/webct/RelativeResourceManager/Template/Theorie/Tables/TableMassesatomiques.html" &lt;br /&gt;TARGET="_blank"&amp;gt;Tableau des masses atomiques &lt;br /&gt;relatives&amp;lt;/A&amp;gt;&amp;lt;/P&amp;gt;&lt;/p&gt;</questiontext>
	public $questiontextformat;// 	<questiontextformat>1</questiontextformat>
	public $generalfeedback;// 	<generalfeedback>&lt;p&gt;&amp;lt;P&amp;gt;&amp;lt;FONT COLOR="#000000"&amp;gt;1 mole de HNO&amp;lt;sub&amp;gt;3&amp;lt;/sub&amp;gt;�&lt;/p&gt;</generalfeedback>
	public $generalfeedbackformat;// 	<generalfeedbackformat>1</generalfeedbackformat>
	public $defaultmark;// 	<defaultmark>1.0000000</defaultmark>
	public $penalty;// 	<penalty>0.3333333</penalty>
	public $qtype;// 	<qtype>multichoice</qtype>
	public $length;// 	<length>1</length>
	public $stamp;// 	<stamp>localhost+140131160414+7AXnY7</stamp>
	public $version;// 	<version>localhost+140131160414+HfBHPl</version>
	public $hidden;// 	<hidden>0</hidden>
	public $timecreated;// 	<timecreated>1391184254</timecreated>
	public $timemodified;// 	<timemodified>1391184254</timemodified>
	public $createdby;// 	<createdby>2</createdby>
	public $modifiedby;// 	<modifiedby>2</modifiedby>
	
	
	/**
	 * @var QuestionCategory
	 */
	public $category;
	
	/**
	 * @param XMLWriter $writer
	 */
	public function toXMLFile(&$writer){
		$writer->writeAttribute('id',$this->id);
			
		$writer->writeElement('parent',$this->parent);
		$writer->writeElement('name',$this->name);
		$writer->writeElement('questiontext',$this->questiontext);
		$writer->writeElement('questiontextformat',$this->questiontextformat);
		$writer->writeElement('generalfeedback',$this->generalfeedback);
		$writer->writeElement('generalfeedbackformat',$this->generalfeedbackformat);
		$writer->writeElement('defaultmark',$this->defaultmark);
		$writer->writeElement('penalty',$this->penalty);
		$writer->writeElement('qtype',$this->qtype);
		$writer->writeElement('length',$this->length);
		$writer->writeElement('stamp',$this->stamp);
		$writer->writeElement('version',$this->version);
		$writer->writeElement('hidden',$this->hidden);
		$writer->writeElement('timecreated',$this->timecreated);
		$writer->writeElement('timemodified',$this->timemodified);
		$writer->writeElement('createdby',$this->createdby);
		$writer->writeElement('modifiedby',$this->modifiedby);
		
		$writer->startElement('question_hints');
		$writer->endElement();

		$writer->startElement('tags');
		$writer->endElement();
		
	}
}

class MultiChoiceQuestion extends Question {	
	
	/**
	 * @var Answer|Array
	 */
	public $answers = array();

	
	/**
	 * @var MultiChoice
	 */
	public $multiChoice;
	
	public function __construct(){
		$this->qtype = "multichoice";
	}

	/* (non-PHPdoc)
	 * @see Question::toXMLFile()
	 */
	public function toXMLFile(&$writer) {
		$writer->startElement('question');		
			parent::toXMLFile($writer);
		
			$writer->startElement('plugin_qtype_multichoice_question');
				$this->multiChoice->toXMLFile($writer);
				$writer->startElement('answers');
					foreach ($this->answers as $answer){
						$answer->toXMLFile($writer);
					}
				$writer->endElement();
			$writer->endElement();
				
		$writer->endElement();
	}
	
}

class MultiChoice {
	public $id;// 	<multichoice id="2">
	
	public $layout;// 	<layout>0</layout>
	public $single;// 	<single>1</single>
	public $shuffleanswers;// 	<shuffleanswers>0</shuffleanswers>
	public $correctfeedback;// 	<correctfeedback>&lt;p&gt;Your answer is correct.&lt;/p&gt;</correctfeedback>
	public $correctfeedbackformat;// 	<correctfeedbackformat>1</correctfeedbackformat>
	public $partiallycorrectfeedback;// 	<partiallycorrectfeedback>&lt;p&gt;Your answer is partially correct.&lt;/p&gt;</partiallycorrectfeedback>
	public $partiallycorrectfeedbackformat;// 	<partiallycorrectfeedbackformat>1</partiallycorrectfeedbackformat>
	public $incorrectfeedback;// 	<incorrectfeedback>&lt;p&gt;Your answer is incorrect.&lt;/p&gt;</incorrectfeedback>
	public $incorrectfeedbackformat;// 	<incorrectfeedbackformat>1</incorrectfeedbackformat>
	public $answernumbering;// 	<answernumbering>abc</answernumbering>
	public $shownumcorrect;// 	<shownumcorrect>1</shownumcorrect>
	
	
	public function toXMLFile(&$writer){
		$writer->startElement('multichoice');
			$writer->writeAttribute('id',$this->id);
				
			$writer->writeElement('layout',$this->layout);
			$writer->writeElement('single',$this->single);
			$writer->writeElement('shuffleanswers',$this->shuffleanswers);
			$writer->writeElement('correctfeedback',$this->correctfeedback);
			$writer->writeElement('correctfeedbackformat',$this->correctfeedbackformat);
			$writer->writeElement('partiallycorrectfeedback',$this->partiallycorrectfeedback);
			$writer->writeElement('partiallycorrectfeedbackformat',$this->partiallycorrectfeedbackformat);
			$writer->writeElement('incorrectfeedback',$this->incorrectfeedback);
			$writer->writeElement('incorrectfeedbackformat',$this->incorrectfeedbackformat);
			$writer->writeElement('answernumbering',$this->answernumbering);
			$writer->writeElement('shownumcorrect',$this->shownumcorrect);
		
		$writer->endElement();
	}
}


class Answer {
	public $id;// 	<answer id="4">
	
	public $answertext;// 	<answertext>&lt;p&gt;1,05 10&amp;lt;SUP&amp;gt;-22&amp;lt;/SUP&amp;gt; g&lt;/p&gt;</answertext>
	public $answerformat;// 	<answerformat>1</answerformat>
	public $fraction;// 	<fraction>1.0000000</fraction>
	public $feedback;// 	<feedback>&lt;p&gt;C'est exact.&lt;/p&gt;</feedback>
	public $feedbackformat;// 	<feedbackformat>1</feedbackformat>
	
	public function toXMLFile(&$writer){
		$writer->startElement('answer');
			$writer->writeAttribute('id',$this->id);
			
			$writer->writeElement('answertext',$this->answertext);
			$writer->writeElement('answerformat',$this->answerformat);
			$writer->writeElement('fraction',$this->fraction);
			$writer->writeElement('feedback',$this->feedback);
			$writer->writeElement('feedbackformat',$this->feedbackformat);
		$writer->endElement();
	}
}


class ShortAnswerQuestion extends Question {

	/**
	 * @var Answer|Array
	 */
	public $answers = array();


	/**
	 * @var ShortAnswer
	*/
	public $shorAnswer;

	public function __construct(){
		$this->qtype = "shortanswer";
	}

	/* (non-PHPdoc)
	 * @see Question::toXMLFile()
	*/
	public function toXMLFile(&$writer) {
		$writer->startElement('question');
			parent::toXMLFile($writer);
	
			$writer->startElement('plugin_qtype_shortanswer_question');
				$this->shorAnswer->toXMLFile($writer);
				$writer->startElement('answers');
				foreach ($this->answers as $answer){
					$answer->toXMLFile($writer);
				}
				$writer->endElement();
			$writer->endElement();

		$writer->endElement();
	}

}

class ShortAnswer {
	public $id;// 	<shortanswer id="2">

	public $usecase;// 	 <usecase>0</usecase>

	public function toXMLFile(&$writer){
		$writer->startElement('shortanswer');
			$writer->writeAttribute('id',$this->id);
		
			$writer->writeElement('usecase',$this->usecase);
		$writer->endElement();
	}
}


class MultiAnswerQuestion extends Question {

	/**
	 * @var MultiAnswer
	*/
	public $multiAnswer;

	public function __construct(){
		$this->qtype = "multianswer";
	}

	
	/**
	 * @param Question $question
	 */
	public function fillWith($question){
		$this->id=$question->id;
		$this->parent=$question->parent;
		$this->name=$question->name;
		$this->questiontext=$question->questiontext;
		$this->questiontextformat=$question->questiontextformat;
		$this->generalfeedback=$question->generalfeedback;
		$this->generalfeedbackformat=$question->generalfeedbackformat;
		$this->defaultmark=$question->defaultmark;
		$this->penalty=$question->penalty;
		$this->length=$question->length;
		$this->stamp=$question->stamp;
		$this->version=$question->version;
		$this->hidden=$question->hidden;
		$this->timecreated=$question->timecreated;
		$this->timemodified=$question->timemodified;
		$this->createdby=$question->createdby;
		$this->modifiedby=$question->modifiedby;
		
		$this->category=$question->category;
	}
	
	
	/* (non-PHPdoc)
	 * @see Question::toXMLFile()
	*/
	public function toXMLFile(&$writer) {
		$writer->startElement('question');
			parent::toXMLFile($writer);
	
			$writer->startElement('plugin_qtype_multianswer_question');
				$this->multiAnswer->toXMLFile($writer);
				$writer->writeElement('answers',"");
			$writer->endElement();
		$writer->endElement();
	}

}


class MultiAnswer {
	public $id;// 	<shortanswer id="2">

	/**
	 * @var int
	 */
	public $question;// 	 <question>3605</question>
	
	/**
	 * @var int|Array
	 */
    public $sequence = array();//        <sequence>3606,3607,3608,3609,3610</sequence>

	public function toXMLFile(&$writer){
		$writer->startElement('multianswer');
			$writer->writeAttribute('id',$this->id);
	
			$writer->writeElement('question',$this->question);
			
			$sequence ="";
			foreach ($this->sequence as $item){
				$sequence=$sequence.$item.',';
			}
			$writer->writeElement('sequence',substr($sequence,0,-1));
			
		$writer->endElement();
	}
}

class FillInBlankQuestion extends MultiAnswerQuestion {
	
}

class MatchingQuestion extends Question {
	
	
	/**
	 * @var Matches
	 */
	public $matches;
	
	public function __construct(){
		$this->qtype = "match";
	}
	

	/* (non-PHPdoc)
	 * @see Question::toXMLFile()
	*/
	public function toXMLFile(&$writer) {
		$writer->startElement('question');
		parent::toXMLFile($writer);
	
			$writer->startElement('plugin_qtype_match_question');
				$this->matches->toXMLFile($writer);
			$writer->endElement();
	
		$writer->endElement();
	}
	
}


class Matches {
	
	/**
	 * @var MatchOptions
	 */
	public $matchOptions;
	
	
	/**
	 * @var Match|Array
	 */
	public $matches = array();
	

	public function toXMLFile(&$writer){
		$writer->startElement('matchoptions');
			$writer->writeAttribute('id',$this->matchOptions->id);
		
			$writer->writeElement('shuffleanswers',$this->matchOptions->shuffleanswers);
			$writer->writeElement('correctfeedback',$this->matchOptions->correctfeedback);
			$writer->writeElement('correctfeedbackformat',$this->matchOptions->correctfeedbackformat);
			$writer->writeElement('partiallycorrectfeedback',$this->matchOptions->partiallycorrectfeedback);
			$writer->writeElement('partiallycorrectfeedbackformat',$this->matchOptions->partiallycorrectfeedbackformat);
			$writer->writeElement('incorrectfeedback',$this->matchOptions->incorrectfeedback);				
			$writer->writeElement('incorrectfeedbackformat',$this->matchOptions->incorrectfeedbackformat);
			$writer->writeElement('shownumcorrect',$this->matchOptions->shownumcorrect);				
		$writer->endElement();
		
		$writer->startElement('matches');
		foreach ($this->matches as $match){
			$writer->startElement('match');
				$writer->writeAttribute('id',$match->id);
				
				$writer->writeElement('questiontext',$match->questiontext);
				$writer->writeElement('questiontextformat',$match->questiontextformat);
				$writer->writeElement('answertext',$match->answertext);
			$writer->endElement();
		}
		$writer->endElement();
			
	}
	
}


class MatchOptions {
	public $id;
	public $shuffleanswers;
	public $correctfeedback;
	public $correctfeedbackformat;
	public $partiallycorrectfeedback;
	public $partiallycorrectfeedbackformat;
	public $incorrectfeedback;
	public $incorrectfeedbackformat;
	public $shownumcorrect;
}

class Match {
	public $id;
	public $questiontext;
	public $questiontextformat;
	public $answertext;
}


class ParagraphQuestion extends Question {


	/**
	 * @var Essay
	 */
	public $essay;

	public function __construct(){
		$this->qtype = "essay";
	}


	/* (non-PHPdoc)
	 * @see Question::toXMLFile()
	*/
	public function toXMLFile(&$writer) {
		$writer->startElement('question');
		parent::toXMLFile($writer);

			$writer->startElement('plugin_qtype_essay_question');
				$writer->startElement('essay');
					$writer->writeAttribute('id',$this->essay->id);
					
					$writer->writeElement('responseformat',$this->essay->responseformat);
					$writer->writeElement('responsefieldlines',$this->essay->responsefieldlines);
					$writer->writeElement('attachments',$this->essay->attachments);
					$writer->writeElement('graderinfo',$this->essay->graderinfo);
					$writer->writeElement('graderinfoformat',$this->essay->graderinfoformat);
					$writer->writeElement('responsetemplate',$this->essay->responsetemplate);
					$writer->writeElement('responsetemplateformat',$this->essay->responsetemplateformat);
					
				$writer->endElement();
			$writer->endElement();

		$writer->endElement();
	}

}

class Essay {
	public $id;
	public $responseformat;
	public $responsefieldlines;
	public $attachments;
	public $graderinfo;
	public $graderinfoformat;
	public $responsetemplate;
	public $responsetemplateformat;
}


class TrueFalseQuestion extends Question {

	/**
	 * @var Answer|Array
	 */
	public $answers = array();


	/**
	 * @var TrueFalseAnswer
	*/
	public $trueFalseAnswer;

	public function __construct(){
		$this->qtype = "truefalse";
	}

	/* (non-PHPdoc)
	 * @see Question::toXMLFile()
	*/
	public function toXMLFile(&$writer) {
		$writer->startElement('question');
			parent::toXMLFile($writer);
	
			$writer->startElement('plugin_qtype_truefalse_question');
				$this->trueFalseAnswer->toXMLFile($writer);
				$writer->startElement('answers');
					foreach ($this->answers as $answer){
						$answer->toXMLFile($writer);
					}
				$writer->endElement();
			$writer->endElement();

		$writer->endElement();
	}

}

class TrueFalseAnswer {
	public $id;//

	public $trueanswer;//
	public $falseanswer;//

	public function toXMLFile(&$writer){
		$writer->startElement('truefalse');
			$writer->writeAttribute('id',$this->id);

			$writer->writeElement('trueanswer',$this->trueanswer);
			$writer->writeElement('falseanswer',$this->falseanswer);
			
		$writer->endElement();
	}
}

