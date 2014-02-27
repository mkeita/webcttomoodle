<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * @author Marc
 *
 */
class FTPConnexionForm extends moodleform {
	
	/**
	 * @var FtpConnexion
	 */
	public $ftpConnexion;
	
	public function __construct($ftpConnexion){
		$this->ftpConnexion = $ftpConnexion;
		parent::__construct();
		
	}
	
	/*
	 * (non-PHPdoc) @see moodleform::definition()
	 */
	function definition() {
		global $DB;
		
		// TODO Auto-generated method stub
		$mform = $this->_form;
		
		$mform->addElement('header', 'ftp_form_hdr', get_string('ftp_form_header','tool_webcttomoodle'));

 		$mform->addElement('text', 'ftpip', get_string('ip','tool_webcttomoodle'));
 		$mform->setType('ftpip', PARAM_TEXT);
 		$mform->setDefault('ftpip', $this->ftpConnexion->ip);
		
 		$mform->addElement('text', 'ftpuser', get_string('user','tool_webcttomoodle'));
 		$mform->setType('ftpuser', PARAM_TEXT);
 		$mform->setDefault('ftpuser', $this->ftpConnexion->user);

 		$mform->addElement('text', 'ftppassword', get_string('password','tool_webcttomoodle'));
 		$mform->setType('ftppassword', PARAM_TEXT);
 		$mform->setDefault('ftppassword', $this->ftpConnexion->password);
 		
 		$mform->addElement('text', 'ftprepository', get_string('repository','tool_webcttomoodle'));
 		$mform->setType('ftprepository', PARAM_TEXT);
 		$mform->setDefault('ftprepository', $this->ftpConnexion->repository);
 			
		$this->add_action_buttons(false, get_string('save_button', 'tool_webcttomoodle'));
	}
	
	function definition_after_data() {
		$mform = $this->_form;
		
		$mform->addElement('hidden', 'isFtpSave', true);
	}
}

class MappingTable {
	
	
	
}
