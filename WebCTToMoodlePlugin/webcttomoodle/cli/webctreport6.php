<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo "Moodle course conversion from Webct running ...\n";

//echo phpinfo();

// Connects to the XE service (i.e. database) on the "localhost" machine
//$conn = oci_connect('webct', 'admin','//localhost/XE');
//oci_close($conn);
//var_dump($conn);
//$conn = oci_connect('webct', 'admin','//localhost/XE');

//select sys_context('USERENV','SERVICE_NAME') from dual
        
//$db = '//localhost/LOCAL';
/**
 * @param unknown $repositoryId
 * @return boolean
 */
class RepositoryInfo {
	public $hasOnlyFiles;
	public $deep;
	
	public function __construct(){
		$this->hasOnlyFiles=true;
		$this->deep = 0;
	}
}

/**
 * @param unknown $repositoryId
 * @param unknown $connnection
 * @return RepositoryInfo
 */
function contentRepositoryhasOnlyFiles($repositoryId,$connnection,$deep){
	
	$repositoryInfo = new RepositoryInfo();
	
	$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$repositoryId."')";
	$stid = oci_parse($connnection,$request);
	oci_execute($stid);
	
	while($row = oci_fetch_assoc($stid)){
		if($row['CE_TYPE_NAME']=='ORGANIZER_PAGE_TYPE'){
			$repositoryInfoTmp =contentRepositoryhasOnlyFiles($row['ID'],$connnection,$deep+1) ;
			$repositoryInfo->hasOnlyFiles &= $repositoryInfoTmp->hasOnlyFiles;
			if($repositoryInfoTmp->deep>$deep){
				$deep = $repositoryInfoTmp->deep;
			}					
		}elseif($row['CE_TYPE_NAME']!="PAGE_TYPE"){
			$repositoryInfo->hasOnlyFiles =false;
		}
	}
	$repositoryInfo->deep = $deep;
	
	return $repositoryInfo;
}

$db = '(DESCRIPTION =
    (ADDRESS = (PROTOCOL = TCP)(HOST = 164.15.59.234)(PORT = 1521))
    (CONNECT_DATA =
      (SID = WEBCTORA)
    )
  )';

//$db_charset = 'WE8ISO8859P1'; //FRANCAIS
$conn = oci_connect('webct', 'ciTy4_',$db);

if (!$conn) {    
    $e = oci_error();
    var_dump($e);
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

$stid1 = oci_parse($conn, 'SELECT * FROM LC_CATEGORY');
oci_execute($stid1);


$writer = new XMLWriter(); 
$writer->openURI('report6.xml'); 
$writer->startDocument('1.0','UTF-8');

$writer->setIndent(4);

$writer->startElement("report");

$counter1 = 0;

while ($row = oci_fetch_array($stid1, OCI_ASSOC+OCI_RETURN_NULLS)) {
    $counter1++;
    
    $catId = $row['ID'];
    //CATEGORY
    $writer->startElement('category');
    $writer->writeAttribute('name', utf8_encode($row['NAME']));
    
    //COURSE
    $request1 = "SELECT * FROM LEARNING_CONTEXT WHERE "
            . "ID IN (SELECT LC_CATEGORIZATION.LEARNING_CONTEXT_ID FROM LC_CATEGORIZATION WHERE LC_CATEGORY_ID='".$catId. "')"
            . "AND TYPE_CODE='Course'";
    $stid2 = oci_parse($conn,$request1);
    oci_execute($stid2);
    
    $counter2 = 0;
    while ($row2 = oci_fetch_array($stid2, OCI_ASSOC+OCI_RETURN_NULLS)) {
        $counter2++;
        $courseId = $row2['ID'];
        
        $writer->startElement('course');        
        $writer->writeAttribute('name', utf8_encode($row2['NAME']));
        $writer->writeAttribute('code', $row2['SOURCE_ID']);
        
        
        //SECTION
        $request2 = "SELECT * FROM LEARNING_CONTEXT WHERE PARENT_ID='".$courseId. "' AND TYPE_CODE='Section'";
        $stid3 = oci_parse($conn,$request2);
        oci_execute($stid3);
        $counter3 = 0;
        while ($row3 = oci_fetch_array($stid3, OCI_ASSOC+OCI_RETURN_NULLS)) {
            $counter3++;       
            $sectionId = $row3['ID'];    
            
            //Get this section DELEVERY_CONTEXT ID!!
            $requestDeliveryContext = "SELECT TEMPLATE_ID FROM CO_LC_ASSIGNMENT WHERE LEARNING_CONTEXT_ID='".$sectionId. "'";
            $stidDeliveryContext = oci_parse($conn,$requestDeliveryContext);
            oci_execute($stidDeliveryContext);
            $deliveryContext = oci_fetch_array($stidDeliveryContext, OCI_ASSOC+OCI_RETURN_NULLS);
            $deliveryContextId = $deliveryContext["TEMPLATE_ID"];
            
            $writer->startElement('section');
	            $writer->writeAttribute('name', utf8_encode($row3['NAME']));
	            $writer->writeAttribute('code', $row3['SOURCE_ID']);
            
	            $request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='ORGANIZER_PAGE_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId."'";
	            $stid4 = oci_parse($conn,$request);
	            oci_execute($stid4);
	            
	            $row4 = oci_fetch_array($stid4, OCI_ASSOC+OCI_RETURN_NULLS);
	            $writer->writeAttribute('nb_repositories',$row4['COUNT(*)']-1);
	             
	            	            
	            if($deliveryContextId!=NULL){
	            	$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='ORGANIZER_PAGE_TYPE' 
							        AND PARENT_ID = (SELECT ID FROM CMS_CONTENT_ENTRY WHERE NAME='ORGANIZER_PAGE_HOME_PAGE_NAME' AND CE_TYPE_NAME='ORGANIZER_PAGE_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId."')
							        AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId."'";
	            	$stid4 = oci_parse($conn,$request);
	            	oci_execute($stid4);
	            	
	            	while ($row4 = oci_fetch_array($stid4, OCI_ASSOC+OCI_RETURN_NULLS)) {
	            		
	            		$writer->startElement('repository');
	            			$writer->writeAttribute('name', utf8_encode($row4['NAME']));	            			
	            			
	            			
	            			$repositoryInfo = contentRepositoryhasOnlyFiles($row4['ID'], $conn,0);
	            					            		
	            			$writer->writeAttribute('hasOnlyFiles', $repositoryInfo->hasOnlyFiles);
	            			$writer->writeAttribute('deep',$repositoryInfo->deep);
	            			
	            		$writer->endElement();
	            		
	            	}
	            	
	            }
	             
            $writer->endElement();
            
//             if($counter3>2){
//                 break;
//             }
            echo ''.$counter1.$counter2.$counter3."\n";
            
        }
        $writer->endElement();    
        
//         if($counter2>2){
//             break;
//         }

    }
    
    $writer->endElement();
    
    //foreach ($row as $item) {
        //Main repertory
        //$dirname = 'D:/Documents/ULB/Moodle Migration/webct_backup_'.$id;
        //rrmdir($dirname);
        //mkdir($dirname, 0777, true);
        //echo 'Repertory recreated';
        
        //$course = new CourseConverter($dirname, $conn, $id);
        //$course->convert();        
    //}
    
//     if($counter1>2){
//        break;
//     }
    
}

oci_close($conn);

$writer->endElement();

$writer->endDocument(); 
$writer->flush();


echo 'END';