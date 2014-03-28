<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once('classes/utils/SFTPConnection.php');
/**
 * @author Marc
 *
 */
class RestoreForm extends moodleform {
	
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
		
		$mform->addElement('header', 'restore_form_hdr', get_string('restore_form_header','tool_webcttomoodle'));
		
		$mform->addElement('html', get_string("restore_instructions","tool_webcttomoodle"). '<br/>');
		
		
		if($this->migrationConnexion->protocol==0){
			$sftp = new SFTPConnection($this->migrationConnexion->ip);
			$sftp->login($this->migrationConnexion->user, $this->migrationConnexion->password);
			
			$files = $sftp->scanFilesystem($this->migrationConnexion->repository);

			if(empty($files)){
				$mform->addElement('html', get_string("no_files","tool_webcttomoodle") . '<br/>');
				return;
			}
				
		}elseif($this->migrationConnexion->protocol==1){
			$ftp = ftp_connect($this->migrationConnexion->ip, 21);
			
			if(!$ftp){
				$mform->addElement('html', get_string("no_ftp_connexion","tool_webcttomoodle"). '<br/>');
				return;
			}
			
			ftp_login($ftp, $this->migrationConnexion->user, $this->migrationConnexion->password);
			
			
			if(!ftp_chdir($ftp, $this->migrationConnexion->repository)){
				$mform->addElement('html', get_string("no_directory","tool_webcttomoodle"). '<br/>');
				ftp_close($ftp);	
				return;
			}
			
			$files = ftp_nlist($ftp, ".");
			
			//récupère la liste des backups disponibles (ftp)
			
			if(empty($files)){
				$mform->addElement('html', get_string("no_files","tool_webcttomoodle") . '<br/>');
				ftp_close($ftp);
				return;
			}
			
			ftp_close($ftp);
					
				
		}elseif($this->migrationConnexion->protocol==2){
			if(is_dir($this->migrationConnexion->repository)==false){
				$mform->addElement('html', get_string("no_directory","tool_webcttomoodle"). '<br/>');
				return;
			}
			
			$files = $this->scan_dir($this->migrationConnexion->repository);

			if(empty($files)){
				$mform->addElement('html', get_string("no_files","tool_webcttomoodle") . '<br/>');
				return;
			}
		}		
		
		
		$codes = array();
		
		$table =
		"<table>
			<tbody>";
			
			foreach ($files as $file){
				$result = null;
				preg_match('/(?i)(.+?)__BACKUP/',$file,$result);
				$code = "";
				$moodleShortName = "";
				
				if(count($result)>=2){
					$code = $result[1];
					
					$strpos1 = strpos($code,"-");
					$strpos2 = strpos($code,"-",$strpos1+1);
					$codeToFind = $code;					
					if($strpos2){
						$codeToFind = substr_replace($code,'', $strpos2,1);
						$codeToFind .='-';
					}
					$codeToFind .= "%";
					
					$courses = $DB->get_records_sql('SELECT * FROM mdl_course WHERE '.$DB->sql_like('shortname',':sname'), array('sname'=>$codeToFind));
					
					if(count($courses)>0){
						foreach($courses as $course){
							$moodleShortName =$course->shortname;						
							$table.="<tr><td><input type='text' name='$moodleShortName' value='$moodleShortName'/></td><td>$code</td><td>$file</td></tr>";
							$codes[$moodleShortName]=$file;
						}
					}else{
						$codeToFind = $code."%";
						$courses = $DB->get_records_sql('SELECT * FROM mdl_course WHERE '.$DB->sql_like('shortname',':sname'), array('sname'=>$codeToFind));
						if(count($courses)>0){
							foreach($courses as $course){
								$moodleShortName =$course->shortname;
								$table.="<tr><td><input type='text' name='$moodleShortName' value='$moodleShortName'/></td><td>$code</td><td>$file</td></tr>";
								$codes[$moodleShortName]=$file;
							}
						}else {						
							$moodleShortName = $code;
							$table.="<tr><td><input type='text' name='$moodleShortName' value='C'/></td><td>$code</td><td>$file</td></tr>";
							$codes[$moodleShortName]=$file;
						}
					}
					
					
						
				}		
						
				
				
			}
		$table.="
			</tbody>
		</table>";

		$mform->addElement('hidden', 'codes', json_encode($codes));
		$mform->setType('codes',PARAM_RAW);
		
		$mform->addElement('html',$table);
				
		$this->add_action_buttons(false, get_string('restore_button', 'tool_webcttomoodle'));
	}
	
	function definition_after_data() {
		$mform = $this->_form;
		
		$mform->addElement('hidden', 'isRestore', true);
	}
	
	function scan_dir($dir) {
		$ignored = array('.', '..', '.svn', '.htaccess');
	
		$files = array();
		foreach (scandir($dir) as $file) {
			if (in_array($file, $ignored)) continue;
			$files[$file] = filesize($dir . '/' . $file);
		}
	
		asort($files);
		$files = array_keys($files);
	
		return ($files) ? $files : false;
	}
}
