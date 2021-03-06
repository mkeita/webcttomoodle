<?php

require_once 'classes/model/GlobalModel.php';

class WebCTModel extends \GlobalModel {
	
	const LEARNING_MODULE_AS_FOLDER = 1;
	const LEARNING_MODULE_AS_BOOK = 2;
		
	private $allLearningModules = array();
		
	/* (non-PHPdoc)
	 * @see GlobalModel::__construct()
	 */
	/**
	 * 
	 * @param string $learningContextId
	 * @param int $nbElemRec Repr�sente le nombre de learning context qui vont �tre r�cup�r�.
	 */
	public function __construct($learningContextId , $nbElemRec , &$indice) {
		$this->learningContextId = $learningContextId;
		parent::__construct();
		
		//TODO TEMPORARY DESACTIVATE DURING DEVELOPPEMENT
		$progression = 100/($nbElemRec*12);
		
   		$this->retrieveGlossaries();
		echo " Glossaires " . utf8_encode("r�cup�r�s")."<br/>";
		$indice += $progression;
		$this->progression($indice);		
   		
  		$this->retrieveQuestions();	
   		echo " Questions " . utf8_encode("r�cup�r�es")."<br/>";
   		$indice += $progression;
   		$this->progression($indice);

// 		foreach($this->questions->allQuestions as $key=>$value){
// 			error_log($key.'-->'.$value->name.'<br/>');
// 		} 		
   		
   		$this->retrieveQuizzes();
   		echo " Evaluations " . utf8_encode("r�cup�r�es")."<br/>";
   		$indice += $progression;
   		$this->progression($indice);
		
     	$this->retrieveAssignments();
     	echo " Taches " . utf8_encode("r�cup�r�es")."<br/>";
     	$indice += $progression;
     	$this->progression($indice);

		$this->retrieveFolders();
     	echo " Folder " . utf8_encode("r�cup�r�")."<br/>";
     	$indice += $progression;
     	$this->progression($indice);

		
    	$this->retrieveWebLinks();
    	echo " WebLinks " . utf8_encode("r�cup�r�s")."<br/>";
    	$indice += $progression;
    	$this->progression($indice);

     	$this->retrieveSyllabus();
     	echo " Syllabus " . utf8_encode("r�cup�r�")."<br/>";
     	$indice += $progression;
     	$this->progression($indice);
	
    	$this->retrieveForum();
    	echo " Forum " . utf8_encode("r�cup�r�")."<br/>";
    	$indice += $progression;
    	$this->progression($indice);
		
    	//$this->retrieveEmail();
    	echo " Email " . utf8_encode("r�cup�r�")."<br/>";
    	$indice += $progression;
    	$this->progression($indice);

		$this->retrieveLearningModules();
		echo "Learning Modules " . utf8_encode("r�cup�r�s")."<br/>";
		$indice += $progression;
		$this->progression($indice);
		
 		$this->retrieveCourseContent();
 		echo " Course Content " . utf8_encode("r�cup�r�")."<br/>";
 		$indice += $progression;
 		$this->progression($indice);
 		
//   		/*******
//   		 * retrieveRapportMigration() doit toujour �tre en derni�re position.
//   		 */
   		$this->retrieveRapportMigration();
   		echo " Rapport Migration " . utf8_encode("r�cup�r�")."<br/>";
   		$indice += $progression;
   		$this->progression($indice);
		
		oci_close($this->connection);
	}

	/* (non-PHPdoc)
	 * @see GlobalModel::preInitialization()
	 */
	public function preInitialization() {
		//Connection to the WebCT DataBase
		$db = '(DESCRIPTION =
		    (ADDRESS = (PROTOCOL = TCP)(HOST = 164.15.59.234)(PORT = 1521))
		    (CONNECT_DATA =
		      (SID = WEBCTORA)
		    )
		  )';
		
		$db_charset = 'UTF8'; //FRANCAIS
		$this->connection = oci_connect('webct', 'ciTy4_',$db, $db_charset);
		//$this->connection = oci_connect('webct', 'ciTy4_',$db);
		
		if (!$this->connection) {
			$e = oci_error();
			trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		}
		
		//Get this section DELEVERY_CONTEXT ID!!
		$requestDeliveryContext = "SELECT TEMPLATE_ID FROM CO_LC_ASSIGNMENT WHERE LEARNING_CONTEXT_ID='".$this->learningContextId. "'";
		$stidDeliveryContext = oci_parse($this->connection,$requestDeliveryContext);
		oci_execute($stidDeliveryContext);
		$deliveryContext = oci_fetch_array($stidDeliveryContext, OCI_ASSOC+OCI_RETURN_NULLS);
		$this->deliveryContextId = $deliveryContext["TEMPLATE_ID"];
		
	}
	
	public function postInitialization(){
		$sectionId = $this->fixedSections[GlobalModel::SECTION_GENERAL];
		
		//Add the news forum
		$this->addActivityForum("Forum d'annonces", "Forum d'annonces",$sectionId);
		//Add the student forum
		$this->addActivityForum(utf8_encode("Forum �tudiants"), utf8_encode("Forum �tudiants"),$sectionId,'general',0);		
	}
	
	
	/**
	 * Add a Folder and all its content
	 */
	public function addActivityForum($name,$description,$sectionId,$type='news',$visible=1){
	
		global $USER;
	
		//Glossary
		$forumModel = new ForumModel();
		
		$forumModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$forumModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$forumModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$forumModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
		$forumModel->grades = new ActivityGradeBook();
		$forumModel->calendar = new Events();
		$forumModel->inforef = new InfoRef();
	
	
		$id = $this->getNextId();
		$forumModel->module = $this->createModule($id,"forum","2013110500",$sectionId,$visible);
	
		$forum = new ActivityForum();
		$forum->id = $id;
		$forum->moduleid =$forumModel->module->id;
		$forum->modulename =$forumModel->module->modulename;
		$forum->contextid=$this->getNextId();
		$forum->forumId = $id;
	
		$forum->type=$type;
		$forum->name =$name;
		//$convertedDescription = $this->convertTextAndCreateAssociedFiles($description,12, $forum);
		$forum->intro=$description;
		$forum->introformat=1;
		
		$forum->assessed=0;
		$forum->assesstimestart=0;
		$forum->assesstimefinish=0;
		$forum->scale=0;
		$forum->maxbytes=0;
		$forum->maxattachments=1;
		$forum->forcesubscribe=1;
		$forum->trackingtype=1;
		$forum->rsstype=0;
		$forum->rssarticles=0;
		$forum->timemodified=time();
		$forum->warnafter=0;
		$forum->blockafter=0;
		$forum->blockperiod=0;
		$forum->completiondiscussions=0;
		$forum->completionreplies=0;
		$forum->completionposts=0;
		$forum->displaywordcount=0;
	
		$forumModel->forum = $forum;
		
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$forumModel->module->id;
		$activity->sectionid=$this->sections[$sectionId]->section->id;
		$activity->modulename=$forumModel->module->modulename;
		$activity->title=$forumModel->forum->name;
		$activity->directory="activities/forum_".$forumModel->forum->forumId;
	
		$this->moodle_backup->contents->activities[] = $activity;
	
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","forum_".$forumModel->forum->forumId,"forum_".$forumModel->forum->forumId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","forum_".$forumModel->forum->forumId,"forum_".$forumModel->forum->forumId."_userinfo",1);
	
		//$inforRef = new InfoRef();
		//$inforRef->userids[]=$USER->id;
		//$inforRef->fileids=$forumModel->forum->filesIds;	
		//$forumModel->inforef = $inforRef;
	
		$this->activities[] = $forumModel;
	
		$this->sections[$sectionId]->section->sequence[]= $forumModel->forum->forumId;
	}
	
	
	public function progression($indice)
	{
		echo "<script>";
		echo "document.getElementById('pourcentage').innerHTML='$indice%';";
		echo "document.getElementById('barre').style.width='$indice%';";
		echo "</script>";
		ob_flush();
		flush();
	}
	
	public function initializeMoodleBackupModel(){
		parent::initializeMoodleBackupModel();
		
		$request = "SELECT * FROM LEARNING_CONTEXT WHERE ID ='".$this->learningContextId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
		
		$shortName = $row['SOURCE_ID'];		
		if(substr($shortName,-8,8)!=".default"){ //On en
			$shortName = $row['ID'];
		}else {
			$shortName =substr($shortName,0,-8);
		} 
				
		$this->moodle_backup->original_course_id = $row['ID'];
		$this->moodle_backup->original_course_fullname = $row['NAME'];//WebCt Course 0";
		$this->moodle_backup->original_course_shortname = $shortName;//WEBCT-0";
		$this->moodle_backup->name = $this->moodle_backup->original_course_shortname."__BACKUP_".time().".mbz"; //test_backup.mbz
		$this->moodle_backup->settings[0] = new MoodleBackupBasicSetting("root","filename",$this->moodle_backup->name);
	}
	
	
	public function initializeCourseModel(){
		parent::initializeCourseModel();

		$this->course->course->fullname = $this->moodle_backup->original_course_fullname;
		$this->course->course->shortname = $this->moodle_backup->original_course_shortname;
		$this->course->course->idnumber = $this->moodle_backup->original_course_id;
				
		$this->moodle_backup->contents->course->title=$this->moodle_backup->original_course_shortname;
		$this->moodle_backup->contents->course->courseid=$this->moodle_backup->original_course_id;
	}
	
	/***************************************************************************************************************
	 * GLOSSARY 
	 */
	
	public function retrieveGlossaries(){
		
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='MEDIA_COLLECTION_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){

			$glossaryId = $row['ORIGINAL_CONTENT_ID'];
			$this->addGlossary($glossaryId);
			
		}
	}
	
	/**
	 * Add a glossary
	 */
	public function addGlossary($glossaryId){
	
		global $USER;
		$sectionId = $this->fixedSections[GlobalModel::SECTION_GENERAL];
	
		//Glossary
		$glossaryModel = new GlossaryModel();
		$glossaryModel->grades = new ActivityGradeBook(); //EMPTY CURRENTLY NOT NEEDED
		$glossaryModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$glossaryModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$glossaryModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$glossaryModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
		$glossaryModel->calendar = new Events(); //EMPTY CURRENTLY NOT NEEDED
	
	
		$glossaryModel->module = $this->createModule($glossaryId,"glossary","2013110500",$sectionId);
	
		$glossaryModel->glossary = $this->createGlossary($glossaryId, $glossaryModel->module);
	
		if($glossaryModel->glossary->name == "mediaLibrary.defaultCollection.name"){
			if(sizeof($glossaryModel->glossary->entries)<=0){
				$rem = "Le glossaire ne contenait aucune entr�e donc il n'a pas �t� r�cup�r�";
				$this->rapportMigration->add(RapportMigration::TYPE_GLOSSARY,RapportMigration::GLOSSARY_EMPTY_NOT_MIGRATED,
						$glossaryModel->glossary->id, $glossaryModel->glossary->name,
						$rem);
				return;
			}else {
				$glossaryModel->glossary->name =utf8_encode("Glossaire par d�faut");
				$glossaryModel->glossary->intro=utf8_encode("Glossaire par d�faut");
			}
	
		}
	
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$glossaryModel->module->id;
		$activity->sectionid=$sectionId;
		$activity->modulename=$glossaryModel->module->modulename;
		$activity->title=$glossaryModel->glossary->name;
		$activity->directory="activities/glossary_".$glossaryModel->glossary->glossaryid;
	
		$this->moodle_backup->contents->activities[] = $activity;
	
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","glossary_".$glossaryModel->glossary->glossaryid,"glossary_".$glossaryModel->glossary->glossaryid."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","glossary_".$glossaryModel->glossary->glossaryid,"glossary_".$glossaryModel->glossary->glossaryid."_userinfo",1);
	
		$inforRef = new InfoRef();
		$inforRef->userids[]=$USER->id;
		$inforRef->fileids=$glossaryModel->glossary->filesIds;
		$glossaryModel->inforef = $inforRef;
	
		$this->activities[] = $glossaryModel;
	
		$this->sections[$sectionId]->section->sequence[]= $glossaryModel->glossary->id;
		
		//rapport
		$this->rapportMigration->add(RapportMigration::TYPE_GLOSSARY,RapportMigration::GLOSSARY_ENTRIES_COUNT,
				$glossaryModel->glossary->id, $glossaryModel->glossary->name,
				count($glossaryModel->glossary->entries));
		
		$this->rapportMigration->glossariesCount++;
	}
	
	/**
	 * @var unknown $glossaryId
	 * @var Module $module
	 * @return Glossary
	 */
	public function createGlossary($glossaryId, $module){
		
		global $USER;
		
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE ORIGINAL_CONTENT_ID='".$glossaryId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
				
		$glossary = new Glossary();
	
		$glossary->id=$glossaryId;//		<activity id="1" moduleid="11" modulename="glossary" contextid="54">
		$glossary->moduleid =$module->id; //ID
		$glossary->modulename =$module->modulename;
		$glossary->contextid=$this->getNextId();
		$glossary->glossaryid=$glossaryId;
		$glossary->name =$row['NAME'];// 		<name>Marc glossary</name>
		
		$description = $row['DESCRIPTION'];
		if(empty($description)){
			$glossary->intro ="";
		}else {
			$glossary->intro =$description->load();// 		<intro>&lt;p&gt;An alphabetical list of terms relating to this course, and their descriptions.&lt;/p&gt;</intro>
		}
		
		$glossary->introformat =1;// 		<introformat>1</introformat>
		$glossary->allowduplicatedentries =0;// 		<allowduplicatedentries>0</allowduplicatedentries>
		$glossary->displayformat ="dictionary";// 		<displayformat>dictionary</displayformat>
		$glossary->mainglossary =0;// 		<mainglossary>0</mainglossary>
		$glossary->showspecial =1;// 		<showspecial>1</showspecial>
		$glossary->showalphabet =1;// 		<showalphabet>1</showalphabet>
		$glossary->showall =1;// 		<showall>1</showall>
		$glossary->allowcomments =0;// 		<allowcomments>0</allowcomments>
		$glossary->allowprintview =1;// 		<allowprintview>1</allowprintview>
		$glossary->usedynalink =1;// 		<usedynalink>1</usedynalink>
		$glossary->defaultapproval =1;// 		<defaultapproval>1</defaultapproval>
		$glossary->globalglossary =0;// 		<globalglossary>0</globalglossary>
		$glossary->entbypage =10;// 		<entbypage>10</entbypage>
		$glossary->editalways =0;// 		<editalways>0</editalways>
		$glossary->rsstype =0;// 		<rsstype>0</rsstype>
		$glossary->rssarticles =0;// 		<rssarticles>0</rssarticles>
		$glossary->assessed =0;// 		<assessed>0</assessed>
		$glossary->assesstimestart =0;// 		<assesstimestart>0</assesstimestart>
		$glossary->assesstimefinish =0;// 		<assesstimefinish>0</assesstimefinish>
		$glossary->scale =0;// 		<scale>0</scale>
		$glossary->timecreated =time();// 		<timecreated>1390818670</timecreated>
		$glossary->timemodified =time();// 		<timemodified>1390818670</timemodified>
		$glossary->completionentries =0;// 		<completionentries>0</completionentries>
	
		
		//FIND ALL ENTRY
		
		$request = "SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID ='".$glossaryId."' AND LINK_TYPE_ID='30014'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
			
			$request1 = "SELECT * FROM CMS_CONTENT_ENTRY WHERE ORIGINAL_CONTENT_ID ='".$row['RIGHTOBJECT_ID']."'";
			$stid1 = oci_parse($this->connection,$request1);
			oci_execute($stid1);
			$row1 = oci_fetch_array($stid1, OCI_ASSOC+OCI_RETURN_NULLS);
					
						
			$entry = new Entry($glossary);
			$entry->id=$row1['ORIGINAL_CONTENT_ID'];// 		id="1">
			$entry->userid=$USER->id;// 		<userid>2</userid>
			$entry->concept=$row1['NAME'];// 		<concept>Entry1</concept>
			
			//Default WebCT Entry, don't recover
			if($entry->concept=='WebCT'){
				continue;
			}
			
			$description = $row1['DESCRIPTION'];
			$completeDescription = $description->load();
			
			$filesName = array();
			$convertedDescription =$this->convertTextAndCreateAssociedFiles($completeDescription,2, $entry); 
						
			$entry->definition =$convertedDescription;// 		<definition>&lt;p&gt;Entry 1 of glossary&lt;/p&gt;</definition>
			
			$entry->sourceglossaryid=0;// 		<sourceglossaryid>0</sourceglossaryid>
					
			$entry->definitionformat=1;// 		<definitionformat>1</definitionformat>
			$entry->definitiontrust=0;// 		<definitiontrust>0</definitiontrust>
			$entry->timecreated=time();// 		<timecreated>1390818856</timecreated>
			$entry->timemodified=time();// 		<timemodified>1390818883</timemodified>
			$entry->teacherentry=1;// 		<teacherentry>1</teacherentry>
			$entry->usedynalink=1;// 		<usedynalink>0</usedynalink>
			$entry->casesensitive=0;// 		<casesensitive>0</casesensitive>
			$entry->fullmatch=1;// 		<fullmatch>0</fullmatch>
			$entry->approved=1;// 		<approved>1</approved>
			
			$request2 = "SELECT * FROM ML_ENTRY WHERE ID ='".$entry->id."'";
			$stid2 = oci_parse($this->connection,$request2);
			oci_execute($stid2);
			$row2 = oci_fetch_array($stid2, OCI_ASSOC+OCI_RETURN_NULLS);
		
			$keywords = explode(',', $row2['KEYWORDS']);
			$count=0;
			foreach ($keywords as $keyword){
				$alias = new Alias($count++,$keyword);
				$entry->aliases[]=$alias;
			}
			
			$attachmentName = $row2["ATTCHMNT_NAME"];
			if(empty($attachmentName)){
				$entry->attachment=0;// 		<attachment>1</attachment>
			}else {
				$entry->attachment=1;// 		<attachment>1</attachment>
				
				
				$request3 = "SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$entry->id."'";
				$stid3 = oci_parse($this->connection,$request3);
				oci_execute($stid3);
				$row3 = oci_fetch_array($stid3, OCI_ASSOC+OCI_RETURN_NULLS);
				
				//ADD THE ATTACHED FILE
				$this->addCMSFile($row3['RIGHTOBJECT_ID'],1,$entry,$glossary);
			}
			
			$glossary->entries[]=$entry;
		
		}	
	
		return $glossary;
	}
	
	
	/**
	 * @param unknown $text
	 * @param unknown $item
	 * @param unknown $parent
	 * @param unknown $mode
	 * 
	 * MODE 1 = Glossary file - attachment
	 * MODE 2 = GLossary file - entry
	 * MODE 3 = Question file - question text
	 * MODE 4 = Question file - General feedback text
	 * MODE 5 = Question file - Answer text
	 * MODE 6 = Question file - Answer feedback text
	 * MODE 7 = Question file - Match subquestion
	 * MODE 8 = Question file - Essay grader info
	 * MODE 9 = Assignment file - Assignment description
	 * MODE 10 = Ressource file
	 * MODE 11 = Book file - Chapter
	 * MODE 12 = Label file - Label intro
	 * 
	 * @return string
	 */
	public function convertTextAndCreateAssociedFiles($text,$mode,$item){
		$htmlContentClass = new HtmlContentClass();
		
		$convertedText = $htmlContentClass->removeGlossaryLinks($text);
		$convertedText = $htmlContentClass->replaceAllLinks($convertedText);
		
		foreach ($htmlContentClass->filesName as $fileName){
			$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE NAME ='".$fileName."' AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
			$stid = oci_parse($this->connection,$request);
			oci_execute($stid);
			$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
			if(!empty($row)){
				$this->addCMSFile($row["ORIGINAL_CONTENT_ID"], $mode, $item);
			}
		}
		
		if($mode == 11){
			//check if there are errors
			if (!empty($htmlContentClass->errors)){
				foreach ($htmlContentClass->errors as $error){
					$rem = "Le lien \"".$error."\" ne peut pas �tre trait� automatiquement dans le chapitre \"".$item->title."\"";
					$this->rapportMigration->add(RapportMigration::TYPE_LEARN_MODULE,RapportMigration::LEARN_MODULE_INTRACTABLE_LINK,
						$item->book->id, $item->book->name,
						$rem);
				}
			}
		}
		
		return $convertedText;
	}
	
	
	/**
	 * @param unknown $contextId	<contextid>54</contextid>
	 * @param unknown $component	<component>mod_glossary</component>
	 * @param unknown $fileArea	<filearea>attachment</filearea>
	 * @param unknown $itemId 	<itemid>1</itemid>
	 * @param string $path 		<filepath>/</filepath>
	 * @return FileBackup
	 */
	public function addCMSRepository($contextId,$component,$fileArea,$itemId,$path){
		
		global $USER;
		
		$repository = new FileBackup();
		$repository->id=$this->getNextId();
		$repository->contenthash="";// 		<contenthash>da39a3ee5e6b4b0d3255bfef95601890afd80709</contenthash>
		$repository->contextid=$contextId;// 		<contextid>54</contextid> // ACTIVITY -- ICI GLOSSARY CONTEXT
		$repository->component=$component;// 		<component>mod_glossary</component>
		$repository->filearea=$fileArea;// 		<filearea>attachment</filearea>
		$repository->itemid=$itemId;// 		<itemid>1</itemid>
		$repository->filepath=$path; // <filepath>/</filepath>
		$repository->filename=".";// 		<filename>.</filename>
		$repository->userid=$USER->id;// 		<userid>2</userid>
		$repository->filesize=0;// 		<filesize>0</filesize>
		$repository->mimetype="$@NULL@$";// 		<mimetype>document/unknown</mimetype>
		$repository->status=0;// 		<status>0</status>
		$repository->timecreated=time();// 		<timecreated>1390818824</timecreated>
		$repository->timemodified=time();// 		<timemodified>1390818869</timemodified>
		$repository->source="$@NULL@$";// 		<source>$@NULL@$</source>
		$repository->author="$@NULL@$";// 		<author>$@NULL@$</author>
		$repository->license="$@NULL@$";// 		<license>$@NULL@$</license>
		$repository->sortorder=0;// 		<sortorder>0</sortorder>
		$repository->repositorytype="$@NULL@$";// 		<repositorytype>$@NULL@$</repositorytype>
		$repository->repositoryid="$@NULL@$";// 		<repositoryid>$@NULL@$</repositoryid>
		$repository->reference="$@NULL@$";// 		<reference>$@NULL@$</reference>
		
		//REFERENCE IN THE COURSE FILES
		$this->files->files[]=$repository;
		
		return $repository;
		
	}
	
	/**
	 * @param unknown $contextId	<contextid>54</contextid>
	 * @param unknown $component	<component>mod_glossary</component>
	 * @param unknown $fileArea	<filearea>attachment</filearea>
	 * @param unknown $itemId 	<itemid>1</itemid>
	 * @param string $path 		<filepath>/</filepath>
	 * 
	 * @return void|FileBackup
	 */
	public function addCMSSimpleFile($fileOriginalContentId, $contextId,$component,$fileArea,$itemId,$path,$name=NULL){
	
		global $USER;
		
		$request = "SELECT CMS_CONTENT_ENTRY.NAME,CMS_CONTENT_ENTRY.FILESIZE,CMS_FILE_CONTENT.CONTENT,CMS_MIMETYPE.MIMETYPE
					FROM CMS_CONTENT_ENTRY
						INNER JOIN CMS_FILE_CONTENT ON CMS_FILE_CONTENT.ID=CMS_CONTENT_ENTRY.FILE_CONTENT_ID
						INNER JOIN CMS_MIMETYPE ON CMS_MIMETYPE.ID=CMS_FILE_CONTENT.MIMETYPE_ID
					WHERE ORIGINAL_CONTENT_ID ='".$fileOriginalContentId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
		
		if(empty($row)){
			$rem = "Fichier non trouv�.";
			$this->rapportMigration->add(RapportMigration::TYPE_FILES,RapportMigration::FILE_NOT_MIGRATED,
					$fileOriginalContentId, $name,
					$rem);
			return null;
		}
		
		$file = new FileBackup();
		$file->id=$this->getNextId();
		$file->contextid=$contextId;// 		<contextid>54</contextid>
		$file->component=$component;// 		<component>mod_glossary</component>
		$file->filearea=$fileArea;// 		<filearea>attachment</filearea>
		$file->itemid=$itemId;// 		<itemid>1</itemid>
		$file->filepath=$path;// 		<filepath>/</filepath>
		
		if(empty($name)){
			$name = $row['NAME'];
			if(!strrpos($name,'.')){
				$name.='.html';
			}
		}else {
			$pos1 = strrpos($row['NAME'],'.');
			$ext1 = substr($row['NAME'], $pos1);
				
			$pos2 = strrpos($name,'.');
			if($pos2){
				$ext2 = substr($name, $pos2);
				if($ext1!=$ext2){
					$name .=$ext1;
				}
			}
		}
		$file->filename= $name;// 		<filename>.</filename>
		$file->userid=$USER->id;// 		<userid>2</userid>
		$file->filesize=$row['FILESIZE'];// 		<filesize>0</filesize>
		$file->author=$USER->firstname." ".$USER->lastname;// 		<author>$@NULL@$</author>
		$file->license="allrightsreserved";// 		<license>$@NULL@$</license>
		$file->sortorder=0;// 		<sortorder>0</sortorder>
		$file->repositorytype="$@NULL@$";// 		<repositorytype>$@NULL@$</repositorytype>
		$file->repositoryid="$@NULL@$";// 		<repositoryid>$@NULL@$</repositoryid>
		$file->reference="$@NULL@$";// 		<reference>$@NULL@$</reference>
		$file->status=0;// 		<status>0</status>
		$file->timecreated=time();// 		<timecreated>1390818824</timecreated>
		$file->timemodified=time();// 		<timemodified>1390818869</timemodified>
		$file->source=$row['NAME'];// 		<source>$@NULL@$</source>
		
		$content = $row["CONTENT"]->load();
		
		$file->contenthash=md5($content);// 		<contenthash>da39a3ee5e6b4b0d3255bfef95601890afd80709</contenthash>
		
		$file->mimetype=$row['MIMETYPE'];// 		<mimetype>document/unknown</mimetype>
				
		//Create the real file		
		$file->createFile($content, $this->repository);
		
		//REFERENCE IN THE COURSE FILES
		$this->files->files[]=$file;		
		
		return $file;		
	}
	
	/**
	 *
	 * @param unknown $fileOriginalContentId
	 * @param unknown $mode
	 * MODE 1 = Glossary file - attachment
	 * MODE 2 = GLossary file - entry
	 * MODE 3 = Question file - question text
	 * MODE 4 = Question file - General feedback text
	 * MODE 5 = Question file - Answer text
	 * MODE 6 = Question file - Answer feedback text
	 * MODE 7 = Question file - Match subquestion
	 * MODE 8 = Question file - Essay grader info
	 * MODE 9 = Assignment file - Assignment description
	 * MODE 10 = Ressource file
	 * MODE 11 = Book file - Chapter
	 * MODE 12 = Label file - Label intro
	 * @param unknown $item
	 * @param unknown $parent
	 * 
	 * @return FileBackup
	 */
	public function addCMSFile($fileOriginalContentId, $mode, &$item){
		
		$itemId = 0;
		$fileArea = "";
		$component ="";
		$contextId=0;
		$typeRapport = "";
		
		switch ($mode){
			case 1 : 
				$component = "mod_glossary";
				$typeRapport = "glossaire";
				$fileArea = "attachment";
				$itemId=$item->id;
				$contextId=$item->glossary->contextid;
				break;
			case 2:
				$component = "mod_glossary";
				$fileArea = "entry";
				$typeRapport = "glossaire";
				$itemId=$item->id;
				$contextId=$item->glossary->contextid;
				break;
				
			case 3:
				$component = "question";
				$fileArea = "questiontext";
				$typeRapport = "question";
				$itemId=$item->id;
				$contextId=$item->category->contextid;
				break;
			case 4:
				$component = "question";
				$fileArea = "generalfeedback";
				$itemId=$item->id;
				$typeRapport = "question";
				$contextId=$item->category->contextid;
				break;
			case 5:
				$component = "question";
				$fileArea = "answer";
				$itemId=$item->id;
				$typeRapport = "question";
				$contextId=$item->contextid;
				break;
				
			case 6:
				$component = "question";
				$fileArea = "answerfeedback";
				$itemId=$item->id;
				$typeRapport = "question";
				$contextId=$item->contextid;
				break;
			case 7:
				$component = "qtype_match";
				$fileArea = "subquestion";
				$itemId=$item->id;
				$contextId=$item->contextid;
				break;				
			case 8:
				$component = "qtype_essay";
				$fileArea = "graderinfo";
				$itemId=$item->id;
				$contextId=$item->category->contextid;
				break;	
			case 9:
				$component = "mod_assign";
				$fileArea = "intro";
				$itemId=0;
				$typeRapport = "tache";
				$contextId=$item->contextid;
				break;
			case 10:
				$component = "mod_resource";
				$fileArea = "content";
				$itemId = 0;
				$typeRapport = "resource";
				$contextId = $item->contextid;
				break;
			case 11:
				$component = "mod_book";
				$fileArea = "chapter";
				$itemId = $item->id;
				$typeRapport = "resource";
				$contextId = $item->book->contextid;
				break;
			case 12:
				$component = "mod_label";
				$fileArea = "intro";
				$itemId = 0;				
				$contextId = $item->contextid;
				break;
								
		}
				
		$repository = $this->addCMSRepository($contextId, $component, $fileArea, $itemId, "/");
			
		$file = $this->addCMSSimpleFile($fileOriginalContentId, $contextId, $component, $fileArea, $itemId, "/");

//		echo $file->filename.'<br/>';
		
		if($file==null){
			$rem = "Fichier non migr�.";
			$this->rapportMigration->add(RapportMigration::TYPE_FILES,RapportMigration::FILE_NOT_MIGRATED,
					$fileOriginalContentId, $item->name,
					$rem);
			return;			
		}
		
		//REFERENCE IN THE GLOSSARY
		switch ($mode){
			case 1:
			case 2:
				$item->glossary->filesIds[] = $repository->id;		
				$item->glossary->filesIds[] = $file->id;
			case 9:
				$item->filesIds[] = $repository->id;
				$item->filesIds[] = $file->id;
			break;
			case 10:
				$item->filesIds[] = $repository->id;
				$item->filesIds[] = $file->id;
				break;
			case 11:
				$item->book->filesIds[] = $repository->id;
				$item->book->filesIds[] = $file->id;
				break;
				
			case 12:
				$item->filesIds[] = $repository->id;
				$item->filesIds[] = $file->id;
				break;
		}

		return $file;
				
	}
	
	
	/*******************************************************************************************************************
	 * QUESTIONS BANK
	 */
	
	
	public function retrieveQuestions(){
	
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='QuestionDatabaseCategory' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
			
			$questionCategory = new QuestionCategory();
			
			$questionCategory->id = $row['ORIGINAL_CONTENT_ID'];
			$questionCategory->name = $row['NAME'];
			$questionCategory->contextid = 0;
			$questionCategory->contextlevel = 50; //COURSE LEVEL
			$questionCategory->contextinstanceid = 0;
			$questionCategory->info = ""; //NO DESCRIPTION IN WEBCT
			$questionCategory->infoformat = 1;
			$questionCategory->stamp = time(); // localhost+140131155733+469Glc
			$questionCategory->parent = 0;
			$questionCategory->sortorder = 999;
			
			//ADD ALL QUESTIONS IN THIS CATEGORY
			$request1 = "SELECT * FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='Question' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."' AND PARENT_ID='".$questionCategory->id."'";
			$stid1 = oci_parse($this->connection,$request1);
			oci_execute($stid1);
			while ($row1 = oci_fetch_array($stid1, OCI_ASSOC+OCI_RETURN_NULLS)){
				
//  				if($row1['ID']!='174815484001' && $row1['ID']!='3548815001'){
//  					continue;
//  				}
// 				if($row1['ID']!='243791592001' && $row1['ID']!='243791596001'
// 					&& $row1['ID']!='243791604001' && $row1['ID']!='243791609001'
// 					&& $row1['ID']!='243791613001' && $row1['ID']!='243791617001'
// 					&& $row1['ID']!='243791621001' && $row1['ID']!='243791625001'
// 					&& $row1['ID']!='243791629001' && $row1['ID']!='243791633001'
// 					&& $row1['ID']!='243791637001' && $row1['ID']!='243791641001'
// 					&& $row1['ID']!='243791645001' && $row1['ID']!='243791649001'
// 					&& $row1['ID']!='243791657001' && $row1['ID']!='243791661001'
// 					&& $row1['ID']!='243791751001' && $row1['ID']!='243791678001'
// 					&& $row1['ID']!='243791682001' && $row1['ID']!='243791686001'
// 					&& $row1['ID']!='243791747001' && $row1['ID']!='243791694001'
// 					&& $row1['ID']!='243791699001' && $row1['ID']!='243791703001'
// 					&& $row1['ID']!='243791707001' && $row1['ID']!='243791711001'
// 					&& $row1['ID']!='243791715001' && $row1['ID']!='243791719001'			
// 					&& $row1['ID']!='243791727001' && $row1['ID']!='243791731001'
// 					&& $row1['ID']!='243791735001' && $row1['ID']!='243791739001'
// 					&& $row1['ID']!='243791743001'){
// 					continue;
// 				}
				
				$question=null;
	
				if($row1['CE_SUBTYPE_NAME']=='MultipleChoice'){ //MULTICHOICE
					$question = new MultiChoiceQuestion();
					
				}else if($row1['CE_SUBTYPE_NAME']=='ShortAnswer'){ //
					$question = new ShortAnswerQuestion();
					
				} else if($row1['CE_SUBTYPE_NAME']=='FillInTheBlank'){ //
					$question = new FillInBlankQuestion();
					
				}else if($row1['CE_SUBTYPE_NAME']=='Matching'){ //
					$question = new MatchingQuestion();
					
				}else if($row1['CE_SUBTYPE_NAME']=='Paragraph'){ //
					$question = new ParagraphQuestion();
					
				}else if($row1['CE_SUBTYPE_NAME']=='TrueFalse'){ //
					$question = new TrueFalseQuestion();
					
				}else if($row1['CE_SUBTYPE_NAME']=='Calculated'){ //
					$question = new CalculatedQuestion();
					
				}else if($row1['CE_SUBTYPE_NAME']=='CombinationMultipleChoice'){ //
					$question = new CombinaisonMultiChoiceQuestion();
					
				}else if($row1['CE_SUBTYPE_NAME']=='JumbledSentence'){ //
					$question = new JumbledSentenceQuestion();
					
				}
				if(empty($question)){
					continue;					
				}
				$question->id = $row1['ORIGINAL_CONTENT_ID'];
				$question->parent= 0;//$questionCategory->id;
				$question->name=$row1['NAME'];
				
				$question->category = $questionCategory;
				
				$this->fillQuestion($question, $row1['FILE_CONTENT_ID']);
				
				if(empty($question)){
					$rem = "Question non migr�e.";
					$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_NOT_MIGRATED,
							$row1['ORIGINAL_CONTENT_ID'], $row1['NAME'],
							$rem);
					
					continue;
				}

				$questionCategory->addQuestion($question);
				
				$this->allQuestions[(string)$question->id] = $question;

				$this->rapportMigration->questionsCount++;
			}
			
			$this->questions->question_categories[] = $questionCategory;
			$this->course->inforef->questioncategoryids[]=$questionCategory->id;
				
		}
			
	}
	
	
	/**
	 * @param Question $question
	 * @param string $fileContentId
	 */
	public function fillQuestion(&$question, $fileContentId){
	
		global $USER;
	
		//GET THE QUESTION FILE
		//GET THE CONTENT
		$request = "SELECT * FROM CMS_FILE_CONTENT WHERE ID='".$fileContentId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	
		if(isset($row["CONTENT"])){
			$content = $row["CONTENT"]->load();
		}else {
			$rem = "Question sans contenu.";
			$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_WITHOUT_CONTENT,
					$question->id, $question->name . ' ('. $question->category->name . ')',
					$rem);
			$question = null;
			return;
		}
	
		//PARSE THE XML FILE AND RETREIVE THE NEEDED INFORMATION
		$xmlContent = new SimpleXMLElement($content);
		$xmlContent->registerXPathNamespace("ims", "http://www.imsglobal.org/xsd/ims_qtiasiv1p2");
	
		if($question instanceof MultiChoiceQuestion || $question instanceof ShortAnswerQuestion
			|| $question instanceof MatchingQuestion || $question instanceof ParagraphQuestion
			|| $question instanceof TrueFalseQuestion || $question instanceof CombinaisonMultiChoiceQuestion){
			//QUESTION TEXT
			$questionText ="";
			
			if(strlen($question->name)>255){

				$rem = "La taille de la question a �t� limit� � 255 caract�res et son nom complet a �t� mis dans la description de cette question.";
				$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_NAME_TO_LONG,
						$question->id, $question->name . ' ('. $question->category->name . ')',
						$rem);
				
				$questionText .= $question->name."<br/>";

				$question->name = substr($question->name, 252)."...";
			}			
			
			$questionText .= $xmlContent->presentation->flow->material->mattext;
			$convertedDescription = $this->convertTextAndCreateAssociedFiles($questionText,3, $question);
					
			//Get the file attached if any and past it to the description
			$imageName = (string)$xmlContent->presentation->flow->material->matimage;
			$imageURI = $xmlContent->presentation->flow->material->matimage['uri'];
			$findContentId   = '?contentID=';
			$pos = strpos($imageURI, $findContentId);
			if($pos>0 && !empty($imageName)){
				$fileContentId = substr($imageURI, $pos+11);
				$this->addCMSFile($fileContentId, 3, $question);
		
				$convertedDescription .= "<br/><img src=\"@@PLUGINFILE@@/".$imageName."\"/>";
			}
			$question->questiontext=$convertedDescription;
		}
			
		$question->questiontextformat="1";// 		<questiontextformat>1</questiontextformat>
	
		//GENERAL FEEDBACK TEXT
		if(!empty($xmlContent->itemfeedback->flow_mat)){
			$generalFeedbackText = $xmlContent->itemfeedback->flow_mat->material->mattext;
			$convertedDescription = $this->convertTextAndCreateAssociedFiles($generalFeedbackText,4, $question);
			$question->generalfeedback=$convertedDescription;// 		<generalfeedback>&lt;p&gt;&amp;lt;P&amp;gt;&amp;lt;FONT COLOR="#000000"&amp;gt;1 mole de HNO&amp;lt;sub&amp;gt;3&amp;lt;/sub&amp;gt;�&lt;/p&gt;</generalfeedback>
		}else {
			$question->generalfeedback="";
		}
		$question->generalfeedbackformat="1";// 		<generalfeedbackformat>1</generalfeedbackformat>
		$question->defaultmark="1.0000000";// 		<defaultmark>1.0000000</defaultmark>
		$question->penalty="0.3333333";// 		<penalty>0.3333333</penalty>
		$question->length="1";// 		<length>1</length>
		$question->stamp=time();// 		<stamp>localhost+140131160414+7AXnY7</stamp>
		$question->version=time();// 		<version>localhost+140131160414+HfBHPl</version>
		$question->hidden="0";// 		<hidden>0</hidden>
		$question->timecreated=time();// 		<timecreated>1391184254</timecreated>
		$question->timemodified=time();// 		<timemodified>1391184254</timemodified>
		$question->createdby=$USER->id;// 		<createdby>2</createdby>
		$question->modifiedby=$USER->id;// 		<modifiedby>2</modifiedby>
	
		//attention aux classes qui h�ritent !!
		if($question instanceof CombinaisonMultiChoiceQuestion){
			$this->fillCombinaisonMutipleChoiceQuestion($question, $xmlContent);
		}else if($question instanceof MultiChoiceQuestion) {
			$this->fillMutipleChoiceQuestion($question, $xmlContent);
		}else if($question instanceof ShortAnswerQuestion){
			$this->fillShortAnswerQuestion($question, $xmlContent);
		}else if($question instanceof FillInBlankQuestion){
			$this->fillFillInBlankQuestion($question, $xmlContent);
		}else if($question instanceof MatchingQuestion){
			$this->fillMatchingQuestion($question, $xmlContent);
		}else if($question instanceof ParagraphQuestion){
			$this->fillParagraphQuestion($question, $xmlContent);
		}else if($question instanceof TrueFalseQuestion){
			$this->fillTrueFalseQuestion($question, $xmlContent);
		}else if($question instanceof CalculatedQuestion){
			$this->fillCalculatedQuestion($question, $xmlContent);
		}else if($question instanceof JumbledSentenceQuestion){
			$this->fillJumbledSentenceQuestion($question, $xmlContent);
		}
		
		
	
	}
	
	/**
	 * @param MultiChoiceQuestion $question
	 * @param SimpleXMLElement $xmlContent
	 */
	public function fillMutipleChoiceQuestion(&$question, $xmlContent){		
		
		$multichoice = new MultiChoice();
		$multichoice->id=$question->id;

		$multichoice->layout=0;// 			<layout>0</layout>
		if((string)$xmlContent->presentation->flow->response_lid['rcardinality']=="Single"){
			$multichoice->single=1;//             <single>1</single>					
		}else {
			$multichoice->single=0;
		}
		
		if((string)$xmlContent->presentation->flow->response_lid->render_choice['shuffle']=="Yes"){
			$multichoice->shuffleanswers=1;//             <single>1</single>
		}else {
			$multichoice->shuffleanswers=0;
		}

		
		foreach ($xmlContent->itemmetadata->qtimetadata as $qtimetadata){
			$break = false;
			foreach ($qtimetadata as $qtimetadatafield){
				if((string)$qtimetadatafield->fieldlabel=="wct_question_labelledletter"){
					if((string)$qtimetadatafield->fieldentry=="Yes"){
						$multichoice->answernumbering="abc";//             <answernumbering>abc</answernumbering>								
					}else {
						$multichoice->answernumbering="123";//             <answernumbering>abc</answernumbering>
					}
					$break = true;
					break;
				}
			}
			if($break){
				break;
			}
		}
		
		//COMBINED FEEDBACK
		$multichoice->correctfeedback=utf8_encode('Votre r�ponse est correcte.');//             <correctfeedback>&lt;p&gt;Your answer is correct.&lt;/p&gt;</correctfeedback>
		$multichoice->correctfeedbackformat="1";//             <correctfeedbackformat>1</correctfeedbackformat>
		$multichoice->partiallycorrectfeedback=utf8_encode('Votre r�ponse est partiellement correcte.');//             <partiallycorrectfeedback>&lt;p&gt;Your answer is partially correct.&lt;/p&gt;</partiallycorrectfeedback>
		$multichoice->partiallycorrectfeedbackformat="1";//             <partiallycorrectfeedbackformat>1</partiallycorrectfeedbackformat>
		$multichoice->incorrectfeedback=utf8_encode('Votre r�ponse est incorrecte.');//             <incorrectfeedback>&lt;p&gt;Your answer is incorrect.&lt;/p&gt;</incorrectfeedback>
		$multichoice->incorrectfeedbackformat="1";//             <incorrectfeedbackformat>1</incorrectfeedbackformat>
		$multichoice->shownumcorrect="1";//             <shownumcorrect>1</shownumcorrect>

		$question->multiChoice = $multichoice;
		
		foreach ($xmlContent->presentation->flow->response_lid->render_choice->flow_label->response_label as $response_label){

			$webctAnswerId = $response_label['ident'];
			
			$answer = new Answer();
			$answer->contextid = $question->category->contextid;
			$answer->id=$this->getNextId();// 		id="4">
			$answerText = $response_label->material->mattext;
			$convertedDescription =  $this->convertTextAndCreateAssociedFiles($answerText,5, $answer);
			$answer->answertext=$convertedDescription;// 		<answertext>&lt;p&gt;1,05 10&amp;lt;SUP&amp;gt;-22&amp;lt;/SUP&amp;gt; g&lt;/p&gt;</answertext>
			$answer->answerformat="1";// 		<answerformat>1</answerformat>
						
			$webctFeedbackId="";
			foreach ($xmlContent->resprocessing->respcondition as $respcondition){
				if((string)$respcondition->conditionvar->varequal==$webctAnswerId){
					if((string)$respcondition->setvar['varname']== "SCORE"){
						$score = $respcondition->setvar/100;
						if((string)$respcondition->setvar['action']=="Subtract"){
							$score=-$score;
						}
						$answer->fraction=$score;// 		<fraction>1.0000000</fraction>
					}
					
					$webctFeedbackId = $respcondition->displayfeedback['linkrefid'];
					break;
				}
			}
			
			foreach ($xmlContent->itemfeedback as $itemfeedback){
				if($itemfeedback['ident']==$webctFeedbackId){
					foreach ($itemfeedback->material->mattext as $mattext){
						if(!empty($mattext[label])){
							$answerFeedbackText = $mattext;
							$convertedDescription =  $this->convertTextAndCreateAssociedFiles($answerFeedbackText,6, $answer);
							$answer->feedback=$convertedDescription;// 		<feedback>&lt;p&gt;C'est exact.&lt;/p&gt;</feedback>
							$answer->feedbackformat="1";// 		<feedbackformat>1</feedbackformat>	
							break;										
						}
					}
					break;			
				}				
			}
				
	
			$question->answers[] = $answer;			
		}		
		
	}
	
	
	
	/**
	 * @param ShortAnswerQuestion $question
	 * @param SimpleXMLElement $xmlContent
	 */
	public function fillShortAnswerQuestion(&$question, $xmlContent){	
	
		$shortanswer = new ShortAnswer();
		$shortanswer->id=$question->id;
	
		$isShortAnswer = true;
		
		foreach ($xmlContent->itemmetadata->qtimetadata as $qtimetadata){
			$break = false;
			foreach ($qtimetadata as $qtimetadatafield){
				//echo 'TEST '.$qtimetadatafield->fieldlabel;
				if((string)$qtimetadatafield->fieldlabel=="wct_sa_caseSensitive"){
					if((string)$qtimetadatafield->fieldentry=="Yes"){
						$shortanswer->usecase=1;
					}else {
						$shortanswer->usecase=0;
					}
				}else if((string)$qtimetadatafield->fieldlabel=="wct_sa_answerBoxNumber"){
					//echo 'ANSWER BOX NUMBER = '.$qtimetadatafield->fieldentry ."<br/>";
					if((int)$qtimetadatafield->fieldentry>1){
						$isShortAnswer = false;
						$rem = "La question courte a �t� transform�e en question CLOZE. Attention du code javascript a �t� rajout� � la fin de la description de la question afin que les champs de textes ont la m�me taille.";
						$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_TRANSFORM_TO_CLOZE,
								$question->id, $question->name . ' ('. $question->category->name . ')',
								$rem);
						
					}
				}
			}
		}		
		$question->shortAnswer = $shortanswer;
		
		
		if($isShortAnswer){ //On cr�e vraiment une short Answer
			//UNIQUEMENT AVEC REPONSE UNIQUE...
		
			
			//$xmlContent->registerXPathNamespace("n", "http://www.imsglobal.org/xsd/ims_qtiasiv1p2");
			//$xmlContent->registerXPathNamespace("webct", "http://www.webct.com/vista/assessment");
			//var_dump($xmlContent->xpath('/n:item'));

			foreach ($xmlContent->xpath('//ims:respcondition') as $respcondition){
					
				$varEqual = $respcondition->conditionvar->varequal;
				$varExt = $respcondition->conditionvar->var_extension;
				$varSubset = $respcondition->conditionvar->varsubset;
				$answerText = "";
				if(!empty($varEqual)){
					$answerText = $varEqual;
				}else if(!empty($varExt)){
					$answerText = $varExt->children('http://www.webct.com/vista/assessment');
					
					$rem = "Une r�ponse est �valu�e � l'aide d'une expression r�guli�re suivante :".$answerText;
					$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_ANSWER_EVALUATE_BY_REGEX,
							$question->id, $question->name . ' ('. $question->category->name . ')',
							$rem);
						
				}else if(!empty($varSubset)){
					$answerText = "*".$varSubset."*";
					
					$rem = "Une r�ponse est valide si elle contient l'expression suivante :".$varSubset;
					$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_ANSWER_VALIDITY,
							$question->id, $question->name . ' ('. $question->category->name . ')',
							$rem);
				}
				
				if(empty($answerText)){
					$rem = "Une r�ponse n'a pas �t� prise en compte parce qu'elle �tait vide.";
					$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_EMPTY_ANSWER,
							$question->id, $question->name . ' ('. $question->category->name . ')',
							$rem);
					continue;
				}
				
				$answer = new Answer();
				$answer->contextid = $question->category->contextid;
				$answer->id=$this->getNextId();// 		id="4"				
				$answer->answertext=$answerText;// 		<answertext>&lt;p&gt;1,05 10&amp;lt;SUP&amp;gt;-22&amp;lt;/SUP&amp;gt; g&lt;/p&gt;</answertext>
				$answer->answerformat="1";// 		<answerformat>1</answerformat>
			
				if((string)$respcondition->setvar['varname']== "SCORE"){
					$score = $respcondition->setvar/100;
					if((string)$respcondition->setvar['action']=="Subtract"){
						$score=-$score;
					}
					$answer->fraction=$score;// 		<fraction>1.0000000</fraction>
				}
								
				$answer->feedback="";		
				$answer->feedbackformat="1";

				$question->answers[] = $answer;
			}
			
		}else {
			$newQuestion = new MultiAnswerQuestion();
			$newQuestion->fillWith($question);
			
			$question = $newQuestion;
			
			$multiAnswer = new MultiAnswer();
			$multiAnswer->question = $question->id;
			
			
			$finalText = $question->questiontext."<br/><ol>";
			
			$count = 0;
			//We're going to find all the questions part
			$responseStrList =$xmlContent->xpath('//ims:response_str');
			$responseCount = count($responseStrList);
			
			$responseMaxSize = 0;
			foreach ($responseStrList as $response_str){
				$responseId = $response_str['ident'];				
				$count++;
				
				$finalText = $finalText."<li>{#".$count."}</li>";

				
				//Add a short answer question..
				$shortAnswerQuestion = new ShortAnswerQuestion();
				$shortAnswerQuestion->id = $this->getNextId();
				$shortAnswerQuestion->parent = $question->id;
				$shortAnswerQuestion->name = $question->name;				
				$shortAnswerQuestion->questiontextformat=1;
				$shortAnswerQuestion->generalfeedback="";
				$shortAnswerQuestion->generalfeedbackformat=1;
				$shortAnswerQuestion->defaultmark="1.0000000";
				$shortAnswerQuestion->penalty="0.0000000";
				$shortAnswerQuestion->length="1";
				$shortAnswerQuestion->stamp=time();
				$shortAnswerQuestion->version=time();
				$shortAnswerQuestion->hidden=0;
				$shortAnswerQuestion->timecreated=time();
				$shortAnswerQuestion->timemodified=time();
				$shortAnswerQuestion->createdby=$question->createdby;
				$shortAnswerQuestion->modifiedby=$question->modifiedby;
				
				
				$shortAnswer = new ShortAnswer();
				$shortAnswer->id=$shortAnswerQuestion->id;
				$shortAnswer->usecase = $shortanswer->usecase;
				
				$shortAnswerQuestion->shortAnswer = $shortAnswer;
				
				//Answers
					
				//$xmlContent->registerXPathNamespace("n", "http://www.imsglobal.org/xsd/ims_qtiasiv1p2");
				//$xmlContent->registerXPathNamespace("webct", "http://www.webct.com/vista/assessment");
				//var_dump($xmlContent->xpath('/n:item'));
				$shortAnswerQuestionText ="";
				
				$maxScore = 0;
				$vide = false;
				foreach ($xmlContent->xpath('//ims:respcondition') as $respcondition){
						
					$varEqual = $respcondition->conditionvar->varequal;
					$varExt = $respcondition->conditionvar->var_extension;
					$varSubset = $respcondition->conditionvar->varsubset;
					$answerText = "";
					if(!empty($varEqual)){
						if((string)$varEqual['respident']==(string)$responseId){							
							$answerText = $varEqual;
						}
					}else if(!empty($varExt)){
						$varExtChild = $varExt->children('http://www.webct.com/vista/assessment');
						if((string)$varExtChild['respident']==(string)$responseId){
							$answerText = $varExtChild;
							
							$rem = "Une r�ponse est �valu�e � l'aide d'une expression r�guli�re suivante :".$answerText;
							$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_ANSWER_EVALUATE_BY_REGEX,
							$question->id, $question->name . ' ('. $question->category->name . ')',
							$rem);
							
						}						
						
					}else if(!empty($varSubset)){
						if((string)$varSubset['respident']==(string)$responseId){
							$answerText = "*".$varSubset."*";
							
							$rem = "Une r�ponse est valide si elle contient l'expression suivante :".$varSubset;
							$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_ANSWER_VALIDITY,
							$question->id, $question->name . ' ('. $question->category->name . ')',
							$rem);
							
						}						

					}
				
					if(empty($answerText)){
						$rem = "Une r�ponse n'a pas �t� prise en compte parce qu'elle �tait vide.";
						$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_EMPTY_ANSWER,
								$question->id, $question->name . ' ('. $question->category->name . ')',
								$rem);
		
						continue;
					}
				
					$answer = new Answer();
					$answer->contextid = $question->category->contextid;
					$answer->id=$this->getNextId();// 		id="4"
					$answer->answertext=$answerText;// 		<answertext>&lt;p&gt;1,05 10&amp;lt;SUP&amp;gt;-22&amp;lt;/SUP&amp;gt; g&lt;/p&gt;</answertext>
					$answer->answerformat="0";// 		<answerformat>1</answerformat>
						
					if((string)$respcondition->setvar['varname']== "SCORE"){
						$score = $respcondition->setvar/100;
						if((string)$respcondition->setvar['action']=="Subtract"){
							$score=-$score;
						}
						$answer->fraction=$score;// 		<fraction>1.0000000</fraction>
					}
				
					if($maxScore < $answer->fraction){
						$maxScore = $answer->fraction;
					} 
					
					$answer->feedback="";
					$answer->feedbackformat="1";
				
					$shortAnswerQuestion->answers[] = $answer;
					
				}
				
				//We have to adapt all the scores to match
				$ponderation = $maxScore *100;
				if($ponderation<1){
					$ponderation = 1;
					
					$rem = "Probl�me de pond�ration rencontr� (<1).";
					$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_GRADE_ERROR,
							$question->id, $question->name . ' ('. $question->category->name . ')',
							$rem);
				}

				$shortAnswerQuestionText="{".$ponderation.":SHORTANSWER";
				if($shortAnswer->usecase==1){
					$shortAnswerQuestionText =$shortAnswerQuestionText."_C";
				}
				$shortAnswerQuestionText =$shortAnswerQuestionText.":";
				
				foreach ($shortAnswerQuestion->answers as $answer){
					if($maxScore!=0){
						$answer->fraction = $answer->fraction/$maxScore;
					}

					//get the max size
					$answerlength = strlen($answer->answertext);
					if($responseMaxSize<$answerlength){
						$responseMaxSize=$answerlength;
					}
					
					$shortAnswerQuestionText=$shortAnswerQuestionText."%".round($answer->fraction*100,0)."%".$answer->answertext."#~";						
				}
								
				$shortAnswerQuestionText = substr($shortAnswerQuestionText,0,-1)."}";
				$shortAnswerQuestion->questiontext =$shortAnswerQuestionText;
				
				//Add the short question to the current category..
				$question->category->addQuestion($shortAnswerQuestion);
				$multiAnswer->sequence[]=$shortAnswerQuestion->id;
					
				$this->allQuestions[(string)$shortAnswerQuestion->id]=$shortAnswerQuestion;
				
			}
			$finalText = $finalText."</ol>";

			
			//Add some JAVASCRIPT after the question to force the input
			if($responseMaxSize>500){
				$rem = "La taille maximale des champs de texte a �t� limit� � 500 malgr� le fait
						que certaines r�ponses prennent plus de place(".$responseMaxSize.").";
				$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_LIMIT_SIZE,
						$question->id, $question->name . ' ('. $question->category->name . ')',
						$rem);
				
				$responseMaxSize = 500;
			}
			$javascript = "<!---DON'T TOUCH THIS CODE, IT ALLOW THE INPUT FIELD TO HAVE THE SAVE SIZE --->
			<div id='inputResizingDiv".$question->id."'> </div>
			<script type='text/javascript'>
			// <![CDATA[
				var parent = document.getElementById('inputResizingDiv".$question->id."').parentNode;
				var inputs = parent.querySelectorAll('input');
				for (var i=0, max=inputs.length; i < max; i++) {
					inputs[i].setAttribute('size',".$responseMaxSize.");
				}
			// ]]>
			</script>";
			
			$finalText .= $javascript;
			
			$question->questiontext =$finalText;
			
			$question->multiAnswer = $multiAnswer;
		}
		
	
	}
	
	
	/**
	 * @param FillInBlankQuestion $question
	 * @param SimpleXMLElement $xmlContent
	 */
	public function fillFillInBlankQuestion(&$question, $xmlContent){
			
		$usercase = 0;
		foreach ($xmlContent->xpath('//ims:qtimetadatafield') as $qtimetadatafield){
			if((string)$qtimetadatafield->fieldlabel=="wct_sa_caseSensitive"){
				if((string)$qtimetadatafield->fieldentry=="Yes"){
					$usercase=1;
				}else {
					$usercase=0;
				}
				break;
			}
		}
		
		$multiAnswer = new MultiAnswer();
		$multiAnswer->question = $question->id;
			

		//On boucle sur le flow
		$xmlFlow = $xmlContent->presentation->flow;

		$questionFinalText="";		
			
		if(strlen($question->name)>255){
			$rem = "La taille de la question a �t� limit� � 255 caract�res et son nom complet a �t� mis dans la description de cette question.";
			$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_NAME_TO_LONG,
					$question->id, $question->name . ' ('. $question->category->name . ')',
					$rem);
			
			$questionFinalText .= $question->name."<br/>";
			
			$question->name = substr($question->name, 252)."...";
		}
		
		
		$count = 0;
		foreach ($xmlFlow->children() as $child){
			
			if($child->getName()=="material"){
				
				if(!empty($child->mattext)){
					$filesName = array();
					$convertedDescription = $this->convertTextAndCreateAssociedFiles((string)$child->mattext,3, $question);
					$questionFinalText.=$convertedDescription;
				}else if(!empty($child->matimage)){
					$imageName = (string)$child->matimage;
					$imageURI = $child->matimage['uri'];
					$findContentId   = '?contentID=';
					$pos = strpos($imageURI, $findContentId);
					if($pos>0 && !empty($imageName)){
						$fileContentId = substr($imageURI, $pos+11);
						$this->addCMSFile($fileContentId, 3, $question);
					
						$questionFinalText .="<br/><img src=\"@@PLUGINFILE@@/".$imageName."\"/>";
					}
				}
				
			}else if($child->getName()=="response_str"){
				
				$response_str = $child;
				
				$responseId = $response_str['ident'];
				$count++;
				
				$questionFinalText.="{#".$count."}";								
				
				//Add a short answer question..
				$shortAnswerQuestion = new ShortAnswerQuestion();
				$shortAnswerQuestion->id = $this->getNextId();
				$shortAnswerQuestion->parent = $question->id;
				$shortAnswerQuestion->name = $question->name;
				$shortAnswerQuestion->questiontextformat=1;
				$shortAnswerQuestion->generalfeedback="";
				$shortAnswerQuestion->generalfeedbackformat=1;
				$shortAnswerQuestion->defaultmark="1.0000000";
				$shortAnswerQuestion->penalty="0.0000000";
				$shortAnswerQuestion->length="1";
				$shortAnswerQuestion->stamp=time();
				$shortAnswerQuestion->version=time();
				$shortAnswerQuestion->hidden=0;
				$shortAnswerQuestion->timecreated=time();
				$shortAnswerQuestion->timemodified=time();
				$shortAnswerQuestion->createdby=$question->createdby;
				$shortAnswerQuestion->modifiedby=$question->modifiedby;
				
				
				$shortAnswer = new ShortAnswer();
				$shortAnswer->id=$shortAnswerQuestion->id;
				$shortAnswer->usecase = $usercase;
				
				$shortAnswerQuestion->shortAnswer = $shortAnswer;
				
				//Answers
				$count2 = 0;
				
				$shortAnswerQuestionText ="";
				
				$maxScore = 0;
				$vide = false;
				foreach ($xmlContent->xpath('//ims:respcondition') as $respcondition){
				
					$varEqual = $respcondition->conditionvar->varequal;
					$varExt = $respcondition->conditionvar->var_extension;
					$varSubset = $respcondition->conditionvar->varsubset;
					$answerText = "";
					if(!empty($varEqual)){
						if((string)$varEqual['respident']==(string)$responseId){
							$answerText = $varEqual;
						}
					}else if(!empty($varExt)){
						$varExtChild = $varExt->children('http://www.webct.com/vista/assessment');
						if((string)$varExtChild['respident']==(string)$responseId){
							$answerText = $varExtChild;
							
							$rem = "Une r�ponse est �valu�e � l'aide d'une expression r�guli�re suivante :".$answerText;
							$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_ANSWER_EVALUATE_BY_REGEX,
									$question->id, $question->name . ' ('. $question->category->name . ')',
									$rem);
								
						}
				
					}else if(!empty($varSubset)){
						if((string)$varSubset['respident']==(string)$responseId){
							$answerText = "*".$varSubset."*";
							
							$rem = "Une r�ponse est valide si elle contient l'expression suivante :".$varSubset;
							$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_ANSWER_VALIDITY,
									$question->id, $question->name . ' ('. $question->category->name . ')',
									$rem);
								
						}
				
					}
				
					if(empty($answerText)){
						$rem = "Une r�ponse n'a pas �t� prise en compte parce qu'elle �tait vide.";
						$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_EMPTY_ANSWER,
								$question->id, $question->name . ' ('. $question->category->name . ')',
								$rem);
						continue;
					}
				
					$answer = new Answer();
					$answer->contextid = $question->category->contextid;
					$answer->id=$this->getNextId();// 		id="4"
					$answer->answertext=$answerText;// 		<answertext>&lt;p&gt;1,05 10&amp;lt;SUP&amp;gt;-22&amp;lt;/SUP&amp;gt; g&lt;/p&gt;</answertext>
					$answer->answerformat="0";// 		<answerformat>1</answerformat>
				
					if((string)$respcondition->setvar['varname']== "SCORE"){
						$score = $respcondition->setvar/100;
						if((string)$respcondition->setvar['action']=="Subtract"){
							$score=-$score;
						}
						$answer->fraction=$score;// 		<fraction>1.0000000</fraction>
					}
				
					if($maxScore < $answer->fraction){
						$maxScore = $answer->fraction;
					}
						
					$answer->feedback="";
					$answer->feedbackformat="1";
				
					$shortAnswerQuestion->answers[] = $answer;
						
				}
				
				//We have to adapt all the scores to match
				$ponderation = $maxScore *100;
				if($ponderation<1){
					$ponderation = 1;
					$rem = "Probl�me de pond�ration rencontr� (<1).";
					$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_GRADE_ERROR,
							$question->id, $question->name . ' ('. $question->category->name . ')',
							$rem);
				}
				
				$shortAnswerQuestionText="{".$ponderation.":SHORTANSWER";
				if($shortAnswer->usecase==1){
					$shortAnswerQuestionText =$shortAnswerQuestionText."_C";
				}
				$shortAnswerQuestionText =$shortAnswerQuestionText.":";
				
				foreach ($shortAnswerQuestion->answers as $answer){
					if($maxScore!=0){
						$answer->fraction = $answer->fraction/$maxScore;
					}
					$shortAnswerQuestionText=$shortAnswerQuestionText."%".round($answer->fraction*100,0)."%".$answer->answertext."#~";
				}
				
				$shortAnswerQuestionText = substr($shortAnswerQuestionText,0,-1)."}";
				$shortAnswerQuestion->questiontext =$shortAnswerQuestionText;
				
				//Add the short question to the current category..
				$question->category->addQuestion($shortAnswerQuestion);
				$multiAnswer->sequence[]=$shortAnswerQuestion->id;
				
				$this->allQuestions[(string)$shortAnswerQuestion->id]=$shortAnswerQuestion;
				
			}
		}
		
		$question->questiontext =$questionFinalText;
		$question->multiAnswer = $multiAnswer;

	}
	
	
	
	/**
	 * @param MatchingQuestion $question
	 * @param SimpleXMLElement $xmlContent
	 */
	public function fillMatchingQuestion(&$question, $xmlContent){
	
		foreach ($xmlContent->xpath('//ims:qtimetadatafield') as $qtimetadatafield){
			if((string)$qtimetadatafield->fieldlabel=="wct_m_grading_scheme"){
				if((string)$qtimetadatafield->fieldentry!="EQUALLY_WEIGHTED"){
					
					$rem = "Bar�me de notation \"".$qtimetadatafield->fieldentry. 
							"\" convertie en pond�ration �quilibr�e.";
					$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_GRADING_SCHEME_CONVERTED,
							$question->id, $question->name . ' ('. $question->category->name . ')',
							$rem);
				}
				break;
			}
		}
		

		
		$hasPreview = false;
		$questionsList = array();
		$responsesList = array();
		$materialTable = $xmlContent->xpath("//ims:material_table[@label='tableLabel']");
		if(!empty($materialTable)){	
			$hasPreview = true;
			$materialTableChildren = $materialTable[0]->children('http://www.webct.com/vista/assessment');
			$extFlows = $materialTableChildren->matching_ext_flow;

			foreach ($extFlows[0]->matching_text_ext as $extMattext){
				$matText=$extMattext->children("http://www.imsglobal.org/xsd/ims_qtiasiv1p2");
				$questionsList[]=(string)$matText->mattext;
			}
			foreach ($extFlows[1]->matching_text_ext as $extMattext){
				$matText = $extMattext->children("http://www.imsglobal.org/xsd/ims_qtiasiv1p2");
				$responsesList[]=(string)$matText->mattext;
			}
		}
		
		$matches = new Matches();
		
		//IDEM MULTICHOICE
		$matchOptions = new MatchOptions();
		$matchOptions->id=$this->getNextId();
		$matchOptions->shuffleanswers=1;// 		<shuffleanswers>1</shuffleanswers>
		$matchOptions->correctfeedback=utf8_encode('Votre r�ponse est correcte.');// 		<correctfeedback>&lt;p&gt;Your answer is correct.&lt;/p&gt;</correctfeedback>
		$matchOptions->correctfeedbackformat=1;// 		<correctfeedbackformat>1</correctfeedbackformat>
		$matchOptions->partiallycorrectfeedback=utf8_encode('Votre r�ponse est partiellement correcte.');;// 		<partiallycorrectfeedback>&lt;p&gt;Your answer is partially correct.&lt;/p&gt;</partiallycorrectfeedback>
		$matchOptions->partiallycorrectfeedbackformat=1;// 		<partiallycorrectfeedbackformat>1</partiallycorrectfeedbackformat>
		$matchOptions->incorrectfeedback=utf8_encode('Votre r�ponse est incorrecte.');// 		<incorrectfeedback>&lt;p&gt;Your answer is incorrect.&lt;/p&gt;</incorrectfeedback>
		$matchOptions->incorrectfeedbackformat=1;// 		<incorrectfeedbackformat>1</incorrectfeedbackformat>
		$matchOptions->shownumcorrect=1;// 		<shownumcorrect>1</shownumcorrect>		
		
		$matches->matchOptions = $matchOptions;		
		
		
		$lastAnswerText ="";
		if($hasPreview){
			$rem = "L'aper�u des colonnes a �t� supprim� et r�int�gr� directement dans les r�ponses.";
			$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_COLUMN_PREVIEW,
					$question->id, $question->name . ' ('. $question->category->name . ')',
					$rem);
			
			
			$questionsNumber = count($questionsList);
			$responsesNumber = count($responsesList);
			
			$maxNumber = $questionsNumber;
			if($questionsNumber<$responsesNumber){
				$maxNumber = $responsesNumber;
			}
			for ($i=0; $i<$maxNumber; $i++){
				$match = new Match();
				$match->id = $this->getNextId();
				$match->contextid = $question->category->contextid;
				if($i<$questionsNumber){
					$convertedText = $this->convertTextAndCreateAssociedFiles($questionsList[$i],7, $match);
				}else {
					$convertedText="";
				}
				$match->questiontext = $convertedText;
				
				$match->questiontextformat =1 ;

				if($i<$responsesNumber){				
					$convertedText = $this->convertTextAndCreateAssociedFiles($responsesList[$i],7, $match);
				}else {
					$rem = "R�ponse manquante alors le texte 'aucune correspondance' a �t� rajout�.";
					$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_MISSING_ANSWER,
							$question->id, $question->name . ' ('. $question->category->name . ')',
							$rem);
					
					$convertedText = "aucune correspondance";
				}
				$lastAnswerText = $convertedText;
				$match->answertext = $convertedText;
				
				$matches->matches[]=$match;
			}	
		}else {
			$extraAnswers = array();
			$extraAnswersFilled = false;
			foreach ($xmlContent->xpath('//ims:response_grp') as $response_grp){
				
				$match = new Match();
				$match->id = $this->getNextId();
				$match->contextid = $question->category->contextid;
				
				$convertedText = $this->convertTextAndCreateAssociedFiles((string)$response_grp->material->mattext,7, $match); 
				$match->questiontext = $convertedText;
				
				$match->questiontextformat =1 ;
	
				$machtText = "";
				foreach ($response_grp->render_choice->flow_label->response_label as $response_label){
					if(substr($response_label['ident'],0,2)!="NO"){
						$machtText = $response_label->material->mattext;
						if(!empty($extraAnswers)){
							break;
						}
					}elseif($extraAnswersFilled==false && $response_label->material->mattext['label']=="EXTRA"){
						$extraAnswers[]=(string)$response_label->material->mattext;
					}
				}
				
				if(!empty($extraAnswers)){
					$extraAnswersFilled = true;
				}
						
				$convertedText = $this->convertTextAndCreateAssociedFiles($machtText,7, $match);				
				$lastAnswerText = $convertedText;
				$match->answertext = $convertedText;
				
				$matches->matches[]=$match;
			}
			
			//We must add the extra answers
			foreach ($extraAnswers as $extraAnswer){
				$match = new Match();
				$match->id = $this->getNextId();
				$match->contextid = $question->category->contextid;
				
				$match->questiontext = "";
				$match->questiontextformat =1 ;
				
				$convertedText = $this->convertTextAndCreateAssociedFiles($extraAnswer,7, $match);
				$lastAnswerText = $convertedText;
				$match->answertext = $convertedText;
				
				$matches->matches[]=$match;
			}
		}
		//ADD the last answer, one more time but without the question..
		$lastMatch = new Match();
		$lastMatch->id = $this->getNextId();
		$lastMatch->questiontext = "";
		$lastMatch->questiontextformat = 1;
		$lastMatch->answertext = $lastAnswerText;		
		$matches->matches[]=$lastMatch;
		
		
		$question->matches = $matches;
		
	}
	
	
	/**
	 * @param ParagraphQuestion $question
	 * @param SimpleXMLElement $xmlContent
	 */
	public function fillParagraphQuestion(&$question, $xmlContent){
		
		$rem = "Seul le nombre de lignes a pu �tre pris en compte pour ce type de question.";
		$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_LINE_NUMBER,
				$question->id, $question->name . ' ('. $question->category->name . ')',
				$rem);
		
		$essay = new Essay();
		
		$essay->id=$this->getNextId();
		$essay->responseformat="editor";
		$essay->attachments=0;
		
		$lineNumber=0;
	
		foreach ($xmlContent->xpath('//ims:qtimetadatafield') as $qtimetadatafield){
			if((string)$qtimetadatafield->fieldlabel=="answerBoxHeight"){
				$lineNumber = $qtimetadatafield->fieldentry;
				break;
			}
		}
		$essay->responsefieldlines = $lineNumber;
		
		$preText = $xmlContent->presentation->flow->response_str->render_fib->response_label->material->mattext;
		$convertedText = $this->convertTextAndCreateAssociedFiles($preText,8, $question);
		$essay->responsetemplate=$convertedText;
		$essay->responsetemplateformat=1;
		
		$convertedText="";
		foreach ($xmlContent->xpath('//ims:itemfeedback') as $itemfeedback){
			if((string)$itemfeedback['ident']=="CORRECT_ANSWER"){
				$solutionText = $itemfeedback->solution->solutionmaterial->material->mattext;
				$convertedText = $this->convertTextAndCreateAssociedFiles($solutionText,8, $question);
				break;
			}
		}
		$essay->graderinfo=$convertedText;
		$essay->graderinfoformat=1;
		

		
		$question->essay = $essay;
	}
	
	
	/**
	 * @param TrueFalseQuestion $question
	 * @param SimpleXMLElement $xmlContent
	 */
	public function fillTrueFalseQuestion(&$question, $xmlContent){
	
		$trueFalseAnswer = new TrueFalseAnswer();
		$trueFalseAnswer->id = $this->getNextId();
		
		$trueAnswer = new Answer();
		$trueAnswer->contextid = $question->category->contextid;
		$trueAnswer->id=$this->getNextId();
		$trueAnswer->answertext="True";
		$trueAnswer->answerformat="0";
		$trueAnswer->feedback="";
		$trueAnswer->feedbackformat="1";

		$falseAnswer = new Answer();
		$falseAnswer->contextid = $question->category->contextid;
		$falseAnswer->id=$this->getNextId();
		$falseAnswer->answertext="False";
		$falseAnswer->answerformat="0";
		$falseAnswer->feedback="";
		$falseAnswer->feedbackformat="1";
		
		
		//NORMALLY ONLY ONE VAREQUAL
		foreach ($xmlContent->xpath('//ims:varequal') as $varEqual){
			
			if((string)$varEqual=="true"){
				$trueAnswer->fraction="1.0000000";
				$falseAnswer->fraction="0.0000000";
			}else {
				$trueAnswer->fraction="0.0000000";
				$falseAnswer->fraction="1.0000000";
			}			
		}
		
		$question->answers[] = $trueAnswer;
		$question->answers[] = $falseAnswer;
		
		$trueFalseAnswer->trueanswer=$trueAnswer->id;
		$trueFalseAnswer->falseanswer=$falseAnswer->id;
	
		$question->trueFalseAnswer = $trueFalseAnswer;
	}
	
	
	/**
	 * @param CalculatedQuestion $question
	 * @param SimpleXMLElement $xmlContent
	 */
	public function fillCalculatedQuestion(&$question, $xmlContent){
	
		$convertedDescription="";
		foreach ($xmlContent->xpath('//ims:qtimetadatafield') as $qtimetadatafield){
			if((string)$qtimetadatafield->fieldlabel=="wct_calc_questionText"){
				$webCtText = (string)$qtimetadatafield->fieldentry;
				
				$webctCaract = array("[", "]");
				$moodleCaract   = array("{", "}");
				
				$convertedText = str_replace($webctCaract, $moodleCaract, $webCtText);				
				$convertedDescription = $this->convertTextAndCreateAssociedFiles($convertedText, 3, $question);				
				
				break;
			}
		}
		
		$imageNames = $xmlContent->xpath('//ims:matimage');
		if(!empty($imageNames)){
			$imageName = (string)$imageNames[0];			
			$imageURI = $imageName['uri'];
			$findContentId   = '?contentID=';
			$pos = strpos($imageURI, $findContentId);
			if($pos>0 && !empty($imageName)){
				$fileContentId = substr($imageURI, $pos+11);
				$this->addCMSFile($fileContentId, 3, $question);
			
				$convertedDescription .= "<br/><img src=\"@@PLUGINFILE@@/".$imageName."\"/>";
			}
		}
		$question->questiontext=$convertedDescription;

		
		$answer = new Answer();
		$answer->contextid = $question->category->contextid;
		$answer->id=$this->getNextId();
		
		$matExtension = $xmlContent->presentation->flow->material->mat_extension;

		$calulatedChild = $matExtension->children('http://www.webct.com/vista/assessment');
		
		$answer->answertext=$this->convertFormula($calulatedChild->calculated->formula);		
		$rem = "Formule WEBCT : ". $calulatedChild->calculated->formula ." --> Formule MOODLE:  " .$answer->answertext;
		$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_FORMULA,
				$question->id, $question->name . ' ('. $question->category->name . ')',
				$rem);
		
		$answer->answerformat=0;
		$answer->fraction="1.0000000";
		$answer->feedback="";
		$answer->feedbackformat=1;

		$question->answers[]=$answer;
		$countItem = 0;
		
		foreach ($calulatedChild->calculated->var as $var){
			$name = $var['name'];
			$datasetDefinition = new DatasetDefinition();
			$datasetDefinition->id = $this->getNextId();
			$datasetDefinition->category=0;
			$datasetDefinition->name=$name;
			$datasetDefinition->type=1;
			
			$datasetDefinition->options="uniform:".$var['min'].':'.$var['max'].":1";
			$datasetDefinition->itemcount=10;
			
			foreach ($calulatedChild->calculated->calculated_set as $calculatedSet){
				$index = $calculatedSet['index'];
				foreach ($calculatedSet->calculated_var as $calculatedVar){
					if((string)$calculatedVar['name']==(string)$name){
						$datasetItem = new DatasetItem($countItem++,$index+1,$calculatedVar['value']);
						$datasetDefinition->datasetItems[]=$datasetItem;
					}
				}
			}

			$question->datasetDefinitions[]=$datasetDefinition;
			
		}		
		
		
		$unitEval = $xmlContent->xpath('//ims:unit_eval');
		
		$numericalUnit = new NumericalUnit(1,1,(string)$unitEval[0]->conditionvar->varequal);
		$question->numericalUnits[]=$numericalUnit;
	
		
		$itemprocExtension = $xmlContent->resprocessing->itemproc_extension;
		
		$calculatedAnswer = $itemprocExtension->children('http://www.webct.com/vista/assessment');
		$toleranceType = $calculatedAnswer->calculated_answer['toleranceType'];
		$tolerance = $calculatedAnswer->calculated_answer['tolerance'];
		
		$precisionType = $calculatedAnswer->calculated_answer['precisionType'];
		$precision = $calculatedAnswer->calculated_answer['precision'];
		
		$numericalOption = new NumericalOption();
		$numericalOption->id=$this->getNextId();
		if(empty($numericalUnit->unit)){
			$numericalOption->showunits=3;
			$numericalOption->unitgradingtype=0;
		}else {
			$numericalOption->showunits=0;
			$numericalOption->unitgradingtype=1;
		}
		$numericalOption->unitsleft=0;
		$numericalOption->unitpenalty=$unitEval[0]->setvar/100;
		
		$question->numericalOptions[]=$numericalOption;
		
		
		$calculatedRecord = new CalculatedRecord();
		$calculatedRecord->id=$this->getNextId();
		$calculatedRecord->answer=$answer->id;
		if((string)$toleranceType=="Unit"){
			$calculatedRecord->tolerancetype=2;
			$calculatedRecord->tolerance=$tolerance;
		}else {			
			$rem = "La tol�rance a �t� mis en %.Type tol�rance: ".$toleranceType ."--> Valeur tol�rance: ". $tolerance;
			$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_TOLERANCE,
					$question->id, $question->name . ' ('. $question->category->name . ')',
					$rem);
				
			$calculatedRecord->tolerancetype=1;
			$calculatedRecord->tolerance=$tolerance/100;
		}
		
		if((string)$precisionType=="Decimal"){
			$calculatedRecord->correctanswerformat=1;		
			$calculatedRecord->correctanswerlength=$precision;				
		}else {
			$rem = "La pr�cision a �t� mise en chiffres significatifs. Type pr�cision: ".$precisionType ."--> Valeur pr�cison: ". $precision;
			$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_PRECISION,
					$question->id, $question->name . ' ('. $question->category->name . ')',
					$rem);
			
			$calculatedRecord->correctanswerformat=2;		
			$calculatedRecord->correctanswerlength=$precision;
		}
		
		$question->calculatedRecords[]=$calculatedRecord;
		
		$calculatedOption = new CalculatedOption();
		$calculatedOption->id=$this->getNextId();
		$calculatedOption->synchronize=0;
		$calculatedOption->single=0;
		$calculatedOption->shuffleanswers=1;
		$calculatedOption->correctfeedback="";
		$calculatedOption->correctfeedbackformat=0;
		$calculatedOption->partiallycorrectfeedback="";
		$calculatedOption->partiallycorrectfeedbackformat=0;
		$calculatedOption->incorrectfeedback="";
		$calculatedOption->incorrectfeedbackformat=0;
		$calculatedOption->answernumbering="abc";
		
		$question->calculatedOptions[]=$calculatedOption;
		
	}
	
	public function convertFormula($webCtFormula){
		
		$webctCaract = array("[", "]","ln"," ");
		$moodleCaract   = array("{", "}","log","");
		
		$tempFormula = str_replace($webctCaract, $moodleCaract, $webCtFormula);
		
		//sum
		$webctCaract = array("sum", ",");
		$moodleCaract   = array("", "+");
		
		$tempFormula = str_replace($webctCaract, $moodleCaract, $tempFormula);
		
		//** --> pow -- difficile � remplacer (pour gagner du temps) cod�e de mani�re dure		
		$webctCaract = array("(1.12**7)","10**9",
				"{x}**{b}",		"{y}**{d}" , "{x}**2",
				"({a}**2)","({b}**2)","({c}**2)","({d}**2)","({e}**2)",
				"(({a}-{f})**2)"   , "(({b}-{f})**2)"  ,"(({c}-{f})**2)", "(({d}-{f})**2)", "(({e}-{f})**2)",
				"(({a}-{f})**3)"   , "(({b}-{f})**3)"  ,"(({c}-{f})**3)", "(({d}-{f})**3)", "(({e}-{f})**3)",
				"(({f}-({g}*{a}))**2)", "(({f}-({g}*{b}))**2)" , "(({f}-({g}*{c}))**2)" , "(({f}-({g}*{d}))**2)" ,"(({f}-({g}*{e}))**2)",
				"((0.2*({a}+{b}+{c}+{d}+{e}))**2)",
				"({d1}/{d2})**2",
				"{I}**2",
				"{v}**2","{v0}**2","{v1}**2","{v2}**2");
		$moodleCaract   = array("pow(1.12,7)", "pow(10,9)",
				"pow({x},{b})" ,"pow({y},{d})","pow({x},2)" ,
				"pow({a},2)","pow({b},2)","pow({c},2)","pow({d},2)","pow({e},2)", 
				"pow({a}-{f},2)" , "pow({b}-{f},2)" , "pow({c}-{f},2)" , "pow({d}-{f},2)", "pow({e}-{f},2)",
				"pow({a}-{f},3)" , "pow({b}-{f},3)" , "pow({c}-{f},3)" , "pow({d}-{f},3)", "pow({e}-{f},3)",
				"pow(({f}-({g}*{a})),2)","pow(({f}-({g}*{b})),2)","pow(({f}-({g}*{c})),2)","pow(({f}-({g}*{d})),2)","pow(({f}-({g}*{e})),2)",
				"pow((0.2*({a}+{b}+{c}+{d}+{e})),2)",
				"pow(({d1}/{d2}),2)",
				"pow({I},2)",
				"pow({v},2)","pow({v0},2)","pow({v1},2)","pow({v2},2)");
		
		$tempFormula = str_replace($webctCaract, $moodleCaract, $tempFormula);
				
		
		$moodleFormula=$tempFormula;
		
		return $moodleFormula;
	}
	
	
	/**
	 * @param CombinaisonMultiChoiceQuestion $question
	 * @param SimpleXMLElement $xmlContent
	 */
	public function fillCombinaisonMutipleChoiceQuestion(&$question, $xmlContent){
	
		//We have to complete the question test with response proposal
		$complementText ="<br/>";
		foreach ($xmlContent->presentation->flow->material[1]->children() as $child){
			if($child->getName()=="mattext"){
				$complementText.=(string)$child;
			}else if($child->getName()=="matbreak"){
				$complementText.="<br/>";
			}
		}

		$question->questiontext.=$complementText;
		
		$multichoice = new MultiChoice();
		$multichoice->id=$question->id;
	
		$multichoice->layout=0;// 			<layout>0</layout>
		if((string)$xmlContent->presentation->flow->response_lid['rcardinality']=="Single"){
			$multichoice->single=1;//             <single>1</single>
		}else {
			$multichoice->single=0;
		}
	
		if((string)$xmlContent->presentation->flow->response_lid->render_choice['shuffle']=="Yes"){
			$multichoice->shuffleanswers=1;//             <single>1</single>
		}else {
			$multichoice->shuffleanswers=0;
		}
	
	
		foreach ($xmlContent->itemmetadata->qtimetadata as $qtimetadata){
			$break = false;
			foreach ($qtimetadata as $qtimetadatafield){
				//echo 'TEST '.$qtimetadatafield->fieldlabel;
				if((string)$qtimetadatafield->fieldlabel=="wct_question_labelledletter"){
					if((string)$qtimetadatafield->fieldentry=="Yes"){
						$multichoice->answernumbering="abc";//             <answernumbering>abc</answernumbering>
					}else {
						$multichoice->answernumbering="123";//             <answernumbering>abc</answernumbering>
					}
					$break = true;
					break;
				}
			}
			if($break){
				break;
			}
		}
	
		//COMBINED FEEDBACK
		$multichoice->correctfeedback=utf8_encode('Votre r�ponse est correcte.');//             <correctfeedback>&lt;p&gt;Your answer is correct.&lt;/p&gt;</correctfeedback>
		$multichoice->correctfeedbackformat="1";//             <correctfeedbackformat>1</correctfeedbackformat>
		$multichoice->partiallycorrectfeedback=utf8_encode('Votre r�ponse est partiellement correcte.');//             <partiallycorrectfeedback>&lt;p&gt;Your answer is partially correct.&lt;/p&gt;</partiallycorrectfeedback>
		$multichoice->partiallycorrectfeedbackformat="1";//             <partiallycorrectfeedbackformat>1</partiallycorrectfeedbackformat>
		$multichoice->incorrectfeedback=utf8_encode('Votre r�ponse est incorrecte.');//             <incorrectfeedback>&lt;p&gt;Your answer is incorrect.&lt;/p&gt;</incorrectfeedback>
		$multichoice->incorrectfeedbackformat="1";//             <incorrectfeedbackformat>1</incorrectfeedbackformat>
		$multichoice->shownumcorrect="1";//             <shownumcorrect>1</shownumcorrect>
	
		$question->multiChoice = $multichoice;
	
		foreach ($xmlContent->presentation->flow->response_lid->render_choice->flow_label->response_label as $response_label){
	
			$webctAnswerId = $response_label['ident'];
	
			$answer = new Answer();
			$answer->contextid = $question->category->contextid;
			$answer->id=$this->getNextId();// 		id="4">
			
			$answerText = "";
			foreach ($response_label->material->mattext as $mattext){
				$answerText .= (string)$mattext;
			}
		
			$convertedDescription =  $this->convertTextAndCreateAssociedFiles($answerText,5, $answer);
			$answer->answertext=$convertedDescription;// 		<answertext>&lt;p&gt;1,05 10&amp;lt;SUP&amp;gt;-22&amp;lt;/SUP&amp;gt; g&lt;/p&gt;</answertext>
			$answer->answerformat="1";// 		<answerformat>1</answerformat>
	
	
			$webctFeedbackId="";
			foreach ($xmlContent->resprocessing->respcondition as $respcondition){
				if((string)$respcondition->conditionvar->varequal==$webctAnswerId){
					if((string)$respcondition->setvar['varname']== "SCORE"){
						$score = $respcondition->setvar/100;
						if((string)$respcondition->setvar['action']=="Subtract"){
							$score=-$score;
						}
						$answer->fraction=$score;// 		<fraction>1.0000000</fraction>
					}
	
					$webctFeedbackId = $respcondition->displayfeedback['linkrefid'];
					break;
				}
			}
	
			foreach ($xmlContent->itemfeedback as $itemfeedback){
				if($itemfeedback['ident']==$webctFeedbackId){
					foreach ($itemfeedback->material->mattext as $mattext){
						if(!empty($mattext[label])){
							$answerFeedbackText = $mattext;
							$convertedDescription =  $this->convertTextAndCreateAssociedFiles($answerFeedbackText,6, $answer);
							$answer->feedback=$convertedDescription;// 		<feedback>&lt;p&gt;C'est exact.&lt;/p&gt;</feedback>
							$answer->feedbackformat="1";// 		<feedbackformat>1</feedbackformat>
							break;
						}
					}
					break;
				}
			}
	
	
			$question->answers[] = $answer;
		}
	
	}
	
	/**
	 * @param JumbledSentenceQuestion $question
	 * @param SimpleXMLElement $xmlContent
	 */
	public function fillJumbledSentenceQuestion(&$question, $xmlContent){
				
		$multiAnswer = new MultiAnswer();
		$multiAnswer->question = $question->id;
			
	
	
		$questionFinalText="";
			
		if(strlen($question->name)>255){

			$rem = "La taille de la question a �t� limit� � 255 caract�res et son nom complet a �t� mis dans la description de cette question.";
			$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_NAME_TO_LONG,
					$question->id, $question->name . ' ('. $question->category->name . ')',
					$rem);
			
			$questionFinalText .= $question->name."<br/>";
				
			$question->name = substr($question->name, 252)."...";
		}

		//On boucle sur le flow
		$xmlImsRenderObject = $xmlContent->presentation->flow->response_lid->render_extension->ims_render_object;
	
		$count = 0;
		$multiChoiceAnswers = array();
		foreach ($xmlImsRenderObject->children() as $child){
				
			if($child->getName()=="material"){
	
				if(!empty($child->mattext)){
					$convertedDescription = $this->convertTextAndCreateAssociedFiles((string)$child->mattext,3, $question);
					$questionFinalText.=$convertedDescription;
				}
	
			}else if($child->getName()=="response_label"){
	
				$count++;	
				$questionFinalText.="{#".$count."}";
				
				$multiChoiceAnswers[(string)$child['ident']]=(string)$child->material->mattext;

			}
		}

		$correctAnswers = array(); 
		foreach ($xmlContent->xpath('//ims:respcondition') as $respcondition){
			if($respcondition->setvar=="100.0"){
				foreach ($respcondition->conditionvar->and->varequal as $varequal){
					$correctAnswers[]=(string)$varequal;
				}
			}else if(!empty($respcondition->setvar)){
				
				$rem = "Les r�ponses alternatives n'ont pas pu �tre reprises";
				$this->rapportMigration->add(RapportMigration::TYPE_QUESTION,RapportMigration::QUESTION_ALTER_ANSWER_NOT_TAKEN,
						$question->id, $question->name . ' ('. $question->category->name . ')',
						$rem);
			}
		}
		
		
		//var_dump($multiChoiceAnswer);
		$countAnswers = count($correctAnswers);
		$count=0;
		//On cr�e les questions � choix multiple
		foreach ($correctAnswers as $correctAnswer){
			$count++;
			
			$multiChoiceQuestion = new MultiChoiceQuestion();
			$multiChoiceQuestion->id = $this->getNextId();
			$multiChoiceQuestion->parent = $question->id;
			$multiChoiceQuestion->name = $question->name;
			
			$multiChoiceQuestion->questiontextformat=1;
			$multiChoiceQuestion->generalfeedback="";
			$multiChoiceQuestion->generalfeedbackformat=1;
			$multiChoiceQuestion->defaultmark="1.0000000";
			$multiChoiceQuestion->penalty="0.0000000";
			$multiChoiceQuestion->length="1";
			$multiChoiceQuestion->stamp=time();
			$multiChoiceQuestion->version=time();
			$multiChoiceQuestion->hidden=0;
			$multiChoiceQuestion->timecreated=time();
			$multiChoiceQuestion->timemodified=time();
			$multiChoiceQuestion->createdby=$question->createdby;
			$multiChoiceQuestion->modifiedby=$question->modifiedby;
			
			
			$multichoice = new MultiChoice();
			$multichoice->id=$multiChoiceQuestion->id;
			$multichoice->layout=0;
			$multichoice->single=1;
			$multichoice->shuffleanswers=1;
			$multichoice->answernumbering=0;
			$multichoice->shownumcorrect=0;
			//COMBINED FEEDBACK
			$multichoice->correctfeedback="";
			$multichoice->correctfeedbackformat="1";
			$multichoice->partiallycorrectfeedback="";
			$multichoice->partiallycorrectfeedbackformat="1";
			$multichoice->incorrectfeedback="";
			$multichoice->incorrectfeedbackformat="1";
			$multichoice->shownumcorrect="1";
			
			$multiChoiceQuestion->multiChoice = $multichoice;
				
			//On cr�e le text des multichoice
			$multiChoiceText = "{1:MULTICHOICE:";
			
			foreach ($correctAnswers as $correctAnswer2){
				
				$answer = new Answer();
				$answer->contextid = $question->category->contextid;
				$answer->id=$this->getNextId();
				$answer->answertext=$multiChoiceAnswers[$correctAnswer2];
				$answer->answerformat="1";
				$answer->feedback="";
				$answer->feedbackformat=1;
				
				if($correctAnswer==$correctAnswer2){
					$multiChoiceText .="~%100%".$multiChoiceAnswers[$correctAnswer2]."#";
					$answer->fraction="1.0000000";
				}else {
					$multiChoiceText .="~%-".($countAnswers*100)."%".$multiChoiceAnswers[$correctAnswer2]."#";
					$answer->fraction="-".$countAnswers.".0000000";
				}
				
				$multiChoiceQuestion->answers[] = $answer;
			}
			
			$multiChoiceText = substr($multiChoiceText,0,-1)."}";
			
			
			$multiChoiceQuestion->questiontext=$multiChoiceText;
			
			
			//Add the short question to the current category..
			$question->category->addQuestion($multiChoiceQuestion);
			$multiAnswer->sequence[]=$multiChoiceQuestion->id;
			
			$this->allQuestions[(string)$multiChoiceQuestion->id]=$multiChoiceQuestion;
				
		}
				
		//Get the file attached if any and past it to the description
		$imageNames = $xmlContent->xpath('//ims:matimage');
		if(!empty($imageNames)){
			$imageName = (string)$imageNames[0];			
			$imageURI = $imageName['uri'];
			$findContentId   = '?contentID=';
			$pos = strpos($imageURI, $findContentId);
			if($pos>0 && !empty($imageName)){
				// 			echo 'IMAGE NAME = '.$imageName."\n";
				// 			echo 'IMAGE URI = '.$imageURI."\n";
				$fileContentId = substr($imageURI, $pos+11);
				$this->addCMSFile($fileContentId, 3, $question);
			
				$questionFinalText .= "<br/><img src=\"@@PLUGINFILE@@/".$imageName."\"/>";
			}
		}
		
		$question->questiontext =$questionFinalText;
		
		//echo '<br/> TEXT = '.$question->questiontext."<br/>";
		$question->multiAnswer = $multiAnswer;
	
	}
	
	
	/***************************************************************************************************************
	 * QUIZZ
	*/
	
	public function retrieveQuizzes(){
	
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='ASSESSMENT_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."' ORDER BY NAME";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
	
			$quizId = $row['ORIGINAL_CONTENT_ID'];
			$this->addQuiz($quizId);
		}
	}
	
	/**
	 * Add a Quiz
	 */
	public function addQuiz($quizId){
	
		global $USER;
	
		$sectionId = $this->fixedSections[GlobalModel::SECTION_ASSESSMENTS];
		
		//Glossary
		$quizModel = new QuizModel();
		$quizModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$quizModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$quizModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$quizModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED

		
		$quizModel->module = $this->createModule($quizId,"quiz","2013110501",$sectionId);	
	
		$quizModel->quiz = $this->createQuiz($quizId, $quizModel->module);
	
		
		//Add the default category
		//Create a new category
		$questionCategory = new QuestionCategory();
		
		$questionCategory->id = $this->getNextId();
		$questionCategory->name = utf8_encode("Cat�gorie par d�faut pour ".$quizModel->quiz->name);
		$questionCategory->contextid = $quizModel->quiz->contextid;
		$questionCategory->contextlevel = 70; //CONTEXT_MODULE
		$questionCategory->contextinstanceid = $quizModel->quiz->quizId;
		$questionCategory->info = utf8_encode("La cat�gorie par d�faut pour les questions partag�es du contexte de ".$quizModel->quiz->name);
		$questionCategory->infoformat = 0;
		$questionCategory->stamp = time(); // localhost+140131155733+469Glc
		$questionCategory->parent = 0;
		$questionCategory->sortorder = 999;
		
		$this->questions->question_categories[] = $questionCategory;
		$this->course->inforef->questioncategoryids[]=$questionCategory->id;
		
		//Grade
		$gradeBook = new ActivityGradeBook();
		
		$gradeItem = new GradeItem();
		$gradeItem->id=$this->getNextId();
		$gradeItem->categoryid = $this->gradebook->grade_categories[0]->id;
		$gradeItem->itemname =$quizModel->quiz->name;
		$gradeItem->itemtype ="mod";
		$gradeItem->itemmodule ="quiz";
		$gradeItem->iteminstance = $quizModel->quiz->id;
		$gradeItem->itemnumber =0 ;//<itemnumber>$@NULL@$</itemnumber>
		$gradeItem->iteminfo ="$@NULL@$";//<iteminfo>$@NULL@$</iteminfo>
		$gradeItem->idnumber ="$@NULL@$";//<idnumber>$@NULL@$</idnumber>
		$gradeItem->calculation ="$@NULL@$";//<calculation>$@NULL@$</calculation>
		$gradeItem->gradetype =1 ;//<gradetype>1</gradetype>
		$gradeItem->grademax =$quizModel->quiz->grade;//<grademax>100.00000</grademax>
		$gradeItem->grademin ="0.00000" ;//<grademin>0.00000</grademin>
		$gradeItem->scaleid = "$@NULL@$";//scaleid>$@NULL@$</scaleid>
		$gradeItem->outcomeid = "$@NULL@$";//<outcomeid>$@NULL@$</outcomeid>
		$gradeItem->gradepass = "0.00000";//<gradepass>0.00000</gradepass>
		$gradeItem->multfactor ="1.00000" ;//<multfactor>1.00000</multfactor>
		$gradeItem->plusfactor = "0.00000";//<plusfactor>0.00000</plusfactor>
		$gradeItem->aggregationcoef = "0.00000";//<aggregationcoef>0.00000</aggregationcoef>
		$gradeItem->sortorder = 1;//<sortorder>1</sortorder>
		$gradeItem->display = 0;//<display>0</display>
		$gradeItem->decimals = "$@NULL@$";//<decimals>$@NULL@$</decimals>
		$gradeItem->hidden = 0;//<hidden>0</hidden>
		$gradeItem->locked = 0;//<locked>0</locked>
		$gradeItem->locktime= 0;//<locktime>0</locktime>
		$gradeItem->needsupdate = 1;//<needsupdate>1</needsupdate>
		$gradeItem->timecreated = time();
		$gradeItem->timemodified = time() ;
		
		$gradeBook->grade_items[]= $gradeItem;
		$quizModel->grades = $gradeBook;
		
		
		
		//Event associ�
		$event = new Event();
		$event->id=$this->getNextId();//
		$event->name=$quizModel->quiz->name;//<name>Eval 2 (Quiz opens)</name>
		$event->description=$quizModel->quiz->intro;//<description>&lt;div class="no-overflow"&gt;&lt;p&gt;Voici ma description de mon �valuation...&lt;/p&gt;&lt;/div&gt;</description>
		$event->format=1;//<format>1</format>
		$event->courseid=$this->course->course->id;//<courseid>6</courseid>
		$event->groupid=0;//<groupid>0</groupid>
		$event->userid=$USER->id;//<userid>2</userid>
		$event->repeatid=0;//<repeatid>0</repeatid>
		$event->modulename="quiz";//<modulename>quiz</modulename>
		$event->instance=$quizModel->quiz->id;//<instance>40</instance>
		$event->eventtype="open";//<eventtype>open</eventtype>
		$event->timestart=$quizModel->quiz->timeopen;//<timestart>-152423940</timestart>
		if($quizModel->quiz->timeopen==0 && $quizModel->quiz->timeclose==0){
			$event->timeduration=0;
		}else if($quizModel->quiz->timeopen==0){
			$event->timeduration=$quizModel->quiz->timeclose  - time();				
		}else if($quizModel->quiz->timeclose==0){	
			$event->timeduration=0;				
		}else {
			$event->timeduration=$quizModel->quiz->timeclose-$quizModel->quiz->timeopen;//<timeduration>0</timeduration>
		}
		$event->visible=0;//<visible>0</visible>
		$event->uuid="";//<uuid></uuid>
		$event->sequence=1;//<sequence>1</sequence>
		$event->timemodified=time();//<timemodified>1392650251</timemodified>
		
		$events = new Events();
		$events->events[] = $event;
		$quizModel->calendar = $events;
		
		
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$quizModel->module->id;
		$activity->sectionid=$sectionId;
		$activity->modulename=$quizModel->module->modulename;
		$activity->title=$quizModel->quiz->name;
		$activity->directory="activities/quiz_".$quizModel->quiz->quizId;
	
		$this->moodle_backup->contents->activities[] = $activity;
	
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","quiz_".$quizModel->quiz->quizId,"quiz_".$quizModel->quiz->quizId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","quiz_".$quizModel->quiz->quizId,"quiz_".$quizModel->quiz->quizId."_userinfo",1);
	
		$inforRef = new InfoRef();
		$inforRef->userids[]=$USER->id;
		$inforRef->fileids=$quizModel->quiz->filesIds;
		
		foreach ($this->questions->question_categories as $categorie){
			
			if($categorie->contextlevel==70){
				if($categorie->contextinstanceid==$quizModel->quiz->quizId){
					$inforRef->questioncategoryids[]=$categorie->id;
				}
			}else {
				$inforRef->questioncategoryids[]=$categorie->id;
			}
		}
		
		$inforRef->gradeItemids[] = $gradeItem->id;
		
		$quizModel->inforef = $inforRef;
				
		$this->activities[] = $quizModel;
		
		$this->sections[$sectionId]->section->sequence[]= $quizModel->quiz->quizId;
		
		//rapport 
		$rem = count($quizModel->quiz->questions);
		$this->rapportMigration->add(RapportMigration::TYPE_EVALUATION,RapportMigration::EVALUATION_QUESTIONS_COUNT,
				$quizModel->quiz->id, $quizModel->quiz->name,
				$rem);
		
		$this->rapportMigration->evaluationsCount++;
		
	}
	
	/**
	 * @var unknown $glossaryId
	 * @var Module $module
	 * @return Glossary
	 */
	public function createQuiz($quizId, $module){
		
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE ORIGINAL_CONTENT_ID='".$quizId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
		
		$quiz = new ActivityQuiz();
		$quiz->id = $quizId;
		$quiz->moduleid =$module->id;
		$quiz->modulename =$module->modulename;
		$quiz->contextid=$this->getNextId();
		$quiz->quizId = $quizId;
		
		
		$quiz->name =$row['NAME'];
		
		$description = $row['DESCRIPTION'];
		if(empty($description)){
			$description ="";
		}else {
			$description =$description->load() . '<br/>';
		}
		
		
		$request = "SELECT * FROM ASSMT_ASSESSMENT 
						LEFT JOIN ASSMT_SETTING ON ASSMT_ASSESSMENT.ID=ASSMT_SETTING.ASSESSMENT_ID 
		  				LEFT JOIN ASSMT_SECURITY_SETTING ON ASSMT_SETTING.ID=ASSMT_SECURITY_SETTING.ID
						LEFT JOIN ASSMT_SUBMISSION_SETTING ON ASSMT_SETTING.ID=ASSMT_SUBMISSION_SETTING.ID
					  	LEFT JOIN ASSMT_RESULT_SETTING ON ASSMT_SETTING.ID=ASSMT_RESULT_SETTING.ID 
					WHERE ASSMT_ASSESSMENT.ID='".$quizId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
				
		$quiz->intro = $description.$row['INSTRUCTIONS'];
		$quiz->introformat =1;		
		
		$timeopen = substr($row['STARTTIME'],0,-3);
		if(empty($timeopen)){
			$timeopen=0;
		}
		$quiz->timeopen = $timeopen;
		
		$timeclose = substr($row['ENDTIME'],0-3);
		if(empty($timeopen)){
			$timeclose=0; 
		}
		$quiz->timeclose=$timeclose; 
		
		$durationUnlimited = $row['DURATIONUNLIMITED'];
		$duration = $row['DURATION'];
		$durationUnit = $row['DURATIONUNITS'];
		
		if($durationUnlimited==0){
			if($durationUnit=='days'){
				$quiz->timelimit = $duration*86400;
			}else if($durationUnit=='hours'){
				$quiz->timelimit = $duration*3600; 
			}else if($durationUnit=='minutes'){
				$quiz->timelimit = $duration*60;
			}else if($durationUnit=='seconds'){
				$quiz->timelimit = $duration;
			}
		}else {
			$quiz->timelimit = 0;
		}
		 
		$allowSubmissionAfter = $row['ALLOWSUBMISSIONAFTER'];
		
		if($allowSubmissionAfter==1){
			$quiz->overduehandling ='autosubmit';
		}else {
			$quiz->overduehandling ='autoabandon';
		}		
		$quiz->graceperiod=0;
		
		$quiz->preferredbehaviour='deferredfeedback';
		$quiz->attempts_number=$row['NUMBEROFATTEMPTS'];
		$quiz->attemptonlast=0;
		switch ($row['RESULTSSCORETYPE']){
			case 'Highest':
				$quiz->grademethod=1;				
				break;
			case 'Average':
				$quiz->grademethod=2;				
				break;
			case 'First':
				$quiz->grademethod=3;				
				break;
			case 'Last':
				$quiz->grademethod=4;			
				break;
			default:
				$quiz->grademethod=1;
				$rem = "Aucune m�thode de gradation n'a �t� trouv�e. La m�thode de gradation par d�faut (Meilleur r�sultat) a �t� appliqu�e.";
				$this->rapportMigration->add(RapportMigration::TYPE_EVALUATION,RapportMigration::EVALUATION_NO_GRADING_METHOD,
						$quiz->id, $quiz->name,
						$rem);
				break;
		}
		
		
		$quiz->decimalpoints=2;		
		$quiz->questiondecimalpoints=-1;
		
		
		//REVIEW OPTIONS
		if($row['RESULTSTEXT']==1){
			$quiz->reviewattempt=69904;
		}else {
			$quiz->reviewattempt=65536;
		}		
		if($row['RESULTSEVALUATION']==0){
			$quiz->reviewcorrectness=0;
		}else {
			$quiz->reviewcorrectness=4368;
		}		
		switch($row['RESULTSRELEASETYPE']){
			case 'doNotRelease':
				$quiz->reviewmarks=0;
				break;
			case 'releaseAfterSubmission':
				$quiz->reviewmarks=4368;
				break;
			case 'releaseAfterAllGrading':
				$quiz->reviewmarks=4368;
				break;
			case 'releaseAfterAvailableEnded':
				$quiz->reviewmarks=16;
				break;
				
			case 'releaseAfterEndedAndGraded':
				$quiz->reviewmarks=16;
				break;
		}
		if($row['RESULTSFEEDBACK']==0){
			$quiz->reviewspecificfeedback=0;				
		}else {
			$quiz->reviewspecificfeedback=4368;
		}
		$quiz->reviewgeneralfeedback=4368;
		
		IF($row['RESULTSCORRECTANSWER']==1 || $row['RESULTSFULLEVALUATION']==1){
			$quiz->reviewrightanswer=4368;				
		}else {
			$quiz->reviewrightanswer=0;
		}
		
		$quiz->reviewoverallfeedback=4368;
		
		
		
		$quiz->questionsperpage=0;
		
		if($row['QUESTIONDELIVERY']='allAtOnce'){
			$quiz->navmethod="free";
		}else {
			$quiz->navmethod="sequential";
		}
		
		if(isset($row['RANDOMIZE_ATTEMPTS']) && $row['RANDOMIZE_ATTEMPTS']==1){
			$quiz->shuffleanswers =1 ;				
		}else {
			$quiz->shuffleanswers =0;
		}
		$quiz->shufflequestions=0;
		
		//QUESTIONS
		
		$quiz->sumgrades="1.00000";
		if(isset($row['MAXSCORE'])){
			$quiz->grade = str_replace(',', '.', $row['MAXSCORE']);
		}else {
			$rem = "Pas de score maximum trouv�. Le grade a �t� mis � 0.";
			$this->rapportMigration->add(RapportMigration::TYPE_EVALUATION,RapportMigration::EVALUATION_NO_MAX_SCORE,
					$quizModel->quiz->id, $quizModel->quiz->name,
					$rem);
			
			$quiz->grade = "0.00000";
		}

		$quiz->timecreated=time();
		$quiz->timemodified=time();
		if(isset($row['SECURITYPASSWORD'])){
			$quiz->password=$row['SECURITYPASSWORD'];
		}else {
			$quiz->password="";
		}
		
		if(isset($row['SECURITYADDRESS'])){
			$address = $row['SECURITYADDRESS'];
			if($address!='0.0.0.0'){
				$quiz->subnet= $address;
			}
		}
		
		$quiz->browsersecurity='-';
		$quiz->delay1=0;
		$quiz->delay2=0;

		$quiz->showuserpicture=0;
		$quiz->showblocks=0;
		
		$feedback = new QuizFeedback("1", "", "1", "0.00000", $quiz->grade+1);
		$quiz->feedbacks[]=$feedback;
		

		//Find the questions
		$this->addQuestionsToQuiz($quiz);
		
		return $quiz;
	}
	
	
	/**
	 * @param ActivityQuiz $quiz
	 */
	public function addQuestionsToQuiz(&$quiz) {
		
		//Find the root section
		//echo "------------------------------------<br/>";
		
		$request = "SELECT ASSMT_SECTION_ELEMENT.ID AS SECTION_ID,ASSMT_QUESTION_SET.ID AS SET_ID 
					FROM ASSMT_SECTION_ELEMENT
						LEFT JOIN ASSMT_QUESTION_SET ON ASSMT_QUESTION_SET.ID = ASSMT_SECTION_ELEMENT.ID 
					WHERE SECTION_PARENT_ID=(SELECT ID FROM ASSMT_SECTION_ELEMENT WHERE SECTION_PARENT_ID IS NULL AND ASSESSMENT_ID='".$quiz->quizId."')
								AND PREVIOUS_ELEMENT_ID IS NULL";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$count = 0;
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
			if(empty($row['SET_ID'])){
				$this->addSimpleQuestionToQuiz($count++, $quiz, $row['SECTION_ID']);
			}else {
				$this->addQuestionSetToQuiz($count++, $quiz, $row['SECTION_ID']);
			}
			
			//echo 'ROW '.$row['ID'] . ' - '. $row['PREVIOUS_ELEMENT_ID'].'<br/>';
			
			$request = "SELECT ASSMT_SECTION_ELEMENT.ID AS SECTION_ID,ASSMT_QUESTION_SET.ID AS SET_ID 
						FROM ASSMT_SECTION_ELEMENT
							LEFT JOIN ASSMT_QUESTION_SET ON ASSMT_QUESTION_SET.ID = ASSMT_SECTION_ELEMENT.ID 
						WHERE SECTION_PARENT_ID=(SELECT ID FROM ASSMT_SECTION_ELEMENT WHERE SECTION_PARENT_ID IS NULL AND ASSESSMENT_ID='".$quiz->quizId."')
								AND PREVIOUS_ELEMENT_ID='".$row['SECTION_ID']."'";
			$stid = oci_parse($this->connection,$request);
			oci_execute($stid);
		}
		
		//echo "------------------------------------<br/>";

	}
	
	/**
	 * @param int $count
	 * @param ActivityQuiz $quiz
	 * @param unknown $sectionId
	 */
	public function addSimpleQuestionToQuiz($count, &$quiz,$sectionId) {
		$request = "SELECT * FROM ASSMT_QUESTION_LINK WHERE ID='".$sectionId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
		
		$quiz->questions[]=$row['QUESTION_ID'];
		
		$questionInstance = new QuestionInstance($count, $row['QUESTION_ID'], str_replace(',', '.', $row['POINTS']));
		$quiz->questionInstances[]=$questionInstance;
		
		
	}
	
	/**
	 * @param int $count
	 * @param ActivityQuiz $quiz
	 * @param unknown $sectionId
	 */
	public function addQuestionSetToQuiz($count, &$quiz,$sectionId) {
		global $USER;
		
		$request = "SELECT * FROM ASSMT_QUESTION_SET WHERE ID='".$sectionId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	
		$grade = str_replace(',', '.', $row['POINTSPERQUESTION']);
		$numberOfQuestions = $row['NUMBEROFQUESTIONS'];

		
		//Create a new category
		$questionCategory = new QuestionCategory();
		
		$questionCategory->id = $row['ID'];
		$questionCategory->name = "Question Set ".$count;
		$questionCategory->contextid = $quiz->contextid;
		$questionCategory->contextlevel = 70; //CONTEXT_MODULE
		$questionCategory->contextinstanceid = $quiz->quizId;
		$questionCategory->info = ""; //NO DESCRIPTION IN WEBCT
		$questionCategory->infoformat = 1;
		$questionCategory->stamp = time(); // localhost+140131155733+469Glc
		$questionCategory->parent = 0;
		$questionCategory->sortorder = 999;
			
				
		//Add a copy of all select course			
		
		//For each numberOfQuestions we have to create a random question
		for ($i=0;$i<$numberOfQuestions;$i++){
		
			$randomQuestion = new RandomQuestion();
			$randomQuestion->id=$questionCategory->id+$i;
			$randomQuestion->parent=$questionCategory->id+$i;
			$randomQuestion->questiontextformat=0;
			$randomQuestion->generalfeedback="";
			$randomQuestion->generalfeedbackformat=0;
			$randomQuestion->defaultmark=$grade;
			$randomQuestion->penalty="0.0000000";//<penalty>0.0000000</penalty>
			$randomQuestion->length=1;//<length>1</length>
			$randomQuestion->stamp=time();//<stamp>localhost+140214135230+whFpdl</stamp>
			$randomQuestion->version=time();//<version>localhost+140214135230+dgK6HA</version>
			$randomQuestion->hidden=0;//<hidden>0</hidden>
			$randomQuestion->timecreated=time();//<timecreated>1392385950</timecreated>
			$randomQuestion->timemodified=time();//<timemodified>1392385950</timemodified>
			$randomQuestion->createdby=$USER->id;//<createdby>2</createdby>
			$randomQuestion->modifiedby=$USER->id;//<modifiedby>2</modifiedby>

			$questionCategory->addQuestion($randomQuestion);
			
			
			//Add the random to the quiz
			$quiz->questions[]=$randomQuestion->id;
			
			$questionInstance = new QuestionInstance($count, $randomQuestion->id, $grade);
			$quiz->questionInstances[]=$questionInstance;			
		}
		
		//Add all the questions for this category
		$request = "SELECT * FROM ASSMT_SECTION_ELEMENT
						WHERE SECTION_PARENT_ID='".$sectionId."' AND PREVIOUS_ELEMENT_ID IS NULL";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){

			//echo 'ROW '.$row['ID'] . ' - '. $row['PREVIOUS_ELEMENT_ID'].'<br/>';

			$request1 = "SELECT * FROM ASSMT_QUESTION_LINK WHERE ID='".$row['ID']."'";
			$stid1 = oci_parse($this->connection,$request1);
			oci_execute($stid1);
			$row1 = oci_fetch_array($stid1, OCI_ASSOC+OCI_RETURN_NULLS);
						
			//echo 'QUESTION ID = '.$row1['QUESTION_ID']."<br/>";
			$this->addCloneQuestionToCategory($this->allQuestions[(string)$row1['QUESTION_ID']], $questionCategory);				
				
			$request = "SELECT * FROM ASSMT_SECTION_ELEMENT
						WHERE SECTION_PARENT_ID='".$sectionId."' AND PREVIOUS_ELEMENT_ID='".$row['ID']."'";
			$stid = oci_parse($this->connection,$request);
			oci_execute($stid);
		}
					
		$this->questions->question_categories[] = $questionCategory;
		$this->course->inforef->questioncategoryids[]=$questionCategory->id;
	
	}
	
	
	/**
	 * @param Question $question
	 * @param QuestionCategory $questionCategory
	 * @return Question
	 */
	public function addCloneQuestionToCategory($question,&$questionCategory){
		
		$cloneQuestion = clone $question;
		
		$cloneQuestion->id= $this->getNextId();
		
		//Also Clone the files
		foreach ($this->files->files as $file){
			if ($file->itemid == $question->id) {
				$cloneFile = clone $file;
				$cloneFile->id = $this->getNextId();
				$cloneFile->itemid = $cloneQuestion->id;
				$cloneFile->contextid=$questionCategory->contextid;
				
				$this->files->files[]=$cloneFile;
			}
		}
		
		if ($cloneQuestion instanceof MultiAnswerQuestion) {
			$cloneQuestion->multiAnswer->question=$cloneQuestion->id;

			$sequence = array();
			
			foreach ($cloneQuestion->multiAnswer->sequence as $questionId){
				
				$originalSubQuestion = $this->allQuestions[(string)$questionId];
				$subQuestion = clone $originalSubQuestion;
				$subQuestion->id = $this->getNextId();
				$subQuestion->parent = $cloneQuestion->id;
				
				foreach ($this->files->files as $file){
					if ($file->itemid == $originalSubQuestion->id) {
						$cloneFile = clone $file;
						$cloneFile->id = $this->getNextId();
						$cloneFile->itemid = $subQuestion->id;
						$cloneFile->contextid=$questionCategory->contextid;
						
						$this->files->files[]=$cloneFile;
					}
				}
				
				$questionCategory->addQuestion($subQuestion);
				
				$sequence[]= $subQuestion->id;
			}			
			
			$cloneQuestion->multiAnswer->sequence = $sequence;
			
		}
		
		$questionCategory->addQuestion($cloneQuestion);
		
		return $cloneQuestion;
	}
	
	
	
	/***************************************************************************************************************
	 * ASSIGNMENT
	*/
	
	public function retrieveAssignments(){
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='PROJECT_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
			
			$assignmentId = $row['ORIGINAL_CONTENT_ID'];
			$this->addAssignment($assignmentId);
		}
	}
	
	
	
	/**
	 * Add a Assignment
	 */
	public function addAssignment($assignmentId){	
	
		global $USER;
		$sectionId = $this->fixedSections[GlobalModel::SECTION_ASSIGNMENTS];
		
		//Glossary
		$assignmentModel = new AssignmentModel();
		$assignmentModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$assignmentModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$assignmentModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$assignmentModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
	
	
		$assignmentModel->module = $this->createModule($assignmentId,"assign","2013110500",$sectionId);
	
		$assignmentModel->assignment = $this->createAssignment($assignmentId, $assignmentModel->module);
	
	
		//Grade
		$gradeBook = new ActivityGradeBook();
	
		$gradeItem = new GradeItem();
		$gradeItem->id=$this->getNextId();
		$gradeItem->categoryid = $this->gradebook->grade_categories[0]->id;
		$gradeItem->itemname =$assignmentModel->assignment->name;
		$gradeItem->itemtype ="mod";
		$gradeItem->itemmodule ="assign";
		$gradeItem->iteminstance = $assignmentModel->assignment->id;
		$gradeItem->itemnumber =0 ;//<itemnumber>$@NULL@$</itemnumber>
		$gradeItem->iteminfo ="$@NULL@$";//<iteminfo>$@NULL@$</iteminfo>
		$gradeItem->idnumber ="$@NULL@$";//<idnumber>$@NULL@$</idnumber>
		$gradeItem->calculation ="$@NULL@$";//<calculation>$@NULL@$</calculation>

		if($assignmentModel->assignment->grade<=0){
			$gradeItem->gradetype =3 ;//<gradetype>1</gradetype>
			$gradeItem->grademax =20;//<grademax>100.00000</grademax>
		}else {
			$gradeItem->gradetype =1 ;//<gradetype>1</gradetype>
			$gradeItem->grademax =$assignmentModel->assignment->grade;//<grademax>100.00000</grademax>
		}
		$gradeItem->grademin ="0.00000" ;//<grademin>0.00000</grademin>
		$gradeItem->scaleid = "$@NULL@$";//scaleid>$@NULL@$</scaleid>
		$gradeItem->outcomeid = "$@NULL@$";//<outcomeid>$@NULL@$</outcomeid>
		$gradeItem->gradepass = "0.00000";//<gradepass>0.00000</gradepass>
		$gradeItem->multfactor ="1.00000" ;//<multfactor>1.00000</multfactor>
		$gradeItem->plusfactor = "0.00000";//<plusfactor>0.00000</plusfactor>
		$gradeItem->aggregationcoef = "0.00000";//<aggregationcoef>0.00000</aggregationcoef>
		$gradeItem->sortorder = 1;//<sortorder>1</sortorder>
		$gradeItem->display = 0;//<display>0</display>
		$gradeItem->decimals = "$@NULL@$";//<decimals>$@NULL@$</decimals>
		$gradeItem->hidden = 0;//<hidden>0</hidden>
		$gradeItem->locked = 0;//<locked>0</locked>
		$gradeItem->locktime= 0;//<locktime>0</locktime>
		$gradeItem->needsupdate = 0;//<needsupdate>1</needsupdate>
		$gradeItem->timecreated = time();
		$gradeItem->timemodified = time() ;
	
		$gradeBook->grade_items[]= $gradeItem;
		$assignmentModel->grades = $gradeBook;
	

		//Grading...
		$grading = new Grading();
		$area = new Area($this->getNextId(), "submissions", "$@NULL@$");
		$grading->areas[]=$area;
		$assignmentModel->grading = $grading;
		
	
		//Event associ�
		$event = new Event();
		$event->id=$this->getNextId();//
		$event->name=$assignmentModel->assignment->name;
		$event->description=$assignmentModel->assignment->intro;//<description>&lt;div class="no-overflow"&gt;&lt;p&gt;Voici ma description de mon �valuation...&lt;/p&gt;&lt;/div&gt;</description>
		$event->format=1;//<format>1</format>
		$event->courseid=$this->course->course->id;//<courseid>6</courseid>
		$event->groupid=0;//<groupid>0</groupid>
		$event->userid=$USER->id;//<userid>2</userid>
		$event->repeatid=0;//<repeatid>0</repeatid>
		$event->modulename="assign";
		$event->instance=$assignmentModel->assignment->id;//<instance>40</instance>
		$event->eventtype="due";//<eventtype>open</eventtype>
		$event->timestart=$assignmentModel->assignment->duedate;//<timestart>-152423940</timestart>
		if($assignmentModel->assignment->duedate==0 && $assignmentModel->assignment->cutoffdate==0){
			$event->timeduration=0;
		}else if($assignmentModel->assignment->duedate==0){
			$event->timeduration=$assignmentModel->assignment->cutoffdate  - time();
		}else if($assignmentModel->assignment->cutoffdate==0){
			$event->timeduration=$assignmentModel->assignment->duedate - time();
		}else {
			$event->timeduration=$assignmentModel->assignment->cutoffdate-$assignmentModel->assignment->duedate;//<timeduration>0</timeduration>
		}
		$event->visible=0;//<visible>0</visible>
		$event->uuid="";//<uuid></uuid>
		$event->sequence=1;//<sequence>1</sequence>
		$event->timemodified=time();//<timemodified>1392650251</timemodified>
	
		$events = new Events();
		$events->events[] = $event;
		$assignmentModel->calendar = $events;
	
	
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$assignmentModel->module->id;
		$activity->sectionid=$sectionId;
		$activity->modulename=$assignmentModel->module->modulename;
		$activity->title=$assignmentModel->assignment->name;
		$activity->directory="activities/assign_".$assignmentModel->assignment->assignmentId;
	
		$this->moodle_backup->contents->activities[] = $activity;
	
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","assign_".$assignmentModel->assignment->assignmentId,"assign_".$assignmentModel->assignment->assignmentId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","assign_".$assignmentModel->assignment->assignmentId,"assign_".$assignmentModel->assignment->assignmentId."_userinfo",1);
	
		$inforRef = new InfoRef();
		$inforRef->userids[]=$USER->id;
		$inforRef->fileids=$assignmentModel->assignment->filesIds;
	
		$inforRef->gradeItemids[] = $gradeItem->id;
	
		$assignmentModel->inforef = $inforRef;
	
		$this->activities[] = $assignmentModel;
	
		$this->sections[$sectionId]->section->sequence[]= $assignmentModel->assignment->assignmentId;
		
		//rapport
		$this->rapportMigration->add(RapportMigration::TYPE_ASSIGNMENT,RapportMigration::ASSIGNMENT_RETRIEVED,
				$assignmentModel->assignment->id, $assignmentModel->assignment->name);
		$this->rapportMigration->assignmentsCount++;
	}
	
	
	public function createAssignment($assignmentId, $module){
	
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE ORIGINAL_CONTENT_ID='".$assignmentId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	
		$cmsContentEntryId= $row['ID'];
		
		$assignment = new ActivityAssignment();
		$assignment->id = $assignmentId;
		$assignment->moduleid =$module->id;
		$assignment->modulename =$module->modulename;
		$assignment->contextid=$this->getNextId();
		$assignment->assignmentId = $assignmentId;
		
		$assignment->name =$row['NAME'];
	
		$description = "";
		if(!empty($row['DESCRIPTION'])){
			$description =$row['DESCRIPTION']->load();			
		}
	
		$request = "SELECT AGN_ASSIGNMENT.TAKEBACKABLE_FLAG, AGN_ASSIGNMENT.INSTRUCTIONS, AGN_ASSIGNMENT.SENDEMAILONSUBMISSION_FLAG,AGN_ASSIGNMENT.DUEDATE,AGN_ASSIGNMENT.LEEWAYDATE,AGN_ASSIGNMENT.COLLABORATIVE,
							SIMPLE_FILE.NAME,SIMPLE_FILE.FILESIZE,
							CMS_FILE_CONTENT.CONTENT,CMS_MIMETYPE.MIMETYPE,
							SECTION_COLUMN.LABEL,SECTION_COLUMN.MAX_VALUE
					FROM AGN_ASSIGNMENT
				        LEFT JOIN SIMPLE_FILE ON SIMPLE_FILE.GROUP_ID=AGN_ASSIGNMENT.SIMPLE_FILE_GROUP_ID
				        LEFT JOIN CMS_FILE_CONTENT ON CMS_FILE_CONTENT.ID=SIMPLE_FILE.FILE_CONTENT_ID
						LEFT JOIN CMS_MIMETYPE ON CMS_MIMETYPE.ID=CMS_FILE_CONTENT.MIMETYPE_ID
						LEFT JOIN SECTION_COLUMN ON SECTION_COLUMN.CONTENT_ENTRY_ID='".$cmsContentEntryId."'        
				    WHERE AGN_ASSIGNMENT.ID='".$assignmentId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);

		if(!empty($row['INSTRUCTIONS'])){
			$description .='<br><b> Instructions : </b><br>'.$row['INSTRUCTIONS']->load();
		}
		
		//TODO SEARCH LINKS
		$convertedDescription = $this->convertTextAndCreateAssociedFiles($description, 9, $assignment);
				
		if(!empty($row['NAME'])){
			$convertedDescription .= "<br/>".utf8_encode("Fichier attach� :") ."<a href=\"@@PLUGINFILE@@/".$row['NAME']."\">".$row['NAME']."</a>";
			$this->addSimpleFile(1, $assignment, $row['NAME'], $row['FILESIZE'], $row['CONTENT']->load(), $row['MIMETYPE']);
		}		
		
		$assignment->intro = $convertedDescription;		

		$assignment->introformat =1;
		$assignment->alwaysshowdescription=0;
		
		$assignment->submissiondrafts=$row['TAKEBACKABLE_FLAG'];//<submissiondrafts>1</submissiondrafts>
		
		$assignment->sendnotifications=$row['SENDEMAILONSUBMISSION_FLAG'];//<sendnotifications>1</sendnotifications>					
		$assignment->sendlatenotifications=0;//<sendlatenotifications>0</sendlatenotifications>
		$assignment->duedate=substr($row['DUEDATE'],0,-3);//<duedate>1393711200</duedate>
		$assignment->cutoffdate=substr($row['LEEWAYDATE'],0,-3);//<cutoffdate>1394488800</cutoffdate>
		$assignment->allowsubmissionsfromdate=0;//<allowsubmissionsfromdate>0</allowsubmissionsfromdate>
		
		if(empty($row['LABEL'])){
			$assignment->grade=0;
		}else {
			if(empty($row['MAX_VALUE'])){
				$assignment->grade=0;
				//error_log("ASSIGNMENT - EVALUATION ALPHANUMERIQUE - ".$assignment->name);
			}else {
				$assignment->grade=str_replace(",", ".", $row['MAX_VALUE']);
			}
		}
		$assignment->timemodified=time();//<timemodified>1392710910</timemodified>
		$assignment->completionsubmit=0;// <completionsubmit>0</completionsubmit>
		$assignment->requiresubmissionstatement=0;//<requiresubmissionstatement>0</requiresubmissionstatement>
		
		if($row['COLLABORATIVE']=='true'){
			$assignment->teamsubmission=1;//<teamsubmission>0</teamsubmission>
		}else {
			$assignment->teamsubmission=0;//<teamsubmission>0</teamsubmission>
		}
		$assignment->requireallteammemberssubmit=0;//<requireallteammemberssubmit>0</requireallteammemberssubmit>
		$assignment->teamsubmissiongroupingid=0;//<teamsubmissiongroupingid>0</teamsubmissiongroupingid>
		
		$assignment->blindmarking=0;//<blindmarking>0</blindmarking>
		$assignment->revealidentities=0;//<revealidentities>0</revealidentities>
		
		$assignment->attemptreopenmethod="none";//<attemptreopenmethod>none</attemptreopenmethod>
		$assignment->maxattempts=-1;//<maxattempts>-1</maxattempts>
		$assignment->markingworkflow=0;//<markingworkflow>0</markingworkflow>
		$assignment->markingallocation=0;//<markingallocation>0</markingallocation>
		
		$pluginConfig = new PluginConfig($this->getNextId(), "onlinetext", "assignsubmission", "enabled", 1);
		$assignment->plugin_configs[]=$pluginConfig;
		
		$pluginConfig = new PluginConfig($this->getNextId(), "file", "assignsubmission", "enabled", 1);		
		$assignment->plugin_configs[]=$pluginConfig;

		$pluginConfig = new PluginConfig($this->getNextId(), "file", "maxfilesubmissions", "enabled", 1);
		$assignment->plugin_configs[]=$pluginConfig;
		
		$pluginConfig = new PluginConfig($this->getNextId(), "file", "maxsubmissionsizebytes", "enabled", 0);
		$assignment->plugin_configs[]=$pluginConfig;

		$pluginConfig = new PluginConfig($this->getNextId(), "comments", "assignsubmission", "enabled", 1);
		$assignment->plugin_configs[]=$pluginConfig;
		
		$pluginConfig = new PluginConfig($this->getNextId(), "comments", "assignfeedback", "enabled", 1);
		$assignment->plugin_configs[]=$pluginConfig;
		
		$pluginConfig = new PluginConfig($this->getNextId(), "editpdf", "assignfeedback", "enabled", 0);
		$assignment->plugin_configs[]=$pluginConfig;
		
		$pluginConfig = new PluginConfig($this->getNextId(), "offline", "assignfeedback", "enabled", 0);
		$assignment->plugin_configs[]=$pluginConfig;
		
		$pluginConfig = new PluginConfig($this->getNextId(), "file", "assignfeedback", "enabled", 0);
		$assignment->plugin_configs[]=$pluginConfig;

		return $assignment;
	}

	/**
	 * @param unknown $mode
	 * MODE 1 = Assignment file - attachment
	 * @param unknown $item
	 * @param unknown $fileName
	 * @param unknown $fileSize
	 * @param unknown $fileContent
	 * @param unknown $fileMimeType
	 * @param string $parent
	 */
	public function addSimpleFile($mode, &$item, $fileName, $fileSize, $fileContent, $fileMimeType){
	
		$fileArea = "";
		$component ="";
		$itemId = 0;
		$contextId=0;
		switch ($mode){
			case 1 :
				$component = "mod_assign";
				$fileArea = "intro";
				$itemId=0;
				$contextId=$item->contextid;
				break;
		}
	
		$repository = new FileBackup();
		$repository->id=$this->getNextId();
		$repository->contenthash="";// 		<contenthash>da39a3ee5e6b4b0d3255bfef95601890afd80709</contenthash>
		$repository->contextid=$contextId;// 		<contextid>54</contextid> // ACTIVITY -- ICI GLOSSARY CONTEXT
		$repository->component=$component;// 		<component>mod_glossary</component>
		$repository->filearea=$fileArea;// 		<filearea>attachment</filearea>
		$repository->itemid=$itemId;// 		<itemid>1</itemid> //GLOSSARY ID
		$repository->filepath="/";// 		<filepath>/</filepath>
		$repository->filename=".";// 		<filename>.</filename>
		$repository->userid=$this->users->users[0]->id;// 		<userid>2</userid>
		$repository->filesize=0;// 		<filesize>0</filesize>
		$repository->mimetype="$@NULL@$";// 		<mimetype>document/unknown</mimetype>
		$repository->status=0;// 		<status>0</status>
		$repository->timecreated=time();// 		<timecreated>1390818824</timecreated>
		$repository->timemodified=time();// 		<timemodified>1390818869</timemodified>
		$repository->source="$@NULL@$";// 		<source>$@NULL@$</source>
		$repository->author="$@NULL@$";// 		<author>$@NULL@$</author>
		$repository->license="$@NULL@$";// 		<license>$@NULL@$</license>
		$repository->sortorder=0;// 		<sortorder>0</sortorder>
		$repository->repositorytype="$@NULL@$";// 		<repositorytype>$@NULL@$</repositorytype>
		$repository->repositoryid="$@NULL@$";// 		<repositoryid>$@NULL@$</repositoryid>
		$repository->reference="$@NULL@$";// 		<reference>$@NULL@$</reference>
			
	
		$file = new FileBackup();
		$file->id=$this->getNextId();
		$file->contextid=$contextId;// 		<contextid>54</contextid>
		$file->component=$component;// 		<component>mod_glossary</component>
		$file->filearea=$fileArea;// 		<filearea>attachment</filearea>
		$file->itemid=$itemId;// 		<itemid>1</itemid>
		$file->filepath="/";// 		<filepath>/</filepath>
		$file->filename=$fileName;// 		<filename>.</filename>
		$file->userid=$this->users->users[0]->id;// 		<userid>2</userid>
		$file->filesize=$fileSize;// 		<filesize>0</filesize>
		$file->author=$this->users->users[0]->firstname." ".$this->users->users[0]->lastname;// 		<author>$@NULL@$</author>
		$file->license="allrightsreserved";// 		<license>$@NULL@$</license>
		$file->sortorder=0;// 		<sortorder>0</sortorder>
		$file->repositorytype="$@NULL@$";// 		<repositorytype>$@NULL@$</repositorytype>
		$file->repositoryid="$@NULL@$";// 		<repositoryid>$@NULL@$</repositoryid>
		$file->reference="$@NULL@$";// 		<reference>$@NULL@$</reference>
		$file->status=0;// 		<status>0</status>
		$file->timecreated=time();// 		<timecreated>1390818824</timecreated>
		$file->timemodified=time();// 		<timemodified>1390818869</timemodified>
		$file->source=$fileName;// 		<source>$@NULL@$</source>
		//$file->content = $fileContent;
	
		$file->contenthash=md5($fileContent);// 		<contenthash>da39a3ee5e6b4b0d3255bfef95601890afd80709</contenthash>
	
		$file->mimetype=$fileMimeType;// 		<mimetype>document/unknown</mimetype>
	

		//Create the real file
		$file->createFile($fileContent, $this->repository);
		
		switch ($mode){
			case 1 :
				$item->filesIds[]=$repository->id;
				$item->filesIds[]=$file->id;
				break;
				
		}
		
		//REFERENCE IN THE COURSE FILES
		$this->files->files[]=$repository;
		$this->files->files[]=$file;
	
	}
	
	
	/***************************************************************************************************************
	 * FOLDERS
	*/
	
	public function retrieveFolders(){
	
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='Template' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
	
			$folderId = $row['ID'];
			$this->addFolder($folderId);
	
		}
	}
	
	
	/**
	 * Add a Folder and all its content
	 */
	public function addFolder($folderId){
	
		global $USER;
		$sectionId = $this->fixedSections[GlobalModel::SECTION_GENERAL];
	
		//Glossary
		$folderModel = new FolderModel();
		$folderModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->grades = new ActivityGradeBook();
		$folderModel->calendar = new Events();
	
		$folderModel->module = $this->createModule($folderId,"folder","2013110500",$sectionId);
	
		$folderModel->folder = $this->createActivityFolder($folderId, $folderModel->module);
	
		
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$folderModel->module->id;
		$activity->sectionid=$this->sections[$sectionId]->section->id;
		$activity->modulename=$folderModel->module->modulename;
		$activity->title=$folderModel->folder->name;
		$activity->directory="activities/folder_".$folderModel->folder->folderId;
	
		$this->moodle_backup->contents->activities[] = $activity;
	
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","folder_".$folderModel->folder->folderId,"folder_".$folderModel->folder->folderId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","folder_".$folderModel->folder->folderId,"folder_".$folderModel->folder->folderId."_userinfo",1);
	
		$inforRef = new InfoRef();
		$inforRef->userids[]=$USER->id;
		$inforRef->fileids=$folderModel->folder->filesIds;
	
		$folderModel->inforef = $inforRef;
	
		$this->activities[] = $folderModel;
		
	
		$this->sections[$sectionId]->section->sequence[]= $folderModel->folder->folderId;
	}
	
	
	/**
	 * @param unknown $folderId
	 * @param Module $module
	 * @return ActivityFolder
	 */
	public function createActivityFolder($folderId, $module){
				
		$folder = new ActivityFolder();
		$folder->id = $folderId;
		$folder->moduleid =$module->id;
		$folder->modulename =$module->modulename;
		$folder->contextid=$this->getNextId();
		$folder->folderId = $folderId;
		
		//Ici on choisit le nom de notre folder
		$folder->name =utf8_encode("Dossiers & fichiers r�cup�r�s");
		$folder->intro=utf8_encode("Ensemble de tous les dossiers et fichiers r�cup�r�s de WEBCT.");
		$folder->introformat=1;
		$folder->revision=0;
		$folder->timemodified=time();
		$folder->display=0;
		$folder->showexpanded=0;

		
		//Recherche et r�cup�re tous les fichiers et dossiers de WebCT
		$this->addFolderFiles($folderId, $folder->contextid, "/", $folder->filesIds);
		
		return $folder;
	}
	
	public function addFolderFiles($folderId, $contextId, $path, &$filesIds){
		
		$component = "mod_folder";
		$fileArea = "content";
		$itemId = 0;
		
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE PARENT_ID='".$folderId."' AND CE_TYPE_NAME IN ('ContentFile','Folder','TEMPLATE_PUBLIC_AREA') AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
		
			if($row['CE_TYPE_NAME']=="ContentFile"){
				$file = $this->addCMSSimpleFile($row["ORIGINAL_CONTENT_ID"], $contextId, $component, $fileArea, $itemId, $path);
				$filesIds[] = $file->id;
				
				$this->rapportMigration->add(RapportMigration::TYPE_FILES,RapportMigration::FILE_MIGRATED,
						$file->id, $file->filename);
				$this->rapportMigration->filesCount++;
				
			}else {
				$newPath = $path.$row['NAME']."/";
				$repository = $this->addCMSRepository($contextId, $component, $fileArea, $itemId, $newPath);
				$filesIds[] = $repository->id;	

				$this->addFolderFiles($row['ID'], $contextId, $newPath, $filesIds);
			}
		
		}		
		
	}
	
	/***************************************************************************************************************
	 * WEB LINKS
	*/
	
	public function retrieveWebLinks(){
	
		
		global $USER;
		$sectionId = $this->fixedSections[GlobalModel::SECTION_GENERAL];
		
		$bookId = $this->getNextId(); 
		
		$bookModel = new BookModel();
		$bookModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$bookModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$bookModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$bookModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
		$bookModel->grades = new ActivityGradeBook();
		$bookModel->calendar = new Events();
		
		$bookModel->module = $this->createModule($bookId,"book","2013110500",$sectionId);
		
		$bookModel->book = $this->createWebLinksActivityBook($bookId, $bookModel->module);
		
		//If no links, no book..
		if(empty($bookModel->book->chapters)){
			return;
		}
		
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$bookModel->module->id;
		$activity->sectionid=$this->sections[$sectionId]->section->id;
		$activity->modulename=$bookModel->module->modulename;
		$activity->title=$bookModel->book->name;
		$activity->directory="activities/book_".$bookModel->book->bookId;
		
		$this->moodle_backup->contents->activities[] = $activity;
		
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","book_".$bookModel->book->bookId,"book_".$bookModel->book->bookId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","book_".$bookModel->book->bookId,"book_".$bookModel->book->bookId."_userinfo",1);
		
		$inforRef = new InfoRef();
		$inforRef->userids[]=$USER->id;
		//$inforRef->fileids=$bookModel->folder->filesIds;
		
		$bookModel->inforef = $inforRef;
		
		$this->activities[] = $bookModel;
				
		$this->sections[$sectionId]->section->sequence[]= $bookModel->book->bookId;
		
		$rem = count($bookModel->book->chapters);
		$this->rapportMigration->add(RapportMigration::TYPE_WEB_LINK,RapportMigration::WEB_LINK_CATEGORY_COUNT,
				$bookModel->book->bookId, $bookModel->book->name,
				$rem);
	}
	
	
	
	
	/**
	 * @param unknown $bookId
	 * @param Module $module
	 * 
	 * @return ActivityBook
	 * 
	 */
	public function createWebLinksActivityBook($bookId, $module){
		$book  = new ActivityBook();
		$book->id = $bookId;
		$book->moduleid =$module->id;
		$book->modulename =$module->modulename;
		$book->contextid=$this->getNextId();
		$book->bookId = $bookId;
		
		$book->name= utf8_encode("Liens WEB");
		$book->intro = utf8_encode("Liens web classifi�s");
		
		$book->introformat=1;
		$book->numbering=0;//1 = number
		$book->customtitles=0;
		$book->timecreated=time();
		$book->timemodified=time();

		
// 		$request = "SELECT COUNT(*)
// 					FROM CMS_CONTENT_ENTRY
// 						LEFT JOIN CO_INVENTORY_ORDER ON CMS_CONTENT_ENTRY.ORIGINAL_CONTENT_ID=CO_INVENTORY_ORDER.OBJECT_ID
// 					WHERE CMS_CONTENT_ENTRY.CE_TYPE_NAME='WEBLINKSCATEGORY' AND CMS_CONTENT_ENTRY.DELETED_FLAG=0 AND CMS_CONTENT_ENTRY.DELIVERY_CONTEXT_ID='".$this->deliveryContextId."' ORDER BY CO_INVENTORY_ORDER.INVENTORY_ORDER";
// 		$stid = oci_parse($this->connection,$request);
// 		oci_execute($stid);
// 		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
// 		$categoryCount = $row["COUNT(*)"];
		
		$request = "SELECT CMS_CONTENT_ENTRY.ID, CMS_CONTENT_ENTRY.NAME, CMS_CONTENT_ENTRY.DESCRIPTION 
					FROM CMS_CONTENT_ENTRY 
						LEFT JOIN CO_INVENTORY_ORDER ON CMS_CONTENT_ENTRY.ORIGINAL_CONTENT_ID=CO_INVENTORY_ORDER.OBJECT_ID 
					WHERE CMS_CONTENT_ENTRY.CE_TYPE_NAME='WEBLINKSCATEGORY' AND CMS_CONTENT_ENTRY.DELETED_FLAG=0 AND CMS_CONTENT_ENTRY.DELIVERY_CONTEXT_ID='".$this->deliveryContextId."' ORDER BY CO_INVENTORY_ORDER.INVENTORY_ORDER";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		
		$count = 0;
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
			$count++;
			$webLinksCategoryId = $row['ID'];
			$chapter = new Chapter();
			$chapter->id=$this->getNextId();
			$chapter->pagenum = $count;
			$chapter->subchapter=0;
			$chapter->title = $row['NAME'];
			
			
			$content ="";
			$description = $row['DESCRIPTION'];
						
			if(empty($description)){
				$content ="";
			}else {
				$content =$description->load().'<br/><br/>';
			}
			
			$isDefaultCategory = false;
			if($chapter->title=="Default"){
				$chapter->title = utf8_encode("Cat�gorie par d�faut");
				$isDefaultCategory = true;
			}
				

			$request1 = "SELECT CMS_CONTENT_ENTRY.NAME, CMS_CONTENT_ENTRY.DESCRIPTION, CO_URL.LINK, CO_URL.OPENINNEWWINDOWFLAG 
						FROM CMS_CONTENT_ENTRY 
							LEFT JOIN CO_URL ON CMS_CONTENT_ENTRY.ORIGINAL_CONTENT_ID=CO_URL.ID 
						WHERE CE_TYPE_NAME='URL_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."' AND PARENT_ID='".$row['ID']."' AND CO_URL.LINK!='/importexport/alertObject.jsp?type=0'";
			$stid1 = oci_parse($this->connection,$request1);
			oci_execute($stid1);
			$content .='<table border="0" cellspacing="0" cellpadding="0" style="width: 100%;table-layout: fixed;word-wrap:break-word;"><tbody>';
			
			//Retrieve All the links..
			$hasLink = false;
			while ($row1 = oci_fetch_array($stid1, OCI_ASSOC+OCI_RETURN_NULLS)){
				$hasLink = true;
				
				$urlDescription ="";
				if(!empty($row1['DESCRIPTION'])){
					$urlDescription =$row1['DESCRIPTION']->load();
				}
				
				$target="_self";
				if($row1['OPENINNEWWINDOWFLAG']==1){
					$target="_blank";
				}
				
				$urlRow = "<tr>"
					."<td style=\"width:50%; border:1px solid #CCC\" >"
						."<label><b><a target='".$target."' href='".$row1['LINK']."'>".$row1['NAME']."</a></b></label><br/>"
						."<div>".$urlDescription."</div>"
					."</td>"
					."<td style=\"width:50%; border:1px solid #CCC\" >".$row1['LINK']."</td>"
				."</tr>";
				$content .= $urlRow;
				
				$this->rapportMigration->webLinksCount++;						
			}
			
			$content .="</tbody></table>";
			
			$chapter->content = $content;
			$chapter->contentformat=1;
			$chapter->hidden=0;
			$chapter->timemodified=time();
			$chapter->importsrc="";
						
			if(!($hasLink==false && $isDefaultCategory==true)){
				$book->chapters[]=$chapter;
			}
			
		}
		
		return $book;
	}
	
	/***************************************************************************************************************
	 * LEARNING MODULE
	*/
	public function retrieveLearningModules(){
		
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='TOC_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
			
			//TEST si le module d'apprentissage peut �tre cr�er sous forme de r�pertoire de fichiers
			
			$request = "SELECT COUNT(*) FROM CMS_LINK 
  							WHERE LEFTOBJECT_ID IN (SELECT ID FROM CMS_CONTENT_ENTRY WHERE CMS_CONTENT_ENTRY.PARENT_ID='".$row['ID']."') AND CMS_LINK.NAME IS NOT NULL AND CMS_LINK.LINK_TYPE_ID='30003'";
            $stid1 = oci_parse($this->connection,$request);
            oci_execute($stid1);
            $row1 = oci_fetch_array($stid1, OCI_ASSOC+OCI_RETURN_NULLS);
            $countExternalTotal = $row1['COUNT(*)'];
            
            //R�cup�re le nombre total de liens
            $request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row['ID']."' AND LINK_TYPE_ID='30002')";
            $stid1 = oci_parse($this->connection,$request);
            oci_execute($stid1);
            $row1 = oci_fetch_array($stid1, OCI_ASSOC+OCI_RETURN_NULLS);
            $totalLinks=$row1['COUNT(*)'];
			
			//R�cup�re le total de pages (SANS LES PAGES HTML CREES DANS LE MODULE)
            $request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
						WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row['ID']."' AND LINK_TYPE_ID='30002')
							  AND ((CMS_CONTENT_ENTRY.CE_TYPE_NAME='PAGE_TYPE' AND CMS_CONTENT_ENTRY.FILE_CONTENT_ID IS NULL) OR CMS_CONTENT_ENTRY.CE_TYPE_NAME='HEADING_TYPE')";
            $stid1 = oci_parse($this->connection,$request);
            oci_execute($stid1);
            $row1 = oci_fetch_array($stid1, OCI_ASSOC+OCI_RETURN_NULLS);
            $totalPageAndLinks=$row1['COUNT(*)'];
            
            $learningModuleDescription ="";
            if(!empty($row['DESCRIPTION'])){
            	$learningModuleDescription =$row['DESCRIPTION']->load();
            }
            
            if($totalLinks==$totalPageAndLinks && $countExternalTotal==0){
            	
            	$rem = "Module d'apprentissage r�cup�r� sous forme de r�pertoire";
            	$this->rapportMigration->add(RapportMigration::TYPE_LEARN_MODULE,RapportMigration::LEARN_MODULE_AS_FOLDER,
            			$row['ID'], $row['NAME'],
            			$rem);
            	 
            	$this->addLearningModuleAsFolder($row['ID'],$row['NAME'],$learningModuleDescription);
            	$this->allLearningModules[$row['ORIGINAL_CONTENT_ID']]=WebCTModel::LEARNING_MODULE_AS_FOLDER;
            }else {
            	$rem = "Module d'apprentissage r�cup�r� sous forme de Book";
            	$this->rapportMigration->add(RapportMigration::TYPE_LEARN_MODULE,RapportMigration::LEARN_MODULE_AS_BOOK,
            			$row['ID'], $row['NAME'],
            			$rem);
            	 
            	if($countExternalTotal>0){
            		//Ici on teste et on �crit dans le rapport s'il y a des actions links
            		$request = "SELECT ID, NAME, CE_TYPE_NAME FROM CMS_CONTENT_ENTRY
							WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK
	                						WHERE LEFTOBJECT_ID IN (SELECT ID FROM CO_ACTIONMENU WHERE CO_ACTIONMENU.TOC_ID='".$row['ID']."') AND CMS_LINK.NAME IS NOT NULL AND CMS_LINK.LINK_TYPE_ID='30003')";
	            	$stid1 = oci_parse($this->connection,$request);
	            	oci_execute($stid1);
	            	while($row1 = oci_fetch_assoc($stid1)){
	            		
	            		$rem = "L'�l�ment \"".$row['NAME']."\" poss�de un lien d'action vers ".$row1['NAME']." (Type =".$row1['CE_TYPE_NAME'].")";
	            		$this->rapportMigration->add(RapportMigration::TYPE_LEARN_MODULE,RapportMigration::LEARN_MODULE_WITH_ACTION_LINK,
	            				$row['ID'], $row['NAME'],
	            				$rem);
	            		
	            	}
            	}
            	$this->addLearningModuleAsBook($row['ID'],$row['NAME'],$learningModuleDescription);
            	$this->allLearningModules[$row['ORIGINAL_CONTENT_ID']]=WebCTModel::LEARNING_MODULE_AS_BOOK;
                        	 
            }
            $this->rapportMigration->learningModulesCount++;
		}
		
		
	}
	
	
	/**
	 * Add a learning module as a Folder (files)
	 */
	public function addLearningModuleAsFolder($learningModuleId,$name,$description){
	
		global $USER;
	
		$sectionId = $this->fixedSections[GlobalModel::SECTION_LEARNING_MODULES];
		//Glossary
		$folderModel = new FolderModel();
		$folderModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->grades = new ActivityGradeBook();
		$folderModel->calendar = new Events();
	
		$folderModel->module = $this->createModule($learningModuleId,"folder","2013110500",$sectionId);
		
		$folderModel->folder = $this->createLearningModuleFolder($learningModuleId, $name,$description,$folderModel->module);
	
	
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$folderModel->module->id;
		$activity->sectionid=$this->sections[$sectionId]->section->id;
		$activity->modulename=$folderModel->module->modulename;
		$activity->title=$folderModel->folder->name;
		$activity->directory="activities/folder_".$folderModel->folder->folderId;
	
		$this->moodle_backup->contents->activities[] = $activity;
	
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","folder_".$folderModel->folder->folderId,"folder_".$folderModel->folder->folderId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","folder_".$folderModel->folder->folderId,"folder_".$folderModel->folder->folderId."_userinfo",1);
	
		$inforRef = new InfoRef();
		$inforRef->userids[]=$USER->id;
		$inforRef->fileids=$folderModel->folder->filesIds;
	
		$folderModel->inforef = $inforRef;
	
		$this->activities[] = $folderModel;
	
		$this->sections[$sectionId]->section->sequence[]= $folderModel->folder->folderId;
		
	}
	
	
	/**
	 * @param unknown $folderId
	 * @param Module $module
	 * @return ActivityFolder
	 */
	public function createLearningModuleFolder($learningModuleId,$name,$description, $module){
	
		$folder = new ActivityFolder();
		$folder->id = $learningModuleId;
		$folder->moduleid =$module->id;
		$folder->modulename =$module->modulename;
		$folder->contextid=$this->getNextId();
		$folder->folderId = $learningModuleId;
	
		$folder->name =$name;
		$folder->intro=$description;
		$folder->introformat=1;
		$folder->revision=0;
		$folder->timemodified=time();
		$folder->display=0;
		$folder->showexpanded=0;
	
	
		//Recherche et r�cup�re tous les fichiers et dossiers de WebCT
		$this->addLearningModuleFolderFiles($learningModuleId, $folder->contextid, "/", $folder->filesIds);
	
		return $folder;
	}
	
	public function addLearningModuleFolderFiles($learningModuleId, $contextId, $path, &$filesIds){
	
		$component = "mod_folder";
		$fileArea = "content";
		$itemId = 0;
	
		$request = "SELECT CMS_CONTENT_ENTRY.ID,CMS_CONTENT_ENTRY.NAME AS CMS_NAME, CMS_LINK.NAME AS LINK_NAME,CMS_CONTENT_ENTRY.CE_TYPE_NAME, CMS_CONTENT_ENTRY.DESCRIPTION,CO_TOC_LINK.INDENTLEVEL 
					FROM CMS_LINK 
					  LEFT JOIN CO_TOC_LINK ON CO_TOC_LINK.ID=CMS_LINK.ID
					  LEFT JOIN CMS_CONTENT_ENTRY ON CMS_CONTENT_ENTRY.ID=CMS_LINK.RIGHTOBJECT_ID
					WHERE CMS_LINK.LEFTOBJECT_ID='".$learningModuleId."' AND CMS_LINK.LINK_TYPE_ID='30002' ORDER BY CMS_LINK.DISPLAY_ORDER";
		$stid = oci_parse($this->connection,$request);
		
		//PREMIERE ex�cution pour savoir qui sera un r�pertoire ou pas
		oci_execute($stid);
		$repertoriesStatus = array();
		$index = 0;
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
			if($row['INDENTLEVEL']>0){
				$repertoriesStatus[$index-1]=true;
			}
			$repertoriesStatus[$index]=false;
			
			$index++;
		}
		
		//var_dump($repertoriesStatus);
		
		$currentRepository=$path;
		oci_execute($stid);
		$index=0;
		$puceLevel = 0;
		$puce = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
		
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
			
			//TEST IF there are action links on the element
			$request = "SELECT COUNT(*) FROM CMS_LINK
  							WHERE LEFTOBJECT_ID IN (SELECT ID FROM CMS_CONTENT_ENTRY WHERE CMS_CONTENT_ENTRY.PARENT_ID='".$row['ID']."') AND CMS_LINK.NAME IS NOT NULL AND CMS_LINK.LINK_TYPE_ID='30003'";
			$stid1 = oci_parse($this->connection,$request);
			oci_execute($stid1);
			$row1 = oci_fetch_assoc($stid1);
			$countExternalTotal = $row1['COUNT(*)'];
			
			$identLevel = $row['INDENTLEVEL'];

			$puceLevel+=$identLevel;
			$puceLevel=$puceLevel<0?$puceLevel=0:$puceLevel;
			$puce[$puceLevel]=$puce[$puceLevel]+1;
			
			$puceName = "";
			for($i=0;$i<count($puce); $i++){
				if($i<=$puceLevel){
					$puceName.=$puce[$i].".";
				}else {
					$puce[$i]=0;
				}
			}
			
			$name = $row['LINK_NAME'];
			if(empty($name)){
				$name = $row['CMS_NAME'];
			}
			
			$name =$puceName." ".$name;
			
			
			
			if($identLevel<0){
				$pos = strlen($currentRepository);
				for($i=0;$i<=(-$identLevel);$i++){
					$pos = strrpos($currentRepository, '/');
					$currentRepository= substr($currentRepository, 0,$pos);
					//TODO Remarque??
			//		echo '$currentRepository ==' .$currentRepository.'    '.$pos.'<br/>';
				}
				
				$currentRepository.='/';
			}
				
			if($row['CE_TYPE_NAME']=='HEADING_TYPE'){
				$newPath = $currentRepository.$name."/";
				$repository = $this->addCMSRepository($contextId, $component, $fileArea, $itemId, $newPath);
				$filesIds[] = $repository->id;
				$repositories[]=$repository->filepath;
				
				if($repertoriesStatus[$index]){
					$currentRepository =$newPath;
				}
				
			}elseif($row['CE_TYPE_NAME']=='PAGE_TYPE'){
				if($repertoriesStatus[$index]){
					$currentRepository .= $name."/";
					$repository = $this->addCMSRepository($contextId, $component, $fileArea, $itemId, $currentRepository);
					$filesIds[] = $repository->id;
					$repositories[]=$repository->filepath;
					$currentLevel = count($repositories)-1;
				}
				
				$request = "SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row["ID"]."' AND LINK_TYPE_ID='30004'";
				
				$stid1 = oci_parse($this->connection,$request);
				oci_execute($stid1);
				$row1 = oci_fetch_assoc($stid1);
				
				$file = $this->addCMSSimpleFile($row1["RIGHTOBJECT_ID"], $contextId, $component, $fileArea, $itemId, $currentRepository,$name);
				$filesIds[] = $file->id;
			}
			
		//	echo 'CURRENT REPOSITORY = '.$currentRepository.'<br/>';
			
			$index++;	
		}
	
	}
	
	

	/** 
	 * Add a learning module as a Book
	 *
	 * @param unknown $learningModuleId
	 * @param unknown $name
	 * @param unknown $description
	 */
	public function addLearningModuleAsBook($learningModuleId,$name,$description){
	
		global $USER;
		$sectionId = $this->fixedSections[GlobalModel::SECTION_LEARNING_MODULES];
		
		$bookModel = new BookModel();
		$bookModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$bookModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$bookModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$bookModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
		$bookModel->grades = new ActivityGradeBook();
		$bookModel->calendar = new Events();
		
		$bookModel->module = $this->createModule($learningModuleId,"book","2013110500",$sectionId);
		
		$bookModel->book = $this->createLearningModuleBook($learningModuleId,$name,$description, $bookModel->module);
		
		
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$bookModel->module->id;
		$activity->sectionid=$this->sections[$sectionId]->section->id;
		$activity->modulename=$bookModel->module->modulename;
		$activity->title=$bookModel->book->name;
		$activity->directory="activities/book_".$bookModel->book->bookId;
		
		$this->moodle_backup->contents->activities[] = $activity;
		
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","book_".$bookModel->book->bookId,"book_".$bookModel->book->bookId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","book_".$bookModel->book->bookId,"book_".$bookModel->book->bookId."_userinfo",1);
		
		$inforRef = new InfoRef();
		$inforRef->userids[]=$USER->id;
		$inforRef->fileids=$bookModel->book->filesIds;
		
		$bookModel->inforef = $inforRef;
		
		$this->activities[] = $bookModel;

		$this->sections[$sectionId]->section->sequence[]= $bookModel->book->bookId;
			
	}
	
	/**
	 * @param unknown $bookId
	 * @param Module $module
	 *
	 * @return ActivityBook
	 *
	 */
	public function createLearningModuleBook($learningModuleId,$learningModuleName,$description, $module){
		$book  = new ActivityBook();
		$book->id = $learningModuleId;
		$book->moduleid =$module->id;
		$book->modulename =$module->modulename;
		$book->contextid=$this->getNextId();
		$book->bookId = $learningModuleId;
	
		$book->name= $learningModuleName;
		$book->intro = $description;
	
		$book->introformat=1;
		$book->numbering=3;//3 = Indented
		$book->customtitles=0;
		$book->timecreated=time();
		$book->timemodified=time();
		
		$chapterFileLinks = array();		
		
		$request = "SELECT CMS_CONTENT_ENTRY.ID,CMS_CONTENT_ENTRY.NAME AS CMS_NAME, CMS_LINK.NAME AS LINK_NAME,
							CMS_CONTENT_ENTRY.CE_TYPE_NAME, CMS_CONTENT_ENTRY.DESCRIPTION,CO_TOC_LINK.INDENTLEVEL,
							CMS_CONTENT_ENTRY.FILE_CONTENT_ID,CO_TOC_LINK.ID AS TOC_LINK_ID
					FROM CMS_LINK
					  LEFT JOIN CO_TOC_LINK ON CO_TOC_LINK.ID=CMS_LINK.ID
					  LEFT JOIN CMS_CONTENT_ENTRY ON CMS_CONTENT_ENTRY.ID=CMS_LINK.RIGHTOBJECT_ID
					WHERE CMS_LINK.LEFTOBJECT_ID='".$learningModuleId."' AND CMS_LINK.LINK_TYPE_ID='30002' ORDER BY CMS_LINK.DISPLAY_ORDER";
		$stid = oci_parse($this->connection,$request);
		
		oci_execute($stid);
		$puceLevel = 0;
		$puce = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
		$pageNum = 0;
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
			
			$pageNum++;
			$identLevel = $row['INDENTLEVEL'];
		
			$puceLevel+=$identLevel;
			$puceLevel=$puceLevel<0?$puceLevel=0:$puceLevel;
			$puce[$puceLevel]=$puce[$puceLevel]+1;
				
			$puceName = "";
			for($i=0;$i<count($puce); $i++){
				if($i<=$puceLevel){
					$puceName.=$puce[$i].".";
				}else {
					$puce[$i]=0;
				}
			}
				
			$simpleName = $row['LINK_NAME'];
			if(empty($simpleName)){
				$simpleName = $row['CMS_NAME'];
			}
						
			$name =$puceName." ".$simpleName;
			
			$isChapter=$puceLevel==0?true:false;
		
			
			//Ici on test et on �crit dans le rapport s'il y a des actions links
			$request = "SELECT ID, NAME, CE_TYPE_NAME FROM CMS_CONTENT_ENTRY
						WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK
                						WHERE LEFTOBJECT_ID IN (SELECT ID FROM CO_ACTIONMENU WHERE CO_ACTIONMENU.TOC_LINK_ID='".$row['TOC_LINK_ID']."') AND CMS_LINK.NAME IS NOT NULL AND CMS_LINK.LINK_TYPE_ID='30003')";
			$stid1 = oci_parse($this->connection,$request);
			oci_execute($stid1);
			while($row1 = oci_fetch_assoc($stid1)){
				$rem = 'La page "'.$name.'" ('.$learningModuleName.') poss�de un lien d\'action vers "'.$row1['NAME'].'"('.$row1['CE_TYPE_NAME'].').';
				$this->rapportMigration->add(RapportMigration::TYPE_LEARN_MODULE,RapportMigration::LEARN_MODULE_WITH_ACTION_LINK,
						$row['ID'], $row1['NAME'],
						$rem);
				
			}
			
			
			if($row['CE_TYPE_NAME']=='HEADING_TYPE'){				
				$book->addChapter($this->addBookChapter($name,$row['LINK_NAME'], $isChapter,$pageNum));
						
			}elseif($row['CE_TYPE_NAME']=='PAGE_TYPE'){
				$content = "";
				$chapter=$this->addBookChapter($name,$content, $isChapter,$pageNum);
				$chapter->book = $book;
				
				if(empty($row['FILE_CONTENT_ID'])){
					$request = "SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row["ID"]."' AND LINK_TYPE_ID='30004'";
					
					$stid1 = oci_parse($this->connection,$request);
					oci_execute($stid1);
					$row1 = oci_fetch_assoc($stid1);
					
					$file = $this->addCMSFile($row1["RIGHTOBJECT_ID"], 11, $chapter);
					
					//R�cup�re le dernier Id rajout� � la liste des fichiers du book
					if(isset($file)){
						//TODO METTRE DANS LE RAPPORT 
						$more ='style="min-height:600px"';
													
 						$content = '<object data="@@PLUGINFILE@@/'.$file->filename.'" type="'.$file->mimetype.'" height="100%" width="100%" '.$more.'>'.
										utf8_encode('<p>Ce fichier ne peut pas directement �tre affich� dans votre navigateur.</p>
		 								<p>Vous pouvez le t�l�charger via le lien suivant :').'<a href="@@PLUGINFILE@@/'.$file->filename.'">'.$file->filename.'</a></p>
									</object>';
 						$chapter->content = $content;
					}
					
				}else {
					$request = "SELECT CMS_CONTENT_ENTRY.NAME,CMS_CONTENT_ENTRY.FILESIZE,CMS_FILE_CONTENT.CONTENT,CMS_MIMETYPE.MIMETYPE
								FROM CMS_CONTENT_ENTRY
									INNER JOIN CMS_FILE_CONTENT ON CMS_FILE_CONTENT.ID=CMS_CONTENT_ENTRY.FILE_CONTENT_ID
									INNER JOIN CMS_MIMETYPE ON CMS_MIMETYPE.ID=CMS_FILE_CONTENT.MIMETYPE_ID
								WHERE CMS_CONTENT_ENTRY.ID ='".$row['ID']."'";
					$stid1 = oci_parse($this->connection,$request);
					oci_execute($stid1);
					$row1 = oci_fetch_assoc($stid1);
					
					$content = $row1["CONTENT"]->load();
					
					$content=$this->convertTextAndCreateAssociedFiles($content,11,$chapter);
					//Cas particulier de modules contenant des caract�res sp�ciaux probl�matiques..
					if($this->learningContextId=="2840620001" || $this->learningContextId=="2988733001"){
						$content = str_ireplace(array(''), ' ', $content);
					}					
					$chapter->content = $content;
					
					//Add the file link corresponding to this file
					$request = "SELECT NAME FROM CMS_CONTENT_ENTRY
									WHERE ID=(SELECT CMS_LINK.RIGHTOBJECT_ID FROM CMS_LINK WHERE CMS_LINK.LINK_TYPE_ID='30004' AND CMS_LINK.LEFTOBJECT_ID='".$row['ID']."')";

					$stid1 = oci_parse($this->connection,$request);
					oci_execute($stid1);
					$row1 = oci_fetch_assoc($stid1);
					
					$chapterFileLinks[$row1['NAME']]='*'.$book->bookId.'*'.$chapter->id;
					
				}
				$book->addChapter($chapter);
				
				
			}elseif($row['CE_TYPE_NAME']=='ASSESSMENT_TYPE'){
				$content = utf8_encode('�valuation :').'<a href="$@QUIZVIEWBYID*'.$row['ID'].'@$">'.$simpleName.'</a>';
				
				$book->addChapter($this->addBookChapter($name,$content, $isChapter,$pageNum));
			}elseif($row['CE_TYPE_NAME']=='PROJECT_TYPE'){
				$content = utf8_encode('T�che :').'<a href="$@ASSIGNVIEWBYID*'.$row['ID'].'@$">'.$simpleName.'</a>';
				
				$book->addChapter($this->addBookChapter($name,$content, $isChapter,$pageNum));
			}elseif($row['CE_TYPE_NAME']=='URL_TYPE'){
				
				$request = "SELECT CMS_CONTENT_ENTRY.NAME,CMS_CONTENT_ENTRY.DESCRIPTION,CO_URL.LINK, CO_URL.OPENINNEWWINDOWFLAG FROM CMS_CONTENT_ENTRY
					  LEFT JOIN CO_URL ON CO_URL.ID=CMS_CONTENT_ENTRY.ORIGINAL_CONTENT_ID
					WHERE CMS_CONTENT_ENTRY.ID='".$row['ID']."'";
				$stid1 = oci_parse($this->connection,$request);
				oci_execute($stid1);
				$row1 = oci_fetch_assoc($stid1);
				
				$content ="<table><tbody>";
								
				$urlDescription ="";
				if(!empty($row1['DESCRIPTION'])){
					$urlDescription =$row1['DESCRIPTION']->load();
				}
			
				$target="_self";
				if($row1['OPENINNEWWINDOWFLAG']==1){
					$target="_blank";
				}
				
				$urlRow = "<tr>"
							."<td>"."</td>"
							."<td valign='top' width='50%'>"
								."<label><b><a target='".$target."' href='".$row1['LINK']."'>".$row1['NAME']."</a></b></label><br/>"
								."<div>".$urlDescription."</div>"
							."</td>"
							."<td valign='top' width='50%'>".$row1['LINK']."</td>"
						."</tr>";
					$content .= $urlRow;
				
				$content .="</tbody></table>";
				
				$book->addChapter($this->addBookChapter($name,$content, $isChapter,$pageNum));
			}else {
			//	echo 'Ce type d\'object n\'est pas trait� : '.$row['CE_TYPE_NAME'].'<br/>';
				$content = utf8_encode('Ici se trouvait un �l�ment ('.$row['CE_TYPE_NAME'].') qui n\'a pu �tre migr� depuis WebCT.');
				$book->addChapter($this->addBookChapter($name,$content, $isChapter,$pageNum));
			}
			

		}
	
		//Post treatment
		//Find all the html link and convert it if necessary
		$htmlContentClass = new HtmlContentClass();		
		foreach ($book->chapters as $chapter){
			$chapter->content = $htmlContentClass->updateBookChapterLinks($chapter->content, $chapterFileLinks);
		}
		
		return $book;
	}
	
	
	public function addBookChapter($name,$content, $isChapter,$pageNum){
		$chapter = new Chapter();
		$chapter->id=$this->getNextId();
		$chapter->pagenum = $pageNum;
		$chapter->subchapter=$isChapter==true?0:1;
		$chapter->title = $name;
		$chapter->content = $content;
		$chapter->contentformat=1;
		$chapter->hidden=0;
		$chapter->timemodified=time();
		$chapter->importsrc="";
		
		return $chapter;
	}
	
	
	/***************************************************************************************************************
	 * COURSE CONTENT
	*/
	public function retrieveCourseContent(){
		
		//We get the defaut repository
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='ORGANIZER_PAGE_TYPE' AND DELETED_FLAG=0 AND PARENT_ID='".$this->deliveryContextId."' AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
		
		$this->createNewCourseContentSection($row['ID'], utf8_encode("Section d'accueil"), utf8_encode("Section d'accueil (Racine dans WebCT)"));
		
	}

	public function createCourseHeaderAndFooter($repositoryId,$section,$firstPosition=false){

		$request = "SELECT * FROM CO_HEADERFOOTER WHERE CO_ORGANIZERPAGE_ID='".$repositoryId."' AND ISDEFAULT='0' AND TYPE='H' ORDER BY CO_HEADERFOOTER.TYPE DESC";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		
		while($row = oci_fetch_assoc($stid)){
			
			$description = $row['HD_FT_TEXT'];
			if(empty($description)){
				$description ="";
			}else {
				$description =$description->load();
			}
			
			if(!empty($description)){
				$this->addLabel("Header", $description, $section->id,$firstPosition);
			}
		}
		
	}
	
	
	public function createNewCourseContentSection($repositoryId,$repositoryPath,$repositoryDescription){

		$rem = "La section de contenu a �t� migr� comme une section dans Moodle.";
		$this->rapportMigration->add(RapportMigration::TYPE_COURSE_CONTENT,RapportMigration::COURSE_CONTENT_AS_SECTION,
				$repositoryId, $repositoryPath,
				$rem);
				
		$sectionModel = $this->addSections($repositoryId, $repositoryPath, $repositoryDescription,false,1);
		$section = $sectionModel->section;
		
		//The header of the main section should be put in the default general section.
		if($repositoryPath==utf8_encode("Section d'accueil")){
			$sectionId = $this->fixedSections[GlobalModel::SECTION_GENERAL];
			$sectionGeneral = $this->sections[$sectionId]->section;
			$this->createCourseHeaderAndFooter($repositoryId, $sectionGeneral,true);
		}else {
			$this->createCourseHeaderAndFooter($repositoryId, $section);
		}	
		//Add all the files of the repository.
		$request = "SELECT CMS_CONTENT_ENTRY.ID, CMS_CONTENT_ENTRY.NAME, CMS_CONTENT_ENTRY.DESCRIPTION, CMS_CONTENT_ENTRY.CE_TYPE_NAME, CMS_CONTENT_ENTRY.ORIGINAL_CONTENT_ID,
       						CO_ORGANIZERLINK.LINKNAME,CO_ORGANIZERLINK.LONG_DESCRIPTION
					FROM CMS_CONTENT_ENTRY
						LEFT JOIN CMS_LINK ON CMS_LINK.RIGHTOBJECT_ID=CMS_CONTENT_ENTRY.ID
  						LEFT JOIN CO_ORGANIZERLINK ON CO_ORGANIZERLINK.ID=CMS_LINK.ID
  					WHERE CMS_CONTENT_ENTRY.ID IN (SELECT LINK2.RIGHTOBJECT_ID FROM CMS_LINK LINK2 WHERE LINK2.LEFTOBJECT_ID='".$repositoryId."')
  								AND CE_TYPE_NAME!='ORGANIZER_PAGE_TYPE'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
				
		while ($row1 = oci_fetch_assoc($stid)){

			$name = $row1['LINKNAME'];
			if(empty($name)){
				$name = $row1['NAME'];
			}
			
			$description = $row1['LONG_DESCRIPTION'];
			if(empty($description)){
				$description = $row1['DESCRIPTION'];
			}
			if(empty($description)){
				$description ="";
			}else {
				$description =$description->load();
			}
		
			if($row1['CE_TYPE_NAME']=='PAGE_TYPE' && stristr($name,'journal d\'importation du contenu du cours') == false){
				$contentId;
		
				if(empty($row1['FILE_CONTENT_ID'])){
					$request = "SELECT * FROM CMS_CONTENT_ENTRY
									WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row1['ID']."' AND CMS_LINK.LINK_TYPE_ID='30004')";
					$stid2 = oci_parse($this->connection,$request);
					oci_execute($stid2);
					$row2 = oci_fetch_assoc($stid2);
		
					$contentId=$row2['ORIGINAL_CONTENT_ID'];
				}else {
					$contentId=$row1['ORIGINAL_CONTENT_ID'];
				}
		
				$this->addResource($contentId,$name,$description,$section->id);		
			}elseif($row1['CE_TYPE_NAME']=='URL_TYPE'){
				$this->addURL($row1['ORIGINAL_CONTENT_ID'],$section->id);
			}elseif($row1['CE_TYPE_NAME']=='ASSESSMENT_TYPE'){
				$this->addInternalURL($name, $description, '$@QUIZVIEWBYID*'.$row1['ORIGINAL_CONTENT_ID'].'@$',$section->id);
			}elseif($row1['CE_TYPE_NAME']=='PROJECT_TYPE'){
				$this->addInternalURL($name, $description, '$@ASSIGNVIEWBYID*'.$row1['ORIGINAL_CONTENT_ID'].'@$',$section->id);
			}elseif($row1['CE_TYPE_NAME']=='MEDIA_COLLECTION_TYPE'){
				$this->addInternalURL($name, $description, '$@GLOSSARYVIEWBYID*'.$row1['ORIGINAL_CONTENT_ID'].'@$',$section->id);
			}elseif($row1['CE_TYPE_NAME']=='SYLLABUS_TYPE'){
				if(isset($this->syllabusManager->syllabus->use_source_file_fl)){
					if($this->syllabusManager->syllabus->use_source_file_fl==1){
						$moduleId = $this->recupererOriginalContentId($this->deliveryContextId);
						$this->addInternalURL($name, $description, '$@RESOURCEVIEWBYID*'.$moduleId.'@$',$section->id);
					}else {
						$this->addInternalURL($name, $description, '$@PAGEVIEWBYID*'.$this->syllabusManager->syllabus->id.'@$',$section->id);
					}
				}
			}elseif($row1['CE_TYPE_NAME']=='TOC_TYPE'){
				if($this->allLearningModules[$row1['ORIGINAL_CONTENT_ID']]==WebCTModel::LEARNING_MODULE_AS_FOLDER){
					$this->addInternalURL($name, $description, '$@FOLDERVIEWBYID*'.$row1['ORIGINAL_CONTENT_ID'].'@$',$section->id);
				}elseif($this->allLearningModules[$row1['ORIGINAL_CONTENT_ID']]==WebCTModel::LEARNING_MODULE_AS_BOOK){
					$this->addInternalURL($name, $description, '$@BOOKVIEWBYID*'.$row1['ORIGINAL_CONTENT_ID'].'@$',$section->id);
				}else {
					$rem = "Module non trouv�.";
					$this->rapportMigration->add(RapportMigration::TYPE_COURSE_CONTENT,RapportMigration::COURSE_CONTENT_NOT_FOUND,
							$row1["ID"], $name,
							$rem);
				}
			}else {
				
				$rem = "Cet �l�ment n'a pas pu �tre migr�.(".$row1['CE_TYPE_NAME'].")";
				$this->rapportMigration->add(RapportMigration::TYPE_COURSE_CONTENT,RapportMigration::COURSE_CONTENT_NOT_MIGRATED,
						$row1["ID"], $name,
						$rem);
			}
		}
		
		
		//Retrieve the first level reposoitories
		$request = "SELECT CMS_CONTENT_ENTRY.ID,CMS_CONTENT_ENTRY.NAME,CO_ORGANIZERLINK.LONG_DESCRIPTION
					FROM CMS_CONTENT_ENTRY
  						LEFT JOIN CMS_LINK ON CMS_LINK.RIGHTOBJECT_ID=CMS_CONTENT_ENTRY.ORIGINAL_CONTENT_ID
  						LEFT JOIN CO_ORGANIZERLINK ON CO_ORGANIZERLINK.ID=CMS_LINK.ID
					WHERE CE_TYPE_NAME='ORGANIZER_PAGE_TYPE' AND DELETED_FLAG=0 AND PARENT_ID='".$repositoryId."' AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
	
	
		while ($row1 = oci_fetch_assoc($stid)){
			$description = $row1['LONG_DESCRIPTION'];
			if(empty($description)){
				$description ="";
			}else {
				$description =$description->load();
			}

			$hasOnlyFiles = $this->contentRepositoryhasOnlyFiles($row1['ID']);
			//TEST if there are only PAGE and FILE (not HTML)
			$repositoryName = $row1['NAME'];
			$repositoryId = $row1['ID'];
			if($hasOnlyFiles){
					
				$this->createCourseHeaderAndFooter($repositoryId, $section);
				
				$this->addCourseContentAsFolder($repositoryId, $repositoryName, $description,$section);
				
				$rem = "La section de contenu a �t� migr� comme un r�peroire de fichiers";
				$this->rapportMigration->add(RapportMigration::TYPE_COURSE_CONTENT,RapportMigration::COURSE_CONTENT_AS_FOLDER,
						$repositoryId, $repositoryPath.' > '.$repositoryName,
						$rem);
				
			}else {
				$this->createNewCourseContentSection($repositoryId, $repositoryPath.' > '.$repositoryName, $description);	
			}

			//$this->addSections($row['ID'], $row['NAME'], $description,true);
		}
				
	}
	
	/**
	 * @param unknown $repositoryId
	 * @return boolean
	 */
	public function contentRepositoryhasOnlyFiles($repositoryId){
		$hasOnlyFile = true;
		
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$repositoryId."')";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		while($row = oci_fetch_assoc($stid)){
			if($row['CE_TYPE_NAME']=='ORGANIZER_PAGE_TYPE'){
				$hasOnlyFile =$this->contentRepositoryhasOnlyFiles($row['ID']) ;				
			}elseif($row['CE_TYPE_NAME']!="PAGE_TYPE" || !empty($row['FILE_CONTENT_ID'])){
				$hasOnlyFile =false;
			}
			if(!$hasOnlyFile){
				return false;
			}
		}
		
		return $hasOnlyFile;
	}
	
	
	
	
	/**
	 * Add a learning module as a Folder (files)
	 * 
	 * @param unknown $courseContentFolderId
	 * @param string $name
	 * @param string $description
	 * @param Section $section
	 */
	public function addCourseContentAsFolder($courseContentFolderId,$name,$description,$section){
	
		global $USER;
	
		$folderModel = new FolderModel();
		$folderModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->grades = new ActivityGradeBook();
		$folderModel->calendar = new Events();
	
		$folderModel->module = $this->createModule($courseContentFolderId,"folder","2013110500",$section->id);
	
		$folderModel->folder = $this->createCourseContentFolder($courseContentFolderId, $name,$description,$folderModel->module);
	
	
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$folderModel->module->id;
		$activity->sectionid=$this->sections[$section->id]->section->id;
		$activity->modulename=$folderModel->module->modulename;
		$activity->title=$folderModel->folder->name;
		$activity->directory="activities/folder_".$folderModel->folder->folderId;
	
		$this->moodle_backup->contents->activities[] = $activity;
	
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","folder_".$folderModel->folder->folderId,"folder_".$folderModel->folder->folderId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","folder_".$folderModel->folder->folderId,"folder_".$folderModel->folder->folderId."_userinfo",1);
	
		$inforRef = new InfoRef();
		$inforRef->userids[]=$USER->id;
		$inforRef->fileids=$folderModel->folder->filesIds;
	
		$folderModel->inforef = $inforRef;
	
		$this->activities[] = $folderModel;
	
		$this->sections[$section->id]->section->sequence[]= $folderModel->folder->folderId;
	}
	
	
	
	/**
	 * @param unknown $folderId
	 * @param Module $module
	 * @return ActivityFolder
	 */
	public function createCourseContentFolder($courseContentFolderId,$name,$description, $module){
	
		$folder = new ActivityFolder();
		$folder->id = $courseContentFolderId;
		$folder->moduleid =$module->id;
		$folder->modulename =$module->modulename;
		$folder->contextid=$this->getNextId();
		$folder->folderId = $courseContentFolderId;
	
		$folder->name =$name;
		$folder->intro=$description;
		$folder->introformat=1;
		$folder->revision=0;
		$folder->timemodified=time();
		$folder->display=1;
		$folder->showexpanded=0;
	
	
		//Recherche et r�cup�re tous les fichiers et dossiers de WebCT
		$this->addCourseContentFolderFiles($courseContentFolderId, $folder->contextid, "/", $folder->filesIds);
	
		return $folder;
	}
	
	
	public function addCourseContentFolderFiles($courseContentFolderId, $contextId, $path, &$filesIds){
	
		$component = "mod_folder";
		$fileArea = "content";
		$itemId = 0;
		
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$courseContentFolderId."')";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		while($row = oci_fetch_assoc($stid)){
			if($row['CE_TYPE_NAME']=='ORGANIZER_PAGE_TYPE'){
				
				$request = "SELECT CMS_CONTENT_ENTRY.ID,CMS_CONTENT_ENTRY.NAME,CO_ORGANIZERLINK.LONG_DESCRIPTION
						FROM CMS_CONTENT_ENTRY
  							LEFT JOIN CMS_LINK ON CMS_LINK.RIGHTOBJECT_ID=CMS_CONTENT_ENTRY.ORIGINAL_CONTENT_ID
  							LEFT JOIN CO_ORGANIZERLINK ON CO_ORGANIZERLINK.ID=CMS_LINK.ID
					 	WHERE CE_TYPE_NAME='ORGANIZER_PAGE_TYPE' AND DELETED_FLAG=0 AND CMS_CONTENT_ENTRY.ID='".$row['ID']."' AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
				$stid1 = oci_parse($this->connection,$request);
				oci_execute($stid1);
				
				while ($row1 = oci_fetch_array($stid1, OCI_ASSOC+OCI_RETURN_NULLS)){
					$description = $row1['LONG_DESCRIPTION'];
					if(empty($description)){
						$description ="";
					}else {
						$description =$description->load();
					}
					$newPath=$path.$row1['NAME'].'/';
					$repository = $this->addCMSRepository($contextId, $component, $fileArea, $itemId, $newPath);
					$filesIds[] = $repository->id;
					$this->addCourseContentFolderFiles($row1['ID'], $contextId, $newPath, $filesIds);
				}		
			}elseif($row['CE_TYPE_NAME']=="PAGE_TYPE"){
				
				$request = "SELECT ORIGINAL_CONTENT_ID FROM CMS_CONTENT_ENTRY
							WHERE ID=(SELECT CMS_LINK.RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row['ID']."' AND LINK_TYPE_ID='30004')";
				$stid1 = oci_parse($this->connection,$request);
				oci_execute($stid1);
				$row1 = oci_fetch_assoc($stid1);
				
				$file = $this->addCMSSimpleFile($row1["ORIGINAL_CONTENT_ID"], $contextId, $component, $fileArea, $itemId, $path);
				$filesIds[] = $file->id;
			}
		}	
	}
	
	

	/**
	 * @param unknown $id
	 * @param unknown $name
	 * @param unknown $description
	 * @param string $includeChildrenRepository
	 * @return SectionModel
	 */
	public function addSections($id,$name,$description,$includeChildrenRepository=false, $visible=0){
	
		$sectionModels = array();	
	
		$sectionModel = new SectionModel();
	
		$section = new Section();
		$section->id=$this->getNextSectionId();
		$section->number=$section->id;
		
		if(strlen($name)>255){
			$rem = "La taille du nom de la section a �t� limit� � 255 caract�res.";
			$this->rapportMigration->add(RapportMigration::TYPE_COURSE_CONTENT,RapportMigration::COURSE_CONTENT_SECTION_NAME_LIMITED,
					$section->id, $name,
					$rem);
			$section->name = substr($name, 252)."...";
			$section->summary=$name."<br/>".$description;
		}else {
			$section->name=$name;
			$section->summary=$description;
		}
		
		$section->summaryformat=1;
		$section->visible=$visible;
		$section->availablefrom=0;
		$section->availableuntil=0;
		$section->showavailability=0;
		$section->groupingid=0;
	
		$sectionModel->section = $section;
	
		$infoRef = new InfoRef();
		$sectionModel->inforef = $infoRef;
	
		$this->sections[$section->id] = $sectionModel;
		
	//	echo 'SECTION ID =='.$section->id.'<br/>';
			
		$moodleBackupSection = new MoodleBackupSectionsSection($section->id,$section->number,"sections/section_".$section->id);
	
		//moodle_backup settings
		$this->moodle_backup->settings[] = new MoodleBackupSectionSetting("section","section_".$section->id,"section_".$section->id."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupSectionSetting("section","section_".$section->id,"section_".$section->id."_userinfo",1);
	
		$this->moodle_backup->contents->sections[]=$moodleBackupSection;
	
		$this->course->course->numsections++; 
		
		return $sectionModel;
		
// 		if($includeChildrenRepository){
// 		//Add Sub section as Label 
// 			//Retrieve the first level reposoitory
// 			$request = "SELECT CMS_CONTENT_ENTRY.ID,CMS_CONTENT_ENTRY.NAME,CO_ORGANIZERLINK.LONG_DESCRIPTION
// 						FROM CMS_CONTENT_ENTRY
//   							LEFT JOIN CMS_LINK ON CMS_LINK.RIGHTOBJECT_ID=CMS_CONTENT_ENTRY.ORIGINAL_CONTENT_ID
//   							LEFT JOIN CO_ORGANIZERLINK ON CO_ORGANIZERLINK.ID=CMS_LINK.ID
// 					 	WHERE CE_TYPE_NAME='ORGANIZER_PAGE_TYPE' AND DELETED_FLAG=0 AND PARENT_ID='".$id."' AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
// 			$stid = oci_parse($this->connection,$request);
// 			oci_execute($stid);
				
// 			while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
// 				$description = $row['LONG_DESCRIPTION'];
// 				if(empty($description)){
// 					$description ="";
// 				}else {
// 					$description =$description->load();
// 				}				
// 				$this->addSubSection($section->id, $row['ID'],$row['NAME'],$description,0);				
// 			}
	
// 		} 
		
	}
	
	public function addURL($urlOriginalId,$section=0){
		
		$urlModel = new URLModel();
		$urlModel->calendar = new Events(); //vide
		$urlModel->comments = new Comments(); //vide
		$urlModel->completion = new ActivityCompletion(); //vide
		$urlModel->filters = new Filters(); //vide
		$urlModel->grades = new ActivityGradeBook(); // Vide
		$urlModel->inforef = new InfoRef(); // A remplir
		$urlModel->roles = new RolesBackup(); //Vide
	
		$urlModel->module = $this->createModule($urlOriginalId,"url","2013110500",$section);
		$urlModel->url = $this->createURL($urlOriginalId,$urlModel->module);
	
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$urlModel->module->id;
		$activity->sectionid=$section;
		$activity->modulename=$urlModel->module->modulename;
		$activity->title=$urlModel->url->name;
		$activity->directory="activities/url_".$urlModel->url->urlId;
	
	
		$this->moodle_backup->contents->activities[] = $activity;
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","url_".$urlModel->url->urlId,"url_".$urlModel->url->urlId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","url_".$urlModel->url->urlId,"url_".$urlModel->url->urlId."_userinfo",1);
	
		$this->activities[] = $urlModel;
	
		$this->sections[$section]->section->sequence[]= $urlModel->url->urlId;
	}
	
	/**
	 * @param unknown $urlOriginalId
	 * @param unknown $module
	 * @return NULL|ResourceUrl
	 */
	public function createURL($urlOriginalId,$module){
	
		$request = "SELECT CMS_CONTENT_ENTRY.NAME,CMS_CONTENT_ENTRY.DESCRIPTION,CO_URL.LINK, CO_URL.OPENINNEWWINDOWFLAG FROM CMS_CONTENT_ENTRY
					  LEFT JOIN CO_URL ON CO_URL.ID=CMS_CONTENT_ENTRY.ORIGINAL_CONTENT_ID
					WHERE CMS_CONTENT_ENTRY.ORIGINAL_CONTENT_ID='".$urlOriginalId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_assoc($stid);
		
		if(empty($row)){
			return null;
		}
		
		$resourceUrl = new ResourceUrl();
		$resourceUrl->id = $urlOriginalId ;
		$resourceUrl->moduleid =$module->id;
		$resourceUrl->modulename =$module->modulename;
		$resourceUrl->contextid=$this->getNextId();
		$resourceUrl->urlId = $urlOriginalId;
		$resourceUrl->name = $row['NAME'];
		
		$description = $row['DESCRIPTION'];
		if(empty($description)){
			$description ="";
		}else {
			$description =$description->load();
		}		
		$resourceUrl->intro = $description;
		$resourceUrl->introformat = "1";
		$resourceUrl->externalurl = $row['LINK'];
		
		if($row['OPENINNEWWINDOWFLAG']==1){
			$resourceUrl->display = "3";
		}else {
			$resourceUrl->display = "5";
		}

		$resourceUrl->displayoptions = 'a:0:{}';
		$resourceUrl->parameters = 'a:0:{}';
		$resourceUrl->timemodified =time();
	
		return $resourceUrl;
	
	}
	
	public function addInternalURL($name,$description,$url,$section=0){
	
		$urlModel = new URLModel();
		$urlModel->calendar = new Events(); //vide
		$urlModel->comments = new Comments(); //vide
		$urlModel->completion = new ActivityCompletion(); //vide
		$urlModel->filters = new Filters(); //vide
		$urlModel->grades = new ActivityGradeBook(); // Vide
		$urlModel->inforef = new InfoRef(); // A remplir
		$urlModel->roles = new RolesBackup(); //Vide
	
		$id = $this->getNextId();
		$urlModel->module = $this->createModule($id,"url","2013110500",$section);
		$urlModel->url = $this->createInternalURL($id,$name,$description,$url,$urlModel->module);
	
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$urlModel->module->id;
		$activity->sectionid=$section;
		$activity->modulename=$urlModel->module->modulename;
		$activity->title=$urlModel->url->name;
		$activity->directory="activities/url_".$urlModel->url->urlId;
	
	
		$this->moodle_backup->contents->activities[] = $activity;
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","url_".$urlModel->url->urlId,"url_".$urlModel->url->urlId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","url_".$urlModel->url->urlId,"url_".$urlModel->url->urlId."_userinfo",1);
	
		$this->activities[] = $urlModel;
	
		$this->sections[$section]->section->sequence[]= $urlModel->url->urlId;
	}
	
	/**
	 * @param unknown $id
	 * @param unknown $name
	 * @param unknown $description
	 * @param unknown $url
	 * @param unknown $module
	 * @return NULL|ResourceUrl
	 */
	public function createInternalURL($id,$name,$description,$url,$module){
	
		$resourceUrl = new ResourceUrl();
		$resourceUrl->id = $id ;
		$resourceUrl->moduleid =$module->id;
		$resourceUrl->modulename =$module->modulename;
		$resourceUrl->contextid=$this->getNextId();
		$resourceUrl->urlId = $id;
		$resourceUrl->name = $name;
		$resourceUrl->intro = $description;
		$resourceUrl->introformat = "1";
		$resourceUrl->externalurl = $url;
		$resourceUrl->display = "0";
		$resourceUrl->displayoptions = 'a:0:{}';
		$resourceUrl->parameters = 'a:0:{}';
		$resourceUrl->timemodified =time();
	
		return $resourceUrl;
	
	}
// 	/**
// 	 * Add a sub section
// 	 */
// 	public function addSubSection($sectionId, $id,$name,$description,$indent=0){
		
// 		//Add Sub section Label
		
		
		
// 		//Add the current section sub section..
// 		$request = "SELECT CMS_CONTENT_ENTRY.ID,CMS_CONTENT_ENTRY.NAME,CO_ORGANIZERLINK.LONG_DESCRIPTION
// 						FROM CMS_CONTENT_ENTRY
//   							LEFT JOIN CMS_LINK ON CMS_LINK.RIGHTOBJECT_ID=CMS_CONTENT_ENTRY.ORIGINAL_CONTENT_ID
//   							LEFT JOIN CO_ORGANIZERLINK ON CO_ORGANIZERLINK.ID=CMS_LINK.ID
// 					 	WHERE CE_TYPE_NAME='ORGANIZER_PAGE_TYPE' AND DELETED_FLAG=0 AND PARENT_ID='".$id."' AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
// 		$stid = oci_parse($this->connection,$request);
// 		oci_execute($stid);
		
// 		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
// 			$description = $row['LONG_DESCRIPTION'];
// 			if(empty($description)){
// 				$description ="";
// 			}else {
// 				$description =$description->load();
// 			}
// 			$this->addSubSection($sectionId, $row['ID'],$row['NAME'],$description,$indent+1);
// 		}
		
// 		//
// 	}
	
	/**
	 * Add files to a section
	 */
	public function addFillesToSection($sectionId, $id,$name,$description,$indent=0){
	
		//Add Sub section Label
	
	
		//
	}
	
	
	/**
	 * Add a Folder and all its content
	 */
	public function addLabel($name,$description,$sectionId,$firstPosition=false){
	
		global $USER;
	
		//Glossary
		$labelModel = new LabelModel();
		$labelModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$labelModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$labelModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$labelModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
		$labelModel->grades = new ActivityGradeBook();
		$labelModel->calendar = new Events();
	
		
		$id = $this->getNextId();
		$labelModel->module = $this->createModule($id,"label","2013110500",$sectionId);
	
		$label = new ResourceLabel();
		$label->id = $id;
		$label->moduleid =$labelModel->module->id;
		$label->modulename =$labelModel->module->modulename;
		$label->contextid=$this->getNextId();
		$label->labelId = $id;
		
		//Ici on choisit le nom de notre folder
		$label->name =$name;
		
		$convertedDescription = $this->convertTextAndCreateAssociedFiles($description,12, $label);
		$label->intro=$convertedDescription;
		$label->introformat=1;
		$label->timemodified=time();
		
		$labelModel->label = $label;
		
	
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$labelModel->module->id;
		$activity->sectionid=$this->sections[$sectionId]->section->id;
		$activity->modulename=$labelModel->module->modulename;
		$activity->title=$labelModel->label->name;
		$activity->directory="activities/label_".$labelModel->label->labelId;
	
		if($firstPosition){
			array_unshift($this->moodle_backup->contents->activities,$activity);
		}else {
			$this->moodle_backup->contents->activities[] = $activity;
		}
	
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","label_".$labelModel->label->labelId,"label_".$labelModel->label->labelId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","label_".$labelModel->label->labelId,"label_".$labelModel->label->labelId."_userinfo",1);
	
		$inforRef = new InfoRef();
		$inforRef->userids[]=$USER->id;
		$inforRef->fileids=$labelModel->label->filesIds;
	
		$labelModel->inforef = $inforRef;
	
		$this->activities[] = $labelModel;
		if($firstPosition){
			array_unshift($this->sections[$sectionId]->section->sequence, $labelModel->label->labelId);
		}else {
			$this->sections[$sectionId]->section->sequence[]= $labelModel->label->labelId;
		}
	}
	
	
	/***************************************************************************************************************
	 * Forum (Folder)
	*/
	
	public function retrieveForum(){
		$request = "Select count(*) as NBELEM
					from CMS_CONTENT_ENTRY cm1
					JOIN CMS_CONTENT_ENTRY cm2 on cm2.ID = cm1.PARENT_ID
					JOIN DIS_MESSAGE msg on msg.TOPIC_ID = cm1.ID
					JOIN PERSON p on msg.AUTHOR_ID = p.ID
					WHERE cm1.DELIVERY_CONTEXT_ID = '".$this->deliveryContextId."' and cm1.CE_TYPE_NAME = 'DISCUSSION_TOPIC_TYPE'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
		if($row["NBELEM"] > 0){
			$this->addForum($this->getNextId());
		}else{
			
			$rem = "Le forum n'a pas pu �tre migr�.";
			$this->rapportMigration->add(RapportMigration::TYPE_FORUM,RapportMigration::FORUM_NOT_MIGRATED,
					$this->learningContextId, "forum 2013-2014",
					$rem);
		}
		
	}
	
	public function addForum($idForum){
		global $USER;
		$sectionId = $this->fixedSections[GlobalModel::SECTION_GENERAL];
		
		//Glossary
		$folderModel = new FolderModel();
		$folderModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->grades = new ActivityGradeBook();
		$folderModel->calendar = new Events();
		
		$folderModel->module = $this->createModule($idForum,"folder","2013110500",$sectionId);
		$folderModel->folder = $this->createActivityForum($idForum, $folderModel->module);
		
		
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$folderModel->module->id;
		$activity->sectionid=$this->sections[$sectionId]->section->id;
		$activity->modulename=$folderModel->module->modulename;
		$activity->title=$folderModel->folder->name;
		$activity->directory="activities/folder_".$folderModel->folder->folderId;
		
		$this->moodle_backup->contents->activities[] = $activity;
		
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","folder_".$folderModel->folder->folderId,"folder_".$folderModel->folder->folderId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","folder_".$folderModel->folder->folderId,"folder_".$folderModel->folder->folderId."_userinfo",1);
		
		$inforRef = new InfoRef();
		$inforRef->userids[]=$USER->id;
		$inforRef->fileids=$folderModel->folder->filesIds;
		
		$folderModel->inforef = $inforRef;
		
		$this->activities[] = $folderModel;
		
		$this->sections[$sectionId]->section->sequence[]= $folderModel->folder->folderId;
		
		
	}
	
	public function createActivityForum($idForum,$module){
		$folder = new ActivityFolder();
		$folder->id = $idForum;
		$folder->moduleid =$module->id;
		$folder->modulename =$module->modulename;
		$folder->contextid=$this->getNextId();
		$folder->folderId = $idForum;
		
		//Ici on choisit le nom de notre folder
		$folder->name =utf8_encode("Forum 2013-2014");
		$folder->intro=utf8_encode("Ensemble de toute les discussion de 2013-2014.");
		$folder->introformat=1;
		$folder->revision=0;
		$folder->timemodified=time();
		$folder->display=0;
		$folder->showexpanded=0;
		
		$this->fillForum($folder->contextid , $folder->filesIds);
		
		return $folder;
	}
	
	public function fillForum($contextid , &$filesIds){
		$request = "Select cm1.ID as ID_TOPIC , cm2.NAME as NAME_CATEGORIE , cm1.NAME as NAME_TOPIC , 
				msg.SUBJECT , msg.SHORT_MESSAGE ,msg.LONG_MESSAGE, msg.POSTDATE ,
				msg.ROOT_MESSAGE_ID, msg.FILE_GROUP_ID , p.WEBCT_ID
				from CMS_CONTENT_ENTRY cm1
				JOIN CMS_CONTENT_ENTRY cm2 on cm2.ID = cm1.PARENT_ID
				JOIN DIS_MESSAGE msg on msg.TOPIC_ID = cm1.ID
				JOIN PERSON p on msg.AUTHOR_ID = p.ID
				WHERE cm1.DELIVERY_CONTEXT_ID = '" . $this->deliveryContextId . "'and 
						cm1.CE_TYPE_NAME = 'DISCUSSION_TOPIC_TYPE'
				order by NAME_CATEGORIE , NAME_TOPIC , msg.ROOT_MESSAGE_ID, msg.POSTDATE";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		$nom_categorie = ' ';
		$nomFichier = ' ';
		$path = '/';
		$pathFichier ="";
		$rootId = "";
		$file = NULL;
		$style = $this->creationCssForum();
		$content = $style;
		$nbObject = 0;
		while($res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS )){
			if($nom_categorie != $res["NAME_CATEGORIE"]){
				$nom_categorie = (($res["NAME_CATEGORIE"] == "Vide") ? utf8_encode("Th�mes non cat�goris�s") : $res["NAME_CATEGORIE"]);
				$path =  '/' . $nom_categorie . '/';
				$this->createFolderInterne($nom_categorie , $contextid, $filesIds, $path);
				$pathFichier = $path . utf8_encode('Fichier de la cat�gorie/');
				$this->createFolderInterne( utf8_encode('Fichier de la cat�gorie'), $contextid, $filesIds, $pathFichier);	
			}

			 if($nomFichier != $res["NAME_TOPIC"]){		 	
			 	if($file != NULL){
			 		$content = $content . '</div>';
			 		$file->contenthash=md5($content);
			 		$file->createFile($content, $this->repository);
			 		$this->files->files[]=$file;
			 		$filesIds[] = $file->id;
			 		$content = $style;
			 	}
			 	
				$nomFichier = $res["NAME_TOPIC"];
				$content = $content . '<body> ';
				$content = $content . '<h1 style="text-align:center">'. utf8_decode($nomFichier) . '</h1>';
				$content = $content . $this->creationTableMatiere($nomFichier);
				$file = $this->createFichierInterne($nomFichier,$contextid ,$path );
				$nbObject = 0;
			}
			
			if($rootId != $res["ROOT_MESSAGE_ID"]){
				$nbObject++;
				$rootId = $res["ROOT_MESSAGE_ID"];
				$content = $content . '<h3 id ="' . utf8_decode($res["SUBJECT"]) . '" > ' . utf8_decode($res["SUBJECT"]) . ':</h3>';
			}
			
			$message = $res["SHORT_MESSAGE"];
			if( $message == NULL){
				if($res["LONG_MESSAGE"]!=null){
					$message = $res["LONG_MESSAGE"]->load();
				}else {
					$message = "";
				}
			}
			
			if($res["FILE_GROUP_ID"] !=NULL){
				$file2 = $this->createFichierAssocie($contextid, $pathFichier, $filesIds, $res["FILE_GROUP_ID"]);
				$message = $message . '</br> <b> Fichier associ� au message :  '.$pathFichier. $file2->filename . '</b>';
			}
			
			$timestamp = substr($res["POSTDATE"],0 , -3 );
			$date = date("D ,d F Y H:i:s",$timestamp);
			$content = $content . '<div class="entrydiv">
  							<table width="100%" cellspacing="0" summary="">
  								<tr>
  									<td width="50%"><strong>Objet :</strong>  ' .utf8_decode($res["SUBJECT"]) .'</td>
  									<td width="50%" class="rightcolumn"><b>Th�me :</b>  ' . utf8_decode($res["NAME_TOPIC"]) . '</td>
  								</tr>
  								<tr>
  									<td><b>Auteur :</b>  ' .utf8_decode($res["WEBCT_ID"]) .'</td>
  									<td class="rightcolumn"><b>Date :</b>  ' .$date.' </td>
  								</tr>
 							 </table>
 							 <div class="entrytext">' . utf8_decode($message) .  '</div>
						</div>';	
			$this->rapportMigration->add(RapportMigration::TYPE_CHAT, RapportMigration::CHAT_MIGRATED,
					$file->id, $file->filename,$nbObject);
			
		}
		
		if($file != NULL){
		
			$file->contenthash=md5($content);
			$file->createFile($content, $this->repository);
			$this->files->files[]=$file;
			$filesIds[] = $file->id;
			$content = "";
		}
		
		
	}
	
	
	/***************************************************************************************************************
	 * Syllabus
	*/
	
	public function retrieveSyllabus(){
		$this->initializeSyllabus();
		$pageId = $this->syllabusManager->syllabus->id;
		if($this->syllabusManager->syllabus->use_source_file_fl == 1){			
			$originalContentId = $this->recupererOriginalContentId($this->deliveryContextId);
			if($originalContentId != NULL){
				$name = "Plan de cour: " . $this->syllabusManager->courseInfo->nomCours;
				$this->addResource($originalContentId,$name,"Description");
				
				$this->rapportMigration->add(RapportMigration::TYPE_PROGRAM,RapportMigration::PROGRAM_MIGRATED,
						$pageId, "Plan de cours: " .$this->syllabusManager->courseInfo->nomCours);
				
				
			}else{
				$rem = "Incoh�rence dans la base de donn�e.";
				$this->rapportMigration->add(RapportMigration::TYPE_PROGRAM,RapportMigration::PROGRAM_BD_INCONSISTENCY,
						$pageId, "Plan de cours: " .$this->syllabusManager->courseInfo->nomCours,
						$rem);
			}		
		}else if($this->verifierCreerProgramme($pageId)){
			$this->addPage($pageId,"syllabus");
		}else{
			$courseName = $this->course->course->fullname;
			
			$rem = "Seulement des formateurs donc pas de cr�ation du programme";
			$this->rapportMigration->add(RapportMigration::TYPE_PROGRAM,RapportMigration::PROGRAM_ONLY_FORMATORS,
					$pageId, "Plan de cours: " .$courseName,
					$rem);
			
		}
	}
	
	public function addResource($originalContentId,$name,$description,$section=0){
		$resourceModel = new RessourceModel();
		$resourceModel->calendar = new Events(); //vide
		$resourceModel->comments = new Comments(); //vide
		$resourceModel->completion = new ActivityCompletion(); //vide
		$resourceModel->filters = new Filters(); //vide
		$resourceModel->grades = new ActivityGradeBook(); // Vide
		$resourceModel->inforef = new InfoRef(); // A remplir
		$resourceModel->roles = new RolesBackup(); //Vide
		
		$resourceModel->module = $this->createModule($originalContentId,"resource","2013110500",$section);
		$resourceModel->ressource = $this->createResource($originalContentId,$name,$description,$resourceModel->module);

		
		$file = $this->addCMSFile($originalContentId, "10", $resourceModel->ressource);
		if($file->mimetype=="application/pdf"){
			$resourceModel->ressource->display=3;
		}
		
		$resourceModel->inforef->fileids = $resourceModel->ressource->filesIds ;	
		
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$resourceModel->module->id;
		$activity->sectionid=$section;
		$activity->modulename=$resourceModel->module->modulename;
		$activity->title=$resourceModel->ressource->name;
		$activity->directory="activities/resource_".$resourceModel->ressource->ressourceId;
		
		
		$this->moodle_backup->contents->activities[] = $activity;
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","resource_".$resourceModel->ressource->ressourceId,"resource_".$resourceModel->ressource->ressourceId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","resource_".$resourceModel->ressource->ressourceId,"resource_".$resourceModel->ressource->ressourceId."_userinfo",1);
		
		$this->activities[] = $resourceModel;
		
		$this->sections[$section]->section->sequence[]= $resourceModel->ressource->ressourceId;			
	}
	
	public function createResource($resourceId ,$name,$description, $module, $display=0){
		
		$resourceActivity = new ActivityRessource();
		$resourceActivity->id = $resourceId ;
		$resourceActivity->moduleid =$module->id;
		$resourceActivity->modulename =$module->modulename;
		$resourceActivity->contextid=$this->getNextId();
		$resourceActivity->ressourceId = $resourceId;
		$resourceActivity->name = $name;
		$resourceActivity->intro = $description;
		$resourceActivity->introformat = "1";
		$resourceActivity->tobemigrated = "0";
		$resourceActivity->legacyfiles = "0";
		$resourceActivity->legacyfileslast = "$@NULL@$";
		$resourceActivity->display = $display;
		$resourceActivity->displayoptions = 'a:1:{s:10:"printintro";i:1;}';
		$resourceActivity->filterFiles = '0';
		$resourceActivity->revision = '1';
		$resourceActivity->timemodified =time();
		
		return $resourceActivity;
		
	}
	
	/**
	 * Add a page
	 *@param $type correspond au type d'information qui sera plac� dans la page (syllabus,url,...)
	 */
	public function addPage($pageId , $type){
		$sectionId = $this->fixedSections[GlobalModel::SECTION_GENERAL];
		
		$pageModel = new PageModel();
		$pageModel->roles = new RolesBackup(); //Vide
		$pageModel->inforef = new InfoRef(); // Vide
		$pageModel->grades = new ActivityGradeBook(); // Vide
		$pageModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$pageModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$pageModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
		$pageModel->calendar = new Events();
		
		$pageModel->module = $this->createModule($pageId,"page","2013110500",$sectionId);
		$pageModel->page  = $this->createPage($pageId, $pageModel->module, $type)	;
		
		
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$pageModel->module->id;
		$activity->sectionid=$sectionId;
		$activity->modulename=$pageModel->module->modulename;
		$activity->title=$pageModel->page->name;
		$activity->directory="activities/page_".$pageModel->page->pageId;
	
	
		$this->moodle_backup->contents->activities[] = $activity;
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","page_".$pageModel->page->pageId,"page_".$pageModel->page->pageId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","page_".$pageModel->page->pageId,"page_".$pageModel->page->pageId."_userinfo",1);
	
		$this->activities[] = $pageModel;
		
		$this->rapportMigration->add(RapportMigration::TYPE_PROGRAM,RapportMigration::PROGRAM_MIGRATED,
				$pageId, $pageModel->page->name);
			
		$this->sections[$sectionId]->section->sequence[]= $pageModel->page->pageId;
	}
	
	/**
	 *@param $type correspond au type d'information qui sera plac� dans la page (syllabus,url,...)
	 */
	public function createPage($pageId , $module , $type){
		$infoContent = "";
		$name = "";
		if($type == "syllabus"){
			$infoContent = $this->recupererInfoSyllabus();
			$name = "Plan de cours: " . $this->syllabusManager->courseInfo->nomCours;
		}
	
		$pageActivity = new ActivityPage();
		$pageActivity->id = $pageId ;
		$pageActivity->moduleid =$module->id;
		$pageActivity->modulename =$module->modulename;
		$pageActivity->contextid=$this->getNextId();
		$pageActivity->pageId = $pageId;
		$pageActivity->name = $name;
		$pageActivity->intro = 'Description';
		$pageActivity->introformat = '1';
		$pageActivity->content = $infoContent;
		$pageActivity->contentformat = '1';
		$pageActivity->legacyfiles = '0';
		$pageActivity->legacyfileslast = '$@NULL@$';
		$pageActivity->display = '5';
		$pageActivity->displayoptions = 'a:1:{s:10:"printintro";s:1:"0";}';
		$pageActivity->revision = '0';
		$pageActivity->timemodified = time();
	
		return $pageActivity;
	
	}
	/***************************************************************************************************************
	 * EMAIL
	*/
	public function retrieveEmail(){
		$this->addEmail($this->getNextId());
	}
	
	public function addEmail($idEmail){
		global $USER;
		$sectionId = $this->fixedSections[GlobalModel::SECTION_GENERAL];
	
		//Glossary
		$folderModel = new FolderModel();
		$folderModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
		$folderModel->grades = new ActivityGradeBook();
		$folderModel->calendar = new Events();
	
		$folderModel->module = $this->createModule($idEmail,"folder","2013110500",$sectionId);
		$folderModel->folder = $this->createActivityEmail($idEmail, $folderModel->module);
	
	
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$folderModel->module->id;
		$activity->sectionid=$this->sections[$sectionId]->section->id;
		$activity->modulename=$folderModel->module->modulename;
		$activity->title=$folderModel->folder->name;
		$activity->directory="activities/folder_".$folderModel->folder->folderId;
	
		$this->moodle_backup->contents->activities[] = $activity;
	
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","folder_".$folderModel->folder->folderId,"folder_".$folderModel->folder->folderId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","folder_".$folderModel->folder->folderId,"folder_".$folderModel->folder->folderId."_userinfo",1);
	
		$inforRef = new InfoRef();
		$inforRef->userids[]=$USER->id;
		$inforRef->fileids=$folderModel->folder->filesIds;
	
		$folderModel->inforef = $inforRef;
	
		$this->activities[] = $folderModel;
	
		$this->sections[$sectionId]->section->sequence[]= $folderModel->folder->folderId;
	
	
	}
	
	public function createActivityEmail($idForum,$module){
		$folder = new ActivityFolder();
		$folder->id = $idForum;
		$folder->moduleid =$module->id;
		$folder->modulename =$module->modulename;
		$folder->contextid=$this->getNextId();
		$folder->folderId = $idForum;
	
		//Ici on choisit le nom de notre folder
		$folder->name =utf8_encode("Courrier");
		$folder->intro=utf8_encode("Ensemble de toute les email de 2013-2014.");
		$folder->introformat=1;
		$folder->revision=0;
		$folder->timemodified=time();
		$folder->display=0;
		$folder->showexpanded=0;
	
		$this->fillEmail($folder->contextid , $folder->filesIds);
	
		return $folder;
	}
	
	public function fillEmail($contextid , &$filesIds){
		$request = "Select pers.WEBCT_ID as NAME_POSSESSEUR_EMAIL , folder.TYPE as FOLDER_TYPE, folder.NAME
						as FOLDER_NAME , mes.SUBJECT , mes.SHORT_MESSAGE ,
					CONCAT (pers2.NAME_N_GIVEN ,pers2.NAME_N_FAMILY) as EXPEDITEUR ,target.NAME as DESTINATAIRE ,
									mes.DATE_SENT ,mes.LONG_MESSAGE , mes.FILE_GROUP_ID
					from Mail_BOX box
					JOIN PERSON pers on box.PERSON_ID = pers.ID
					JOIN MAIL_FOLDER folder on box.ID = folder.MAIL_BOX_ID
					JOIN MAIL_RECEIPT rec on folder.ID = rec.MAIL_FOLDER_ID
					JOIN MAIL_MESSAGE mes on rec.MAIL_MESSAGE_ID = mes.ID
					JOIN PERSON pers2 on mes.PERSON_ID = pers2.ID
					JOIN MAIL_TARGET target on mes.ID = target.MAIL_MESSAGE_ID
					where box.LEARNING_CONTEXT_ID = '".$this->learningContextId ."' and box.ID != '368132341001'
						 and folder.TYPE != 1 and folder.TYPE != 3
					order by pers.WEBCT_ID ,  folder.TYPE , folder.NAME";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		$mailBox = ' ';
		$folderType = ' ';
		$folder = ' ';
		$nomFichier = ' ';
		$path = '/';
		$pathFolder = ' ';
		$pathFichier ="";
		$file = NULL;
		$style = $this->creationCssForum();
		$content = $style;
		while($res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS )){
			if($mailBox != $res["NAME_POSSESSEUR_EMAIL"]){
				$mailBox = $res["NAME_POSSESSEUR_EMAIL"];
				$path =  '/' . $mailBox . '/';
				$this->createFolderInterne($mailBox , $contextid, $filesIds, $path);
				$pathFichier = $path . utf8_encode('Fichiers r�cup�r�s/');
				$this->createFolderInterne( utf8_encode('Fichiers r�cup�r�s'), $contextid, $filesIds, $pathFichier);
			}
	
			if($folderType != $res["FOLDER_TYPE"] || $folderType == '4'){
				$folderType = $res["FOLDER_TYPE"];
				if($folderType != '4'){
					$folder = (($folderType == 0) ? utf8_encode("Bo�te de r�ception") : utf8_encode("El�ment envoy�") );
				}else{
					$folder =  $res["FOLDER_NAME"];
				}
				$pathFolder = $path . $folder . '/';
				$this->createFolderInterne($folder , $contextid, $filesIds, $pathFolder);
				if($file != NULL){
					$content = $content . '</div>';
					$file->contenthash=md5($content);
					$file->createFile($content, $this->repository);
					$this->files->files[]=$file;
					$filesIds[] = $file->id;
					$content = $style;
				}
				$nomFichier = "Email";
				$file = $this->createFichierInterne($nomFichier,$contextid ,$pathFolder );
			}
	
			$message = $res["SHORT_MESSAGE"];
			if( $message == NULL){
				$message = $res["LONG_MESSAGE"]->load();
			}
	
			if($res["FILE_GROUP_ID"] !=NULL){
				$file2 = $this->createFichierAssocie($contextid, $pathFichier, $filesIds, $res["FILE_GROUP_ID"]);
				$message = $message . '</br> <b> Fichier associ� au message :  '.$pathFichier. $file2->filename . '</b>';
			}
	
			$timestamp = substr($res["DATE_SENT"],0 , -3 );
			$date = date("D ,d F Y H:i:s",$timestamp);
			$content = $content . '<div class="entrydiv">
  							<table width="100%" cellspacing="0" summary="">
  								<tr>
  									<td width="50%"><strong>Objet :</strong>  ' .$res["SUBJECT"] .'</td>
  									<td width="50%" class="rightcolumn"><b>A :</b>  ' .utf8_encode($res["DESTINATAIRE"]). '</td>
  								</tr>
  								<tr>
  									<td><b>DE :</b> ' .$res["EXPEDITEUR"] .'</td>
  									<td class="rightcolumn"><b>Envoy� :</b>  ' .$date.' </td>
  								</tr>
 							 </table>
 							 <div class="entrytext">' . utf8_decode($message) .  '</div>
						</div>';
		}
	
		if($file != NULL){
			$file->contenthash=md5($content);
			$file->createFile($content, $this->repository);
			$this->files->files[]=$file;
			$filesIds[] = $file->id;
			$content = "";
		}
	
	
	}
	
	/***************************************************************************************************************
	 * Rapport Migration
	*/
	public function retrieveRapportMigration(){
		$request1 = "Select LEARNING_CONTEXT.NAME  , LEARNING_CONTEXT.SOURCE_ID , LC_CATEGORY.NAME as CATEGORIE FROM LEARNING_CONTEXT
JOIN LC_CATEGORIZATION  on LEARNING_CONTEXT.PARENT_ID = LC_CATEGORIZATION.LEARNING_CONTEXT_ID
JOIN LC_CATEGORY  on LC_CATEGORIZATION.LC_CATEGORY_ID = LC_CATEGORY.ID
WHERE LEARNING_CONTEXT.ID = '".$this->learningContextId ."' and LEARNING_CONTEXT.TYPE_CODE = 'Section'";
		$stid2 = oci_parse($this->connection,$request1);
		oci_execute($stid2);
		$row = oci_fetch_array($stid2, OCI_ASSOC+OCI_RETURN_NULLS);
		$request2 = "Select NAME , SOURCE_ID FROM LEARNING_CONTEXT 
		WHERE ID = '". $this->learningContextId ."' and TYPE_CODE = 'Section'";
		$this->rapportMigration->fullName = $row["NAME"]  ;
		$this->rapportMigration->shortName = $row["SOURCE_ID"];
		$this->rapportMigration->nomFichier = 'ZZZ_Migration_Report_'.$this->moodle_backup->original_course_shortname.'_'.time().'.xml';
		$this->rapportMigration->categorieCour = $row["CATEGORIE"];
		$fileId = $this->getNextId();
		$contextId = $this->getNextId();
		$this->addRapportMigration($fileId , $contextId);
	}
	
	public function addRapportMigration($fileId , $contextId){
		
		$sectionId = $this->fixedSections[GlobalModel::SECTION_GENERAL];
		
		$resourceModel = new RessourceModel();
		$resourceModel->calendar = new Events(); //vide
		$resourceModel->comments = new Comments(); //vide
		$resourceModel->completion = new ActivityCompletion(); //vide
		$resourceModel->filters = new Filters(); //vide
		$resourceModel->grades = new ActivityGradeBook(); // Vide
		$resourceModel->inforef = new InfoRef(); // A remplir
		$resourceModel->roles = new RolesBackup(); //Vide
		
		$resourceModel->module = $this->createModule($fileId,"resource","2013110500",$sectionId);
		$resourceModel->ressource = $this->createResource($fileId, "Rapport de la migration","rapport" , $resourceModel->module,3);
		$resourceModel->ressource->contextid = $contextId;
		
		$component = "mod_resource";
		$fileArea = "content";
		$itemId = 0;
		$repository = $this->addCMSRepository($contextId, $component, $fileArea, $itemId, "/");	
		$file = $this->createFichierInterne($resourceModel->ressource->name, $contextId, "/");
		$file->id = $fileId;
		$file->component = $component;
		$content = $this->rapportMigration->toHtml();	
		$file->contenthash=md5($content);
		$file->createFile($content, $this->repository);
		$this->files->files[]=$file;
		$resourceModel->ressource->filesIds[] = $repository->id;
		$resourceModel->ressource->filesIds[] = $file->id;
		
		$resourceModel->inforef->fileids = $resourceModel->ressource->filesIds ;
		
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$resourceModel->module->id;
		$activity->sectionid=$sectionId;
		$activity->modulename=$resourceModel->module->modulename;
		$activity->title=$resourceModel->ressource->name;
		$activity->directory="activities/resource_".$resourceModel->ressource->ressourceId;
		
		
		$this->moodle_backup->contents->activities[] = $activity;
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","resource_".$resourceModel->ressource->ressourceId,"resource_".$resourceModel->ressource->ressourceId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","resource_".$resourceModel->ressource->ressourceId,"resource_".$resourceModel->ressource->ressourceId."_userinfo",1);
		
		$this->activities[] = $resourceModel;
		
		$this->sections[$sectionId]->section->sequence[]= $resourceModel->ressource->ressourceId;	
	}
	
	/**
	 * Permet de r�cup�rer le delivryContext du syllabus li� au cours.
	 * 
	 * @return Le delivryContextId su Syllabus.
	 */
	private function recupererDeliveryContextId_Syllabus() {
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE DELIVERY_CONTEXT_ID ='" . $this->deliveryContextId . "' AND
		CE_TYPE_NAME = 'SYLLABUS_TYPE'";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		$res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS );
		return $res ["ID"];
	}
	
	
	
	private function initializeSyllabus() {
		$res = $this->recupererInfo_TableSyllabus ();
		$this->syllabusManager->syllabus = new Syllabus ();
		if($res != NULL){
			$this->syllabusManager->syllabus->_construct ( $res );
			$idSyllabus = $this->syllabusManager->syllabus->id;
			$this->initialiserCourseInfo();
			if ($this->syllabusManager->syllabus->use_source_file_fl == '0') {
				$this->initialiseResource ( $idSyllabus );
				$this->initialiseCustum ( $idSyllabus );
				$this->initialiseCustomHtmlItem ( $idSyllabus );
				$this->initialiserPolicy ( $idSyllabus );
				$this->initialiserCourseReq ( $idSyllabus );
				$this->initialiserLesson ( $idSyllabus );
				//$this->initialiserEducatorInfo ( $idSyllabus );
				$this->initialiserLearningObjectifGroup ( $idSyllabus );
				
			}
		}else{
			echo 'Mauvais Learning_Context';
		}
		
	}
	
	/**
	 * Un programme sera cr�er seulement s'il poss�de au moins 
	 *  une rubrique en plus de CourseInfo et EducatorInfo.
	 *  @return vrai si on peut cr�er un programme.
	 */
	private function verifierCreerProgramme($idSyllabus){
		$request = "SELECT count(*) FROM SYLLITEM WHERE SYLLABUS_ID = '" . $idSyllabus . "' and
				ITEM_TYPE_CD != 'CourseInfo' and ITEM_TYPE_CD != 'EducatorInfo' ";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		$res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS );
//		echo 'count() = ' . $res["COUNT(*)"] ; '</br>';
		return $res["COUNT(*)"] != "0";
		
	}
	
	private  function recupererInfoSyllabus(){
		$res = "";
		if ($this->syllabusManager->syllabus->use_source_file_fl == '0'){
			//$res = $this->syllabusManager->courseInfo->info ();
			// for($i =0 ; $i < count($this->syllabusManager->educatorInfo); $i++)
			// $res = $res . $this->syllabusManager->educatorInfo[$i]->info();
			for($i = 0; $i < count ( $this->syllabusManager->courseReq ); $i ++)
				$res = $res . $this->syllabusManager->courseReq [$i]->info ();
			for($i = 0; $i < count ( $this->syllabusManager->lesson ); $i ++)
				$res = $res . $this->syllabusManager->lesson [$i]->info ();
			for($i = 0; $i < count ( $this->syllabusManager->policy ); $i ++)
				$res = $res . $this->syllabusManager->policy [$i]->info ();
			for($i = 0; $i < count ( $this->syllabusManager->ressource ); $i ++)
				$res = $res . $this->syllabusManager->ressource [$i]->info ();
			for($i = 0; $i < count ( $this->syllabusManager->custumHtmlItem ); $i ++)
				$res = $res . $this->syllabusManager->custumHtmlItem [$i]->info ();
			for($i = 0; $i < count ( $this->syllabusManager->custum ); $i ++)
				$res = $res . $this->syllabusManager->custum [$i]->info ();
			$res = $res . $this->syllabusManager->learningObj->info ();
		}
		return $res;
	}
	private function initialiserCourseInfo() {
		$request = "SELECT * FROM LEARNING_CONTEXT WHERE ID = '" . $this->learningContextId . "'";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		$res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS );
		$nomCour = $res["NAME"];
		$request = "SELECT NAME FROM LEARNING_CONTEXT WHERE ID = '" . $res ["PARENT_ID"] . "'";
		$stid2 = oci_parse ( $this->connection, $request );
		oci_execute ( $stid2 );
		$res2 = oci_fetch_array ( $stid2, OCI_ASSOC + OCI_RETURN_NULLS );
		$this->syllabusManager->courseInfo = new CourseInfo ();
		if($res2 != NULL){
			$this->syllabusManager->courseInfo->_construct ( $nomCour, $res2 ["NAME"] );
		}
		
	}
	private function initialiserLearningObjectifGroup($idSyllabus) {
		$request = "SELECT * FROM SYLLITEM WHERE  SYLLABUS_ID = '" . $idSyllabus . "' AND
		ITEM_TYPE_CD = 'LearningObjectiveGroup'";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		$this->syllabusManager->learningObj = new LearningObj_link ();
		$this->syllabusManager->learningObj->_construct ();
		while ( $res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
			$req = "SELECT * FROM SYLLITEM_LEARNINGOBJ_LINK
					JOIN CMS_LINK on SYLLITEM_LEARNINGOBJ_LINK.ID = CMS_LINK.ID
					JOIN CMS_CONTENT_ENTRY on CMS_LINK.RIGHTOBJECT_ID = CMS_CONTENT_ENTRY.ID
					 where '" . $res ["ID"] . "' = SYLLITEM_LEARNINGOBJ_LINK.SYLLITEM_ID";
			
			$stid2 = oci_parse ( $this->connection, $req );
			oci_execute ( $stid2 );
			
			while ( $res2 = oci_fetch_array ( $stid2, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
				$learningObjectifRub = new LearningObj_linkRub ();
				if($res2 != NULL){
					$learningObjectifRub->_construct ( $res2, $res ["TITLE"] );
				}
			
				// var_dump($learningObjectifRub);
				$this->syllabusManager->learningObj->learningObject [] = clone $learningObjectifRub;
			}
		}
	}
	private function initialiserEducatorInfo($idSyllabus) {
		$request = "SELECT * FROM SYLLITEM WHERE  SYLLABUS_ID = '" . $idSyllabus . "' AND
		ITEM_TYPE_CD = 'EducatorInfo'";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		while ( $res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
			$reqRole = "SELECT * FROM ROLE
					JOIN MEMBER  on ROLE.MEMBER_ID = MEMBER.ID
		   			JOIN PERSON on MEMBER.PERSON_ID = PERSON.ID
		   			WHERE  ROLE.ID = " . $res ["ROLE_ID"];
			$stid2 = oci_parse ( $this->connection, $reqRole );
			oci_execute ( $stid2 );
			$res2 = oci_fetch_array ( $stid2, OCI_ASSOC + OCI_RETURN_NULLS );
			
			$educatorInfo = new EducatorInfoSyllabus ();
			$educatorInfo->_construct ( $res2 );
			$this->syllabusManager->educatorInfo [] = clone $educatorInfo;
		}
	}
	private function initialiserLesson($idSyllabus) {
		$request = "SELECT * FROM SYLLITEM WHERE  SYLLABUS_ID = '" . $idSyllabus . "' AND
		ITEM_TYPE_CD = 'Lesson'";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		while ( $res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
			$lesson = new Lesson ();
			$lesson->_construct ();
			$request2 = "SELECT * FROM SYLLITEM_DETAIL WHERE  SYLLITEM_ID = '" . $res ["ID"] . "'";
			$stid2 = oci_parse ( $this->connection, $request2 );
			oci_execute ( $stid2 );
			while ( $res2 = oci_fetch_array ( $stid2, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
				if($res2 != NULL){
					if ($res2 ["NAME"] == "syllabus.label.lesson.topics") {
						$lesson->lessonTopic->_construct ( $res, $res2 );
					} elseif ($res2 ["NAME"] == "syllabus.label.lesson.readings") {
						$lesson->lessonReadings->_construct ( $res, $res2 );
					} elseif ($res2 ["NAME"] == "syllabus.label.lesson.assignments") {
						$lesson->lessonAssignements->_construct ( $res, $res2 );
					} elseif ($res2 ["NAME"] == "syllabus.label.lesson.goals") {
						$lesson->lessonGoals->_construct ( $res, $res2 );
					}
				}
				
			}
			$this->syllabusManager->lesson [] = clone $lesson;
		}
	}
	private function initialiserCourseReq($idSyllabus) {
		$request = "SELECT * FROM SYLLITEM WHERE  SYLLABUS_ID = '" . $idSyllabus . "' AND
		ITEM_TYPE_CD = 'CourseReq'";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		while ( $res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
			$courseReq = new CourseReq ();
			$courseReq->_construct ();
			$request2 = "SELECT * FROM SYLLITEM_DETAIL WHERE  SYLLITEM_ID = '" . $res ["ID"] . "'";
			$stid2 = oci_parse ( $this->connection, $request2 );
			oci_execute ( $stid2 );
			while ( $res2 = oci_fetch_array ( $stid2, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
				if($res2 != NULL){
					if ($res2 ["NAME"] == "syllabus.label.requirements.introduction") {
						$courseReq->courseReqIntro->_construct ( $res, $res2 );
					} elseif ($res2 ["NAME"] == "syllabus.label.requirements.requirements") {
						$courseReq->courseReqReqs->_construct ( $res, $res2 );
					}
				}
				
			}
			$this->syllabusManager->courseReq [] = clone $courseReq;
		}
	}
	private function initialiserPolicy($idSyllabus) {
		$request = "SELECT * FROM SYLLITEM WHERE  SYLLABUS_ID = '" . $idSyllabus . "' AND
		ITEM_TYPE_CD = 'Policy'";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		while ( $res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
			$policy = new Policy ();
			$policy->_construct ();
			$request2 = "SELECT * FROM SYLLITEM_DETAIL WHERE  SYLLITEM_ID = '" . $res ["ID"] . "'";
			$stid2 = oci_parse ( $this->connection, $request2 );
			oci_execute ( $stid2 );
			while ( $res2 = oci_fetch_array ( $stid2, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
				if($res2 != NULL){
					if ($res2 ["NAME"] == "syllabus.label.policy.introduction") {
						$policy->policyIntro->_construct ( $res, $res2 );
					} elseif ($res2 ["NAME"] == "syllabus.label.policy.additionalInformation") {
						$policy->policyAddReq->_construct ( $res, $res2 );
					}
				}
				
			}
			$this->syllabusManager->policy [] = clone $policy;
		}
	}
	private function initialiseCustomHtmlItem($idSyllabus) {
		$request = "SELECT * FROM SYLLITEM WHERE  SYLLABUS_ID = '" . $idSyllabus . "' AND
		ITEM_TYPE_CD = 'Custom HTML Item'";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		while ( $res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
			$request2 = "SELECT * FROM SYLLSUBITEM WHERE  SYLLITEM_ID = '" . $res ["ID"] . "'";
			$stid2 = oci_parse ( $this->connection, $request2 );
			oci_execute ( $stid2 );
			$res2 = oci_fetch_array ( $stid2, OCI_ASSOC + OCI_RETURN_NULLS );
			$customHtml = new CustumHtmlItem ();
			if($res2 != NULL){
				$customHtml->_construct ( $res, $res2 );
			}
			
			$this->syllabusManager->custumHtmlItem [] = clone $customHtml;
		}
	}
	private function initialiseCustum($idSyllabus) {
		$request = "SELECT * FROM SYLLITEM WHERE  SYLLABUS_ID = '" . $idSyllabus . "' AND
		ITEM_TYPE_CD = 'Custom'";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		while ( $res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
			$request2 = "SELECT * FROM SYLLSUBITEM WHERE  SYLLITEM_ID = '" . $res["ID"] . "'";
			$stid2 = oci_parse ( $this->connection, $request2 );
			oci_execute ( $stid2 );
			$res2 = oci_fetch_array ( $stid2, OCI_ASSOC + OCI_RETURN_NULLS );
			$custom = new Custum ();
			if($res2 != NULL){
				$custom->_construct ( $res, $res2 );
			}
			
			$this->syllabusManager->custum [] = clone $custom;
		}
	}
	private function initialiseResource($idSyllabus) {
		$request = "SELECT * FROM SYLLITEM WHERE  SYLLABUS_ID = '" . $idSyllabus . "' AND
		ITEM_TYPE_CD = 'Resource'";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		while ( $res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
			$request2 = "SELECT * FROM SYLLSUBITEM WHERE  SYLLITEM_ID = '" . $res ["ID"] . "'";
			$stid2 = oci_parse ( $this->connection, $request2 );
			oci_execute ( $stid2 );
			$res2 = oci_fetch_array ( $stid2, OCI_ASSOC + OCI_RETURN_NULLS );
			$resource = new Ressource ();
			if($res2 != NULL){
				$resource->_construct ( $res, $res2 );
			}
			
			$this->syllabusManager->ressource [] = clone $resource;
		}
	}
	
	/**
	 * Permet de r�cup�rer les informations de la table SYLLABUS pour un cour en particulier.
	 * 
	 * @return Le contenu de la table Syllabus li� au cour.
	 */
	private function recupererInfo_TableSyllabus() {
		$deliveryContextId_Syllabus = $this->recupererDeliveryContextId_Syllabus ();
		$request = "SELECT * FROM SYLLABUS WHERE ID ='" . $deliveryContextId_Syllabus . "'";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute ( $stid );
		$res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS );
		return $res;
	}
	
	private function recupererOriginalContentId($deliveryContextId){
		$request = "SELECT CMS_LINK.RIGHTOBJECT_ID FROM CMS_CONTENT_ENTRY
			JOIN CMS_LINK on CMS_LINK.LEFTOBJECT_ID = CMS_CONTENT_ENTRY.ID
			WHERE CMS_CONTENT_ENTRY.DELIVERY_CONTEXT_ID = ". $deliveryContextId ."
			and CMS_LINK.LINK_TYPE_ID = '30004' and CMS_CONTENT_ENTRY.CE_TYPE_NAME = 'SYLLABUS_TYPE' ";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute($stid);
		$res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS );
		$request = "SELECT ORIGINAL_CONTENT_ID FROM CMS_CONTENT_ENTRY WHERE ID = '". $res["RIGHTOBJECT_ID"] ."'";
		$stid = oci_parse ( $this->connection, $request );
		oci_execute($stid);
		$res = oci_fetch_array ( $stid, OCI_ASSOC + OCI_RETURN_NULLS );
		return $res["ORIGINAL_CONTENT_ID"];
	}
	
	private function creationCssForum(){
		$style = "<style>
  .entrydiv {
      background: none repeat scroll 0% 0% #EDEDED;
      padding: 3px 6px 6px;
      clear: both;
      overflow: visible;
      margin-bottom: 9px;
      border: 1px solid #AAA;
      
  }
  .entrydiv table tr td.rightcolumn {
      width: 100% !important;
      text-align: right !important;
  }
  .rightcolumn {
      float: right;
      padding: 1px 0px;
      width: 100%;
      overflow: visible;
      text-align: left;
      font-size: 85%;
  }
  .entrydiv table {
      border-collapse: collapse;
      font-size: 100%;
  }
  .entrydiv table tr td {
      
      padding: 2px 1px;
      font-size: 85%;
  }
  .entrytext {
      background: none repeat scroll 0% 0% #FFF;
      padding: 6px;
      margin-bottom: 6px;
      clear: both;
  }
body {
    color: #000;
	background-color:  #BBD2E1
}
body {
    font-family: Verdana,Arial,Helvetica,sans-serif;
    font-size: 0.8em;
}
  </style>";
		return $style;		
	}
	

	private function createFolderInterne($nom_categorie, $contextid, &$filesIds, $path) {
		$component = "mod_folder";
		$fileArea = "content";
		$itemId = 0;
		$repository = $this->addCMSRepository ( $contextid, $component, $fileArea, $itemId, $path );
		$filesIds [] = $repository->id;
	}
private function createFichierAssocie($contextid, $path, &$filesIds , $fileGroupId) {
		//var_dump($fileGroupId);
		$req = "SELECT  sf.NAME,sf.FILESIZE,cms.CONTENT,CMS_MIMETYPE.MIMETYPE
						 from SIMPLE_FILE sf 
						JOIN CMS_FILE_CONTENT cms on sf.FILE_CONTENT_ID = cms.ID
						JOIN CMS_MIMETYPE  ON CMS_MIMETYPE.ID = cms.MIMETYPE_ID
						where  sf.GROUP_ID = '" . $fileGroupId . "'";
		$stid2 = oci_parse ( $this->connection, $req );
		oci_execute ( $stid2 );
		//echo '</br> ' . $req . '</br>';
		$res = oci_fetch_array ( $stid2, OCI_ASSOC + OCI_RETURN_NULLS );
		//var_dump($res);
		global $USER;
		$component = "mod_folder";
		$fileArea = "content";
		$itemId = 0;
		
		$file = new FileBackup ();
		$file->id = $this->getNextId ();
		$file->contextid = $contextid;
		$file->component = $component;
		$file->filearea = $fileArea;
		$file->itemid = $itemId;
		$file->filepath = $path;
		$file->filename = $res ["NAME"];
		$file->userid = $USER->id;
		$file->filesize = $res ["FILESIZE"];
		$file->author = "Admin User";
		$file->license = "allrightsreserved";
		$file->sortorder = 0;
		$file->repositorytype = "$@NULL@$";
		$file->repositoryid = "$@NULL@$";
		$file->reference = "$@NULL@$";
		$file->status = 0;
		$file->timecreated = time ();
		$file->timemodified = time ();
		$file->source = $res ["NAME"];
		$file->mimetype = $res["MIMETYPE"];
		
		$content =  $res["CONTENT"]->load();
		$file->contenthash = md5( $content);
		$file->createFile ( $content, $this->repository );
		$this->files->files [] = $file;
		$filesIds [] = $file->id;
		return $file;
	}
	private function createFichierInterne($nomFichier, $contextid, $path) {
		global $USER;
		$component = "mod_folder";
		$fileArea = "content";
		$itemId = 0;
		
		$file = new FileBackup ();
		$file->id = $this->getNextId ();
		$file->contextid = $contextid;
		$file->component = $component;
		$file->filearea = $fileArea;
		$file->itemid = $itemId;
		$file->filepath = $path;
		$file->filename = $nomFichier . '.html';
		$file->userid = $USER->id;
		$file->filesize = 0;
		$file->author = "Admin User";
		$file->license = "allrightsreserved";
		$file->sortorder = 0;
		$file->repositorytype = "$@NULL@$";
		$file->repositoryid = "$@NULL@$";
		$file->reference = "$@NULL@$";
		$file->status = 0;
		$file->timecreated = time ();
		$file->timemodified = time ();
		$file->source = $nomFichier;
		$file->mimetype = 'text/html';
		return $file;
	}
	

	private function creationTableMatiere($nameTopic){
		
		$request = "Select cm2.NAME as NAME_CATEGORIE , cm1.NAME as NAME_TOPIC, msg.ROOT_MESSAGE_ID , msg.SUBJECT
				from CMS_CONTENT_ENTRY cm1
				JOIN CMS_CONTENT_ENTRY cm2 on cm2.ID = cm1.PARENT_ID
				JOIN DIS_MESSAGE msg on msg.TOPIC_ID = cm1.ID
				WHERE cm1.DELIVERY_CONTEXT_ID = '" . $this->deliveryContextId . "' and
						cm1.NAME = :nameTopic and cm1.CE_TYPE_NAME = 'DISCUSSION_TOPIC_TYPE' 
				order by NAME_CATEGORIE , NAME_TOPIC , msg.ROOT_MESSAGE_ID, msg.POSTDATE";
		$stid = oci_parse ( $this->connection, $request );
		oci_bind_by_name($stid, ":nameTopic", $nameTopic);
		oci_execute ( $stid );
		$rootMsgId = "";
		$content = "<h2> Table des mati�res </h2> <ul>";
		while($res = oci_fetch_array( $stid, OCI_ASSOC + OCI_RETURN_NULLS )){
			if($rootMsgId != $res["ROOT_MESSAGE_ID"]){
				$rootMsgId = $res["ROOT_MESSAGE_ID"];
				$content = $content . "<li> <a href=\"#". utf8_decode($res["SUBJECT"]) . "\"> " . utf8_decode($res["SUBJECT"]) . "</a></li>";
			}
		}
	
		$content = $content . '</ul>';
		return $content;
	}
}
