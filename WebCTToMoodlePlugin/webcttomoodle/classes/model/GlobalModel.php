<?php

require_once 'classes/model/IBackupModel.php';

require_once 'classes/model/general/InfoRef.php';
require_once 'classes/model/general/RolesBackup.php';
require_once 'classes/model/general/Comments.php';
require_once 'classes/model/general/Filters.php';
require_once 'classes/model/general/Events.php';


require_once 'classes/model/MoodleBackup.php';
require_once 'classes/model/Roles.php';
require_once 'classes/model/Users.php';
require_once 'classes/model/Questions.php';
require_once 'classes/model/Badges.php';
require_once 'classes/model/Completion.php';
require_once 'classes/model/Files.php';
require_once 'classes/model/GradeBook.php';
require_once 'classes/model/Groups.php';
require_once 'classes/model/Outcomes.php';
require_once 'classes/model/Scales.php';

require_once 'classes/model/sections/Section.php';

require_once 'classes/model/course/block/Block.php';
require_once 'classes/model/course/Course.php';
require_once 'classes/model/course/Enrolements.php';


require_once 'classes/model/activities/ActivityGradeBook.php';
require_once 'classes/model/activities/Glossary.php';
require_once 'classes/model/activities/ActivityCompletion.php';
require_once 'classes/model/activities/Module.php';
require_once 'classes/model/activities/Quiz.php';
require_once 'classes/model/activities/Assignment.php';
require_once 'classes/model/activities/Grading.php';
require_once 'classes/model/activities/Folder.php';
require_once 'classes/model/activities/ActivityPage.php';
require_once 'classes/model/activities/ActivityRessource.php';

require_once 'classes/utils/HtmlContentClass.php';

require_once 'classes/model/Syllabus.php';


defined('MOODLE_INTERNAL') || die();

// Include all the needed stuff (backup)
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');


abstract class GlobalModel implements \IBackupModel {
	
	/**
	 * @var MoodleBackup
	 */
	public $moodle_backup;
	
	/**
	 * @var Roles
	 */
	public $roles;
	
	/**
	 * @var Users
	 */
	public $users;
	
	/**
	 * @var Questions
	 */
	public $questions;
	
	/**
	 * @var Badges
	 */
	public $badges;
	
	/**
	 * @var Completion
	 */
	public $completion;
	
	/**
	 * @var Files
	 */
	public $files;
	

	/**
	 * @var GradeBook
	 */
	public $gradebook;
	
	/**
	 * @var Groups
	 */
	public $groups;
	
	/**
	 * @var Outcomes
	 */
	public $outcomes;
	
	
	/**
	 * @var Scales
	 */
	public $scales;
	
	
	/**
	 * @var CourseModel
	 */
	public $course;

	/**
	 * @var SectionModel|Array
	 */
	public $sections = array();
	
	
	/**
	 * @var ActivityModel|Array
	 */
	public $activities = array();
	
	/**
	 *
	 * @var SyllabusManage
	 */
	public $syllabusManager;

	protected $idCount = 1;
	
	/**
	 * @return GlobalModel
	 */
	public function __construct(){
		$this->preInitialization();		
		$this->initializeMoodleBackupModel();
		$this->initializeUsersModel();
		
		$this->initializeRolesModel(); //EMPTY CURRENTLY NOT NEEDED
		$this->initializeQuestionsModel();  //EMPTY CURRENTLY NOT NEEDED
		$this->initializeBadgesModel();  //EMPTY CURRENTLY NOT NEEDED
		$this->initializeCompletionModel();//EMPTY CURRENTLY NOT NEEDED
		$this->initializeGroupsModel();//EMPTY CURRENTLY NOT NEEDED
		$this->initializeOutcomesModel();//EMPTY CURRENTLY NOT NEEDED
		$this->initializeScalesModel();//EMPTY CURRENTLY NOT NEEDED
				
		$this->files = new Files();
		$this->initializeSectionsModels();

		$this->initializeCourseModel();
		
		$this->initializeGradeBookModel();
		
		$this->initializeSyllabusModel();
		
	}
	
	
	public function getNextId(){
		return $this->idCount++;
	}
	public abstract function preInitialization();
	
	/**
	 *
	 */
	public function initializeMoodleBackupModel(){
		global $CFG;//, $DB;
		
		
		$moodleBackup = new MoodleBackup();
				
		$moodleBackup->moodle_version = $CFG->version;
		$moodleBackup->moodle_release = $CFG->release;
		$moodleBackup->backup_version = $CFG->backup_version;
		$moodleBackup->backup_release = $CFG->backup_release;
		$moodleBackup->backup_date = time();
		$moodleBackup->mnet_remoteusers = 0;
		$moodleBackup->include_files = 1;
		$moodleBackup->include_file_references_to_external_content = 0;
		$moodleBackup->original_wwwroot = $CFG->wwwroot;
		$moodleBackup->original_site_identifier_hash = md5(get_site_identifier());
		$moodleBackup->original_system_contextid = context_system::instance()->id;
		$moodleBackup->original_course_id = 0;
		$moodleBackup->original_course_contextid = 0;//48";
		$moodleBackup->original_course_startdate = time();
		
		//TODO MUST BE OVERRIDE BY CONCRETE CLASS - WebCt Course 0";
		$moodleBackup->original_course_fullname = "";
		//TODO MUST BE OVERRIDE BY CONCRETE CLASS - WEBCT-0";
		$moodleBackup->original_course_shortname = "";
		//TODO MUST BE OVERRIDE BY CONCRETE CLASS - test_backup.mbz
		$moodleBackup->name = "backup-".$moodleBackup->original_course_shortname.".mbz"; 
		
		$detail = new MoodleBackupDetail();
		$detail->type=backup::TYPE_1COURSE;
		$detail->format=backup::FORMAT_MOODLE;
		$detail->interactive=backup::INTERACTIVE_YES;
		$detail->mode=backup::MODE_GENERAL;
		$detail->execution=backup::EXECUTION_INMEDIATE;
		$detail->executiontime=0;
		
		//TODO MUST BE OVERRIDE BY CONCRETE CLASS
		$detail->backup_id="0"; 
		
		$moodleBackup->details[] = $detail;
		
		$moodleBackup->contents = new MoodleBackupContents();
		//TODO MUST BE OVERRIDE BY CONCRETE CLASS
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","filename",$moodleBackup->name);		
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","imscc11",0);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","users",1);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","anonymize",1);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","role_assignments",1);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","activities",1);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","blocks",1);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","filters",1);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","comments",1);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","badges",1);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","calendarevents",1);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","userscompletion",1);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","logs",0);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","grade_histories",0);
		$moodleBackup->settings[] = new MoodleBackupBasicSetting("root","questionbank",1);
	
		$this->moodle_backup =  $moodleBackup;
	}
	
	
	/**
	 */
	public function initializeRolesModel(){
		$roles = new Roles();
	
// 		$role = new Role();
// 		$role->id=5;// 	<role id="5">
// 		$role->name="";// 	<name></name>
// 		$role->shortname="student";// 	<shortname>student</shortname>
// 		$role->nameincourse="$@NULL@$";// 	<nameincourse>$@NULL@$</nameincourse>
// 		$role->description="";// 	<description></description>
// 		$role->sortorder=5;// 	<sortorder>5</sortorder>
// 		$role->archetype="student";// 	<archetype>student</archetype>
		
// 		$roles->roles[] = $role; 
		
		$this->roles = $roles;
	}
	

	public function initializeUsersModel(){
		
		global $USER, $DB;
    			
		$users = new Users();
		
		//CURRENT USER
		$userid = $USER->id;
		$currentUser = $DB->get_record_select('user', 'id='.$userid);
		
		$user = new User();
		$user->id=$currentUser->id;// 		<user id="2" contextid="5">
		$user->contextid=0;
		$user->username=$currentUser->username;// 		<username>admin</username>
		$user->idnumber=$currentUser->idnumber;// 		<idnumber></idnumber>
		$user->email=$currentUser->email;// 		<email>keitamarc@hotmail.com</email>
		$user->icq=$currentUser->icq;// 		<icq></icq>
		$user->skype=$currentUser->skype;// 		<skype></skype>
		$user->yahoo=$currentUser->yahoo;// 		<yahoo></yahoo>
		$user->aim=$currentUser->aim;// 		<aim></aim>
		$user->msn=$currentUser->msn;// 		<msn></msn>
		$user->phone1=$currentUser->phone1;// 		<phone1></phone1>
		$user->phone2=$currentUser->phone2;// 		<phone2></phone2>
		$user->institution=$currentUser->institution;// 		<institution></institution>
		$user->department=$currentUser->department;// 		<department></department>
		$user->address=$currentUser->address;// 		<address></address>
		$user->city=$currentUser->city;// 		<city></city>
		$user->country=$currentUser->country;// 		<country></country>
		$user->lastip=$currentUser->lastip;// 		<lastip>127.0.0.1</lastip>
		$user->picture=$currentUser->picture;// 		<picture>0</picture>
		$user->url=$currentUser->url;// 		<url></url>
		$user->description=$currentUser->description;// 		<description></description>
		$user->descriptionformat=$currentUser->descriptionformat;// 		<descriptionformat>1</descriptionformat>
		$user->imagealt=$currentUser->imagealt;// 		<imagealt>$@NULL@$</imagealt>
		$user->auth=$currentUser->auth;// 		<auth>manual</auth>
		$user->firstnamephonetic=$currentUser->firstnamephonetic;// 		<firstnamephonetic></firstnamephonetic>
		$user->lastnamephonetic=$currentUser->lastnamephonetic;// 		<lastnamephonetic></lastnamephonetic>
		$user->middlename=$currentUser->middlename;// 		<middlename></middlename>
		$user->alternatename=$currentUser->alternatename;// 		<alternatename></alternatename>
		$user->firstname=$currentUser->firstname;// 		<firstname>Admin</firstname>
		$user->lastname=$currentUser->lastname;// 		<lastname>User</lastname>
		$user->confirmed=$currentUser->confirmed;// 		<confirmed>1</confirmed>
		$user->policyagreed=$currentUser->policyagreed;// 		<policyagreed>0</policyagreed>
		$user->deleted=$currentUser->deleted;// 		<deleted>0</deleted>
		$user->lang=$currentUser->lang;// 		<lang>en</lang>
		$user->theme=$currentUser->theme;// 		<theme></theme>
		$user->timezone=$currentUser->timezone;// 		<timezone>99</timezone>
		$user->firstaccess=$currentUser->firstaccess;// 		<firstaccess>1389139014</firstaccess>
		$user->lastaccess=$currentUser->lastaccess;// 		<lastaccess>1390830410</lastaccess>
		$user->lastlogin=$currentUser->lastlogin;// 		<lastlogin>1390808544</lastlogin>
		$user->currentlogin=$currentUser->currentlogin;// 		<currentlogin>1390829789</currentlogin>
		$user->mailformat=$currentUser->mailformat;// 		<mailformat>1</mailformat>
		$user->maildigest=$currentUser->maildigest;// 		<maildigest>0</maildigest>
		$user->maildisplay=$currentUser->maildisplay;// 		<maildisplay>1</maildisplay>
		$user->autosubscribe=$currentUser->autosubscribe;// 		<autosubscribe>1</autosubscribe>
		$user->trackforums=$currentUser->trackforums;// 		<trackforums>0</trackforums>
		$user->timecreated=$currentUser->timecreated;// 		<timecreated>0</timecreated>
		$user->timemodified=$currentUser->timemodified;// 		<timemodified>1390829789</timemodified>
		$user->trustbitmask=$currentUser->trustbitmask;// 		<trustbitmask>0</trustbitmask>

		$user->preferences[]=new UserPreference("1","htmleditor", get_user_preferences("htmleditor","", $currentUser));
		$user->preferences[]=new UserPreference("2","email_bounce_count", get_user_preferences("email_bounce_count","", $currentUser));
		$user->preferences[]=new UserPreference("3","email_send_count", get_user_preferences("email_send_count","", $currentUser));
		$user->preferences[]=new UserPreference("4","filepicker_recentrepository", get_user_preferences("filepicker_recentrepository","", $currentUser));
		$user->preferences[]=new UserPreference("5","filepicker_recentlicense", get_user_preferences("filepicker_recentlicense","", $currentUser));
		$user->preferences[]=new UserPreference("10","block13hidden", get_user_preferences("block13hidden","", $currentUser));
		$user->preferences[]=new UserPreference("11","docked_block_instance_13", get_user_preferences("docked_block_instance_13","", $currentUser));
		$user->preferences[]=new UserPreference("12","quiz_reordertab", get_user_preferences("quiz_reordertab","", $currentUser));
		$user->preferences[]=new UserPreference("13","userselector_preserveselected", get_user_preferences("userselector_preserveselected","", $currentUser));
		$user->preferences[]=new UserPreference("14","userselector_autoselectunique", get_user_preferences("userselector_autoselectunique","", $currentUser));
		$user->preferences[]=new UserPreference("15","userselector_searchanywhere", get_user_preferences("userselector_searchanywhere","", $currentUser));
		
		$users->users[] = $user;
		
		$this->users = $users;
	}
	
	/**
	 * Initialize Questions
	 */
	public function initializeQuestionsModel(){
		$questions = new Questions();
	
		$this->questions = $questions;
	}
	
	/**
	 * Initialize BadgesModel
	 */
	public function initializeBadgesModel(){
		$badges = new Badges();
	
		$this->badges = $badges;
	}
	
	/**
	 * Initialize Completion
	 */
	public function initializeCompletionModel(){
		$completion = new Completion();
	
		$this->completion= $completion;
	}
	
	
	
	/**
	 * Initialize GradeBook
	 */
	public function initializeGradeBookModel(){
		$gradebook = new GradeBook();
		
		$gradeCategory = new GradeCategory();
		$gradeCategory->id=$this->getNextId();
		$gradeCategory->parent ="$@NULL@$";//<parent>$@NULL@$</parent>
		$gradeCategory->depth =1;//<depth>1</depth>
		$gradeCategory->path ="/".$gradeCategory->id."/";//<path>/148/</path>
		$gradeCategory->fullname ="?";//<fullname>?</fullname>
		$gradeCategory->aggregation ="11";//<aggregation>11</aggregation>
		$gradeCategory->keephigh =0;//<keephigh>0</keephigh>
		$gradeCategory->droplow =0;//<droplow>0</droplow>
		$gradeCategory->aggregateonlygraded =1;//<aggregateonlygraded>1</aggregateonlygraded>
		$gradeCategory->aggregateoutcomes =0;//<aggregateoutcomes>0</aggregateoutcomes>
		$gradeCategory->aggregatesubcats =0;//<aggregatesubcats>0</aggregatesubcats>
		$gradeCategory->timecreated =time();//<timecreated>1392640698</timecreated>
		$gradeCategory->timemodified =time();//<timemodified>1392640698</timemodified>
		$gradeCategory->hidden=0;//<hidden>0</hidden>
		
		$gradebook->grade_categories[] = $gradeCategory;
		
		$gradeItem = new GradeItem();
		$gradeItem->categoryid = "$@NULL@$";//<categoryid>$@NULL@$</categoryid>
		$gradeItem->itemname ="$@NULL@$" ;//<itemname>$@NULL@$</itemname>
		$gradeItem->itemtype ="course" ;//<itemtype>course</itemtype>
		$gradeItem->itemmodule ="$@NULL@$" ;//<itemmodule>$@NULL@$</itemmodule>
		$gradeItem->iteminstance = $gradeCategory->id;//<iteminstance>148</iteminstance>
		$gradeItem->itemnumber ="$@NULL@$" ;//<itemnumber>$@NULL@$</itemnumber>
		$gradeItem->iteminfo ="$@NULL@$" ;//<iteminfo>$@NULL@$</iteminfo>
		$gradeItem->idnumber ="$@NULL@$" ;//<idnumber>$@NULL@$</idnumber>
		$gradeItem->calculation ="$@NULL@$";//<calculation>$@NULL@$</calculation>
		$gradeItem->gradetype =1 ;//<gradetype>1</gradetype>
		$gradeItem->grademax ="100.00000" ;//<grademax>100.00000</grademax>
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
		$gradeItem->timecreated = time();//<timecreated>1392640698</timecreated>
		$gradeItem->timemodified = time() ;//<timemodified>1392640698</timemodified>
		
		$gradebook->grade_items[]=$gradeItem;
	
		$this->gradebook = $gradebook;
	}
	
	public function initializeSyllabusModel(){
		$this->syllabusManager = new SyllabusManage();
		$this->syllabusManager->_construct();
	
	}
	
	
	/**
	 * Initialize Groups
	 */
	public function initializeGroupsModel(){
		$groups = new Groups();
	
		$this->groups = $groups;
	}
	
	/**
	 * Initialize Outcomes
	 */
	public function initializeOutcomesModel(){
		$outcomes = new Outcomes();
	
		$this->outcomes = $outcomes;
	}
	
	/**
	 * Initialize Scales
	 */
	public function initializeScalesModel(){
		$scales = new Scales();
		
		$this->scales = $scales;
	}
	
	/**
	 * Initialize CourseModel
	 */
	public function initializeCourseModel(){
		$courseModel = new CourseModel();
	
		$course = new Course();
		$course->id = 0;
		$course->contextid = 0;
		
		//TODO MUST BE OVERRIDE BY CONCRETE CLASS
		$course->shortname = "";
		//TODO MUST BE OVERRIDE BY CONCRETE CLASS
		$course->fullname = "";
		//TODO MUST BE OVERRIDE BY CONCRETE CLASS
		$course->idnumber = "";
		
		$course->summary = "";
		$course->summaryformat = 1;
		//$course->format = "weeks";
		$course->format = "topics";
		$course->newsitems = 5;
		$course->startdate = time();
		$course->marker = 0;
		$course->maxbytes = 0;
		$course->legacyfiles = 0;
		$course->showreports = 0;
		$course->visible = 1;
		$course->groupmode = 0;
		$course->groupmodeforce = 0;
		$course->defaultgroupingid = 0;
		$course->lang = "";
		$course->theme = "";
		$course->timecreated = time();
		$course->timemodified = time();
		$course->requested = 0;
		$course->enablecompletion = 0;
		$course->completionnotify = 0;
		$course->numsections = 2;
		$course->hiddensections = 0;
		$course->coursedisplay = 0;
	
	
		$category = new CourseCategory();
		$category->id="1";
		$category->name = "Miscellaneous";
		$category->description = "$@NULL@$";
	
		$course->category=$category;
	
		$courseModel->course = $course;
	
		$enrolments = new Enrolments();
		
// 		$enrolment_manual = new Enrolment();
// 		$enrolment_manual->id=10;
// 		$enrolment_manual->enrol="manual";
// 		$enrolment_manual->status=0;
// 		$enrolment_manual->name="$@NULL@$";
// 		$enrolment_manual->enrolperiod=0;
// 		$enrolment_manual->enrolstartdate=0;
// 		$enrolment_manual->enrolenddate=0;
// 		$enrolment_manual->notifyall=0;
// 		$enrolment_manual->password="$@NULL@$";
// 		$enrolment_manual->cost="$@NULL@$";
// 		$enrolment_manual->currency="$@NULL@$";
// 		$enrolment_manual->roleid=0;
// 		$enrolment_manual->customint1="$@NULL@$";// 	<customint1>$@NULL@$</customint1>
// 		$enrolment_manual->customint2="$@NULL@$";// 	<customint2>$@NULL@$</customint2>
// 		$enrolment_manual->customint3="$@NULL@$";// 	<customint3>$@NULL@$</customint3>
// 		$enrolment_manual->customint4="$@NULL@$";// 	<customint4>$@NULL@$</customint4>
// 		$enrolment_manual->customint5="$@NULL@$";// 	<customint5>$@NULL@$</customint5>
// 		$enrolment_manual->customint6="$@NULL@$";// 	<customint6>$@NULL@$</customint6>
// 		$enrolment_manual->customint7="$@NULL@$";// 	<customint7>$@NULL@$</customint7>
// 		$enrolment_manual->customint8="$@NULL@$";// 	<customint8>$@NULL@$</customint8>
// 		$enrolment_manual->customchar1="$@NULL@$";// 	<customchar1>$@NULL@$</customchar1>
// 		$enrolment_manual->customchar2="$@NULL@$";// 	<customchar2>$@NULL@$</customchar2>
// 		$enrolment_manual->customchar3="$@NULL@$";// 	<customchar3>$@NULL@$</customchar3>
// 		$enrolment_manual->customdec1="$@NULL@$";// 	<customdec1>$@NULL@$</customdec1>
// 		$enrolment_manual->customdec2="$@NULL@$";// 	<customdec2>$@NULL@$</customdec2>
// 		$enrolment_manual->customtext1="$@NULL@$";// 	<customtext1>$@NULL@$</customtext1>
// 		$enrolment_manual->customtext2="$@NULL@$";// 	<customtext2>$@NULL@$</customtext2>
// 		$enrolment_manual->customtext3="$@NULL@$";// 	<customtext3>$@NULL@$</customtext3>
// 		$enrolment_manual->customtext4="$@NULL@$";// 	<customtext4>$@NULL@$</customtext4>		
// 		$enrolment_manual->timecreated=time();
// 		$enrolment_manual->timemodified=time();
	
// 		$enrolment_guest = new Enrolment();
// 		$enrolment_guest->id=11;
// 		$enrolment_guest->enrol="guest";
// 		$enrolment_guest->status=1;
// 		$enrolment_guest->name="$@NULL@$";
// 		$enrolment_guest->enrolperiod=0;
// 		$enrolment_guest->enrolstartdate=0;
// 		$enrolment_guest->enrolenddate=0;
// 		$enrolment_guest->notifyall=0;
// 		$enrolment_guest->password="$@NULL@$";
// 		$enrolment_guest->cost="$@NULL@$";
// 		$enrolment_guest->currency="$@NULL@$";
// 		$enrolment_guest->roleid=0;
// 		$enrolment_guest->customint1="$@NULL@$";// 	<customint1>$@NULL@$</customint1>
// 		$enrolment_guest->customint2="$@NULL@$";// 	<customint2>$@NULL@$</customint2>
// 		$enrolment_guest->customint3="$@NULL@$";// 	<customint3>$@NULL@$</customint3>
// 		$enrolment_guest->customint4="$@NULL@$";// 	<customint4>$@NULL@$</customint4>
// 		$enrolment_guest->customint5="$@NULL@$";// 	<customint5>$@NULL@$</customint5>
// 		$enrolment_guest->customint6="$@NULL@$";// 	<customint6>$@NULL@$</customint6>
// 		$enrolment_guest->customint7="$@NULL@$";// 	<customint7>$@NULL@$</customint7>
// 		$enrolment_guest->customint8="$@NULL@$";// 	<customint8>$@NULL@$</customint8>
// 		$enrolment_guest->customchar1="$@NULL@$";// 	<customchar1>$@NULL@$</customchar1>
// 		$enrolment_guest->customchar2="$@NULL@$";// 	<customchar2>$@NULL@$</customchar2>
// 		$enrolment_guest->customchar3="$@NULL@$";// 	<customchar3>$@NULL@$</customchar3>
// 		$enrolment_guest->customdec1="$@NULL@$";// 	<customdec1>$@NULL@$</customdec1>
// 		$enrolment_guest->customdec2="$@NULL@$";// 	<customdec2>$@NULL@$</customdec2>
// 		$enrolment_guest->customtext1="$@NULL@$";// 	<customtext1>$@NULL@$</customtext1>
// 		$enrolment_guest->customtext2="$@NULL@$";// 	<customtext2>$@NULL@$</customtext2>
// 		$enrolment_guest->customtext3="$@NULL@$";// 	<customtext3>$@NULL@$</customtext3>
// 		$enrolment_guest->customtext4="$@NULL@$";// 	<customtext4>$@NULL@$</customtext4>
// 		$enrolment_guest->timecreated=time();
// 		$enrolment_guest->timemodified=time();
	
// 		$enrolment_self = new Enrolment();
// 		$enrolment_self->id=12;
// 		$enrolment_self->enrol="self";
// 		$enrolment_self->status=1;
// 		$enrolment_self->name="$@NULL@$";
// 		$enrolment_self->enrolperiod=0;
// 		$enrolment_self->enrolstartdate=0;
// 		$enrolment_self->enrolenddate=0;
// 		$enrolment_self->notifyall=0;
// 		$enrolment_self->password="$@NULL@$";
// 		$enrolment_self->cost="$@NULL@$";
// 		$enrolment_self->currency="$@NULL@$";
// 		$enrolment_self->roleid=0;
// 		$enrolment_self->customint1=0;// 	<customint1>$@NULL@$</customint1>
// 		$enrolment_self->customint2=0;// 	<customint2>$@NULL@$</customint2>
// 		$enrolment_self->customint3=0;// 	<customint3>$@NULL@$</customint3>
// 		$enrolment_self->customint4=1;// 	<customint4>$@NULL@$</customint4>
// 		$enrolment_self->customint5=0;// 	<customint5>$@NULL@$</customint5>
// 		$enrolment_self->customint6=1;// 	<customint6>$@NULL@$</customint6>
// 		$enrolment_self->customint7="$@NULL@$";// 	<customint7>$@NULL@$</customint7>
// 		$enrolment_self->customint8="$@NULL@$";// 	<customint8>$@NULL@$</customint8>
// 		$enrolment_self->customchar1="$@NULL@$";// 	<customchar1>$@NULL@$</customchar1>
// 		$enrolment_self->customchar2="$@NULL@$";// 	<customchar2>$@NULL@$</customchar2>
// 		$enrolment_self->customchar3="$@NULL@$";// 	<customchar3>$@NULL@$</customchar3>
// 		$enrolment_self->customdec1="$@NULL@$";// 	<customdec1>$@NULL@$</customdec1>
// 		$enrolment_self->customdec2="$@NULL@$";// 	<customdec2>$@NULL@$</customdec2>
// 		$enrolment_self->customtext1="$@NULL@$";// 	<customtext1>$@NULL@$</customtext1>
// 		$enrolment_self->customtext2="$@NULL@$";// 	<customtext2>$@NULL@$</customtext2>
// 		$enrolment_self->customtext3="$@NULL@$";// 	<customtext3>$@NULL@$</customtext3>
// 		$enrolment_self->customtext4="$@NULL@$";// 	<customtext4>$@NULL@$</customtext4>
// 		$enrolment_self->timecreated=time();
// 		$enrolment_self->timemodified=time();
	
	
	
// 		$enrolments->enrolments[] = $enrolment_manual;
// 		$enrolments->enrolments[] = $enrolment_guest;
// 		$enrolments->enrolments[] = $enrolment_self;
	
		$courseModel->enrolments = $enrolments;
		
	
		$inforef = new InfoRef();
		//$inforef->roleids[]=5;
		
		$courseModel->inforef = $inforef;
		
		
		
		//Roles
		$roles = new RolesBackup();
		$courseModel->roles = $roles;
		
		//Events
		$events = new Events();
		$courseModel->events = $events;
		
		//Comments
		$comments = new Comments();
		$courseModel->comments = $comments;
		
		//Filters
		$filters = new Filters();
		$courseModel->filters = $filters;
		
		
		//Reference dans moodle_backup
		$moodleBackupCourse = new MoodleBackupContentsCourse();
		$moodleBackupCourse->directory="course";
		$moodleBackupCourse->courseid=$course->idnumber;
		$moodleBackupCourse->title=$course->shortname;
	
		$this->moodle_backup->contents->course=$moodleBackupCourse;
	
		$this->course = $courseModel;
	}
	
	
	/**
	 * Initialize SectionModel
	 */
	public function initializeSectionsModels(){
	
		$sectionModels = array();
	
		//DEFAULT SECTION
		$sectionModel = new SectionModel();
	
		//Default section used to put all the activities (for now)
		$section = new Section();
		$section->id=0;
		$section->number=0;
		$section->name="$@NULL@$";
		$section->summary="";
		$section->summaryformat=1;
		$section->visible=1;
		$section->availablefrom=0;
		$section->availableuntil=0;
		$section->showavailability=0;
		$section->groupingid=0;
	
		$sectionModel->section = $section;
	
		$infoRef = new InfoRef();		
		$sectionModel->inforef = $infoRef;
		
		$sectionModels[0]=$sectionModel;
		
		
		//Reference dans moodle_backup
		$moodleBackupSection = new MoodleBackupSectionsSection($section->id,$section->number,"sections/section_".$section->id);
	
		//moodle_backup settings
		$this->moodle_backup->settings[] = new MoodleBackupSectionSetting("section","section_".$section->id,"section_".$section->id."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupSectionSetting("section","section_".$section->id,"section_".$section->id."_userinfo",1);
	
		$this->moodle_backup->contents->sections[]=$moodleBackupSection;
	

		//SECTION DES EVALUATIONS
		$sectionModel = new SectionModel();
		
		//Default section used to put all the activities (for now)
		$section = new Section();
		$section->id=1;
		$section->number=1;
		$section->name="Evaluations";
		$section->summary="Liste de tous les quiz";
		$section->summaryformat=1;
		$section->visible=0;
		$section->availablefrom=0;
		$section->availableuntil=0;
		$section->showavailability=0;
		$section->groupingid=0;
		
		$sectionModel->section = $section;
		
		$infoRef = new InfoRef();
		$sectionModel->inforef = $infoRef;
	
		$sectionModels[1]=$sectionModel;
		
		$moodleBackupSection = new MoodleBackupSectionsSection($section->id,$section->number,"sections/section_".$section->id);
		
		//moodle_backup settings
		$this->moodle_backup->settings[] = new MoodleBackupSectionSetting("section","section_".$section->id,"section_".$section->id."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupSectionSetting("section","section_".$section->id,"section_".$section->id."_userinfo",1);
		
		$this->moodle_backup->contents->sections[]=$moodleBackupSection;
		
		
		//SECTION DES TACHES
		$sectionModel = new SectionModel();
		
		//Default section used to put all the activities (for now)
		$section = new Section();
		$section->id=2;
		$section->number=2;
		$section->name=utf8_encode("Tâches");
		$section->summary=utf8_encode("Liste de tous les tâches");
		$section->summaryformat=1;
		$section->visible=0;
		$section->availablefrom=0;
		$section->availableuntil=0;
		$section->showavailability=0;
		$section->groupingid=0;
		
		$sectionModel->section = $section;
		
		$infoRef = new InfoRef();
		$sectionModel->inforef = $infoRef;
		
		$sectionModels[2]=$sectionModel;
		
		$moodleBackupSection = new MoodleBackupSectionsSection($section->id,$section->number,"sections/section_".$section->id);
		
		//moodle_backup settings
		$this->moodle_backup->settings[] = new MoodleBackupSectionSetting("section","section_".$section->id,"section_".$section->id."_included",1);
		$this->moodle_backup->settings[] = new MoodleBackupSectionSetting("section","section_".$section->id,"section_".$section->id."_userinfo",1);
		
		$this->moodle_backup->contents->sections[]=$moodleBackupSection;
		
		
		$this->sections = $sectionModels;
	}
	
	/**
	 * Add a glossary
	 */
	public function addGlossary($glossaryId){
		
		global $USER;
		
		//Glossary
		$glossaryModel = new GlossaryModel();
		$glossaryModel->grades = new ActivityGradeBook(); //EMPTY CURRENTLY NOT NEEDED
		$glossaryModel->roles = new RolesBackup(); //EMPTY CURRENTLY NOT NEEDED
		$glossaryModel->comments = new Comments(); //EMPTY CURRENTLY NOT NEEDED
		$glossaryModel->completion = new ActivityCompletion(); //EMPTY CURRENTLY NOT NEEDED
		$glossaryModel->filters = new Filters(); //EMPTY CURRENTLY NOT NEEDED
		$glossaryModel->calendar = new Events(); //EMPTY CURRENTLY NOT NEEDED
		
		
		$glossaryModel->module = $this->createModule($glossaryId,"glossary","2013110500");
		
		$glossaryModel->glossary = $this->createGlossary($glossaryId, $glossaryModel->module);
		
		//reference dans moodle_backup
		$activity = new MoodleBackupActivity();
		$activity->moduleid=$glossaryModel->module->id;
		$activity->sectionid=0;
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
		
		$this->sections[0]->section->sequence[]= $glossaryModel->glossary->id;
	}
	
	/**
	 * @var unknown $glossaryId
	 * @var Module $module
	 * @return Glossary
	 */
	public function createGlossary($glossaryId, $module){
		$glossary = new Glossary();
		
		$glossary->id=$glossaryId;//		<activity id="1" moduleid="11" modulename="glossary" contextid="54">
		$glossary->moduleid =$module->id; //ID
		$glossary->modulename =$module->modulename;
		$glossary->contextid=0;
		$glossary->glossaryid=$glossaryId;
		$glossary->name ="Marc glossary";// 		<name>Marc glossary</name>
		$glossary->intro ="An alphabetical list of terms relating to this course, and their descriptions.";// 		<intro>&lt;p&gt;An alphabetical list of terms relating to this course, and their descriptions.&lt;/p&gt;</intro>
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
		
		$entry1 = new Entry();
		$entry1->id=1;// 		id="1">
		$entry1->userid=2;// 		<userid>2</userid>
		$entry1->concept="Entry1";// 		<concept>Entry1</concept>
		$entry1->definition="Entry 1 of glossary";// 		<definition>&lt;p&gt;Entry 1 of glossary&lt;/p&gt;</definition>
		$entry1->definitionformat=1;// 		<definitionformat>1</definitionformat>
		$entry1->definitiontrust=0;// 		<definitiontrust>0</definitiontrust>
		$entry1->attachment=1;// 		<attachment>1</attachment>
		$entry1->timecreated=time();// 		<timecreated>1390818856</timecreated>
		$entry1->timemodified=time();// 		<timemodified>1390818883</timemodified>
		$entry1->teacherentry=1;// 		<teacherentry>1</teacherentry>
		$entry1->sourceglossaryid=0;// 		<sourceglossaryid>0</sourceglossaryid>
		$entry1->usedynalink=0;// 		<usedynalink>0</usedynalink>
		$entry1->casesensitive=0;// 		<casesensitive>0</casesensitive>
		$entry1->fullmatch=1;// 		<fullmatch>0</fullmatch>
		$entry1->approved=1;// 		<approved>1</approved>
		
		$alias = new Alias(1,"test");
		$entry1->aliases[]=$alias;
		
		$this->addFileGlossaryFile();
		
		
		$entry2 = new Entry();
		$entry2->id=2;
		$entry2->userid=2;
		$entry2->concept="Second entry";
		$entry2->definition="Ma deuxieme entree";
		$entry2->definitionformat=1;
		$entry2->definitiontrust=0;
		$entry2->attachment="";
		$entry2->timecreated=time();
		$entry2->timemodified=time();
		$entry2->teacherentry=1;
		$entry2->sourceglossaryid=0;
		$entry2->usedynalink=1;
		$entry2->casesensitive=0;
		$entry2->fullmatch=0;
		$entry2->approved=1;
		
		$alias = new Alias(2,"Second");
		$entry2->aliases[]=$alias;
		
		$entry3 = new Entry();
		$entry3->id=3;
		$entry3->userid=2;
		$entry3->concept="Entry 3";
		$entry3->definition="Entry 3 description";
		$entry3->definitionformat=1;
		$entry3->definitiontrust=0;
		$entry3->attachment="";
		$entry3->timecreated=time();
		$entry3->timemodified=time();
		$entry3->teacherentry=1;
		$entry3->sourceglossaryid=0;
		$entry3->usedynalink=1;
		$entry3->casesensitive=0;
		$entry3->fullmatch=0;
		$entry3->approved=1;
		
		$alias = new Alias(3,"Entry3");
		$entry3->aliases[]=$alias;
		
		$glossary->entries[]=$entry1;
		$glossary->entries[]=$entry2;
		$glossary->entries[]=$entry3;
		
		$glossary->filesIds[]="101";
		$glossary->filesIds[]="102";
		
		
		return $glossary;
	}
	
	
	/**
	 * @return Module
	 */
	public function createModule($id, $name, $version){
		$module = new Module();
		
		$module->id=$id;// 		<module id="11" version="2013110500">
		$module->version= $version; //"2013110500";
		$module->modulename=$name;// 		<modulename>glossary</modulename>
		
		if($name=="quiz"){
			$module->sectionid=$this->sections[1]->section->id;
			$module->sectionnumber=$this->sections[1]->section->number;
			$module->visible=$this->sections[1]->section->visible;
		}else if($name=="assign"){
			$module->sectionid=$this->sections[2]->section->id;
			$module->sectionnumber=$this->sections[2]->section->number;
			$module->visible=$this->sections[2]->section->visible;
		}else {
			$module->sectionid=$this->sections[0]->section->id;// 		<sectionid>36</sectionid>
			$module->sectionnumber=$this->sections[0]->section->number;// 		<sectionnumber>0</sectionnumber>
			$module->visible=$this->sections[0]->section->visible;// 		<visible>1</visible>
		}
				
		$module->idnumber="";// 		<idnumber></idnumber>
		$module->added=time();// 		<added>1390818670</added>
		$module->score=0;// 		<score>0</score>
		$module->indent=0;// 		<indent>0</indent>
		$module->groupmode=0;// 		<groupmode>0</groupmode>
		$module->groupingid=0;// 		<groupingid>0</groupingid>
		$module->groupmembersonly=0;// 		<groupmembersonly>0</groupmembersonly>
		$module->visibleold=$module->visible;// 		<visibleold>1</visibleold>
		$module->completion=0;// 		<completion>0</completion>
		$module->completiongradeitemnumber="$@NULL@$";// 		<completiongradeitemnumber>$@NULL@$</completiongradeitemnumber>
		$module->completionview=0;// 		<completionview>0</completionview>
		$module->completionexpected=0;// 		<completionexpected>0</completionexpected>
		$module->availablefrom=0;// 		<availablefrom>0</availablefrom>
		$module->availableuntil=0;// 		<availableuntil>0</availableuntil>
		$module->showavailability=0;// 		<showavailability>0</showavailability>
		$module->showdescription=0;// 		<showdescription>0</showdescription>
				
		$module->availability_info = new AvailabilityInfo();
		
		return $module;
	}
	
	/*****************************************************************************************************************
	 * GLOSSARY
	 * 
	 */
	
	
	/**
	 * @param Entry $entry
	 * @param unknown $fileId
	 * @param Glossary $glossary
	 */
	public function addFileGlossaryFile($entry, $fileId, &$glossary){
		$repository = new FileBackup();
		$repository->id=101;
		$repository->contenthash="da39a3ee5e6b4b0d3255bfef95601890afd80709";// 		<contenthash>da39a3ee5e6b4b0d3255bfef95601890afd80709</contenthash>
		$repository->contextid=0;// 		<contextid>54</contextid> // ACTIVITY -- ICI GLOSSARY CONTEXT
		$repository->component="mod_glossary";// 		<component>mod_glossary</component>
		$repository->filearea="attachment";// 		<filearea>attachment</filearea>
		$repository->itemid=1;// 		<itemid>1</itemid> //GLOSSARY ID
		$repository->filepath="/";// 		<filepath>/</filepath>
		$repository->filename=".";// 		<filename>.</filename>
		$repository->userid=2;// 		<userid>2</userid>
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


		$file = new FileBackup();
		$file->id=102;
		$file->contenthash="432fdfe414023111171d02311035a270ee65254d";// 		<contenthash>da39a3ee5e6b4b0d3255bfef95601890afd80709</contenthash>
		$file->contextid=0;// 		<contextid>54</contextid>
		$file->component="mod_glossary";// 		<component>mod_glossary</component>
		$file->filearea="attachment";// 		<filearea>attachment</filearea>
		$file->itemid=1;// 		<itemid>1</itemid>
		$file->filepath="/";// 		<filepath>/</filepath>
		$file->filename="ComVerbale.odt";// 		<filename>.</filename>
		$file->userid=2;// 		<userid>2</userid>
		$file->filesize=25633;// 		<filesize>0</filesize>
		$file->mimetype="plication/vnd.oasis.opendocument.text";// 		<mimetype>document/unknown</mimetype>
		$file->status=0;// 		<status>0</status>
		$file->timecreated=time();// 		<timecreated>1390818824</timecreated>
		$file->timemodified=time();// 		<timemodified>1390818869</timemodified>
		$file->source="ComVerbale.odt";// 		<source>$@NULL@$</source>
		$file->author="Admin User";// 		<author>$@NULL@$</author>
		$file->license="allrightsreserved";// 		<license>$@NULL@$</license>
		$file->sortorder=0;// 		<sortorder>0</sortorder>
		$file->repositorytype="$@NULL@$";// 		<repositorytype>$@NULL@$</repositorytype>
		$file->repositoryid="$@NULL@$";// 		<repositoryid>$@NULL@$</repositoryid>
		$file->reference="$@NULL@$";// 		<reference>$@NULL@$</reference>

		$filename = "C:/Users/Marc/Documents/ComVerbale.odt" ;
		$file->content = file_get_contents($filename);

		$this->files->files[]=$repository;
		$this->files->files[]=$file;
	}
	
	
	
	public function toXMLFile($repository){
		
		$repository = $this->repository;
		
		$this->moodle_backup->toXMLFile($repository);
		$this->roles->toXMLFile($repository);
		$this->users->toXMLFile($repository);
		$this->questions->toXMLFile($repository);
		$this->badges->toXMLFile($repository);
		$this->completion->toXMLFile($repository);
		$this->files->toXMLFile($repository);
		$this->gradebook->toXMLFile($repository);
		$this->groups->toXMLFile($repository);
		$this->outcomes->toXMLFile($repository);
		$this->scales->toXMLFile($repository);
		
		
		//COURSE REPOSITORY
		$dir = $repository.'/course';
		
		if(is_dir($dir)){
			rrmdir($dir);
		}
		mkdir($dir);
		$this->course->course->toXMLFile($dir);
		$this->course->enrolments->toXMLFile($dir);
		$this->course->inforef->toXMLFile($dir);
		$this->course->roles->toXMLFile($dir);
		$this->course->events->toXMLFile($dir);
		$this->course->comments->toXMLFile($dir);
		$this->course->filters->toXMLFile($dir);
		
		
		//SECTIONS REPOSITORY
		$dir = $repository.'/sections';
		
		if(is_dir($dir)){
			rrmdir($dir);
		}
		mkdir($dir);
		foreach ($this->sections as $sectionModel){

			$sectionDir = $dir.'/section_'.$sectionModel->section->id; 
			
			if(is_dir($sectionDir)){
				rrmdir($sectionDir);
			}
			mkdir($sectionDir);
				
			$sectionModel->section->toXMLFile($sectionDir);
			$sectionModel->inforef->toXMLFile($sectionDir);
			
		}
		
		
		//ACTIVITIES REPOSITORY
		$dir = $repository.'/activities';
		
		if(is_dir($dir)){
			rrmdir($dir);
		}
		mkdir($dir);
		
		foreach ($this->activities as $activityModel){
			$activityDir="";
			if ($activityModel instanceof GlossaryModel) {
				$activityDir = $dir.'/glossary_'.$activityModel->module->id;
				
				if(is_dir($activityDir)){
					rrmdir($activityDir);
				}
				mkdir($activityDir);
				
				$activityModel->glossary->toXMLFile($activityDir);
				
			}else if ($activityModel instanceof QuizModel) {
				$activityDir = $dir.'/quiz_'.$activityModel->module->id;
			
				if(is_dir($activityDir)){
					rrmdir($activityDir);
				}
				mkdir($activityDir);
			
				$activityModel->quiz->toXMLFile($activityDir);
				
			}else if ($activityModel instanceof AssignmentModel) {
				$activityDir = $dir.'/assign_'.$activityModel->module->id;
				
			
				if(is_dir($activityDir)){
					rrmdir($activityDir);
				}
				mkdir($activityDir);
			
				$activityModel->assignment->toXMLFile($activityDir);
				$activityModel->grading->toXMLFile($activityDir);
			}else if($activityModel instanceof PageModel){
				$activityDir = $dir.'/page_'.$activityModel->module->id;			
				echo $activityDir . '</br>';
				if(is_dir($activityDir)){
					rrmdir($activityDir);
				}
				mkdir($activityDir);
				
			}else if ($activityModel instanceof FolderModel) {
				$activityDir = $dir.'/folder_'.$activityModel->module->id;
			
				$activityModel->page->toXMLFile($activityDir);			
			}else if ($activityModel instanceof RessourceModel){
				$activityDir = $dir.'/resource_'.$activityModel->module->id;
				echo $activityDir . '</br>';
				if(is_dir($activityDir)){
					rrmdir($activityDir);
				}
				mkdir($activityDir);
				
				$activityModel->folder->toXMLFile($activityDir);
				$activityModel->ressource->toXMLFile($activityDir);
			}
			
			$activityModel->calendar->toXMLFile($activityDir);
			
			$activityModel->comments->toXMLFile($activityDir);
			$activityModel->completion->toXMLFile($activityDir);
			$activityModel->filters->toXMLFile($activityDir);
			$activityModel->grades->toXMLFile($activityDir);
			$activityModel->inforef->toXMLFile($activityDir);
			$activityModel->module->toXMLFile($activityDir);
			$activityModel->roles->toXMLFile($activityDir);
		}
			
			
		}
		
	public function toMBZArchive($directory){
		
		echo '<br/>REPOSITORY = '.$this->repository."<br/>";
		
		$this->toXMLFile($this->repository);
		
		//zip the repertory to .mbz
		$packer = get_file_packer('application/vnd.moodle.backup');
		
		$packer->archive_to_pathname(array(null=>$this->repository), $directory."/".$this->moodle_backup->name);
	}
	
	
}

Class CourseModel {
	
	/**
	 * @var Course
	 */	
	public $course;
	
	
	/**
	 * @var Enrolments
	 */
	public $enrolments;

	
	/**
	 * @var InfoRef
	 */
	public $inforef;
	
	
	/**
	 * @var RolesBackup
	 */
	public $roles;
	
	
	/**
	 * @var Events
	 */
	public $events;
	
	/**
	 * @var Comments
	 */
	public $comments;
	
	
	/**
	 * @var Filters
	 */
	public $filters;

	/**
	 * @var BlockModel | Array
	 */
	public $blocks = array();
	
}

Class SectionModel {

	/**
	 * @var Section
	 */
	public $section;


	/**
	 * @var InfoRef
	 */
	public $inforef;

}


Class ActivityModel {

	/**
	 * @var Events
	 */
	public $calendar;

	/**
	 * @var Comments
	 */
	public $comments;
	
	
	/**
	 * @var ActivityCompletion
	 */
	public $completion;
	
	
	/**
	 * @var Filters
	 */
	public $filters;
	
	
	/**
	 * @var ActivityGradeBook
	 */
	public $grades;
	
	
	/**
	 * @var InfoRef
	 */
	public $inforef;

	/**
	 * @var Module
	 */
	public $module;
	
	/**
	 * @var RolesBackup
	 */
	public $roles;
	
	/**
	 * @var Grading
	 */
	public $grading;
	
	
}

class GlossaryModel extends ActivityModel {
	/**
	 * @var Glossary
	 */
	public $glossary;
	
}

class QuizModel extends ActivityModel {
	/**
	 * @var ActivityQuiz
	 */
	public $quiz;

}

class ForumModel extends ActivityModel {
	/**
	 * @var
	 */
	public $forum;

}

Class BlockModel {

	/**
	 * @var Block
	 */
	public $block;

}

class AssignmentModel extends ActivityModel {
	/**
	 * @var ActivityAssignment
	 */
	public $assignment;

}

class FolderModel extends ActivityModel {

class PageModel extends ActivityModel{
	/**
	 * @var ActivityFolder
	*@var ActivityPage
	*/
	public $page;
}

class RessourceModel extends ActivityModel{
	
	/**
	 * @var ActivityRessource
	 */
	public $ressource;
}
