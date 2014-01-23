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
		
		//$model = new WebCTModel($learningContextId);
		
		$model = new MockModel();
		
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
		
		//Create the backup repository
		$dir = $repository.'/'.mb_substr($model->moodle_backup->name, 0, -4);
		
		if(is_dir($dir)){
			rrmdir($dir);
		}
		mkdir($dir);
		
		$model->toXMLFile($dir);
		
		
		//$model->moodle_backup->toXMLFile($repository);
		//$model->moodle_backup->toXMLFile($repository);
		
		
		//zip the repertory to .mbz
		$packer = get_file_packer('application/vnd.moodle.backup');
		
		$packer->archive_to_pathname(array(null=>$dir), $dir.'.mbz');
		
		
	}
	
}

class WebCTServiceSettings {
	
}