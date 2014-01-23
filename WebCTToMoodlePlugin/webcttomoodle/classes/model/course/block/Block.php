<?php
require_once 'classes/model/IBackupModel.php';

class Block implements \IBackupModel {
	
	//id="26" contextid="44" version="2013110500"
	public $id;
	public $contextid;
	public $version;
	
	
	public $blockname;// 	<blockname>news_items</blockname>
	public $parentcontextid;// 	<parentcontextid>42</parentcontextid>
	public $showinsubcontexts;// 	<showinsubcontexts>0</showinsubcontexts>
	public $pagetypepattern;// 	<pagetypepattern>course-view-*</pagetypepattern>
	public $subpagepattern;// 	<subpagepattern>$@NULL@$</subpagepattern>
	public $defaultregion;// 	<defaultregion>side-post</defaultregion>
	public $defaultweight;// 	<defaultweight>1</defaultweight>
	
	
	public $configdata; //ConfigData 
	public $bloc_position = array();
	
	public function toXMLFile($repository) {
		// TODO Auto-generated method stub
	}
}

class ConfigData {
	
}

class BlockPosition {
}

