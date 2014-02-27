<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once 'classes/model/WebCTModel.php';
require_once 'classes/model/MockModel.php';
require_once 'lib/Utilities.php';



class WebCTService {
	
	/**
	 * @var WebCTServiceSettings
	 */
	public $settings;
	
	
	/**
	 * @param string $learningContextId
	 * @return GlobalModel
	 */
	public function createGlobalModel($learningContextId) {
		$model = new WebCTModel($learningContextId);
		return $model;
	}
	
	
	
	
	/**
	 * @param GlobalModel $model
	 * @param WebCTServiceSettings $settings
	 */
	public function createBackup($model){
		global $CFG;
		
		$ftpConnexion = $this->settings->ftpConnection;
		
		$repository = $CFG->tempdir;
				
		$archiveName = $model->toMBZArchive($repository);
		$destination = $ftpConnexion->repository.$model->moodle_backup->name;
		
		$ftp = ftp_connect($ftpConnexion->ip, 21);
		ftp_login($ftp, $ftpConnexion->user, $ftpConnexion->password);
		if (!ftp_chdir($ftp,$ftpConnexion->repository)){
			ftp_mkdir($ftp, $ftpConnexion->repository);
			ftp_chdir($ftp,$ftpConnexion->repository);
		}

		$file = ftp_put($ftp, $destination , $archiveName, FTP_BINARY);
		ftp_close($ftp);
		
	}
	
	
	
	public function restoreExistingCourse($shortName, $backupFile){
		global $DB;
		
		$course = $DB->get_record('course',array('shortname'=>$shortName));
		if(!empty($course)){
			$filePath = $this->prepareArchive($course->id, $backupFile);
			$this->restoreWebCTCourse($course->id, $filePath, backup::TARGET_EXISTING_DELETING);
		}
	}

	public function restoreNewCourse($backupFile){
	
		//New course in category "Miscellaneous"
		$courseId = restore_dbops::create_new_course('', '', 1);
		if(!empty($courseId)){
			$filePath = $this->prepareArchive($courseId, $backupFile);
			$this->restoreWebCTCourse($courseId, $filePath, backup::TARGET_NEW_COURSE);				
		}
		
	}
	
	protected function prepareArchive($courseId, $backupFile){
		global $CFG,$USER;
		
		$ftpConnexion = $this->settings->ftpConnection;
		
		$tmpdir = $CFG->tempdir . '/backup/';
		
		$filename = restore_controller::get_tempdir_name($courseId, $USER->id);
				
		$source = $tmpdir.$filename;
		
		$ftp = ftp_connect($ftpConnexion->ip, 21);
		ftp_login($ftp, $ftpConnexion->user, $ftpConnexion->password);
		ftp_chdir($ftp,$ftpConnexion->repository);
		$file = ftp_get($ftp, $source , $backupFile, FTP_BINARY);
		ftp_close($ftp);
		
		$filepath = restore_controller::get_tempdir_name($courseId, $USER->id);
		$pathname = $tmpdir . $filepath.'/';
		
		$fb = get_file_packer('application/vnd.moodle.backup');
		$result = $fb->extract_to_pathname($source,$pathname);
		
		if ($result) {
			fulldelete($source);
		}
		
		return $filepath;
	}
	
	protected function restoreWebCTCourse($courseId,$filepath,$target){
		global $DB,$USER;
		
		$transaction = $DB->start_delegated_transaction();
		
		// Restore backup into course.
		$controller = new restore_controller($filepath, $courseId,
				backup::INTERACTIVE_NO, backup::MODE_GENERAL, $USER->id,
				$target);
		$controller->execute_precheck();
		
		$options = array();
		$options['keep_roles_and_enrolments'] = 0;
		$options['keep_groups_and_groupings'] = 0;
		restore_dbops::delete_course_content($courseId, $options);
		
		$controller->execute_plan();
		
		// Commit.
		$transaction->allow_commit();
		
		return true;
	}
	
}

class WebCTServiceSettings {
	
	
	/**
	 * @var FTPConnexionForm
	 */
	
	public $ftpConnection;
}