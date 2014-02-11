<?php

/* 
*RAPPORT 2 = GROUPS / SELECTIVE CONDITIONS
*/

echo "Moodle Report 3 running ...\n";



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
$writer->openURI('report3.xml'); 
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
	        $writer->writeAttribute('code', utf8_encode($row2['SOURCE_ID']));
	        	        
	        //SECTION
	        $request2 = "SELECT * FROM LEARNING_CONTEXT WHERE PARENT_ID='".$courseId. "' AND TYPE_CODE='Section'";
	        $stid3 = oci_parse($conn,$request2);
	        oci_execute($stid3);
	        $counter3 = 0;
	        while ($row3 = oci_fetch_array($stid3, OCI_ASSOC+OCI_RETURN_NULLS)) {
	            $counter3++;       
	            $sectionId = $row3['ID'];    
	            $writer->startElement('section');
	            $writer->writeAttribute('name', utf8_encode($row3['NAME']));
	            $writer->writeAttribute('code', utf8_encode($row3['SOURCE_ID']));
	            
	            
	            //Get this section DELEVERY_CONTEXT ID!!
	            $requestDeliveryContext = "SELECT TEMPLATE_ID FROM CO_LC_ASSIGNMENT WHERE LEARNING_CONTEXT_ID='".$sectionId. "'";
	            $stidDeliveryContext = oci_parse($conn,$requestDeliveryContext);
	            oci_execute($stidDeliveryContext);
	            $deliveryContext = oci_fetch_array($stidDeliveryContext, OCI_ASSOC+OCI_RETURN_NULLS);
	            $deliveryContextId = $deliveryContext["TEMPLATE_ID"];
	            
	            
	          
	            //GROUPS
	            $count = 0;
	           	$request = "SELECT COUNT(*) FROM LEARNING_CONTEXT WHERE PARENT_ID='".$sectionId. "' AND TYPE_CODE='Group'";
                $stid = oci_parse($conn,$request);
                oci_execute($stid);
                $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                $count = $countDB["COUNT(*)"];
	            $writer->writeAttribute('groups', $count); 
	
	            
	            //RELEASE CRITERIA
	            $dateCriteriaCount=0;
	            $count = 0;
	            if($deliveryContextId!=NULL){
	            	//Get only non deleted assessment
	            	$request = "SELECT COUNT(*) FROM SR_CRITERIA WHERE HEAD_ID IN (SELECT HEAD_ID FROM SR_USAGE WHERE DELIVERY_CONTEXT_ID='".$deliveryContextId."' AND HEAD_ID IS NOT NULL)";
	            	$stid = oci_parse($conn,$request);
	            	oci_execute($stid);
	            	$countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	            	$count = $countDB["COUNT(*)"];
	            }
	            $dateCriteriaCount = $count;
	            $writer->writeAttribute('selective_release_criteria_total', $count);

	            $count = 0;
	            if($deliveryContextId!=NULL){
	            	//Get only non deleted assessment
	            	$request = "SELECT COUNT(*) FROM SR_CRITERIA WHERE HEAD_ID IN (SELECT HEAD_ID FROM SR_USAGE WHERE DELIVERY_CONTEXT_ID='".$deliveryContextId."' AND HEAD_ID IS NOT NULL) AND OPERAND='sr.compare.userId'";
	            	$stid = oci_parse($conn,$request);
	            	oci_execute($stid);
	            	$countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	            	$count = $countDB["COUNT(*)"];
	            }
	            $dateCriteriaCount-=$count;
	            $writer->writeAttribute('selective_release_criteria_userid', $count);
	             
	            $count = 0;
	            if($deliveryContextId!=NULL){
	            	//Get only non deleted assessment
	            	$request = "SELECT COUNT(*) FROM SR_CRITERIA WHERE HEAD_ID IN (SELECT HEAD_ID FROM SR_USAGE WHERE DELIVERY_CONTEXT_ID='".$deliveryContextId."' AND HEAD_ID IS NOT NULL) AND OPERAND='sr.compare.groupId'";
	            	$stid = oci_parse($conn,$request);
	            	oci_execute($stid);
	            	$countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	            	$count = $countDB["COUNT(*)"];
	            }
	            $dateCriteriaCount-=$count;
	            $writer->writeAttribute('selective_release_criteria_groupid', $count);
	            
	            
	            $count = 0;
	            if($deliveryContextId!=NULL){
	            	//Get only non deleted assessment
	            	$request = "SELECT COUNT(*) FROM SR_CRITERIA WHERE HEAD_ID IN (SELECT HEAD_ID FROM SR_USAGE WHERE DELIVERY_CONTEXT_ID='".$deliveryContextId."' AND HEAD_ID IS NOT NULL) AND OPERAND IS NULL";
	            	$stid = oci_parse($conn,$request);
	            	oci_execute($stid);
	            	$countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	            	$count = $countDB["COUNT(*)"];
	            }
	            $dateCriteriaCount-=$count;
	            $writer->writeAttribute('selective_release_criteria_grade', $count);
	            

	            $writer->writeAttribute('selective_release_criteria_date', $dateCriteriaCount);
	             	            
 	            $writer->endElement();
// 	            if($counter3>2){
// 	               break;
// 	            }
	            echo ''.$counter1.$counter2.$counter3."\n";
	            
	        }
        $writer->endElement();    
        
//         if($counter2>2){
//            break;
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
    
// 	}
}

oci_close($conn);

$writer->endElement();

$writer->endDocument(); 
$writer->flush();


echo 'END';