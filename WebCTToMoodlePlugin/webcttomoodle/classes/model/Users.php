<?php
require_once 'classes/model/IBackupModel.php';

class Users implements \IBackupModel {
	
	/**
	 * @var User | Array
	 */
	public $users = array() ;
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/users.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('users');
		
		foreach ($this->users as $user){
 			$writer->startElement('user');
				$writer->writeAttribute('id',$user->id);
				$writer->writeAttribute('contextid',$user->contextid);
				
				$writer->writeElement('username',$user->username);
				$writer->writeElement('idnumber',$user->idnumber);
				$writer->writeElement('email',$user->email);
				$writer->writeElement('icq',$user->icq);
				$writer->writeElement('skype',$user->skype);
				$writer->writeElement('yahoo',$user->yahoo);
				$writer->writeElement('aim',$user->aim);
				$writer->writeElement('msn',$user->msn);
				$writer->writeElement('phone1',$user->phone1);
				$writer->writeElement('phone2',$user->phone2);
				$writer->writeElement('institution',$user->institution);
				$writer->writeElement('department',$user->department);
				$writer->writeElement('address',$user->address);
				$writer->writeElement('city',$user->city);
				$writer->writeElement('country',$user->country);
				$writer->writeElement('lastip',$user->lastip);
				$writer->writeElement('picture',$user->picture);
				$writer->writeElement('url',$user->url);
				$writer->writeElement('description',$user->description);
				$writer->writeElement('descriptionformat',$user->descriptionformat);
				$writer->writeElement('imagealt',$user->imagealt);
				$writer->writeElement('auth',$user->auth);
				$writer->writeElement('firstnamephonetic',$user->firstnamephonetic);
				$writer->writeElement('lastnamephonetic',$user->lastnamephonetic);
				$writer->writeElement('middlename',$user->middlename);
				$writer->writeElement('alternatename',$user->alternatename);
				$writer->writeElement('firstname',$user->firstname);
				$writer->writeElement('lastname',$user->lastname);
				$writer->writeElement('confirmed',$user->confirmed);
				$writer->writeElement('policyagreed',$user->policyagreed);
				$writer->writeElement('deleted',$user->deleted);
				$writer->writeElement('lang',$user->lang);
				$writer->writeElement('theme',$user->theme);
				$writer->writeElement('timezone',$user->timezone);
				$writer->writeElement('firstaccess',$user->firstaccess);
				$writer->writeElement('lastaccess',$user->lastaccess);
				$writer->writeElement('lastlogin',$user->lastlogin);
				$writer->writeElement('currentlogin',$user->currentlogin);
				$writer->writeElement('mailformat',$user->mailformat);
				$writer->writeElement('maildigest',$user->maildigest);
				$writer->writeElement('maildisplay',$user->maildisplay);
				$writer->writeElement('autosubscribe',$user->autosubscribe);
				$writer->writeElement('trackforums',$user->trackforums);
				$writer->writeElement('timecreated',$user->timecreated);
				$writer->writeElement('timemodified',$user->timemodified);
				$writer->writeElement('trustbitmask',$user->trustbitmask);
				
				$writer->startElement('custom_fields');
				$writer->endElement();
				$writer->startElement('tags');
				$writer->endElement();
				
				$writer->startElement('preferences');
				foreach ($user->preferences as $preference){
					$writer->startElement('preference');
						$writer->writeAttribute('id',$preference->id);
						
						$writer->writeElement('name',$preference->name);
						$writer->writeElement('value',$preference->value);
					$writer->endElement();
				}
				$writer->endElement();
				
				$writer->startElement('roles');
					$writer->startElement('role_overrides');
					$writer->endElement();						
					$writer->startElement('role_assignments');
					$writer->endElement();
				$writer->endElement();
				
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		
	}
}

class User {
	
	public $id;// 	id="2" contextid="5">
	public $contextid;
	
	public $username;// 	<username>admin</username>
	public $idnumber;// 	<idnumber></idnumber>
	public $email;// 	<email>keitamarc@hotmail.com</email>
	public $icq;// 	<icq></icq>
	public $skype;// 	<skype></skype>
	public $yahoo;// 	<yahoo></yahoo>
	public $aim;// 	<aim></aim>
	public $msn;// 	<msn></msn>
	public $phone1;// 	<phone1></phone1>
	public $phone2;// 	<phone2></phone2>
	public $institution;// 	<institution></institution>
	public $department;// 	<department></department>
	public $address;// 	<address></address>
	public $city;// 	<city></city>
	public $country;// 	<country></country>
	public $lastip;// 	<lastip>127.0.0.1</lastip>
	public $picture;// 	<picture>0</picture>
	public $url;// 	<url></url>
	public $description;// 	<description></description>
	public $descriptionformat;// 	<descriptionformat>1</descriptionformat>
	public $imagealt;// 	<imagealt>$@NULL@$</imagealt>
	public $auth;// 	<auth>manual</auth>
	public $firstnamephonetic;// 	<firstnamephonetic></firstnamephonetic>
	public $lastnamephonetic;// 	<lastnamephonetic></lastnamephonetic>
	public $middlename;// 	<middlename></middlename>
	public $alternatename;// 	<alternatename></alternatename>
	public $firstname;// 	<firstname>Admin</firstname>
	public $lastname;// 	<lastname>User</lastname>
	public $confirmed;// 	<confirmed>1</confirmed>
	public $policyagreed;// 	<policyagreed>0</policyagreed>
	public $deleted;// 	<deleted>0</deleted>
	public $lang;// 	<lang>en</lang>
	public $theme;// 	<theme></theme>
	public $timezone;// 	<timezone>99</timezone>
	public $firstaccess;// 	<firstaccess>1389139014</firstaccess>
	public $lastaccess;// 	<lastaccess>1390830410</lastaccess>
	public $lastlogin;// 	<lastlogin>1390808544</lastlogin>
	public $currentlogin;// 	<currentlogin>1390829789</currentlogin>
	public $mailformat;// 	<mailformat>1</mailformat>
	public $maildigest;// 	<maildigest>0</maildigest>
	public $maildisplay;// 	<maildisplay>1</maildisplay>
	public $autosubscribe;// 	<autosubscribe>1</autosubscribe>
	public $trackforums;// 	<trackforums>0</trackforums>
	public $timecreated;// 	<timecreated>0</timecreated>
	public $timemodified;// 	<timemodified>1390829789</timemodified>
	public $trustbitmask;// 	<trustbitmask>0</trustbitmask>

	public $custom_fields=array();
	public $tags=array();
	
	
	/**
	 * @var UserPreference | Array
	 */
	public $preferences=array();
	
	/**
	 * @var RolesBackup | Array
	 */
	public $roles=array();
}


class UserPreference {
	public $id;// 	<preference id="1">
	public $name;// 	<name>htmleditor</name>
	public $value;// 	<value></value>
	
	public function __construct($id,$name,$value){
		$this->id=$id;
		$this->name=$name;
		$this->value=$value;
	}
}