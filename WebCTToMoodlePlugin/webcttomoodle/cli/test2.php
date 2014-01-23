<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo "Connvert HTML File\n";

//echo phpinfo();

// Connects to the XE service (i.e. database) on the "localhost" machine
//$conn = oci_connect('webct', 'admin','//localhost/XE');
//oci_close($conn);
//var_dump($conn);
//$conn = oci_connect('webct', 'admin','//localhost/XE');

//select sys_context('USERENV','SERVICE_NAME') from dual
        
//$db = '//localhost/LOCAL';
function convertHTMLContentLinks($htmlContent,  &$filesNames){
	
	$findWebCT   = '/webct/RelativeResourceManager/';	
	$pos1 = strpos($htmlContent, $findWebCT);
	
	while($pos1>0){
		
		$findQuot   = '&quot;';
		$pos2 = strpos($htmlContent, $findQuot, $pos1);
			
		$formerLink = substr($htmlContent, $pos1,$pos2-$pos1);
		
		$lastSlashPos = strrpos($formerLink, "/");
		
		$fileName = substr($formerLink, $lastSlashPos+1);
		
		 $filesNames[] =  $fileName; 
		
		$newLink = "@@PLUGINFILE@@/".$fileName;
			
		$htmlContent = str_replace($formerLink, $newLink, $htmlContent);
	
		//$htmlContent = convertHTMLContentLinks($htmlContent, $filesNames);
		$pos1 = strpos($htmlContent, $findWebCT);
	}	
	
	return $htmlContent;
} 

$html1 = "&lt;IMG SRC=&quot;/webct/RelativeResourceManager/Template/Typoflechedouble2.gif&quot;align=  ABSCENTER&gt; &lt;IMG SRC=&quot;/webct/RelativeResourceManager/Template/Typoflechedouble3.gif&quot;align=  ABSCENTER&gt; &lt;IMG SRC=&quot;/webct/RelativeResourceManager/Template/Typoflechedouble4.gif&quot;align=  ABSCENTER&gt;";
$html2 = "&lt;img src=&quot;/webct/RelativeResourceManager/Template/ressources/GlosAcideorg.gif&quot;&gt;";
$html3 = "&lt;A HREF = &quot;/webct/RelativeResourceManager/Template/ressources/PipetteRincage.mov&quot;&gt;";
$html4 = "&lt;TITLE&gt;Bivalent&lt;/TITLE&gt; &lt;H2&gt;Bivalent&lt;/H2&gt; &lt;P&gt;    - Se dit d'un atome qui présente deux liaisons  &lt;a href=&quot;/webct/RelativeResourceManager/Template/GUID002/scripts/student/gl_view.pl?view+COVALENCE&quot;&gt;covalentes&lt;/a&gt;.&lt;BR&gt;&lt;/P&gt; &lt;P&gt;   - Qualifie  la charge réelle de deux unités (positive ou  négative) portée par l'atome d'un ion simple ou  la charge formelle portée par un atome d'un ion  composé ou d'une molécule.&lt;BR&gt;&lt;/P&gt;";
$html5 = "&l";

$fileNames=array();
$x=0;
while($x<1){
	$fileNames = array();
	echo ''.convertHTMLContentLinks($html1,$fileNames)."\n";
	$x++;
}
var_dump($fileNames);

echo ''.convertHTMLContentLinks($html2)."\n";
echo ''.convertHTMLContentLinks($html3)."\n";
echo ''.convertHTMLContentLinks($html4)."\n";
echo ''.convertHTMLContentLinks($html5)."\n";

echo 'END';