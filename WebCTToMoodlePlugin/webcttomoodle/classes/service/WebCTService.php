<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once 'classes/model/WebCTModel.php';
require_once 'classes/model/MockModel.php';
require_once 'lib/Utilities.php';



class WebCTService {
	
	

	/**
	 * @param string $learningContextId
	 * @return GlobalModel
	 */
	public function createGlobalModel($learningContextId) {
		
		$model = new WebCTModel($learningContextId);
		
		//$model = new MockModel();
		
		//var_dump($model);
		echo 'MODEL CREATED'."\n";
		return $model;
		
	}
	
	
	
	
	/**
	 * @param GlobalModel $model
	 * @param WebCTServiceSettings $settings
	 */
	public function createBackup($model,$settings){
		
		$repository = "D:/Documents/ULB/Moodle Migration/MoodleBackups";
				
		$model->toMBZArchive($repository);
	}
	
}

class WebCTServiceSettings {
	
}