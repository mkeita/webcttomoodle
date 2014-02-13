<?php
require_once 'classes/model/IBackupModel.php';

class Files implements \IBackupModel {
	
	/**
	 * @var FileBackup | Array
	 */
	public $files = array() ;
	
	public function toXMLFile($repository) {
		$writer = new XMLWriter();
		$writer->openURI($repository.'/files.xml');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
		
		$writer->startElement('files');
		
		foreach ($this->files as $file){
			$writer->startElement('file');
				$writer->writeAttribute('id',$file->id);
				
				$writer->writeElement('contenthash',$file->contenthash);
				$writer->writeElement('contextid',$file->contextid);
				$writer->writeElement('component',$file->component);
				$writer->writeElement('filearea',$file->filearea);
				$writer->writeElement('itemid',$file->itemid);
				$writer->writeElement('filepath',$file->filepath);
				$writer->writeElement('filename',$file->filename);
				$writer->writeElement('userid',$file->userid);
				$writer->writeElement('filesize',$file->filesize);
				$writer->writeElement('mimetype',$file->mimetype);
				$writer->writeElement('status',$file->status);
				$writer->writeElement('timecreated',$file->timecreated);
				$writer->writeElement('timemodified',$file->timemodified);
				$writer->writeElement('source',$file->source);
				$writer->writeElement('author',$file->author);
				$writer->writeElement('license',$file->license);
				$writer->writeElement('sortorder',$file->sortorder);
				$writer->writeElement('repositorytype',$file->repositorytype);
				$writer->writeElement('repositoryid',$file->repositoryid);
				$writer->writeElement('reference',$file->reference);
				
				if(!empty($file->content)){
					$dir = $repository.'/files/'.substr($file->contenthash,0,2);
					if(!is_dir($dir)){
						mkdir($dir,0777, true);
					}
					$filename = $dir.'/'.$file->contenthash;
					if(!is_file($filename)){
						$fileContent = fopen($filename,'x');
						fwrite($fileContent, $file->content);
						fclose($fileContent);
					}
				}
				
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		
	}
}

class FileBackup {
	
	public $id;// 	id="102">
	
	public $contenthash;// 	<contenthash>432fdfe414023111171d02311035a270ee65254d</contenthash>
	public $contextid;// 	<contextid>54</contextid>
	public $component;// 	<component>mod_glossary</component>
	public $filearea;// 	<filearea>attachment</filearea>
	public $itemid;// 	<itemid>1</itemid>
	public $filepath;// 	<filepath>/</filepath>
	public $filename;// 	<filename>ComVerbale.odt</filename>
	public $userid;// 	<userid>2</userid>
	public $filesize;// 	<filesize>25633</filesize>
	public $mimetype;// 	<mimetype>application/vnd.oasis.opendocument.text</mimetype>
	public $status;// 	<status>0</status>
	public $timecreated;// 	<timecreated>1390818824</timecreated>
	public $timemodified;// 	<timemodified>1390818857</timemodified>
	public $source;// 	<source>ComVerbale.odt</source>
	public $author;// 	<author>Admin User</author>
	public $license;// 	<license>allrightsreserved</license>
	public $sortorder;// 	<sortorder>0</sortorder>
	public $repositorytype;// 	<repositorytype>$@NULL@$</repositorytype>
	public $repositoryid;// 	<repositoryid>$@NULL@$</repositoryid>
	public $reference;// 	<reference>$@NULL@$</reference>
	
	public $content; //Binary Content

}