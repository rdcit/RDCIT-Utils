<?php
 
include('../includes/ItemBasket.class.php');
include('../includes/connection.inc.php');

session_start();
$Basket = new ItemBasket('shopping_basket');
$Basket->load('shopping_basket');

$jsonReceiveData = json_decode(file_get_contents('php://input'),true);

if ( !empty($jsonReceiveData['list']) && $jsonReceiveData['action_type']=="add") {
	foreach ($jsonReceiveData['list'] as $item){
		$Basket->addItem($item);
	}
}
 
if ( !empty($jsonReceiveData['list']) && $jsonReceiveData['action_type']=="remove") {
	foreach ($jsonReceiveData['list'] as $item_code){
		$Basket->removeItem($item_code);
	}
}

if ( !empty($jsonReceiveData['list']) && $jsonReceiveData['action_type']=="reorder") {
		$Basket->orderItems($jsonReceiveData['list']);
}

if ( $jsonReceiveData['action_type']=="empty") {
		$Basket->emptyBasket();

}
 
$Basket->save();


 
?>