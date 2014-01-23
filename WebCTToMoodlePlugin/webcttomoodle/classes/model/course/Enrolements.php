<?php
require_once 'classes/model/IBackupModel.php';


class Enrolments implements \IBackupModel {
	
	/**
	 * @var Enrolment|Array
	 */
	public $enrolments = array();
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/enrolments.xml');
		$writer->startDocument('1.0','UTF-8');
			$writer->startElement('enrolments');
				$writer->startElement('enrols');
				
				foreach ($this->enrolments as $enrolment){
					$writer->startElement('enrol');
					$writer->writeAttribute('id', $enrolment->id);
					
					$writer->writeElement('enrol',$enrolment->enrol);
					$writer->writeElement('status',$enrolment->status);
					$writer->writeElement('name',$enrolment->name);
					$writer->writeElement('enrolperiod',$enrolment->enrolperiod);
					$writer->writeElement('enrolstartdate',$enrolment->enrolstartdate);
					$writer->writeElement('enrolenddate',$enrolment->enrolenddate);
					$writer->writeElement('expirynotify',$enrolment->expirynotify);
					$writer->writeElement('notifyall',$enrolment->notifyall);
					$writer->writeElement('password',$enrolment->password);
					$writer->writeElement('cost',$enrolment->cost);
					$writer->writeElement('currency',$enrolment->currency);
					$writer->writeElement('roleid',$enrolment->roleid);
					$writer->writeElement('customint1',$enrolment->customint1);
					$writer->writeElement('customint2',$enrolment->customint2);
					$writer->writeElement('customint3',$enrolment->customint3);
					$writer->writeElement('customint4',$enrolment->customint4);
					$writer->writeElement('customint5',$enrolment->customint5);
					$writer->writeElement('customint6',$enrolment->customint6);
					$writer->writeElement('customint7',$enrolment->customint7);
					$writer->writeElement('customint8',$enrolment->customint8);
					$writer->writeElement('customchar1',$enrolment->customchar1);
					$writer->writeElement('customchar2',$enrolment->customchar2);
					$writer->writeElement('customchar3',$enrolment->customchar3);
					$writer->writeElement('customdec1',$enrolment->customdec1);
					$writer->writeElement('customdec2',$enrolment->customdec2);
					$writer->writeElement('customtext1',$enrolment->customtext1);
					$writer->writeElement('customtext2',$enrolment->customtext2);
					$writer->writeElement('customtext3',$enrolment->customtext3);
					$writer->writeElement('customtext4',$enrolment->customtext4);
					$writer->writeElement('timecreated',$enrolment->timecreated);
					$writer->writeElement('timemodified',$enrolment->timemodified);
					
					$writer->endElement();
				}
				
				$writer->endElement();
			$writer->endElement();
		$writer->endDocument();
	}
	
}





class Enrolment {

	public $id; //id="10"
	
	public $enrol;// 	<enrol>manual</enrol>
	public $status;// 	<status>0</status>
	public $name;// 	<name>$@NULL@$</name>
	public $enrolperiod;// 	<enrolperiod>0</enrolperiod>
	public $enrolstartdate;// 	<enrolstartdate>0</enrolstartdate>
	public $enrolenddate;// 	<enrolenddate>0</enrolenddate>
	public $expirynotify;// 	<expirynotify>0</expirynotify>
	public $notifyall;// 	<notifyall>0</notifyall>
	public $password;// 	<password>$@NULL@$</password>
	public $cost;// 	<cost>$@NULL@$</cost>
	public $currency;// 	<currency>$@NULL@$</currency>
	public $roleid;// 	<roleid>5</roleid>
	public $customint1;// 	<customint1>$@NULL@$</customint1>
	public $customint2;// 	<customint2>$@NULL@$</customint2>
	public $customint3;// 	<customint3>$@NULL@$</customint3>
	public $customint4;// 	<customint4>$@NULL@$</customint4>
	public $customint5;// 	<customint5>$@NULL@$</customint5>
	public $customint6;// 	<customint6>$@NULL@$</customint6>
	public $customint7;// 	<customint7>$@NULL@$</customint7>
	public $customint8;// 	<customint8>$@NULL@$</customint8>
	public $customchar1;// 	<customchar1>$@NULL@$</customchar1>
	public $customchar2;// 	<customchar2>$@NULL@$</customchar2>
	public $customchar3;// 	<customchar3>$@NULL@$</customchar3>
	public $customdec1;// 	<customdec1>$@NULL@$</customdec1>
	public $customdec2;// 	<customdec2>$@NULL@$</customdec2>
	public $customtext1;// 	<customtext1>$@NULL@$</customtext1>
	public $customtext2;// 	<customtext2>$@NULL@$</customtext2>
	public $customtext3;// 	<customtext3>$@NULL@$</customtext3>
	public $customtext4;// 	<customtext4>$@NULL@$</customtext4>
	public $timecreated;// 	<timecreated>1390206206</timecreated>
	public $timemodified;// 	<timemodified>1390206206</timemodified>
	
	public $userEnrolment = array(); //EnrolmentUserEnrolment
	
	
}

class EnrolmentUserEnrolment {
	
}
