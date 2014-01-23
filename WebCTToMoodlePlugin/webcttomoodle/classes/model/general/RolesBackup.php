<?php
require_once 'classes/model/IBackupModel.php';

class RolesBackup implements \IBackupModel {
	
	/**
	 * @var RoleOverride | Array
	 */
	public $role_overrides = array();

	/**
	 * @var RoleAssignment | Array
	 */
	public $role_assignments = array();
	
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/roles.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('roles');
		
			$writer->startElement('role_overrides');
			foreach ($this->role_overrides as $role_override){
			}
			$writer->endElement();
			
			$writer->startElement('role_assignments');
			foreach ($this->role_assignments as $role_assignment){
			}
			$writer->endElement();
		
		$writer->endElement();
		$writer->endDocument();
		
	}
}

class RoleOverride {

}

class RoleAssignment {

}