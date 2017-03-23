<?php
require_once 'includes/connection.inc.php';
require_once 'includes/html_top.inc.php';
require_once 'settings/db_settings.php';
require_once 'includes/PHPExcel.php';
include('includes/ItemBasket.class.php');

?>

   <div id="sidebar">
    Some guiding text comes here.
  </div> 
<div id="main">

<h2>Loading items from a dataplan</h2>

<?php 
//var_dump($_SESSION);

if(count($_FILES) != 0){

	try {
		$dbh = new PDO("mysql:dbname=$db;host=$dbhost", $dbuser, $dbpass );
	}catch (PDOException $e) {
		echo 'Connection failed: ' . $e->getMessage();
		die();
	}
	
	$target_dir = "uploads/";
	$target_dir = $target_dir . basename( "upload_".$_SESSION['sessionid']."_.xls");
	
	if (move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $target_dir)) {
		echo "<p>The file ". basename( $_FILES["uploadFile"]["name"]). " has been uploaded.<br/><br/></p>";
		
		
		//load xls
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
		
		$inputFileName = $target_dir;
		
		try {
			$inputFileType = PHPExcel_IOFactory::identify ( $inputFileName );
			$objReader = PHPExcel_IOFactory::createReader ( $inputFileType );
			$objPHPExcel = $objReader->load ( $inputFileName );
		} catch ( Exception $e ) {
			die ( 'Error loading file "' . pathinfo ( $inputFileName, PATHINFO_BASENAME ) . '": ' . $e->getMessage () );
			require_once "includes/html_bottom.inc.php";
		}
		
		// Get worksheet dimensions
		$sheet = $objPHPExcel->getSheet ( 0 );
		$highestRow = $sheet->getHighestRow ();
		$highestColumn = $sheet->getHighestColumn ();
		
		$excelData = array ();
		// Read all rows
		for($row = 1; $row <= $highestRow; $row ++) {
		
			$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, FALSE, FALSE, FALSE);
			array_push($excelData, $rowData[0]);
		}
		
		
		$Basket = new ItemBasket('shopping_basket');
		$Basket->load('shopping_basket');
		
		
		echo '<table><thead><th>Item</th><th>Description</th><th>Result</th></thead><tbody>';
		
		$last_item = "";
		for($i = 2; $i < $highestRow; $i ++) {
			
			$item_name = trim($excelData[$i][0]);
			$item_description = trim($excelData[$i][1]);
			
			if(strlen($item_name) == 0 || $last_item == $item_name) continue;
			
			
			$sql = "SELECT * FROM data_item_response_options
					WHERE data_item_name = :itemname
					AND description = :description";
			$sth = $dbh->prepare($sql);
			$sth->bindParam(':itemname', $item_name);
			$sth->bindParam(':description', $item_description);
			$sth->execute();
			
			$item =  $sth->fetch(PDO::FETCH_ASSOC);

			//echo '<tr><td>'.$item_name.'</td><td>'.$item_description.'</td><td>';
			
			if(!empty($item['data_item_id'])){
				
				if($Basket->isItemInBasket($item['data_item_id'])){
					echo '<tr class="warning"><td>'.$item_name.'</td><td>'.$item_description.'</td><td>';
					echo 'Item is already in basket.</td></tr>';
					$last_item = $item_name;
				}
				else{
					
					$Basket->addItem(array("id"=>$item['data_item_id'],"name"=>$item['data_item_name'],"descr"=>$item['description'],
					"concept"=>$item['concept'],"group"=>$item['concept_group']));
					
					echo '<tr class="success"><td>'.$item_name.'</td><td>'.$item_description.'</td><td>';
					echo 'Loaded to basket</td></tr>';
					$last_item = $item_name;
				}
				
			}
			else{
				echo '<tr class="error"><td>'.$item_name.'</td><td>'.$item_description.'</td><td>';
				echo 'Item not found in database</td></tr>';
			}
			
			
		}
		
		echo '</tbody></table>';
		
		$Basket->save();
		
		
		
		
		
		
		
		
		
		
		
		
		echo '</p>';
	} else {
		echo "<p>Sorry, there was an error uploading your file.<br/></p>";
		
	}
	
	
}
else{
?>

<h2>Upload a dataplan</h2>
<br/>
<form method="post" action="uploaddp.php" method="post" enctype="multipart/form-data">
<table class="noborder">
<tr><td>Please choose a file:</td><td> <input type="file" name="uploadFile"></td></tr>
<tr><td><input type="submit" value="Upload File"></td></tr>
</table>
</form> 

<?php 
}
?>



<?php 
require_once 'includes/html_bottom.inc.php';
?>