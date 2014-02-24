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

	
	/**
	 * @param Question $question
	 */
	public function addQuestion($question){
		$question->category = $this;
		$this->questions[]= $question;
	}
}

abstract class Question {
	
	public $id;// 	<question id="2">
	public $parent;// 	<parent>0</parent>
	public $name;// 	<name>AM a 01</name>
	public $questiontext;// 	<questiontext>&lt;p&gt;&amp;lt;P&amp;gt;&amp;lt;font color="#3366FF"&amp;gt;&amp;amp;nbsp;&amp;amp;nbsp;&amp;amp;nbsp;Quelle est la &lt;br /&gt;masse d'une molécule de nitrate d'hydrogène &lt;br /&gt;(HNO&amp;lt;sub&amp;gt;3&amp;lt;/sub&amp;gt;) ?&amp;lt;/Font&amp;gt;&lt;br /&gt;&amp;lt;/P&amp;gt;&lt;/p&gt;
		// 	&lt;p&gt;&amp;lt;P&amp;gt;&amp;lt;A &lt;br /&gt;HREF="/webct/RelativeResourceManager/Template/Theorie/Tables/TableMassesatomiques.html" &lt;br /&gt;TARGET="_blank"&amp;gt;Tableau des masses atomiques &lt;br /&gt;relatives&amp;lt;/A&amp;gt;&amp;lt;/P&amp;gt;&lt;/p&gt;</questiontext>
	public $questiontextformat;// 	<questiontextformat>1</questiontextformat>
	public $generalfeedback;// 	<generalfeedback>&lt;p&gt;&amp;lt;P&amp;gt;&amp;lt;FONT COLOR="#000000"&amp;gt;1 mole de HNO&amp;lt;sub&amp;gt;3&amp;lt;/sub&amp;gt; &lt;/p&gt;</generalfeedback>
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
	
	
	public function __clone(){
		$category = null;
	}
	
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
	
	public function __clone(){
		parent::__clone();
		
		$this->answers = array_merge_recursive(array(), $this->answers);
		$this->multiChoice = clone $this->multiChoice;
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
	public $shortAnswer;

	public function __construct(){
		$this->qtype = "shortanswer";
	}

	public function __clone(){
		parent::__clone();
	
		$this->answers = array_merge_recursive(array(),$this->answers);
		$this->shortAnswer = clone $this->shortAnswer;
	}
	
	/* (non-PHPdoc)
	 * @see Question::toXMLFile()
	*/
	public function toXMLFile(&$writer) {
		$writer->startElement('question');
			parent::toXMLFile($writer);
	
			$writer->startElement('plugin_qtype_shortanswer_question');
				$this->shortAnswer->toXMLFile($writer);
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

	public function __clone(){
		parent::__clone();
	
		$this->multiAnswer = clone $this->multiAnswer;
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

    public function __clone(){
    	$this->sequence = array_merge_recursive(array(), $this->sequence);
    }
    
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

class JumbledSentenceQuestion extends MultiAnswerQuestion {

}

class MatchingQuestion extends Question {
	
	
	/**
	 * @var Matches
	 */
	public $matches;
	
	public function __construct(){
		$this->qtype = "match";
	}
	
	public function __clone(){
		parent::__clone();
	
		$this->matches = clone $this->matches;
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
	

	public function __clone(){
		$this->matchOptions = clone $this->matchOptions;		
		$this->matches = array_merge_recursive(array(), $this->matches);
	}
	
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

	public function __clone(){
		parent::__clone();
	
		$this->essay = clone $this->essay;
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

	
	public function __clone(){
		parent::__clone();
	
		$this->answers = array_merge_recursive(array(), $this->answers);
		$this->trueFalseAnswer = clone $this->trueFalseAnswer;
	}
	/* (non-PHPdoc)
	 * @see Question::toXMLFile()
	*/
	public function toXMLFile(&$writer) {
		$writer->startElement('question');
			parent::toXMLFile($writer);
	
			$writer->startElement('plugin_qtype_truefalse_question');
				$writer->startElement('answers');
					foreach ($this->answers as $answer){
						$answer->toXMLFile($writer);
					}
				$writer->endElement();
				$this->trueFalseAnswer->toXMLFile($writer);
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



class CalculatedQuestion extends Question {

	/**
	 * @var Answer|Array
	 */
	public $answers = array();


	/**
	 * @var NumericalUnit|Array
	 */	
	public $numericalUnits = array();
	
	/**
	 * @var NumericalOption|Array
	 */
	public $numericalOptions = array();
	

	/**
	 * @var DatasetDefinition|Array
	 */
	public $datasetDefinitions = array();

	/**
	 * @var CalculatedRecord|Array
	 */
	public $calculatedRecords = array();
	
	/**
	 * @var CalculatedOption|Array
	 */
	public $calculatedOptions = array();
	
	
	public function __construct(){
		$this->qtype = "calculated";
	}

	public function __clone(){
		parent::__clone();
	
		$this->answers = array_merge_recursive(array(), $this->answers);
		$this->numericalUnits = array_merge_recursive(array(), $this->numericalUnits);
		$this->numericalOptions = array_merge_recursive(array(), $this->numericalOptions);
		$this->datasetDefinitions = array_merge_recursive(array(), $this->datasetDefinitions);
		$this->calculatedRecords = array_merge_recursive(array(), $this->calculatedRecords);
		$this->calculatedOptions = array_merge_recursive(array(), $this->calculatedOptions);
	}
	
	/* (non-PHPdoc)
	 * @see Question::toXMLFile()
	*/
	public function toXMLFile(&$writer) {
		$writer->startElement('question');
			parent::toXMLFile($writer);
	
			$writer->startElement('plugin_qtype_calculated_question');
				$writer->startElement('answers');
				foreach ($this->answers as $answer){
					$answer->toXMLFile($writer);
				}
				$writer->endElement();
				
				$writer->startElement('numerical_units');
				foreach ($this->numericalUnits as $numericalUnit){
					$numericalUnit->toXMLFile($writer);
				}
				$writer->endElement();
				
				$writer->startElement('numerical_options');
				foreach ($this->numericalOptions as $numericalOption){
					$numericalOption->toXMLFile($writer);
				}
				$writer->endElement();
				
				$writer->startElement('dataset_definitions');
				foreach ($this->datasetDefinitions as $datasetDefinition){
					$datasetDefinition->toXMLFile($writer);
				}
				$writer->endElement();
				
				$writer->startElement('calculated_records');
				foreach ($this->calculatedRecords as $calculatedRecord){
					$calculatedRecord->toXMLFile($writer);
				}
				$writer->endElement();
				
				$writer->startElement('calculated_options');
				foreach ($this->calculatedOptions as $calculatedOption){
					$calculatedOption->toXMLFile($writer);
				}
				$writer->endElement();
				
			$writer->endElement();

		$writer->endElement();
	}

}


class NumericalUnit {
	public $id;
	
	public $multiplier;
	public $unit;
	
	public function __construct($id,$multiplier,$unit){
		$this->id = $id;
		$this->multiplier = $multiplier;
		$this->unit = $unit;
	}
	
	public function toXMLFile(&$writer){
		$writer->startElement('numerical_unit');
			$writer->writeAttribute('id',$this->id);
				
			$writer->writeElement('multiplier',$this->multiplier);
			$writer->writeElement('unit',$this->unit);
		$writer->endElement();
	}
}


class NumericalOption {
	public $id;

	public $showunits;
	public $unitsleft;
	public $unitgradingtype;
	public $unitpenalty;

	public function toXMLFile(&$writer){
		$writer->startElement('numerical_option');
			$writer->writeAttribute('id',$this->id);
	
			$writer->writeElement('showunits',$this->showunits);
			$writer->writeElement('unitsleft',$this->unitsleft);
			$writer->writeElement('unitgradingtype',$this->unitgradingtype);
			$writer->writeElement('unitpenalty',$this->unitpenalty);
		$writer->endElement();
	}
}


class DatasetDefinition {
	public $id;

	public $category;
	public $name;
	public $type;
	public $options;
	public $itemcount;
	
	/**
	 * @var DatasetItem|Array
	 */
	public $datasetItems = array();

	public function __clone(){
	
		$this->datasetItems = array_merge_recursive(array(), $this->datasetItems);
	}
	
	public function toXMLFile(&$writer){
		$writer->startElement('dataset_definition');
			$writer->writeAttribute('id',$this->id);
	
			$writer->writeElement('category',$this->category);
			$writer->writeElement('name',$this->name);
			$writer->writeElement('type',$this->type);
			$writer->writeElement('options',$this->options);
			$writer->writeElement('itemcount',$this->itemcount);
			
			$writer->startElement('dataset_items');
			foreach ($this->datasetItems as $datasetItem){
				$datasetItem->toXMLFile($writer);
			}
			$writer->endElement();
			
		$writer->endElement();
	}
}

class DatasetItem {
	public $id;

	public $number;
	public $value;
	
	public function __construct($id,$number,$value){
		$this->id=$id;
		$this->number=$number;
		$this->value=$value;
	}
	

	public function toXMLFile(&$writer){
		$writer->startElement('dataset_item');
			$writer->writeAttribute('id',$this->id);
	
			$writer->writeElement('number',$this->number);
			$writer->writeElement('value',$this->value);
		$writer->endElement();
	}
}


class CalculatedRecord {
	public $id;

	public $answer;
	public $tolerance;
	public $tolerancetype;
	public $correctanswerlength;
	public $correctanswerformat;

	public function toXMLFile(&$writer){
		$writer->startElement('calculated_record');
			$writer->writeAttribute('id',$this->id);
	
			$writer->writeElement('answer',$this->answer);
			$writer->writeElement('tolerance',$this->tolerance);
			$writer->writeElement('tolerancetype',$this->tolerancetype);
			$writer->writeElement('correctanswerlength',$this->correctanswerlength);
			$writer->writeElement('correctanswerformat',$this->correctanswerformat);
		
		$writer->endElement();
	}
}


class CalculatedOption {
	public $id;

	public $synchronize;
	public $single;
	public $shuffleanswers;
	public $correctfeedback;
	public $correctfeedbackformat;
	public $partiallycorrectfeedback;
	public $partiallycorrectfeedbackformat;
	public $incorrectfeedback;
	public $incorrectfeedbackformat;
	public $answernumbering;

	public function toXMLFile(&$writer){
		$writer->startElement('calculated_option');
			$writer->writeAttribute('id',$this->id);
	
			$writer->writeElement('synchronize',$this->synchronize);
			$writer->writeElement('single',$this->single);
			$writer->writeElement('shuffleanswers',$this->shuffleanswers);
			$writer->writeElement('correctfeedback',$this->correctfeedback);
			$writer->writeElement('correctfeedbackformat',$this->correctfeedbackformat);
			$writer->writeElement('partiallycorrectfeedback',$this->partiallycorrectfeedback);
			$writer->writeElement('partiallycorrectfeedbackformat',$this->partiallycorrectfeedbackformat);
			$writer->writeElement('incorrectfeedback',$this->incorrectfeedback);
			$writer->writeElement('incorrectfeedbackformat',$this->incorrectfeedbackformat);
			$writer->writeElement('answernumbering',$this->answernumbering);

		$writer->endElement();
	}
}


class CombinaisonMultiChoiceQuestion extends MultiChoiceQuestion {
	
}

class RandomQuestion extends Question {
	
	public function __construct(){
		$this->qtype = "random";
	}
	
	/* (non-PHPdoc)
	 * @see Question::toXMLFile()
	*/
	public function toXMLFile(&$writer) {
		$writer->startElement('question');
		parent::toXMLFile($writer);
		
		$writer->endElement();
	}
}
