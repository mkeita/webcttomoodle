<?php

require_once 'classes/model/GlobalModel.php';

class WebCTModel extends \GlobalModel {
	
	private $connection;
	
	private $learningContextId = "366249217001";
	
	private $deliveryContextId;
		
	/* (non-PHPdoc)
	 * @see GlobalModel::__construct()
	 */
	public function __construct($learningContextId) {
		$this->learningContextId = $learningContextId;
		parent::__construct();
		
		//TODO TEMPORARY DESACTIVATE GLOSSARIES EXTRACT
		//$this->retrieveGlossaries();

		//$this->retrieveQuestions();
		
		
		$this->retrieveQuizzes();
		
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
			var_dump($e);
			trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		}
		
		//Get this section DELEVERY_CONTEXT ID!!
		$requestDeliveryContext = "SELECT TEMPLATE_ID FROM CO_LC_ASSIGNMENT WHERE LEARNING_CONTEXT_ID='".$this->learningContextId. "'";
		$stidDeliveryContext = oci_parse($this->connection,$requestDeliveryContext);
		oci_execute($stidDeliveryContext);
		$deliveryContext = oci_fetch_array($stidDeliveryContext, OCI_ASSOC+OCI_RETURN_NULLS);
		$this->deliveryContextId = $deliveryContext["TEMPLATE_ID"];
		
	}
	
	public function initializeMoodleBackupModel(){
		parent::initializeMoodleBackupModel();
		
		$request = "SELECT * FROM LEARNING_CONTEXT WHERE ID ='".$this->learningContextId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
		
		$shortName = $row['SOURCE_ID'];		
		if(substr($shortName,-7,7)!=".default"){ //On en
			$shortName = $row['ID'];
		}else {
			$shortName =substr($shortName,0,-7);
		} 
				
		$this->moodle_backup->original_course_id = $row['ID'];
		$this->moodle_backup->original_course_fullname = $row['NAME'];//WebCt Course 0";
		$this->moodle_backup->original_course_shortname = $shortName;//WEBCT-0";
		$this->moodle_backup->name = "backup-".$this->moodle_backup->original_course_shortname.".mbz"; //test_backup.mbz
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
		$glossary->contextid=0;
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
					
						
			$entry = new Entry();
			$entry->id=$row1['ORIGINAL_CONTENT_ID'];// 		id="1">
			$entry->userid=$USER->id;// 		<userid>2</userid>
			$entry->concept=$row1['NAME'];// 		<concept>Entry1</concept>
			
			
			$description = $row1['DESCRIPTION'];
			$completeDescription = $description->load();
			
			$filesName = array();
			$convertedDescription =$this->convertTextAndCreateAssociedFiles($completeDescription,2, $entry, $glossary); 
						
			$entry->definition =$convertedDescription;// 		<definition>&lt;p&gt;Entry 1 of glossary&lt;/p&gt;</definition>
			
			$entry->sourceglossaryid=0;// 		<sourceglossaryid>0</sourceglossaryid>
					
			$entry->definitionformat=1;// 		<definitionformat>1</definitionformat>
			$entry->definitiontrust=0;// 		<definitiontrust>0</definitiontrust>
			$entry->timecreated=time();// 		<timecreated>1390818856</timecreated>
			$entry->timemodified=time();// 		<timemodified>1390818883</timemodified>
			$entry->teacherentry=1;// 		<teacherentry>1</teacherentry>
			$entry->usedynalink=0;// 		<usedynalink>0</usedynalink>
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
				$this->addFile($row3['RIGHTOBJECT_ID'],1,$entry,$glossary);
			}
			
			$glossary->entries[]=$entry;
		
		}	
	
		return $glossary;
	}
	
	
	function convertHTMLContentLinks($htmlContent,  &$filesNames){
		
		$findWebCT   = '/webct/RelativeResourceManager/';	
		$pos1 = strpos($htmlContent, $findWebCT);
		$findQuot   = '"';
		
		//error_log("HTMLCONTENT = ".$htmlContent, 0);
		
		while($pos1>0){
			
			$pos2 = strpos($htmlContent, $findQuot, $pos1);
				
			$formerLink = substr($htmlContent, $pos1,$pos2-$pos1);
			
			$lastSlashPos = strrpos($formerLink, "/");
			
			$fileName = substr($formerLink, $lastSlashPos+1);
			
			$filesNames[] =  $fileName; 
			
			$newLink = "@@PLUGINFILE@@/".$fileName;
				
			$htmlContent = str_replace($formerLink, $newLink, $htmlContent);
		
			//$htmlContent = convertHTMLContentLinks($htmlContent, $filesNames);
			$newPos1 = strpos($htmlContent, $findWebCT);
			if($pos1==$newPos1){
				break;
			}else {
				$pos1 = $newPos1;
			}
			//error_log("POS = ".$pos1, 0);
		}	
		
		return $htmlContent;
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
	 * 
	 * @return mixed
	 */
	public function convertTextAndCreateAssociedFiles($text,$mode,$item,$parent=NULL){
		$filesName = array();
		$convertedText = $this->convertHTMLContentLinks($text,$filesName);
		foreach ($filesName as $fileName){
			$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE NAME ='".$fileName."' AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
			$stid = oci_parse($this->connection,$request);
			oci_execute($stid);
			$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
			if(!empty($row)){
				error_log($fileName,0);
				$this->addFile($row["ORIGINAL_CONTENT_ID"], $mode, $item, $parent);
			}
		}
		
		return $convertedText;
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
	 * @param unknown $item
	 * @param unknown $parent
	 */
	public function addFile($fileOriginalContentId, $mode, $item, &$parent=NULL){
		
		$fileArea = "";
		$component ="";
		switch ($mode){
			case 1 : 
				$component = "mod_glossary";
				$fileArea = "attachment";
				break;
			case 2:
				$component = "mod_glossary";
				$fileArea = "entry";
				break;
				
			case 3:
				$component = "question";
				$fileArea = "questiontext";
				break;
			case 4:
				$component = "question";
				$fileArea = "generalfeedback";
				break;
			case 5:
				$component = "question";
				$fileArea = "answer";
				break;
			case 6:
				$component = "question";
				$fileArea = "answerfeedback";
				break;
			case 7:
				$component = "qtype_match";
				$fileArea = "subquestion";
				break;				
			case 8:
				$component = "qtype_essay";
				$fileArea = "graderinfo";
				break;
				
				
				
		}
				
		$repository = new FileBackup();
		$repository->id=$this->filesCount++;
		$repository->contenthash="";// 		<contenthash>da39a3ee5e6b4b0d3255bfef95601890afd80709</contenthash>
		$repository->contextid=0;// 		<contextid>54</contextid> // ACTIVITY -- ICI GLOSSARY CONTEXT
		$repository->component=$component;// 		<component>mod_glossary</component>
		$repository->filearea=$fileArea;// 		<filearea>attachment</filearea>
		$repository->itemid=$item->id;// 		<itemid>1</itemid> //GLOSSARY ID
		$repository->filepath="/";// 		<filepath>/</filepath>
		$repository->filename=".";// 		<filename>.</filename>
		$repository->userid=$this->users->users[0]->id;// 		<userid>2</userid>
		$repository->filesize=0;// 		<filesize>0</filesize>
		$repository->mimetype="document/unknown";// 		<mimetype>document/unknown</mimetype>
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
			
		
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE ORIGINAL_CONTENT_ID ='".$fileOriginalContentId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
				
		if(empty($row)){
			//No element found!!
			return;
		}
		
		$file = new FileBackup();
		$file->id=$this->filesCount++;
		$file->contextid=0;// 		<contextid>54</contextid>
		$file->component=$component;// 		<component>mod_glossary</component>
		$file->filearea=$fileArea;// 		<filearea>attachment</filearea>
		$file->itemid=$item->id;// 		<itemid>1</itemid>
		$file->filepath="/";// 		<filepath>/</filepath>
		$file->filename=$row['NAME'];// 		<filename>.</filename>
		$file->userid=$this->users->users[0]->id;// 		<userid>2</userid>
		$file->filesize=$row['FILESIZE'];// 		<filesize>0</filesize>
		$file->author=$this->users->users[0]->firstname." ".$this->users->users[0]->lastname;// 		<author>$@NULL@$</author>
		$file->license="allrightsreserved";// 		<license>$@NULL@$</license>
		$file->sortorder=0;// 		<sortorder>0</sortorder>
		$file->repositorytype="$@NULL@$";// 		<repositorytype>$@NULL@$</repositorytype>
		$file->repositoryid="$@NULL@$";// 		<repositoryid>$@NULL@$</repositoryid>
		$file->reference="$@NULL@$";// 		<reference>$@NULL@$</reference>
		$file->status=0;// 		<status>0</status>
		$file->timecreated=time();// 		<timecreated>1390818824</timecreated>
		$file->timemodified=time();// 		<timemodified>1390818869</timemodified>
		$file->source=$row['NAME'];// 		<source>$@NULL@$</source>
		

		//GET THE CONTENT
		$request = "SELECT * FROM CMS_FILE_CONTENT WHERE ID='".$row['FILE_CONTENT_ID']."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
		
		echo 'FILE='.$file->filename . "///".$fileOriginalContentId."\n";
		$file->content = $row["CONTENT"]->load();

		$file->contenthash=md5($file->content);// 		<contenthash>da39a3ee5e6b4b0d3255bfef95601890afd80709</contenthash>
		
		
		//RETRIEVE THE MIME TYPE
		$request = "SELECT * FROM CMS_MIMETYPE WHERE ID='".$row['MIMETYPE_ID']."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
		
		$file->mimetype=$row['MIMETYPE'];// 		<mimetype>document/unknown</mimetype>
			
		//$filename = "C:/Users/Marc/Documents/ComVerbale.odt" ;
		//$file->content = file_get_contents($filename);
		
		//REFERENCE IN THE GLOSSARY
		switch ($mode){
			case 1:
			case 2:
				$parent->filesIds[] = $repository->id;		
				$parent->filesIds[] = $file->id;
			break;
		}
		//REFERENCE IN THE COURSE FILES
		$this->files->files[]=$repository;
		$this->files->files[]=$file;
		
				
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
				$question=null;
	
				if($row1['CE_SUBTYPE_NAME']=='MultipleChoice'){ //MULTICHOICE
					$question = new MultiChoiceQuestion();
					$question->id = $row1['ORIGINAL_CONTENT_ID'];
					$question->parent= 0;//$questionCategory->id;
					$question->name=$row1['NAME'];
					
					$this->fillMutipleChoiceQuestion($question, $row1['FILE_CONTENT_ID']);
					$questionCategory->questions[]=$question;
				}else if($row1['CE_SUBTYPE_NAME']=='ShortAnswer'){ //
					$question = new ShortAnswerQuestion();
					$question->category = $questionCategory;
				} else if($row1['CE_SUBTYPE_NAME']=='FillInTheBlank'){ //
					$question = new FillInBlankQuestion();
					$question->category = $questionCategory;
				}else if($row1['CE_SUBTYPE_NAME']=='Matching'){ //
					$question = new MatchingQuestion();
					$question->category = $questionCategory;
				}else if($row1['CE_SUBTYPE_NAME']=='Paragraph'){ //
					$question = new ParagraphQuestion();
					$question->category = $questionCategory;
				}else if($row1['CE_SUBTYPE_NAME']=='TrueFalse'){ //
					$question = new TrueFalseQuestion();
					$question->category = $questionCategory;
				}else if($row1['CE_SUBTYPE_NAME']=='Calculated'){ //
					$question = new CalculatedQuestion();
					$question->category = $questionCategory;
				}else if($row1['CE_SUBTYPE_NAME']=='CombinationMultipleChoice'){ //
					$question = new CombinaisonMultiChoiceQuestion();
					$question->category = $questionCategory;
				}else if($row1['CE_SUBTYPE_NAME']=='JumbledSentence'){ //
					$question = new JumbledSentenceQuestion();
					$question->category = $questionCategory;
				}
				if(empty($question)){
					continue;					
				}
				$question->id = $row1['ORIGINAL_CONTENT_ID'];
				$question->parent= 0;//$questionCategory->id;
				$question->name=$row1['NAME'];
					
				$this->fillQuestion($question, $row1['FILE_CONTENT_ID']);
				$questionCategory->questions[]=$question;
				
				
				//break;
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
	
		$content = $row["CONTENT"]->load();
	
		//PARSE THE XML FILE AND RETREIVE THE NEEDED INFORMATION
		$xmlContent = new SimpleXMLElement($content);
		$xmlContent->registerXPathNamespace("ims", "http://www.imsglobal.org/xsd/ims_qtiasiv1p2");
	
		if($question instanceof MultiChoiceQuestion || $question instanceof ShortAnswerQuestion
			|| $question instanceof MatchingQuestion || $question instanceof ParagraphQuestion
			|| $question instanceof TrueFalseQuestion || $question instanceof CombinaisonMultiChoiceQuestion){
			//QUESTION TEXT
			$questionText ="";
			
			if(strlen($question->name)>255){
				echo 'QUESTION NAME TOO LONG - '.$question->name;
				$questionText .= $question->name."<br/>";

				$question->name = substr($question->name, 252)."...";
			}			
			
			$questionText .= $xmlContent->presentation->flow->material->mattext;
			$convertedDescription = $this->convertTextAndCreateAssociedFiles($questionText,3, $question);
		
			//TODO
			//CAS PARTICULIER où la question est dans le nom !!!
			//if(empty($convertedDescription)){
			//	$convertedDescription = $question->name;
			//}
			
			//Get the file attached if any and past it to the description
			$imageName = $xmlContent->presentation->flow->material->matimage;
			$imageURI = $xmlContent->presentation->flow->material->matimage['uri'];
			$findContentId   = '?contentID=';
			$pos = strpos($imageURI, $findContentId);
			if($pos>0){
				// 			echo 'IMAGE NAME = '.$imageName."\n";
				// 			echo 'IMAGE URI = '.$imageURI."\n";
				$fileContentId = substr($imageURI, $pos+11);
				$this->addFile($fileContentId, 3, $question);
		
				$convertedDescription .= "<br/><img src=\"@@PLUGINFILE@@/".$imageName."\"/>";
			}
			$question->questiontext=$convertedDescription;
		}
			
		$question->questiontextformat="1";// 		<questiontextformat>1</questiontextformat>
	
		//GENERAL FEEDBACK TEXT
		if(!empty($xmlContent->itemfeedback->flow_mat)){
			$generalFeedbackText = $xmlContent->itemfeedback->flow_mat->material->mattext;
			$convertedDescription = $this->convertTextAndCreateAssociedFiles($generalFeedbackText,4, $question);
			$question->generalfeedback=$convertedDescription;// 		<generalfeedback>&lt;p&gt;&amp;lt;P&amp;gt;&amp;lt;FONT COLOR="#000000"&amp;gt;1 mole de HNO&amp;lt;sub&amp;gt;3&amp;lt;/sub&amp;gt; &lt;/p&gt;</generalfeedback>
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
	
		//attention aux classes qui héritent !!
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
		$multichoice->correctfeedback=utf8_encode('Votre réponse est correcte.');//             <correctfeedback>&lt;p&gt;Your answer is correct.&lt;/p&gt;</correctfeedback>
		$multichoice->correctfeedbackformat="1";//             <correctfeedbackformat>1</correctfeedbackformat>
		$multichoice->partiallycorrectfeedback=utf8_encode('Votre réponse est partiellement correcte.');//             <partiallycorrectfeedback>&lt;p&gt;Your answer is partially correct.&lt;/p&gt;</partiallycorrectfeedback>
		$multichoice->partiallycorrectfeedbackformat="1";//             <partiallycorrectfeedbackformat>1</partiallycorrectfeedbackformat>
		$multichoice->incorrectfeedback=utf8_encode('Votre réponse est incorrecte.');//             <incorrectfeedback>&lt;p&gt;Your answer is incorrect.&lt;/p&gt;</incorrectfeedback>
		$multichoice->incorrectfeedbackformat="1";//             <incorrectfeedbackformat>1</incorrectfeedbackformat>
		$multichoice->shownumcorrect="1";//             <shownumcorrect>1</shownumcorrect>

		$question->multiChoice = $multichoice;
		
		
		$count = 0;
		foreach ($xmlContent->presentation->flow->response_lid->render_choice->flow_label->response_label as $response_label){

			$webctAnswerId = $response_label['ident'];
			
			$answer = new Answer();
			$answer->id=$count++;// 		id="4">
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
					}
				}
			}
		}		
		$question->shorAnswer = $shortanswer;
		
		
		if($isShortAnswer){ //On crée vraiment une short Answer
			//UNIQUEMENT AVEC REPONSE UNIQUE...
		
			$count = 0;
			
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
					echo "EXTENSION = ".$answerText."<br/>";
				}else if(!empty($varSubset)){
					$answerText = "*".$varSubset."*";
					echo "CONTAIN = ".$answerText."<br/>";
				}
				
				if(empty($answerText)){
					continue;
				}
				
				$answer = new Answer();
				$answer->id=$count++;// 		id="4"				
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
			echo "MULTI ANSWERS";
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
			foreach ($responseStrList as $response_str){
				$responseId = $response_str['ident'];				
				$count++;
				
				$finalText = $finalText."<li>{#".$count."}</li>";

				
				//Add a short answer question..
				$shortAnswerQuestion = new ShortAnswerQuestion();
				$shortAnswerQuestion->id = $question->id+$count;
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
				
				$shortAnswerQuestion->shorAnswer = $shortAnswer;
				
				//Answers
				$count2 = 0;
					
				//$xmlContent->registerXPathNamespace("n", "http://www.imsglobal.org/xsd/ims_qtiasiv1p2");
				//$xmlContent->registerXPathNamespace("webct", "http://www.webct.com/vista/assessment");
				//var_dump($xmlContent->xpath('/n:item'));
				$shortAnswerQuestionText ="";
				
				$maxScore = 0;
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
							echo "EXTENSION = ".$answerText."<br/>";
						}						
						
					}else if(!empty($varSubset)){
						if((string)$varSubset['respident']==(string)$responseId){
							$answerText = "*".$varSubset."*";
							echo "CONTAIN = ".$answerText."<br/>";
						}						

					}
				
					if(empty($answerText)){
						continue;
					}
				
					$answer = new Answer();
					$answer->id=$count2++;// 		id="4"
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
					echo 'PROBLEME AVEC PONDERATION DE '.$shortAnswerQuestion->name." -- ".$responseId."<br/>";
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
				$question->category->questions[] = $shortAnswerQuestion; 
				$multiAnswer->sequence[]=$shortAnswerQuestion->id;
				
			}
			$finalText = $finalText."</ol>";
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
			echo 'QUESTION NAME TOO LONG - '.$question->name, PHP_EOL;
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
					$imageName = $child->matimage;
					$imageURI = $child->matimage['uri'];
					$findContentId   = '?contentID=';
					$pos = strpos($imageURI, $findContentId);
					if($pos>0){
						$fileContentId = substr($imageURI, $pos+11);
						$this->addFile($fileContentId, 3, $question);
					
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
				$shortAnswerQuestion->id = $question->id+$count;
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
				
				$shortAnswerQuestion->shorAnswer = $shortAnswer;
				
				//Answers
				$count2 = 0;
				
				$shortAnswerQuestionText ="";
				
				$maxScore = 0;
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
							echo "EXTENSION = ".$answerText."<br/>";
						}
				
					}else if(!empty($varSubset)){
						if((string)$varSubset['respident']==(string)$responseId){
							$answerText = "*".$varSubset."*";
							echo "CONTAIN = ".$answerText."<br/>";
						}
				
					}
				
					if(empty($answerText)){
						continue;
					}
				
					$answer = new Answer();
					$answer->id=$count2++;// 		id="4"
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
					echo 'PROBLEME AVEC PONDERATION DE '.$shortAnswerQuestion->name." -- ".$responseId."<br/>";
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
				$question->category->questions[] = $shortAnswerQuestion;
				$multiAnswer->sequence[]=$shortAnswerQuestion->id;
				
				
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
					echo "PROBLEME DE GRADE -> ICI  = ". $qtimetadatafield->fieldentry ." - Question = ".$question->name. "<br/>" ;
				}
				break;
			}
		}
		
		$matches = new Matches();
		
		//IDEM MULTICHOICE
		$matchOptions = new MatchOptions();
		$matchOptions->id=1;
		$matchOptions->shuffleanswers=1;// 		<shuffleanswers>1</shuffleanswers>
		$matchOptions->correctfeedback=utf8_encode('Votre réponse est correcte.');// 		<correctfeedback>&lt;p&gt;Your answer is correct.&lt;/p&gt;</correctfeedback>
		$matchOptions->correctfeedbackformat=1;// 		<correctfeedbackformat>1</correctfeedbackformat>
		$matchOptions->partiallycorrectfeedback=utf8_encode('Votre réponse est partiellement correcte.');;// 		<partiallycorrectfeedback>&lt;p&gt;Your answer is partially correct.&lt;/p&gt;</partiallycorrectfeedback>
		$matchOptions->partiallycorrectfeedbackformat=1;// 		<partiallycorrectfeedbackformat>1</partiallycorrectfeedbackformat>
		$matchOptions->incorrectfeedback=utf8_encode('Votre réponse est incorrecte.');// 		<incorrectfeedback>&lt;p&gt;Your answer is incorrect.&lt;/p&gt;</incorrectfeedback>
		$matchOptions->incorrectfeedbackformat=1;// 		<incorrectfeedbackformat>1</incorrectfeedbackformat>
		$matchOptions->shownumcorrect=1;// 		<shownumcorrect>1</shownumcorrect>		
		
		$matches->matchOptions = $matchOptions;		
		
		$count=0;
		$lastAnswerText ="";
		foreach ($xmlContent->xpath('//ims:response_grp') as $response_grp){
			
			$match = new Match();
			$match->id = $count++;
			
			$filesName = array();
			$convertedText = $this->convertTextAndCreateAssociedFiles((string)$response_grp->material->mattext,7, $match); 
			$match->questiontext = $convertedText;
			
			$match->questiontextformat =1 ;

			$machtText = "";
			foreach ($response_grp->render_choice->flow_label->response_label as $response_label){
				if(substr($response_label['ident'],0,2)!="NO"){
					$machtText = $response_label->material->mattext;
					break;
				}
			}
					
			$filesName = array();
			$convertedText = $this->convertTextAndCreateAssociedFiles($machtText,7, $match);				
			$lastAnswerText = $convertedText;
			$match->answertext = $convertedText;
			
			$matches->matches[]=$match;
		}
		//ADD the last answer, one more time but without the question..
		$lastMatch = new Match();
		$lastMatch->id = $count++;
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
	
		$essay = new Essay();
		
		$essay->id=1;
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
		$trueFalseAnswer->id = 1;
		
		$trueAnswer = new Answer();
		$trueAnswer->id="1";
		$trueAnswer->answertext="True";
		$trueAnswer->answerformat="0";
		$trueAnswer->feedback="";
		$trueAnswer->feedbackformat="1";

		$falseAnswer = new Answer();
		$falseAnswer->id="2";
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
			$imageName = $imageNames[0];
			
			$imageURI = $imageName['uri'];
			$findContentId   = '?contentID=';
			$pos = strpos($imageURI, $findContentId);
			if($pos>0){
				// 			echo 'IMAGE NAME = '.$imageName."\n";
				// 			echo 'IMAGE URI = '.$imageURI."\n";
				$fileContentId = substr($imageURI, $pos+11);
				$this->addFile($fileContentId, 3, $question);
			
				$convertedDescription .= "<br/><img src=\"@@PLUGINFILE@@/".$imageName."\"/>";
			}
		}
		$question->questiontext=$convertedDescription;

		
		$answer = new Answer();
		$answer->id="1";
		
		$matExtension = $xmlContent->presentation->flow->material->mat_extension;

		$calulatedChild = $matExtension->children('http://www.webct.com/vista/assessment');
		
		$answer->answertext=$this->convertFormula($calulatedChild->calculated->formula);
		$answer->answerformat=0;
		$answer->fraction="1.0000000";
		$answer->feedback="";
		$answer->feedbackformat=1;

		$question->answers[]=$answer;
		$countItem = 0;
		$countDataset=0;
		foreach ($calulatedChild->calculated->var as $var){
			$name = $var['name'];
			$datasetDefinition = new DatasetDefinition();
			$datasetDefinition->id = $countDataset++;
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
		$numericalOption->id=1;
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
		$calculatedRecord->id=1;
		$calculatedRecord->answer=$answer->id;
		if((string)$toleranceType=="Unit"){
			$calculatedRecord->tolerancetype=2;
			$calculatedRecord->tolerance=$tolerance;
		}else {
			echo " - SPECIAL TOLERANCE = ".$toleranceType. "-".$tolerance; 
			$calculatedRecord->tolerancetype=1;
			$calculatedRecord->tolerance=$tolerance/100;
		}
		
		if((string)$precisionType=="Decimal"){
			$calculatedRecord->correctanswerformat=1;		
			$calculatedRecord->correctanswerlength=$precision;				
		}else {
			echo " - SPECIAL PRECISION = ".$precisionType. "-".$precision;
			$calculatedRecord->correctanswerformat=2;		
			$calculatedRecord->correctanswerlength=$precision;
		}
		
		$question->calculatedRecords[]=$calculatedRecord;
		
		$calculatedOption = new CalculatedOption();
		$calculatedOption->id=1;
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
		
		echo '<br/>';
		
	}
	
	public function convertFormula($webCtFormula){
		
		$webctCaract = array("[", "]","ln"," ");
		$moodleCaract   = array("{", "}","log","");
		
		$tempFormula = str_replace($webctCaract, $moodleCaract, $webCtFormula);
		
		//sum
		$webctCaract = array("sum", ",");
		$moodleCaract   = array("", "+");
		
		$tempFormula = str_replace($webctCaract, $moodleCaract, $tempFormula);
		
		//** --> pow -- difficile à remplacer (pour gagner du temps) codée de manière dure		
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
		
		//TODO Verification des formules
		echo 'WEBCT FORMULA = '.$webCtFormula." -- MOODLE FORMULA = ".$moodleFormula."  ";
		
		
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
		$multichoice->correctfeedback=utf8_encode('Votre réponse est correcte.');//             <correctfeedback>&lt;p&gt;Your answer is correct.&lt;/p&gt;</correctfeedback>
		$multichoice->correctfeedbackformat="1";//             <correctfeedbackformat>1</correctfeedbackformat>
		$multichoice->partiallycorrectfeedback=utf8_encode('Votre réponse est partiellement correcte.');//             <partiallycorrectfeedback>&lt;p&gt;Your answer is partially correct.&lt;/p&gt;</partiallycorrectfeedback>
		$multichoice->partiallycorrectfeedbackformat="1";//             <partiallycorrectfeedbackformat>1</partiallycorrectfeedbackformat>
		$multichoice->incorrectfeedback=utf8_encode('Votre réponse est incorrecte.');//             <incorrectfeedback>&lt;p&gt;Your answer is incorrect.&lt;/p&gt;</incorrectfeedback>
		$multichoice->incorrectfeedbackformat="1";//             <incorrectfeedbackformat>1</incorrectfeedbackformat>
		$multichoice->shownumcorrect="1";//             <shownumcorrect>1</shownumcorrect>
	
		$question->multiChoice = $multichoice;
	
	
		$count = 0;
		foreach ($xmlContent->presentation->flow->response_lid->render_choice->flow_label->response_label as $response_label){
	
			$webctAnswerId = $response_label['ident'];
	
			$answer = new Answer();
			$answer->id=$count++;// 		id="4">
			
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
			echo 'QUESTION NAME TOO LONG - '.$question->name, PHP_EOL;
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
				echo '<br/> REPONSE ALTERNATIVE DANS: '. $question->name. '<br/>';
			}
		}
		
		
		//var_dump($multiChoiceAnswer);
		$countAnswers = count($correctAnswers);
		$count=0;
		//On crée les questions à choix multiple
		foreach ($correctAnswers as $correctAnswer){
			$count++;
			
			$multiChoiceQuestion = new MultiChoiceQuestion();
			$multiChoiceQuestion->id = $question->id+$count;
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
				
			//On crée le text des multichoice
			$multiChoiceText = "{1:MULTICHOICE:";
			$answerCount = 0;
			foreach ($correctAnswers as $correctAnswer2){
				
				$answer = new Answer();
				$answer->id=$answerCount++;
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
			$question->category->questions[] = $multiChoiceQuestion;
			$multiAnswer->sequence[]=$multiChoiceQuestion->id;
		}
				
		//Get the file attached if any and past it to the description
		$imageNames = $xmlContent->xpath('//ims:matimage');
		if(!empty($imageNames)){
			$imageName = $imageNames[0];
			
			$imageURI = $imageName['uri'];
			$findContentId   = '?contentID=';
			$pos = strpos($imageURI, $findContentId);
			if($pos>0){
				// 			echo 'IMAGE NAME = '.$imageName."\n";
				// 			echo 'IMAGE URI = '.$imageURI."\n";
				$fileContentId = substr($imageURI, $pos+11);
				$this->addFile($fileContentId, 3, $question);
			
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
	
		$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='ASSESSMENT_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
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
	
		echo $quizId.' - ';
	
		
		global $USER;
	
		//Glossary
		$quizModel = new QuizModel();
		$quizModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$quizModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$quizModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$quizModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED


		
		$event = new Event();
		$gradeBook = new ActivityGradeBook();
		
		//Event associé
		$events = new Events();
		$events->events[] = $event; 
		$quizModel->calendar = $events;
		
		//Grade
		$quizModel->grades = $gradeBook; 

		$quizModel->module = $this->createModule($quizId,"quiz","2013110501");
	
	
		$quizModel->quiz = $this->createQuiz($quizId, $quizModel->module);
	
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$quizModel->module->id;
		$activity->sectionid=0;
		$activity->modulename=$quizModel->module->modulename;
		$activity->title=$quizModel->quiz->name;
		$activity->directory="activities/glossary_".$quizModel->quiz->quizId;
	
		$this->moodle_backup->contents->activities[] = $activity;
	
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","quiz_".$quizModel->quiz->quizId,"quiz_".$quizModel->quiz->quizId."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupActivitySetting("activity","quiz_".$quizModel->quiz->quizId,"quiz_".$quizModel->quiz->quizId."_userinfo",1);
	
		$inforRef = new InfoRef();
		$inforRef->userids[]=$USER->id;
		$inforRef->fileids=$quizModel->quiz->filesIds;
		$quizModel->inforef = $inforRef;
	
		$this->activities[] = $quizModel;
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
		$quiz->contextid=0;
		$quiz->quizId = $quizId;
		
		
		$quiz->name =$row['NAME'];
		
		$description = $row['DESCRIPTION'];
		if(empty($description)){
			$glossary->intro ="";
		}else {
			$quiz->intro =$description->load();
		}
		
		$quiz->introformat =1;
		
		
		
		$request = "SELECT * FROM ASSMT_ASSESSMENT 
						INNER JOIN ASSMT_SETTING ON ASSMT_ASSESSMENT.ID=ASSMT_SETTING.ASSESSMENT_ID 
		  				INNER JOIN ASSMT_SECURITY_SETTING ON ASSMT_SETTING.ID=ASSMT_SECURITY_SETTING.ID
						INNER JOIN ASSMT_SUBMISSION_SETTING ON ASSMT_SETTING.ID=ASSMT_SUBMISSION_SETTING.ID
					  	INNER JOIN ASSMT_RESULT_SETTING ON ASSMT_SETTING.ID=ASSMT_RESULT_SETTING.ID 
					WHERE ASSMT_ASSESSMENT.ID='".$quizId."'";
		$stid = oci_parse($this->connection,$request);
		oci_execute($stid);
		$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
		
		$timeopen = $row['STARTTIME'];
		if(empty($timeopen)){
			$timeopen=0;
		}
		$quiz->timeopen = $timeopen;
		
		$timeclose = $row['ENDTIME'];
		if(empty($timeopen)){
			$timeclose=0; 
		}
		$quiz->timeclose=$timeclose; 
		
		$durationUnlimited = $row['DURATIONUNLIMITED'];
		$duration = $row['DURATION'];
		$durationUnit = $row['DURATIONLIMITS'];
		
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
		
		$quiz->preferredbehaviour=0;
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
		}
		
		
		$quiz->decimalpoints=2;		
		$quiz->questiondecimalpoints=-1;
		$quiz->reviewattempt=69904;
		$quiz->reviewcorrectness=4368;
		$quiz->reviewmarks=4368;
		$quiz->reviewspecificfeedback=4368;
		$quiz->reviewgeneralfeedback=4368;
		$quiz->reviewrightanswer=4368;
		$quiz->reviewoverallfeedback=4368;
		
		$quiz->questionsperpage=0;
		
		if($row['QUESTIONDELIVERY']='allAtOnce'){
			$quiz->navmethod="free";
		}else {
			$quiz->navmethod="sequential";
		}
		
		if($row['RANDOMIZE_ATTEMPTS']==0){
			$quiz->shufflequestions=0;
			$quiz->shuffleanswers =0 ;				
		}else {
			$quiz->shufflequestions=1;
			$quiz->shuffleanswers =1;
		}
		
		//QUESTIONS
		
		$quiz->sumgrades="1.00000";
		$quiz->grade = str_replace(',', '.', $row['MAXSCORE']);

		$quiz->timecreated=time();
		$quiz->timemodified=time();
		
		$quiz->password=$row['SECURITYPASSWORD'];
		$address = $row['SECURITYADDRESS'];
		if($address!='0.0.0.0'){
			$quiz->subnet= $address;
		}
		$quiz->browsersecurity='-';
		$quiz->delay1=0;
		$quiz->delay2=0;

		$quiz->showuserpicture=0;
		$quiz->showblocks=0;
				
		return $quiz;
	}
	
}