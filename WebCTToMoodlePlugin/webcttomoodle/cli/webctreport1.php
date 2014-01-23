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

$db_charset = 'WE8ISO8859P1'; //FRANCAIS
$conn = oci_connect('webct', 'ciTy4_',$db, $db_charset);

if (!$conn) {    
    $e = oci_error();
    var_dump($e);
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

$stid1 = oci_parse($conn, 'SELECT * FROM LC_CATEGORY');
oci_execute($stid1);


$writer = new XMLWriter(); 
$writer->openURI('report1.xml'); 
$writer->startDocument('1.0');

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
            
            
            //FIND WITCH TOOL IS ACTIVATED
            //SELECT * FROM CM_TOOL WHERE ID IN (SELECT TOOL_ID FROM CM_TOOL_USED WHERE COURSEMENU_ID=((SELECT ID FROM CM_COURSEMENU WHERE TEMPLATE_ID='366249239001')));
            $request = "SELECT * FROM CM_TOOL WHERE ID IN (SELECT TOOL_ID FROM CM_TOOL_USED WHERE COURSEMENU_ID=((SELECT ID FROM CM_COURSEMENU WHERE TEMPLATE_ID='".$deliveryContextId. "')))";
            $stid = oci_parse($conn,$request);
            oci_execute($stid);
            $calendar = false;
            $search = false;
            $syllabus = false;
            $announcements = false;
            $chat = false;
            $discussions = false;
            $mail = false;
            $roster = false;
            $wio = false;
            $assessments = false;
            $assignments = false;
            $goals = false;
            $leaningModules = false;
            $localContent = false;
            $mediaLibrary = false;
            $scorm = false;
            $webLinks = false;
            $myGrades = false;
            $myProgres = false;
            $notes = false;
            while ($row4 = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
                $toolName = $row4["NAME"];
                switch ($toolName){
                    case "Calendar":
                        $calendar = true;
                        break;
                    case "Search":
                        $search = true;
                        break;
                    case "Syllabus":
                        $syllabus = true;
                        break;
                    case "Campus Announcements":
                        $announcements = true;
                        break;
                    case "Chat/Whiteboard":
                        $chat = true;
                        break;
                    case "Discussion":
                        $discussions = true;
                        break;
                    case "Mail":
                        $mail = true;
                        break;
                    case "Personal Profile":
                        $roster = true;
                        break;
                    case "WIO":
                        $wio = true;
                        break;
                    case "assessment":
                        $assessments = true;
                        break;
                    case "Projects":
                        $assignments = true;
                        break;
                    case "LearningObjective":
                        $goals = true;
                        break;
                    case "TOC":
                        $leaningModules = true;
                        break;
                    case "Cdrom":
                        $localContent = true;
                        break;
                    case "MediaLibrary":
                        $mediaLibrary = true;
                        break;
                    case "SCORM":
                        $scorm = true;
                        break;
                    case "URL":
                        $webLinks = true;
                        break;
                    case "My Grades":
                        $myGrades = true;
                        break;
                    case "Tracking":
                        if($row4["COURSE_MAP_DISPLAY_NAME"]=="tracking.CTBCourseMapDisplayName.myprogress"){
                            $myProgres = true;    
                        }
                        break;
                    case "Notes":
                        $notes = true;
                        break;
                }
            }
            
            $writer->writeAttribute('tool_calendar', $calendar);
            $writer->writeAttribute('tool_search', $search);
            $writer->writeAttribute('tool_syllabus', $syllabus);
            $writer->writeAttribute('tool_announcements', $announcements);
            $writer->writeAttribute('tool_chat', $chat);
            $writer->writeAttribute('tool_discussions', $discussions);
            $writer->writeAttribute('tool_mail', $mail);
            $writer->writeAttribute('tool_roster', $roster);
            $writer->writeAttribute('tool_wio', $wio);
            $writer->writeAttribute('tool_assessments', $assessments);
            $writer->writeAttribute('tool_assignments', $assignments);
            $writer->writeAttribute('tool_goals', $goals);
            $writer->writeAttribute('tool_leaningModules', $leaningModules);
            $writer->writeAttribute('tool_localContent', $localContent);
            $writer->writeAttribute('tool_mediaLibrary', $mediaLibrary);
            $writer->writeAttribute('tool_scorm', $scorm);
            $writer->writeAttribute('tool_webLinks', $webLinks);
            $writer->writeAttribute('tool_myGrades', $myGrades);
            $writer->writeAttribute('tool_myProgres', $myProgres);
            $writer->writeAttribute('tool_notes', $notes);

            //CALENDAR            
            $requestCalendar = "SELECT COUNT(*) FROM CALENDAR_ENTRY WHERE LEARNING_CONTEXT_ID='".$sectionId. "'";
            //$requestCalendar = "SELECT COUNT(*) FROM CALENDAR_ENTRY WHERE LEARNING_CONTEXT_ID='366249217001'";
            
            $stidCalendar = oci_parse($conn,$requestCalendar);
            oci_execute($stidCalendar);
            $countDB = oci_fetch_array($stidCalendar, OCI_ASSOC+OCI_RETURN_NULLS);
            $writer->writeAttribute('number_calendar_entries', $countDB["COUNT(*)"]);            
                       
            
            //ANNOUNCEMENTS
            $requestAnnoucements = "SELECT COUNT(*) FROM ANNOUNCEMENT WHERE CREATED_IN_LCID='".$sectionId. "'";
            $stidAnnoucements = oci_parse($conn,$requestAnnoucements);
            oci_execute($stidAnnoucements);
            $countDB = oci_fetch_array($stidAnnoucements, OCI_ASSOC+OCI_RETURN_NULLS);
            $writer->writeAttribute('number_announcements', $countDB["COUNT(*)"]); 
            

            //SYLLABUS
            $count = 0;
            if($deliveryContextId!=NULL){
                $request = "SELECT COUNT(*) FROM SYLLITEM WHERE SYLLABUS_ID = (SELECT ORIGINAL_CONTENT_ID FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='SYLLABUS_TYPE' AND DELIVERY_CONTEXT_ID='".$deliveryContextId. "')";
                $stid = oci_parse($conn,$request);
                oci_execute($stid);
                $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                $count = $countDB["COUNT(*)"];
            }
            $writer->writeAttribute('number_syllabus_items', $count);
            
            //DISCUSSIONS MESSAGES
            $count = 0;
            if($deliveryContextId!=NULL){
                $request = "SELECT COUNT(*) FROM DIS_MESSAGE WHERE DELIVERY_CONTEXT_ID='".$deliveryContextId. "'";
                $stid = oci_parse($conn,$request);
                oci_execute($stid);
                $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                $count = $countDB["COUNT(*)"];
            }
            $writer->writeAttribute('number_discussion_messages', $count); 

            
            //MAIL MESSAGES
            $request = "SELECT COUNT(*) FROM MAIL_MESSAGE WHERE LEARNING_CONTEXT_ID='".$sectionId. "'";
            $stid = oci_parse($conn,$request);
            oci_execute($stid);
            $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
            $writer->writeAttribute('number_mail_messages', $countDB["COUNT(*)"]); 
            
            //ASSESSMENTS
            $count = 0;
            if($deliveryContextId!=NULL){
                //Get only non deleted assessment
                $request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='ASSESSMENT_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId. "'";
                $stid = oci_parse($conn,$request);
                oci_execute($stid);
                $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                $count = $countDB["COUNT(*)"];
            }
            $writer->writeAttribute('number_assessments', $count); 

            //ASSIGNMENTS
            $count = 0;
            if($deliveryContextId!=NULL){
                $request = "SELECT COUNT(*) FROM AGN_ASSIGNMENT WHERE DELIVERY_CONTEXT_ID='".$deliveryContextId. "'";
                $stid = oci_parse($conn,$request);
                oci_execute($stid);
                $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                $count = $countDB["COUNT(*)"];
            }
            $writer->writeAttribute('number_assignments', $count); 
            
            
            //GOALS
            $count = 0;
            if($deliveryContextId!=NULL){
                $request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='LEARNING_OBJECTIVE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId."'";
                $stid = oci_parse($conn,$request);
                oci_execute($stid);
                $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                $count = $countDB["COUNT(*)"];
            }
            $writer->writeAttribute('number_learning_objectives', $count);
            
            
            //LEARNING MODULE
            $count = 0;
            if($deliveryContextId!=NULL){
                $request = "SELECT COUNT(*) FROM CMS_LINK WHERE LEFTOBJECT_ID IN (SELECT ORIGINAL_CONTENT_ID FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='TOC_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId."')";
                $stid = oci_parse($conn,$request);
                oci_execute($stid);
                $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                $count = $countDB["COUNT(*)"];
            }
            $writer->writeAttribute('number_learning_module_links', $count);
           
            
            //LOCAL CONTENT ENTRY
            $count = 0;
            if($deliveryContextId!=NULL){
                $request = "SELECT COUNT(*) FROM LOCAL_CONTENT_ENTRY WHERE TEMPLATE_ID='".$deliveryContextId."'";
                $stid = oci_parse($conn,$request);
                oci_execute($stid);
                $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                $count = $countDB["COUNT(*)"];
            }
            $writer->writeAttribute('number_local_content_entries', $count);
            
            
            //MEDIA LIBRARY (GLOSSARY)
            $count = 0;
            if($deliveryContextId!=NULL){
                $request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='MEDIA_ENTRY_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId."'";
                $stid = oci_parse($conn,$request);
                oci_execute($stid);
                $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                $count = $countDB["COUNT(*)"];
            }
            $writer->writeAttribute('number_glossaries_entries', $count);
            
            //SCORM PACKAGE
            $count = 0;
            if($deliveryContextId!=NULL){
                $request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='SCORM_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId."'";
                $stid = oci_parse($conn,$request);
                oci_execute($stid);
                $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                $count = $countDB["COUNT(*)"];
            }
            $writer->writeAttribute('number_scorm_pacquages', $count);
            
            
            //WEB LINKS
            $count = 0;
            if($deliveryContextId!=NULL){
                $request = "SELECT COUNT(*) FROM CMS_CONTENT_ENTRY WHERE CE_TYPE_NAME='URL_TYPE' AND DELETED_FLAG=0 AND DELIVERY_CONTEXT_ID='".$deliveryContextId."'";
                $stid = oci_parse($conn,$request);
                oci_execute($stid);
                $countDB = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                $count = $countDB["COUNT(*)"];
            }
            $writer->writeAttribute('number_web_links', $count);
            
            $writer->endElement();
            
            //if($counter3>2){
            //    break;
            //}
            echo ''.$counter1.$counter2.$counter3."\n";
            
        }
        $writer->endElement();    
        
        //if($counter2>2){
        //    break;
        //}

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
    
    //if($counter1>2){
    //    break;
    //
}

oci_close($conn);

$writer->endElement();

$writer->endDocument(); 
$writer->flush();


echo 'END';