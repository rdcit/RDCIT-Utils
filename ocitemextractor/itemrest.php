<?php
session_start ();
header( 'Content-type: text/html; charset=utf-8' );

require_once 'settings/db.inc.php';
require_once 'classes/Item.class.php';

// read the category value from the url
if (isset ( $_GET ['item'] )) {
	$i = $_GET ['item'];
} // set default value for category
else {
	$i = null;
	die("Missing item name.");
}


$data_type = 6;


$itemData = new Item();
$itemData->setName($i);
$itemData->setData_type($data_type);
$itemData->setCounter(0);


try {
	$dbh = new PDO ( "pgsql:dbname=$db;host=$dbhost", $dbuser, $dbpass );
} catch ( PDOException $e ) {
	echo 'Connection failed: ' . $e->getMessage ();
}

$sql = "SELECT item_id, name, description, units, item_data_type_id, oc_oid FROM item WHERE name ='".$i."' AND item_data_type_id='".$data_type."'";

$sth = $dbh->prepare ( $sql );
$sth->execute ();

$items = $sth->fetchAll ( PDO::FETCH_ASSOC );

$row=0;

foreach($items as $key=>$item){

	//if ($row>8000) break;


	
	$sql2 = "SELECT value FROM item_data WHERE item_id='".$item['item_id']."'";
	
	$sth2 = $dbh->prepare ( $sql2 );
	$sth2->execute ();
	$values = $sth2->fetchAll ( PDO::FETCH_ASSOC );


	foreach ($values as $item_val){
		if ($item_val['value']!=''){
			
		$itemData->add($item['item_id'],$item['item_id'],$item['description'],$item['units'], $item['oc_oid'],$item_val['value']);
		
		}
	}
	
	
	$row++;
}

$responseArray = array (
		'serviceProvider' => 'CIT Cambridge',
		'serviceName' => 'itemdata',
		'counter'=>$itemData->getCounter(),
		'data' => $itemData->getTopValues(4)
);

//header ( "Access-Control-Allow-Origin: *" );
header ( 'Content-Type: application/json' );
echo json_encode ( $responseArray );



?>