<?php
session_start ();
header( 'Content-type: text/html; charset=utf-8' );
require_once 'classes/PHPExcel.php';
require_once 'settings/db.inc.php';
require_once 'classes/ItemContainer.class.php';

ini_set('implicit_flush', true);
ob_implicit_flush(true);

$container = new ItemContainer();
$container->setContainerName("octest");

try {
	$dbh = new PDO ( "pgsql:dbname=$db;host=$dbhost", $dbuser, $dbpass );
} catch ( PDOException $e ) {
	echo 'Connection failed: ' . $e->getMessage ();
}

$sql = "SELECT item_id, name, description, units, item_data_type_id, oc_oid FROM item";

$sth = $dbh->prepare ( $sql );
$sth->execute ();

$items = $sth->fetchAll ( PDO::FETCH_ASSOC );

$row=0;

echo "Extracting items...<br/><br/>";


foreach($items as $key=>$item){

	//if ($row>8000) break;


	
	$sql2 = "SELECT DISTINCT value FROM item_data WHERE item_id='".$item['item_id']."'";
	
	$sth2 = $dbh->prepare ( $sql2 );
	$sth2->execute ();
	$values = $sth2->fetchAll ( PDO::FETCH_ASSOC );

	
	foreach ($values as $item_val){
		if ($item_val['value']!=''){
			
			echo $item['name']."__".$item['item_data_type_id']."+".$item['item_id']."+".$item['name']."+".$item['description']."+".$item['units']."+".
				$item['item_data_type_id']."+".$item['oc_oid']."+".$item_val['value'];
			echo "<br/><br/>";
	        ob_flush();
        	flush();
			
		$container->addItem($item['name']."__".$item['item_data_type_id'],$item['item_id'],$item['name'],$item['description'],$item['units'],
				$item['item_data_type_id'],$item['oc_oid'],$item_val['value']);
		}
	}
	
	
	$row++;
}
ob_end_flush();
//echo json_encode($container->getItem("13_BMI2__7"));
echo "<br/><br/>";
//echo implode("**",$container->keys());
//echo "<br/><br/>";
//var_dump($container);

$container->saveContainer("items_octest_all.csv");

echo '<a href="download.php">Download csv data</a>';

?>