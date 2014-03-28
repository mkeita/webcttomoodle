<?php

class SFTPConnection
{
    private $connection;
    private $sftp;

    public function __construct($host, $port=22)
    {
        $this->connection = @ssh2_connect($host, $port);
        if (! $this->connection){
        	echo "Could not connect to $host on port $port.";
            throw new Exception("Could not connect to $host on port $port.");
        }
    }

    public function login($username, $password)
    {
        if (! @ssh2_auth_password($this->connection, $username, $password)){
        	echo "Could not authenticate with username $username " .
                                "and password $password.";
            throw new Exception("Could not authenticate with username $username " .
                                "and password $password.");
        }

        $this->sftp = @ssh2_sftp($this->connection);
        if (! $this->sftp){
        	echo "Could not initialize SFTP subsystem.";
            throw new Exception("Could not initialize SFTP subsystem.");
        }
    }

    public function uploadFile($local_file, $remote_file)
    {
        /*$sftp = $this->sftp;
        $stream = @fopen("ssh2.sftp://$sftp$remote_file", 'w');

        if (! $stream)
            throw new Exception("Could not open file: $remote_file");

        $data_to_send = @file_get_contents($local_file);
        if ($data_to_send === false)
            throw new Exception("Could not open local file: $local_file.");

        if (@fwrite($stream, $data_to_send) === false)
            throw new Exception("Could not send data from file: $local_file.");

        @fclose($stream);*/
        
        ssh2_scp_send($this->connection, $local_file, $remote_file, 0777);
    }
    
    public function receiveFile($remote_file, $local_file)
    {    	
/*    	$sftp = $this->sftp;
    	
    	$stream = @fopen("ssh2.sftp://$sftp$remote_file", 'r');
       	
    	if (! $stream)
    		throw new Exception("Could not open file: $remote_file");
    	$size = $this->getFileSize($remote_file);
    	$contents = '';
    	$read = 0;
    	$len = $size;
    	while ($read < $len && ($buf = fread($stream, $len - $read))) {
    		$read += strlen($buf);
    		$contents .= $buf;
    	}
    	file_put_contents ($local_file, $contents);
    	@fclose($stream);*/
    	ssh2_scp_recv($this->connection, $remote_file, $local_file);    	 
    }
    
    public function getFileSize($file){
    	$sftp = $this->sftp;
    	return filesize("ssh2.sftp://$sftp$file");
    }
    
    function scanFilesystem($remote_file) {
    	if (!$this->sftp){
    		return null;
    	}
    	
    	$sftp = $this->sftp;
    	
    	$dir = "ssh2.sftp://$sftp$remote_file";
    	$tempArray = array();
    	if(is_dir($dir)){
	    	$handle = opendir($dir);
	    	// List all the files
	    	while (false !== ($file = readdir($handle))) {
	    		if (substr("$file", 0, 1) != "."){
	    			if(is_dir($file)){
	    				//                $tempArray[$file] = $this->scanFilesystem("$dir/$file");
	    			} else {
	    				$tempArray[]=$file;
	    			}
	    		}
	    	}
	    	closedir($handle);
    	}
    	return $tempArray;
    }
}