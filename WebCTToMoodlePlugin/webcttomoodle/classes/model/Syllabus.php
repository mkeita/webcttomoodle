<?php


class SyllabusManage {
	/**
	 * 
	 * @var Syllabus
	 */
	public $syllabus ;
	/**
	 * 
	 * @var array de Policy
	 */
	public $policy;
	/**
	 * 
	 * @var array de custum
	 */
	public $custum;
	/**
	 * 
	 * @var array de ressource
	 */
	public $ressource;
	/**
	 * 
	 * @var array EducatorInfo
	 */
	public $educatorInfo;
	/**
	 * 
	 * @var array de CustumHtmlItem
	 */
	public $custumHtmlItem;
	/**
	 * 
	 * @var array de LearningObject
	 */
	public $learningObj ;
	/**
	 *
	 * @var array de courseReq
	 */
	public $courseReq; 
	/**
	 * 
	 * @var array de lesson
	 */
	public $lesson;
	
	/**
	 * @var courseInfo
	 */
	public $courseInfo;
	
	function _construct(){
		$this->syllabus = new Syllabus();
		$this->custum = array();
		$this->policy = array();
		$this->ressource = array();
		$this->courseReq = array();
		$this->custumHtmlItem = array();
		$this->learningObj = array();
		$this->courseReq = array();
		$this->lesson = array();
		$this->courseInfo = array();
	}
}

class Syllabus {
	public $id;
	public $lastmodify_ts;
	public $source_filename;   //Tt le temps à NULL dans la base de donné
	public $create_ts;
	public $use_source_file_fl; //Booléen qui indique de quelle maniére a été crée le syllabus.
	
	function _construct(array $donnees){
		$this->id = $donnees["ID"];
		$this->lastmodify_ts = $donnees["LASTMODIFY_TS"] ;
		$this->source_filename = $donnees["SOURCE_FILENAME"] ;
		$this->create_ts =  $donnees["CREATE_TS"];
		$this->use_source_file_fl = $donnees["USE_SOURCE_FILE_FL"];	
	}
	
	
		
}

class RubriqueSyllabus {
	public $id;
	public $syllItem_id;
	public $syllabus_id;
	public $title;
	public $lastmodify_ts;
	public $create_ts ;
}

class Policy {
	public $policyIntro; 		//Dans la table SYLLITem_details , coursReq possede 2 tuple.
	public $policyAddReq;
	
	function _construct(){
		$this->policyIntro = new PolicyRubrique();
		$this->policyAddReq = new PolicyRubrique();
	}
	
}

class PolicyRubrique extends RubriqueSyllabus {
	public $name;
	public $value;
	
	function _construct(array $res, array $res2){
		$this->id = $res2["ID"];
		$this->lastmodify_ts = $res2["LASTMODIFY_TS"] ;
		$this->create_ts =  $res2["CREATE_TS"];
		$this->title = $res["TITLE"];
		$this->syllItem_id = $res2["SYLLITEM_ID"];
		$this->name = $res2["NAME"];
		$this->value = $res2["VALUE"];
	}	
}

class Custum extends RubriqueSyllabus {
	public $cust_item_name;
	public $cust_item_value;
	
	function _construct(array $res, array $res2){
		$this->id = $res2["ID"];
		$this->lastmodify_ts = $res2["LASTMODIFY_TS"] ;
		$this->create_ts =  $res2["CREATE_TS"];
		$this->title = $res["TITLE"];
		$this->syllItem_id = $res2["SYLLITEM_ID"];
		$this->cust_item_name = $res2["CUST_ITEM_NAME"];
		$valeur = $res2["CUST_ITEM_VALUE"];
	//	$this->cust_item_value = $valeur->load();
		
	}
}

class Ressource extends RubriqueSyllabus {
	public $ressource_name;
	public $ressource_publisher;
	public $ressource_author;
	public $ressource_isbn;
	public $ressource_info;
	public $ressource_edition_year;
	
	function _construct(array $res1, array $res2){
		$this->id = $res2["ID"];
		$this->lastmodify_ts = $res2["LASTMODIFY_TS"] ;
		$this->create_ts =  $res2["CREATE_TS"];
		$this->title = $res1["TITLE"];
		$this->ressource_name =	$res2["RESOURCE_NAME"];
		$this->ressource_publisher = $res2["RESOURCE_PUBLISHER"];
		$this->ressource_author = $res2["RESOURCE_AUTHOR"];
		$this->ressource_isbn = $res2["RESOURCE_ISBN"];
		$this->ressource_info = $res2["RESOURCE_INFO"];
		$this->ressource_edition_year = $res2["RESOURCE_EDITION_YEAR"];
	}
	
}


class EducatorInfoSyllabus  {
	public $role_id;
	public $email;
	public $nom;
	public $prenom;
	
	function _construct(array $donnees){
		$this->role_id = $donnees["ROLE_ID"];
		$this->nom = $donnees["NAME_N_FAMILY"];
		$this->prenom = $donnees["NAME_N_GIVEN"];
		$this->email = $donnees["EMAIL"];
	}
}

class CustumHtmlItem extends RubriqueSyllabus {
	public $custm_html_value;
	
	function _construct(array $res, array $res2){
		$this->id = $res2["ID"];
		$this->lastmodify_ts = $res2["LASTMODIFY_TS"] ;
		$this->create_ts =  $res2["CREATE_TS"];
		$this->title = $res["TITLE"];
		$val = $res2["CUST_HTML_VALUE"];
		$this->custm_html_value = $val->load();
	}
}

class LearningObj_link {
	public $learningObject;
	
	public function _construct(){	
		$this->learningObject = array();
	}
}


class LearningObj_linkRub  {
	public $categorieObjectif;
	public $name_cms_content;
	public $description_cms_content;
	public $title;
	
	function _construct(array $res2, $title){		
		$this->title = $title;	
		$this->name_cms_content = $res2["NAME"];	
		$this->description_cms_content = $res2["DESCRIPTION"]->load();
		$this->categorieObjectif = null; // Trouver le lien
	}	
}

class CourseReq {
	public $courseReqIntro;  //Dans la table SYLLITem_details , coursReq possede 2 tuple.
	public $courseReqReqs;   // Intro et requirements
	
	function _construct(){
		$this->courseReqIntro = new CourseReqRubrique();
		$this->courseReqReqs = new CourseReqRubrique();
	}
}


class CourseReqRubrique extends RubriqueSyllabus {
	public $name;
	public $value;
	
	function _construct(array $res , array $res2){
		$this->id = $res2["ID"];
		$this->lastmodify_ts = $res2["LASTMODIFY_TS"] ;
		$this->create_ts =  $res2["CREATE_TS"];
		$this->title = $res["TITLE"];
		$this->syllItem_id = $donnees["SYLLITEM_ID"];
		$this->name = $res2["NAME"];
		$this->value = $res2["VALUE"];
	}
}

class Lesson {
	public $lessonTopic;	  //Dans la table SYLLITem_details , lesson possede 4 tuple.
	public $lessonReadings;
	public $lessonAssignements;
	public $lessonGoals;
	
	function _construct(){
		$this->lessonTopic = new LessonRubrique();
		$this->lessonReadings = new LessonRubrique();
		$this->lessonGoals = new LessonRubrique();
		$this->lessonAssignements = new LessonRubrique();
	}
}


class LessonRubrique extends RubriqueSyllabus {
	public $lesson_title;
	public $lesson_date;
	public $name;
	public $value;
	
	function _construct(array $res , array $res2){
		$this->id = $res2["ID"];
		$this->lastmodify_ts = $res2["LASTMODIFY_TS"] ;
		$this->create_ts =  $res2["CREATE_TS"];
		$this->title = $res["TITLE"];
		$this->syllItem_id = $res2["SYLLITEM_ID"];
		$this->name = $res2["NAME"];
		$this->value = $res2["VALUE"];
		$this->lesson_title = $res["LESSON_TITLE"];
		$this->lesson_date = $res["LESSON_DATE"];
	}
}

class CourseInfo {
	public $nomCours;
	public $infoSection;
	
	function _construct($nomCour , $infoSec){
		$this->nomCours = $nomCour;
		$this->infoSection = $infoSec;
	}
	
	//TEST CONTENU..
}

?>