<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * @author Marc
 *
 */
class RestoreForm extends moodleform {
	
	/**
	 * @var FtpConnexion
	 */
	public $ftpConnexion;
	
	public function __construct($ftpConnexion){
		$this->ftpConnexion = $ftpConnexion;
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
		
		
		$ftp = ftp_connect($this->ftpConnexion->ip, 21);
		
		if(!$ftp){
			$mform->addElement('html', get_string("no_ftp_connexion","tool_webcttomoodle"). '<br/>');
			return;
		}
		
		ftp_login($ftp, $this->ftpConnexion->user, $this->ftpConnexion->password);
		
		
		if(!ftp_chdir($ftp, $this->ftpConnexion->repository)){
			$mform->addElement('html', get_string("no_directory","tool_webcttomoodle"). '<br/>');
			return;
		}
		
		$files = ftp_nlist($ftp, ".");
		
		//récupère la liste des backups disponibles (ftp)
		
		if(empty($files)){
			$mform->addElement('html', get_string("no_files","tool_webcttomoodle") . '<br/>');
			return;
		}
		
		$codes = array();
		
		$table =
		"<table>
			<tbody>";
			
			foreach ($files as $file){
				$result = null;
				preg_match('/\/(.+)#/',$file,$result);
				$code = "";
				$moodleShortName = "";
				
				if(count($result)>=2){
					$code = $result[1];
					$codes[$code]=$file;
					
					$codeToFind = str_ireplace(array('_','-'), '%', $code); 
					 
					$course = $DB->get_record_sql('SELECT * FROM mdl_course WHERE '.$DB->sql_like('shortname',':sname'), array('sname'=>$codeToFind));
					
					if(!empty($course)){
						$moodleShortName =$course->shortname;
					}
				}		
						
				$table.="<tr><td><input type='text' name='$code' value='$moodleShortName'/></td><td>$code</td><td>$file</td></tr>";
				
			}
		$table.="
			</tbody>
		</table>";
				
		ftp_close($ftp);
		
		$mform->addElement('hidden', 'codes', json_encode($codes));
		$mform->setType('codes',PARAM_RAW);
		
		$mform->addElement('html',$table);
				
		$this->add_action_buttons(false, get_string('restore_button', 'tool_webcttomoodle'));
	}
	
	function definition_after_data() {
		$mform = $this->_form;
		
		$mform->addElement('hidden', 'isRestore', true);
	}
}
