<?php
class SyllabusManage {
	/**
	 *
	 * @var Syllabus
	 */
	public $syllabus;
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
	public $learningObj;
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
	 *
	 * @var courseInfo
	 */
	public $courseInfo;
	function _construct() {
		$this->syllabus = new Syllabus ();
		$this->courseInfo = array ();
		$this->custum = array ();
		$this->policy = array ();
		$this->ressource = array ();
		$this->courseReq = array ();
		$this->custumHtmlItem = array ();
		$this->learningObj = array ();
		$this->courseReq = array ();
		$this->lesson = array ();
	}
}
class Syllabus {
	public $id;
	public $lastmodify_ts;
	public $source_filename; // Tt le temps à NULL dans la base de donné
	public $create_ts;
	public $use_source_file_fl; // Booléen qui indique de quelle maniére a été crée le syllabus.
	function _construct(array $donnees) {
		$this->id = $donnees ["ID"];
		$this->lastmodify_ts = $donnees ["LASTMODIFY_TS"];
		$this->source_filename = $donnees ["SOURCE_FILENAME"];
		$this->create_ts = $donnees ["CREATE_TS"];
		$this->use_source_file_fl = $donnees ["USE_SOURCE_FILE_FL"];
	}
}
class RubriqueSyllabus {
	public $id;
	public $syllItem_id;
	public $syllabus_id;
	public $title;
	public $lastmodify_ts;
	public $create_ts;
}
class Policy {
	public $policyIntro; // Dans la table SYLLITem_details , coursReq possede 2 tuple.
	public $policyAddReq;
	function _construct() {
		$this->policyIntro = new PolicyRubrique ();
		$this->policyAddReq = new PolicyRubrique ();
	}
	public function info() {
		$resultat = "<p> <strong> <font color = \"0000ff\"> " . $this->policyIntro->title . " </font> </strong> </br> ";
		if (($this->policyIntro->value != NULL) || ($this->policyAddReq->value != NULL)) {
			$resultat = $resultat . "<ul>";
			if ($this->policyIntro->value != NULL)
				$resultat = $resultat . " <li> Introduction: " . $this->policyIntro->value . "</li>";
			if ($this->policyAddReq->value != NULL)
				$resultat = $resultat . "<li> Informations supplementaires: " . $this->policyAddReq->value . "</li> ";
			$resultat = $resultat . "</ul>";
		}
		$resultat = $resultat . "</p>";
		
		return $resultat;
	}
}
class PolicyRubrique extends RubriqueSyllabus {
	public $name;
	public $value;
	function _construct(array $res, array $res2) {
		$this->id = $res2 ["ID"];
		$this->lastmodify_ts = $res2 ["LASTMODIFY_TS"];
		$this->create_ts = $res2 ["CREATE_TS"];
		$this->title = $res ["TITLE"];
		$this->syllItem_id = $res2 ["SYLLITEM_ID"];
		$this->name = $res2 ["NAME"];
		$this->value = $res2 ["VALUE"];
	}
}
class Custum extends RubriqueSyllabus {
	public $cust_item_name;
	public $cust_item_value;
	function _construct(array $res, array $res2) {
		$this->id = $res2 ["ID"];
		$this->lastmodify_ts = $res2 ["LASTMODIFY_TS"];
		$this->create_ts = $res2 ["CREATE_TS"];
		$this->title = $res ["TITLE"];
		$this->syllItem_id = $res2 ["SYLLITEM_ID"];
		$this->cust_item_name = $res2 ["CUST_ITEM_NAME"];
		$this->cust_item_value = $res2 ["CUST_ITEM_VALUE"];
	}
	public function info() {
		$resultat = "<p> <strong> <font color = \"0000ff\"> " . $this->title . " </font> </strong> </br> ";
		
		if (($this->cust_item_name != NULL) || ($this->cust_item_value != NULL)) {
			$resultat = $resultat . "<ul> <li> ";
			if ($this->cust_item_name != NULL)
				$resultat = $resultat . $this->cust_item_name . " : ";
			if ($this->cust_item_value != NULL)
				$resultat = $resultat . $this->cust_item_value;
			$resultat = $resultat . "</li> </ul>";
		}
		$resultat = $resultat . "</p>";
		return $resultat;
	}
}
class Ressource extends RubriqueSyllabus {
	public $ressource_name;
	public $ressource_publisher;
	public $ressource_author;
	public $ressource_isbn;
	public $ressource_info;
	public $ressource_edition_year;
	public $ressource_required;
	
	function _construct(array $res1, array $res2) {
		$this->id = $res2 ["ID"];
		$this->lastmodify_ts = $res2 ["LASTMODIFY_TS"];
		$this->create_ts = $res2 ["CREATE_TS"];
		$this->title = $res1 ["TITLE"];
		$this->ressource_name = $res2 ["RESOURCE_NAME"];
		$this->ressource_publisher = $res2 ["RESOURCE_PUBLISHER"];
		$this->ressource_author = $res2 ["RESOURCE_AUTHOR"];
		$this->ressource_isbn = $res2 ["RESOURCE_ISBN"];
		$this->ressource_info = $res2 ["RESOURCE_INFO"];
		$this->ressource_edition_year = $res2 ["RESOURCE_EDITION_YEAR"];
		if($res2 ["RESOURCE_REQUIRED_FL"] == 1){
			$this->ressource_required = 'Ressource obligatoire';
		}else{
			$this->ressource_required = 'Ressource recommande';
		}
	}
	public function info() {
		$resultat = "<p> <strong> <font color = \"0000ff\"> ". $this->title . "</font> </strong> </br>";
		if (($this->ressource_name != NULL) || ($this->ressource_author != NULL) || ($this->ressource_publisher != NULL) || ($this->ressource_edition_year != NULL) || ($this->ressource_isbn != NULL) || ($this->ressource_info != NULL) || ($this->ressource_required != NULL)) {
			$resultat = $resultat . '<ul>';
			if (($this->ressource_name != NULL))
				$resultat = $resultat . " <li> Titre: " . $this->ressource_name . "</li> ";
			if (($this->ressource_author != NULL))
				$resultat = $resultat . " <li> Auteur: " . $this->ressource_author . "</li> ";
			if ($this->ressource_publisher != NULL)
				$resultat = $resultat . "<li> Editeur: " . $this->ressource_publisher . "</li> ";
			if (($this->ressource_edition_year != NULL))
				$resultat = $resultat . "<li> Edition/Annee: " . $this->ressource_edition_year . "</li> ";
			if ($this->ressource_isbn != NULL)
				$resultat = $resultat . "<li> ISBN: " . $this->ressource_isbn . "</li> ";
			if ($this->ressource_info != NULL)
				$resultat = $resultat . "<li> Informations supplementaires : " . $this->ressource_info . "</li> ";
			if ($this->ressource_required != NULL)
				$resultat = $resultat . "<li> Type : " . $this->ressource_required . "</li> ";
			$resultat = $resultat . '</ul>';
		}
		$resultat = $resultat . '</p>';
		
		return $resultat;
	}
}
class EducatorInfoSyllabus {
	public $email;
	public $nom;
	public $prenom;
	function _construct(array $donnees) {
		$this->nom = $donnees ["NAME_N_FAMILY"];
		$this->prenom = $donnees ["NAME_N_GIVEN"];
		$this->email = $donnees ["EMAIL"];
	}
	public function info() {
		$resultat = "<p> <strong> Formateur de section:" . $this->nom . " " . $this->prenom . " </strong> </br> ";
		$resultat = $resultat . "<ul><li> Courrier electronique : " . $this->email . "</li> </ul> ";
		$resultat = $resultat . "</p>";
		return $resultat;
	}
}
class CustumHtmlItem extends RubriqueSyllabus {
	public $custm_html_value;
	function _construct(array $res, array $res2) {
		$this->id = $res2 ["ID"];
		$this->lastmodify_ts = $res2 ["LASTMODIFY_TS"];
		$this->create_ts = $res2 ["CREATE_TS"];
		$this->title = $res ["TITLE"];
		$val = $res2 ["CUST_HTML_VALUE"];
		$this->custm_html_value = $val->load ();
	}
	public function info() {
		$resultat = "<p> <strong> <font color = \"0000ff\">" . $this->title . " </font>> </strong> </br> ";
		if ($this->custm_html_value != NULL)
			$resultat = $resultat . "<ul><li> " . $this->custm_html_value . "</li> </ul> ";
		$resultat = $resultat . "</p>";
		return $resultat;
	}
}
class LearningObj_link {
	public $learningObject;
	public function _construct() {
		$this->learningObject = array ();
	}
	public function info() {
		$resultat = " ";
		for($i = 0; $i < count ( $this->learningObject ); $i ++) {
			$resultat = $resultat . "<p> <strong> <font color = \"0000ff\">" . $this->learningObject [$i]->title . " </font> </strong> </br> ";
			if (($this->learningObject [$i]->name_cms_content != NULL) || ($this->learningObject [$i]->description_cms_content != NULL)) {
				$resultat = $resultat . "<ul>";
				if ($this->learningObject [$i]->name_cms_content != NULL)
					$resultat = $resultat . "<li> <strong> " . $this->learningObject [$i]->name_cms_content . " </strong> </li> ";
				if ($this->learningObject [$i]->description_cms_content != NULL)
					$resultat = $resultat . "<li> " . $this->learningObject [$i]->description_cms_content . "</li> ";
				$resultat = $resultat . "</ul>";
			}
			$resultat = $resultat . "</p>";
		}
		return $resultat;
	}
}
class LearningObj_linkRub {
	public $categorieObjectif;
	public $name_cms_content;
	public $description_cms_content;
	public $title;
	function _construct(array $res2, $title) {
		$this->title = $title;
		$this->name_cms_content = $res2 ["NAME"];
		$this->description_cms_content = $res2 ["DESCRIPTION"]->load ();
		$this->categorieObjectif = null; // Trouver le lien
	}
}
class CourseReq {
	public $courseReqIntro; // Dans la table SYLLITem_details , coursReq possede 2 tuple.
	public $courseReqReqs; // Intro et requirements
	function _construct() {
		$this->courseReqIntro = new CourseReqRubrique ();
		$this->courseReqReqs = new CourseReqRubrique ();
	}
	public function info() {
		$resultat = "<p> <strong> <font color = \"0000ff\">" . $this->courseReqIntro->title . "</font> </strong> </br> ";
		if (($this->courseReqIntro->value != NULL) || ($this->courseReqReqs->value != NULL)) {
			$resultat = $resultat . "<ul>";
			if ($this->courseReqIntro->value != NULL)
				$resultat = $resultat . " <li> Introduction: " . $this->courseReqIntro->value . "</li>";
			if ($this->courseReqReqs->value != NULL)
				$resultat = $resultat . "<li> Condition requise: " . $this->courseReqReqs->value . "</li> ";
			$resultat = $resultat . "</ul>";
		}
		$resultat = $resultat . "</p>";
		return $resultat;
	}
}
class CourseReqRubrique extends RubriqueSyllabus {
	public $name;
	public $value;
	function _construct(array $res, array $res2) {
		$this->id = $res2 ["ID"];
		$this->lastmodify_ts = $res2 ["LASTMODIFY_TS"];
		$this->create_ts = $res2 ["CREATE_TS"];
		$this->title = $res ["TITLE"];
		$this->syllItem_id = $res2 ["SYLLITEM_ID"];
		$this->name = $res2 ["NAME"];
		$this->value = $res2 ["VALUE"];
	}
}
class Lesson {
	public $lessonTopic; // Dans la table SYLLITem_details , lesson possede 4 tuple.
	public $lessonReadings;
	public $lessonAssignements;
	public $lessonGoals;
	function _construct() {
		$this->lessonTopic = new LessonRubrique ();
		$this->lessonReadings = new LessonRubrique ();
		$this->lessonGoals = new LessonRubrique ();
		$this->lessonAssignements = new LessonRubrique ();
	}
	public function info() {
		$resultat = "<p> <strong> <font color = \"0000ff\">" . $this->lessonTopic->title . " </font> </strong> </br> ";
		if (($this->lessonTopic->lesson_title != NULL) || ($this->lessonTopic->value != NULL) || ($this->lessonReadings->value != NULL) || ($this->lessonAssignements->value != NULL) || ($this->lessonGoals->value != NULL)) {
			$resultat = $resultat . "<ul>";
			if ($this->lessonTopic->lesson_title != NULL)
				$resultat = $resultat . " <li> Titre de la lecon: " . $this->lessonTopic->lesson_title . "</li>";
			if ($this->lessonGoals->value != NULL)
				$resultat = $resultat . "<li> Objectifs: " . $this->lessonGoals->value . "</li> ";
			if ($this->lessonTopic->value != NULL)
				$resultat = $resultat . "<li> Rubriques: " . $this->lessonTopic->value . "</li> ";
			if ($this->lessonReadings->value != NULL)
				$resultat = $resultat . "<li> Lecture: " . $this->lessonReadings->value . "</li> ";
			if ($this->lessonAssignements->value != NULL)
				$resultat = $resultat . "<li> Taches: " . $this->lessonAssignements->value . "</li> ";
			$resultat = $resultat . "</ul>";
		}
		$resultat = $resultat . "</p>";
		return $resultat;
	}
}
class LessonRubrique extends RubriqueSyllabus {
	public $lesson_title;
	public $lesson_date;
	public $name;
	public $value;
	function _construct(array $res, array $res2) {
		$this->id = $res2 ["ID"];
		$this->lastmodify_ts = $res2 ["LASTMODIFY_TS"];
		$this->create_ts = $res2 ["CREATE_TS"];
		$this->title = $res ["TITLE"];
		$this->syllItem_id = $res2 ["SYLLITEM_ID"];
		$this->name = $res2 ["NAME"];
		$this->value = $res2 ["VALUE"];
		$this->lesson_title = $res ["LESSON_TITLE"];
		$this->lesson_date = $res ["LESSON_DATE"];
	}
}
class CourseInfo {
	public $nomCours;
	public $infoSection;
	function _construct($nomCour, $infoSec) {
		$this->nomCours = $nomCour;
		$this->infoSection = $infoSec;
	}
	public function info() {
		$resultat = "<p> <strong> <font color = \"0000ff\"> Information sur la section: " . $this->nomCours . "</font> </strong> </br> ";
		if ($this->nomCours != NULL)
			$resultat = $resultat . "<ul><li> Nom du cours: " . $this->infoSection . "</li> </ul> ";
		$resultat = $resultat . "</p>";
		return $resultat;
	}
}

?>