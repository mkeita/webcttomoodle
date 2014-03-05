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
$writer->openURI('report4.xml'); 
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
            
	            $request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='TOC_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId."'";
	            $stid4 = oci_parse($conn,$request);
	            oci_execute($stid4);
	            
	            $row4 = oci_fetch_array($stid4, OCI_ASSOC+OCI_RETURN_NULLS);
	            $writer->writeAttribute('nb_learning_module',$row4['COUNT(*)']);
	             
	            
	            
	            //LEARNING MODULE
	            if($deliveryContextId!=NULL){
	            	$request = "SELECT * FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='TOC_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId."'";
	            	$stid4 = oci_parse($conn,$request);
	            	oci_execute($stid4);
	            	
	            	while ($row4 = oci_fetch_array($stid4, OCI_ASSOC+OCI_RETURN_NULLS)) {
	            		
	            		$writer->startElement('learning_module');
	            			$writer->writeAttribute('name', utf8_encode($row4['NAME']));

							//ACTION MENU LINKS	            			
	            			$request = "SELECT COUNT(*) FROM CMS_LINK WHERE LEFTOBJECT_ID=(SELECT ID FROM CO_ACTIONMENU WHERE TOC_ID='".$row4['ID']."') AND CMS_LINK.LINK_TYPE_ID='30003'";
	            			$stid5 = oci_parse($conn,$request);
	            			oci_execute($stid5);
	            			$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
	            			$writer->writeAttribute('total_external_action_links', utf8_encode($row5['COUNT(*)']));
	            			
	            			
	            			//ACTION ALL INTERNAL LINKS
	            			$request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30002')";
	            			$stid5 = oci_parse($conn,$request);
	            			oci_execute($stid5);
	            			$row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS);
	            			$writer->writeAttribute('total_links', utf8_encode($row5['COUNT(*)']));
	            			
	            			//PAGE INCLUDE IN WEB_CT
	            			$request = "SELECT * FROM CMS_CONTENT_ENTRY	            						  	
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30002')
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
		            		$request = "SELECT * FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30002')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='ASSESSMENT_TYPE'";
		            		$stid5 = oci_parse($conn,$request);
		            		oci_execute($stid5);
		            		
		            		$countLinks = 0;
		            		while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
		            			$countLinks++;
		            		}
		            		$writer->startElement('assessments');		            		
		            			$writer->writeAttribute('assessments_total', $countLinks);
		            		$writer->endElement();
		            		
		            		
	            			//ASSIGNMENTS INCLUDE IN WEB_CT
	            			$request = "SELECT * FROM CMS_CONTENT_ENTRY  
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30002')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='PROJECT_TYPE'";
	            			$stid5 = oci_parse($conn,$request);
	            			oci_execute($stid5);

	            			$countLinks = 0;
	            			while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
	            				$countLinks++;
	            			}
	            			$writer->startElement('assignments');	            			
		            			$writer->writeAttribute('assignments_total', $countLinks);
		            		$writer->endElement();
		            			
		            		
		            		//HEADING INCLUDE IN WEB_CT
		            		$request = "SELECT * FROM CMS_CONTENT_ENTRY
										  WHERE ID IN (SELECT RIGHTOBJECT_ID FROM CMS_LINK WHERE LEFTOBJECT_ID='".$row4['ID']."' AND LINK_TYPE_ID='30002')
										  AND CMS_CONTENT_ENTRY.CE_TYPE_NAME='HEADING_TYPE'";
		            		$stid5 = oci_parse($conn,$request);
		            		oci_execute($stid5);
		            		
		            		$countLinks = 0;
		            		while ($row5 = oci_fetch_array($stid5, OCI_ASSOC+OCI_RETURN_NULLS)) {
		            			$countLinks++;
		            		}
		            		$writer->startElement('headings');
		            		$writer->writeAttribute('headings_total', $countLinks);
		            		$writer->endElement();
	            			
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