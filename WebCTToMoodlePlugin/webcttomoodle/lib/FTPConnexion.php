<?php
class FTPConnexion {
	public $ip='0.0.0.0';
	public $user='anonymous';
	public $password='';
	public $repository='/back1/';

	public function __construct($ip,$user,$password,$repository){
		$this->ip=$ip;
		$this->user=$user;
		$this->password=$password;
		$this->repository=$repository;
	}
}