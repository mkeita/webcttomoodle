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
$writer->openURI('report5.xml'); 
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
            
            
            //Get this section DELEVERY_CONTEXT ID!!
            $requestDeliveryContext = "SELECT TEMPLATE_ID FROM CO_LC_ASSIGNMENT WHERE LEARNING_CONTEXT_ID='".$sectionId. "'";
            $stidDeliveryContext = oci_parse($conn,$requestDeliveryContext);
            oci_execute($stidDeliveryContext);
            $deliveryContext = oci_fetch_array($stidDeliveryContext, OCI_ASSOC+OCI_RETURN_NULLS);
            $deliveryContextId = $deliveryContext["TEMPLATE_ID"];
            
            $writer->startElement('section');
	            $writer->writeAttribute('name', utf8_encode($row3['NAME']));
	            $writer->writeAttribute('code', utf8_encode($row3['SOURCE_ID']));
            
	            $request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='ORGANIZER_PAGE_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId."'";
	            $stid4 = oci_parse($conn,$request);
	            oci_execute($stid4);
	            
	            $row4 = oci_fetch_array($stid4, OCI_ASSOC+OCI_RETURN_NULLS);
	            $writer->writeAttribute('nb_repositories',$row4['COUNT(*)']-1);
	             
	            
	            
	            //LEARNING MODULE
	            if($deliveryContextId!=NULL){
	            	$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='ORGANIZER_PAGE_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId."'";
	            	$stid4 = oci_parse($conn,$request);
	            	oci_execute($stid4);
	            	
	            	while ($row4 = oci_fetch_array($stid4, OCI_ASSOC+OCI_RETURN_NULLS)) {
	            		
	            		$writer->startElement('repository');
	            			$writer->writeAttribute('name', utf8_encode($row4['NAME']));	            			
	            			
	            			//ACTION ALL INTERNAL LINKS
	            			$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')";
	            			$stid5 = oci_parse($conn,$request);
	            			oci_execute($stid5);
	            			$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
	            			$totalElements = $row5['COUNT(*)'];
	            			$writer->writeAttribute('total_elements', $totalElements);
	            			
	            			//PAGE INCLUDE IN WEB_CT
	            			$request = "SELECT * FROM CMS_CONTENT_ENTRY	            						  	
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='PAGE_TYPE'";
	            			$stid5 = oci_parse($conn,$request);
	            			oci_execute($stid5);
	            			
	            			$countPages = 0;
	            			$countTocPages = 0;
	            			$countPDF=0;
	            			$countVideo = 0;
	            			$countPowerPoint = 0;
	            			$countImage = 0;
	            			$countTocOthers = 0;
	            			$countWord=0;
	            			$countFlash=0;
	            			$countExcel=0;
	            			$countHTML=0;
	            			$countAudio=0;
	            			$countText=0;
	            			$countArchive=0;

	            			while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
	            				$countPages++;
	            				if(empty($row5['FILE_CONTENT_ID'])){
	            					
	            					$request = "SELECT CMS_MIMETYPE.CE_TYPE_NAME,CMS_MIMETYPE.CE_SUBTYPE_NAME FROM CMS_CONTENT_ENTRY
												  LEFT JOIN CMS_FILE_CONTENT ON CMS_CONTENT_ENTRY.FILE_CONTENT_ID=CMS_FILE_CONTENT.ID
												  LEFT JOIN CMS_MIMETYPE ON CMS_FILE_CONTENT.MIMETYPE_ID=CMS_MIMETYPE.ID
												  WHERE CMS_CONTENT_ENTRY.ID = (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row5['ID']."' AND LINK_TYPE_ID='30004')";

			            			$stid6 = oci_parse($conn,$request);
	    	 		       			oci_execute($stid6);
	    	 		       			$row6 = oci_fetch_array($stid6, OCI_ASSOC+OCI_RETURN_NULLS);
	    	 		       			$subtype = $row6['CE_SUBTYPE_NAME'];
									if($subtype=='Other Files'){
										$countTocOthers++;
									}elseif($subtype =="Adobe PDF"){
										$countPDF++;
									}elseif($subtype =="Power Point"){
										$countPowerPoint++;
									}elseif($subtype =="Video"){
										$countVideo++;
									}elseif($subtype =="Image"){
										$countImage++;
									}elseif($subtype =="Word"){
										$countWord++;
									}elseif($subtype =="Excel"){
										$countExcel++;
									}elseif($subtype =="Flash"){
										$countFlash++;
									}elseif($subtype =="HTML"){
										$countHTML++;
									}elseif($subtype =="Audio"){
										$countAudio++;
									}elseif($subtype =="Text"){
										$countText++;
									}elseif($subtype =="Archive"){
										$countArchive++;
									}else {
										echo '----------------->'.$row6['CE_SUBTYPE_NAME'].PHP_EOL;
									}
													  		
	            				}else {
	            					$countTocPages++;
	            				}
	            			}
	            			$writer->startElement('pages');
		            			$writer->writeAttribute('pages_total', $countPages);
		            			$writer->writeAttribute('pages_simple', $countTocPages);
		            			$writer->writeAttribute('pages_pdf', $countPDF);
		            			$writer->writeAttribute('pages_word', $countWord);
		            			$writer->writeAttribute('pages_excel', $countExcel);
		            			$writer->writeAttribute('pages_image', $countImage);
		            			$writer->writeAttribute('pages_powerpoint', $countPowerPoint);
		            			$writer->writeAttribute('pages_video', $countVideo);
		            			$writer->writeAttribute('pages_flash', $countFlash);
		            			$writer->writeAttribute('pages_html', $countHTML);
		            			$writer->writeAttribute('pages_audio', $countAudio);
		            			$writer->writeAttribute('pages_text', $countText);
		            			$writer->writeAttribute('pages_archive', $countArchive);
		            			$writer->writeAttribute('pages_others', $countTocOthers);
		            			
		            		$writer->endElement();
	            			
	            			
		            		//ASSESSMENTS INCLUDE IN WEB_CT
		            		$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='ASSESSMENT_TYPE'";
		            		$stid5 = oci_parse($conn,$request);
		            		oci_execute($stid5);
		            		$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
		            		$countAssessments=$row5['COUNT(*)'];
		            		//$countAssessments = 0;
		            		//while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
		            		//	$countAssessments++;
		            		//}
		            		$writer->startElement('assessments');		            		
		            			$writer->writeAttribute('assessments_total', $countAssessments);
		            		$writer->endElement();
		            		
		            		
	            			//ASSIGNMENTS INCLUDE IN WEB_CT
	            			$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY  
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='PROJECT_TYPE'";
	            			$stid5 = oci_parse($conn,$request);
	            			oci_execute($stid5);
	            			$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
	            			$countAssignments=$row5['COUNT(*)'];
	            			
// 	            			$countAssignments = 0;
// 	            			while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
// 	            				$countAssignments++;
// 	            			}
	            			$writer->startElement('assignments');	            			
		            			$writer->writeAttribute('assignments_total', $countAssignments);
		            		$writer->endElement();
		            			
		            		
		            		
		            		//TOC INCLUDE IN WEB_CT
		            		$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='TOC_TYPE'";
		            		$stid5 = oci_parse($conn,$request);
		            		oci_execute($stid5);
		            		$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
		            		$countTOCs=$row5['COUNT(*)'];
// 		            		$countTOCs = 0;
// 		            		while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
// 		            			$countTOCs++;
// 		            		}
		            		$writer->startElement('learning_modules');
		            		$writer->writeAttribute('learning_modules_total', $countTOCs);
		            		$writer->endElement();
		            		
		            		//LIENS INCLUDE IN WEB_CT
		            		$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='URL_TYPE'";
		            		$stid5 = oci_parse($conn,$request);
		            		oci_execute($stid5);
		            		$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
		            		$countURLs=$row5['COUNT(*)'];
// 		            		$countURLs = 0;
// 		            		while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
// 		            			$countURLs++;
// 		            		}
		            		$writer->startElement('urls');
		            		$writer->writeAttribute('urls_total', $countURLs);
		            		$writer->endElement();
	            			
		            		//GLOSSARY INCLUDE IN WEB_CT
		            		$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='MEDIA_COLLECTION_TYPE'";
		            		$stid5 = oci_parse($conn,$request);
		            		oci_execute($stid5);
		            		$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
		            		$countGlossary=$row5['COUNT(*)'];
// 		            		$countGlossary = 0;
// 		            		while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
// 		            			$countGlossary++;
// 		            		}
		            		$writer->startElement('glossaries');
		            		$writer->writeAttribute('glossaries_total', $countGlossary);
		            		$writer->endElement();
		            		
		            		
		            		//DISCUSSION INCLUDE IN WEB_CT
		            		$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='DISCUSSION_TOPIC_TYPE'";
		            		$stid5 = oci_parse($conn,$request);
		            		oci_execute($stid5);
		            		$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
		            		$countDiscussion=$row5['COUNT(*)'];
		            		
// 		            		$countDiscussion = 0;
// 		            		while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
// 		            			$countDiscussion++;
// 		            		}
		            		$writer->startElement('discussions');
		            		$writer->writeAttribute('discussions_total', $countDiscussion);
		            		$writer->endElement();
		            		
		            		//SELF_ENROLLMENT_TYPE
		            		$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='SELF_ENROLLMENT_TYPE'";
		            		$stid5 = oci_parse($conn,$request);
		            		oci_execute($stid5);
		            		$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
		            		$countSelfEnrollments=$row5['COUNT(*)'];
		            		
// 		            		$countSelfEnrollments = 0;
// 		            		while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
// 		            			$countSelfEnrollments++;
// 		            		}
		            		$writer->startElement('self_enrollment');
		            		$writer->writeAttribute('self_enrollment_total', $countSelfEnrollments);
		            		$writer->endElement();
		            		
		            		//SYLLABUS_TYPE
		            		$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='SYLLABUS_TYPE'";
		            		$stid5 = oci_parse($conn,$request);
		            		oci_execute($stid5);
		            		$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
		            		$countSyllabi=$row5['COUNT(*)'];
		            		
		            		// 		            		$countSelfEnrollments = 0;
		            		// 		            		while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
		            		// 		            			$countSelfEnrollments++;
		            		// 		            		}
		            		$writer->startElement('syllabi');
		            		$writer->writeAttribute('syllabi_total', $countSyllabi);
		            		$writer->endElement();
		            		
		            		
		            		//CHAT_ROOM_TYPE
		            		$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='CHAT_ROOM_TYPE'";
		            		$stid5 = oci_parse($conn,$request);
		            		oci_execute($stid5);
		            		$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
		            		$countChat=$row5['COUNT(*)'];
		            		
		            		// 		            		$countSelfEnrollments = 0;
		            		// 		            		while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
		            		// 		            			$countSelfEnrollments++;
		            		// 		            		}
		            		$writer->startElement('chat');
		            		$writer->writeAttribute('chat_total', $countChat);
		            		$writer->endElement();
		            		
		            		//PROXY_STUDYMATE_1X8
		            		//SCORM_TYPE
		            		//URL
		            		//DISCUSSION_CATEGORY_TYPE
		            		
		            		
		            		//REPOSITORY INCLUDE IN WEB_CT
		            		$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='ORGANIZER_PAGE_TYPE'";
		            		$stid5 = oci_parse($conn,$request);
		            		oci_execute($stid5);
		            		$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
		            		$countRepositories=$row5['COUNT(*)'];
		            		
// 		            		$countRepositories = 0;
// 		            		while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
// 		            			$countRepositories++;
// 		            		}
		            		$writer->startElement('repositories');
		            		$writer->writeAttribute('repositories_total', $countRepositories);
		            		$writer->endElement();
		            		
		            		
		            		if($totalElements>($countPages+$countAssessments+$countAssignments+$countRepositories+$countURLs+$countTOCs+$countGlossary+$countDiscussion+$countSelfEnrollments+$countSyllabi+$countChat)){
		            			echo "-----------------------> IL MANQUE DES ELEMENTS".PHP_EOL;
		            			$request = "SELECT * FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30001')";
		            			$stid5 = oci_parse($conn,$request);
		            			oci_execute($stid5);
		            			while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
		            				echo 'TYPE = '.$row5['CE_TYPE_NAME'].PHP_EOL;
		            			}
		            			
		            		}
		            		
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