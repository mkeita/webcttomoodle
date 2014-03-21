<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * @author Marc
 *
 */
class CourseSelectionForm extends \moodleform {
	
	/**
	 * @var MigrationConnexion
	 */
	public $migrationConnexion;
	
	public function __construct($migrationConnexion){
		$this->migrationConnexion = $migrationConnexion;
		parent::__construct();
	
	}
	
	/*
	 * (non-PHPdoc) @see moodleform::definition()
	 */
	function definition() {
		
		// TODO Auto-generated method stub
		$mform = $this->_form;
		
		$mform->addElement('header', 'course_selection_hdr', get_string('course_selection_form_header','tool_webcttomoodle'));
		
		if($this->migrationConnexion->protocol==0){
			try{
				$sftp = new SFTPConnection("164.15.72.104");
			}catch (Exception $e){
				$mform->addElement('html',$e.'<br/>');
			}
		}elseif($this->migrationConnexion->protocol==1){
			$ftp = ftp_connect($this->migrationConnexion->ip, 21);
			
			if(!$ftp){
				$mform->addElement('html',get_string("no_ftp_connexion","tool_webcttomoodle"). '<br/>');
				return;
			}
		}elseif($this->migrationConnexion->protocol==2){
			if(!is_dir($this->migrationConnexion->repository)){
				$mform->addElement('html',get_string("no_directory","tool_webcttomoodle"). '<br/>');
			}
		} 
		
		
		$mform->addElement('html', get_string("backup_instructions","tool_webcttomoodle"). '<br/><br/>');
		
		$mform->addElement('textarea', 'learningContextIds', get_string('webct_learning_context_id','tool_webcttomoodle'),'wrap="virtual" rows="15" cols="50"');
		$mform->setType('learningContextIds', PARAM_TEXT);
		
		$this->add_action_buttons(false, get_string('backup_course_button', 'tool_webcttomoodle'));
	}
	
	function definition_after_data() {
		$mform = $this->_form;
	
		$mform->addElement('hidden', 'isBackup', true);
	}
}