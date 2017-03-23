<?php
require_once 'includes/connection.inc.php';
require_once 'includes/html_top.inc.php';
require_once 'settings/db_settings.php';
require_once 'includes/PHPExcel.php';


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
if(isset($_GET) && $_GET['action']=="create"){
	
	$fileName="dataplan_".uniqid()."_.xls";
	
	$phpExcel = new PHPExcel();
	$phpExcel->getActiveSheet()->setTitle("DataPlan");
	$phpExcel->setActiveSheetIndex(0);
	$sheet=$phpExcel->getActiveSheet();
	
	$basket = $_SESSION["shopping_basket"];

	$sql="";
	$row=3;
	foreach($basket as $basketItem){
		$sql="SELECT * FROM data_item_response_options WHERE data_item_id=".intval($basketItem['id']);
		$sth = $dbh->prepare($sql);
		$sth->execute();
		
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		//Data Item Name	 Description	Type	Unit	Multi/Single	Response Options	Coded Values	Required	Description Label	
		//Ontology term	Ontology Code	Ontology Type	Ontology Modifier
		

		foreach ($result as $res){
			
			$sheet->setCellValueByColumnAndRow(0, $row, $res['data_item_name']);
			$sheet->setCellValueByColumnAndRow(1, $row, $res['description']);
			$sheet->setCellValueByColumnAndRow(2, $row, $res['data_type']);
			$sheet->setCellValueByColumnAndRow(3, $row, $res['unit']);
			//$sheet->setCellValueByColumnAndRow(4, $row, $result['multisingle']);
			$sheet->setCellValueByColumnAndRow(5, $row, $res['response_options']);
			$sheet->setCellValueByColumnAndRow(6, $row, $res['response_values']);
			
			//$sheet->setCellValueByColumnAndRow(7, $row, $res['required']);
			
			$sheet->setCellValueByColumnAndRow(8, $row, $res['description_label']);
			$sheet->setCellValueByColumnAndRow(9, $row, $res['ontology_term']);
			$sheet->setCellValueByColumnAndRow(10, $row, $res['ontology_code']);
			$sheet->setCellValueByColumnAndRow(11, $row, $res['ontology_type']);
			$sheet->setCellValueByColumnAndRow(12, $row, $res['ontology_modifier']);
			
			$row++;
				
		}
		
		
	}
	
	//Data Item Name	 Description	Type	Unit	Multi/Single	Response Options	Coded Values	Required	Description Label
	//Ontology term	Ontology Code	Ontology Type	Ontology Modifier
	
	$sheet->getRowDimension('2')->setRowHeight(30);
	
	$sheet->getStyle('A2:H2')->applyFromArray(
			array(
					'fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('rgb' => 'D9D9D9')
					)
			)
	);
	$sheet->getStyle('A1:H1')->applyFromArray(
			array(
					'fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('rgb' => 'D9D9D9')
					)
			)
	);
	
	
	
	
	$sheet->getStyle('I2:N2')->applyFromArray(
			array(
					'fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('rgb' => 'BDD7EE')
					)
			)
	);
	$sheet->getStyle('I1:N1')->applyFromArray(
			array(
					'fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('rgb' => 'BDD7EE')
					)
			)
	);
	
	for($col = 'A'; $col !== 'N'; $col++) {
		$sheet->getColumnDimension($col)
		->setAutoSize(true);
	}
	$sheet->mergeCells('A1:H1');
	$sheet->mergeCells('I1:N1');
	
	$sheet->getStyle("A1")->getFont()->setBold(true);
	$sheet->setCellValue("A1", "Variable Description");
	$sheet->getStyle("I1")->getFont()->setBold(true);
	$sheet->setCellValue("I1", "Clinical Coding");
	
	$style = array(
			'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			),
			'borders' => array(
					'allborders' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
					)
			)
	);
	
	$sheet->getStyle("A1:H1")->applyFromArray($style);
	$sheet->getStyle("I1:N1")->applyFromArray($style);
	$sheet->getStyle("A2:N2")->applyFromArray($style);
	
	$sheet->getStyle("A2")->getFont()->setBold(true);
	$sheet->setCellValue("A2", "Data Item Name");
	$sheet->getStyle("B2")->getFont()->setBold(true);
	$sheet->setCellValue("B2", "Description");
	$sheet->getStyle("C2")->getFont()->setBold(true);
	$sheet->setCellValue("C2", "Type");
	$sheet->getStyle("D2")->getFont()->setBold(true);
	$sheet->setCellValue("D2", "Unit");
	$sheet->getStyle("E2")->getFont()->setBold(true);
	$sheet->setCellValue("E2", "Multi/Single");
	$sheet->getStyle("F2")->getFont()->setBold(true);
	$sheet->setCellValue("F2", "Response Options");
	$sheet->getStyle("G2")->getFont()->setBold(true);
	$sheet->setCellValue("G2", "Coded Values");
	$sheet->getStyle("H2")->getFont()->setBold(true);
	$sheet->setCellValue("H2", "Required");
	$sheet->getStyle("I2")->getFont()->setBold(true);
	$sheet->setCellValue("I2", "Description Label");
	$sheet->getStyle("J2")->getFont()->setBold(true);
	$sheet->setCellValue("J2", "Ontology Term");
	$sheet->getStyle("K2")->getFont()->setBold(true);
	$sheet->setCellValue("K2", "Ontology Code");
	$sheet->getStyle("L2")->getFont()->setBold(true);
	$sheet->setCellValue("L2", "Ontology Type");
	$sheet->getStyle("M2")->getFont()->setBold(true);
	$sheet->setCellValue("M2", "Ontology Modifier");
	$sheet->getStyle("N2")->getFont()->setBold(true);
	$sheet->setCellValue("N2", "Comment");
	
	
	
	$objWriter = PHPExcel_IOFactory::createWriter($phpExcel, "Excel5");
	$objWriter->save("dataplans/".$fileName);
	
	//var_dump($result);
	echo '<div id="main"><br/><p>Download dataplan from <a href="dataplans/'.$fileName.'">HERE</a></p><br/></div>';
	

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

<h2>Dataplan Preview</h2>
<br/>
<table class="noborder">
<tr><td><a href="createdp.php?action=create">Create my dataplan</a></td><td><a href="uploaddp.php">Upload a dataplan</a></td></tr>
</table>
<br/>
<br/>

<input type="checkbox" class="showhide" id="datatype" <?php 
if ($_SESSION['display_controllers']['datatype']=="true") echo 'checked="checked"';
?>> Type 
<input type="checkbox" class="showhide" id="dataunit" <?php 
if ($_SESSION['display_controllers']['dataunit']=="true") echo 'checked="checked"';
?>> Unit 

<input type="checkbox" class="showhide" id="responseoptions" <?php 
if ($_SESSION['display_controllers']['responseoptions']=="true") echo 'checked="checked"';
?>> Response options

 <input type="checkbox" class="showhide" id="codedvalues" <?php 
if ($_SESSION['display_controllers']['codedvalues']=="true") echo 'checked="checked"';
?>>Coded values


<div id="dataplan_preview">

</div>
<br/>
<a href="createdp.php?action=create">Create my dataplan</a>

<br/>
<br/>
</div>



<?php 

}



require_once 'includes/html_bottom.inc.php';
?>