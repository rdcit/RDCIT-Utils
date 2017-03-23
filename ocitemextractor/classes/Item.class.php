<?php




class Item implements JsonSerializable{
	protected $id;
	protected $oc_ids = array();
	protected $name;
	protected $descriptions = array();
	protected $units = array();
	protected $data_type;
	protected $oids = array(); 
	protected $values = array();
	protected $counter;

	
	
	//GETTERS
	
	function getId(){
		return $this->id;
	}
	function getOc_ids(){
		return $this->oc_ids;
	}
	function getName(){
		return $this->name;
	}
	function getDescriptions(){
		return $this->descriptions;
	}
	function getUnits(){
		return $this->units;
	}
	function getData_type(){
		return $this->data_type;
	}
	function getOids(){
		return $this->oids;
	}
	function getValues(){
		return $this->values;
	}
	function getCounter(){
		return $this->counter;
	}
	
	//CUSTOM FUNCTIONS
	public function add($id,$oc_ids,$descriptions,$units,$oids,$value){
		
		if(!in_array($oc_ids,$this->oc_ids)){
			$this->oc_ids[] = $oc_ids;
		}
		
		if(!in_array($descriptions,$this->descriptions)){
			$this->descriptions[] = $descriptions;
		}
		
		if(!in_array($units,$this->units)){
			$this->units[] = $units;
		}
		
		if(!in_array($oids,$this->oids)){
			$this->oids[] = $oids;
		}
		
		if(isset($this->values[$value])){
			$curr = $this->values[$value];
			$curr++;
			$this->values[$value] = $curr;
			
		}
		else{
			$this->values[$value]=1;
			
		}
		$this->counter++;
		
	}

	public function setName($name){
		$this->name=$name;
	}
	
	public function setData_type($data_type){
		$this->data_type=$data_type;
	}
	
	public function setCounter($counter){
		$this->counter=$counter;
	}
	
	
	public function getTopValues($top){
		$sortedArr = $this->values;
		asort($sortedArray);
		
		return  array_slice($sortedArr,0,$top, true);
		
	}
	
	
	public function jsonSerialize()
	{
		return get_object_vars($this);
	}
	
}

?>