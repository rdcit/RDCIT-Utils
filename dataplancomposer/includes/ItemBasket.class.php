<?php

class ItemBasket {
	
	var $basketName;
	var $items;
	
	
	function __construct($name) {
		$this->basketName = $name;
		$this->items = $_SESSION[$this->basketName];
	}
	
	function getItems() {
		return $this->items;
	}
	
	function addItem($item) {
		$addItem = true;
		foreach($this->items as $items){
			if($items["id"]==$item["id"]){
				$addItem = false;
				break;
			}
		}
		if($addItem){
			array_push($this->items, $item);
		}
		
	}
	
	function removeItem($item_code){
		foreach($this->items as $key=>$item){
			if ($item["id"]==$item_code){
				unset($this->items[$key]);
			}
		}
//		if (isset($this->items[$item_code])){
//			unset($this->items[$item_code]);
//		}
	}
	
	function isItemInBasket($item_id){

		foreach($this->items as $items){
			if($items["id"]==$item_id){
				return true;
			}
		}
		return false;
	}
	
	
	
	function hasItems() {
		return (bool) $this->items;
	}
	
	function save() {

		$_SESSION[$this->basketName] = $this->items;
	}
	
	function load($name){
		if($_SESSION[$name] != null){
			$this->items = $_SESSION[$name];
		}
		else{
			$this->items = array();
		}
		
		
	}
	
	function emptyBasket(){
		$this->items=null;
	}
	
	function orderItems($new_order){
		$newItems = array();
		foreach($new_order as $itemid){
			foreach($this->items as $item){
				if($item["id"]==$itemid){
					$newItems[]=$item;
					break;
				}
			}
			
		}
		$this->items=$newItems;
	}
	

	
}


?>