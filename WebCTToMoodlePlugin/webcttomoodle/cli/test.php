<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//phpinfo();
echo "Moodle course conversion from Webct running ...\n";

//echo phpinfo();

// Connects to the XE service (i.e. database) on the "localhost" machine
//$conn = oci_connect('webct', 'admin','//localhost/XE');
//oci_close($conn);
//var_dump($conn);
//$conn = oci_connect('webct', 'admin','//localhost/XE');

//select sys_context('USERENV','SERVICE_NAME') from dual
        
//$db = '//localhost/LOCAL';


$db = '(DESCRIPTION =
    (ADDRESS = (PROTOCOL = TCP)(HOST = 164.15.59.234)(PORT = 1521))
    (CONNECT_DATA =
      (SID = WEBCTORA)
    )
  )';

$db_charset = 'WE8ISO8859P1'; //FRANCAIS
$conn = oci_connect('webct', 'ciTy4_',$db, $db_charset);

if (!$conn) {    
    $e = oci_error();
    var_dump($e);
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

$stid1 = oci_parse($conn, "SELECT * FROM CMS_FILE_CONTENT WHERE ID='366251499001'");
oci_execute($stid1);


while ($row = oci_fetch_array($stid1, OCI_ASSOC+OCI_RETURN_NULLS)) {
	
	$a = $row["CONTENT"];
	$content = $a->load();
	
	
	
	$file = fopen("D://test.odt","x");
	fwrite($file, $content);
	fclose($file);
}

oci_close($conn);


echo 'END';