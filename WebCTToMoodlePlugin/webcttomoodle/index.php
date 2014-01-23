<?php
// 

/**
 * Create Moodle backup files from WebCT
 *
 * @package    toolwebcttomoodle
*/
require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once 'classes/view/CourseSelectionForm.php';
require_once 'classes/service/WebCTService.php';

admin_externalpage_setup('toolwebcttomoodle');

$courseId = optional_param('courseId', -1, PARAM_INT);
$learningContextId = optional_param('learningContextId', "", PARAM_TEXT);

echo 'COURSE_ID ='.$courseId."\n";

if(empty($learningContextId)){
	echo $OUTPUT->header();	
	echo $OUTPUT->heading(get_string('pageheader', 'tool_webcttomoodle'));
		
	$courseSelectioForm = new CourseSelectionForm();
	$courseSelectioForm->display();	
	
	echo $OUTPUT->footer();	
	die();
	
}else {	
	$settings=null;	
	
	$webCTService = new WebCTService();
	
	$model = $webCTService->createGlobalModel($learningContextId);
	 
	$webCTService->createBackup($model, $settings);
	
	echo $OUTPUT->header();
	echo $OUTPUT->footer();
	die();
}

?>