<?php
interface IBackupModel {
	
	/**
	 * @param unknown $var (directory or XMLWriter)
	 */
	public function toXMLFile($var);
}