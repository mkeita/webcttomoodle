<?php
class MigrationConnexion {
	/**
	 * 0 --> SFTP
	 * 1 --> FTP
	 * 2 --> LOCAL
	 * @var int
	 */
	public $protocol=0;
	public $ip='0.0.0.0';
	public $user='anonymous';
	public $password='';
	public $repository='/back1/';

	public function __construct($protocol,$ip,$user,$password,$repository){
		$this->protocol=$protocol;
		$this->ip=$ip;
		$this->user=$user;
		$this->password=$password;
		$this->repository=$repository;
	}
}