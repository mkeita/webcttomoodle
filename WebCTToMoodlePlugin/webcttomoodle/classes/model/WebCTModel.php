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
		
		$this->retrieveGlossaries();
		
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
			$convertedDescription = $this->convertHTMLContentLinks($completeDescription,$filesName);
			
			foreach ($filesName as $fileName){
				$request1 = "SELECT * FROM CMS_CONTENT_ENTRY WHERE NAME ='".$fileName."' AND DELIVERY_CONTEXT_ID='".$this->deliveryContextId."'";
				$stid1 = oci_parse($this->connection,$request1);
				oci_execute($stid1);
				$row1 = oci_fetch_array($stid1, OCI_ASSOC+OCI_RETURN_NULLS);
				if(!empty($row1)){
					error_log($fileName,0);
					$this->addFile($row1["ORIGINAL_CONTENT_ID"], 2, $entry, $glossary);					
				}
			}
						
			$entry->definition =$convertedDescription;// 		<definition>&lt;p&gt;Entry 1 of glossary&lt;/p&gt;</definition>
			
			$entry->sourceglossaryid=$glossaryId;// 		<sourceglossaryid>0</sourceglossaryid>
					
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
	
	
	/* (non-PHPdoc)
	 * @see GlobalModel::addFileGlossaryFile()
	 * 
	 * MODE 1 = Glossary file - attachment
	 * MODE 2 = GLossary file - entry
	 * 
	 */
	public function addFile($fileOriginalContentId, $mode, $item, &$parent){
		
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
		$parent->filesIds[] = $repository->id;		
		$parent->filesIds[] = $file->id;
	
		//REFERENCE IN THE COURSE FILES
		$this->files->files[]=$repository;
		$this->files->files[]=$file;
		
				
	}
	
}