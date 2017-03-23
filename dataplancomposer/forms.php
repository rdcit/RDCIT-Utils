<?php
require_once 'includes/connection.inc.php';
require_once 'includes/html_top.inc.php';
require_once 'settings/db_settings.php';
require_once 'includes/PHPExcel.php';


function removeIffyChars($itemname){
	$newname = preg_replace("/[^a-zA-Z0-9]/", "", $itemname);
	
	return $newname;
}

function ocItemTypeToSCTOType($ocType, $itemName){
	
	if($ocType == "text" || $ocType == "textarea") {
		return "text";
	}else if($ocType == "date"){
		return "date";
	}
	else if($ocType == "single-select" || $ocType == "radio"){
		return "select_one ".removeIffyChars($itemName);
	}
	else if($ocType == "multi-select" || $ocType == "checkbox"){
		return "select_multiple ".removeIffyChars($itemName);
	}
	else return "";
	
}

function ocDataTypeToSCTOType($ocDataType){
	
	if($ocDataType == "ST") return "text";
	if($ocDataType == "INT") return "integer";
	if($ocDataType == "REAL") return "decimal";
	return "";
	
}





try {
	$dbh = new PDO("mysql:dbname=$db;host=$dbhost", $dbuser, $dbpass );
}catch (PDOException $e) {
	echo 'Connection failed: ' . $e->getMessage();
}

//var_dump($_SESSION);
?>

   <div id="sidebar">
    
  </div> 

<?php 

class SpecialValueBinder extends PHPExcel_Cell_DefaultValueBinder implements PHPExcel_Cell_IValueBinder
{
	public function bindValue(PHPExcel_Cell $cell, $value = null)
	{

		$value = PHPExcel_Shared_String::SanitizeUTF8($value);
		$cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_STRING);
		return true;
	}
}


if(isset($_GET) && $_GET['action']=="create_oc_crf"){
	
	echo '<div id="main">';
	
	$crfFileName="crf_".uniqid()."_.xls";
	$templateFileName="templates/crf_template_dpc.xls";
	

	
	
	/**  Tell PHPExcel that we want to use our Special Value Binder  **/
	PHPExcel_Cell::setValueBinder( new SpecialValueBinder() );
	
	
	try{
	$inputFileType = PHPExcel_IOFactory::identify ( $templateFileName );
	$objReader = PHPExcel_IOFactory::createReader ( $inputFileType );
	$objPHPExcel = $objReader->load ( $templateFileName );
	}
	catch ( Exception $e ) {
	die ( 'Error loading file "' . pathinfo ( $templateFileName, PATHINFO_BASENAME ) . '": ' . $e->getMessage () );
	}

	//ITEMS SHEET
	$objPHPExcel->setActiveSheetIndex(3);
	$sheet=$objPHPExcel->getActiveSheet();
	
	$basket = $_SESSION["shopping_basket"];
	$sections = array();
	$groups = array();
	
	$sql="";
	$row=2;
	foreach($basket as $basketItem){
		$sql="SELECT *
  		FROM data_item_response_options 
  		WHERE data_item_id = :itemid";
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":itemid",$basketItem['id']);
		$sth->execute();
		
		$itemData = $sth->fetchAll(PDO::FETCH_ASSOC);

		if(empty($itemData[0]['unique_code'])){
			$multiple = false;
		}
		else{
			$multiple = true;
		}

		//ITEM_NAME
		$sheet->setCellValueByColumnAndRow(0, $row, removeIffyChars($itemData[0]['data_item_name']));
		//DESCRIPTION_LABEL
		$sheet->setCellValueByColumnAndRow(1, $row, $itemData[0]['description_label']);
		//LEFT ITEM TEXT
		$sheet->setCellValueByColumnAndRow(2, $row, $itemData[0]['data_item_name']);
		//UNITS
		$sheet->setCellValueByColumnAndRow(3, $row, $itemData[0]['unit']);
		//SECTION LABEL
		$sheet->setCellValueByColumnAndRow(5, $row, removeIffyChars($itemData[0]['concept']));
		if(!in_array($itemData[0]['concept'], $sections)){
			$sections[]=$itemData[0]['concept'];
		}
		//GROUP LABEL
		$sheet->setCellValueByColumnAndRow(6, $row, removeIffyChars($itemData[0]['concept_group']));
		if(!in_array($itemData[0]['concept_group'], $groups)){
			$groups[]=$itemData[0]['concept_group'];
		}
		
		if($multiple){
			$unique_code = $itemData[0]['unique_code'];
			$sql="SELECT *
  				FROM data_item_response_codes
  				WHERE unique_code = :uc";
			$sth = $dbh->prepare($sql);
			$sth->bindParam(":uc",$unique_code);
			$sth->execute();
			$itemData2 = $sth->fetch(PDO::FETCH_ASSOC);
			//RESPONSE TYPE
			$sheet->setCellValueByColumnAndRow(13, $row, $itemData2['type']);
			//RESPONSE LABEL
			$sheet->setCellValueByColumnAndRow(14, $row, $itemData2['label']);
			//RESPONSE OPTION TEXT
			$sheet->setCellValueByColumnAndRow(15, $row, $itemData2['text']);
			//RESPONSE VALUES OR CALCULATIONS
			$sheet->setCellValueByColumnAndRow(16, $row, $itemData2['values']);
			//RESPONSE OPTION TEXT
			$sheet->setCellValueByColumnAndRow(18, $row, $itemData2['default_value']);
			//DATA TYPE
			$sheet->setCellValueByColumnAndRow(19, $row, $itemData2['data_type']);
		}
		else{
			//response type = text
			$sheet->setCellValueByColumnAndRow(13, $row, "text");
			
			if($itemData[0]['unit'] == "date"){
				//data type = DATE
				$sheet->setCellValueByColumnAndRow(19, $row, "DATE");
			}
			else{
				//data type = ST
				$sheet->setCellValueByColumnAndRow(19, $row, "ST");
			}
		}
		
		$row++;
	}
	
	//SECTIONS SHEET
	$objPHPExcel->setActiveSheetIndex(1);
	$sheet=$objPHPExcel->getActiveSheet();
	
	$sr = 2;
	foreach($sections as $s){
		$sheet->setCellValueByColumnAndRow(0, $sr, removeIffyChars($s));
		$sr++;
	}
	

	//GROUPS SHEET
	$objPHPExcel->setActiveSheetIndex(2);
	$sheet=$objPHPExcel->getActiveSheet();
	
	$gr = 2;
	foreach($groups as $g){
		$sheet->setCellValueByColumnAndRow(0, $gr, removeIffyChars($g));
		$gr++;
	}
	
	
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $inputFileType);
	$objWriter->save("created_files/".$crfFileName);
	
	echo '<br/><p>Download CRF from <a href="created_files/'.$crfFileName.'">HERE</a></p><br/></div>';
	require_once 'includes/html_bottom.inc.php';

}

else if(isset($_GET) && $_GET['action']=="create_scto_sheet"){
	echo '<div id="main">';
	
	$sheetFileName="scto_".uniqid()."_.xlsx";
	$templateFileName="templates/scto_design_templatexls.xls";
	
	try{
		$inputFileType = PHPExcel_IOFactory::identify ( $templateFileName );
		$objReader = PHPExcel_IOFactory::createReader ( $inputFileType );
		//$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load ( $templateFileName );

	}
	catch ( Exception $e ) {
		die ( 'Error loading file "' . pathinfo ( $templateFileName, PATHINFO_BASENAME ) . '": ' . $e->getMessage () );
	}
	
	$currSurveyRow = 9;
	$currChoicesRow = 4;
	
	
	//SURVEY SHEET
	$objPHPExcel->setActiveSheetIndex(0);
	$sheet=$objPHPExcel->getActiveSheet();
	
	$basket = $_SESSION["shopping_basket"];
	$choices = array();
	
	$sql="";
	$row=2;
	foreach($basket as $basketItem){
		$sql="SELECT *
  		FROM data_item_response_options
  		WHERE data_item_id = :itemid";
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":itemid",$basketItem['id']);
		$sth->execute();
	
		$itemData = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		if(empty($itemData[0]['unique_code'])){
			
			//TYPE
			$sheet->setCellValueByColumnAndRow(0, $currSurveyRow, strtolower(ocItemTypeToSCTOType($itemData[0]['data_type'], 
					removeIffyChars($itemData[0]['data_item_name']))));
		}
		else{
			
			$unique_code = $itemData[0]['unique_code'];
			$sql="SELECT *
  				FROM data_item_response_codes
  				WHERE unique_code = :uc";
			$sth = $dbh->prepare($sql);
			$sth->bindParam(":uc",$unique_code);
			$sth->execute();
			$itemResponses = $sth->fetch(PDO::FETCH_ASSOC);
			
			//TYPE
			$sheet->setCellValueByColumnAndRow(0, $currSurveyRow, strtolower(ocItemTypeToSCTOType($itemResponses['type'], 
					removeIffyChars($itemData[0]['data_item_name']) )));

			$choices[] = array("id"=>strtolower(removeIffyChars($itemData[0]['data_item_name'])), "choicesdata"=>$itemResponses);
			
		}
		
		//NAME
		$sheet->setCellValueByColumnAndRow(1, $currSurveyRow, strtolower(removeIffyChars($itemData[0]['data_item_name'])));
		//LABEL
		$sheet->setCellValueByColumnAndRow(2, $currSurveyRow, $itemData[0]['data_item_name']);
		
		$currSurveyRow++;
	}
	
	echo "<br/><br/>";
	var_dump($choices);
	echo "<br/><br/>";
	
	//CHOICES SHEET
	$objPHPExcel->setActiveSheetIndex(1);
	$sheet=$objPHPExcel->getActiveSheet();

	foreach($choices as $c){
		$values = explode(",",$c['choicesdata']['values']);
		$texts = explode(",",$c['choicesdata']['text']);
		
		var_dump($values);
		for($i=0;$i<count($values);$i++){
			//list_name
			$sheet->setCellValueByColumnAndRow(0, $currChoicesRow, $c['id']);
			//name
			$sheet->setCellValueByColumnAndRow(1, $currChoicesRow, $values[$i]);
			//label
			$sheet->setCellValueByColumnAndRow(2, $currChoicesRow, $texts[$i]);
			
			$currChoicesRow++;
		}
		
		
	}
	
	var_dump($choices);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save("created_files/".$sheetFileName);
	
	
	
	echo '<br/><p>Download SCTO spreadsheet from <a href="created_files/'.$sheetFileName.'">HERE</a></p><br/></div>';
	require_once 'includes/html_bottom.inc.php';
}

else{

	
?>

<div id="main">
<script type="text/javascript">

jQuery( document ).ready(function() {

	jQuery( 'body' ).on('click', '.showhide', function () {
		loadItemsTableFromServer();
    	});

	
	loadItemsTableFromServer();        	
	

    });

</script>

<h2>Create form definitions</h2>
<br/>
<table class="noborder">
<tr><td>
<a href="forms.php?action=create_oc_crf">Create an OC CRF</a>
</td>
<td>
<a href="forms.php?action=create_scto_sheet">Create a SCTO Form</a>
</td>
</tr>
</table>
<br/>
<br/>


<br/>
<br/>
</div>



<?php 

}



require_once 'includes/html_bottom.inc.php';
?>