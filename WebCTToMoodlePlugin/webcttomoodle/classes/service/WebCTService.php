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
	 * @param int $nbElemRec Représente le nombre de learning context qui vont être récupéré. 
	 * @return GlobalModel
	 */
	public function createGlobalModel($learningContextId , $nbElemRec, &$indice) {
		$model = new WebCTModel($learningContextId , $nbElemRec, $indice);
		return $model;
	}
	
	
	
	
	/**
	 * @param GlobalModel $model
	 * @param WebCTServiceSettings $settings
	 */
	public function createBackup($model){
		global $CFG;
		
		$repository = $CFG->tempdir;
		
		
		$rapport = $repository.'/'.$model->rapportMigration->nomFichier;
	
		$archiveName = $model->toMBZArchive($repository);

		$migrationConnexion = $this->settings->migrationConnection;
		
		$destination = $migrationConnexion->repository.$model->moodle_backup->name;
		
		if($migrationConnexion->protocol==0){
			$sftp = new SFTPConnection($migrationConnexion->ip);
			$sftp->login($migrationConnexion->user, $migrationConnexion->password);
			
			$sftp->uploadFile($archiveName, $destination);
			$sftp->uploadFile($rapport,$migrationConnexion->repository.$model->rapportMigration->nomFichier);
			
		}elseif($migrationConnexion->protocol==1){
			
			
			$ftp = ftp_connect($migrationConnexion->ip, 21);
			ftp_login($ftp, $migrationConnexion->user, $migrationConnexion->password);
			if (!ftp_chdir($ftp,$migrationConnexion->repository)){
				ftp_mkdir($ftp, $migrationConnexion->repository);
				ftp_chdir($ftp,$migrationConnexion->repository);
			}
	
			$file = ftp_put($ftp, $destination , $archiveName, FTP_BINARY);			
			$file = ftp_put($ftp, $migrationConnexion->repository.$model->rapportMigration->nomFichier , $rapport , FTP_BINARY);
			
			ftp_close($ftp);
		}elseif($migrationConnexion->protocol==2){
			rename($archiveName, $destination);
			rename($rapport,$migrationConnexion->repository.$model->rapportMigration->nomFichier);
		}
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
		
		$tmpdir = $CFG->tempdir . '/backup/';
		
		$filename = restore_controller::get_tempdir_name($courseId, $USER->id);
				
		$source = $tmpdir.$filename;
		
		$migrationConnection= $this->settings->migrationConnection;
		if($migrationConnection->protocol==0){
			$sftp = new SFTPConnection($migrationConnection->ip);
			$sftp->login($migrationConnection->user, $migrationConnection->password);
				
			$sftp->receiveFile($migrationConnection->repository.$backupFile, $source);
			
		}elseif($migrationConnection->protocol==1){
			$ftp = ftp_connect($migrationConnection->ip, 21);
			ftp_login($ftp, $migrationConnection->user, $migrationConnection->password);
			ftp_chdir($ftp,$migrationConnection->repository);
			
			$file = ftp_get($ftp, $source , $backupFile, FTP_BINARY);
			ftp_close($ftp);
		}elseif($migrationConnection->protocol==2){
			$moveResult = rename($migrationConnection->repository.$backupFile, $source);
		}
		
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
	 * @var MigrationConnexion
	 */
	
	public $migrationConnection;
}