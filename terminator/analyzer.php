<?php
/**
 * This is an auto updater for the Human Phenotype Ontology. The script will download 
 * the hp.obo file from the HPO website, parse it and import the new Terms from it to the database.
 * 
 * @author Csaba Halmagyi
 */


header( 'Content-type: text/html; charset=utf-8' );
set_time_limit(3000);

if(isset($_GET['from'])) $from = $_GET['from'];
if(isset($_GET['to'])) $to = $_GET['to'];

if(isset($_GET['file'])) {$fileName = $_GET['file'];}
else die("Missing file name parameter!");
require_once 'classes/HPO.class.php';
require_once 'classes/PHPExcel.php';

?>
 <!DOCTYPE html>
<html>
<head>
<title>Terminator</title>
<style>
.highlight {
  background:#EE0000;
}
</style>
<script src="js/jquery.min.js"></script>
</head>
<body>
<script type"text/javascript">
jQuery(document).ready(function($){
	jQuery("td").hover(
		       function(){ 
			       var tdClass = $(this).attr('class');;
			       jQuery("td").each(function() {
				       if (jQuery(this).attr('class') == tdClass && jQuery(this).attr('class') != 'separator'){
				    	   jQuery(this).addClass("highlight");
					   }
			    	   
			    	});
			       },
		       function(){
				       jQuery("td").each(function() {
				    	   jQuery(this).removeClass("highlight");
				    	});
					}
		);
});
</script>

<?php 
//Creates a Human Phenotype Ontology Class
$ont = new HPO();
$ont->read();

class SpecialValueBinder extends PHPExcel_Cell_DefaultValueBinder implements PHPExcel_Cell_IValueBinder
{
	public function bindValue(PHPExcel_Cell $cell, $value = null)
	{

		$value = PHPExcel_Shared_String::SanitizeUTF8($value);
		$cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_STRING);
		return true;
	}
}


/**  Tell PHPExcel that we want to use our Special Value Binder  **/
PHPExcel_Cell::setValueBinder( new SpecialValueBinder() );

// read the xls data file
$inputFileName = 'uploads/'.$fileName;

try {
	$inputFileType = PHPExcel_IOFactory::identify ( $inputFileName );
	$objReader = PHPExcel_IOFactory::createReader ( $inputFileType );
	$objPHPExcel = $objReader->load ( $inputFileName );
} catch ( Exception $e ) {
	die ( 'Error loading file "' . pathinfo ( $inputFileName, PATHINFO_BASENAME ) . '": ' . $e->getMessage () );
}

// Get worksheet dimensions
$sheet = $objPHPExcel->getSheet ( 0 );
$highestRow = $sheet->getHighestRow ();
$highestColumn = $sheet->getHighestColumn ();

$termsToAnalyse = array ();
// Read all rows
for($row = 1; $row <= $highestRow; $row ++) {

	$rowData = $sheet->rangeToArray('A' . $row . ':' . 'B' . $row, NULL, FALSE, FALSE, FALSE);
	array_push($termsToAnalyse, $rowData[0]);
	//array_push ( $excelData, $rowdata );
}





$phpExcel = new PHPExcel();
$phpExcel->getActiveSheet()->setTitle("HPO");
$phpExcel->setActiveSheetIndex(0);
$sheet=$phpExcel->getActiveSheet();
$row = 1;

$time_start = microtime(true);

foreach($termsToAnalyse as $t){
	echo '<b>Determining path from '.$t[0].' to '.$t[1].'.</b><br/>';
	$path = $ont->getFirstPath($t[0],$t[1]);
	
	if ($path['error'] == null){
		$end = count($path['path']);
		echo '<table><tr>';
		
		for ($i=$end-1;$i>=0;$i--){
			$class = explode(':',$path['path'][$i]);
			$class = $class[1];
			echo '<td class="'.$class.'">'.$path['path'][$i].'</td>';
			
			$sheet->setCellValueByColumnAndRow($end-$i-1, $row, $path['path'][$i]);
			
			if ($i != 0) echo '<td class="separator"> => </td>';
	
		}
		echo '</tr></table>';
	}
	else {
		echo $path['error'].'<br/>';
		$sheet->setCellValueByColumnAndRow(0, $row, $t[1]);
		$sheet->setCellValueByColumnAndRow(1, $row, $path['error']);
		$sheet->setCellValueByColumnAndRow(2, $row, $t[0]);
	}
	$row++;
}

$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
echo '<br/><b>Total Execution Time:</b> '.$execution_time.' Seconds<br/><br/>';

$objWriter = PHPExcel_IOFactory::createWriter($phpExcel, "CSV");
$objWriter->save("tempfiles/path_".$fileName);

//var_dump($result);
echo '<div id="main"><br/><p>Download the paths in CSV format <a href="tempfiles/path_'.$fileName.'">(right click, save link as)</a></p><br/></div>';


?>
</body>
</html>
