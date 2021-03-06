<?php
// 

/**
 * Create Moodle backup files from WebCT
 *
 * @package    toolwebcttomoodle
*/
require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

require_once 'classes/view/FTPConnexionForm.php';
require_once 'classes/view/CourseSelectionForm.php';
require_once 'classes/view/RestoreForm.php';
require_once 'classes/service/WebCTService.php';
require_once 'lib/MigrationConnexion.php';

admin_externalpage_setup('toolwebcttomoodle');



/*class BackupThread extends Thread{
	public function process($pParams=null){
		
		$model = $webCTService->createGlobalModel($lc);
		$webCTService->createBackup($model);
		//return "Fin apr�s".$pParams->time ."secondes";
	}
}*/

// class RestoreNewThread extends Thread {

// 	/**
// 	 * @var WebCTService
// 	 */
// 	public $service;

// 	public $value;
// 	public $file;

// 	public function __construct($service, $value, $file){
// 		$this->service=$service;
// 		$this->value=$value;
// 		$this->file=$file;
// 	}

// 	public function run(){
// 		$this->service->restoreNewCourse($this->value, $this->file);
// 	}
// }

// class RestoreExistingThread extends Thread {

// 	/**
// 	 * @var WebCTService
// 	 */
// 	public $service;

// 	public $value;
// 	public $file;

// 	public function __construct($service, $value, $file){
// 		$this->service=$service;
// 		$this->value=$value;
// 		$this->file=$file;
// 	}

// 	public function run(){
// 		$this->service->restoreExistingCourse($this->value, $this->file);
// 	}
// }

$progressBar = 	'<div id="conteneur" style="display:none; background-color:transparent; width:80%; border:1px solid #000000;">
					<div id="barre" style="display:block; background-color:rgba(132, 232, 104, 0.7); width:0%; height:100%;float:top;clear : top ;clear:both">
						<div id="pourcentage" style="text-align:right; height:100%; font-size:1.8em;">
							&nbsp;
						</div>
					</div>
				</div>
				<label id="progress_bar_description"></label>';

$isBackup = optional_param('isBackup',false,PARAM_BOOL);
$isRestore = optional_param('isRestore',false,PARAM_BOOL);
$isConnexionSave = optional_param('isConnexionSave',false,PARAM_BOOL);

$useThreads = optional_param('userThreads',false,PARAM_BOOL);

//var_dump($useThreads);

$learningContextIds = optional_param('learningContextIds', "", PARAM_TEXT);

$migrationConnexion;
$protocol=NULL;
if(isset($_COOKIE['CONNEXION_FOR_WEBCT'])) {
	$migrationConnexion = json_decode($_COOKIE['CONNEXION_FOR_WEBCT']);
	$protocol = $migrationConnexion->protocol;
}
	
$protocol = optional_param('protocols',$protocol,PARAM_INT);

if($protocol==0 && $isConnexionSave){
		$migrationConnexion=new MigrationConnexion(
				0,
				optional_param('ip','164.15.72.104',PARAM_TEXT),
				optional_param('user','ftpuser',PARAM_TEXT),
				optional_param('password','ftpuser',PARAM_TEXT),
				optional_param('repository','/ingest/',PARAM_TEXT));
		setcookie("CONNEXION_FOR_WEBCT", json_encode($migrationConnexion), time()+60*60*24*30);
		
}elseif($protocol==1 && $isConnexionSave){
		$migrationConnexion=new MigrationConnexion(
				1,
				optional_param('ip','127.0.0.1',PARAM_TEXT),
				optional_param('user','anonymous',PARAM_TEXT),
				optional_param('password','',PARAM_TEXT),
				optional_param('repository','/ingest/',PARAM_TEXT));
		setcookie("CONNEXION_FOR_WEBCT", json_encode($migrationConnexion), time()+60*60*24*30);
}elseif($protocol==2 && $isConnexionSave){
		$migrationConnexion=new MigrationConnexion(
				2,
				optional_param('ip','',PARAM_TEXT),
				optional_param('user','',PARAM_TEXT),
				optional_param('password','',PARAM_TEXT),
				optional_param('repository','/ingest/',PARAM_TEXT));
		
		setcookie("CONNEXION_FOR_WEBCT", json_encode($migrationConnexion), time()+60*60*24*30);
}

if(!isset($migrationConnexion)){
	$migrationConnexion=new MigrationConnexion(
			0,
			optional_param('ip','164.15.72.104',PARAM_TEXT),
			optional_param('user','ftpuser',PARAM_TEXT),
			optional_param('password','ftpuser',PARAM_TEXT),
			optional_param('repository','/ingest/',PARAM_TEXT));
	setcookie("CONNEXION_FOR_WEBCT", json_encode($migrationConnexion), time()+60*60*24*30);
}

$settings = new WebCTServiceSettings();
$settings->migrationConnection = $migrationConnexion;

$webCTService = new WebCTService();
$webCTService->settings = $settings;

if($isBackup || $isRestore){
	@ignore_user_abort(true);	
	@set_time_limit(0);
	raise_memory_limit(MEMORY_HUGE);
}

if($isBackup){

	$learningContextIds = optional_param('learningContextIds', "", PARAM_TEXT);
	
	if(!empty($learningContextIds)){

		echo $OUTPUT->header();
		
		echo $progressBar;
		
		$lcList = preg_split('/[\n]/', $learningContextIds);
		//var_dump($lcList);
		
		activerAffichage();
		$indice = 0;
		$nbElem = count($lcList);
		foreach ($lcList as $lc){
			$lc=trim($lc);

			progression($indice);
			$timestart=microtime(true);
			if(!empty($lc)){
				$model = $webCTService->createGlobalModel($lc, $nbElem,$indice);					

				$webCTService->createBackup($model);
				
				echo $lc.': course backup created <br/>';
				
			}	
			$timeend=microtime(true);
			$time=$timeend-$timestart;
			$page_load_time = number_format($time, 3);
			echo " <b> Debut du script: ".date("H:i:s", $timestart);
			echo "<br>Fin du script: ".date("H:i:s", $timeend);
			echo "<br>Script execute en " . $page_load_time . " sec </b> </br>";
			ob_flush();
			flush();
			$indice += 100/($nbElem*12); 		
		}
		
		
		echo $OUTPUT->footer();
		die();
	}

}elseif($isRestore){
	global $USER;
	
	//ON EFFECTUE LA RESTAURATION DES COURS...
	echo $OUTPUT->header();
	
	echo $progressBar;
	
	$codes =json_decode(optional_param('codes', "", PARAM_TEXT),true);
	activerAffichage();
	$nbElemRestore = count($codes);
	
	$indice =0;
	
	$webCTService->step = 1/$nbElemRestore;
	foreach ($codes as $code=>$file){
		$value = optional_param(str_replace(' ', '', $code), "", PARAM_TEXT);
		progression($indice);
		if(empty($value)){
			$indice += 100 /$nbElemRestore ;
			continue;
		}
		$webCTService->currentProgress=$indice;
		
		if($value == "C"){
			//CREE UN NOUVEAU COURS
			echo utf8_encode('<b>Cr�ation du cours - '.$code.'</b><br/>');
			ob_flush();
			flush();
// 			if($useThreads==1){
// 				$thread = new RestoreNewThread($webCTService, $value, $file);			
// 				var_dump($thread->start());
// 			}else {
				$webCTService->restoreNewCourse($value, $file);
//			}
		}else{
			//ON ECRASE LE COURS EXISTANT
			echo '<b>Restauration du cours - '.$code.'</b><br/>';
			ob_flush();
			flush();
// 			if($useThreads==1){
// 				$thread = new RestoreExistingThread($webCTService, $value, $file);	
// 				var_dump($thread->start());
// 			}else {
				$webCTService->restoreExistingCourse($value, $file);
//			}
		} 
		
		$indice += 100 /$nbElemRestore ;		
	}
	progression($indice);
	echo $OUTPUT->footer();
	die();
}
//FORMULAIRES PAR DEFAUT...
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pageheader', 'tool_webcttomoodle'));

$ftpConnexionForm = new FTPConnexionForm($migrationConnexion);
$ftpConnexionForm->display();

$courseSelectioForm = new CourseSelectionForm($migrationConnexion);
$courseSelectioForm->display();

$restoreForm = new RestoreForm($migrationConnexion);
$restoreForm->display();

echo $OUTPUT->footer();
die();
	
function progression($indice)
{
	echo "<script>";
	echo "document.getElementById('pourcentage').innerHTML='$indice%';";
	echo "document.getElementById('barre').style.width='$indice%';";
	echo "</script>";
	
	ob_flush();
	flush();
}

function activerAffichage(){
	echo "<script>";
	echo "document.getElementById('conteneur').style.display = \"block\";";
	echo "</script>";
	echo "<br/>";
	ob_flush();
	flush();
}



?>