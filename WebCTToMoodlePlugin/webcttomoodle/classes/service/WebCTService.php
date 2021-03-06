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
	
	public $currentProgress = 0;
	public $step = 1;
	
	
	/**
	 * @param string $learningContextId
	 * @param int $nbElemRec Repr�sente le nombre de learning context qui vont �tre r�cup�r�. 
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
		
		//$repository = $CFG->tempdir.'/backup_temp';
		$repository = $CFG->tempdir;
		
		$rapport = $repository.'/'.$model->rapportMigration->nomFichier;
	
		$archiveName = $model->toMBZArchive($repository);

		$migrationConnexion = $this->settings->migrationConnection;
		
		$destination = $migrationConnexion->repository.$model->moodle_backup->name;
		
		//Pas de d�placement de fichier pour l'instant
//		echo '<br/>ARCHIVE NAME = '.$archiveName.' </br>';
//		echo '<br/>REPORT NAME = '.$rapport.' </br>';
//		return;
		
		if($migrationConnexion->protocol==0){
			$sftp = new SFTPConnection($migrationConnexion->ip);
			$sftp->login($migrationConnexion->user, $migrationConnexion->password);
			
			$sftp->uploadFile($archiveName, $destination);
			$sftp->uploadFile($rapport,$migrationConnexion->repository.$model->rapportMigration->nomFichier);
			
			unlink($archiveName);
			unlink($rapport);
			
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
			
			unlink($archiveName);
			unlink($rapport);
			
		}elseif($migrationConnexion->protocol==2){
			rename($archiveName, $destination);
			rename($rapport,$migrationConnexion->repository.$model->rapportMigration->nomFichier);
		}
	}
	
	
	
	public function restoreExistingCourse($shortName, $backupFile){
		global $DB;
		
		$timestart=microtime(true);
		
		$course = $DB->get_record('course',array('shortname'=>$shortName));
		if(!empty($course)){
			echo 'Suppression du contenu du cours.... <br/>';			
			ob_flush();
			flush();
			$transaction = $DB->start_delegated_transaction();
			$options = array();
			$options['keep_roles_and_enrolments'] = 0;
			$options['keep_groups_and_groupings'] = 0;
			restore_dbops::delete_course_content($course->id, $options);
			$transaction->allow_commit();
			
			//Suppress the course content
			echo utf8_encode('Pr�paration de l\'archive.... <br/>');
			ob_flush();
			flush();
			$filePath = $this->prepareArchive($course->id, $backupFile);

			$this->restoreWebCTCourse($course->id, $filePath, backup::TARGET_EXISTING_DELETING);
			echo utf8_encode('<b>Cours restaur�.</b> <br/>');			
			ob_flush();
			flush();
		}
		
		$timeToRestore = floor(microtime(true) - $timestart);
		$hour = floor($timeToRestore/3600);
		$minute = floor(($timeToRestore - $hour*3600)/60);
		$second = $timeToRestore - $hour*3600 - $minute*60;
		
		echo '<b>Temps de restauration = '.$hour.'h '.$minute.'min '.$second.'sec ('.$timeToRestore.'s) </b><br/><br/><br/>';
		ob_flush();
		flush();
		error_log("COURS ".$shortName." restor� en ".$hour.'h '.$minute.'min '.$second.'sec ('.$timeToRestore.'s)');
		
	}

	public function restoreNewCourse($shortName,$backupFile){
		error_log('Cr�ation d\'un nouveau cours....'.$shortName);
		
		$timestart=microtime(true);
		
		//New course in category "Miscellaneous"
		echo utf8_encode('Cr�ation d\'un nouveau cours.... <br/>');
		ob_flush();
		flush();
		$courseId = restore_dbops::create_new_course('','', 1);
		
		if(!empty($courseId)){
			echo utf8_encode('Pr�paration de l\'archive.... <br/>');
			ob_flush();
			flush();
			$filePath = $this->prepareArchive($courseId, $backupFile);
			
			$this->restoreWebCTCourse($courseId, $filePath, backup::TARGET_NEW_COURSE);	

			echo utf8_encode('<b>Cours restaur�.</b> <br/>');
			ob_flush();
			flush();
		}
		
		$timeToRestore = floor(microtime(true) - $timestart);
		$hour = floor($timeToRestore/3600);
		$minute = floor(($timeToRestore - $hour*3600)/60);
		$second = $timeToRestore - $hour*3600 - $minute*60;
		
		echo '<b>Temps de restauration = '.$hour.'h '.$minute.'min '.$second.'sec ('.$timeToRestore.'s) </b><br/><br/><br/>';
		ob_flush();
		flush();
		error_log("COURS ".$shortName." restor� en ".$hour.'h '.$minute.'min '.$second.'sec ('.$timeToRestore.'s)');
		
	}
	
	protected function prepareArchive($courseId, $backupFile){
		global $CFG,$USER;
		
		$tmpdir = $CFG->tempdir . '/backup/';
		
		$filename = restore_controller::get_tempdir_name($courseId, $USER->id);
				
		$source = $tmpdir.$filename;
		
		$migrationConnection= $this->settings->migrationConnection;
		
		echo 'Taille de l\'archive = '.(filesize($migrationConnection->repository.$backupFile)/1000000).' M <br/>';
		
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
			@set_time_limit(0);
			$moveResult = copy($migrationConnection->repository.$backupFile, $source);
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
		global $CFG,$DB,$USER;
		
		$transaction = $DB->start_delegated_transaction();
		
		// Restore backup into course.
		$logger = new WebCTServiceLogger($CFG->debugdeveloper ? backup::LOG_DEBUG : backup::LOG_INFO);
		$progress = new WebCTServiceProgress($logger, $this->currentProgress, $this->step);
		
		$controller = new restore_controller($filepath, $courseId,
				backup::INTERACTIVE_NO, backup::MODE_GENERAL, $USER->id,
				$target,$progress);
		
		$controller->add_logger($logger);
		
		//$controller->get_logger()->set_next( new output_indented_logger(backup::LOG_INFO, true, true) );
		
		$controller->execute_precheck();
		
		echo 'Restauration du cours.... <br/>';
		ob_flush();
		flush();
		
		$controller->execute_plan();

		$controller->destroy();
		
		// Commit.
		$transaction->allow_commit();
		
		//post restauration..
		$this->postRestoration($courseId);
		
		return true;
	}
	
	
	protected function postRestoration($courseId){
		echo 'POST RESTORATION <br/>';
		
		//Upgrade the course format and number of sections
//		$courseFormat = course_get_format($courseId);		
//		$options = $courseFormat->get_format_options();
		$modinfo = get_fast_modinfo($courseId);
		$sections = $modinfo->get_section_info_all();
		$sectionsCount = count($sections)-1;

		$cw = new stdClass();
		$cw->id   = $courseId;
		$cw->format  = 'topics';
		$cw->numsections  = $sectionsCount;
		update_course($cw);
		
		//Move choose sections to the end
		//$this->moveSectionToEnd($courseId, utf8_encode('Section g�n�rale'));
		$this->moveSectionToEnd($courseId, utf8_encode('�valuations'));
		$this->moveSectionToEnd($courseId, utf8_encode('T�ches'));
		$this->moveSectionToEnd($courseId, utf8_encode('Modules d\'apprentissage'));
				
	}
	
	protected function moveSectionToEnd($courseId,$sectioName){
		global $DB;
		
		$modinfo = get_fast_modinfo($courseId);
		$sections = $modinfo->get_section_info_all();
		$sectionsCount = count($sections)-1;
		
		$course = $DB->get_record('course', array('id' => $courseId), '*', MUST_EXIST);
		foreach ($sections as $key=>$section){
			if($section->name==$sectioName){
				move_section_to($course,$section->section,$sectionsCount);
				break;
			}
		}
	}
		
}

class WebCTServiceSettings {
	
	
	/**
	 * @var MigrationConnexion
	 */
	
	public $migrationConnection;
}

class WebCTServiceLogger extends base_logger {

 	protected function action($message, $level, $options = null) {
        $prefix = $this->get_prefix($level, $options);
        $depth = isset($options['depth']) ? $options['depth'] : 0;
        // Depending of running from browser/command line, format differently
        error_log($prefix . str_repeat('  ', $depth) . $message);
//      ob_flush();
// 		flush();
        return true;
    }
}

class WebCTServiceProgress extends core_backup_progress {

	/**
	 * @var WebCTServiceLogger
	 */
	protected $logger;

	protected $currentProgress;
	protected $step;
	
	public function __construct($logger,$currentProgress=0, $step=1) {
		$this->logger=$logger;

		$this->currentProgress=$currentProgress;
		$this->step = $step;
	}
	
	public function update_progress() {
		if($this->is_in_progress_section()){
			$range = $this->get_progress_proportion_range();
//			$this->logger->process($this->get_current_description().' ==> '.$range[0].'-'.$range[1], backup::LOG_DEBUG);
			//$this->logger->process(var_dump(), backup::LOG_DEBUG);
			
			$progress = $this->currentProgress + $range[1]*100*$this->step;
			
			echo "<script>";
			echo "document.getElementById('pourcentage').innerHTML='".$progress."%';";
			echo "document.getElementById('barre').style.width='".$progress."%';";
			echo "document.getElementById('progress_bar_description').innerHTML='".$this->get_current_description()."';";
			echo "</script>";
			//ob_flush();
			flush();
		}
	}
}
