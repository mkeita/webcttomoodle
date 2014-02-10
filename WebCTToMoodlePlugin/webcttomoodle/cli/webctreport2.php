<?php

/* 
*RAPPORT 2 = QUESTIONS DANS WEBCT
*/

echo "Moodle Report 2 running ...\n";



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
$writer->openURI('report2.xml'); 
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
	            
	            
	          
	            //QUESTIONS
	            $count = 0;
	            if($deliveryContextId!=NULL){
	                //Get only non deleted assessment
	                $request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='Question' AND CE_SUBTYPE_NAME='Calculated' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId. "'";
	                $stid = oci_parse($conn,$request);
	                oci_execute($stid);
	                $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	                $count = $countDB["COUNT(*)"];
	            }
	            $writer->writeAttribute('Calculated', $count); 
	
	            
	            $count = 0;
	            if($deliveryContextId!=NULL){
	            	//Get only non deleted assessment
	            	$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='Question' AND CE_SUBTYPE_NAME='CombinationMultipleChoice' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId. "'";
	            	$stid = oci_parse($conn,$request);
	            	oci_execute($stid);
	            	$countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	            	$count = $countDB["COUNT(*)"];
	            }
	            $writer->writeAttribute('CombinationMultipleChoice', $count);
	            
	            $count = 0;
	            if($deliveryContextId!=NULL){
	            	//Get only non deleted assessment
	            	$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='Question' AND CE_SUBTYPE_NAME='FillInTheBlank' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId. "'";
	            	$stid = oci_parse($conn,$request);
	            	oci_execute($stid);
	            	$countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	            	$count = $countDB["COUNT(*)"];
	            }
	            $writer->writeAttribute('FillInTheBlank', $count);
	            
	            $count = 0;
	            if($deliveryContextId!=NULL){
	            	//Get only non deleted assessment
	            	$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='Question' AND CE_SUBTYPE_NAME='JumbledSentence' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId. "'";
	            	$stid = oci_parse($conn,$request);
	            	oci_execute($stid);
	            	$countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	            	$count = $countDB["COUNT(*)"];
	            }
	            $writer->writeAttribute('JumbledSentence', $count);
	
	            $count = 0;
	            if($deliveryContextId!=NULL){
	            	//Get only non deleted assessment
	            	$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='Question' AND CE_SUBTYPE_NAME='Matching' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId. "'";
	            	$stid = oci_parse($conn,$request);
	            	oci_execute($stid);
	            	$countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	            	$count = $countDB["COUNT(*)"];
	            }
	            $writer->writeAttribute('Matching', $count);
	            
	            
	            $count = 0;
	            if($deliveryContextId!=NULL){
	            	//Get only non deleted assessment
	            	$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='Question' AND CE_SUBTYPE_NAME='MultipleChoice' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId. "'";
	            	$stid = oci_parse($conn,$request);
	            	oci_execute($stid);
	            	$countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	            	$count = $countDB["COUNT(*)"];
	            }
	            $writer->writeAttribute('MultipleChoice', $count);
	            
	            
	            $count = 0;
	            if($deliveryContextId!=NULL){
	            	//Get only non deleted assessment
	            	$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='Question' AND CE_SUBTYPE_NAME='Paragraph' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId. "'";
	            	$stid = oci_parse($conn,$request);
	            	oci_execute($stid);
	            	$countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	            	$count = $countDB["COUNT(*)"];
	            }
	            $writer->writeAttribute('Paragraph', $count);
	            
	            $count = 0;
	            if($deliveryContextId!=NULL){
	            	//Get only non deleted assessment
	            	$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='Question' AND CE_SUBTYPE_NAME='ShortAnswer' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId. "'";
	            	$stid = oci_parse($conn,$request);
	            	oci_execute($stid);
	            	$countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	            	$count = $countDB["COUNT(*)"];
	            }
	            $writer->writeAttribute('ShortAnswer', $count);
	             
	            $count = 0;
	            if($deliveryContextId!=NULL){
	            	//Get only non deleted assessment
	            	$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='Question' AND CE_SUBTYPE_NAME='TrueFalse' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId. "'";
	            	$stid = oci_parse($conn,$request);
	            	oci_execute($stid);
	            	$countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
	            	$count = $countDB["COUNT(*)"];
	            }
	            $writer->writeAttribute('TrueFalse', $count);
	            
	            
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