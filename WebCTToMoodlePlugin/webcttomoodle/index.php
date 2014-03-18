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
require_once 'lib/FTPConnexion.php';

admin_externalpage_setup('toolwebcttomoodle');


/*class BackupThread extends Thread{
	public function process($pParams=null){
		
		$model = $webCTService->createGlobalModel($lc);
		$webCTService->createBackup($model);
		//return "Fin apr�s".$pParams->time ."secondes";
	}
}*/
?>
<div id="conteneur" style="display:none; background-color:transparent; position:absolute; top:100px; left:15%; z-index: 10 ;float:top ;clear:top ;clear:both; height:50px; width:70%; border:1px solid #000000;">
	<div id="barre" style="display:block; background-color:#FFD700; width:0%; height:100%;float:top;clear : top ;clear:both">
		<div id="pourcentage" style="text-align:right; height:100%; font-size:1.8em;">
			&nbsp;
		</div>
	</div>
</div>
<?php 
$isBackup = optional_param('isBackup',false,PARAM_BOOL);
$isRestore = optional_param('isRestore',false,PARAM_BOOL);
$isFtpSave = optional_param('isFtpSave',false,PARAM_BOOL);

$learningContextIds = optional_param('learningContextIds', "", PARAM_TEXT);

$ftpConnexion;

if($isFtpSave || !isset($_COOKIE['FTP_CONNEXION_FOR_WEBCT'])){
	$ftpConnexion=new FTPConnexion(
			optional_param('ftpip','127.0.0.1',PARAM_TEXT),
			optional_param('ftpuser','anonymous',PARAM_TEXT),
			optional_param('ftppassword','',PARAM_TEXT),
			optional_param('ftprepository','/backup_1/',PARAM_TEXT));
	
	setcookie("FTP_CONNEXION_FOR_WEBCT", json_encode($ftpConnexion), time()+3600);
}else {
	$ftpConnexion = json_decode($_COOKIE['FTP_CONNEXION_FOR_WEBCT']);
}

$settings = new WebCTServiceSettings();
$settings->ftpConnection = $ftpConnexion;

$webCTService = new WebCTService();
$webCTService->settings = $settings;


if($isBackup){

	$learningContextIds = optional_param('learningContextIds', "", PARAM_TEXT);
	
	if(!empty($learningContextIds)){

		echo $OUTPUT->header();
		
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
				$model = $webCTService->createGlobalModel($lc, $nbElem,&$indice);					
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
	set_time_limit(0);
	global $USER;
	
	//ON EFFECTUE LA RESTAURATION DES COURS...
	echo $OUTPUT->header();
	
	$codes =json_decode(optional_param('codes', "", PARAM_TEXT));
	
	
	
	foreach ($codes as $code=>$file){
		$value = optional_param($code, "", PARAM_TEXT);
				
		if(empty($value)){
			continue;
		}
		
		if($value == "NEW"){
			//CREE UN NOUVEAU COURS
			$webCTService->restoreNewCourse($file);
			echo 'NOUVEAU COURS CREE - '.$code.'<br/>'; 
		}else{
			//ON ECRASE LE COURS EXISTANT
			$webCTService->restoreExistingCourse($value, $file);
			echo 'COURS RESTAURE - '.$code.'<br/>';
		} 
	}
		
	echo $OUTPUT->footer();
	die();
}
//FORMULAIRES PAR DEFAUT...
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pageheader', 'tool_webcttomoodle'));

$ftpConnexionForm = new FTPConnexionForm($ftpConnexion);
$ftpConnexionForm->display();

$courseSelectioForm = new CourseSelectionForm($ftpConnexion);
$courseSelectioForm->display();

$restoreForm = new RestoreForm($ftpConnexion);
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
	ob_flush();
	flush();
}

function activerAffichage(){
echo "<script>";
echo "document.getElementById('conteneur').style.display = \"block\";";
echo "</script>";
echo "</br> </br> </br>";
ob_flush();
flush();
ob_flush();
flush();
}

?>