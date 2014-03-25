<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * @author Marc
 *
 */
class FTPConnexionForm extends moodleform {
	
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
		global $DB;
		
		// TODO Auto-generated method stub
		$mform = $this->_form;
		
		$mform->addElement('header', 'ftp_form_hdr', get_string('ftp_form_header','tool_webcttomoodle'));

		$radioarray=array();
		$radioarray[] =& $mform->createElement('radio', 'protocols', '', get_string('sftp','tool_webcttomoodle'), 0);
		$radioarray[] =& $mform->createElement('radio', 'protocols', '', get_string('ftp','tool_webcttomoodle'), 1);
		$radioarray[] =& $mform->createElement('radio', 'protocols', '', get_string('local','tool_webcttomoodle'), 2);
		$mform->addGroup($radioarray, 'radioar', get_string('transfer_protocol','tool_webcttomoodle'), array(' '), false);
		$mform->setDefault('protocols', $this->migrationConnexion->protocol);
				
 		$mform->addElement('text', 'ip', get_string('ip','tool_webcttomoodle'));
 		$mform->setType('ip', PARAM_TEXT);
 		$mform->setDefault('ip', $this->migrationConnexion->ip);
		
 		$mform->addElement('text', 'user', get_string('user','tool_webcttomoodle'));
 		$mform->setType('user', PARAM_TEXT);
 		$mform->setDefault('user', $this->migrationConnexion->user);

 		$mform->addElement('text', 'password', get_string('password','tool_webcttomoodle'));
 		$mform->setType('password', PARAM_TEXT);
 		$mform->setDefault('password', $this->migrationConnexion->password);
 		
 		$mform->addElement('text', 'repository', get_string('repository','tool_webcttomoodle'));
 		$mform->setType('repository', PARAM_TEXT);
 		$mform->setDefault('repository', $this->migrationConnexion->repository);
 		$mform->addHelpButton('repository', 'repository_format', 'tool_webcttomoodle');
 			
		$this->add_action_buttons(false, get_string('save_button', 'tool_webcttomoodle'));
	}
	
	function definition_after_data() {
		$mform = $this->_form;
		
		$mform->addElement('hidden', 'isConnexionSave', true);
	}
}

class MappingTable {
	
	
	
}
