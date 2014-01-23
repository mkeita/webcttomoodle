<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * @author Marc
 *
 */
class CourseSelectionForm extends \moodleform {
	
	/*
	 * (non-PHPdoc) @see moodleform::definition()
	 */
	protected function definition() {
		// TODO Auto-generated method stub
		$mform = $this->_form;
		
		$mform->addElement('header', 'course_selection_hdr', get_string('course_selection_form_header','tool_webcttomoodle'));
		
		$mform->addElement('text', 'courseId', get_string('course_id','tool_webcttomoodle'));
		$mform->setType('courseId', PARAM_INT);
		
		$mform->addElement('text', 'learningContextId', get_string('webct_learning_context_id','tool_webcttomoodle'),"366249217001");
		$mform->setType('learningContextId', PARAM_TEXT);
		
		$this->add_action_buttons(false, get_string('backup_course_button', 'tool_webcttomoodle'));
	}
}