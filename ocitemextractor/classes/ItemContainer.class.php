<?php
require_once 'Item.class.php';
require_once 'PHPExcel.php';

class ItemContainer{
	protected $containerName;
	protected $items = array();
	
/* 	
	protected $id;
	protected $oc_ids = array();
	protected $name;
	protected $descriptions = array();
	protected $units = array();
	protected $data_type;
	protected $oids = array();
	protected $values = array(); */


	public function getItem($key){
		if (isset($this->items[$key])) return $this->items[$key];
	}
	
	public function addItem($key,$oc_id,$name,$description,$unit,$data_type,$oid,$value) {

			if (isset($this->items[$key])) {
				$this->items[$key]->add($key,$oc_id,$description,$unit,$data_type,$oid,$value);
				
			}
			else if ($key!=null && $key!=''){
				
				$i = new Item($key,$oc_id,$name,$description,$unit,$data_type,$oid,$value);
				$this->items[$key] = $i;
			}
	
	}
	
	public function setContainerName($name){
		$this->containerName = $name;
	}
	
	
	
	public function saveContainer($filename){
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$row = 2;
		
		$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'ITEM_ID');
		$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'NAME');
		$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'DESCRIPTION');
		$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'UNITS');
		$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'DATA_TYPE');
		$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'OID');
		$objPHPExcel->getActiveSheet()->SetCellValue('G1', 'VALUES');
		
		
		foreach($this->items as $items){
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $items->getId());
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $items->getName());
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, implode("##",$items->getDescriptions()));
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, implode("##",$items->getUnits()));
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $items->getData_type());
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, implode("##",$items->getOids()));
			
			$valCounter=1;
			foreach ($items->getValues() as $value){
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7+$valCounter, $row, $value);
				
				if ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow(7+$valCounter, 1)->getValue() =='' || 
						$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(7+$valCounter, 1)->getValue() == null){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7+$valCounter, 1, "VALUE".$valCounter);
					
				}
				$valCounter++;
			}
			//$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, implode("##",$items->getValues()));
				
			$row++;
		}
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
		$objWriter->save('./'.$filename);
		
	}
	
	
	public function keys() {
		return array_keys($this->items);
	}
	
	
	
}



?>