<?php
require_once 'classes/model/IBackupModel.php';

class ActivityForum implements \IBackupModel {
	public $id;
	public $moduleid;
	public $modulename;	
	public $contextid ;

	public $forumId;
	public $type;
	public $name;
	public $intro;
	public $introformat;
	public $assessed;
	public $assesstimestart;
	public $assesstimefinish;
	public $scale;
	public $maxbytes;
	public $maxattachments;
	public $forcesubscribe;
	public $trackingtype;
	public $rsstype;
	public $rssarticles;
	public $timemodified;
	public $warnafter;
	public $blockafter;
	public $blockperiod;
	public $completiondiscussions;
	public $completionreplies;
	public $completionposts;
	public $displaywordcount;
	public $discussions;
	public $subscriptions;
	public $digests;
	public $readposts;	
	public $trackedprefs;
	
public function toXMLFile($repository) {
		
		$writer = new XMLWriter ();	
		$writer->openURI ( $repository. '/forum.xml' );
		$writer->startDocument ( '1.0', 'UTF-8' );
		$writer->setIndent(true);
			$writer->startElement ( 'activity' );
			$writer->writeAttribute ( 'id', $this->id );
			$writer->writeAttribute ( 'moduleid', $this->moduleid );
			$writer->writeAttribute ( 'modulename', $this->modulename );
			$writer->writeAttribute ( 'contextid', $this->contextid );	
				$writer->startElement ( 'forum' );
				$writer->writeAttribute ( 'id', $this->forumId );
					$writer->writeElement ( 'type', $this->type );
					$writer->writeElement ( 'name', $this->name );
					$writer->writeElement ( 'intro', $this->intro );
					$writer->writeElement ( 'introformat', $this->introformat );
					$writer->writeElement ( 'assessed', $this->assessed );
					$writer->writeElement ( 'assesstimestart', $this->assesstimestart );
					$writer->writeElement ( 'assesstimefinish', $this->assesstimefinish );
					$writer->writeElement ( 'scale', $this->scale );
					$writer->writeElement ( 'maxbytes', $this->maxbytes );
					$writer->writeElement ( 'maxattachments', $this->maxattachments );
					$writer->writeElement ( 'forcesubscribe', $this->forcesubscribe );
					$writer->writeElement ( 'trackingtype', $this->trackingtype );
					$writer->writeElement ( 'rsstype', $this->rsstype );
					$writer->writeElement ( 'rssarticles', $this->rssarticles );
					$writer->writeElement ( 'timemodified', $this->timemodified );
					$writer->writeElement ( 'warnafter', $this->warnafter );
					$writer->writeElement ( 'blockafter', $this->blockafter );
					$writer->writeElement ( 'blockperiod', $this->blockperiod );
					$writer->writeElement ( 'completiondiscussions', $this->completiondiscussions );
					$writer->writeElement ( 'completionreplies', $this->completionreplies );
					$writer->writeElement ( 'completionposts', $this->completionposts );
					$writer->writeElement ( 'displaywordcount', $this->displaywordcount );
					$writer->writeElement ( 'discussions', $this->discussions );
					$writer->writeElement ( 'subscriptions', $this->subscriptions );
					$writer->writeElement ( 'digests', $this->digests );
					$writer->writeElement ( 'readposts', $this->readposts );
					$writer->writeElement ( 'trackedprefs', $this->trackedprefs );					
				$writer->endElement ();
			$writer->endElement ();
		$writer->endDocument ();
	}


}


?>