<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * @author Marc
 *
 */
class TestForm extends \moodleform {
	
	public function __construct(){
		parent::__construct();
	
	}
	
	/*
	 * (non-PHPdoc) @see moodleform::definition()
	 */
	function definition() {
		
		// TODO Auto-generated method stub
		$mform = $this->_form;
		
		$mform->addElement('header', 'course_selection_hdr', get_string('course_selection_form_header','tool_webcttomoodle'));
		
		$skillsarray = array(
		    'val1' => 'Skill A',
		    'val2' => 'Skill B',
		    'val3' => 'Skill C'
		);
		$mform->addElement('select', 'md_skills', "METADATA", $skillsarray);
		$mform->getElement('md_skills')->setMultiple(true);
		// This will select the skills A and B.
		$mform->getElement('md_skills')->setSelected(array('val1', 'val2'));

		
		$skillsarray = array(
				'val1' => 'Skill A2',
				'val2' => 'Skill B2',
				'val3' => 'Skill C2'
		);
		$mform->addElement('select', 'md_skills2', "METADATA", $skillsarray);
		//$mform->getElement('md_skills2')->setMultiple(true);
		// This will select the skills A and B.
		$mform->getElement('md_skills2')->setSelected(array('val1'));
		
		
		$this->add_action_buttons(false, get_string('backup_course_button', 'tool_webcttomoodle'));
	}
	
	function definition_after_data() {
		$mform = $this->_form;
	}
}