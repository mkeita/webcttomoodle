<?php
require_once 'classes/model/IBackupModel.php';

class Roles implements \IBackupModel {
	
	/**
	 * @var Role | Array
	 */
	public $roles = array() ;
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/roles.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('roles_definition');
		
		foreach ($this->roles as $role){
			$writer->startElement('role');
				$writer->writeAttribute('id',$role->id);
				
				$writer->writeElement('name',$role->name);
				$writer->writeElement('shortname',$role->shortname);
				$writer->writeElement('nameincourse',$role->nameincourse);
				$writer->writeElement('description',$role->description);
				$writer->writeElement('sortorder',$role->sortorder);
				$writer->writeElement('archetype',$role->archetype);
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		
	}
}

class Role {
	public $id;// 	<role id="5">
	public $name;// 	<name></name>
	public $shortname;// 	<shortname>student</shortname>
	public $nameincourse;// 	<nameincourse>$@NULL@$</nameincourse>
	public $description;// 	<description></description>
	public $sortorder;// 	<sortorder>5</sortorder>
	public $archetype;// 	<archetype>student</archetype>

}